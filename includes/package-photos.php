<?php
/** Locally hosted destination photography and its source/licensing information. */
function package_photo_library(): array
{
    static $library;
    if ($library === null) {
        $library = json_decode((string) file_get_contents(__DIR__ . '/package-photos.json'), true, 512, JSON_THROW_ON_ERROR);
    }
    return $library;
}

function package_curated_image(string $slug): ?string
{
    return package_photo_library()['routes'][$slug] ?? null;
}

function package_photo_credit(string $image): ?array
{
    return package_photo_library()['photos'][$image] ?? null;
}
