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

class imap_apppasswd extends \rcube_plugin
{
    public $task = 'settings';
    private string $log_table = 'userlogins';
    private \rcmail $rc;
    private \PDO $db;

    function init(): void
    {
        $this->load_config('config.inc.php.dist');
        $this->load_config();
        $this->add_texts('l10n/', true);
        $this->rc = \rcmail::get_instance();

        if ($this->rc->task == 'settings') {
            $this->add_hook('settings_actions', [$this, 'settings_actions']);
            $this->register_action('plugin.imap_apppasswd', [$this, 'show_settings']);
            $this->register_action('plugin.imap_apppasswd_remove', [$this, 'remove_password']);
            $this->register_action('plugin.imap_apppasswd_delete_all', [$this, 'delete_all']);
            $this->register_action('plugin.imap_apppasswd_add', [$this, 'add_password']);
            $this->register_action('plugin.imap_apppasswd_rename', [$this, 'rename_password']);

            $this->db = new PDO("mysql:host=db.example.com;dbname=mail", "roundcube", "Eemeep2cheil4dee5ahShohquo5EC6ko"); //\mysqli("db.example.com", "roundcube", "Eemeep2cheil4dee5ahShohquo5EC6ko", "mail");
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
        $this->register_handler('plugin.body', [$this, 'settingshtml']);
        $this->rc->output->set_pagetitle($this->gettext('imap_apppasswd'));
        $this->rc->output->send('plugin');
    }

    /**
     * Settings tab content.
     */
    public function settingshtml(): string
    {
        $this->include_stylesheet("imap_apppasswd.css");
        $this->include_script("imap_apppasswd.js");

//        $this->rc->db

        $s = $this->db->prepare("SELECT DISTINCT pws.*, pwid, src_ip, src_rdns, src_loc, src_isp, timestamp as last_used
            FROM (SELECT * FROM app_passwords WHERE uid = :uid) pws
            LEFT JOIN (WITH s1 AS (
                SELECT *, RANK() OVER (PARTITION BY log.pwid ORDER BY log.timestamp DESC) AS `Rank` FROM log
            ) SELECT * FROM s1 WHERE `Rank` = 1 ORDER BY timestamp) l ON l.pwid = pws.id ORDER BY created;");
        $user_name = explode("@",$this->rc->get_user_name())[0];
//        \rcmail::write_log('imap_apppw', "username ".$this->rc->get_user_name()." ".$this->rc->get_user_id());
        $s->bindValue("uid", $user_name);
        $s->execute();
//        $r = $s->get_result();

        $table = new \html_table([]);
        $table->add_row();
        $table->add_header([], "");
        $table->add_header([], $this->gettext("imap_setting"));
        $table->add_header([], $this->gettext("smtp_setting"));

        $table->add_row(['class' => 'small_row']);
        $table->add([], "");
        $table->add(['colspan' => 2, 'class' => 'small_label'], $this->gettext("server"));
        $table->add_row();
        $table->add([], $this->gettext("server"));
        $table->add([], $this->rc->config->get("advertised_client_imap_host"));
        $table->add([], $this->rc->config->get("advertised_client_smtp_host"));
        $table->add_row(['class' => 'small_row']);
        $table->add([], "");
        $table->add(['colspan' => 2, 'class' => 'small_label'], $this->gettext("port"));
        $table->add_row();
        $table->add([], $this->gettext("port"));
        $table->add([], $this->rc->config->get("advertised_client_imap_port"));
        $table->add([], $this->rc->config->get("advertised_client_smtp_port"));
        $table->add_row(['class' => 'small_row']);
        $table->add([], "");
        $table->add(['colspan' => 2, 'class' => 'small_label'], $this->gettext("protocol"));
        $table->add_row();
        $table->add([], $this->gettext("protocol"));
        $table->add([], $this->rc->config->get("advertised_client_imap_protocol"));
        $table->add([], $this->rc->config->get("advertised_client_smtp_protocol"));
        $table->add_row(['class' => 'small_row']);
        $table->add([], "");
        $table->add(['colspan' => 2, 'class' => 'small_label'], $this->gettext("password_method"));
        $table->add_row();
        $table->add([], $this->gettext("password_method"));
        $table->add([], $this->rc->config->get("advertised_client_imap_password_method"));
        $table->add([], $this->rc->config->get("advertised_client_smtp_password_method"));

        $html = "";

        while ($row = $s->fetch(\PDO::FETCH_ASSOC)) {
            $now = new DateTimeImmutable();
            $lu = new DateTimeImmutable($row['last_used']);
            $ct = new DateTimeImmutable($row['created']);

            $now->diff($lu)->format("%d days ago");

            $html .= \html::div(['class' => 'apppw_entry', 'data-apppw-id' => $row['id']],
                \html::span(['class' => 'apppw_title'],
                    \html::span(['class' => 'apppw_title_text'], ($row['comment'] ?? $this->gettext('unnamed_app'))).
                            \html::a(['class' => 'apppw_title_edit', 'title' => $this->gettext('edit'), 'onclick' => 'apppw_edit('.$row['id'].')'], '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/></svg>')).
                        \html::span(['class' => 'apppw_lastused', 'title' => $row['last_used'] == null ? $this->gettext('never_used') : $lu->format(DATE_RFC822)],
                            $row['last_used'] == null ?
                            $this->gettext('never_used') :
                            $this->gettext('last_used')." ".$this->format_diff($now->diff($lu))." ".$this->gettext('last_used_from')." ".
                                \html::span(['title' => $row['src_ip']],
                                    (empty($row['src_rdns']) ? $row['src_ip'] : $row['src_rdns']).(empty($row['src_isp']) ? "" : " (".$row['src_isp'].")"))).
                        (empty($row['src_loc']) ? "" : \html::span(['class' => 'apppw_location'], $row['src_loc'])).
                        \html::span(['class' => 'apppw_created', 'title' => $ct->format(DATE_RFC822)], $this->gettext('created')." ".$this->format_diff($now->diff($ct))).
                        \html::a(['class' => 'apppw_delete', 'onclick' => 'apppw_remove('.$row['id'].')'], $this->gettext("delete"))
            );
        }

//        $this->inc
        return \html::div(["class" => "apppw_info"],
            \html::p([], $this->gettext("page_allows_password_creation_for_clients")).
            \html::p([], $this->gettext("use_the_following_settings")). $table->show()
        ).\html::div(['class' => 'apppw','id' => 'apppw_container'], \html::div(['class' => 'apppw_list','id' => 'apppw_list'],$html).
            \html::div(['class' => 'apppw_actions'],
                (new \html_button(['onclick' => 'apppw_add()', 'class' => 'create']))->show($this->gettext('add')).
                (new \html_button(['onclick' => 'apppw_remove_all()', 'class' => 'delete']))->show($this->gettext('delete_all'))
            ));
    }

    public function remove_password(mixed $attr = null) : void
    {
        rcmail::write_log('imap_apppw', print_r($attr, true));
        rcmail::write_log('imap_apppw', print_r($_REQUEST, true));

        $s = $this->db->prepare("DELETE FROM app_passwords WHERE id = :id;");
//        $s->bind_param("i", $_REQUEST['id']);
        $s->bindValue("id", $_REQUEST['id'], \PDO::PARAM_INT);

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
        rcmail::write_log('imap_apppw', $hash);


        $s = $this->db->prepare("INSERT INTO app_passwords (uid, password) VALUES (:uid, :password);");
//        $s->bind_param("ss", explode("@", $this->rc->get_user_name())[0], $hash);
        $s->bindValue("uid", explode("@", $this->rc->get_user_name())[0]);
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
        $s = $this->db->prepare("UPDATE app_passwords SET comment = :comment WHERE id = :id;");
        $s->bindValue("comment", $name, \PDO::PARAM_STR);
        $s->bindValue("id", $id, \PDO::PARAM_INT);

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