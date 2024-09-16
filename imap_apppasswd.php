<?php
// Copyright (c) 2024 Bennet Becker <dev@bennet.cc>
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

//namespace bennetcc;
//
//class_alias('\bennetcc\imap_apppasswd', '\imap_apppasswd');

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

    private \bennetcc\Log $log;

    function init(): void
    {
        $this->load_config('config.inc.php.dist');
        $this->load_config();
        $this->add_texts('l10n/', true);
        $this->rc = \rcmail::get_instance();

        $this->log = new \bennetcc\Log(IMAP_APPPW_LOG_FILE, IMAP_APPPW_PREFIX, $this->rc->config->get(__('log_level'), \bennetcc\LogLevel::INFO->value));

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

            $this->add_hook('settings_actions', [$this, 'settings_actions']);
            $this->register_action('plugin.imap_apppasswd', [$this, 'show_settings']);
            $this->register_action('plugin.imap_apppasswd.history', [$this, 'show_history']);

            $this->register_action('plugin.imap_apppasswd_remove', [$this, 'remove_password']);
            $this->register_action('plugin.imap_apppasswd_delete_all', [$this, 'delete_all']);
            $this->register_action('plugin.imap_apppasswd_add', [$this, 'add_password']);
            $this->register_action('plugin.imap_apppasswd_rename', [$this, 'rename_password']);
        }
    }

    public function show_history(): void {
        $this->include_stylesheet("imap_apppasswd.css");
        $this->include_script("imap_apppasswd.js");
        $pwid = filter_input(INPUT_GET, "_pwid", FILTER_SANITIZE_NUMBER_INT);
        if (!empty($pwid) && is_numeric($pwid)) {
            $s = $this->db->prepare("SELECT a.*, count(l.pwid) as total FROM app_passwords a LEFT JOIN mail.log l on a.id = l.pwid WHERE a.uid = :uid and a.id = :id;");
            $user_name = $this->resolve_username();

            $s->bindParam(":uid", $user_name);
            $s->bindParam(":id", $pwid);
            $s->execute();
            if ($s->rowCount() == 1) {
                $row = $s->fetch(PDO::FETCH_ASSOC);
                $this->register_handler('imap_apppasswd.history', $this->historyhtml($pwid));
                $this->register_handler('imap_apppasswd.imap_apppasswd.history_title', function ($ignore) use ($row) {
                    return $this->gettext(['name' => 'imap_apppasswd_history_for', 'vars' => ['password' => $row['comment']]]);
                });
                $this->register_handler('imap_apppasswd.imap_apppasswd.history_title.back', function ($ignore) use ($row) {
                    return $this->rc->url("plugin.imap_apppasswd");
                });
                $this->register_handler('imap_apppasswd.history.count', function ($attrib) use ($row) {
                    return $this->historycount_display($attrib, $row['total']);
                });
                $this->rc->output->set_pagetitle($this->gettext(['name' => 'imap_apppasswd_history_for', 'vars' => ['password' => $row['comment']]]));
                $this->rc->output->send('imap_apppasswd.history');
                return;
            }
        }

        $this->rc->output->redirect("plugin.imap_apppasswd");

    }

    function historyhtml(int $id): callable {
        return function ($args) use ($id) {
            $this->log->debug("password ".$id);
            $page = intval(filter_input(INPUT_GET, "_page", FILTER_SANITIZE_NUMBER_INT) ?? 1);
            $page = max(1, $page);
            //password ownership is checked in show_history()
            $s = $this->db->prepare("SELECT * FROM log, (SELECT count(*) as total FROM log WHERE pwid = :id) c WHERE pwid = :id ORDER BY timestamp DESC LIMIT 20 OFFSET :offset;");
            $s->bindParam(":id", $id, \PDO::PARAM_INT);
            $offset = ($page - 1) * 20;
            $s->bindParam(":offset", $offset, \PDO::PARAM_INT);
            $s->execute();

            if ($s->rowCount() == 0) {
                return \html::span(["class" => "my-5 block w-100 h-100 block text-center"], $this->gettext("no_history"));
            }

            $table = new \html_table(["class" => "w-100"]);
            $table->add_row();
            $table->add_header([], $this->gettext("timestamp"));
            $table->add_header([], $this->gettext("service"));
            $table->add_header([], $this->gettext("src_ip"));
            $table->add_header([], $this->gettext("src_rdns"));
            $table->add_header([], $this->gettext("src_loc"));
            $table->add_header([], $this->gettext("src_isp"));

            $total = 0;

            while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                $timestamp = new DateTimeImmutable($row['timestamp'] ?? "01-01-1970 00:00:00.0000", new DateTimeZone("UTC"));
                $table->add_row();
                $table->add(['class' => 'timestamp'], $timestamp->format(DATE_RFC822));
                $table->add([], strtoupper($row['service']));
                $table->add([], $row['src_ip']);
                $table->add([], $row['src_rdns']);
                $table->add(['class' => 'nowrap'], $row['src_loc']);
                $table->add(['class' => 'nowrap'], $row['src_isp']);
                $total = $row['total'];
            }

            $maxpages = ceil($total / 20.0);

            $this->rc->output->set_env("pwid", $id);
            $this->rc->output->set_env("current_page", $page);
            $this->rc->output->set_env("pagecount", $maxpages);

            return $table->show();
        };
    }

    /**
     * Add a tab to Settings.
     */
    public function settings_actions($args): array
    {
        $args['actions'][] = [
            'action' => 'plugin.imap_apppasswd',
            'class'  => 'imap_apppasswd',
            'label'  => 'imap_apppasswd',
            'domain' => 'imap_apppasswd',
        ];

        return $args;
    }

    public function show_settings(): void
    {
        $this->register_handler('imap_apppasswd.apppw_list', [$this, 'apppw_listhtml']);
        $this->register_handler('imap_apppasswd.username', function ($attrib) {
            return html::quote(rcube_utils::idn_to_utf8($this->resolve_username()));
        });
        $this->register_handler('imap_apppasswd.smtp_username', [$this, 'resolve_username']);

        $this->rc->output->set_pagetitle($this->gettext('imap_apppasswd'));

        $this->include_stylesheet("imap_apppasswd.css");
        $this->include_script("imap_apppasswd.js");

        $this->rc->output->send('imap_apppasswd.apppasswords');
    }

    /**
     * Helper to resolve Roundcube username (email) to Nextcloud username
     *
     * Returns resolved name.
     *
     * @param $user string The username
     * @return string
     */
    private function resolve_username(string $user = ""): string
    {
        $this->log->trace("user: ".$user);

        if (empty($user)) {
            // verbatim roundcube username
            $user = $this->rc->user->get_username();
        }

        $this->log->trace("user: ".$user);

        $username_tmpl = $this->rc->config->get(__("username"));

        $mail = $this->rc->user->get_username("mail");
        $mail_local = $this->rc->user->get_username("local");
        $mail_domain = $this->rc->user->get_username("domain");

        $imap_user = empty($_SESSION['username']) ? $mail_local : $_SESSION['username'];

        $this->log->trace($username_tmpl,$mail,$mail_local,$mail_domain,$imap_user);

        return str_replace(["%s", "%i", "%e", "%l", "%u", "%d", "%h"],
            [$user, $imap_user, $mail, $mail_local, $mail_local, $mail_domain, $_SESSION['storage_host'] ?? ""],
            $username_tmpl);
    }

    /**
     * List of passwords in the Settings tab content.
     */
    public function apppw_listhtml(): string {
        $s = $this->db->prepare("SELECT * FROM app_passwords_with_log WHERE uid = :uid;");
        $user_name = $this->resolve_username();

        $s->bindValue("uid", $user_name);
        $s->execute();

        $html = \html::span(['class' => 'no_passwords '.($s->rowCount() == 0 ? '' : 'hidden')],$this->gettext('no_passwords'));

        while ($row = $s->fetch(\PDO::FETCH_ASSOC)) {
            $this->log->trace($row);
            $now = new DateTimeImmutable("now", new DateTimeZone("UTC"));

            $last_used = new DateTimeImmutable($row['last_used_timestamp'] ?? "01-01-1970 00:00:00.0000", new DateTimeZone("UTC"));
            $created = new DateTimeImmutable($row['created'] ?? "01-01-1970 00:00:00.0000", new DateTimeZone("UTC"));

            $this->log->trace($now, $last_used, $created);

            $html .= \html::div(['class' => 'apppw_entry', 'data-apppw-id' => $row['id']],
                \html::span(['class' => 'apppw_title'],
                    \html::span(['class' => 'apppw_title_text'], ($row['comment'] ?? $this->gettext('unnamed_app'))).
                            \html::a(['class' => 'apppw_title_edit', 'title' => $this->gettext('edit'), 'onclick' => 'apppw_edit('.$row['id'].')'], IMAP_APPPW_EDIT_BTN)).
                        \html::span(['class' => 'apppw_lastused', 'title' => $row['last_used_timestamp'] == null ? $this->gettext('never_used') : $last_used->format(DATE_RFC822)],
                            $row['last_used_timestamp'] == null ?
                            $this->gettext('never_used') :
                            $this->gettext('last_used')." ".$this->format_diff($now->diff($last_used))." ".$this->gettext('last_used_from')." ".
                                \html::span(['title' => $row['src_ip'] ?? ""],
                                    (empty($row['last_used_src_rdns']) || $row['last_used_src_rdns'] == "<>" ? $row['last_used_src_ip'] : $row['last_used_src_rdns']).(empty($row['last_used_src_isp']) ? "" : " (".$row['last_used_src_isp'].")"))).
                        \html::span(['class' => 'apppw_location'], (empty($row['last_used_src_loc']) ? $this->gettext('unknown_location') : $row['last_used_src_loc'])).
                        \html::span(['class' => 'apppw_created', 'title' => $created->format(DATE_RFC822)], $this->gettext('created')." ".$this->format_diff($now->diff($created))).
                \html::a(['class' => 'apppw_delete', 'href' => $this->rc->url(["_action" => "plugin.imap_apppasswd.history", "_pwid" => $row['id']])], $this->gettext("show_full_history")).
                \html::a(['class' => 'apppw_delete', 'onclick' => 'apppw_remove('.$row['id'].')'], $this->gettext("delete"))
            );
        }

        return $html;
    }

    public function remove_password(mixed $attr = null) : void
    {
        $this->log->debug($attr,$_REQUEST);
        $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);

        // We need uid here to protect from users delete each others passwords
        $s = $this->db->prepare("DELETE FROM app_passwords WHERE id = :id AND uid = :uid;");

        $s->bindValue("id", $id, \PDO::PARAM_INT);
        $s->bindValue("uid", $this->resolve_username());

        if($s->execute()) {
            $this->rc->output->command("plugin.apppw_remove_from_list", ["id" => $_REQUEST['id']]);
            $this->rc->output->show_message($this->gettext("apppw_deleted_success"));
        } else {
            $this->rc->output->show_message($this->gettext("apppw_deleted_error"), "error");
        }

        $this->log->info($this->rc->user->get_username()." deleted app password ".$id);
    }

    public function delete_all(mixed $attr = null) : void
    {
        $this->log->debug($attr,$_REQUEST);

        // We need uid here to protect from users delete each others passwords
        $s = $this->db->prepare("DELETE FROM app_passwords WHERE uid = :uid;");

        $s->bindValue("uid", $this->resolve_username());

        if($s->execute()) {
            $this->rc->output->command("plugin.apppw_remove_from_list", ["id" => "all"]);
            $this->rc->output->show_message($this->gettext("apppw_deleted_success"));
        } else {
            $this->rc->output->show_message($this->gettext("apppw_deleted_error"), "error");
        }

        $this->log->info($this->rc->user->get_username()." deleted all app passwords");
    }

    public function add_password() : void
    {
        try {
            $rand = random_bytes($this->rc->config->get('imap_apppasswd_length', 16));
            $salt = random_bytes(16);
        } catch (\Random\RandomException $e) {
            $rand = openssl_random_pseudo_bytes($this->rc->config->get('imap_apppasswd_length', 16));
            $salt = openssl_random_pseudo_bytes(16);
        }

        //Map random chars to a-zA-Z0-9
        //TODO: we might want to consider excluding vowels to prevent accidental generation of words or even slurs
        $pw = implode("-", str_split(implode(array_map(function($c) {
            $i = ord($c);
            $i = $i % (26 + 26 + 10);

            if($i < 10) { //0123456789
                return chr($i + 48);
            } else if ($i < 36) { // A-Z
                return chr($i - 10 + 65);
            } else { // a-z
                return chr($i - 10 - 26 + 97);
            }
        }, str_split($rand))), $this->rc->config->get('imap_apppasswd_chunksize', 4)));

        // Map salt to abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./
        // because python crashes otherwise: ValueError: invalid characters in sha512_crypt salt
        // https://stackoverflow.com/a/71120618
        $msalt = implode(array_map(function($c) {
            $i = ord($c);
            $i = $i % (26 + 26 + 10 + 2);
            if($i < 12) { // ./0123456789
                return chr($i + 46);
            } else if ($i < 38) { // A-Z
                return chr($i - 12 + 65);
            } else { // a-z
                return chr($i - 12 - 26 + 97);
            }
        }, str_split($salt)));

        $hash = "{CRYPT}".crypt($pw, "$6$".$msalt);
        $this->log->debug($hash);

        $s = $this->db->prepare("INSERT INTO app_passwords (uid, password, created) VALUES (:uid, :password, UTC_TIMESTAMP());");

        $s->bindValue("uid", $this->resolve_username());
        $s->bindValue("password", $hash);

        if($s->execute()) {
            $this->rc->output->command("plugin.apppw_add", ["id" => $this->db->lastInsertId(),"passwd" => $pw]);
        } else {
            $this->rc->output->show_message($this->gettext("apppw_add_error"), "error");
        }

        $this->log->info($this->rc->user->get_username()." added an app password");

    }

    public function rename_password() : void
    {
        $name = strip_tags($_REQUEST['name']);
        $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
        // We need uid here to protect from users renaming each others passwords
        $s = $this->db->prepare("UPDATE app_passwords SET comment = :comment WHERE id = :id AND uid = :uid;");
        $s->bindValue("comment", $name);
        $s->bindValue("id", $id, \PDO::PARAM_INT);
        $s->bindValue("uid", $this->resolve_username());

        if($s->execute()) {
            $this->rc->output->command("plugin.apppw_renamed", ["id" => $id, "name" => $name]);
        } else {
            $this->rc->output->show_message($this->gettext("apppw_rename_error"), "error");
        }

        $this->log->info($this->rc->user->get_username()." renamed app password ".$id." to ".$name);
    }
    private function format_diff(DateInterval $diff) : string {
        if($diff->format("%y") != "0") {
            return sprintf($this->gettext("years_ago"), $diff->format("%y"));
        }
        if($diff->format("%m") != "0") {
            return sprintf($this->gettext("months_ago"), $diff->format("%m"));
        }
        if($diff->format("%d") != "0") {
            return sprintf($this->gettext("days_ago"), $diff->format("%d"));
        }
        if($diff->format("%h") != "0") {
            return sprintf($this->gettext("hours_ago"), $diff->format("%h"));
        }
        if($diff->format("%i") != "0") {
            return sprintf($this->gettext("minutes_ago"), $diff->format("%i"));
        }

        return $this->gettext("just_now");

    }

    private function historycount_display($attrib, int $total) : string
    {
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmcountdisplay';
        }

        $this->rc->output->add_gui_object('countdisplay', $attrib['id']);

        $content =  $this->rc->action != 'show' ? $this->get_historycount_text($total) : $this->rc->gettext('loading');

        return html::span($attrib, $content);
    }

    private function get_historycount_text($count = null, $page = null) : string
    {
        if ($page === null) {
            $page = intval(filter_input(INPUT_GET, "_page", FILTER_SANITIZE_NUMBER_INT) ?? 1);
            $page = max(1, $page);
        }

        $page_size = 20;
        $start_msg = ($page-1) * $page_size + 1;
        $max       = $count;

        if (!$max) {
            $out = $this->gettext('no_history');
        }
        else {
            $out = $this->gettext([
                'name' => 'history_from_to_of',
                'vars' => [
                    'from'  => $start_msg,
                    'to'    => min($max, $start_msg + $page_size - 1),
                    'count' => $max
                ]
            ]);
        }

        return rcube::Q($out);
    }
}