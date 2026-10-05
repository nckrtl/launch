<?php

declare(strict_types=1);

use Launch\PestRunner;

require dirname(__DIR__).'/vendor/autoload.php';

$projectRoot = dirname(__DIR__);
$passthrough = array_values(array_slice($argv, 1));

// The forced test cache path makes clearing the managed app's cache unnecessary.
exit(new PestRunner($projectRoot)->run($passthrough));
