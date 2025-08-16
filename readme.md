# tk8base
    
__Web:__ <https://github.com/tropotek/tk8base>  
__Authors:__ Tropotek <http://www.tropotek.com/>

A base Tropotek Tk framework example site.
Use this as a base site implementing the Tropotek Tk Framework libraries.

## Contents

- [Install](#install)
- [Upgrade](#upgrade)
- [Cron Script](#cron-script)

## Install

- Set up a database for the site and keep the login details handy.
- Make sure you have the latest version of composer [https://getcomposer.org/download/].
- Use the following commands:
```bash
$ git clone https://github.com/tropotek/tk8base.git
$ cd tk8base
$ git checkout 8.0.0    // Use the latest tag version here
$ composer install
```
- You will be asked a number of questions to set up the environment settings.
- Create a new admin user to log into the site:
```bash
$ ./bin/cmd adm {username}
```
- Edit the `/config.php` file if you require custom settings.
- To enable debug mode and logging edit the `/config.php` file:
```php
    // Enable Debug in a dev environment
    $config['env.type'] = 'dev';
    // Setup dev environment
    if ($config->isDev()) {
        // Send all emails to the debug address
        $config['system.debug.email'] = 'dev@example.com';
        // allow any password without strict validation
        $config['auth.password.strict'] = false;
    }
```
- Change the permissions of the `/data/` folder to be writable by your server.
- Browse to the site URL and login with the admin user credentials.

## Upgrade

Before upgrading, make sure you have a backup of the site or at least the `/data` folder and the database.

Backup the DB using the site `cmd` command:
```bash
$ cd {siteroot}
$ ./bin/cmd dbb > sql_backup.sql
# or
$ mysqldump -u {username} -p {database} > sql_backup.sql
```

If the site data files are not to large create a tarball:
```bash
$ tar -czf site_backup.tar.gz /path/to/siteroot 
```

Upgrade the site using the site `cmd` command:
```bash
$ cd {siteroot}
$ ./bin/cmd ug
```

Manual upgrade process if the above fails:
```bash
$ git reset --hard      // clear any local changes
$ git pull
$ git checkout 8.0.1    // Use the latest tag version here
$ composer install
```

__Warning:__ Upgrading could potentially break the site. Be sure to back up all DB's and
site `/data` files before running these commands.


## Cron Script

Install the cron script to ensure all features of the site work using `crontab -e`:
```cron
# Run the cron script every 2 hours
0 */2 * * *  /{pathToSite}/bin/cmd cron
```



