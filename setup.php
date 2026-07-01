<?php

declare(strict_types=1);

/**
 * Project Setup Script
 *
 * Guides you through setting up a new project:
 * - Copies .env.example to .env
 * - Configures APP_NAME, APP_URL, VITE_APP_URL, MAIL_FROM_ADDRESS
 * - Installs Composer and Node dependencies
 * - Links the sibling Craft Laravel package when available
 * - Generates app key, runs migrations
 * - Sets up Git config-based hooks
 * - Links and secures site with Orbit/Herd
 * - Optionally deletes itself
 */
$setupSteps = [
    'copyEnvFile',
    'updateAppName',
    'updateAppUrl',
    'installComposerDependencies',
    'linkCraftLaravel',
    'installNodeDependencies',
    'generateAppKey',
    'createDatabase',
    'runMigrations',
    'setupGitHooks',
    'linkSite',
    'buildAssets',
    'askDeleteScript',
];

// Detect local dev CLI (Orbit or Herd). Prefer Orbit when both are available.
function getLocalDevCli(): ?string
{
    static $cli = null;
    if ($cli !== null) {
        return $cli;
    }
    foreach (['orbit', 'herd'] as $cmd) {
        $out = [];
        exec('which '.$cmd.' 2>/dev/null', $out, $code);
        if ($code === 0 && $out !== []) {
            $cli = $cmd;

            return $cli;
        }
    }

    return null;
}

function getRootFolderName(): string
{
    return basename(getcwd());
}

function formatAppName(string $folderName): string
{
    return ucwords(str_replace(['-', '_'], ' ', $folderName));
}

function runSetup(array $steps): void
{
    echo "Starting project setup...\n\n";

    $envContent = '';
    $updated = false;

    foreach ($steps as $step) {
        [$envContent, $updated] = $step($envContent, $updated);
    }

    echo "\nSetup completed successfully! Your project is ready.\n";
    echo "Run `composer dev` to start developing.\n";
}

function copyEnvFile($envContent, $updated)
{
    if (! file_exists('.env.example')) {
        echo "Error: .env.example not found. Run this from the project root.\n";
        exit(1);
    }

    if (! file_exists('.env')) {
        echo "Creating .env from .env.example...\n";
        if (! copy('.env.example', '.env')) {
            echo "Error: Failed to create .env file.\n";
            exit(1);
        }
        echo ".env created.\n\n";
    } else {
        echo ".env already exists, using existing file.\n\n";
    }

    $envContent = file_get_contents('.env');

    return [$envContent, $updated];
}

function updateAppName($envContent, $updated)
{
    $defaultAppName = formatAppName(getRootFolderName());

    echo "Enter project name [{$defaultAppName}]: ";
    $projectName = trim(fgets(STDIN));

    if (empty($projectName)) {
        $projectName = $defaultAppName;
    }

    $quoted = '"'.str_replace('"', '\"', $projectName).'"';

    $envContent = preg_replace('/APP_NAME=.*/', "APP_NAME={$quoted}", $envContent, -1, $count);
    if ($count > 0) {
        echo "APP_NAME set to {$quoted}.\n\n";
        $updated = true;
    }

    return [$envContent, $updated];
}

function updateAppUrl($envContent, $updated)
{
    $folderName = getRootFolderName();
    $defaultUrl = "https://{$folderName}.test";

    echo "Enter application URL [{$defaultUrl}]: ";
    $appUrl = trim(fgets(STDIN));

    if (empty($appUrl)) {
        $appUrl = $defaultUrl;
    }

    // Update APP_URL
    $envContent = preg_replace('/APP_URL=.*/', "APP_URL={$appUrl}", $envContent, -1, $count);
    if ($count > 0) {
        echo "APP_URL set to {$appUrl}.\n";
        $updated = true;
    }

    // Update VITE_APP_URL
    if (preg_match('/VITE_APP_URL=.*/', $envContent)) {
        $envContent = preg_replace('/VITE_APP_URL=.*/', "VITE_APP_URL={$appUrl}", $envContent);
    } else {
        $envContent .= "\nVITE_APP_URL={$appUrl}\n";
    }
    echo "VITE_APP_URL set to {$appUrl}.\n";

    // Update MAIL_FROM_ADDRESS
    $domain = parse_url($appUrl, PHP_URL_HOST);
    if ($domain) {
        $envContent = preg_replace('/MAIL_FROM_ADDRESS=.*/', "MAIL_FROM_ADDRESS=\"app@{$domain}\"", $envContent, -1, $count);
        if ($count > 0) {
            echo "MAIL_FROM_ADDRESS set to app@{$domain}.\n";
        }
    }

    // Flush all env changes to disk
    if ($updated && file_put_contents('.env', $envContent)) {
        echo "Changes saved to .env.\n\n";
    }

    return [$envContent, $updated];
}

function installComposerDependencies($envContent, $updated)
{
    echo "Installing PHP dependencies...\n";
    passthru('composer install', $returnVar);

    if ($returnVar === 0) {
        echo "Composer dependencies installed.\n\n";
    } else {
        echo "Failed to install Composer dependencies.\n\n";
    }

    return [$envContent, $updated];
}

