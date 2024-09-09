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

        $this->log = new \bennetcc\Log(IMAP_APPPW_LOG_FILE, IMAP_APPPW_PREFIX, $this->rc->config->get(__('log_level'), \bennetcc\LogLevel::WARNING));

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
            $this->register_action('plugin.imap_apppasswd_remove', [$this, 'remove_password']);
            $this->register_action('plugin.imap_apppasswd_delete_all', [$this, 'delete_all']);
            $this->register_action('plugin.imap_apppasswd_add', [$this, 'add_password']);
            $this->register_action('plugin.imap_apppasswd_rename', [$this, 'rename_password']);
        }
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
//        $this->register_handler('plugin.body', [$this, 'settingshtml']);
        $this->register_handler('imap_apppasswd.apppw_list', [$this, 'apppw_listhtml']);

        $this->rc->output->set_pagetitle($this->gettext('imap_apppasswd'));
//        $this->rc->output->send('plugin');
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

        $username_tmpl = $this->rc->config->get(__("imap_username"));

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
                                \html::span(['title' => $row['src_ip']],
                                    (empty($row['last_used_src_rdns']) || $row['last_used_src_rdns'] == "<>" ? $row['last_used_src_ip'] : $row['last_used_src_rdns']).(empty($row['last_used_src_isp']) ? "" : " (".$row['last_used_src_isp'].")"))).
                        (empty($row['src_loc']) ? "" : \html::span(['class' => 'apppw_location'], $row['src_loc'])).
                        \html::span(['class' => 'apppw_created', 'title' => $created->format(DATE_RFC822)], $this->gettext('created')." ".$this->format_diff($now->diff($created))).
                        \html::a(['class' => 'apppw_delete', 'onclick' => 'apppw_remove('.$row['id'].')'], $this->gettext("delete"))
            );
        }

        return $html;
    }

    public function remove_password(mixed $attr = null) : void
    {
        $this->log->debug($attr,$_REQUEST);


        // We need uid here to protect from users delete each others passwords
        $s = $this->db->prepare("DELETE FROM app_passwords WHERE id = :id AND uid = :uid;");

        $s->bindValue("id", $_REQUEST['id'], \PDO::PARAM_INT);
        $s->bindValue("uid", $this->resolve_username());

        if($s->execute()) {
            $this->rc->output->command("plugin.apppw_remove_from_list", ["id" => $_REQUEST['id']]);
            $this->rc->output->show_message($this->gettext("apppw_deleted_success"));
        } else {
            $this->rc->output->show_message($this->gettext("apppw_deleted_error"), "error");
        }
    }

    public function add_password() : void
    {
        try {
            $rand = random_bytes($this->rc->config->get('imap_apppasswd_length', 16));
            $salt = base64_encode(random_bytes(16));
        } catch (\Random\RandomException $e) {
            $rand = openssl_random_pseudo_bytes($this->rc->config->get('imap_apppasswd_length', 16));
            $salt = openssl_random_pseudo_bytes(16);
        }

        //Map random chars to a-zA-Z0-9
        //TODO: we might want to consider excluding vowels to prevent accidental generation of words or even slurs
        $pw = implode("-", str_split(implode(array_map(function($c) {
            $i = ord($c);
            $i = $i % (26 + 26 + 10);

            if($i < 10) {
                return chr($i + 48);
            } else if ($i < 36) {
                return chr($i - 10 + 65);
            } else {
                return chr($i - 10 - 26 + 97);
            }
        }, str_split($rand))), $this->rc->config->get('imap_apppasswd_chunksize', 4)));

        $hash = "{CRYPT}".crypt($pw, "$6$".$salt);
        $this->log->debug($hash);

        $s = $this->db->prepare("INSERT INTO app_passwords (uid, password, created) VALUES (:uid, :password, UTC_TIMESTAMP());");

        $s->bindValue("uid", $this->resolve_username());
        $s->bindValue("password", $hash);

        if($s->execute()) {
            $this->rc->output->command("plugin.apppw_add", ["id" => $this->db->lastInsertId(),"passwd" => $pw]);
        } else {
            $this->rc->output->show_message($this->gettext("apppw_add_error"), "error");
        }

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
}