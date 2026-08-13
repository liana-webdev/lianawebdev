<?php
declare(strict_types=1);

return [
    'bramelle-partners' => [
        'business' => 'Bramelle Partners',
        'location' => 'North Sydney',
        'niche' => 'Accounting and business advisory',
        'page_label' => 'SME businesses page',
        'page_url' => 'https://bramellepartners.com.au/sme-businesses/',
        'reviewed_at' => '13 August 2026',
        'summary' => 'The page contains enough expertise and proof to persuade, but it asks visitors to assemble the reason to enquire themselves.',
        'strength' => 'The fixed-fee structure, advisory breadth and free director consultation give Bramelle Partners a commercially strong offer.',
        'issues' => [
            [
                'title' => 'The opening competes with the offer',
                'evidence' => 'Large, tightly cropped team imagery occupies the first decision area before the page establishes the SME outcome or fixed-fee advantage.',
                'impact' => 'A business owner comparing firms must work harder to understand why Bramelle Partners is the right choice.',
            ],
            [
                'title' => 'The decision path is diluted',
                'evidence' => 'The service explanation runs for more than 1,000 words, while testimonials and the free director consultation appear as separate elements rather than one persuasive sequence.',
                'impact' => 'Useful proof exists, but it does not arrive at the moment a visitor is deciding whether to enquire.',
            ],
            [
                'title' => 'Desktop friction reduces polish',
                'evidence' => 'The reviewed desktop layout produced horizontal scrolling on the page.',
                'impact' => 'A small interface fault can make a strong advisory firm feel less controlled than its actual service.',
            ],
        ],
        'first_repair' => 'Rebuild the first two screens around the SME problem, the commercial outcome, relevant proof and the free director consultation. Preserve the deeper service detail below for visitors who need it.',
        'current_path' => [
            'Broad introduction',
            'Long service explanation',
            'Testimonials separated from the decision',
            'Consultation after substantial scrolling',
        ],
        'recommended_path' => [
            'SME problem and desired outcome',
            'Fixed-fee and advisory proof',
            'Relevant client result',
            'Free director consultation',
        ],
    ],
];
