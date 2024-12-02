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

namespace bennetcc\imap_apppasswd\traits;
use function bennetcc\imap_apppasswd\__;

trait ResolveUsername {
    /**
     * Helper to resolve Roundcube username (email) to IMAP username
     *
     * Returns resolved name.
     *
     * @param $user string The username
     * @return string
     */
    private function resolve_username(string $user = ""): string
    {
        $this->log->trace("user: " . $user);

        if (empty($user)) {
            // verbatim roundcube username
            $user = $this->rc->user->get_username();
        }

        $this->log->trace("user: " . $user);

        $username_tmpl = $this->rc->config->get(__("username"));

        $mail = $this->rc->user->get_username("mail");
        $mail_local = $this->rc->user->get_username("local");
        $mail_domain = $this->rc->user->get_username("domain");

        $imap_user = empty($_SESSION['username']) ? $mail_local : $_SESSION['username'];

        $this->log->trace($username_tmpl, $mail, $mail_local, $mail_domain, $imap_user);

        return str_replace(["%s", "%i", "%e", "%l", "%u", "%d", "%h"],
            [$user, $imap_user, $mail, $mail_local, $mail_local, $mail_domain, $_SESSION['storage_host'] ?? ""],
            $username_tmpl);
    }
}
