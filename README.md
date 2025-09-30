# IMAP App Passwords

![Screenshot from 2024-04-22 11-36-01](https://github.com/bennet0496/imap_apppasswd/assets/4955327/233c02d1-9d29-41e2-8c91-4aef5ec5ba9a)

Add application specific password to your dovecot IMAP environment. 

In a world where SSO is not only convenient, but also the norm, there is a problem 
when it comes to mandatory 2FA/MFA in conjunction with the mail protocols SMTP and 
IMAP. While most other webservices have MFA as a second line of defense in cases
where users lose their password attacks including but not limited to phishing, IMAP
and SMTP lack these capabilities and would allow an adversary to snoop a user's
emails or to even impersonate them to peers. Established mail services like Gmail
and Outlook circumvent this with XOAUTH2 (or app passwords). While Dovecot 
supports XOAUTH2, the problem is that the client implementation of it in Thunderbird
(and maybe also other clients), require static OAUTH Keys that are hard coded in 
its source code. Thunderbird shipps with keys from some large providers, enabling 
OAUTH usage for these, but there is no way to deploy you own keys, without shipping
a fork of Thunderbird with is not really feasible.

So the next best option are application specific passwords for each client the user
is going to use. If you don't already have an IdP/IAM and Account Console to create
and manage these, then the next best place might be the Webmailer that hopefully
has 2FA anyway. This is what this plugin is for. You can create App passwords, see
where they were last used and delete them if not needed any more.

However, this plugin also requires you Dovecot (and SMTP Server [eg. Exim, Postfix])
to be set up a certain way. 

## Prepare the database
For the database, you can use any host you'd like to hold the data. This doesn't necessarily need 
to be the same host, Roundcube or Dovecot are running on; however, both will need database access.
This host will need to have mariadb (or mysql) installed
```bash
apt install mariadb-server
```

Then create the database, users e.g. with
```sql
CREATE DATABASE mail_auth;
GRANT USAGE ON *.* TO `mailserver`@`localhost` IDENTIFIED BY 'password123';
GRANT USAGE ON *.* TO `roundcube`@`webmail.example.com` IDENTIFIED BY 'password123';

GRANT SELECT ON `mail_auth`.`log` TO `roundcube`@`webmail.example.com`;
GRANT SELECT, SHOW VIEW ON `mail_auth`.`app_passwords_with_log` TO `roundcube`@`webmail.example.com`;
GRANT SELECT, INSERT, UPDATE (`comment`), DELETE ON `mail_auth`.`app_passwords` TO `roundcube`@`webmail.example.com`;

GRANT SELECT ON `mail_auth`.`app_passwords` TO `mailserver`@`localhost`;
GRANT SELECT, INSERT ON `mail_auth`.`log` TO `mailserver`@`localhost`;
```
And the tables from the DDL in `database/DDL.sql`. Replace the passwords and host specifiers
appropriately.

## Setup the mail server

To set up the mail server, either setup Dovecot web auth from https://github.com/bennet0496/dovecot_web_auth or
use the old Postlogin script method below, that may or may not work anymore.


## Setup your mail server (the old way)
<details>
You will need the following to be installed on you mail server
```bash
apt install dovecot-mysql geoip-database geoipupdate
```

### Configure Dovecot
#### Configure the passdb
Now you'll need to configure Dovecot to use a SQL passdb that holds our application passwords.
In `/etc/dovecot/conf.d/10-auth.conf` uncomment the SQL include line
```
# ...
!include auth-sql.conf.ext
# ...
# ...
```
And add the passdb (and only the passdb) to the `auth-sql.conf.ext` file
```
# Authentication for SQL users. Included from 10-auth.conf.
#
# <doc/wiki/AuthDatabase.SQL.txt>

passdb {
  driver = sql

  # Path for SQL configuration file, see example-config/dovecot-sql.conf.ext
  args = /etc/dovecot/dovecot-sql.conf.ext
  override_fields = userdb_passdb=sql
  skip = authenticated
}
```
This species the SQL configuration and sets a custom userdb field that we'll use later in the
postlogin process to detect usage of the passdb. And lastly we want this passdb to be skipped if
another passdb, e.g. LDAP has already authenticated the user, i.e. when they log in with their
real password to Roundcube. It would also make sense to limit the passdb with the real passwords
to only be usable from out Roundcube
```
passdb {
  driver = ldap

  # Path for LDAP configuration file, see example-config/dovecot-ldap.conf.ext
  args = /etc/dovecot/dovecot-ldap.passdb.conf.ext
  
  # Replace with Roundcube IP
  override_fields = allow_nets=1.2.3.4/32 userdb_passdb=ldap
}
```
Again we mark which passwd was used, and we limit it to only successfully authenticate from
out webmailer's IP.

Now we configure the actual SQL statements Dovecot will use to authenticate against our passdb.
In `/etc/dovecot/dovecot-sql.conf.ext` set
```
driver = mysql
connect = host=127.0.0.1 dbname=mail user=mailserver password=password123

password_query = password_query = SELECT uid AS username, password, id AS userdb_passid \
  FROM app_passwords \
  WHERE uid = '%n' AND REGEXP_SUBSTR(password, '[$].*') = ENCRYPT('%w', REGEXP_SUBSTR(password, '[$].*[$]'))
```
Replace the `connect` line appropriately. This retrieves the username, password and password ID as
userdb attribute to we can tie the login to a specific password in our post-login script. We
need to pass the password to the database, as dovecot does not support the result to be multiple 
lines. This mean we transmit the in plain text to hash in query, therefore the connection should
be local or SSL/TLS encrypted.

#### Post-login Script (Last login tracking)
We want to show the user when each password was last used and from where. For this we need to
create a [post-login script for the IMAP](https://doc.dovecot.org/admin_manual/post_login_scripting/) 
(and POP3 if you into that sort of thing) service in Dovecot. In `/etc/dovecot/conf.d/10-master.conf` 
to the IMAP service, add `imap-postlogin` the executable line. And create a service `imap-postlogin`
pointing to the script
```
# ...
service imap {
  # Most of the memory goes to mmap()ing files. You may need to increase this
  # limit if you have huge mailboxes.
  #vsz_limit = $default_vsz_limit

  executable = imap imap-postlogin

  # Max. number of IMAP processes (connections)
  process_limit = 1024
}

service imap-postlogin {
  # all post-login scripts are executed via script-login binary
  executable = script-login /usr/local/bin/postlogin.sh

  # the script process runs as the user specified here (v2.0.14+):
  user = dovecot
  # this UNIX socket listener must use the same name as given to imap executable
  unix_listener imap-postlogin {
  }
}

#...
```
The script `dovecot/postlogin.sh` gathers the information and executes the parameter passed 
from the Dovecot script-login binary to finish the process. It depends on the `dovecot/geopip.py`
to gather Geographic information. You will need to have MaxMind GeoLite setup for this to work.
In this script you can also configure names for local networks
```python
local_networks = {
    "Network 1":                 ["172.17.1.0", 24],
    "Network 2":                 ["10.8.0.0", 16],
    "Network 3":                 ["192.168.0.0", 24],
}
```
If your Database server is not local, you will also need to modify the `postlogin.sh` to use the 
correct host and password.

### A word on the SMTP Server
If you use SASL login from your SMTP Server you are mostly set. It will just use the passdb aswell,
however, last-login tracking will not work, as I was unable to get dovecot to fire a post-login
script with the auth service.

If you have separate authentication in you SMTP server, you'll have to set it up to use the database
as well. And for good measure you might also want to look into firing a post-login script from there.
</details>

## Plugin Setup

Install the plugin with composer
```
composer require mpipks/imap_apppasswd
```

and configure it using `config.inc.php`.

The most important option is correctly setting up the database connection, by setting 
the DSN and credentials. You also need to set up how the username is derived. Here it is
important to set it up the same way Dovecot will actually match the username after canonicalization.
Meaning that even if you allow Login with the email as username Dovecot, if in the background it just
matches against the local part, you need to set matching against the local part here.
