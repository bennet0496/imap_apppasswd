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

//namespace bennetcc;
//
//class_alias('\bennetcc\imap_apppasswd', '\imap_apppasswd');

use bennetcc\Log;

require "util.php";
require "log.php";

const IMAP_APPPW_PREFIX = "imap_apppasswd";
const IMAP_APPPW_LOG_FILE = "imap_apppw";
const IMAP_APPPW_VERSION = "git-main";

function __(string $val): string
{
    return IMAP_APPPW_PREFIX . "_" . $val;
}

class imap_apppasswd extends \rcube_plugin
{
    public $task = 'settings';
    private \rcmail $rc;
    private \PDO $db;

    private Log $log;

    function init(): void
    {
        $this->load_config('config.inc.php.dist');
        $this->load_config();

        $this->add_texts('l10n/', true);
        $this->rc = \rcmail::get_instance();

        $this->log = new Log(IMAP_APPPW_LOG_FILE, IMAP_APPPW_PREFIX, $this->rc->config->get(__('log_level'), \bennetcc\LogLevel::INFO->value));

        if ($this->rc->task == 'settings') {
            $dsn = $this->rc->config->get(__('db_dsn'));
            $dbu = $this->rc->config->get(__('db_username'));
            $dbpw = $this->rc->config->get(__('db_password'));

            if ($dsn) {
                $this->db = new PDO($dsn, $dbu, $dbpw);
            } else {
                $this->log->error("database not selected");
                return;
            }

            $this->add_hook('settings_actions', [$this, 'hook_settings_actions']);
            $this->register_action('plugin.imap_apppasswd', [$this, 'action_show_settings']);
            $this->register_action('plugin.imap_apppasswd.history', [$this, 'action_show_history']);

            $this->register_action('plugin.imap_apppasswd.remove', [$this, 'action_remove_password']);
            $this->register_action('plugin.imap_apppasswd.delete_all', [$this, 'action_delete_all']);
            $this->register_action('plugin.imap_apppasswd.add', [$this, 'action_add_password']);
            $this->register_action('plugin.imap_apppasswd.rename', [$this, 'action_rename_password']);

        }
    }

