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

namespace bennetcc {

    class Log
    {
        private string $file;
        private string $app_prefix;
        private LogLevel $log_level;

        public function __construct(string $file, string $app_prefix, LogLevel|int $log_level)
        {
            $this->file = $file;
            $this->app_prefix = $app_prefix;
            $this->log_level = LogLevel::from($log_level);
        }

        private function log(string $prefix, ...$lines): void
        {
            foreach ($lines as $line) {
                $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
                $func = $bt["function"];
                $cls = $bt["class"];
                if ($cls == "bennetcc\Log") {
                    $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2];
                    $func = $bt["function"];
                    $cls = $bt["class"];
                }
                if (!is_string($line)) {
                    $line = print_r($line, true);
                }
                $llines = explode(PHP_EOL, $line);
                \rcmail::write_log($this->file, "[" . $this->app_prefix . "/" . $prefix . "] {" . $cls . "::" . $func . "} " . $llines[0]);
                unset($llines[0]);
                if (count($llines) > 0) {
                    foreach ($llines as $l) {
                        \rcmail::write_log($this->file, str_pad("...", strlen("[" . $this->app_prefix . "/" . $prefix . "] "), " ", STR_PAD_BOTH) . "{" . $cls . "::" . $func . "} " . $l);
                    }
                }
            }
        }

        public function trace(...$lines): void
        {
            if ($this->log_level->value >= LogLevel::TRACE->value) {
                $this->log("TRACE", ...$lines);
            }
        }

        public function debug(...$lines): void
        {
            if ($this->log_level->value >= LogLevel::DEBUG->value) {
                $this->log("DEBUG", ...$lines);
            }
        }

        public function warn(...$lines): void
        {
            if ($this->log_level->value >= LogLevel::WARNING->value) {
                $this->log("WARN", ...$lines);
            }
        }

        public function info(...$lines): void
        {
            if ($this->log_level->value >= LogLevel::INFO->value) {
                $this->log("INFO", ...$lines);
            }
        }

        public function error(...$lines): void
        {
            if ($this->log_level->value >= LogLevel::ERROR->value) {
                $this->log("ERROR", ...$lines);
            }
        }
    }

    enum LogLevel: int
    {
        case TRACE = 4;
        case DEBUG = 3;
        case WARNING = 2;
        case INFO = 1;
        case ERROR = 0;
    }
}