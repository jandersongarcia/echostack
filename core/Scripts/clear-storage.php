<?php
/**
 * Script: core/Scripts/clear-storage.php
 * Uso:
 *   composer clear:storage [--only=cache|logs|trash] [--keep-days=N] [--dry-run]
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Core\Helpers\PathResolver;
use Core\Utils\Core\LanguageHelper;
use Symfony\Component\Finder\Finder;

$basePath = PathResolver::basePath();

// Idioma
$lang = LanguageHelper::getDefaultLanguage();
$langFile = "$basePath/core/Lang/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "$basePath/core/Lang/en.php";
}
$__ = include $langFile;
$t = fn($key, $replacements = []) =>
    str_replace(
        array_map(fn($k) => ":{$k}", array_keys($replacements)),
        array_values($replacements),
        $__['clear:storage'][$key] ?? $key
    );

$options = getopt('', ['only::', 'keep-days::', 'dry-run']);
$only = $options['only'] ?? null;
$keepDays = isset($options['keep-days']) ? (int) $options['keep-days'] : 0;
$dryRun = isset($options['dry-run']);

$storage = "$basePath/storage";
$targets = [
    'cache' => "$storage/cache",
    'logs' => "$storage/logs",
    'trash' => "$storage/trash",
];

if ($only && !isset($targets[$only])) {
    echo $t('invalid_only') . "\n";
    exit(1);
}

$deleted = 0;
$skipped = 0;

foreach ($targets as $type => $path) {
    if ($only && $only !== $type)
        continue;
    if (!is_dir($path))
        continue;

    echo $t('clearing', ['type' => $type]) . "\n";

    $finder = new Finder();
    $finder->in($path)->depth(0)->ignoreDotFiles(true);

    if ($keepDays > 0) {
        $threshold = new DateTime("-{$keepDays} days");
        $finder->filter(fn(SplFileInfo $f) => new DateTime('@' . $f->getMTime()) < $threshold);
    }

    foreach ($finder as $file) {
        $fpath = $file->getRealPath();
        if ($dryRun) {
            echo $t('dryrun_skip', ['file' => $fpath]) . "\n";
            $skipped++;
        } else {
            if ($file->isDir()) {
                deletePath($fpath);
            } else {
                unlink($fpath);
            }
            echo $t('file_removed', ['file' => $fpath]) . "\n";
            $deleted++;
        }
    }
}

function deletePath(string $path): void
{
    if (is_dir($path)) {
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..')
                continue;
            $full = $path . DIRECTORY_SEPARATOR . $item;
            deletePath($full);
        }
        @rmdir($path);
    } elseif (is_file($path)) {
        @unlink($path);
    }
}


echo $t('summary', [
    'deleted' => $deleted,
    'skipped' => $skipped,
    'dryrun' => $dryRun ? ' (dry-run)' : ''
]) . "\n";
exit(0);
