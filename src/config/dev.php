<?php
/**
 * Finalise system migration.
 * Called after the system DB has be migrated or mirrored.
 *
 */

// Production code




// dev env code
if (!\Tk\Config::isDev()) return;

// Setup users if none exist (new install)
$user = \App\Db\User::find(1);
if (is_null($user)) {

    // TODO: remove for new sites, create a new system first user `admin` for example.
    \Tk\Log::debug("Adding new users to site.");
    $sql = <<<SQL
        SET FOREIGN_KEY_CHECKS = 0;
        SET SQL_SAFE_UPDATES = 0;

        TRUNCATE TABLE user;
        TRUNCATE TABLE auth;
        TRUNCATE TABLE auth_remember;

        INSERT INTO user (type, given_name) VALUES ('staff', 'Developer');
        INSERT INTO auth (fkey, fid, permissions, username, email, timezone) VALUES
          ('App\\\\Db\\\\User', LAST_INSERT_ID(), 1, 'dev', 'dev@example.com', 'Australia/Melbourne');

        INSERT INTO user (type, given_name) VALUES ('staff', 'Designer');
        INSERT INTO auth (fkey, fid, permissions, username, email, timezone) VALUES
          ('App\\\\Db\\\\User', LAST_INSERT_ID(), 6, 'design', 'design@example.com', 'Australia/Melbourne');

        INSERT INTO user (type, given_name) VALUES ('staff', 'Staff');
        INSERT INTO auth (fkey, fid, permissions, username, email, timezone) VALUES
          ('App\\\\Db\\\\User', LAST_INSERT_ID(), 14, 'staff', 'staff@example.com', 'Australia/Melbourne');

        INSERT INTO user (type, given_name) VALUES ('member', 'Member');
        INSERT INTO auth (fkey, fid, username, email, timezone) VALUES
          ('App\\\\Db\\\\User', LAST_INSERT_ID(), 'member', 'member@example.com', 'Australia/Brisbane');

        SET SQL_SAFE_UPDATES = 1;
        SET FOREIGN_KEY_CHECKS = 1;
    SQL;
    try {
        \Tk\Db::execute($sql);
    } catch (\Exception $e) {
        \Tk\Log::debug($e->__toString());
    }

    // Update user passwords
    foreach (\Bs\Auth::findAll() as $auth) {
        $auth->password = \Bs\Auth::hashPassword('password');
        $auth->save();
    }
}

