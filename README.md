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
its source code. Thunderbird ships with keys from some large providers, enabling 
OAUTH usage for these, but there is no way to deploy you own keys, without shipping
a fork of Thunderbird with is not really feasible.

> [!NOTE]
> Apparently you can add Oauth Providers via Plugins now. But this will only cover
> Thunderbird for Desktop. You still don't have it on mobile or any other Mail Client
> People might use.

So the next best option are application specific passwords for each client the user
is going to use. If you don't already have an IdP/IAM and Account Console to create
and manage these, then the next best place might be the Webmailer that hopefully
has 2FA anyway. This is what this plugin is for. You can create App passwords, see
where they were last used and delete them if not needed any more.

However, this plugin also requires you Dovecot (and SMTP Server [eg. Exim, Postfix])
to be set up a certain way. 

## Prepare the database
For the database, you can use any host you'd like to hold the data. This doesn't 
necessarily need to be the same host, Roundcube or Dovecot are running on; however, 
both will need database access. This host will need to have mariadb (or mysql) 
installed
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
The table structure is described in the Repo for the Dovecot service 
[here](https://github.com/bennet0496/dovecot_web_auth)

## Setup the mail server

To set up the mail server, either setup the purpose build 
[Dovecot Web Auth](https://github.com/bennet0496/dovecot_web_auth) or
otherwise set your mail server up to use the database. E.g. with a 
[sql authdb and post-login Script](https://github.com/bennet0496/dovecot-apppasswd).

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
