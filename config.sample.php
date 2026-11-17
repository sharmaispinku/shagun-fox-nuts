<?php
/**
 * Shagun Enterprises — site configuration
 *
 * Copy this file to config.php and adjust. config.php is blocked from web
 * access by .htaccess / server config, but never put real secrets in a
 * world-readable location.
 */

return [

    'site' => [
        'name'      => 'Shagun Enterprises',
        'legal'     => 'Shagun Enterprises',
        'tagline'   => 'Premium makhana grades from Purnea, Bihar',
        // Absolute URL without trailing slash — used for canonical, sitemap, OG tags.
        'url'       => 'https://example.com',
        'phone'     => '+91 82109 72729',
        'phone_raw' => '+918210972729',
        'whatsapp'  => '918210972729',
        'email'     => 'sandeepsk1994.sk@gmail.com',
        'address'   => 'At + Post – Harda Bazar, Kolasi Road, Harda, Purnea – 854303, Bihar, India',
        'address_short' => 'Harda Bazar, Kolasi Road, Purnea, Bihar 854303',
        'gstin'     => '10GEIPK2689D1ZL',
        'pan'       => 'GEIPK2689D',
        'hours'     => 'Mon – Sat, 9:30 AM – 7:00 PM IST',
        // Google Maps embed URL (no API key needed for the ?output=embed form).
        'map_embed' => 'https://maps.google.com/maps?q=Harda%20Bazar%2C%20Purnea%2C%20Bihar%20854303&t=&z=14&ie=UTF8&iwloc=&output=embed',
        'map_link'  => 'https://www.google.com/maps/search/?api=1&query=Harda+Bazar+Kolasi+Road+Purnea+Bihar+854303',
    ],

    /**
     * Storage. 'sqlite' needs zero setup and is ideal for a quote-generation
     * site. Switch to 'mysql' on busier hosts — same schema, different driver.
     */
    'db' => [
        'driver' => 'sqlite',                 // sqlite | mysql
        'path'   => __DIR__ . '/data/app.sqlite',
        'host'   => 'localhost',
        'port'   => 3306,
        'name'   => 'shagun',
        'user'   => '',
        'pass'   => '',
        'charset'=> 'utf8mb4',
    ],

    /**
     * Outgoing email (new order notifications). 'mail' uses PHP mail();
     * 'smtp' is only honoured if you wire a mailer library in.
     */
    'mail' => [
        'enabled' => true,
        'mode'    => 'mail',                  // mail | log
        'from'    => 'orders@example.com',
        'from_name' => 'Shagun Enterprises Website',
        'to'      => 'sandeepsk1994.sk@gmail.com',
    ],

    'security' => [
        // Max order requests accepted from one IP per hour.
        'rate_limit_per_hour' => 6,
        // Order requests older than this many days are ignored by the tracker.
        'session_name' => 'shagun_sess',
        // Set true when the site is served over HTTPS.
        'force_https'  => false,
    ],

    'features' => [
        'market_rates'  => true,   // show indicative rate table
        'online_orders' => true,   // accept quote requests from the website
    ],
];