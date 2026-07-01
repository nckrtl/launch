<?php

use Tests\TestCase;

pest()->extend(TestCase::class)
    ->beforeEach(function () {
        $this->withoutVite();
    })
    ->in('Feature');

expect()->extend('toBeOne', fn () => $this->toBe(1));
