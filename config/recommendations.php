<?php

return [

    // How many days of behavior data count toward trends and profiles.
    'trend_days' => 30,

    // Exponential decay per day so recent events matter more.
    'decay_per_day' => 0.06,

    // Signal weights when computing a product's trend score.
    'trend_weights' => [
        'search' => 3.0,
        'view' => 1.0,
        'wishlist' => 2.5,
        'cart' => 3.0,
        'order' => 5.0,
    ],

    // Blend ratios used by the weighted shuffle.
    'shuffle' => [
        'personalization' => 0.35,
        'trend' => 0.35,
        'randomness' => 0.30,
    ],

    // Fuzzy search tuning. Scores are normalised to roughly 0..1 per token.
    'search' => [
        // Minimum score for a product to be considered a hit.
        'min_score' => 0.25,
    ],

    // Cache lifetime (seconds) for computed trend rankings.
    'cache_ttl' => 600,
];
