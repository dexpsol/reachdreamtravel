<?php
/**
 * Shared site content: contact details, packages, destinations, vehicles and gallery.
 * Edit values here once and every page picks them up.
 *
 * Photos in assets/images/ and the fleet-*.jpg vehicle photos are free to use
 * without attribution (Unsplash licence or public domain).
 */

$site = [
    'name'      => 'Reach Dream Travel',
    'phone'     => '+91 98883 51723',
    'phoneLink' => '+919888351723',
    'whatsapp'  => '919780434402', // Also update whatsappNumber in script.js.
    'email'     => 'reachdreamtravel@gmail.com',
];

/*
 * Approximate road times from our base. Real times depend on traffic, weather and stops.
 */
$driveTimes = [
    ['Shimla', '7–8 hrs', 'shimla'],
    ['Manali', '9–10 hrs', 'manali'],
    ['Kasol', 'about 9 hrs', 'kasol'],
    ['Kinnaur', '2 days (night in Shimla)', 'kinnaur'],
    ['Spiti Valley', '3 days (via Kinnaur)', 'spiti'],
    ['Chandratal', '2 days (night in Manali)', 'chandratal'],
];

/** Escape a value for HTML output. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_price($amount): string
{
    return '₹' . number_format((int) $amount, 0, '.', ',');
}

/** WhatsApp link with a prefilled message. */
function wa_link(string $message = 'Hello Reach Dream Travel, I would like to plan a trip.'): string
{
    global $site;
    return 'https://wa.me/' . $site['whatsapp'] . '?text=' . rawurlencode($message);
}

/** Resolve a photo to its category folder and optional thumbnail. */
function img(string $name, bool $small = false): string
{
    $fileName = basename($name);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $baseName = $extension !== '' ? pathinfo($fileName, PATHINFO_FILENAME) : $fileName;
    $stayPrefixes = ['camping', 'cottage', 'hotel-', 'kinnaur-camps', 'room-', 'tent-', 'tents-'];
    $isStay = false;
    foreach ($stayPrefixes as $prefix) {
        if (str_starts_with($baseName, $prefix)) {
            $isStay = true;
            break;
        }
    }
    $folder = $baseName === 'road-van' ? 'car' : ($isStay ? 'stays' : 'destinations');
    $absoluteFolder = dirname(__DIR__) . '/assets/images/' . $folder;
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        $extension = 'jpg';
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $candidate) {
            if (is_file($absoluteFolder . '/' . $baseName . '.' . $candidate)) {
                $extension = $candidate;
                break;
            }
        }
    }
    $imagePath = 'assets/images/' . $folder . '/' . $baseName . '.' . $extension;
    if ($small) {
        $thumbnailPath = 'assets/images/' . $folder . '/' . $baseName . '-sm.jpg';
        if (is_file($absoluteFolder . '/' . $baseName . '-sm.jpg')) {
            return $thumbnailPath;
        }
    }
    return $imagePath;
}

/* ---------------------------------------------------------------------------
 * Tour packages. Every package includes pickup and drop.
 * 'cat' drives the filters on packages.php.
 * ------------------------------------------------------------------------- */
$packageCategories = ['all' => 'All packages', 'road' => 'Long road trips', 'hills' => 'Hill stations', 'nature' => 'Nature & adventure', 'heritage' => 'Heritage & culture', 'city' => 'City escapes', 'north' => 'Northern India'];

$customRoute = static function (string $title, string $label, string $category, string $season, string $route, string $overview, array $highlights, string $image): array {
    return [
        'title' => $title, 'label' => $label, 'cat' => $category,
        'duration' => 'Custom duration', 'short' => 'Flexible', 'popular' => false,
        'image' => $image, 'difficulty' => 'Planned around your group', 'season' => $season,
        'start' => 'Pickup point by arrangement', 'end' => 'Drop-off point by arrangement',
        'route' => $route, 'overview' => $overview, 'highlights' => $highlights,
        'days' => [['Plan your trip', 'Share your dates, group size and interests. We’ll suggest a route and pace that fit your plans and the season.']],
    ];
};

