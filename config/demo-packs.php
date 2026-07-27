<?php

return [
    'default' => [
        'label' => 'Shopcrafty Essentials',
        'description' => 'A balanced everyday catalogue for the official Shopcrafty theme.',
        'theme' => 'default',
        'store_name' => 'Shopcrafty Essentials',
        'brands' => ['Northstar', 'Everyday Co.'],
        'categories' => [
            'Featured' => 'A considered selection for everyday shopping.',
            'New arrivals' => 'Fresh products recently added to the store.',
            'Best sellers' => 'Customer favourites and proven essentials.',
        ],
        'products' => [
            ['Canvas Weekender', 'Featured', 'Northstar', 89, 119, 24],
            ['Daily Carry Bottle', 'New arrivals', 'Everyday Co.', 28, null, 60],
            ['Soft Knit Throw', 'Best sellers', 'Northstar', 54, 72, 18],
        ],
        'copy' => [
            'hero' => ['eyebrow' => 'The everyday edit', 'heading' => 'Good things, thoughtfully chosen', 'subheading' => 'Discover useful pieces made for modern daily life.', 'cta_label' => 'Shop the collection', 'cta_url' => '/shop'],
            'banners' => [
                ['title' => 'Made for the everyday', 'subtitle' => 'Simple pieces, considered well', 'label' => 'Shop now', 'url' => '/shop'],
                ['title' => 'New season arrivals', 'subtitle' => 'Find your next favourite', 'label' => 'Explore', 'url' => '/shop'],
            ],
        ],
    ],

    'boutique' => [
        'label' => 'Noir Boutique',
        'description' => 'A refined fashion catalogue designed for the Boutique theme.',
        'theme' => 'boutique',
        'store_name' => 'Noir Boutique',
        'brands' => ['Atelier Noir', 'Maison Edit'],
        'categories' => [
            'New in' => 'The latest silhouettes and seasonal arrivals.',
            'Dresses' => 'Fluid shapes and occasion-ready essentials.',
            'Accessories' => 'Finishing touches for a considered wardrobe.',
        ],
        'products' => [
            ['Silk Bias Dress', 'Dresses', 'Atelier Noir', 168, 220, 12],
            ['Structured Leather Tote', 'Accessories', 'Maison Edit', 142, 185, 15],
            ['Tailored Wool Blazer', 'New in', 'Atelier Noir', 198, 260, 9],
        ],
        'copy' => [
            'hero' => ['eyebrow' => 'The new edit', 'heading' => 'A wardrobe with intention', 'subheading' => 'Quiet confidence, cut in considered fabrics and timeless lines.', 'cta_label' => 'Shop the edit', 'cta_url' => '/shop'],
            'banners' => [
                ['title' => 'The new edit', 'subtitle' => 'Ease, structure, and a little drama', 'label' => 'Discover', 'url' => '/shop'],
                ['title' => 'Made to be kept', 'subtitle' => 'Investment pieces for every season', 'label' => 'Explore', 'url' => '/shop'],
            ],
        ],
    ],

    'market' => [
        'label' => 'Market Supply',
        'description' => 'A broad marketplace catalogue designed for the Marketplace theme.',
        'theme' => 'market',
        'store_name' => 'Market Supply',
        'brands' => ['Field & Found', 'Common Goods'],
        'categories' => [
            'Home' => 'Useful objects for rooms you live in.',
            'Outdoor' => 'Equipment for weekends outside.',
            'Tech' => 'Small tools that make life easier.',
        ],
        'products' => [
            ['Linen Market Basket', 'Home', 'Field & Found', 42, 58, 32],
            ['All-Weather Daypack', 'Outdoor', 'Common Goods', 76, 99, 20],
            ['Compact Desk Lamp', 'Tech', 'Field & Found', 64, 84, 27],
        ],
        'copy' => [
            'hero' => ['eyebrow' => 'The independent marketplace', 'heading' => 'Find something worth bringing home', 'subheading' => 'A thoughtful mix of useful goods from makers we trust.', 'cta_label' => 'Browse departments', 'cta_url' => '/shop'],
            'banners' => [
                ['title' => 'Good goods, gathered', 'subtitle' => 'Explore our latest marketplace finds', 'label' => 'Shop now', 'url' => '/shop'],
                ['title' => 'Built for better days', 'subtitle' => 'Practical pieces with lasting appeal', 'label' => 'Explore', 'url' => '/shop'],
            ],
        ],
    ],
];
