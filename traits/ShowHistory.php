<?php
/*
 * Copyright (c) 2024. Bennet Becker <dev@bennet.cc>
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace bennetcc\imap_apppasswd\traits;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use html;
use html_table;
use PDO;
use PDOException;
use rcube;
use function bennetcc\imap_apppasswd\__;

trait ShowHistory {
    /**
     * Action handler for `plugin.imap_apppasswd.history`.
     * Called to start rendering the history page
     * @return void
     * @throws Exception
     */
    public function action_show_history(): void
    {
        if ($this->rc->output->type != 'html') {
            // don't run on ajax
            return;
        }
        //include our style and scripts
        $this->include_stylesheet("imap_apppasswd.css");
        $this->include_script("imap_apppasswd.js");
        //a valiant effort...
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->rc->output->include_script('list.js');

        //get password id to display the history for
        $pwid = filter_input(INPUT_GET, "_pwid", FILTER_SANITIZE_NUMBER_INT);
        if (!empty($pwid) && is_numeric($pwid)) {
            //select password info to validate ownership and get counter values
            $s = $this->db->prepare("SELECT a.*, count(l.pwid) as total FROM app_passwords a LEFT JOIN log l on a.id = l.pwid WHERE a.uid = :uid and a.id = :id;");
            $user_name = $this->resolve_username();

            //user must own password
            $s->bindParam(":uid", $user_name);
            $s->bindParam(":id", $pwid);
            $s->execute();
            //only one or none password should exist
            if ($s->rowCount() == 1) {
                $row = $s->fetch(PDO::FETCH_ASSOC);
                //register the table object handler, actually rendering the history
                $this->register_handler('imap_apppasswd.history', $this->object_handler_history($pwid));

                //title element
                $this->register_handler('imap_apppasswd.imap_apppasswd.history_title',
                    fn($ignore) => $this->gettext(['name' => 'history_for', 'vars' => ['password' => $row['comment']]]));
                //url for back button
                $this->register_handler('imap_apppasswd.imap_apppasswd.history_title.back',
                    fn($ignore) => $this->rc->url("plugin.imap_apppasswd"));
                //total number of log entries
                $this->register_handler('imap_apppasswd.history.count',
                    fn($attrib) => $this->object_handler_history_count($attrib, $row['total']));
                //tab title
                $this->rc->output->set_pagetitle(
                    $this->gettext(['name' => 'history_for', 'vars' => ['password' => $row['comment']]]));

                //send the view
                $this->rc->output->send('imap_apppasswd.history');
                return;
            } elseif ($s->rowCount() > 1) { //there should _never_ be more than one password satisfying the constraint
                $this->log->error(sprintf("The database is broken! More than one password satisfies 'WHERE a.uid = %s and a.id = %s'.", $user_name, $pwid));
                rcube::raise_error([
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => 'password validation exception',
                ], true, true); //FATAL error
                die();
            }
        }
        //user doesn't own the password or the password does not exist. drop them back to password overview.
        $this->rc->output->redirect("plugin.imap_apppasswd");

    }
    
    /**
     * Indirect object handler for the history GUI object, rendering the actual history table
     * @param int $id ID of the password to render
     * @return callable callback for rendering
     * @noinspection PhpUnusedParameterInspection
     */
    function object_handler_history(int $id): callable
    {
        /**
         * The actual callback for `imap_apppasswd.history` using the ID we need
         * @param never $ignore ignored
         * @return string  HTML string of history table
         * @throws PDOException
         * @throws \DateMalformedStringException
         * @throws Exception
         */
        return function ($ignore) use ($id) {
            $this->log->debug("password " . $id);
            //get page number and clamp to 0 < $page < INT_MAX
            $page = intval(filter_input(INPUT_GET, "_page", FILTER_SANITIZE_NUMBER_INT) ?? 1);
            $page = max(1, $page);
            $page_size = intval($this->rc->config->get(__("history_page_size"), 20)) ?? 20;
            //password ownership is checked in show_history()
            $s = $this->db->prepare("SELECT * FROM log, (SELECT count(*) as total FROM log WHERE pwid = :id) c WHERE pwid = :id ORDER BY timestamp DESC LIMIT 20 OFFSET :offset;");
            $s->bindParam(":id", $id, PDO::PARAM_INT);
            $offset = ($page - 1) * $page_size;
            $s->bindParam(":offset", $offset, PDO::PARAM_INT);
            $s->execute();

            //no logs yet or page number to high
            if ($s->rowCount() == 0) {
                return html::span(["class" => "my-5 block w-100 h-100 block text-center"], $this->gettext("no_history"));
            }

            //Table head
            $table = new html_table(["class" => "w-100"]);
            $table->add_row();
            $table->add_header([], $this->gettext("timestamp"));
            $table->add_header([], $this->gettext("service"));
            $table->add_header([], $this->gettext("src_ip"));
            $table->add_header([], $this->gettext("src_rdns"));
            $table->add_header([], $this->gettext("src_loc"));
            $table->add_header([], $this->gettext("src_isp"));

            //value is assigned on each row, even though it's the
            //same on every row. But we don't have `$row` after the
            //loop anymore
            $total = 0;

            while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                //time is always UTC. JS on the client translates it to the user timezone
                $timestamp = new DateTimeImmutable($row['timestamp'] ?? "01-01-1970 00:00:00.0000", new DateTimeZone("UTC"));
                $table->add_row();
                $table->add(['class' => 'timestamp'], $timestamp->format(DATE_RFC822));
                $table->add([], strtoupper($row['service']));
                $table->add([], $row['src_ip']);
                $table->add([], $row['src_rdns']);
                $table->add(['class' => 'nowrap'], $row['src_loc']);
                $table->add(['class' => 'nowrap'], $row['src_isp']);
                $total = $row['total']; //hack from above
            }

            $maxpages = ceil(floatval($total) / floatval($page_size));

            //values for rendering the paginator
            $this->rc->output->set_env("pwid", $id);
            $this->rc->output->set_env("current_page", $page);
            $this->rc->output->set_env("pagecount", $maxpages);

            return $table->show();
        };
    }

    /**
     * Object handler for the history entry counter in the paginator
     * strongly inspired by the massages pagination of RC itself
     * @param mixed $attrib RC stuff
     * @param int $total total number of entries
     * @return string HTML text
     */
    private function object_handler_history_count(mixed $attrib, int $total): string
    {
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmcountdisplay';
        }

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->rc->output->add_gui_object('countdisplay', $attrib['id']);

        $content = $this->rc->action != 'show' ? $this->historycount_text($total) : $this->rc->gettext('loading');

        return html::span($attrib, $content);
    }

    /**
     * Utility function for {@link self::object_handler_history_count}
     * @param int|null $count number of pages
     * @param int|null $page current page
     * @return string text
     */
    private function historycount_text(int|null $count = null, int|null $page = null): string
    {
        if ($page === null) {
            $page = intval(filter_input(INPUT_GET, "_page", FILTER_SANITIZE_NUMBER_INT) ?? 1);
            $page = max(1, $page);
        }

        $page_size = intval($this->rc->config->get(__("history_page_size"), 20)) ?? 20;
        $start_msg = ($page - 1) * $page_size + 1;
        $max = $count;

        if (!$max) {
            $out = $this->gettext('no_history');
        } else {
            $out = $this->gettext([
                'name' => 'history_from_to_of',
                'vars' => [
                    'from' => $start_msg,
                    'to' => min($max, $start_msg + $page_size - 1),
                    'count' => $max
                ]
            ]);
        }

        return rcube::Q($out);
    }
}
