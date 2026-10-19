<?php
declare(strict_types=1);

function package_facility_options(): array
{
    return [
        'hotel' => ['Hotel', 'fa-hotel'],
        'food' => ['Food', 'fa-utensils'],
        'sightseeing' => ['Sightseeing', 'fa-binoculars'],
        'transport' => ['Transport', 'fa-car-side'],
    ];
}

function package_stay_options(): array
{
    return ['standard' => 'Standard hotel', 'deluxe' => 'Deluxe hotel', 'premium' => 'Premium hotel / resort',
        'cottage' => 'Cottage / villa', 'homestay' => 'Homestay', 'camp' => 'Camp / glamping'];
}

function package_option_defaults(): array
{
    return [
        'facilities' => array_keys(package_facility_options()),
        'inclusions' => ['Accommodation as per the selected stay category', 'Meals as specified in the confirmed itinerary',
            'Sightseeing as per the itinerary', 'Transport with driver as per the itinerary'],
        'exclusions' => ['Airfare and train tickets', 'Personal expenses, shopping and tips',
            'Entry tickets, permits and optional activities unless specified', 'Services not listed in the confirmed inclusions'],
    ];
}

function package_options_from_post(array $post): array
{
    $facilities = $post['facilities'] ?? [];
    if (!is_array($facilities) || array_filter($facilities, static fn($value) => !is_string($value))
        || array_diff($facilities, array_keys(package_facility_options()))) {
        throw new RuntimeException('Choose valid package facilities.');
    }
    $lines = static function ($value): array {
        if (!is_string($value)) throw new RuntimeException('Enter inclusions and exclusions as text, one item per line.');
        return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $value) ?: []), static fn($line) => $line !== ''));
    };
    return ['facilities' => array_values(array_unique($facilities)),
        'inclusions' => $lines($post['inclusions'] ?? ''), 'exclusions' => $lines($post['exclusions'] ?? '')];
}
