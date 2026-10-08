<?php

declare(strict_types=1);

use Launch\PestRunner;

require dirname(__DIR__).'/vendor/autoload.php';

// tests/bootstrap.php points the config cache at a nonexistent path, so there is
// no need to clear (and mutate) the checkout's cached config before a run.
exit((new PestRunner(dirname(__DIR__)))->run(array_values(array_slice($argv, 1))));
