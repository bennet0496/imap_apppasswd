<?php
/*
 * Copyright (c) 2026. Bennet Becker <dev@bennet.cc>
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

$labels['machinetranslated'] = 'Machine Translated using Qwen 3 30B A3B Instruct 2507. In case of errors please open an issue at <a href="https://github.com/bennet0496/imap_apppasswd">bennet0496/imap_apppasswd</a>';

$labels['imap_apppasswd'] = 'App-wachtwoorden';

$labels['last_used'] = 'Laatst gebruikt';
$labels['never_used'] = 'Nooit gebruikt';
$labels['last_used_from'] = 'vanaf';
$labels['created'] = 'Aangemaakt';
$labels['unnamed_app'] = 'Naamloos app';
$labels['unknown_location'] = 'Onbekende locatie';
$labels['delete'] = 'Verwijderen';
$labels['delete_all'] = 'Alle verwijderen';
$labels['confirm_delete_single'] = 'Weet u zeker dat u het wachtwoord "$password" wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.';
$labels['confirm_delete_all'] = 'Weet u zeker dat u ALLE app-wachtwoorden wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.';
$labels['add'] = 'Toevoegen';
$labels['copied'] = 'Wachtwoord gekopieerd naar klembord';
$labels['copy_failed_check_perms'] = 'Kopiëren mislukt, controleer de browsermachtigingen';
$labels['edit'] = 'Bewerken';
$labels['done'] = 'Klaar';
$labels['ok'] = 'OK';

$labels['no_passwords'] = 'Er zijn nog geen app-wachtwoorden aangemaakt. U kunt er een aanmaken door op de knop Toevoegen te klikken.';

$labels['apppw_deleted_success'] = 'App-wachtwoord succesvol verwijderd';
$labels['apppw_deleted_error'] = 'Fout bij verwijderen van app-wachtwoord, probeer het later opnieuw';
$labels['apppw_add_error'] = 'Fout bij toevoegen van wachtwoord, probeer het later opnieuw';
$labels['apppw_rename_error'] = 'Fout bij hernoemen van wachtwoord, probeer het later opnieuw';
$labels['apppw_rename_error_comment_is_password'] = 'U kunt het wachtwoord zelf niet gebruiken als naam voor het wachtwoord';
$labels['apppw_rename_error_too_long'] = 'De wachtwoordnaam is te lang. Gebruik maximaal $max tekens!';

$labels['years_ago'] = '$count jaar geleden';
$labels['months_ago'] = '$count maand(en) geleden';
$labels['days_ago'] = '$count dag(en) geleden';
$labels['hours_ago'] = '$count uur(s) geleden';
$labels['minutes_ago'] = '$count minuut(en) geleden';
$labels['just_now'] = 'net nu';

$labels['page_allows_password_creation_for_clients'] = <<<EOT
Deze pagina stelt u in staat om app-wachtwoorden aan te maken voor uw e-mailclients. Nadat u een wachtwoord hebt aangemaakt, typ of kopieer het in uw e-mailprogramma. Het wachtwoord wordt slechts één keer weergegeven. U hoeft het wachtwoord echter niet te onthouden of op te schrijven. Als u een extra client wilt toevoegen, maakt u gewoon een nieuw wachtwoord aan; u kunt zoveel maken als u wilt.
EOT;

$labels['use_the_following_settings'] = 'Gebruik de volgende instellingen voor uw client';
$labels['server'] = 'Server';
$labels['port'] = 'Poort';
$labels['protocol'] = 'Verbindingsbeveiliging';
$labels['password_method'] = 'Authenticatiemethode';

$labels['imap_setting'] = 'Inkomende (IMAP) instellingen';
$labels['smtp_setting'] = 'Uitgaande (SMTP) instellingen';

$labels['show_full_history'] = 'Toon volledige geschiedenis';
$labels['timestamp'] = 'Tijdstempel';
$labels['service'] = 'Service';
$labels['src_ip'] = 'Toegangs-IP';
$labels['src_rdns'] = 'Toegangs-hostnaam';
$labels['src_loc'] = 'Toegangslocatie';
$labels['src_isp'] = 'Toegangs-serviceprovider';
$labels['history_for'] = 'Toegangsgeschiedenis voor $password';
$labels['no_history'] = 'Geen geschiedenis';
$labels['history_from_to_of'] = 'Logvermeldingen van $from tot $to van $count';

$labels['account_excluded'] = 'Uw account is uitgesloten of vrijgesteld van het gebruik van app-wachtwoorden';