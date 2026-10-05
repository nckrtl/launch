<?php

declare(strict_types=1);

return [
    // Keep generated package context opt-in, not appended to the shared AGENTS file.
    'agents' => [
        'claude_code' => ['guidelines_path' => '.ai/package-guidelines.md'],
        'codex' => ['guidelines_path' => '.ai/package-guidelines.md'],
    ],
];
