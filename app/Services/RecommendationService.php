<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\UserSearchLog;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Lightweight recommendation engine.
 *
 * Learns from real user behaviour (searches, product views, wishlists,
 * cart items, orders) stored in the database. Exposes:
 *   - trending(): which products are heating up right now
 *   - personalizedFor(): content-based picks for a specific user/guest
 *   - orderProducts(): weighted shuffle that surfaces hot / relevant items
 *   - search(): typo-tolerant fuzzy product search
 */
class RecommendationService
{
    private ?Collection $corpus = null;

    /** @var array<int,float>|null */
    private ?array $trendScoresCache = null;

    /** @var array<string,array<int,float>> */
    private array $personalScoresCache = [];

    /**
     * Active products with the relations every recommendation needs,
     * loaded once per request and keyed by id.
     */
    public function corpus(): Collection
    {
        if ($this->corpus !== null) {
            return $this->corpus;
        }

        return $this->corpus = Product::with('primaryImage', 'category', 'categories')
            ->where('is_active', true)
            ->get()
            ->keyBy('id');
    }

    /**
     * Corpus filtered to a category slug (primary or multi-assigned).
     */
    public function corpusForCategory(?string $categorySlug): Collection
    {
        if (!$categorySlug || $categorySlug === 'all') {
            return $this->corpus();
        }

        return $this->corpus()->filter(function (Product $product) use ($categorySlug) {
            if ($product->category && $product->category->slug === $categorySlug) {
                return true;
            }
            foreach ($product->categories as $category) {
                if ($category->slug === $categorySlug) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Record a search so the model can learn what this user looks for.
     */
    public function trackSearch(string $query, ?int $userId, ?string $guestId, int $resultsCount): void
    {
        if (trim($query) === '') {
            return;
        }

        UserSearchLog::create([
            'user_id' => $userId,
            'guest_id' => $guestId,
            'query' => trim($query),
            'results_count' => $resultsCount,
        ]);
    }

    /**
     * Record a product detail view.
     */
    public function trackView(int $productId, ?int $userId, ?string $guestId): void
    {
        if ($userId === null && !$guestId) {
            return;
        }

        ProductView::create([
            'user_id' => $userId,
            'guest_id' => $guestId,
            'product_id' => $productId,
        ]);
    }

    /**
     * Products that are trending right now, weighted by recent behaviour
     * with exponential decay so latest signals count more.
     */
    public function trending(int $limit = 8): Collection
    {
        return Cache::remember('rec_trending_'.$limit, config('recommendations.cache_ttl', 600), function () use ($limit) {
            $scores = $this->trendScores();

            $items = [];
            foreach ($scores as $id => $score) {
                $product = $this->corpus()->get($id);
                if (!$product) {
                    continue;
                }
                $items[] = ['product' => $product, 'score' => $score, 'in_stock' => $product->stock > 0 ? 1 : 0];
            }

            usort($items, fn ($a, $b) => $a['in_stock'] !== $b['in_stock']
                ? $b['in_stock'] <=> $a['in_stock']
                : $b['score'] <=> $a['score']);

            $result = collect(array_column($items, 'product'))->take($limit);

            if ($result->isEmpty()) {
                $result = $this->corpus()->values()->inRandomOrder()->take($limit);
            }

            return $result;
        });
    }

    /**
     * Does this user/guest have enough behaviour history to personalize for them?
     */
    public function hasProfile(?int $userId, ?string $guestId): bool
    {
        $profile = $this->userProfile($userId, $guestId);

        return !empty($profile['tokens']) || !empty($profile['categories']);
    }

    /**
     * Content-based recommendations for a specific user or guest.
     * Falls back to trending when there is no profile yet.
     */
    public function personalizedFor(?int $userId, ?string $guestId, int $limit = 8): Collection
    {
        $scores = $this->personalScores($userId, $guestId);

        if (empty($scores)) {
            return $this->trending($limit);
        }

        arsort($scores);

        $result = collect();
        foreach ($scores as $id => $score) {
            $product = $this->corpus()->get($id);
            if (!$product) {
                continue;
            }
            $result->push($product);
            if ($result->count() >= $limit) {
                break;
            }
        }

        return $result;
    }

    /**
     * Re-order a set of products so the hottest and most relevant items
     * surface first while keeping a shuffled feel.
     */
    public function orderProducts(Collection $products, ?int $userId = null, ?string $guestId = null): Collection
    {
        if ($products->isEmpty()) {
            return $products;
        }

        $trend = $this->normalize($this->trendScores());
        $personal = $this->normalize($this->personalScores($userId, $guestId));
        $blend = config('recommendations.shuffle');

        return $products
            ->map(function (Product $product) use ($trend, $personal, $blend) {
                $weight = ($blend['trend'] * ($trend[$product->id] ?? 0))
                    + ($blend['personalization'] * ($personal[$product->id] ?? 0))
                    + ($blend['randomness'] * (mt_rand(0, 1000) / 1000));

                return ['product' => $product, 'weight' => $weight, 'in_stock' => $product->stock > 0 ? 1 : 0];
            })
            ->sortByDesc(fn ($item) => [$item['in_stock'], $item['weight']])
            ->map(fn ($item) => $item['product'])
            ->values();
    }

    /**
     * Products related to a given product (shared categories + name overlap),
     * used for "You Might Like" / "You may also like".
     */
    public function relatedFor(Product $product, int $limit = 4): Collection
    {
        $categoryIds = $this->productCategoryIds($product);
        $nameTokens = $this->tokenize($product->name);

        $scores = [];
        foreach ($this->corpus() as $other) {
            if ($other->id === $product->id) {
                continue;
            }

            $score = count(array_intersect($categoryIds, $this->productCategoryIds($other))) * 2.0;

            foreach ($this->tokenize($other->name) as $token) {
                if (in_array($token, $nameTokens, true)) {
                    $score += 1.0;
                }
            }

            if ($score > 0) {
                $scores[$other->id] = $score;
            }
        }

        arsort($scores);

        $result = collect();
        foreach ($scores as $id => $score) {
            $candidate = $this->corpus()->get($id);
            if (!$candidate) {
                continue;
            }
            $result->push($candidate);
            if ($result->count() >= $limit) {
                break;
            }
        }

        if ($result->count() < $limit) {
            $needed = $limit - $result->count();
            $fallback = $this->trending($needed + 5)
                ->reject(fn ($p) => $p->id === $product->id || $result->contains('id', $p->id))
                ->take($needed);
            $result = $result->merge($fallback);
        }

        return $result;
    }

    /**
     * Typo-tolerant product search. Always returns something usable:
     * scored matches, or trending products when nothing matches.
     *
     * @return array{products: Collection, bestMatch: ?string, isFuzzy: bool}
     */
    public function search(string $query, ?string $categorySlug = null, ?int $userId = null, ?string $guestId = null): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['products' => collect(), 'bestMatch' => null, 'isFuzzy' => false];
        }

        $tokens = $this->tokenize($query);
        $corpus = $this->corpusForCategory($categorySlug);

        $scored = [];
        foreach ($corpus as $product) {
            $score = $this->scoreProductForQuery($product, $tokens, $query);
            if ($score >= (float) config('recommendations.search.min_score', 0.25)) {
                $scored[$product->id] = $score;
            }
        }
        arsort($scored);

        $products = collect();
        foreach ($scored as $id => $score) {
            $product = $corpus->get($id);
            if (!$product) {
                continue;
            }
            $products->push($product);
        }

        $top = $products->first();
        $bestMatch = null;
        $isFuzzy = false;
        $queryLower = mb_strtolower($query);
        $skuHit = $corpus->contains(fn (Product $p) => $p->product_id && mb_strtolower((string) $p->product_id) === $queryLower);

        if ($top && !$skuHit) {
            $topName = mb_strtolower((string) $top->name);
            $queryFlat = $this->flatten($query);
            $nameFlat = $this->flatten($topName);
            $isExact = $queryFlat !== '' && $nameFlat !== ''
                && (mb_strpos($nameFlat, $queryFlat) !== false || mb_strpos($queryFlat, $nameFlat) !== false);

            if ($topName !== $queryLower && !$isExact) {
                $bestMatch = $top->name;
                $isFuzzy = true;
            }
        }

        if ($products->isEmpty()) {
            $products = $this->trending(12);
        }

        $this->trackSearch($query, $userId, $guestId, $products->count());

        return ['products' => $products, 'bestMatch' => $bestMatch, 'isFuzzy' => $isFuzzy];
    }

    /**
     * Raw trend score for every product id.
     *
     * @return array<int,float>
     */
    public function trendScores(): array
    {
        if ($this->trendScoresCache !== null) {
            return $this->trendScoresCache;
        }

        $since = now()->subDays((int) config('recommendations.trend_days', 30));
        $decay = number_format((float) config('recommendations.decay_per_day', 0.06), 4, '.', '');
        $decaySql = "EXP(-TIMESTAMPDIFF(DAY, created_at, NOW()) * {$decay})";
        $weights = config('recommendations.trend_weights');

        $scores = [];

        $apply = function (iterable $rows, float $weight) use (&$scores) {
            foreach ($rows as $row) {
                if (!$row->product_id) {
                    continue;
                }
                $scores[$row->product_id] = ($scores[$row->product_id] ?? 0) + ((float) $row->s * $weight);
            }
        };

        $apply(
            ProductView::where('created_at', '>=', $since)
                ->selectRaw("product_id, SUM({$decaySql}) AS s")
                ->groupBy('product_id')
                ->get(),
            (float) ($weights['view'] ?? 1)
        );

        $apply(
            WishlistItem::where('created_at', '>=', $since)
                ->selectRaw("product_id, SUM({$decaySql}) AS s")
                ->groupBy('product_id')
                ->get(),
            (float) ($weights['wishlist'] ?? 2.5)
        );

        $apply(
            CartItem::where('created_at', '>=', $since)
                ->selectRaw("product_id, SUM({$decaySql}) AS s")
                ->groupBy('product_id')
                ->get(),
            (float) ($weights['cart'] ?? 3)
        );

        $apply(
            OrderItem::where('created_at', '>=', $since)
                ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
                ->selectRaw("product_id, SUM({$decaySql} * quantity) AS s")
                ->groupBy('product_id')
                ->get(),
            (float) ($weights['order'] ?? 5)
        );

        // Most-searched terms mapped onto products whose name contains them.
        $terms = UserSearchLog::where('created_at', '>=', $since)
            ->selectRaw("LOWER(TRIM(query)) AS q, SUM({$decaySql}) AS s")
            ->groupBy('q')
            ->orderByDesc('s')
            ->limit(100)
            ->get();

        $searchWeight = (float) ($weights['search'] ?? 3);
        foreach ($terms as $term) {
            $query = $term->q;
            if ($query === '') {
                continue;
            }
            foreach ($this->corpus() as $product) {
                if (mb_stripos((string) $product->name, $query) !== false) {
                    $scores[$product->id] = ($scores[$product->id] ?? 0) + ((float) $term->s * $searchWeight);
                }
            }
        }

        return $this->trendScoresCache = $scores;
    }

    /**
     * Personalization scores for a user/guest, computed from their searches,
     * views, wishlist, cart and order history.
     *
     * @return array<int,float>
     */
    public function personalScores(?int $userId, ?string $guestId): array
    {
        $key = ($userId ?? 'u').'|'.($guestId ?? 'g');
        if (isset($this->personalScoresCache[$key])) {
            return $this->personalScoresCache[$key];
        }

        $profile = $this->userProfile($userId, $guestId);

        if (empty($profile['tokens']) && empty($profile['categories'])) {
            return $this->personalScoresCache[$key] = [];
        }

        $scores = [];
        foreach ($this->corpus() as $product) {
            $score = 0.0;
            foreach ($this->tokenize((string) $product->name) as $token) {
                $score += $profile['tokens'][$token] ?? 0;
            }
            foreach ($this->productCategoryIds($product) as $categoryId) {
                $score += ($profile['categories'][$categoryId] ?? 0) * 0.6;
            }
            if ($score > 0) {
                $scores[$product->id] = $score;
            }
        }

        return $this->personalScoresCache[$key] = $scores;
    }

    /**
     * @return array{tokens: array<string,float>, categories: array<int,float>}
     */
    private function userProfile(?int $userId, ?string $guestId): array
    {
        $since = now()->subDays((int) config('recommendations.trend_days', 30));

        $tokens = [];
        $categories = [];

        $addTerms = function (iterable $terms, float $weight) use (&$tokens) {
            foreach ($terms as $term) {
                if ($term === '' || $term === null) {
                    continue;
                }
                $tokens[$term] = ($tokens[$term] ?? 0) + $weight;
            }
        };

        UserSearchLog::where('created_at', '>=', $since)
            ->where(function ($q) use ($userId, $guestId) {
                $q->where('user_id', $userId)->orWhere('guest_id', $guestId);
            })
            ->pluck('query')
            ->each(function ($query) use ($addTerms) {
                $addTerms($this->tokenize((string) $query), 1.0);
            });

        $productIds = collect();
        if ($userId) {
            $productIds = $productIds
                ->merge(WishlistItem::where('user_id', $userId)->pluck('product_id'))
                ->merge(CartItem::where('user_id', $userId)->pluck('product_id'))
                ->merge(ProductView::where('user_id', $userId)->pluck('product_id'))
                ->merge(
                    OrderItem::whereHas('order', fn ($q) => $q->where('user_id', $userId))
                        ->pluck('product_id')
                );
        }
        if ($guestId) {
            $productIds = $productIds->merge(ProductView::where('guest_id', $guestId)->pluck('product_id'));
        }

        $productIds->unique()->filter()->each(function ($id) use (&$tokens, &$categories, $addTerms) {
            $product = $this->corpus()->get($id);
            if (!$product) {
                return;
            }
            $addTerms($this->tokenize((string) $product->name), 1.0);
            foreach ($this->productCategoryIds($product) as $categoryId) {
                $categories[$categoryId] = ($categories[$categoryId] ?? 0) + 1;
            }
        });

        return ['tokens' => $tokens, 'categories' => $categories];
    }

    /**
     * Score how well a single product matches the search tokens.
     */
    private function scoreProductForQuery(Product $product, array $tokens, string $query): float
    {
        if ($product->product_id && mb_stripos((string) $product->product_id, $query) !== false) {
            return 1.2;
        }

        $name = mb_strtolower((string) $product->name);
        $description = mb_strtolower((string) $product->description);

        // Whole-name pass: removes separators and spaces so "press-on",
        // "presson" and "presson" all compare against "presson" evenly.
        $queryFlat = $this->flatten($query);
        $nameFlat = $this->flatten($name);

        $wholeScore = 0.0;
        if ($queryFlat !== '' && $nameFlat !== '') {
            if ($queryFlat === $nameFlat) {
                $wholeScore = 1.0;
            } elseif (mb_strpos($nameFlat, $queryFlat) !== false || mb_strpos($queryFlat, $nameFlat) !== false) {
                $wholeScore = 0.85;
            } else {
                $percent = 0.0;
                similar_text($queryFlat, $nameFlat, $percent);
                if ($percent / 100 >= 0.6) {
                    $wholeScore = $percent / 100;
                }
            }
        }

        $categoryNames = collect();
        foreach ($product->categories as $category) {
            $categoryNames->push(mb_strtolower((string) $category->name));
        }
        if ($product->category) {
            $categoryNames->push(mb_strtolower((string) $product->category->name));
        }
        $categoryNames = $categoryNames->unique();

        $nameTokens = $this->tokenize($name);

        $perToken = [];
        foreach ($tokens as $token) {
            $best = 0.0;
            foreach ($nameTokens as $nameToken) {
                $best = max($best, $this->tokenMatchScore($token, $nameToken));
            }
            if (mb_strpos($name, $token) === 0) {
                $best = max($best, 0.85);
            }
            if (mb_strpos($name, $token) !== false) {
                $best = max($best, 0.75);
            }
            $perToken[] = $best;
        }

        $score = max(array_sum($perToken) / max(count($tokens), 1), $wholeScore);

        $bonus = 0.0;
        foreach ($tokens as $token) {
            foreach ($categoryNames as $categoryName) {
                if (mb_strpos($categoryName, $token) !== false) {
                    $bonus += 0.3;
                    break;
                }
            }
            foreach ($categoryNames as $categoryName) {
                foreach ($this->tokenize($categoryName) as $categoryToken) {
                    if ($this->tokenMatchScore($token, $categoryToken) >= 0.6) {
                        $bonus += 0.25;
                        break 2;
                    }
                }
            }
            if (mb_strpos($description, $token) !== false) {
                $bonus += 0.03;
            }
        }

        return $score + min($bonus, 0.8);
    }

    /**
     * Normalise a string for whole-name comparison: lowercase and strip
     * spaces, hyphens, underscores and slashes.
     */
    private function flatten(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        return mb_strtolower((string) preg_replace('/[\s\-_\/]+/u', '', $text));
    }

    /**
     * Similarity between two single tokens, 0..1.
     */
    private function tokenMatchScore(string $a, string $b): float
    {
        if ($a === $b) {
            return 1.0;
        }
        if ($a === '' || $b === '') {
            return 0.0;
        }
        if (str_starts_with($a, $b) || str_starts_with($b, $a)) {
            return 0.8;
        }

        $percent = 0.0;
        similar_text($a, $b, $percent);

        return $percent / 100 >= 0.6 ? $percent / 100 : 0.0;
    }

    /**
     * Lowercase tokens from a string.
     *
     * @return string[]
     */
    private function tokenize(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? '';
        $tokens = array_filter(explode(' ', $text), fn ($t) => mb_strlen($t) > 1);

        return array_values($tokens);
    }

    /**
     * @return int[]
     */
    private function productCategoryIds(Product $product): array
    {
        $ids = [];
        if ($product->category_id) {
            $ids[] = $product->category_id;
        }
        foreach ($product->categories as $category) {
            $ids[] = $category->id;
        }

        return array_values(array_unique($ids));
    }

    /**
     * Scale a score map to 0..1.
     *
     * @param array<int,float> $scores
     * @return array<int,float>
     */
    private function normalize(array $scores): array
    {
        $max = max($scores ?: [0.0]);
        if ($max <= 0) {
            return [];
        }

        $normalized = [];
        foreach ($scores as $id => $score) {
            $normalized[$id] = $score / $max;
        }

        return $normalized;
    }
}
