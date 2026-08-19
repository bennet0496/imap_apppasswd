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

$labels['imap_apppasswd'] = '应用密码';

$labels['last_used'] = '上次使用';
$labels['never_used'] = '从未使用';
$labels['last_used_from'] = '来自';
$labels['created'] = '创建于';
$labels['unnamed_app'] = '未命名应用';
$labels['unknown_location'] = '未知位置';
$labels['delete'] = '删除';
$labels['delete_all'] = '全部删除';
$labels['confirm_delete_single'] = '确定要删除密码 "$password" 吗？此操作无法撤销。';
$labels['confirm_delete_all'] = '确定要删除您所有的应用密码吗？此操作无法撤销。';
$labels['add'] = '添加';
$labels['copied'] = '密码已复制到剪贴板';
$labels['copy_failed_check_perms'] = '复制密码失败，请检查浏览器权限';
$labels['edit'] = '编辑';
$labels['done'] = '完成';
$labels['ok'] = '确定';

$labels['no_passwords'] = '暂无创建的应用密码。您可以通过点击下方的“添加”按钮创建一个。';

$labels['apppw_deleted_success'] = '应用密码已成功删除';
$labels['apppw_deleted_error'] = '删除应用密码时出错，请稍后重试';
$labels['apppw_add_error'] = '添加密码时出错，请稍后重试';
$labels['apppw_rename_error'] = '重命名密码时出错，请稍后重试';
$labels['apppw_rename_error_comment_is_password'] = '您不能将密码本身用作密码名称';
$labels['apppw_rename_error_too_long'] = '密码名称过长。请使用不超过 $max 个字符！';

$labels['years_ago'] = '$count 年前';
$labels['months_ago'] = '$count 个月前';
$labels['days_ago'] = '$count 天前';
$labels['hours_ago'] = '$count 小时前';
$labels['minutes_ago'] = '$count 分钟前';
$labels['just_now'] = '刚刚';

$labels['page_allows_password_creation_for_clients'] = <<<EOT
此页面允许您为邮件客户端生成应用密码。创建密码后，请将其输入或复制到您的邮件应用程序中。密码仅显示一次。但您无需也不应记住或记录该密码。如需添加更多客户端，只需生成新的密码即可；您可以创建任意数量的密码。
EOT;

$labels['use_the_following_settings'] = '请在客户端中使用以下设置';
$labels['server'] = '服务器';
$labels['port'] = '端口';
$labels['protocol'] = '连接安全';
$labels['password_method'] = '认证方式';

$labels['imap_setting'] = '接收邮件（IMAP）设置';
$labels['smtp_setting'] = '发送邮件（SMTP）设置';

$labels['show_full_history'] = '显示访问历史';
$labels['timestamp'] = '时间戳';
$labels['service'] = '服务';
$labels['src_ip'] = '访问IP';
$labels['src_rdns'] = '访问主机名';
$labels['src_loc'] = '访问位置';
$labels['src_isp'] = '访问服务提供商';
$labels['history_for'] = '$password 的访问历史';
$labels['no_history'] = '无历史记录';
$labels['history_from_to_of'] = '共 $count 条日志，时间范围：$from 至 $to';

$labels['account_excluded'] = '您的账户被排除在应用密码使用之外';