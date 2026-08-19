<?php
/*
 * Copyright (c) 2024-2026. Bennet Becker <dev@bennet.cc>
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

use function bennetcc\imap_apppasswd\__;
use function bennetcc\imap_apppasswd\chunk;
use function bennetcc\imap_apppasswd\random_from_alphabet;
use const bennetcc\imap_apppasswd\IMAP_APPPW_EDIT_BTN;

require_once dirname(__FILE__)."/../util.php";

trait ManagePasswords {
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
            fn ($attrib) => \html::quote(\rcube_utils::idn_to_utf8($this->resolve_username())));

        $this->register_handler('imap_apppasswd.smtp_username', [$this, 'resolve_username']);

        //Page title
        $this->rc->output->set_pagetitle($this->gettext('imap_apppasswd'));

        //Include our style and scripts
        $this->include_stylesheet("imap_apppasswd.css");
        $this->include_script("imap_apppasswd.js");

        $this->rc->output->set_env(__("comment_length"), $this->rc->config->get(__('comment_length'), 64));

        if ($this->is_disabled()) {
            $this->rc->output->send('imap_apppasswd.disabled');
        } else {
            //send the main settings template
            $this->rc->output->send('imap_apppasswd.apppasswords');
        }
    }



    /**
     * Action handler for `plugin.imap_apppasswd_add`
     * Called from JS via AJAX to create a new password
     * @return void
     * @throws \Exception
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
            \rcube::raise_error([
                'file'    => __FILE__,
                'line'    => __LINE__,
                'message' => 'miss-configured plugin '.__CLASS__
            ], true, true);
        }

        try {
            //Map random chars to a-zA-Z0-9
            //TODO: we might want to consider excluding vowels to prevent accidental generation of words or even slurs
            $password = chunk(
                random_from_alphabet($desired_len, "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz", $this->log),
                $this->rc->config->get(__('chunksize'), 4));

            // Map salt to abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./
            // because python crashes otherwise: ValueError: invalid characters in sha512_crypt salt
            // https://stackoverflow.com/a/71120618
            $mapped_salt = random_from_alphabet(16, "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz", $this->log);

            if (strlen($password) < $desired_len || strlen($mapped_salt) < 16) {
                $this->log->error("random_bytes and/or openssl return no random value. Please check your system configuration");
                \rcube::raise_error([
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => 'randomness error'
                ], true, true);
            }

            //hash with SHA512 and default settings
            $hash = "{CRYPT}" . crypt($password, "$6$" . $mapped_salt);
            $this->log->debug($hash);

            //insert without name
            $s = $this->db->prepare("INSERT INTO app_passwords (uid, password, created) VALUES (:uid, :password, UTC_TIMESTAMP());");

            if (!$s) {
                \rcube::raise_error([
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => 'database error'
                ], true, true);
            }

            $s->bindValue("uid", $this->resolve_username());
            $s->bindValue("password", $hash);

            if ($s->execute()) {
                $this->rc->output->command("plugin.imap_apppasswd.add", ["id" => $this->db->lastInsertId(), "passwd" => $password]);
            } else {
                $this->rc->output->show_message($this->gettext("apppw_add_error"), "error");
            }

            $this->log->info($this->rc->user->get_username() . " added an app password");
        } catch (\Exception) {
            $this->log->error("random_bytes and/or openssl return no random value. Please check your system configuration");
            \rcube::raise_error([
                'file'    => __FILE__,
                'line'    => __LINE__,
                'message' => 'randomness error'
            ], true, true);
        }

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

        if (strlen($name) > $this->rc->config->get(__('comment_length'), 64)) {
            $this->log->info($this->rc->user->get_username() . " entered too long comment for " . $id);
            $this->rc->output->show_message(
                $this->gettext([
                    "name" => "apppw_rename_error_too_long",
                    "vars" => [
                        "max" => $this->rc->config->get(__('comment_length'), 64)]
                ]), "error");
            return;
        }

        $s = $this->db->prepare("SELECT * FROM app_passwords WHERE id = :id AND uid = :uid;");
        $s->bindValue("id", $id, \PDO::PARAM_INT);
        $s->bindValue("uid", $this->resolve_username());
        $s->execute();

        $result = $s->fetch(\PDO::FETCH_ASSOC);
        $password_len = $this->rc->config->get(__('length'), 16);
        $chunksize = $this->rc->config->get(__('chunksize'), 4);
        $chunks = ceil($password_len / $chunksize);
        for($offset = 0; $offset <= (strlen($name) - ($password_len + $chunks - 1)); $offset++) {
            $check_part = substr($name, $offset, $password_len + $chunks - 1);
            $this->log->trace($password_len, $chunks, $password_len + $chunks - 1, $offset, $check_part);
            // User Pasted the password as part of password name
            if (password_verify($check_part, substr($result['password'], strlen("{CRYPT}")))) {
                $this->log->info($this->rc->user->get_username() . " pasted the password as name for " . $id);
                $this->rc->output->show_message($this->gettext("apppw_rename_error_comment_is_password"), "error");
                return;
            }
        }

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
     * List of passwords in the Settings tab content.
     * @return string HTML List
     * @throws \DateMalformedStringException
     */
    public function object_handler_apppw_list(): string
    {
        //get app password for user
        $s = $this->db->prepare("SELECT * FROM app_passwords WHERE uid = :uid AND deleted IS NULL;");
        $user_name = $this->resolve_username();

        $s->bindValue("uid", $user_name);
        $s->execute();

        //add and optionally show the 'no_password' element
        $html = \html::span(['class' => 'no_passwords ' . ($s->rowCount() == 0 ? '' : 'hidden')], $this->gettext('no_passwords'));

        while ($row = $s->fetch(\PDO::FETCH_ASSOC)) {
            $this->log->trace($row);
            $now = new \DateTimeImmutable("now", new \DateTimeZone("UTC"));

            $s_log = $this->db->prepare("SELECT * FROM log WHERE pwid = :pwid ORDER BY id DESC LIMIT 1;");
            $s_log->bindValue("pwid", $row['id'], \PDO::PARAM_INT);
            $s_log->execute();

            $log_row = $s_log->fetch(\PDO::FETCH_ASSOC);

            $this->log->trace($log_row);

            $last_used = new \DateTimeImmutable($log_row['timestamp'] ?? "01-01-1970 00:00:00.0000", new \DateTimeZone("UTC"));
            $created = new \DateTimeImmutable($row['created'] ?? "01-01-1970 00:00:00.0000", new \DateTimeZone("UTC"));

            $this->log->trace($now, $last_used, $created);

            //ugly but it works...
            $html .= \html::div(['class' => 'apppw_entry', 'data-apppw-id' => $row['id']],
                \html::span(['class' => 'apppw_title'],
                    \html::span(['class' => 'apppw_title_text'], ($row['comment'] ?? $this->gettext('unnamed_app'))) .
                    \html::a(['class' => 'apppw_title_edit', 'title' => $this->gettext('edit'), 'onclick' => 'return rcmail.command("plugin.imap_apppasswd.rename",' . $row['id'] . ',this,event)'], IMAP_APPPW_EDIT_BTN)) .
                \html::span(['class' => 'apppw_lastused', 'title' => $log_row['timestamp'] == null ? $this->gettext('never_used') : $last_used->format(DATE_RFC822)],
                    $log_row['timestamp'] == null ?
                        $this->gettext('never_used') :
                        $this->gettext('last_used') . " " . $this->format_diff($now->diff($last_used)) . " " . $this->gettext('last_used_from') . " " .
                        \html::span(['title' => $log_row['src_ip'] ?? ""],
                            (empty($log_row['src_rdns']) || $log_row['src_rdns'] == "<>" ? $log_row['src_ip'] : $log_row['src_rdns']) . (empty($log_row['src_isp']) ? "" : " (" . $log_row['src_isp'] . ")"))) .
                \html::span(['class' => 'apppw_location'], (empty($log_row['src_loc']) ? $this->gettext('unknown_location') : $row['src_loc'])) .
                \html::span(['class' => 'apppw_created', 'title' => $created->format(DATE_RFC822)], $this->gettext('created') . " " . $this->format_diff($now->diff($created))) .
                \html::a(['class' => 'apppw_delete', 'href' => $this->rc->url(["_action" => "plugin.imap_apppasswd.history", "_pwid" => $row['id']])], $this->gettext("show_full_history")) .
                \html::a(['class' => 'apppw_delete', 'onclick' => 'return rcmail.command("plugin.imap_apppasswd.remove",' . $row['id'] . ',this,event)'], $this->gettext("delete"))
            );
        }

        return $html;
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
}