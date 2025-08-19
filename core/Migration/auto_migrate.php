<?php

use Medoo\Medoo;
use Monolog\Logger;
use Core\Helpers\PathResolver;



/**
 * Verifica se a migração automática está habilitada via .env.
 */
function shouldAutoMigrate(): bool
{
    return isset($_ENV['AUTO_MIGRATE']) && $_ENV['AUTO_MIGRATE'] === 'true';
}

/**
 * Verifica se a tabela existe no banco de dados.
 */
function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    return $stmt->fetch() !== false;
}

/**
 * Desativa a flag AUTO_MIGRATE no arquivo .env após sucesso.
 */
function disableAutoMigrate(Logger $logger): void
{
    
    $envFile =   PathResolver::basePath() . '/.env';

    if (!file_exists($envFile)) {
        $logger->warning(".env file not found. Unable to disable AUTO_MIGRATE.");
        return;
    }

    $content = file_get_contents($envFile);
    $updated = preg_replace('/^AUTO_MIGRATE\s*=\s*true$/mi', 'AUTO_MIGRATE=false', $content);

    if ($updated !== null) {
        file_put_contents($envFile, $updated);
        $logger->info("AUTO_MIGRATE has been set to false.");
    } else {
        $logger->warning("Failed to update AUTO_MIGRATE in .env.");
    }
}

/**
 * Executa a migração do banco de dados se necessário.
 */
function runMigrationIfNeeded(Medoo $database, Logger $logger): void
{
    if (!shouldAutoMigrate()) {
        return;
    }

    if (tableExists($database->pdo, 'users')) {
        return;
    }

    $migrationFile = __DIR__ . '/auth-migrations.sql';

    if (!file_exists($migrationFile)) {
        $logger->error("Migration file not found: auth-migrations.sql", [
            'file' => $migrationFile
        ]);
        return;
    }

    try {
        $sql = file_get_contents($migrationFile);
        $database->pdo->exec($sql);

        // Verifica se a tabela foi realmente criada após a migração
        if (tableExists($database->pdo, 'users')) {
            disableAutoMigrate($logger);
        } else {
            $logger->error("Migration executed but 'users' table still missing.", [
                'file' => $migrationFile
            ]);
        }

    } catch (\PDOException $e) {
        $logger->error("Failed to execute migration.", [
            'exception' => $e,
            'file' => $migrationFile
        ]);
    }
}