$packages = [
    'shimla-spiti-kinnaur' => [
        'title' => 'Spiti Valley via Kinnaur', 'label' => 'Our biggest road trip', 'cat' => 'road',
        'duration' => '8 days · 7 nights', 'short' => '8D · 7N', 'popular' => true,
        'image' => 'spiti-key-sunset', 'difficulty' => 'Moderate', 'season' => 'June – October',
        'start' => 'Your home or hotel', 'end' => 'Your home or hotel',
        'route' => 'Shimla · Kalpa · Nako · Tabo · Kaza · Manali',
        'overview' => 'A full loop through the high Himalaya. We drive to Shimla, then through Kinnaur’s apple orchards into Spiti’s cold desert of old monasteries and clear night skies. We come back through Manali.',
        'highlights' => ['Kinner Kailash mountain views from Kalpa', 'Nako Lake and village', 'Tabo Monastery, over 1,000 years old', 'Key Monastery above the Spiti river', 'Kunzum Pass on the way to Manali', 'Nights in simple, cosy homestays'],
        'days' => [
            ['Drive to Shimla', 'Morning pickup from your home or hotel. About 7–8 hours by road. Evening walk on Mall Road.'],
            ['Shimla to Sarahan', 'Drive along the Sutlej river to Sarahan and visit the Bhimakali Temple.'],
            ['Sarahan to Kalpa', 'Through apple orchards to Kalpa. See the Kinner Kailash peaks at sunset.'],
            ['Kalpa to Tabo', 'Stop at Nako Lake, then continue to Tabo and its old monastery.'],
            ['Tabo to Kaza', 'Visit Dhankar Monastery on its cliff, then reach Kaza, the main town of Spiti.'],
            ['Around Kaza', 'Key Monastery, Kibber village and the high village of Langza.'],
            ['Kaza to Manali', 'Cross Kunzum Pass. Visit Chandratal Lake if the road is open.'],
            ['Return journey', 'Drive back from Manali. Drop at your home or hotel.'],
        ],
    ],
    'shimla-kullu-manali' => [
        'title' => 'Shimla · Kullu · Manali', 'label' => 'Most loved hill trip', 'cat' => 'hills',
        'duration' => '6 days · 5 nights', 'short' => '6D · 5N', 'popular' => false,
        'image' => 'solang', 'difficulty' => 'Easy', 'season' => 'All year (snow Dec – Feb)',
        'start' => 'Your home or hotel', 'end' => 'Your home or hotel',
        'route' => 'Shimla · Kufri · Kullu · Manali · Solang · Atal Tunnel',
        'overview' => 'A family-friendly loop with time in Shimla and Manali, mountain scenery, rivers and a drive through the Atal Tunnel.',
        'highlights' => ['Mall Road and the Ridge in Shimla', 'Kufri hills', 'River rafting stop in Kullu', 'Hidimba Temple and Old Manali', 'Solang Valley snow', 'Drive through the Atal Tunnel'],
        'days' => [
            ['Drive to Shimla', 'Pickup from your home or hotel. About 7–8 hours by road. Evening free in Shimla.'],
            ['Shimla and Kufri', 'The Ridge, Jakhu Temple and Mall Road, plus a trip up to Kufri.'],
            ['Shimla to Manali', 'Drive through the Kullu valley (about 8 hours). Optional river rafting on the way.'],
            ['Manali sightseeing', 'Hidimba Temple, Vashisht hot springs, Old Manali and Mall Road.'],
            ['Solang & Atal Tunnel', 'Snow fun at Solang Valley and a drive through the Atal Tunnel to Sissu (weather permitting).'],
            ['Return journey', 'Drive back from Manali. Drop at your home or hotel.'],
        ],
    ],
    'shimla-local' => [
        'title' => 'Shimla short break', 'label' => 'Quick weekend trip', 'cat' => 'hills',
        'duration' => '4 days · 3 nights', 'short' => '4D · 3N', 'popular' => false,
        'image' => 'shimla-city', 'difficulty' => 'Easy', 'season' => 'All year',
        'start' => 'Your home or hotel', 'end' => 'Your home or hotel',
        'route' => 'Shimla · Mall Road · Kufri · Narkanda',
        'overview' => 'The easiest hill break. Cool air, old British buildings, pine forests and a day out to Kufri and Narkanda.',
        'highlights' => ['Mall Road and the Ridge', 'Jakhu Temple', 'Viceregal Lodge', 'Kufri and Narkanda day trip'],
        'days' => [
            ['Drive to Shimla', 'Pickup from your home or hotel. About 7–8 hours by road.'],
            ['Shimla sightseeing', 'Jakhu Temple, the Ridge, Christ Church, Viceregal Lodge and Mall Road.'],
            ['Kufri & Narkanda', 'A day trip to Kufri and Narkanda for mountain views (and snow in winter).'],
            ['Return journey', 'Relaxed breakfast, then drive back home.'],
        ],
    ],
    'shakti-peeths-himachal' => [
        'title' => 'Famous Shakti Peeths & Goddess Temples', 'label' => 'A sacred Himachal circuit', 'cat' => 'heritage',
        'duration' => '5 days · 4 nights', 'short' => '5D · 4N', 'popular' => false,
        'image' => 'hidimba', 'difficulty' => 'Easy', 'season' => 'All year',
        'start' => 'Your home or hotel', 'end' => 'Your home or hotel',
        'route' => 'Kangra · Bankhandi · Chintpurni · Naina Devi',
        'overview' => 'Visit six revered goddess temples across Kangra, Una and Bilaspur. See Jwalamukhi’s natural flame, hilltop Naina Devi and the historic shrines of Kangra on a flexible pilgrimage route.',
        'highlights' => ['Jwalamukhi Temple’s naturally burning flame', 'Naina Devi Temple overlooking the valley', 'Historic Bajreshwari Devi Temple', 'Chamunda Devi Temple beside the Baner River', 'Chintpurni Temple in Una', 'Maa Baglamukhi Temple in Bankhandi'],
        'days' => [
            ['Arrive in Kangra', 'Pickup from your home or hotel and travel to Kangra. Visit Bajreshwari Devi Temple, an ancient shrine rebuilt after an earthquake.'],
            ['Jwalamukhi & Chamunda', 'Visit Jwalamukhi Temple, famed for its blue flame emerging from the rock, then continue to Chamunda Devi Temple on the banks of the Baner River.'],
            ['Maa Baglamukhi', 'Travel to Bankhandi village, around 40 km from Kangra, to visit the highly revered Maa Baglamukhi Temple.'],
            ['Chintpurni', 'Continue to Una and visit Chintpurni Temple, where devotees pray for relief from worries and grief.'],
            ['Naina Devi & return', 'Visit hilltop Naina Devi Temple in Bilaspur, traditionally believed to mark the place where Sati’s eyes fell, then begin your return journey.'],
        ],
    ],
    'kinnaur-chitkul' => [
        'title' => 'Kinnaur & Chitkul', 'label' => 'Villages near the Tibet border', 'cat' => 'road',
        'duration' => '6 days · 5 nights', 'short' => '6D · 5N', 'popular' => false,
        'image' => 'kinnaur-sangla', 'difficulty' => 'Easy – moderate', 'season' => 'April – November',
        'start' => 'Your home or hotel', 'end' => 'Your home or hotel',
        'route' => 'Shimla · Sangla · Chitkul · Kalpa',
        'overview' => 'A shorter trip into the high mountains. Visit Chitkul, the last village on the road to Tibet, and wake up to snow peaks in Kalpa.',
        'highlights' => ['Sangla valley and the Baspa river', 'Chitkul village', 'Sunrise over Kinner Kailash from Kalpa', 'Apple orchards (Aug – Oct)'],
        'days' => [
            ['Drive to Shimla', 'Pickup from your home or hotel. About 7–8 hours by road.'],
            ['Shimla to Sangla', 'Along the Sutlej and into the green Sangla valley.'],
            ['Chitkul', 'Morning in Chitkul village by the river. Evening back in Sangla.'],
            ['Sangla to Kalpa', 'Short drive to Kalpa. Views of the Kinner Kailash peaks.'],
            ['Kalpa to Shimla', 'Drive back to Shimla with stops along the river.'],
            ['Return journey', 'Drive back from Shimla. Drop at your home or hotel.'],
        ],
    ],
    'kasol-parvati' => [
        'title' => 'Kasol & Parvati Valley', 'label' => 'Riverside rest', 'cat' => 'nature',
        'duration' => '4 days · 3 nights', 'short' => '4D · 3N', 'popular' => false,
        'image' => 'kasol-town', 'difficulty' => 'Easy', 'season' => 'March – June, Sept – Nov',
        'start' => 'Your home or hotel', 'end' => 'Your home or hotel',
        'route' => 'Kasol · Manikaran · Tosh · Chalal',
        'overview' => 'Slow, relaxing days by the Parvati river — pine forests, cafés, hot springs and short village walks.',
        'highlights' => ['Riverside cafés in Kasol', 'Manikaran Sahib Gurudwara and hot springs', 'Forest walk to Chalal', 'Tosh village views'],
        'days' => [
            ['Drive to Kasol', 'Pickup from your home or hotel. About 9 hours by road. Evening by the river.'],
            ['Manikaran & Tosh', 'Visit Manikaran Sahib, then drive up to Tosh village.'],
            ['Chalal & free time', 'Easy forest walk to Chalal and a free afternoon in Kasol.'],
            ['Return journey', 'Drive back from Kasol. Drop at your home or hotel.'],
        ],
    ],
    'chandratal-lahaul' => [
        'title' => 'Chandratal & Lahaul', 'label' => 'Through the Atal Tunnel', 'cat' => 'nature',
        'duration' => '5 days · 4 nights', 'short' => '5D · 4N', 'popular' => false,
        'image' => 'chandratal', 'difficulty' => 'Moderate', 'season' => 'Mid June – early October',
        'start' => 'Your home or hotel', 'end' => 'Your home or hotel',
        'route' => 'Manali · Atal Tunnel · Sissu · Chandratal',
        'overview' => 'Drive through the Atal Tunnel into Lahaul, then on a rough mountain road to Chandratal — a blue lake at 4,300 m. We use a 4×4 for the last part.',
        'highlights' => ['Atal Tunnel and Sissu waterfall', 'Chandra river valley', 'Night in camps under the stars', 'Sunrise at Chandratal Lake'],
        'days' => [
            ['Drive to Manali', 'Pickup from your home or hotel. About 9–10 hours by road.'],
            ['Manali to Sissu', 'Through the Atal Tunnel into Lahaul. Afternoon at Sissu waterfall.'],
            ['Sissu to Chandratal', 'Along the Chandra river to the lake. Night in camps nearby.'],
            ['Chandratal to Manali', 'Sunrise at the lake, then back to Manali.'],
            ['Return journey', 'Drive back from Manali. Drop at your home or hotel.'],
        ],
    ],
    'golden-triangle-delhi-agra-jaipur' => [
        'title' => 'Golden Triangle', 'label' => 'Classic India loop', 'cat' => 'heritage',
        'duration' => '4 days · 3 nights', 'short' => '4D · 3N', 'popular' => true,
        'image' => 'taj-mahal-agra', 'difficulty' => 'Easy', 'season' => 'All year',
        'start' => 'Delhi airport or hotel', 'end' => 'Delhi airport or hotel',
        'route' => 'Delhi · Agra · Jaipur',
        'overview' => 'A compact India circuit with the capital, a Mughal masterpiece and a royal Rajasthani city. You see the key monuments without the long travel fatigue.',
        'highlights' => ['Red Fort and Jama Masjid in Delhi', 'Sunrise at the Taj Mahal', 'Agra Fort and local artisan streets', 'Amber Fort and Hawa Mahal in Jaipur', 'Heritage hotel stays'],
        'days' => [
            ['Arrival in Delhi', 'Pick-up from the airport or your hotel. Easy city check-in and an evening walk around Connaught Place or Chandni Chowk.'],
            ['Delhi to Agra', 'Drive to Agra after breakfast. Visit Agra Fort and evening markets.'],
            ['Taj Mahal and Jaipur', 'Early sunrise at the Taj Mahal, then continue to Jaipur with a stop for lunch.'],
            ['Jaipur highlights', 'Amber Fort, City Palace and local bazaars before your return to Delhi.'],
        ],
    ],
    'rajasthan-heritage-trail' => [
        'title' => 'Rajasthan Heritage Trail', 'label' => 'Palaces, forts & desert nights', 'cat' => 'heritage',
        'duration' => '6 days · 5 nights', 'short' => '6D · 5N', 'popular' => false,
        'image' => 'hawa-mahal-jaipur', 'difficulty' => 'Easy', 'season' => 'October – March',
        'start' => 'Delhi airport or hotel', 'end' => 'Delhi airport or hotel',
        'route' => 'Jaipur · Jodhpur · Jaisalmer · Udaipur',
        'overview' => 'A royal route through Rajasthan’s forts, havelis and desert towns. The trip balances heritage sightseeing with relaxed evenings and local food.',
        'highlights' => ['Amber Fort and City Palace in Jaipur', 'Mehrangarh Fort in Jodhpur', 'Golden sands of Jaisalmer', 'Lake Palace views in Udaipur', 'Rajasthani folk culture and cuisine'],
        'days' => [
            ['Arrive in Jaipur', 'Meet in Jaipur and settle into a heritage stay. Evening walk in the bazaars.'],
            ['Jaipur city tour', 'Amber Fort, Hawa Mahal, Jantar Mantar and local craft markets.'],
            ['Jaipur to Jodhpur', 'Drive to Jodhpur for fort views and the blue city streets.'],
            ['Jodhpur to Jaisalmer', 'Continue to the golden desert city and enjoy a sunset desert experience.'],
            ['Jaisalmer to Udaipur', 'Drive onward to Udaipur for lakeside evenings and palace views.'],
            ['Return journey', 'Drive back to Delhi or depart from Udaipur depending on your plan.'],
        ],
    ],
    'delhi-agra-weekend' => [
        'title' => 'Delhi & Agra Weekend', 'label' => 'Quick cultural escape', 'cat' => 'city',
        'duration' => '3 days · 2 nights', 'short' => '3D · 2N', 'popular' => false,
        'image' => 'taj-mahal-agra', 'difficulty' => 'Easy', 'season' => 'All year',
        'start' => 'Delhi airport or hotel', 'end' => 'Delhi airport or hotel',
        'route' => 'Delhi · Agra',
        'overview' => 'A smooth short trip for couples, families or friends who want a fast cultural getaway with iconic monuments, great food and no complicated planning.',
        'highlights' => ['Old Delhi heritage lanes', 'Red Fort and India Gate', 'Sunrise Taj Mahal visit', 'Agra Fort and marble market'],
        'days' => [
            ['Arrival in Delhi', 'Airport or hotel pickup and a guided evening in central Delhi.'],
            ['Delhi sightseeing', 'Old Delhi, Raj Ghat, India Gate and local food stops.'],
            ['Agra day trip', 'Drive to Agra for the Taj Mahal, Agra Fort and a relaxed evening return to Delhi.'],
        ],
    ],
];

