-- Estrutura da tabela roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados iniciais da tabela roles
INSERT INTO `roles` (`id`, `name`) VALUES
  (1, 'admin'),
  (2, 'manager'),
  (3, 'user');

-- Estrutura da tabela users
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 3,
  `user` VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci,
  `email` VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci,
  `password` VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci,
  `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `fk_users_roles` (`role_id`),
  CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura da tabela password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci,
  `token` VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci,
  `expiration` DATETIME NOT NULL,
  `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estrutura da tabela user_tokens
CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci,
  `revoked` TINYINT(1) NOT NULL DEFAULT 0,
  `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_user_tokens_users` (`user_id`),
  CONSTRAINT `fk_user_tokens_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
