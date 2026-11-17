<?php
/**
 * Seed content used by install.php.
 *
 * Rates are indicative wholesale references (INR per kg, ex-Purnea, excluding
 * GST and freight) and should be reviewed by the business before going live.
 * Keep them consistent with the figures you quote on WhatsApp and invoices.
 */

declare(strict_types=1);

return [

    'products' => [

        [
            'slug'            => 'premium-select',
            'name'            => 'Premium Select',
            'tagline'         => 'Our largest, most uniform grain — for premium retail, gifting and export-style presentations.',
            'grade_size'      => '18 mm & above',
            'suta'            => '7+ Suta',
            'description'     => 'The top of our range. Sieved on the largest screen, then hand-checked for split, broken and discoloured grain. Colour is light and consistent across the lot, and the larger kernel gives a cleaner expansion on roasting. Best suited to buyers who sell makhana as a standalone premium snack or pack it under their own label.',
            'features'        => [
                '18 mm and above sieve size',
                '7+ suta count',
                'Hand-checked for splits and broken grain',
                'Light, consistent colour across the lot',
                '25 / 50 kg PP bags or retail pouches',
            ],
            'moq_kg'          => 25,
            'indicative_rate' => 520,
            'pack_sizes'      => '25 / 50 kg PP · 100–500 g pouches',
            'image'           => 'product-premium.jpg',
            'badge'           => 'Largest grain',
        ],
        [
            'slug'            => 'superior',
            'name'            => 'Superior Grade',
            'tagline'         => 'Everyday premium grain — uniform 16 mm, clean colour, dependable across lots.',
            'grade_size'      => '16 mm & above',
            'suta'            => '6+ Suta',
            'description'     => 'The workhorse of our premium range. Uniform 16 mm grain with a clean, light colour, sized and hand-checked so that a lot ordered today matches a lot ordered next season. Works well for branded retail packs, hotels, cafes and catering, and for gifting where presentation matters but the very top grade is not essential.',
            'features'        => [
                '16 mm and above sieve size',
                '6+ suta count',
                'Uniform sizing within the lot',
                'Suitable for branded retail packs',
                '25 / 50 kg PP bags or retail pouches',
            ],
            'moq_kg'          => 25,
            'indicative_rate' => 430,
            'pack_sizes'      => '25 / 50 kg PP · 100–500 g pouches',
            'image'           => 'product-superior.jpg',
            'badge'           => '',
        ],
        [
            'slug'            => 'standard',
            'name'            => 'Standard Grade',
            'tagline'         => 'A dependable mid-size grade that balances appearance with cost.',
            'grade_size'      => '13 mm & above',
            'suta'            => '5+ Suta',
            'description'     => 'Our mid-size grade, built for volume buyers who need consistency at a sensible rate. Sized at 13 mm and checked for splits, it holds up well in loose and lightly packed retail, in sweet shops and in the wholesale mandi trade where the buyer weighs and grades in front of the customer.',
            'features'        => [
                '13 mm and above sieve size',
                '5+ suta count',
                'Consistent colour lot to lot',
                'Sized for loose and pouch packing',
                '25 / 50 kg PP bags',
            ],
            'moq_kg'          => 25,
            'indicative_rate' => 350,
            'pack_sizes'      => '25 / 50 kg PP · 200–500 g pouches',
            'image'           => 'product-standard.jpg',
            'badge'           => '',
        ],
        [
            'slug'            => 'regular',
            'name'            => 'Regular Grade',
            'tagline'         => 'Economy grain for buyers who roast, flavour and pack it themselves.',
            'grade_size'      => '10 mm & above',
            'suta'            => '4+ Suta',
            'description'     => 'The value end of the range, sized at 10 mm and above. Smaller grain means more pieces per kilo, so it suits buyers who process further — roasting, flavouring, coating or blending into mixtures — and kitchens and institutions buying in volume rather than for shelf display.',
            'features'        => [
                '10 mm and above sieve size',
                '4+ suta count',
                'Good expansion on roasting',
                'Best for onward processing',
                '25 / 50 kg PP bags',
            ],
            'moq_kg'          => 25,
            'indicative_rate' => 290,
            'pack_sizes'      => '25 / 50 kg PP',
            'image'           => 'product-regular.jpg',
            'badge'           => '',
        ],
    ],

    'distributors' => [
        ['name' => 'Food Village',                   'area' => '', 'since' => '', 'note' => '', 'sort_order' => 0],
        ['name' => 'Ramesh Kumar Kedia Enterprises', 'area' => '', 'since' => '', 'note' => '', 'sort_order' => 1],
        ['name' => 'Shri Shyam Trading',             'area' => '', 'since' => '', 'note' => '', 'sort_order' => 2],
        ['name' => 'Jaiswal Sales',                  'area' => '', 'since' => '', 'note' => '', 'sort_order' => 3],
    ],
];