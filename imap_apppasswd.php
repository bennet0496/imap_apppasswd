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


const IMAP_APPPW_PREFIX = "imap_apppasswd";
const IMAP_APPPW_LOG_FILE = "imap_apppw";

use bennetcc\imap_apppasswd\traits\DisableUser;
use bennetcc\imap_apppasswd\traits\ManagePasswords;
use bennetcc\imap_apppasswd\traits\ShowHistory;
use bennetcc\Log;
use bennetcc\imap_apppasswd\traits\FormatTimeDifference;
use bennetcc\imap_apppasswd\traits\ResolveUsername;
use bennetcc\LogLevel;
use function bennetcc\imap_apppasswd\__;

require_once "util.php";
require_once "log.php";
require_once "traits/DisableUser.php";
require_once "traits/ResolveUsername.php";
require_once "traits/FormatTimeDifference.php";
require_once "traits/ShowHistory.php";
require_once "traits/ManagePasswords.php";

class imap_apppasswd extends rcube_plugin
{
    use DisableUser, ResolveUsername, FormatTimeDifference, ShowHistory, ManagePasswords;
    public $task = 'settings';
    private rcmail $rc;
    private PDO $db;
    private Log $log;

    function init(): void
    {
        $this->load_config('config.inc.php.dist');
        $this->load_config();

        $this->add_texts('l10n/', true);
        $this->rc = rcmail::get_instance();

        $this->log = new Log(IMAP_APPPW_LOG_FILE, IMAP_APPPW_PREFIX, $this->rc->config->get(__('log_level'), LogLevel::INFO->value));

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
}