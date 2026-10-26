<?php
declare(strict_types=1);

function tour_content_plan(): array
{
    $json = (string) file_get_contents(dirname(__DIR__) . '/storage/tour-package-content.json');
    return json_decode(ltrim($json, "\xEF\xBB\xBF"), true, 512, JSON_THROW_ON_ERROR);
}

function tour_content_import(PDO $db): string
{
    $sights = [
        'shimla' => ['The Ridge and Christ Church', 'Mall Road', 'Jakhu Temple', 'Viceregal Lodge', 'Annandale', 'Lakkar Bazaar', 'Chadwick Falls'],
        'kufri' => ['Green Valley', 'Himalayan Nature Park', 'Mahasu Peak', 'Kufri Fun World', 'Fagu apple orchards'],
        'chail' => ['Chail Palace', 'Kali Ka Tibba', 'Chail Cricket Ground', 'Sidh Baba Temple', 'Chail Wildlife Sanctuary', 'Sadhupul'],
        'manali' => ['Hadimba Devi Temple', 'Old Manali', 'Mall Road', 'Solang Valley', 'Vashisht Temple and hot springs', 'Club House', 'Tibetan Monastery'],
        'kasol' => ['Manikaran Sahib Gurudwara', 'Parvati River', 'Kasol market and cafes', 'Tosh village', 'Malana village'],
        'kinnaur' => ['Sangla', 'Chitkul', 'Kalpa', 'Reckong Peo', 'Khab confluence', 'Nako Lake and Monastery'],
        'spiti' => ['Tabo Monastery', 'Dhankar Monastery and Lake', 'Gue village', 'Key Monastery', 'Kibber', 'Chicham Bridge', 'Langza', 'Hikkim', 'Komic', 'Pin Valley', 'Kungri', 'Mudh'],
        'chandratal' => ['Chandratal Lake', 'Kunzum Pass', 'Losar', 'Atal Tunnel'],
        'kangra' => ['Kangra Fort', 'Shri Brajeshwari Devi Temple', 'Jwala Ji Temple', 'Baglamukhi Mata Temple'],
        'palampur' => ['Chamunda Devi Temple'],
        'amritsar' => ['Golden Temple', 'Jallianwala Bagh', 'Maharaja Ranjit Singh Summer Palace Museum', 'Attari-Wagah Border'],
        'jammu-katra' => ['Mata Vaishno Devi Shrine', 'Banganga'],
        'dalhousie-khajjiar' => ['Gandhi Chowk', 'Mall Road', 'St. Johns Church', 'Subhash Baoli', 'Panchpula', 'Satdhara Waterfalls', 'Chamera Lake', 'Dainkund Peak', 'Kalatop Wildlife Sanctuary'],
        'khajjiar' => ['Khajjiar Lake', 'Khajji Nag Temple'],
        'dharamshala' => ['HPCA Cricket Stadium', 'War Memorial', 'Kangra Tea Gardens', 'Kotwali Bazaar'],
        'mcleod-ganj' => ['Dalai Lama Temple Complex', 'Tibetan Museum', 'Bhagsunag Temple', 'Bhagsu Waterfall', 'St. John in the Wilderness Church', 'Dal Lake', 'Naddi'],
    ];
    $key = 'tour-package-docx-2026-10-26-v1';
    $db->exec('CREATE TABLE IF NOT EXISTS content_imports (import_key VARCHAR(190) PRIMARY KEY, previous_catalog LONGTEXT NOT NULL, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $db->beginTransaction();
    try {
        $check = $db->prepare('SELECT import_key FROM content_imports WHERE import_key = ? FOR UPDATE');
        $check->execute([$key]);
        if ($check->fetchColumn()) {
            $db->rollBack();
            return 'The Word document content has already been imported. No records were changed.';
        }
        $packages = app_db_rows_to_catalog($db->query('SELECT slug, payload FROM packages FOR UPDATE')->fetchAll(), 'packages');
        $destinations = app_db_rows_to_catalog($db->query('SELECT slug, payload FROM destination_groups FOR UPDATE')->fetchAll(), 'destination_groups');
        $before = ['packages' => [], 'destinations' => []];
        $savePackage = $db->prepare('INSERT INTO packages (slug, payload) VALUES (?, ?) ON DUPLICATE KEY UPDATE payload = VALUES(payload)');
        $saveDestination = $db->prepare('UPDATE destination_groups SET payload = ? WHERE slug = ?');
        foreach (tour_content_plan() as $patch) {
            $slug = $patch['slug'];
            $existing = $packages[$slug] ?? null;
            $before['packages'][$slug] = $existing;
            $links = $patch['destinations'];
            unset($patch['slug'], $patch['destinations']);
            if ($existing) {
                // Preserve prices, inclusions, images, status and fields absent from the source.
                unset($patch['image'], $patch['cat']);
                $record = array_replace($existing, $patch);
            } else {
                $mainDestination = $destinations[$links[0]] ?? null;
                if (!$mainDestination) throw new RuntimeException('Missing destination: ' . $links[0]);
                $record = array_replace([
                    'label' => 'Himachal holiday', 'popular' => false, 'active' => true,
                    'season' => 'Plan around your dates', 'facilities' => ['hotel', 'sightseeing', 'transport'],
                    'inclusions' => [], 'exclusions' => [], 'price_regular' => null, 'price_discount' => null,
                ], $patch);
                $record['image'] = package_curated_image($slug) ?? $mainDestination['image'];
            }
            if (preg_match('/(\d+)\s*days?\D+(\d+)\s*nights?/i', $record['duration'], $parts)) {
                $record['short'] = $parts[1] . 'D / ' . $parts[2] . 'N';
            }
            $savePackage->execute([$slug, app_db_json($record)]);
            foreach ($links as $destinationSlug) {
                if (!isset($destinations[$destinationSlug])) throw new RuntimeException('Missing destination: ' . $destinationSlug);
                if (!array_key_exists($destinationSlug, $before['destinations'])) $before['destinations'][$destinationSlug] = $destinations[$destinationSlug];
                $destination = $destinations[$destinationSlug];
                $destination['packages'] = array_values(array_unique(array_merge($destination['packages'] ?? [], [$slug])));
                $destination['see'] = array_values(array_unique(array_merge($destination['see'] ?? [], $sights[$destinationSlug] ?? [])));
                $destinations[$destinationSlug] = $destination;
                $saveDestination->execute([app_db_json($destination), $destinationSlug]);
            }
        }
        $audit = $db->prepare('INSERT INTO content_imports (import_key, previous_catalog) VALUES (?, ?)');
        $audit->execute([$key, app_db_json($before)]);
        $db->commit();
        return 'Word document imported: 3 packages added and 8 existing packages updated; ' . count($before['destinations']) . ' existing destinations updated. Original records were backed up in the database.';
    } catch (Throwable $exception) {
        if ($db->inTransaction()) $db->rollBack();
        throw $exception;
    }
}