    /**
     * Action handler for `plugin.imap_apppasswd`.
     * Called to render the settings page
     * @return void
     */
    public function action_show_settings(): void
    {
        if ($this->rc->output->type != 'html') {
            // don't run on ajax
            return;
        }

        //Object handler for password list
        $this->register_handler('imap_apppasswd.apppw_list', [$this, 'object_handler_apppw_list']);

        //Object handlers for username in description table
        $this->register_handler('imap_apppasswd.username',
            fn ($attrib) => html::quote(rcube_utils::idn_to_utf8($this->resolve_username())));

        $this->register_handler('imap_apppasswd.smtp_username', [$this, 'resolve_username']);

        //Page title
        $this->rc->output->set_pagetitle($this->gettext('imap_apppasswd'));

        //Include our style and scripts
        $this->include_stylesheet("imap_apppasswd.css");
        $this->include_script("imap_apppasswd.js");

        if ($this->is_disabled()) {
            $this->rc->output->send('imap_apppasswd.disabled');
        } else {
            //send the main settings template
            $this->rc->output->send('imap_apppasswd.apppasswords');
        }
    }

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
     * Action handler for `plugin.imap_apppasswd_add`
     * Called from JS via AJAX to create a new password
     * @return void
     * @throws Exception
     */
    public function action_add_password(): void
    {
        if ($this->rc->output->type != 'js') {
            // only run on ajax
            return;
        }

        $desired_len = $this->rc->config->get(__('length'), 16);
        if  (!is_int($desired_len) || $desired_len < 1) {
            $this->log->error(sprintf("length must be integer greater than 0, got %d", $desired_len));
            rcube::raise_error([
                'file'    => __FILE__,
                'line'    => __LINE__,
                'message' => 'miss-configured plugin '.__CLASS__
            ], true, true);
        }

        try { // Draw random bytes, as defined by config
            $random_bytes = random_bytes($desired_len);
            $salt = random_bytes(16);
        } catch (Exception) { // Fallback if entropy is low
            $random_bytes = openssl_random_pseudo_bytes($desired_len);
            $salt = openssl_random_pseudo_bytes(16);
        }

        //random call is broken
        if ($random_bytes === false || $salt === false || strlen($random_bytes) < $desired_len) {
            $this->log->error("random_bytes and/or openssl return no random value. Please check your system configuration");
            rcube::raise_error([
                'file'    => __FILE__,
                'line'    => __LINE__,
                'message' => 'randomness error'
            ], true, true);
        }

        //Map random chars to a-zA-Z0-9
        //TODO: we might want to consider excluding vowels to prevent accidental generation of words or even slurs
        $password = implode("-", str_split(implode(array_map(function (#[\SensitiveParameter] $c) {
            $i = ord($c);
            //$i = $i % (26 + 26 + 10);
            //map value for even probability. mod would be easier, but may skews the probability if
            //256 mod |alphabet| != 0
            $e = 26 + 26 + 10;
            $i = intval(round($i / ((2.0 ** 8.0) / floatval($e)))) % $e;

            $this->log->trace(sprintf('password mapper: ord($c)=%d, floatval(26 + 26 + 10)=%f, ((2.0 ** 8.0) / floatval(26 + 26 + 10))=%f, mapped_raw=%f, mapped=%d',
                ord($c), floatval(26 + 26 + 10), ((2.0 ** 8.0) / floatval(26 + 26 + 10)), ord($c) / ((2.0 ** 8.0) / floatval(26 + 26 + 10)), $i));

            if ($i < 10) { //0123456789 (ASCII 48-57)
                return chr($i + ord('0'));
            } else if ($i < 36) { // A-Z (ASCII 65-90)
                return chr($i - 10 + ord('A'));
            } else { // a-z (ASCII 97-122)
                return chr($i - 10 - 26 + ord('a'));
            }
        }, str_split($random_bytes))), $this->rc->config->get(__('chunksize'), 4)));

        // Map salt to abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./
        // because python crashes otherwise: ValueError: invalid characters in sha512_crypt salt
        // https://stackoverflow.com/a/71120618
        $mapped_salt = implode(array_map(function (#[\SensitiveParameter] $c) {
            $i = ord($c);
            //$i = $i % (26 + 26 + 10 + 2);
            //map value for even probability. mod would be easier, but may skews the probability if
            //256 mod |alphabet| != 0
            $e = 26 + 26 + 10 + 2;
            $i = intval(round($i / (((2.0 ** 8.0) - 1) / floatval($e)))) % $e;

            if ($i < 12) { // ./0123456789 (ASCII 46-57) (Rand 0-45 => i 0-11)
                return chr($i + ord('.'));
            } else if ($i < 38) { // A-Z (ASCII 65-90) (Rand 46-149 => i 12-37)
                return chr($i - 12 + ord('A'));
            } else { // a-z (ASCII 97-122) (Rand 150-255 => i 38-64)
                return chr($i - 12 - 26 + ord('a'));
            }
        }, str_split($salt)));

        //hash with SHA512 and default settings
        $hash = "{CRYPT}" . crypt($password, "$6$" . $mapped_salt);
        $this->log->debug($hash);

        //insert without name
        $s = $this->db->prepare("INSERT INTO app_passwords (uid, password, created) VALUES (:uid, :password, UTC_TIMESTAMP());");

        $s->bindValue("uid", $this->resolve_username());
        $s->bindValue("password", $hash);

        if ($s->execute()) {
            $this->rc->output->command("plugin.imap_apppasswd.add", ["id" => $this->db->lastInsertId(), "passwd" => $password]);
        } else {
            $this->rc->output->show_message($this->gettext("apppw_add_error"), "error");
        }

        $this->log->info($this->rc->user->get_username() . " added an app password");

    }

    /**
     * Action handler for `plugin.imap_apppasswd.rename`
     * Called from JS via AJAX to edit the name/comment
     * @return void
     */
    public function action_rename_password(): void
    {
        if ($this->rc->output->type != 'js') {
            // only run on ajax
            return;
        }

        //stripe HTML tag to prevent XSS
        $name = strip_tags(filter_input(INPUT_POST, 'name'));
        $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
        // We need uid here to protect from users renaming each others passwords
        $s = $this->db->prepare("UPDATE app_passwords SET comment = :comment WHERE id = :id AND uid = :uid;");
        $s->bindValue("comment", $name);
        $s->bindValue("id", $id, \PDO::PARAM_INT);
        $s->bindValue("uid", $this->resolve_username());

        if ($s->execute()) {
            $this->rc->output->command("plugin.imap_apppasswd.renamed", ["id" => $id, "name" => $name]);
        } else {
            $this->rc->output->show_message($this->gettext("apppw_rename_error"), "error");
        }

        $this->log->info($this->rc->user->get_username() . " renamed app password " . $id . " to " . $name);
    }

