# tk8base
    
__Web:__ <https://github.com/tropotek/tk8base>  
__Authors:__ Tropotek <http://www.tropotek.com/>

A veterinary anatomical database for use to manage necropsy and biopsy cases and clients.

## Contents

- [Installation](#installation)
- [Introduction](#introduction)

## Installation

1. First set up a database for the site and keep the login details handy.
2. Make sure you have the latest version of composer [https://getcomposer.org/download/] installed.
3. Use the following commands:
    ```bash
    $ git clone https://github.com/tropotek/tkapd.git
    $ cd tkapd
    $ composer install
    ````
4. You will be asked a number of questions to set up the environment settings.
5. Edit the `/config.php` file to your required settings.
6. You may have to change the permissions of the `/data/` folder so PHP can read and write to it.
7. To enable debug mode and logging edit the `/config.php` file to suit your server.
8. Browse to the site location URL to see if it all worked.

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


## Introduction



