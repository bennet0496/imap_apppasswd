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

$labels['imap_apppasswd'] = 'Alkalmazásjelszók';

$labels['last_used'] = 'Utolsó használat';
$labels['never_used'] = 'Soha nem használt';
$labels['last_used_from'] = 'forrásból';
$labels['created'] = 'Létrehozva';
$labels['unnamed_app'] = 'Névtelen alkalmazás';
$labels['unknown_location'] = 'Ismeretlen hely';
$labels['delete'] = 'Törlés';
$labels['delete_all'] = 'Összes törlése';
$labels['confirm_delete_single'] = 'Biztosan törölni szeretnéd a "$password" jelszót? Ez a művelet nem vonható vissza.';
$labels['confirm_delete_all'] = 'Biztosan törölni szeretnéd az összes alkalmazásjelszót? Ez a művelet nem vonható vissza.';
$labels['add'] = 'Hozzáadás';
$labels['copied'] = 'Jelszó vágólapra másolva';
$labels['copy_failed_check_perms'] = 'A jelszó másolása sikertelen, ellenőrizd a böngésző engedélyeit';
$labels['edit'] = 'Szerkesztés';
$labels['done'] = 'Kész';
$labels['ok'] = 'OK';

$labels['no_passwords'] = 'Még nincs létrehozva alkalmazásjelszó. A Hozzáadás gombra kattintva hozhatsz létre egyet az alábbiakban.';

$labels['apppw_deleted_success'] = 'Az alkalmazásjelszó sikeresen törölve';
$labels['apppw_deleted_error'] = 'Hiba történt az alkalmazásjelszó törlésekor, próbáld újra később';
$labels['apppw_add_error'] = 'Hiba történt a jelszó hozzáadásakor, próbáld újra később';
$labels['apppw_rename_error'] = 'Hiba történt a jelszó átnevezésénél, próbáld újra később';
$labels['apppw_rename_error_comment_is_password'] = 'A jelszót saját magát nem használhatod jelszónévnek';
$labels['apppw_rename_error_too_long'] = 'A jelszónév túl hosszú. Kérlek, használj $max karaktert vagy kevesebbet!';

$labels['years_ago'] = '$count évvel ezelőtt';
$labels['months_ago'] = '$count hónappal ezelőtt';
$labels['days_ago'] = '$count nappal ezelőtt';
$labels['hours_ago'] = '$count órával ezelőtt';
$labels['minutes_ago'] = '$count perccel ezelőtt';
$labels['just_now'] = 'épp most';

$labels['page_allows_password_creation_for_clients'] = <<<EOT
Ez a oldal lehetővé teszi az alkalmazásjelszavak létrehozását e-mail kliensek számára. A jelszó létrehozása után másold vagy írd be az e-mail alkalmazásodba. A jelszó csak egyszer jelenik meg. Ugyanakkor nem kell és nem is javasolt megjegyezni vagy feljegyezni a jelszót. Ha további klienseket szeretnél hozzáadni, egyszerűen hozz létre egy új jelszót; akárhányat létrehozhatsz.
EOT;

$labels['use_the_following_settings'] = 'Használd az alábbi beállításokat a kliensedhez';
$labels['server'] = 'Kiszolgáló';
$labels['port'] = 'Port';
$labels['protocol'] = 'Kapcsolatbiztonság';
$labels['password_method'] = 'Hitelesítési módszer';

$labels['imap_setting'] = 'Bejövő (IMAP) beállítások';
$labels['smtp_setting'] = 'Kimenő (SMTP) beállítások';

$labels['show_full_history'] = 'Teljes hozzáférési előzmények megjelenítése';
$labels['timestamp'] = 'Időbélyeg';
$labels['service'] = 'Szolgáltatás';
$labels['src_ip'] = 'Hozzáférési IP';
$labels['src_rdns'] = 'Hozzáférési gazda név';
$labels['src_loc'] = 'Hozzáférési hely';
$labels['src_isp'] = 'Hozzáférési szolgáltató';
$labels['history_for'] = '$password hozzáférési előzményei';
$labels['no_history'] = 'Nincs előzmény';
$labels['history_from_to_of'] = '$from és $to közötti bejegyzések, összesen $count';

$labels['account_excluded'] = 'Fiókod kizárva van az alkalmazásjelszavak használatából';