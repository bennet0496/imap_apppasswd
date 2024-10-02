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

$labels['imap_apppasswd'] = "Anwendungsspezifische Kennwörter";

$labels['last_used'] = "Zuletzt benutzt";
$labels['never_used'] = "noch nie verwendet";
$labels['last_used_from'] = "von";
$labels['created'] = "Erstellt";
$labels['unnamed_app'] = "Unbenannte Anwendung";
$labels['unknown_location'] = "Unbekannter Standort";
$labels['delete'] = "Löschen";
$labels['delete_all'] = "Alle Löschen";
$labels['confirm_delete_single'] = "Bist du sicher, dass du das anwendungsspezifische Kennwort \"%password%\" löschen willst? Diese Aktion kann nicht rückgängig gemacht werden.";
$labels['confirm_delete_all'] = "Bist du sicher, dass du ALLE anwendungsspezifischen Kennwörter löschen willst? Diese Aktion kann nicht rückgängig gemacht werden.";
$labels['add'] = "Hinzufügen";
$labels['copied'] = "Kennwort in Zwischenablage kopiert";
$labels['copy_failed_check_perms'] = "Fehler beim kopieren des Kennwortes, bitte prüfe die Browserbrechtigungen";
$labels['edit'] = "Bearbeiten";
$labels['done'] = 'Fertig';
$labels['ok'] = 'OK';

$labels['no_passwords'] = 'Es wurden noch keine Anwendungsspezifischen Kennworte erstellt. Du kannst durch klicken des Hinzufügen Knopfes eines erstellen.';

$labels['apppw_deleted_success'] = 'Anwendungsspezifisches Kennwort erfolgreich gelöscht';
$labels['apppw_deleted_error'] = 'Fehler beim löschen des Anwendungsspezifischen Kennwortes, bitte versuche es später erneut';
$labels['apppw_add_error'] = 'Fehler beim hinzufügen eines Anwendungsspezifischen Kennwortes, bitte versuche es später erneut';
$labels['apppw_rename_error'] = 'Fehler beim umbennenen des Anwendungsspezifischen Kennwortes, bitte versuche es später erneut';

$labels['years_ago'] = "vor %s Jahr(en)";
$labels['months_ago'] = "vor %s Monat(en)";
$labels['days_ago'] = "vor %s Tag(en)";
$labels['hours_ago'] = "vor %s Stunde(n)";
$labels['minutes_ago'] = "vor %s Minute(n)";
$labels['just_now'] = "gerade Ebend";

$labels['page_allows_password_creation_for_clients'] = <<<EOT
Auf dieser Seite können Sie anwendungsspezifische Kennwörter für ihre E-mail Anwendungen erstellen. Nach der Erstellung
müssen Sie dieses in der Anwendung eingeben oder einfügen. Das Kennwort wird nur einmal angezeit. Jedoch, müssen und
sollen Sie sich dieses Kennwort nicht merken oder notieren. Wenn Sie ein weiteres Kennwort für eine weitere Anwendung benötigen,
können Sie einfach ein neues erstellen; und das beliebig oft.
EOT;

$labels['use_the_following_settings'] = "Nutze die folgenden Einstellungen für deine Anwendung";
$labels['server'] = "Server";
$labels['port'] = "Port";
$labels['protocol'] = "Verbindungssicherheit";
$labels['password_method'] = "Authentifizierungs Methode";

$labels['imap_setting'] = "Eingehende (IMAP) Einstellungen";
$labels['smtp_setting'] = "Ausgehenede (SMTP) Einstellungen";

$labels["show_full_history"] = "Zeige Zugriffshistorie";
$labels["timestamp"] = "Zeitstempel";
$labels["service"] = "Dienst";
$labels["src_ip"] = "Zugriffs-IP";
$labels["src_rdns"] = "Zugriffsadresse";
$labels["src_loc"] = "Zugriffsort";
$labels["src_isp"] = "Zugriffsanschlussinhaber";
$labels["history_for"] = 'Zugriffshistorie für $password';
$labels["no_history"] = "Keine Zugriffe";
$labels["history_from_to_of"] = 'Logeinträge $from bis $to von $count';

$labels["account_excluded"] = "Dein Account ist von der Nutzung von Anwendungsspezifische Kennwörter ausgeschlossen oder befreit";