# tk8base
    
__Web:__ <https://github.com/tropotek/tk8base>  
__Authors:__ Tropotek <http://www.tropotek.com/>

A base Tropotek Tk framework example site.
Use this as a base site implementing the Tropotek Tk Framework libraries.

## Contents

- [Installation](#installation)

## Installation

- Set up a database for the site and keep the login details handy.
- Make sure you have the latest version of composer [https://getcomposer.org/download/] installed.
- Use the following commands:
```bash
$ git clone https://github.com/tropotek/tk8base.git
$ cd tk8base
$ composer install
```
- You will be asked a number of questions to set up the environment settings.
- Create a new admin user to access the site:
```bash
$ ./bin/cmd adm {username}
```
- Edit the `/config.php` file if you require custom settings.
- Change the permissions of the `/data/` folder to be writable by your server.
- Browse to the site URL and login with the admin user credentials.


## Upgrading

Upgrade the site by the CLI command;
```bash
$ cd {siteroot}
$ ./bin/cmd ug
```

Manual upgrade process if the above fails:
```bash
$ git reset --hard
$ git checkout 8.0.0    // Use the latest tag version here
$ composer update
```

__Warning:__ Upgrading could potentially break the site. Be sure to backup all DBa and
site `/data` files before running these commands.




