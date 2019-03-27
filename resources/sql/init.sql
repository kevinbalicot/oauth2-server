/** USE THIS SQL FILE FOR DEVELOPMENT ONLY **/

/** Password is toto **/
INSERT INTO user VALUES('admin', 'Johne', 'Doe', 'johnedoe@fake.com', '$2y$10$aIK7EY67osfyc7mJW8dZCueSmqZ/QyLq9G0OBwIo5pZz2K3aGNEba', 1, NULL, NULL, NULL, NULL, NULL);
/** Password is toto **/
INSERT INTO user VALUES('julesmith', 'Jule', 'Smith', 'julesmith@fake.com', '$2y$10$aIK7EY67osfyc7mJW8dZCueSmqZ/QyLq9G0OBwIo5pZz2K3aGNEba', 1, NULL, NULL, NULL, NULL, NULL);

INSERT INTO client VALUES('auth', '0327fe26-2cb2-11b6-b990-05c6c78b4f2b', 'AuthorizationServer', NULL);

INSERT INTO scope VALUES('auth:user');
INSERT INTO scope VALUES('auth:user:read');
INSERT INTO scope VALUES('auth:user:write');
INSERT INTO scope VALUES('auth:user:scope');

INSERT INTO scope VALUES('auth:scope');
INSERT INTO scope VALUES('auth:scope:read');
INSERT INTO scope VALUES('auth:scope:write');

INSERT INTO scope VALUES('auth:client');
INSERT INTO scope VALUES('auth:client:user');

INSERT INTO users_scopes VALUES('admin', 'auth:user');
INSERT INTO users_scopes VALUES('admin', 'auth:scope');

INSERT INTO clients_scopes VALUES('auth', 'auth:user');
INSERT INTO clients_scopes VALUES('auth', 'auth:scope');
INSERT INTO clients_scopes VALUES('auth', 'auth:client');

INSERT INTO clients_users VALUES('auth', 'admin');
INSERT INTO clients_users VALUES('auth', 'julesmith');