function linkCraftLaravel($envContent, $updated)
{
    $path = dirname(getcwd()).'/craft-laravel';

    if (! file_exists($path.'/composer.json')) {
        echo "Skipping Craft Laravel link (not found at {$path}).\n\n";

        return [$envContent, $updated];
    }

    echo "Linking Craft Laravel from {$path}...\n";
    passthru('composer link '.escapeshellarg($path), $returnVar);

    if ($returnVar === 0) {
        echo "Craft Laravel linked.\n\n";
    } else {
        echo "Failed to link Craft Laravel.\n\n";
    }

    return [$envContent, $updated];
}

function installNodeDependencies($envContent, $updated)
{
    echo "Installing Node dependencies with vp...\n";
    passthru('vp install', $returnVar);

    if ($returnVar === 0) {
        echo "Node dependencies installed.\n\n";
    } else {
        echo "Failed to install Node dependencies. Is VitePlus (vp) installed?\n\n";
    }

    return [$envContent, $updated];
}

function generateAppKey($envContent, $updated)
{
    echo "Generating application key...\n";
    passthru('php artisan key:generate', $returnVar);

    if ($returnVar === 0) {
        echo "Application key generated.\n\n";
    } else {
        echo "Failed to generate application key.\n\n";
    }

    return [$envContent, $updated];
}

function createDatabase($envContent, $updated)
{
    // SQLite: create the file if it doesn't exist
    if (preg_match('/DB_CONNECTION=sqlite/', $envContent)) {
        $dbPath = 'database/database.sqlite';
        if (! file_exists($dbPath)) {
            echo "Creating SQLite database...\n";
            touch($dbPath);
            echo "Database created at {$dbPath}.\n\n";
        }
    }

    return [$envContent, $updated];
}

function runMigrations($envContent, $updated)
{
    echo "Running database migrations...\n";
    passthru('php artisan migrate --force', $returnVar);

    if ($returnVar === 0) {
        echo "Migrations completed.\n\n";
    } else {
        echo "Failed to run migrations.\n\n";
    }

    return [$envContent, $updated];
}

function setupGitHooks($envContent, $updated)
{
    exec('git --version 2>/dev/null', $output, $code);
    $version = $output[0] ?? '';

    if ($code !== 0 || ! preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $matches)) {
        echo "Skipping Git hooks (Git not found).\n\n";

        return [$envContent, $updated];
    }

    $major = (int) $matches[1];
    $minor = (int) $matches[2];

    if ($major < 2 || ($major === 2 && $minor < 54)) {
        echo "Skipping Git config hooks (Git 2.54+ required, found {$version}).\n\n";

        return [$envContent, $updated];
    }

    echo "Setting up Git config-based hooks...\n";

    exec('git config --local --get core.hooksPath 2>/dev/null', $hooksPathOutput, $hooksPathReturnVar);
    if ($hooksPathReturnVar === 0 && ($hooksPathOutput[0] ?? '') === '.vite-hooks/_') {
        passthru('git config --local --unset core.hooksPath', $unsetHooksPathReturnVar);
        if ($unsetHooksPathReturnVar === 0) {
            echo "Removed legacy VitePlus hook path.\n";
        }
    }

    $hooks = [
        ['craft-lint', 'pre-commit', 'composer lint'],
        ['craft-frontend', 'pre-commit', 'vp check --fix'],
        ['craft-test', 'pre-push', 'composer test'],
        ['craft-analyse', 'pre-push', 'composer analyse'],
    ];

    $returnVar = 0;

    foreach ($hooks as [$name, $event, $command]) {
        passthru('git config --local --replace-all '.escapeshellarg("hook.{$name}.event").' '.escapeshellarg($event), $eventReturnVar);
        passthru('git config --local --replace-all '.escapeshellarg("hook.{$name}.command").' '.escapeshellarg($command), $commandReturnVar);

        if ($eventReturnVar !== 0 || $commandReturnVar !== 0) {
            $returnVar = 1;
        }
    }

    if ($returnVar === 0) {
        echo "Git hooks configured.\n\n";
    } else {
        echo "Failed to configure Git hooks.\n\n";
    }

    return [$envContent, $updated];
}

function linkSite($envContent, $updated)
{
    $cli = getLocalDevCli();
    if ($cli === null) {
        echo "Skipping site link (no Orbit or Herd found).\n\n";

        return [$envContent, $updated];
    }

    $name = ucfirst($cli);
    echo "Linking site with {$name}...\n";
    passthru($cli.' link', $returnVar);

    if ($returnVar === 0) {
        echo "Site linked with {$name}.\n\n";
    } else {
        echo "Failed to link site with {$name}.\n\n";
    }

    return [$envContent, $updated];
}

function buildAssets($envContent, $updated)
{
    echo "Building frontend assets...\n";
    passthru('vp build', $returnVar);

    if ($returnVar === 0) {
        echo "Assets built.\n\n";
    } else {
        echo "Failed to build assets.\n\n";
    }

    return [$envContent, $updated];
}

function askDeleteScript($envContent, $updated)
{
    echo 'Delete this setup script? (y/n) [y]: ';
    $answer = strtolower(trim(fgets(STDIN)));

    if (empty($answer) || $answer === 'y' || $answer === 'yes') {
        if (unlink(__FILE__)) {
            echo "Setup script deleted.\n";
        } else {
            echo "Failed to delete setup script.\n";
        }
    }

    return [$envContent, $updated];
}

runSetup($setupSteps);
