-- 1. Users table
CREATE TABLE `users` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_general_ci',
    `email` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_general_ci',
    `password` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
    `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `email` (`email`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB;

-- 2. Tokens table
CREATE TABLE `tokens` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT(20) NOT NULL DEFAULT '0',
    `token` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
    `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `FK_tokens_users` (`user_id`) USING BTREE,
    CONSTRAINT `FK_tokens_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB;

-- 3. Password resets table
CREATE TABLE `password_resets` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_general_ci',
    `token` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
    `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB;
