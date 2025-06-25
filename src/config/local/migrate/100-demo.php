<?php


\Bs\Registry::setValue('site.email', 'info@tropotek.com.au');
\Bs\Registry::instance()->save();

// Setup demo users if none exist
$user = \App\Db\User::findByUsername('dev');
if (!$user) {

    \Tk\Log::debug("Adding new users to site.");
    $sql = <<<SQL
        SET FOREIGN_KEY_CHECKS = 0;
        SET SQL_SAFE_UPDATES = 0;

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

    // Update user passwords with 'ttek-2025'
    foreach (\Bs\Auth::findAll() as $auth) {
        $auth->password = \Bs\Auth::hashPassword('ttek-2025');
        $auth->save();
    }

    // setup demo team data
    $sql = <<<SQL
INSERT INTO team (team_id, name, description, active, modified, created) VALUES
    (1, 'Read Team', 'This is a description', 1, NOW(), NOW()),
    (2, 'Blue Team', '', 1, NOW(), NOW()),
    (3, 'Out Team', '', 0, NOW(), NOW())
SQL;
    try {
        \Tk\Db::execute($sql);
    } catch (\Exception $e) {
        \Tk\Log::debug($e->__toString());
    }

    $sql = <<<SQL
INSERT INTO team_has_user (team_id, user_id) VALUES
    (1, 101),
    (1, 102),
    (1, 103);
SQL;
    try {
        \Tk\Db::execute($sql);
    } catch (\Exception $e) {
        \Tk\Log::debug($e->__toString());
    }

}