$packages += [
    'nainital' => $customRoute('Nainital Holiday', 'Lake views & easy hill walks', 'north', 'March–June, September–November', 'Naini Lake · Mall Road · Naina Devi Temple', 'A relaxed Kumaon hill break centred on Naini Lake, with time for boating, Mall Road and a visit to Naina Devi Temple.', ['Boating on Naini Lake', 'Walk along Mall Road', 'Visit Naina Devi Temple'], 'manali-valley'),
    'mussoorie' => $customRoute('Mussoorie Holiday', 'The Queen of Hills', 'north', 'September–June', 'Kempty Falls · Gun Hill · Mall Road', 'Take in Mussoorie’s mountain viewpoints and waterfalls, with scenic walks and unhurried time on Mall Road.', ['Kempty Falls', 'Gun Hill viewpoint', 'Scenic walks along Mall Road'], 'shimla-city'),
    'rishikesh' => $customRoute('Rishikesh Getaway', 'River, yoga & adventure', 'north', 'February–May, August–October', 'Ganges · Laxman Jhula · Ganga Aarti', 'A riverside escape with spiritual landmarks and optional adventure activities. Rafting and other activities depend on season and local conditions.', ['White-water rafting, when available', 'Laxman Jhula', 'Evening Ganga Aarti'], 'forest-river'),
    'auli' => $customRoute('Auli Snow & Ski Escape', 'High Himalayan views', 'nature', 'November–March for snow and skiing', 'Auli · Himalayan viewpoints', 'A winter trip to Auli, with the option to plan around snow conditions, ski activities and cable car visits.', ['Skiing, subject to snow and operations', 'Cable car ride, subject to operations', 'Views of snow-covered Himalayan peaks'], 'snow-peaks'),
    'jim-corbett' => $customRoute('Jim Corbett Wildlife Trip', 'Forest & wildlife', 'nature', 'November–June; safari zones vary', 'Jim Corbett National Park · Kosi River', 'Plan a wildlife-focused break around available safari zones, park rules and your travel dates.', ['Jeep safari, subject to permits and zone availability', 'Wildlife spotting', 'Kosi River scenery'], 'forest-river'),
    'kashmir-valley' => $customRoute('Kashmir & Jammu Tour', 'Lakes, gardens & mountain valleys', 'north', 'April–October for sightseeing; November–February for snow', 'Srinagar · Gulmarg · Pahalgam · Sonamarg · Jammu · Patnitop', 'Build a Kashmir and Jammu journey around Srinagar’s lake and gardens, mountain scenery and the places that interest you most. Winter activities depend on weather and local operations.', ['Dal Lake houseboats and Shikara rides', 'Mughal Gardens in Srinagar', 'Gulmarg meadows and cable car', 'Pahalgam and the Lidder River', 'Sonamarg glaciers and trekking routes', 'Add Patnitop to your route'], 'snow-peaks'),
    'dharamshala-mcleodganj' => $customRoute('Dharamshala & McLeod Ganj', 'Tibetan culture & pine forests', 'hills', 'Plan around your dates', 'Dharamshala · McLeod Ganj', 'A flexible Himachal stay for Tibetan culture, pine-forest walks and time in the mountain town of McLeod Ganj.', ['Tibetan art and culture', 'McLeod Ganj', 'Pine-forest surroundings'], 'manali-valley'),
    'dalhousie-khajjiar' => $customRoute('Dalhousie & Khajjiar', 'Colonial charm & open meadows', 'hills', 'Plan around your dates', 'Dalhousie · Khajjiar', 'Pair Dalhousie’s colonial-era character with a visit to Khajjiar’s broad green meadows.', ['Dalhousie town walks', 'Khajjiar meadows', 'Views across the Dhauladhar range'], 'meadow'),
    'jammu-katra-patnitop' => $customRoute('Jammu, Katra & Patnitop', 'Pilgrimage & mountain air', 'heritage', 'Plan around your dates and local conditions', 'Jammu City · Katra · Vaishno Devi · Patnitop', 'Combine time in Jammu with a visit to Katra and the Vaishno Devi shrine, then add a relaxing stay in Patnitop.', ['Jammu City', 'Katra and Vaishno Devi shrine visit', 'Patnitop hill-station break'], 'snow-peaks'),
];

