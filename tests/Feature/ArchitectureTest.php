<?php

declare(strict_types=1);

test('it does not leave debugging statements in production code', function () {
    expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
        ->not->toBeUsed();
});

test('it enforces strict types declaration', function () {
    expect('Spodnet\ComposerOutdatedChanges')
        ->toUseStrictTypes();
});
