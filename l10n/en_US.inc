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

$labels['imap_apppasswd'] = "App Passwords";

$labels['last_used'] = "Last used";
$labels['never_used'] = "Never used";
$labels['last_used_from'] = "from";
$labels['created'] = "Created";
$labels['unnamed_app'] = "Unnamed App";
$labels['unknown_location'] = "Unknown location";
$labels['delete'] = "Delete";
$labels['delete_all'] = "Delete All";
$labels['confirm_delete_single'] = "Are you sure you want to delete the password \"%password%\"? This action can not be undone.";
$labels['confirm_delete_all'] = "Are you sure you want to delete ALL of your application passwords? This action can not be undone.";
$labels['add'] = "Add";
$labels['copied'] = "Password copied to clipboard";
$labels['copy_failed_check_perms'] = "Failed copying password, check browser permissions";
$labels['edit'] = "Edit";
$labels['done'] = 'Done';
$labels['ok'] = 'OK';

$labels['no_passwords'] = 'There are no App passwords created yet. You can create one below by clicking the Add button.';

$labels['apppw_deleted_success'] = 'App Password successfully deleted';
$labels['apppw_deleted_error'] = 'Error deleting App Password, please try again later';
$labels['apppw_add_error'] = 'Error adding password, please try again later';
$labels['apppw_rename_error'] = 'Error renaming password, please try again later';

$labels['years_ago'] = "%s year(s) ago";
$labels['months_ago'] = "%s month(s) ago";
$labels['days_ago'] = "%s day(s) ago";
$labels['hours_ago'] = "%s hour(s) ago";
$labels['minutes_ago'] = "%s minute(s) ago";
$labels['just_now'] = "just now";

$labels['page_allows_password_creation_for_clients'] = <<<EOT
This page allows to create App Passwords for your Mail clients. After creating a password, type or copy it into your 
email application. The password will only be shown once. However, you are neither supposed nor required to remember 
or take note of the password. If you want to add an additional client, just create a new password; you can create as 
many as you want.
EOT;

$labels['use_the_following_settings'] = "Use the following settings for your client";
$labels['server'] = "Server";
$labels['port'] = "Port";
$labels['protocol'] = "Connection Security";
$labels['password_method'] = "Authentication Method";

$labels['imap_setting'] = "Incoming (IMAP) Settings";
$labels['smtp_setting'] = "Outgoing (SMTP) Settings";


$labels["show_full_history"] = "Show access history";
$labels["timestamp"] = "Timestamp";
$labels["service"] = "Service";
$labels["src_ip"] = "Access IP";
$labels["src_rdns"] = "Access Hostname";
$labels["src_loc"] = "Access Location";
$labels["src_isp"] = "Access Service Provider";
$labels["history_for"] = 'Access history for $password';
$labels["no_history"] = "No history";
$labels["history_from_to_of"] = 'Log entries from $from to $to of $count';

$labels["account_excluded"] = "Your account is excluded or exempt from using app passwords";
