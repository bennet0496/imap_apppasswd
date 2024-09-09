#!/bin/bash

#
# Copyright (c) 2024. Bennet Becker <dev@bennet.cc>
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.
#
#

# We have the values from the userdb here
# however we don't the ones from the actual userdb
# but the passdb can set userdb values that where "userdb_" prefixed in the previous config
# we have these variable here without the prefix and uppercased

# $PASSDB is ldap or sql depeding which one was used
# $PASSID is the database id of the password that matched during login
# $IP is the remote IP

# https://stackoverflow.com/a/70597002
__rfc5952_expand () {
    read addr mask < <(IFS=/; echo $1)
    quads=$(grep -oE "[a-fA-F0-9]{1,4}" <<< ${addr/\/*} | wc -l)

    grep -qs ":$" <<< $addr && { addr="${addr}0000"; (( quads++ )); }
    grep -qs "^:" <<< $addr && { addr="0000${addr}"; (( quads++ )); }
    [ $quads -lt 8 ] && addr=${addr/::/:$(for (( i=1; i<=$(( 8 - quads )) ; i++ )); do printf "0000:"; done)}

    addr=$(for quad in $(IFS=:; echo ${addr}); do printf "${delim}%04x" "0x${quad}"; delim=":"; done)
    [ ! -z $mask ] && echo $addr/$mask || echo $addr
}

test "$PASSDB" = "sql" && (
PTR=$(
  grep -q : <<<"$IP" &&
    echo "$(sed 's/://g;s/\(.\)/\1./g' < <(__rfc5952_expand "$IP" | rev))ip6.arpa" ||
    awk -F. '{print $4"."$3"."$2"."$1".in-addr.arpa"}' <<<"$IP"
)
RDNS=$(dig +short PTR "$PTR")
INFO=$(/usr/local/bin/geoip.py "$IP")
LOC=$(head -n 1 <<<"$INFO")
ISP=$(tail -n 1 <<<"$INFO")
mysql -u dovecot mail <<<"INSERT INTO log (id, pwid, src_ip, src_rdns, src_loc, src_isp, timestamp) VALUES (NULL, $PASSID, '$IP', '$RDNS', '$LOC', '$ISP', UTC_TIMESTAMP(3)"
)

# We have to do this otherwise the login fails
exec "$@"
