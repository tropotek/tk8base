-- --------------------------------------------
-- @version install
-- --------------------------------------------

CREATE TABLE IF NOT EXISTS user
(
    user_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(128) NOT NULL DEFAULT '',
    title VARCHAR(16) NOT NULL DEFAULT '',
    given_name VARCHAR(128) NOT NULL DEFAULT '',
    family_name VARCHAR(128) NOT NULL DEFAULT '',
    phone VARCHAR(32) NOT NULL DEFAULT '',
    address VARCHAR(1000) NOT NULL DEFAULT '',
    city VARCHAR(128) NOT NULL DEFAULT '',
    state VARCHAR(128) NOT NULL DEFAULT '',
    postcode VARCHAR(128) NOT NULL DEFAULT '',
    country VARCHAR(128) NOT NULL DEFAULT '',
    template VARCHAR(256) NOT NULL DEFAULT '',
    image VARCHAR(256) NOT NULL DEFAULT '',
    modified TIMESTAMP ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY (type)
);

CREATE TABLE IF NOT EXISTS notify (
    notify_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL DEFAULT NULL,
    title VARCHAR(256) NOT NULL DEFAULT '',
    message TEXT,
    url VARCHAR(256) NOT NULL DEFAULT '',
    icon BLOB NOT NULL DEFAULT '',
    read_at DATETIME NULL,                                    -- Date user read notification in browser
    notified_at DATETIME NULL,                                -- Date message was sent as browser notification
    ttl_mins INT UNSIGNED NOT NULL DEFAULT 1440,
    expiry DATETIME GENERATED ALWAYS AS (created + INTERVAL ttl_mins MINUTE) VIRTUAL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY (user_id),
    CONSTRAINT fk_notify__user_id FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS file
(
    file_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL DEFAULT NULL,
    fkey VARCHAR(64) DEFAULT '' NOT NULL,
    fid INT DEFAULT 0 NOT NULL DEFAULT 0,
    label VARCHAR(128) NOT NULL DEFAULT '',
    filename VARCHAR(255) NOT NULL DEFAULT '',              -- the files relative path from site root
    bytes INT UNSIGNED NOT NULL DEFAULT 0,
    mime VARCHAR(255) NOT NULL DEFAULT '',
    notes TEXT NULL,
    selected BOOL NOT NULL DEFAULT FALSE,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY user_id (user_id),
    KEY fkey (fkey),
    KEY fkey_2 (fkey, fid),
    KEY fkey_3 (fkey, fid, label),
    CONSTRAINT fk_file__user_id FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- --------------------------------------------------------------

CREATE TABLE IF NOT EXISTS team
(
    team_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL DEFAULT '',
    description TEXT,
    active BOOL NOT NULL DEFAULT TRUE,
    modified TIMESTAMP ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS team_has_user
(
  team_id INT UNSIGNED NOT NULL DEFAULT 0,
  user_id INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (team_id, user_id),
  CONSTRAINT team_has_user__team_id FOREIGN KEY (team_id) REFERENCES team (team_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT team_has_user__user_id FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

