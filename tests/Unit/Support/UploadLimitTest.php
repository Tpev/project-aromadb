<?php

use App\Support\UploadLimit;

test('upload limit parser understands php ini shorthand sizes', function () {
    expect(UploadLimit::parseIniBytes('2M'))->toBe(2 * 1024 * 1024)
        ->and(UploadLimit::parseIniBytes('512K'))->toBe(512 * 1024)
        ->and(UploadLimit::parseIniBytes('1G'))->toBe(1024 * 1024 * 1024)
        ->and(UploadLimit::parseIniBytes('-1'))->toBeNull();
});

test('upload limit formatter returns readable french labels', function () {
    expect(UploadLimit::formatBytes(3562 * 1024))->toBe('3,5 Mo')
        ->and(UploadLimit::formatBytes(2 * 1024 * 1024))->toBe('2 Mo');
});
