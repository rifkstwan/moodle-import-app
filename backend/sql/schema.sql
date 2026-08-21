-- Moodle Import App Database Schema
-- PostgreSQL
DROP TABLE IF EXISTS users CASCADE;

CREATE TABLE users
(
    id        BIGSERIAL PRIMARY KEY,
    name      VARCHAR(255) NOT NULL,
    surname   VARCHAR(255) NOT NULL,
    email     VARCHAR(255) NOT NULL UNIQUE
);

COMMENT ON TABLE users IS 'User accounts imported via CSV upload';

COMMENT ON COLUMN users.id IS 'Auto-increment primary key';

COMMENT ON COLUMN users.name IS 'Full user name (title-cased) imported from CSV';

COMMENT ON COLUMN users.surname IS 'User surname (title-cased) imported from CSV';

COMMENT ON COLUMN users.email IS 'Normalized e-mail address (lower-case, unique) imported from CSV';