
-- --------------------------------------------------------
-- Exportação adaptada para PostgreSQL
-- Gerado em 2025-07-22 13:15:58
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS password_resets (
  id BIGSERIAL PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expiration TIMESTAMP NOT NULL,
  creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS roles (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (id, name) VALUES
  (1, 'master'),
  (2, 'admin'),
  (3, 'manager'),
  (4, 'user')
ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS users (
  id BIGSERIAL PRIMARY KEY,
  role_id BIGINT NOT NULL DEFAULT 3,
  "user" VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  activated BOOLEAN NOT NULL DEFAULT TRUE,
  creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_roles FOREIGN KEY (role_id) REFERENCES roles(id)
);

INSERT INTO users (id, role_id, "user", email, password, activated, creation_date) VALUES
  (1, 1, 'Master', 'master@echoapi.local', '$2y$10$kUpUMOLxlkcoZIlZuMb1zOuW7d3bW5kkpkFaDCV6Fg5/qzNGBZBMC', TRUE, '2025-07-14 18:43:15')
ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS user_tokens (
  id BIGSERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL,
  token VARCHAR(255) NOT NULL,
  revoked BOOLEAN NOT NULL DEFAULT FALSE,
  creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_tokens_users FOREIGN KEY (user_id) REFERENCES users(id)
);
