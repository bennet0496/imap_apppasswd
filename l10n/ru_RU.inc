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

$labels['imap_apppasswd'] = 'Пароли приложений';

$labels['last_used'] = 'Последнее использование';
$labels['never_used'] = 'Никогда не использовался';
$labels['last_used_from'] = 'с';
$labels['created'] = 'Создано';
$labels['unnamed_app'] = 'Безымянное приложение';
$labels['unknown_location'] = 'Неизвестное местоположение';
$labels['delete'] = 'Удалить';
$labels['delete_all'] = 'Удалить все';
$labels['confirm_delete_single'] = 'Вы уверены, что хотите удалить пароль "$password"? Эта операция не может быть отменена.';
$labels['confirm_delete_all'] = 'Вы уверены, что хотите удалить ВСЕ ваши пароли приложений? Эта операция не может быть отменена.';
$labels['add'] = 'Добавить';
$labels['copied'] = 'Пароль скопирован в буфер обмена';
$labels['copy_failed_check_perms'] = 'Не удалось скопировать пароль, проверьте разрешения браузера';
$labels['edit'] = 'Редактировать';
$labels['done'] = 'Готово';
$labels['ok'] = 'OK';

$labels['no_passwords'] = 'Пароли приложений еще не созданы. Вы можете создать один, нажав кнопку Добавить ниже.';

$labels['apppw_deleted_success'] = 'Пароль приложения успешно удален';
$labels['apppw_deleted_error'] = 'Ошибка при удалении пароля приложения, попробуйте позже';
$labels['apppw_add_error'] = 'Ошибка при добавлении пароля, попробуйте позже';
$labels['apppw_rename_error'] = 'Ошибка при переименовании пароля, попробуйте позже';
$labels['apppw_rename_error_comment_is_password'] = 'Вы не можете использовать сам пароль в качестве имени пароля';
$labels['apppw_rename_error_too_long'] = 'Имя пароля слишком длинное. Используйте не более $max символов!';

$labels['years_ago'] = '$count год(а/лет) назад';
$labels['months_ago'] = '$count месяц(ев) назад';
$labels['days_ago'] = '$count день(дней) назад';
$labels['hours_ago'] = '$count час(ов) назад';
$labels['minutes_ago'] = '$count минут(ы) назад';
$labels['just_now'] = 'только что';

$labels['page_allows_password_creation_for_clients'] = <<<EOT
Эта страница позволяет создавать пароли приложений для ваших почтовых клиентов. После создания пароля введите или скопируйте его в ваше почтовое приложение. Пароль будет отображаться только один раз. Тем не менее, вам не нужно и не следует запоминать или записывать пароль. Если вы хотите добавить дополнительный клиент, просто создайте новый пароль; вы можете создать столько, сколько хотите.
EOT;

$labels['use_the_following_settings'] = 'Используйте следующие настройки для вашего клиента';
$labels['server'] = 'Сервер';
$labels['port'] = 'Порт';
$labels['protocol'] = 'Безопасность соединения';
$labels['password_method'] = 'Метод аутентификации';

$labels['imap_setting'] = 'Параметры входящей почты (IMAP)';
$labels['smtp_setting'] = 'Параметры исходящей почты (SMTP)';

$labels['show_full_history'] = 'Показать полную историю доступа';
$labels['timestamp'] = 'Временная метка';
$labels['service'] = 'Сервис';
$labels['src_ip'] = 'IP-адрес доступа';
$labels['src_rdns'] = 'Имя хоста доступа';
$labels['src_loc'] = 'Местоположение доступа';
$labels['src_isp'] = 'Провайдер доступа';
$labels['history_for'] = 'История доступа для $password';
$labels['no_history'] = 'Нет истории';
$labels['history_from_to_of'] = 'Записи журнала с $from по $to из $count';

$labels['account_excluded'] = 'Ваш аккаунт исключен или освобожден от использования паролей приложений';