    /**
     * Action handler for `plugin.imap_apppasswd.remove`
     * Called from JS via AJAX to delete a password
     * @param mixed|null $attr
     * @return void
     */
    public function action_remove_password(): void
    {
        if ($this->rc->output->type != 'js') {
            // only run on ajax
            return;
        }

        $this->log->debug($_REQUEST);
        $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);

        if ($this->rc->config->get(__("delete_mode"), "soft") == "hard") {
            // We need uid here to protect from users delete each others passwords
            $s = $this->db->prepare("DELETE FROM app_passwords WHERE id = :id AND uid = :uid;");
        } else {
            $s = $this->db->prepare("UPDATE app_passwords SET deleted = UTC_TIMESTAMP(3) WHERE id = :id AND uid = :uid;");
        }

        $s->bindValue("id", $id, \PDO::PARAM_INT);
        $s->bindValue("uid", $this->resolve_username());

        if ($s->execute()) {
            $this->rc->output->command("plugin.imap_apppasswd.remove_from_list", ["id" => $id]);
            $this->rc->output->show_message($this->gettext("apppw_deleted_success"));
        } else {
            $this->rc->output->show_message($this->gettext("apppw_deleted_error"), "error");
        }

        $this->log->info($this->rc->user->get_username() . " deleted app password " . $id);
    }

    /**
     * Action handler for `plugin.imap_apppasswd.delete_all`
     * Called from JS via AJAX to delete all passwords
     * @param mixed|null $attr
     * @return void
     */
    public function action_delete_all(): void
    {
        $this->log->debug($_REQUEST);

        if ($this->rc->config->get(__("delete_mode"), "soft") == "hard") {
            // We need uid here to protect from users delete each others passwords
            $s = $this->db->prepare("DELETE FROM app_passwords WHERE uid = :uid;");
        } else {
            $s = $this->db->prepare("UPDATE app_passwords SET deleted = UTC_TIMESTAMP(3) WHERE uid = :uid;");
        }

        $s->bindValue("uid", $this->resolve_username());

        if ($s->execute()) {
            $this->rc->output->command("plugin.imap_apppasswd.remove_from_list", ["id" => "all"]);
            $this->rc->output->show_message($this->gettext("apppw_deleted_success"));
        } else {
            $this->rc->output->show_message($this->gettext("apppw_deleted_error"), "error");
        }

        $this->log->info($this->rc->user->get_username() . " deleted all app passwords");
    }

    /**
     * Indirect object handler for the history GUI object, rendering the actual history table
     * @param int $id ID of the password to render
     * @return callable callback for rendering
     */
    function object_handler_history(int $id): callable
    {
        /**
         * The actual callback for `imap_apppasswd.history` using the ID we need
         * @param never $ignore ignored
         * @return string  HTML string of history table
         * @throws PDOException
         * @throws DateMalformedStringException
         */
        return function ($ignore) use ($id) {
            $this->log->debug("password " . $id);
            //get page number and clamp to 0 < $page < INT_MAX
            $page = intval(filter_input(INPUT_GET, "_page", FILTER_SANITIZE_NUMBER_INT) ?? 1);
            $page = max(1, $page);
            $page_size = intval($this->rc->config->get(__("history_page_size"), 20)) ?? 20;
            //password ownership is checked in show_history()
            $s = $this->db->prepare("SELECT * FROM log, (SELECT count(*) as total FROM log WHERE pwid = :id) c WHERE pwid = :id ORDER BY timestamp DESC LIMIT 20 OFFSET :offset;");
            $s->bindParam(":id", $id, \PDO::PARAM_INT);
            $offset = ($page - 1) * $page_size;
            $s->bindParam(":offset", $offset, \PDO::PARAM_INT);
            $s->execute();

            //no logs yet or page number to high
            if ($s->rowCount() == 0) {
                return \html::span(["class" => "my-5 block w-100 h-100 block text-center"], $this->gettext("no_history"));
            }

            //Table head
            $table = new \html_table(["class" => "w-100"]);
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
     * List of passwords in the Settings tab content.
     * @return string HTML List
     * @throws DateMalformedStringException
     */
    public function object_handler_apppw_list(): string
    {
        //get app password for user
        $s = $this->db->prepare("SELECT * FROM app_passwords_with_log WHERE uid = :uid AND deleted IS NULL;");
        $user_name = $this->resolve_username();

        $s->bindValue("uid", $user_name);
        $s->execute();

        //add and optionally show the 'no_password' element
        $html = \html::span(['class' => 'no_passwords ' . ($s->rowCount() == 0 ? '' : 'hidden')], $this->gettext('no_passwords'));

        while ($row = $s->fetch(\PDO::FETCH_ASSOC)) {
            $this->log->trace($row);
            $now = new DateTimeImmutable("now", new DateTimeZone("UTC"));

            $last_used = new DateTimeImmutable($row['last_used_timestamp'] ?? "01-01-1970 00:00:00.0000", new DateTimeZone("UTC"));
            $created = new DateTimeImmutable($row['created'] ?? "01-01-1970 00:00:00.0000", new DateTimeZone("UTC"));

            $this->log->trace($now, $last_used, $created);

            //ugly but it works...
            $html .= \html::div(['class' => 'apppw_entry', 'data-apppw-id' => $row['id']],
                \html::span(['class' => 'apppw_title'],
                    \html::span(['class' => 'apppw_title_text'], ($row['comment'] ?? $this->gettext('unnamed_app'))) .
                    \html::a(['class' => 'apppw_title_edit', 'title' => $this->gettext('edit'), 'onclick' => 'apppw_edit(' . $row['id'] . ')'], IMAP_APPPW_EDIT_BTN)) .
                \html::span(['class' => 'apppw_lastused', 'title' => $row['last_used_timestamp'] == null ? $this->gettext('never_used') : $last_used->format(DATE_RFC822)],
                    $row['last_used_timestamp'] == null ?
                        $this->gettext('never_used') :
                        $this->gettext('last_used') . " " . $this->format_diff($now->diff($last_used)) . " " . $this->gettext('last_used_from') . " " .
                        \html::span(['title' => $row['src_ip'] ?? ""],
                            (empty($row['last_used_src_rdns']) || $row['last_used_src_rdns'] == "<>" ? $row['last_used_src_ip'] : $row['last_used_src_rdns']) . (empty($row['last_used_src_isp']) ? "" : " (" . $row['last_used_src_isp'] . ")"))) .
                \html::span(['class' => 'apppw_location'], (empty($row['last_used_src_loc']) ? $this->gettext('unknown_location') : $row['last_used_src_loc'])) .
                \html::span(['class' => 'apppw_created', 'title' => $created->format(DATE_RFC822)], $this->gettext('created') . " " . $this->format_diff($now->diff($created))) .
                \html::a(['class' => 'apppw_delete', 'href' => $this->rc->url(["_action" => "plugin.imap_apppasswd.history", "_pwid" => $row['id']])], $this->gettext("show_full_history")) .
                \html::a(['class' => 'apppw_delete', 'onclick' => 'return rcmail.command("plugin.imap_apppasswd.remove",' . $row['id'] . ',this,event)'], $this->gettext("delete"))
            );
        }

        return $html;
    }

    /**
     * Object handler for the history entry counter in the paginator
     * strongly inspired by the massages pagination of RC itself
     * @param mixed $attrib RC stuff
     * @param int $total total number of entries
     * @return string HTML text
     */
    private function object_handler_history_count($attrib, int $total): string
    {
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmcountdisplay';
        }

        $this->rc->output->add_gui_object('countdisplay', $attrib['id']);

        $content = $this->rc->action != 'show' ? $this->historycount_text($total) : $this->rc->gettext('loading');

        return html::span($attrib, $content);
    }

    /**
     * Add a tab to Settings.
     */
    public function hook_settings_actions($args): array
    {
        $args['actions'][] = [
            'action' => 'plugin.imap_apppasswd',
            'class' => 'imap_apppasswd',
            'label' => 'imap_apppasswd',
            'domain' => 'imap_apppasswd',
        ];

        return $args;
    }

    /**
     * Helper to resolve Roundcube username (email) to IMAP username
     *
     * Returns resolved name.
     *
     * @param $user string The username
     * @return string
     */
    private function resolve_username(string $user = ""): string
    {
        $this->log->trace("user: " . $user);

        if (empty($user)) {
            // verbatim roundcube username
            $user = $this->rc->user->get_username();
        }

        $this->log->trace("user: " . $user);

        $username_tmpl = $this->rc->config->get(__("username"));

        $mail = $this->rc->user->get_username("mail");
        $mail_local = $this->rc->user->get_username("local");
        $mail_domain = $this->rc->user->get_username("domain");

        $imap_user = empty($_SESSION['username']) ? $mail_local : $_SESSION['username'];

        $this->log->trace($username_tmpl, $mail, $mail_local, $mail_domain, $imap_user);

        return str_replace(["%s", "%i", "%e", "%l", "%u", "%d", "%h"],
            [$user, $imap_user, $mail, $mail_local, $mail_local, $mail_domain, $_SESSION['storage_host'] ?? ""],
            $username_tmpl);
    }

    /**
     * Format a DateInternal as human-readable string like "n days ago"
     * @param DateInterval $diff
     * @return string
     */
    private function format_diff(DateInterval $diff): string
    {
        if ($diff->format("%y") != "0") {
            return sprintf($this->gettext("years_ago"), $diff->format("%y"));
        }
        if ($diff->format("%m") != "0") {
            return sprintf($this->gettext("months_ago"), $diff->format("%m"));
        }
        if ($diff->format("%d") != "0") {
            return sprintf($this->gettext("days_ago"), $diff->format("%d"));
        }
        if ($diff->format("%h") != "0") {
            return sprintf($this->gettext("hours_ago"), $diff->format("%h"));
        }
        if ($diff->format("%i") != "0") {
            return sprintf($this->gettext("minutes_ago"), $diff->format("%i"));
        }

        return $this->gettext("just_now");

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

    private function is_disabled(): bool
    {
        $ex = $this->rc->config->get(__("exclude_users"), []);
        $exg = $this->rc->config->get(__("exclude_users_in_addr_books"), []);
        $exa = $this->rc->config->get(__("exclude_users_with_addr_book_value"), []);
        /** @noinspection SpellCheckingInspection */
        $exag = $this->rc->config->get(__("exclude_users_in_addr_book_group"), []);

        $this->log->trace($ex,$exg, $exa, $exag);

        // exclude directly deny listed users
        if (is_array($ex) && (in_array($this->rc->get_user_name(), $ex) || in_array($this->resolve_username(), $ex) || in_array($this->rc->get_user_email(), $ex))) {
            $this->log->info("access for " . $this->resolve_username() . " disabled via direct deny list");
            return true;
        }

        // exclude directly deny listed address books
        if (is_array($exg) && count($exg) > 0) {
            foreach ($exg as $book) {
                /** @noinspection SpellCheckingInspection */
                $abook = $this->rc->get_address_book($book);
                if ($abook) {
                    if (array_key_exists("uid", $book->coltypes)) {
                        $entries = $book->search(["email", "uid"], [$this->rc->get_user_email(), $this->resolve_username()]);
                    } else {
                        $entries = $book->search("email", $this->rc->get_user_email());
                    }
                    if ($entries) {
                        $this->log->info("access for " . $this->resolve_username() .
                            " disabled in " . $book->get_name() . " because they exist in there");
                        return true;
                    }
                }
            }
        }

        // exclude users with a certain attribute in an address book
        if (is_array($exa) && count($exa) > 0) {
            // value not properly formatted
            if (!is_array($exa[0])) {
                $exa = [$exa];
            }
            foreach ($exa as $val) {
                if (count($val) == 3) {
                    $book = $this->rc->get_address_book($val[0]);
                    $attr = $val[1];
                    $match = $val[2];

                    if (array_key_exists("uid", $book->coltypes)) {
                        $entries = $book->search(["email", "uid"], [$this->rc->get_user_email(), $this->resolve_username()]);
                    } else {
                        $entries = $book->search("email", $this->rc->get_user_email());
                    }

                    if ($entries) {
                        while ($e = $entries->iterate()) {
                            if (array_key_exists($attr, $e) && ($e[$attr] == $match ||
                                    (is_array($e[$attr]) && in_array($match, $e[$attr])))) {
                                $this->log->info("access for " . $this->resolve_username() .
                                    " disabled in " . $book->get_name() . " because of " . $attr . "=" . $match);
                                return true;
                            }
                        }
                    }
                }
            }
        }

        // exclude users in groups
        if (is_array($exag) && count($exag) > 0) {
            if (!is_array($exag[0])) {
                /** @noinspection SpellCheckingInspection */
                $exag = [$exag];
            }
            foreach ($exag as $val) {
                if (count($val) == 2) {
                    $book = $this->rc->get_address_book($val[0]);
                    $group = $val[1];

                    $groups = $book->get_record_groups(base64_encode($this->resolve_username()));

                    if (in_array($group, $groups)) {
                        $this->log->info("access for " . $this->resolve_username() .
                            " disabled in " . $book->get_name() . " because of group membership " . $group);
                        return true;
                    }
                }
            }
        }

        return false;
    }
}