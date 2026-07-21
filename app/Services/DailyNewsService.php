<?php

declare(strict_types=1);

namespace App\Services;

use DateTime;
use Exception;
use Illuminate\Support\Facades\Http;
use SimplePie\SimplePie;

class DailyNewsService
{
    private const GOLD_KEYWORDS = [
        'fed' => 5,
        'interest' => 4,
        'inflation' => 5,
        'cpi' => 5,
        'ppi' => 4,
        'recession' => 5,
        'geopolitical' => 5,
        'war' => 5,
        'oil' => 3,
        'usd' => 4,
        'gold' => 5,
        'interest rate' => 4,
        'monetary policy' => 5,
        'quantitative easing' => 4,
        'quantitative tightening' => 4,
        'central bank' => 5,
        'economic slowdown' => 4,
        'global supply chain' => 3,
        'economic crisis' => 5,
    ];

    private const CRYPTO_KEYWORDS = [
        'fed' => 5,
        'interest rate' => 5,
        'rate hike' => 5,
        'rate cut' => 5,
        'liquidity' => 4,
        'inflation' => 5,
        'cpi' => 5,
        'recession' => 5,
        'risk-on' => 4,
        'risk-off' => 4,
        'bitcoin' => 5,
        'btc' => 5,
        'etf' => 4,
        'institutional demand' => 4,
        'stablecoin' => 4,
        'exchange inflows' => 4,
        'exchange outflows' => 4,
        'sec' => 5,
        'crypto regulation' => 5,
        'hack' => 5,
        'bankruptcy' => 5,
    ];

    public function generate()
    {
        $now = new DateTime;
        $twoDaysAgo = clone $now;
        $twoDaysAgo->modify('-2 days');

        $goldArticles = [];
        $cryptoArticles = [];
        $rssSources = $this->getRssData();

        // Fetch feeds sequentially to prevent hitting PHP's memory limit
        foreach ($rssSources as $source) {
            $maxRetries = 3;
            $feedArticles = [];

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $feed = new SimplePie;
                    $feed->enable_cache(false);
                    $feed->set_feed_url($source);
                    $feed->set_timeout(30);

                    $feed->set_useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

                    $feed->init();
                    $feed->handle_content_type();

                    if ($feed->error()) {
                        throw new Exception($feed->error());
                    }

                    foreach ($feed->get_items() as $item) {
                        if ($item->get_date() && $item->get_permalink()) {
                            $feedArticles[] = [
                                'title' => (string) $item->get_title(),
                                'contentSnippet' => strip_tags((string) $item->get_description()),
                                'link' => (string) $item->get_permalink(),
                                'pubDate' => (string) $item->get_date('c'),
                            ];
                        }
                        $item->__destruct();
                    }

                    $feed->__destruct();
                    unset($feed);

                    break; // Success — exit retry loop

                } catch (Exception $e) {
                    if (isset($feed)) {
                        $feed->__destruct();
                        unset($feed);
                    }

                    if ($attempt < $maxRetries) {
                        logger()->warning("Feed {$source} failed (attempt {$attempt}/{$maxRetries}): {$e->getMessage()}. Retrying...");
                        sleep($attempt * 5); // 5s, 10s, 15s backoff
                    } else {
                        logger()->error("Feed {$source} failed after {$maxRetries} attempts: {$e->getMessage()}. Skipping.");
                    }
                }
            }

            // Filter immediately to avoid building a massive array
            $goldArticles = array_merge(
                $goldArticles,
                $this->filterHighImpactNews($feedArticles, self::GOLD_KEYWORDS, 'title', $twoDaysAgo, $now)
            );
            $cryptoArticles = array_merge(
                $cryptoArticles,
                $this->filterHighImpactNews($feedArticles, self::CRYPTO_KEYWORDS, 'full', $twoDaysAgo, $now)
            );

            unset($feedArticles);
            gc_collect_cycles();

