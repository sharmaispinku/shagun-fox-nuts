<?php
/**
 * FAQ — grouped buyer questions with FAQPage structured data.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'FAQs — makhana grades, ordering, packing | ' . SITE_NAME;
$pageDesc  = 'Answers on makhana grades and suta counts, minimum order quantity, sampling, packing, GST invoicing, dispatch and payment terms.';

$groups = [
    'Ordering' => [
        ['What is the minimum order quantity?', 'Wholesale lots start at 25 kg for Standard and Regular grades and 25 kg for the premium grades as well. If you need less, ask for a sample lot — 1 to 2 kg is usually enough to evaluate colour, grain size and roast behaviour.'],
        ['How do I place an order?', 'Send the grade, quantity, packing preference and delivery city through the bulk order form, or message us on WhatsApp. We confirm stock, share a firm quote and invoice against your GSTIN.'],
        ['How quickly can you dispatch?', 'Stocked grades usually move within 3–7 working days of order confirmation. Fresh lots or custom packing can take 2–3 weeks depending on the season.'],
        ['Do you take advance payment?', 'Terms are agreed per order. First-time buyers typically pay an advance against the proforma invoice; regular trade accounts are discussed case by case.'],
        ['Can I see the lot before buying?', 'Yes. Ask for a sample of the specific grade, or visit the unit at Harda Bazar by appointment so you can inspect the grading floor.'],
    ],
    'Grades & quality' => [
        ['What does "suta" mean?', 'Suta is the trade count used to describe makhana size and uniformity — a higher suta number indicates a larger, more uniform grain. Premium Select is 7+ suta, Superior is 6+, Standard 5+ and Regular 4+.'],
        ['What is the difference between the grades?', 'Grain size and uniformity. Premium Select (18 mm, 7+ suta) is the largest and most uniform, suited to premium retail and gifting. Superior (16 mm, 6+) is the everyday premium grade. Standard (13 mm, 5+) balances cost and appearance. Regular (10 mm, 4+) is the economical choice for processing and value packs.'],
        ['How do you control moisture?', 'Seed is dried and rested before roasting and popping, and lots are checked before packing. Lower moisture means better shelf life and stable weight in transit.'],
        ['Are your lots hand sorted?', 'Yes — after sieve sizing, lots are hand-checked to remove split, broken and discoloured grains before packing.'],
        ['Is makhana the same as fox nuts?', 'Yes. Makhana, fox nuts and gorgon fruit all refer to the popped seed of Euryale ferox, an aquatic plant grown in Bihar, parts of eastern India, Nepal, Japan and Korea.'],
    ],
    'Packing & documents' => [
        ['What packing options do you offer?', 'Standard 25 kg and 50 kg PP bags with liner; retail stand-up pouches in 100 g, 200 g and 500 g; and custom printed liners for onward packing at your facility.'],
        ['Can you pack under my brand name?', 'Yes, subject to artwork approval and minimum pouch volumes. Share your artwork and pouch specification and we will confirm feasibility and cost.'],
        ['Do you provide a GST invoice?', 'Yes. We raise a tax invoice against your GSTIN for every consignment and share it with the transport documents.'],
        ['What documents come with a consignment?', 'Invoice, weight slip, batch/lot details and the LR or e-way bill copy, shared by WhatsApp or email as the goods move.'],
    ],
    'Rates & logistics' => [
        ['How is the final rate decided?', 'Grade and suta, moisture at loading, packing, volume and season. Rates on the site are indicative wholesale references; your quote is firmer because it is tied to a specific lot.'],
        ['Do you deliver across India?', 'We dispatch by road across Bihar, Uttar Pradesh, Jharkhand, West Bengal, Delhi NCR and other states on request. Freight is quoted at actuals or arranged by the buyer.'],
        ['Where is your unit located?', 'At + Post Harda Bazar, Kolasi Road, Harda, Purnea – 854303, Bihar, India. Visits are by appointment.'],
        ['Can I collect the goods myself?', 'Yes — self-pickup is possible from the Purnea unit. Share the vehicle details in advance so loading can be planned.'],
    ],
];

$jsonLd = [[
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(static function (array $qa) {
        return [
            '@type'          => 'Question',
            'name'           => $qa[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
        ];
    }, array_merge(...array_values($groups))),
]];

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span><span aria-current="page">FAQs</span>
    </nav>
</div>

<section class="section section--tight">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow">FAQs</span>
            <h2>Everything buyers ask us.</h2>
            <p>Grades, suta counts, packing, documents and dispatch. If your question is not here, the grading desk answers on WhatsApp within working hours.</p>
        </div>

        <?php foreach ($groups as $group => $qaList): ?>
            <div class="reveal" style="max-width:860px;margin:0 auto 40px">
                <h3 style="font-size:1.15rem;margin-bottom:6px"><?= e($group) ?></h3>
                <div class="faq-list">
                    <?php foreach ($qaList as $i => [$q, $a]): ?>
                        <div class="faq-item">
                            <button class="faq-q" aria-expanded="false" aria-controls="faq-<?= e(preg_replace('/[^a-z0-9]+/i', '-', strtolower($group))) ?>-<?= $i ?>" type="button">
                                <?= e($q) ?>
                                <span class="icon" aria-hidden="true">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M12 5v14M5 12h14"/></svg>
                                </span>
                            </button>
                            <div class="faq-a" id="faq-<?= e(preg_replace('/[^a-z0-9]+/i', '-', strtolower($group))) ?>-<?= $i ?>">
                                <div><p><?= e($a) ?></p></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div class="cta-inner">
            <div>
                <h2>Still deciding?</h2>
                <p>Request a sample lot and compare the grade in your own kitchen before you commit to volume.</p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-gold btn-lg" href="/bulk-order?type=sample">Request a sample</a>
                <a class="btn btn-outline-light btn-lg" href="<?= e(wa_link('Hello Shagun Enterprises, I have a question about makhana.')) ?>" target="_blank" rel="noopener">Ask on WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>