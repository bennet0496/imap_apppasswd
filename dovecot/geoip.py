#!/usr/bin/env python3

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

import geoip2.database
import socket
import struct
import sys

ip = sys.argv[1]
geoip = "/var/lib/GeoIP/"

local_networks = {
    "Network 1":                 ["192.0.2.0", 24],
    "Network 2":          ["198.51.100.0", 24],
    "Network 3":             ["203.0.113.0", 24],
    "Network 4":    ["192.168.4.0", 24],
    "Network 5":                   ["192.168.3.0", 24],
    "Wifi Network 1":                  ["192.168.1.0", 24],
    "Wifi Network 2":     ["192.168.0.0", 24],
}

try:
    packedIP = socket.inet_aton(ip)
    ip_int = struct.unpack("!L", packedIP)[0]
    # ip_int & (0xffffffff << (32-mask))
    # == struct.unpack("!L",socket.inet_aton(net))[0]
    # ip_int & (0xffffffff << (32-local_networks[i][1]))
    idx = [ip_int & (0xffffffff << (32-local_networks[i][1])) == struct.unpack("!L", socket.inet_aton(local_networks[i][0]))[0] for i in local_networks].index(True)

    print("MPI PKS " + list(local_networks.items())[idx][0])
    print("local network")
except ValueError:
    with geoip2.database.Reader(geoip + 'GeoLite2-City.mmdb') as city_reader, geoip2.database.Reader( geoip + 'GeoLite2-ASN.mmdb') as asn_reader:
        try:
            city = city_reader.city(ip)
            isp = asn_reader.asn(ip)
            print(str(city.city.name) + ", " + str(city.subdivisions.most_specific.name) + ", " + str(city.country.name))
            print(str(isp.autonomous_system_organization))
        except geoip2.errors.AddressNotFoundError:
            print()