$defaultPackages = $packages;
require_once dirname(__DIR__) . '/backend/package-store.php';
$packages = package_catalog($packages);
$routeImages = [
    'delhi-agra-weekend' => 'taj-mahal-agra',
    'golden-triangle-delhi-agra-jaipur' => 'taj-mahal-agra',
    'rajasthan-heritage-trail' => 'hawa-mahal-jaipur',
];
foreach ($routeImages as $slug => $image) {
    if (isset($packages[$slug]) && empty($packages[$slug]['image_customized'])) {
        $packages[$slug]['image'] = $image;
    }
}

/* ---------------------------------------------------------------------------
 * Destinations (destinations.php, footer and about page).
 * ------------------------------------------------------------------------- */
$destinations = [
    'shimla' => [
        'name' => 'Shimla', 'tagline' => 'The Queen of Hills', 'image' => 'shimla-church', 'altitude' => '2,200 m', 'best' => 'All year', 'drive' => '7–8 hrs by road',
        'text' => 'The closest big hill station. Old British buildings, Mall Road, pine forests and the famous toy train.',
        'see' => ['The Ridge & Christ Church', 'Mall Road', 'Jakhu Temple', 'Viceregal Lodge', 'Kufri & Narkanda'],
        'packages' => ['shimla-local', 'shimla-kullu-manali'],
    ],
    'manali' => [
        'name' => 'Manali & Solang', 'tagline' => 'Snow and adventure', 'image' => 'manali-valley', 'altitude' => '2,050 m', 'best' => 'All year (snow Dec – Feb)', 'drive' => '9–10 hrs by road',
        'text' => 'Snow, rivers, old wooden temples and adventure sports. Also the starting point for the Atal Tunnel and Lahaul.',
        'see' => ['Hidimba Devi Temple', 'Old Manali', 'Solang Valley', 'Atal Tunnel & Sissu', 'Vashisht hot springs'],
        'packages' => ['shimla-kullu-manali', 'chandratal-lahaul'],
    ],
    'kasol' => [
        'name' => 'Kullu & Kasol', 'tagline' => 'Life by the river', 'image' => 'kasol-river', 'altitude' => '1,580 m', 'best' => 'March – June, Sept – Nov', 'drive' => 'About 9 hrs by road',
        'text' => 'Pine forests, a fast blue river and slow, peaceful days. Great for short walks, hot springs and cafés.',
        'see' => ['Kasol', 'Manikaran Sahib', 'Tosh & Chalal', 'Kullu shawl makers'],
        'packages' => ['kasol-parvati', 'shimla-kullu-manali'],
    ],
    'kinnaur' => [
        'name' => 'Kinnaur', 'tagline' => 'Apple orchards & snow peaks', 'image' => 'kinnaur-autumn', 'altitude' => '2,900 m (Kalpa)', 'best' => 'April – November', 'drive' => '2 days by road',
        'text' => 'Green valleys, apple orchards and wooden villages under the snowy Kinner Kailash mountains.',
        'see' => ['Sarahan', 'Sangla & Chitkul', 'Kalpa', 'Nako Lake'],
        'packages' => ['kinnaur-chitkul', 'shimla-spiti-kinnaur'],
    ],
    'spiti' => [
        'name' => 'Spiti Valley', 'tagline' => 'The cold desert', 'image' => 'spiti-key', 'altitude' => '3,800 m (Kaza)', 'best' => 'June – October', 'drive' => '3 days by road',
        'text' => 'A high, dry mountain valley with monasteries on cliffs, tiny villages and some of the clearest night skies in India.',
        'see' => ['Key Monastery', 'Dhankar Monastery', 'Tabo', 'Langza & Kibber', 'Pin Valley'],
        'packages' => ['shimla-spiti-kinnaur'],
    ],
    'chandratal' => [
        'name' => 'Chandratal & Lahaul', 'tagline' => 'The moon lake', 'image' => 'chandratal-b', 'altitude' => '4,300 m', 'best' => 'Mid June – early October', 'drive' => '2 days by road',
        'text' => 'A blue lake shaped like a half moon, open only in summer. Reached through the Atal Tunnel from Manali.',
        'see' => ['Chandratal Lake', 'Sissu', 'Batal', 'Chandra river valley'],
        'packages' => ['chandratal-lahaul'],
    ],
    'delhi' => [
        'name' => 'Delhi', 'tagline' => 'India’s capital energy', 'image' => 'shimla-city', 'altitude' => '216 m', 'best' => 'All year', 'drive' => 'On arrival day',
        'text' => 'A layered mix of Mughal heritage, colonial landmarks, buzzing markets and endless food streets. Delhi is a natural gateway for India’s classic heritage circuits.',
        'see' => ['Red Fort', 'India Gate', 'Chandni Chowk', 'Jama Masjid', 'Qutub Minar'],
        'packages' => ['golden-triangle-delhi-agra-jaipur', 'delhi-agra-weekend'],
    ],
    'agra' => [
        'name' => 'Agra', 'tagline' => 'The city of marble and memory', 'image' => 'shimla-church', 'altitude' => '169 m', 'best' => 'All year', 'drive' => '3–5 hrs from Delhi',
        'text' => 'Home to the Taj Mahal and a remarkable Mughal legacy, Agra gives you one of India’s most memorable heritage experiences in just a day or two.',
        'see' => ['Taj Mahal', 'Agra Fort', 'Mehtab Bagh', 'local marble markets', 'Fatehpur Sikri day trip'],
        'packages' => ['golden-triangle-delhi-agra-jaipur', 'delhi-agra-weekend'],
    ],
    'rajasthan' => [
        'name' => 'Rajasthan', 'tagline' => 'Royal forts and desert skies', 'image' => 'manali-valley', 'altitude' => 'Varies by city', 'best' => 'October – March', 'drive' => 'Flexible by route',
        'text' => 'From Jaipur’s palace lanes to Jaisalmer’s golden desert dunes, Rajasthan brings together a rich mix of history, architecture and warm hospitality.',
        'see' => ['Jaipur', 'Jodhpur', 'Jaisalmer', 'Udaipur', 'Desert camps'],
        'packages' => ['rajasthan-heritage-trail', 'golden-triangle-delhi-agra-jaipur'],
    ],
    'nainital' => ['name' => 'Nainital', 'tagline' => 'A hill town around a lake', 'altitude' => 'Varies by route', 'best' => 'March–June, September–November', 'drive' => 'Route planned around pickup', 'text' => 'Spend time by Naini Lake, browse Mall Road and visit Naina Devi Temple on a relaxed Kumaon hill-station break.', 'see' => ['Boating on Naini Lake', 'Mall Road', 'Naina Devi Temple'], 'packages' => ['nainital']],
    'mussoorie' => ['name' => 'Mussoorie', 'tagline' => 'The Queen of Hills', 'altitude' => 'Varies by route', 'best' => 'September–June', 'drive' => 'Route planned around pickup', 'text' => 'Waterfalls, viewpoints and scenic walks make Mussoorie an easy-going hill escape.', 'see' => ['Kempty Falls', 'Gun Hill viewpoint', 'Mall Road'], 'packages' => ['mussoorie']],
    'rishikesh' => ['name' => 'Rishikesh', 'tagline' => 'The Ganges, yoga & adventure', 'altitude' => 'Varies by route', 'best' => 'February–May, August–October', 'drive' => 'Route planned around pickup', 'text' => 'A riverside town known for yoga, spiritual landmarks and adventure activities that vary with the season.', 'see' => ['White-water rafting, when available', 'Laxman Jhula', 'Evening Ganga Aarti'], 'packages' => ['rishikesh']],
    'auli' => ['name' => 'Auli', 'tagline' => 'Snow slopes & Himalayan views', 'altitude' => 'Varies by route', 'best' => 'November–March for snow and skiing', 'drive' => 'Route planned around pickup', 'text' => 'A high-altitude mountain destination for winter scenery and skiing when snow and local operations allow.', 'see' => ['Ski slopes', 'Cable car, subject to operations', 'Himalayan viewpoints'], 'packages' => ['auli']],
    'jim-corbett' => ['name' => 'Jim Corbett National Park', 'tagline' => 'Forest & wildlife', 'altitude' => 'Varies by zone', 'best' => 'November–June; zone access varies', 'drive' => 'Route planned around pickup', 'text' => 'Plan a park visit around available safari zones, permits and current local conditions.', 'see' => ['Jeep safaris, subject to permits', 'Wildlife spotting', 'Kosi River scenery'], 'packages' => ['jim-corbett']],
    'srinagar' => ['name' => 'Srinagar', 'tagline' => 'Lakes, houseboats & gardens', 'altitude' => 'Varies by route', 'best' => 'April–October for sightseeing', 'drive' => 'Route planned around pickup', 'text' => 'Discover Dal Lake, traditional houseboats, Shikara rides and Srinagar’s historic Mughal Gardens.', 'see' => ['Dal Lake', 'Houseboats and Shikara rides', 'Mughal Gardens'], 'packages' => ['kashmir-valley']],
    'gulmarg' => ['name' => 'Gulmarg', 'tagline' => 'Alpine meadows & winter sports', 'altitude' => 'Varies by route', 'best' => 'April–October for meadows; winter for snow', 'drive' => 'Route planned around pickup', 'text' => 'Visit for mountain meadows and seasonal snow activities, with cable-car access dependent on operations.', 'see' => ['Gulmarg meadows', 'Skiing in winter', 'Gondola, subject to operations'], 'packages' => ['kashmir-valley']],
    'pahalgam' => ['name' => 'Pahalgam', 'tagline' => 'Pine forests & the Lidder River', 'altitude' => 'Varies by route', 'best' => 'April–October', 'drive' => 'Route planned around pickup', 'text' => 'Explore the wooded valleys and river scenery of Pahalgam, also a base for the Amarnath Yatra.', 'see' => ['Lidder River', 'Pine forests', 'Amarnath Yatra base area'], 'packages' => ['kashmir-valley']],
    'sonamarg' => ['name' => 'Sonamarg', 'tagline' => 'Glaciers & high mountain trails', 'altitude' => 'Varies by route', 'best' => 'April–October', 'drive' => 'Route planned around pickup', 'text' => 'A scenic mountain stop known for glaciers, trout fishing and trekking routes to high-altitude lakes.', 'see' => ['Glacier views', 'Trout fishing', 'High-altitude lake trekking routes'], 'packages' => ['kashmir-valley']],
    'jammu-katra' => ['name' => 'Jammu City & Katra', 'tagline' => 'A gateway for pilgrimage', 'altitude' => 'Varies by route', 'best' => 'Plan around your dates', 'drive' => 'Route planned around pickup', 'text' => 'Visit Jammu and Katra, the starting point for pilgrims travelling to the Vaishno Devi shrine.', 'see' => ['Jammu City', 'Katra', 'Vaishno Devi shrine'], 'packages' => ['jammu-katra-patnitop']],
    'patnitop' => ['name' => 'Patnitop', 'tagline' => 'A quiet Jammu hill escape', 'altitude' => 'Varies by route', 'best' => 'Plan around your dates', 'drive' => 'Route planned around pickup', 'text' => 'Add a restorative mountain stay in Patnitop to a Jammu and Katra pilgrimage or holiday route.', 'see' => ['Mountain scenery', 'Easy walks', 'A relaxed stop after Katra'], 'packages' => ['jammu-katra-patnitop']],
    'dharamshala-mcleodganj' => ['name' => 'Dharamshala & McLeod Ganj', 'tagline' => 'Tibetan culture & pine forests', 'altitude' => 'Varies by route', 'best' => 'Plan around your dates', 'drive' => 'Route planned around pickup', 'text' => 'Explore the cultural centre of McLeod Ganj and the pine-forested mountain setting around Dharamshala.', 'see' => ['Tibetan art and culture', 'McLeod Ganj', 'Pine forests'], 'packages' => ['dharamshala-mcleodganj']],
    'dalhousie-khajjiar' => ['name' => 'Dalhousie & Khajjiar', 'tagline' => 'Colonial town & open meadows', 'altitude' => 'Varies by route', 'best' => 'Plan around your dates', 'drive' => 'Route planned around pickup', 'text' => 'Pair Dalhousie’s colonial-era character with the broad green meadows of Khajjiar.', 'see' => ['Dalhousie town walks', 'Khajjiar meadows', 'Dhauladhar views'], 'packages' => ['dalhousie-khajjiar']],
];

