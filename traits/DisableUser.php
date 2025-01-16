<?php
/*
 * Copyright (c) 2024-2025. Bennet Becker <dev@bennet.cc>
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

trait DisableUser {
    private function is_disabled(): bool
    {
        $ex = $this->rc->config->get(__("exclude_users"), []);
        $exg = $this->rc->config->get(__("exclude_users_in_addr_books"), []);
        $exn = $this->rc->config->get(__("exclude_users_not_in_addr_books"), []);
        $exa = $this->rc->config->get(__("exclude_users_with_addr_book_value"), []);
        /** @noinspection SpellCheckingInspection */
        $exag = $this->rc->config->get(__("exclude_users_in_addr_book_group"), []);

        $this->log->trace($ex,$exg, $exa, $exag);

        // exclude directly deny listed users
        if (is_array($ex) && (in_array($this->rc->get_user_name(), $ex) || in_array($this->resolve_username(), $ex) || in_array($this->rc->get_user_email(), $ex))) {
            $this->log->info("access for " . $this->resolve_username() . " disabled via direct deny list");
            return true;
        }

        // exclude directly deny listed address books
        if (is_array($exg) && count($exg) > 0) {
            foreach ($exg as $book) {
                /** @noinspection SpellCheckingInspection */
                $abook = $this->rc->get_address_book($book);
                if ($abook) {
                    if (array_key_exists("uid", $book->coltypes)) {
                        $entries = $book->search(["email", "uid"], [$this->rc->get_user_email(), $this->resolve_username()]);
                    } else {
                        $entries = $book->search("email", $this->rc->get_user_email());
                    }
                    if ($entries) {
                        $this->log->info("access for " . $this->resolve_username() .
                            " disabled in " . $book->get_name() . " because they exist in there");
                        return true;
                    }
                }
            }
        }

        // exclude not directly listed address books
        if (is_array($exn) && count($exn) > 0) {
            foreach ($exn as $book) {
                /** @noinspection SpellCheckingInspection */
                $abook = $this->rc->get_address_book($book);
                if ($abook) {
                    if (array_key_exists("uid", $book->coltypes)) {
                        $entries = $book->search(["email", "uid"], [$this->rc->get_user_email(), $this->resolve_username()]);
                    } else {
                        $entries = $book->search("email", $this->rc->get_user_email());
                    }
                    if (!$entries) {
                        $this->log->info("access for " . $this->resolve_username() .
                            " disabled in " . $book->get_name() . " because they do not exist in there");
                        return true;
                    }
                }
            }
        }

        // exclude users with a certain attribute in an address book
        if (is_array($exa) && count($exa) > 0) {
            // value not properly formatted
            if (!is_array($exa[0])) {
                $exa = [$exa];
            }
            foreach ($exa as $val) {
                if (count($val) == 3) {
                    $book = $this->rc->get_address_book($val[0]);
                    $attr = $val[1];
                    $match = $val[2];

                    if (array_key_exists("uid", $book->coltypes)) {
                        $entries = $book->search(["email", "uid"], [$this->rc->get_user_email(), $this->resolve_username()]);
                    } else {
                        $entries = $book->search("email", $this->rc->get_user_email());
                    }

                    if ($entries) {
                        while ($e = $entries->iterate()) {
                            if (array_key_exists($attr, $e) && ($e[$attr] == $match ||
                                    (is_array($e[$attr]) && in_array($match, $e[$attr])))) {
                                $this->log->info("access for " . $this->resolve_username() .
                                    " disabled in " . $book->get_name() . " because of " . $attr . "=" . $match);
                                return true;
                            }
                        }
                    }
                }
            }
        }

        // exclude users in groups
        if (is_array($exag) && count($exag) > 0) {
            if (!is_array($exag[0])) {
                /** @noinspection SpellCheckingInspection */
                $exag = [$exag];
            }
            foreach ($exag as $val) {
                if (count($val) == 2) {
                    $book = $this->rc->get_address_book($val[0]);
                    $group = $val[1];

                    $groups = $book->get_record_groups(base64_encode($this->resolve_username()));

                    if (in_array($group, $groups)) {
                        $this->log->info("access for " . $this->resolve_username() .
                            " disabled in " . $book->get_name() . " because of group membership " . $group);
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
