<?php

declare(strict_types=1);

namespace Launch;

use RuntimeException;

class AgentContextRefresher
{
    public function __construct(private readonly string $projectRoot) {}

    public function refresh(string $guidelines): void
    {
        $this->validate($guidelines);
        $destination = $this->projectRoot.'/.ai/package-guidelines.md';
        $directory = dirname($destination);

        if (! is_dir($directory)) {
            throw new RuntimeException('Agent context directory does not exist.');
        }

        // Same-directory rename is atomic: an interrupted writer never truncates
        // the published file. Only the disposable candidate can be incomplete.
        $candidate = tempnam($directory, '.package-guidelines-');
        if ($candidate === false || dirname($candidate) !== realpath($directory)) {
            throw new RuntimeException('Unable to create an agent context candidate.');
        }

        try {
            $this->writeCandidate($candidate, $guidelines);
            $written = file_get_contents($candidate);
            if ($written !== $guidelines) {
                throw new RuntimeException('Agent context candidate failed read-back verification.');
            }

            $this->validate($written);

            if (! chmod($candidate, 0644)) {
                throw new RuntimeException('Unable to set agent context candidate permissions.');
            }

            $this->publishCandidate($candidate, $destination);
        } finally {
            if (is_file($candidate)) {
                unlink($candidate);
            }
        }
    }

    protected function writeCandidate(string $path, string $content): void
    {
        $stream = fopen($path, 'wb');
        if ($stream === false) {
            throw new RuntimeException('Unable to open agent context candidate.');
        }

        try {
            $offset = 0;
            while ($offset < strlen($content)) {
                $bytes = fwrite($stream, substr($content, $offset));
                if ($bytes === false || $bytes === 0) {
                    throw new RuntimeException('Unable to write complete agent context candidate.');
                }

                $offset += $bytes;
            }

            if (! fflush($stream) || ! fsync($stream)) {
                throw new RuntimeException('Unable to flush agent context candidate.');
            }
        } finally {
            fclose($stream);
        }
    }

    protected function publishCandidate(string $candidate, string $destination): void
    {
        if (! rename($candidate, $destination)) {
            throw new RuntimeException('Unable to publish agent context candidate.');
        }
    }

    private function validate(string $guidelines): void
    {
        foreach ([
            '<laravel-boost-guidelines>',
            '=== foundation rules ===',
            '=== laravel/core rules ===',
            '=== inertia-laravel/core rules ===',
            '=== wayfinder/core rules ===',
            '=== nckrtl/launch-laravel/core rules ===',
            '</laravel-boost-guidelines>',
        ] as $required) {
            if (! str_contains($guidelines, $required)) {
                throw new RuntimeException('Incomplete agent context: missing '.$required);
            }
        }

        if (! str_starts_with($guidelines, "<laravel-boost-guidelines>\n")
            || ! str_ends_with($guidelines, "</laravel-boost-guidelines>\n")
            || substr_count($guidelines, '<laravel-boost-guidelines>') !== 1
            || substr_count($guidelines, '</laravel-boost-guidelines>') !== 1) {
            throw new RuntimeException('Invalid agent context managed block.');
        }
    }
}