/* ---------------------------------------------------------------------------
 * Vehicles. 'group' lists the group-size filters a vehicle suits.
 * ------------------------------------------------------------------------- */
$vehicles = [
    [
        'name' => 'Maruti Swift', 'type' => 'car', 'tag' => 'Hatchback', 'group' => 'small',
        'image' => 'assets/images/car/fleet-swift.jpg', 'alt' => 'White Maruti Suzuki Swift hatchback',
        'summary' => 'Small car for 1–4 people. Dzire sedan & Alto also available.',
        'details' => 'A small, comfortable car that is easy on fuel. Good for couples and small families on normal highway and hill roads. Ask for the Dzire if you need more boot space.',
        'seats' => '4', 'bags' => '2 bags', 'extra' => ['fa-snowflake', 'AC'],
        'best' => 'City & hill drives', 'terrain' => 'Paved highways',
        'features' => ['Air conditioning', 'Easy to park in hill towns', 'Economical on long drives', 'Dzire sedan & Alto on request'],
        'routes' => ['Shimla trips', 'Shimla local', 'Kullu · Manali'],
    ],
    [
        'name' => 'Maruti Ertiga', 'type' => 'suv', 'tag' => 'MUV', 'group' => 'medium',
        'image' => 'assets/images/car/fleet-ertiga.jpg', 'alt' => 'White Maruti Suzuki Ertiga MPV',
        'summary' => 'Seven-seater for families of 5–6 people.',
        'details' => 'Three rows of seats, so the whole family travels together. Smooth and comfortable on long drives and on hill roads.',
        'seats' => '6', 'bags' => '3 bags', 'extra' => ['fa-snowflake', 'AC'],
        'best' => 'Family trips', 'terrain' => 'Highways & hill roads',
        'features' => ['Three-row seating', 'Rear AC vents', 'Space for 3 suitcases', 'Great value for families'],
        'routes' => ['Manali trips', 'Shimla · Kullu · Manali', 'Kasol'],
    ],
    [
        'name' => 'Toyota Innova Crysta', 'type' => 'suv', 'tag' => 'Premium MUV', 'group' => 'medium',
        'image' => 'assets/images/car/fleet-innova.jpg', 'alt' => 'White Toyota Innova Crysta',
        'summary' => 'Big, powerful and very comfortable — best for long trips.',
        'details' => 'Our most booked vehicle for long trips. Comfortable seats, a strong engine and a smooth ride make long days on mountain roads much easier.',
        'seats' => '6–7', 'bags' => '4 bags', 'extra' => ['fa-snowflake', 'AC'],
        'best' => 'Long journeys', 'terrain' => 'All-weather hill roads',
        'features' => ['Captain seats in the middle row', 'Powerful diesel for steep climbs', 'Generous luggage room', 'Premium, quiet cabin'],
        'routes' => ['Shimla to Spiti via Kinnaur', 'Manali · Atal Tunnel', 'Multi-day circuits'],
    ],
    [
        'name' => 'Suzuki Jimny 4×4', 'type' => 'suv', 'tag' => '4×4', 'group' => 'small',
        'image' => 'assets/images/car/fleet-jimny-4x4.jpg', 'alt' => 'Suzuki Jimny 4x4 with roof rack on a hillside',
        'summary' => 'Small 4×4 for rough roads in Spiti and Chandratal.',
        'details' => 'A real 4×4. Where the good road ends — near Kaza, Chandratal or far Kinnaur villages — the Jimny keeps going.',
        'seats' => '4', 'bags' => '2 bags', 'extra' => ['fa-mountain', '4WD'],
        'best' => 'Off-road & Spiti', 'terrain' => 'Rough & unpaved tracks',
        'features' => ['Low-range 4×4 gearbox', 'High ground clearance', 'Compact for narrow roads', 'Roof rack for extra luggage'],
        'routes' => ['Kaza · Chandratal', 'Chitkul · Sangla', 'Pin Valley'],
    ],
    [
        'name' => 'Tempo Traveller', 'type' => 'group', 'tag' => 'Mini coach', 'group' => 'large',
        'image' => 'assets/images/car/fleet-traveller.jpg', 'alt' => 'White Force Tempo Traveller on a Himalayan mountain road',
        'summary' => 'Mini bus for groups of 12–17 people.',
        'details' => 'The usual choice for group trips. Push-back seats, a high roof and a roof carrier, so everyone and all the bags go in one vehicle.',
        'seats' => '12–17', 'bags' => 'Roof carrier', 'extra' => ['fa-snowflake', 'AC'],
        'best' => 'Group journeys', 'terrain' => 'Highways & hill roads',
        'features' => ['Push-back seats', 'Roof carrier for luggage', 'High roof for easy movement', 'Music system'],
        'routes' => ['Shimla · Manali circuits', 'Corporate & school trips', 'Wedding groups'],
    ],
    [
        'name' => 'Force Urbania', 'type' => 'group', 'tag' => 'Luxury van', 'group' => 'large',
        'image' => 'assets/images/car/fleet-urbania-van.jpg', 'alt' => 'Premium passenger van in a Himalayan valley',
        'summary' => 'Premium van for groups who want extra comfort.',
        'details' => 'A newer, more comfortable group van: big windows, reclining seats and a quieter ride.',
        'seats' => '10–17', 'bags' => 'Large boot', 'extra' => ['fa-snowflake', 'AC'],
        'best' => 'Premium groups', 'terrain' => 'Highways & hill roads',
        'features' => ['Panoramic windows', 'Individual reclining seats', 'Quieter, car-like ride', 'USB charging points'],
        'routes' => ['Premium family tours', 'Corporate offsites', 'Airport transfers'],
    ],
];

