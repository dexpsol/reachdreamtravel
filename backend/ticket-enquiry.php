<?php
declare(strict_types=1);

function ticket_travel_classes(): array
{
    return [
        'Railway' => ['Sleeper', 'AC 3 Tier', 'AC 2 Tier', 'AC First Class', 'AC Chair Car', 'Second Sitting'],
        'Air' => ['Economy', 'Premium Economy', 'Business', 'First Class'],
        'Bus' => ['AC Seater', 'Non-AC Seater', 'AC Sleeper', 'Non-AC Sleeper'],
    ];
}

function ticket_enquiry_error(array $values): ?string
{
    $classes = ticket_travel_classes();
    $type = $values['ticket_type'] ?? '';
    if (!isset($classes[$type])) return 'Choose Railway, Air or Bus tickets.';
    foreach (['journey_from' => 'departure city or station', 'journey_to' => 'destination city or station'] as $key => $label) {
        if (($values[$key] ?? '') === '') return 'Please enter your ' . $label . '.';
    }
    if (strcasecmp($values['journey_from'], $values['journey_to']) === 0) return 'Departure and destination must be different.';
    if (!preg_match('/^[0-9]{10}$/D', $values['phone'] ?? '')) return 'Please enter a valid 10 digit phone number.';
    if (!filter_var($values['passengers'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]])) return 'Enter a passenger count between 1 and 100.';
    $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Kolkata'));
    $validDate = static function (string $value): bool {
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $value)) return false;
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $parsed !== false && $parsed->format('Y-m-d') === $value;
    };
    $date = $values['date'] ?? '';
    if (!$validDate($date) || $date < $today->format('Y-m-d')) return 'Choose a valid travel date that is today or later.';
    $journey = $values['journey_type'] ?? '';
    if (!in_array($journey, ['One way', 'Return'], true)) return 'Choose a journey type.';
    if ($journey === 'Return' && (!$validDate($values['return_date'] ?? '') || $values['return_date'] < $date)) return 'Choose a return date on or after your travel date.';
    if (!in_array($values['travel_class'] ?? '', array_merge(['No preference'], $classes[$type]), true)) return 'Choose a valid travel class for your ticket type.';
    return null;
}