            sleep(1);
        }

        $this->sortArticles($goldArticles);
        $this->sortArticles($cryptoArticles);

        $goldPrice = $this->getGoldPrice();
        $btcPrice = $this->getBtcPrice();

        $aiPrompts = $this->generateAiPrompts($goldArticles, (string) $goldPrice, $cryptoArticles, (string) $btcPrice);

        $aiAnalysis = [];
        foreach ($aiPrompts as $prompt) {
            $systemMsg = '';
            $userMsg = '';
            foreach ($prompt['messages'] as $msg) {
                if ($msg['role'] === 'system') {
                    $systemMsg = $msg['content'];
                } elseif ($msg['role'] === 'user') {
                    $userMsg = $msg['content'];
                }
            }
            if ($systemMsg && $userMsg) {
                $rawAnalysis = $this->analyze($systemMsg, $userMsg);
                $aiAnalysis[$prompt['asset']] = $this->normalizeAiOutput($rawAnalysis);
            }
        }

        return [
            'dateRange' => "{$twoDaysAgo->format('Y-m-d')} - {$now->format('Y-m-d')}",
            'aiPrompts' => $aiPrompts,
            'aiAnalysis' => $aiAnalysis,
            'gold' => [
                'count' => count($goldArticles),
                'currentPrice' => $goldPrice,
                'articles' => $goldArticles,
            ],
            'crypto' => [
                'count' => count($cryptoArticles),
                'currentPrice' => $btcPrice,
                'articles' => $cryptoArticles,
            ],
        ];
    }

    private function analyze(string $systemPrompt, string $userPrompt)
    {
        $output = Http::withHeaders([
            'x-goog-api-key' => config('services.gemini.key'),
            'Content-Type' => 'application/json',
        ])->timeout(300)->post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
            [
                'system_instruction' => [
                    'parts' => [
                        [
                            'text' => $systemPrompt,
                        ],
                    ],
                ],
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $userPrompt,
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 1.0,
                    'topP' => 0.8,
                    'topK' => 10,
                ],
            ]
        );
        $responseData = $output->json();

        return $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'No analysis generated.';
    }

    private function normalizeAiOutput(string $rawOutput): array
    {
        $raw = json_decode(trim(str_replace(['```json', '```'], '', $rawOutput)), true);

        $raw = is_array($raw) ? $raw : [];

        $rawScore = $raw['summary']['confidence_score'] ?? null;
        $confidence = null;

        if (is_numeric($rawScore)) {
            $score = (float) $rawScore;
            // Handle 0.0-1.0 scale
            if ($score <= 1 && $score > 0) {
                $score *= 10;
            }
            // Handle 0-100 scale
            elseif ($score > 10) {
                $score /= 10;
            }

            $score = intval(round($score));
            $score = min(10, max(0, $score)); // clamp between 0 and 10

            $confidence = "{$score}/10";
        }

        return [
            'asset' => $raw['asset'] ?? null,
            'bias' => $raw['summary']['bias'] ?? null,
            'confidence' => $confidence,
            'key_driver' => $raw['key_driver']['theme'] ?? null,
            'source' => $raw['top_news_source']['source_name'] ?? null,
            'data' => $raw,
        ];
    }

    private function getGoldPrice()
    {
        try {
            $response = Http::get('https://giavang.now/api/prices', [
                'type' => 'XAUUSD',
            ]);

            return $response->json('buy');
        } catch (Exception $e) {
            return null;
        }
    }

    private function getBtcPrice()
    {
        try {
            $response = Http::get('https://api.binance.com/api/v3/ticker/price', [
                'symbol' => 'BTCUSDT',
            ]);

            return $response->json('price');
        } catch (Exception $e) {
            return null;
        }
    }

    private function getRssData()
    {
        return [
            // Gold & Commodities
            'https://www.nasdaq.com/feed/rssoutbound?category=Commodities',
            'https://news.google.com/rss/search?q=gold+price+OR+XAUUSD+OR+precious+metals&hl=en&gl=US&ceid=US:en',
            'https://www.fxstreet.com/rss/news?f=gold',
            'https://www.fxstreet.com/rss/news',
            'https://feeds.bloomberg.com/markets/news.rss',
            'https://www.investing.com/rss/news_1.rss',
            'https://www.investing.com/rss/news_285.rss',
            'https://finance.yahoo.com/rss/',

            // Crypto
            'https://www.coindesk.com/arc/outboundfeeds/rss/',
            'https://cointelegraph.com/rss',
            'https://decrypt.co/feed',
            'https://cryptoslate.com/feed/',
            'https://www.ethnews.com/rss',
            'https://app.chaingpt.org/rssfeeds.xml',
        ];
    }

    /**
     * Filters and scores news articles based on keywords and recency.
     *
     * @param  array  $articles  Let's assume structure: [['title' => '', 'contentSnippet' => '', 'link' => '', 'pubDate' => ''], ...]
     * @param  array  $keywords  Discovered keywords mapped to score weight
     * @param  string  $scanMode  'title' or 'full' Let's know if we scan just the title or content as well
     * @param  DateTime  $twoDaysAgo  Start date of timeframe
     * @param  DateTime  $now  End date of timeframe
     * @return array
     */
    private function filterHighImpactNews(array $articles, array $keywords, string $scanMode, DateTime $twoDaysAgo, DateTime $now)
    {
        $scoredArticles = [];

        foreach ($articles as $item) {
            $link = $item['link'] ?? $item['url'] ?? null;
            if (!$this->isValidAbsoluteUrl($link)) {
                continue;
            }

            $pubDateRaw = $item['pubDate'] ?? $item['isoDate'] ?? null;
            if (!$pubDateRaw) {
                continue;
            }

            try {
                $pubDate = new DateTime($pubDateRaw);
            } catch (Exception $e) {
                continue; // invalid date
            }

            // Target articles only within the last 2 days
            if ($pubDate < $twoDaysAgo || $pubDate > $now) {
                continue;
            }

            $title = $item['title'] ?? '';
            $content = $item['contentSnippet'] ?? $item['content'] ?? '';

            $text = $scanMode === 'title'
                ? strtolower($title)
                : strtolower($title.' '.$content);

            $score = 0;
            foreach ($keywords as $word => $weight) {
                if (str_contains($text, strtolower($word))) {
                    $score += $weight;
                }
            }

            if ($score === 0) {
                continue; // If 0 score, skip
            }

            $scoredArticles[] = [
                'title' => $title,
                'content' => $content,
                'pubDate' => $pubDate->format(DateTime::ATOM),
                'link' => $link,
                'score' => $score,
            ];
        }

        return $scoredArticles;
    }

    private function sortArticles(array &$articles)
    {
        usort($articles, function ($a, $b) {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }

            return strtotime($b['pubDate']) <=> strtotime($a['pubDate']);
        });
    }

    private function isValidAbsoluteUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        // Must start with http:// or https://
        if (preg_match('/^https?:\/\/.+/i', $url) !== 1) {
            return false;
        }

        // Reject URLs with broken template variables (e.g., /undefined/undefined/ from ChainGPT)
        $brokenPatterns = ['/undefined/', '/null/', '/[object'];
        foreach ($brokenPatterns as $pattern) {
            if (str_contains($url, $pattern)) {
                return false;
            }
        }

        return true;
    }

    private const SYSTEM_MESSAGES = [
        'gold' => "You are a high-conviction gold macro analyst.\nFocus only on price-relevant macro & institutional signals.\nWeigh credibility heavily. Analysis only (DYOR).\nRespond exclusively in valid JSON.",
        'crypto' => "You are a high-conviction Bitcoin macro analyst.\nFocus only on price-relevant macro & institutional signals.\nIgnore hype and opinion pieces.\nWeigh credibility heavily. Analysis only (DYOR).\nRespond exclusively in valid JSON.",
    ];

    private function formatArticlesForPrompt(array $articles): string
    {
        $formatted = [];
        foreach ($articles as $index => $article) {
            $link = $article['link'] ?? $article['url'] ?? 'N/A';
            $content = $article['content'] ?? $article['contentSnippet'] ?? '';
            $num = $index + 1;
            $formatted[] = "ARTICLE {$num}\nLink: {$link}\nContent:\n{$content}\n";
        }

        return implode("\n---\n\n", $formatted);
    }

    private function buildUserPrompt(string $asset, array $articles, string $price): string
    {
        $schema = $asset === 'gold' ? '{
        "asset": "gold",
        "timestamp_utc": "",
        "summary": {
            "bias": "Bullish | Bearish",
            "confidence_score": 0,
            "price_direction_24h": "Up | Down",
            "price_direction_7d": "Up | Down"
        },
        "key_driver": {
            "theme": "",
            "explanation": ""
        },
        "market_context": {
            "usd_dynamics": "",
            "real_yields": "",
            "risk_sentiment": ""
        },
        "top_news_source": {
            "source_name": "",
            "headline": "",
            "url": ""
        },
        "risk_factors": []
        }' : '{
        "asset": "crypto",
        "timestamp_utc": "",
        "summary": {
            "bias": "Bullish | Bearish",
            "confidence_score": 0,
            "price_direction_24h": "Up | Down",
            "price_direction_7d": "Up | Down"
        },
        "key_driver": {
            "theme": "",
            "explanation": ""
        },
        "market_context": {
            "liquidity": "",
            "risk_regime": "",
            "institutional_flows": ""
        },
        "top_news_source": {
            "source_name": "",
            "headline": "",
            "url": ""
        },
        "risk_factors": []
        }';

        $ticker = $asset === 'gold' ? 'XAU/USD' : 'BTC/USD';
        $priceString = empty($price) ? 'Unknown' : $price;
        $articlesString = $this->formatArticlesForPrompt($articles);

        return <<<PROMPT
        📰 NEWS INPUT:
        {$articlesString}

        💰 Current {$ticker} Price:
        {$priceString}

        =========================
        STRICT OUTPUT RULES
        =========================
        • Return VALID JSON ONLY
        • Use EXACT schema below
        • DO NOT add/remove/rename fields
        • confidence_score MUST be an integer from 0 to 10
        • price_direction_24h MUST be exactly "Up" or "Down" only (no "Neutral" or "Up/Neutral")
        • DO NOT use markdown or code blocks
        • Never omit fields
        • If uncertain, use null or "Unknown"

        =========================
        JSON SCHEMA
        =========================
        {$schema}
        PROMPT;
    }

    private function generateAiPrompts(array $goldArticles, string $goldPrice, array $cryptoArticles, string $btcPrice): array
    {
        $outputs = [];

        if (count($goldArticles) > 0) {
            $outputs[] = [
                'asset' => 'gold',
                'messages' => [
                    ['role' => 'system', 'content' => self::SYSTEM_MESSAGES['gold']],
                    [
                        'role' => 'user',
                        'content' => $this->buildUserPrompt('gold', $goldArticles, $goldPrice),
                    ],
                ],
            ];
        }

        if (count($cryptoArticles) > 0) {
            $outputs[] = [
                'asset' => 'crypto',
                'messages' => [
                    ['role' => 'system', 'content' => self::SYSTEM_MESSAGES['crypto']],
                    [
                        'role' => 'user',
                        'content' => $this->buildUserPrompt('crypto', $cryptoArticles, $btcPrice),
                    ],
                ],
            ];
        }

        return $outputs;
    }
}