$groupSizes = ['all' => 'Any group size', 'small' => '1–4 travellers', 'medium' => '5–7 travellers', 'large' => '8–17 travellers'];

$defaultVehicles = $vehicles;
require_once dirname(__DIR__) . '/backend/database.php';
$databaseVehicles = app_db_catalog('taxis');
if ($databaseVehicles !== null) {
    $vehicles = array_values($databaseVehicles);
    foreach ($vehicles as &$vehicle) {
        $imageFile = basename((string) ($vehicle['image'] ?? ''));
        if (preg_match('/^(?:fleet|car)-.+\.jpg$/i', $imageFile)) {
            $vehicle['image'] = 'assets/images/car/' . $imageFile;
        }
    }
    unset($vehicle);
}

/* ---------------------------------------------------------------------------
 * Gallery photos. 'cat' drives the gallery filters.
 * ------------------------------------------------------------------------- */
$galleryCategories = [
    'all' => 'All photos', 'valleys' => 'Valleys & villages', 'lakes' => 'Lakes',
    'monasteries' => 'Temples & monasteries', 'snow' => 'Snow & peaks', 'towns' => 'Hill towns', 'adventure' => 'Camping & adventure',
];

$gallery = [
    ['file' => 'spiti-key-sunset', 'title' => 'Key Monastery at sunset',   'place' => 'Spiti Valley', 'cat' => 'monasteries'],
    ['file' => 'kinnaur-camps',    'title' => 'Riverside camps',           'place' => 'Kinnaur',      'cat' => 'adventure'],
    ['file' => 'chandratal-b',     'title' => 'Chandratal Lake',           'place' => 'Lahaul',       'cat' => 'lakes'],
    ['file' => 'hidimba',          'title' => 'Hidimba Devi Temple',       'place' => 'Manali',       'cat' => 'monasteries'],
    ['file' => 'shimla-train',     'title' => 'The toy train',             'place' => 'Shimla',       'cat' => 'towns'],
    ['file' => 'spiti-dhankar',    'title' => 'Dhankar Monastery',         'place' => 'Spiti Valley', 'cat' => 'monasteries'],
    ['file' => 'kinnaur-sangla',   'title' => 'Sangla valley',             'place' => 'Kinnaur',      'cat' => 'valleys'],
    ['file' => 'solang',           'title' => 'Solang Valley in winter',   'place' => 'Manali',       'cat' => 'snow'],
    ['file' => 'spiti-buddha',     'title' => 'Langza Buddha',             'place' => 'Spiti Valley', 'cat' => 'monasteries'],
    ['file' => 'shimla-church',    'title' => 'Christ Church',             'place' => 'Shimla',       'cat' => 'towns'],
    ['file' => 'kasol-river',      'title' => 'Parvati river',             'place' => 'Kasol',        'cat' => 'valleys'],
    ['file' => 'spiti-stars',      'title' => 'Camping under the stars',   'place' => 'Spiti Valley', 'cat' => 'adventure'],
    ['file' => 'kinnaur-autumn',   'title' => 'Autumn in Kinnaur',         'place' => 'Kinnaur',      'cat' => 'valleys'],
    ['file' => 'snow-peaks',       'title' => 'Snow trail',                'place' => 'Manali',       'cat' => 'snow'],
    ['file' => 'shimla-lodge',     'title' => 'Viceregal Lodge',           'place' => 'Shimla',       'cat' => 'towns'],
    ['file' => 'chandratal',       'title' => 'Crystal-clear Chandratal',  'place' => 'Lahaul',       'cat' => 'lakes'],
    ['file' => 'parvati',          'title' => 'Parvati valley meadows',    'place' => 'Kullu',        'cat' => 'valleys'],
    ['file' => 'camping',          'title' => 'High-altitude camping',     'place' => 'Himachal',     'cat' => 'adventure'],
    ['file' => 'spiti-key-snow',   'title' => 'Key Monastery in snow',     'place' => 'Spiti Valley', 'cat' => 'snow'],
    ['file' => 'manali-mall',      'title' => 'Mall Road',                 'place' => 'Manali',       'cat' => 'towns'],
    ['file' => 'trek-lahaul',      'title' => 'Trekking in Lahaul',        'place' => 'Lahaul',       'cat' => 'adventure'],
    ['file' => 'kinnaur-peaks',    'title' => 'Kinner Kailash range',      'place' => 'Kinnaur',      'cat' => 'snow'],
    ['file' => 'forest-river',     'title' => 'Forest river',              'place' => 'Kullu',        'cat' => 'valleys'],
    ['file' => 'shimla-snow',      'title' => 'Winter in Shimla',          'place' => 'Shimla',       'cat' => 'snow'],
];
