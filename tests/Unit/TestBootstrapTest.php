<?php

use Symfony\Component\Process\Process;

it('rejects cached test configuration before booting Laravel', function (): void {
    $root = sys_get_temp_dir().'/launch-test-bootstrap-'.bin2hex(random_bytes(8));
    mkdir($root.'/bootstrap/cache', 0755, true);
    mkdir($root.'/tests');
    copy(dirname(__DIR__).'/TestCase.php', $root.'/tests/TestCase.php');
    file_put_contents($root.'/bootstrap/cache/config.testing.php', '<?php throw new RuntimeException("Cache must not execute");');

    $code = <<<'PHP'
require $argv[1];
require $argv[2];
try {
    (new class('isolation') extends Tests\TestCase {})->createApplication();
    exit(1);
} catch (RuntimeException $exception) {
    fwrite(STDOUT, $exception->getMessage());
}
PHP;

    try {
        $process = new Process([
            PHP_BINARY, '-r', $code,
            dirname(__DIR__, 2).'/vendor/autoload.php', $root.'/tests/TestCase.php',
        ], $root);
        $process->setTimeout(30);
        $process->mustRun();

        expect($process->getOutput())->toContain('Remove the disposable bootstrap/cache/config.testing.php')
            ->not->toContain('Cache must not execute');
    } finally {
        unlink($root.'/tests/TestCase.php');
        unlink($root.'/bootstrap/cache/config.testing.php');
        rmdir($root.'/tests');
        rmdir($root.'/bootstrap/cache');
        rmdir($root.'/bootstrap');
        rmdir($root);
    }
});
