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
use DateInterval;

trait FormatTimeDifference {
    /**
     * Format a DateInternal as human-readable string like "n days ago"
     * @param DateInterval $diff
     * @return string
     */
    private function format_diff(DateInterval $diff): string
    {
        if ($diff->format("%y") != "0") {
            return $this->gettext(["name" => "years_ago", "vars" => ["count" => $diff->format("%y")]]);
        }
        if ($diff->format("%m") != "0") {
            return $this->gettext(["name" => "months_ago", "vars" => ["count" => $diff->format("%m")]]);
        }
        if ($diff->format("%d") != "0") {
            return $this->gettext(["name" => "days_ago", "vars" => ["count" => $diff->format("%d")]]);
        }
        if ($diff->format("%h") != "0") {
            return $this->gettext(["name" => "hours_ago", "vars" => ["count" => $diff->format("%h")]]);
        }
        if ($diff->format("%i") != "0") {
            return $this->gettext(["name" => "minutes_ago", "vars" => ["count" => $diff->format("%i")]]);
        }

        return $this->gettext("just_now");

    }
}