<?php


// default install to add an admin user, customize this to your own requirements
$users = \App\Db\User::findAll();
if (!count($users)) {

    \Tk\Log::debug("Adding new admin user");
    $sql = <<<SQL
        SET FOREIGN_KEY_CHECKS = 0;
        SET SQL_SAFE_UPDATES = 0;

        TRUNCATE TABLE user;
        TRUNCATE TABLE auth;
        TRUNCATE TABLE auth_remember;

        INSERT INTO user (type, given_name) VALUES ('staff', 'Administrator');
        INSERT INTO auth (fkey, fid, permissions, username, email, timezone) VALUES
          ('App\\\\Db\\\\User', LAST_INSERT_ID(), 1, 'admin', 'admin@example.com', 'Australia/Melbourne');

        SET SQL_SAFE_UPDATES = 1;
        SET FOREIGN_KEY_CHECKS = 1;
    SQL;
    try {
        \Tk\Db::execute($sql);
    } catch (\Exception $e) {
        \Tk\Log::debug($e->__toString());
    }

    echo "New admin user added, to set the password call 'php ./bin/cmd pwd admin'\n";
}

