<?php

namespace App\Services;

use DateTime;
use Exception;
use Illuminate\Support\Facades\Http;
use SimplePie\SimplePie;

class DailyNewsService
{
    private const GOLD_KEYWORDS = [
        "fed" => 5,
        "interest" => 4,
        "inflation" => 5,
        "cpi" => 5,
        "ppi" => 4,
        "recession" => 5,
        "geopolitical" => 5,
        "war" => 5,
        "oil" => 3,
        "usd" => 4,
        "gold" => 5,
        "interest rate" => 4,
        "monetary policy" => 5,
        "quantitative easing" => 4,
        "quantitative tightening" => 4,
        "central bank" => 5,
        "economic slowdown" => 4,
        "global supply chain" => 3,
        "economic crisis" => 5
    ];

    private const CRYPTO_KEYWORDS = [
        "fed" => 5,
        "interest rate" => 5,
        "rate hike" => 5,
        "rate cut" => 5,
        "liquidity" => 4,
        "inflation" => 5,
        "cpi" => 5,
        "recession" => 5,
        "risk-on" => 4,
        "risk-off" => 4,
        "bitcoin" => 5,
        "btc" => 5,
        "etf" => 4,
        "institutional demand" => 4,
        "stablecoin" => 4,
        "exchange inflows" => 4,
        "exchange outflows" => 4,
        "sec" => 5,
        "crypto regulation" => 5,
        "hack" => 5,
        "bankruptcy" => 5
    ];

    public function generate()
    {
        $now = new DateTime();
        $twoDaysAgo = clone $now;
        $twoDaysAgo->modify('-2 days');

        $goldArticles = [];
        $cryptoArticles = [];
        $rssSources = $this->getRssData();

        // Fetch feeds sequentially to prevent hitting PHP's memory limit
        foreach ($rssSources as $source) {
            $feed = new SimplePie();
            $feed->enable_cache(false);
            $feed->set_feed_url($source);

            $feed->set_useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

            $feed->init();
            $feed->handle_content_type();

            if ($feed->error()) {
                throw new Exception("Error fetching feed {$source}: " . $feed->error());
            }

            $feedArticles = [];
            foreach ($feed->get_items() as $item) {
                if ($item->get_date() && $item->get_permalink()) {
                    $feedArticles[] = [
                        'title' => (string) $item->get_title(),
                        'contentSnippet' => strip_tags((string) $item->get_description()),
                        'link' => (string) $item->get_permalink(),
                        'pubDate' => (string) $item->get_date('c'), // ISO 8601 format
                    ];
                }
                $item->__destruct(); 
            }

            $feed->__destruct();
            unset($feed);

            // Filter immediately to avoid building a massive array of all articles
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

        return [
            'dateRange' => "{$twoDaysAgo->format('Y-m-d')} - {$now->format('Y-m-d')}",
            'gold' => [
                'count' => count($goldArticles),
                'currentPrice' => $goldPrice,
                'articles' => $goldArticles
            ],
            'crypto' => [
                'count' => count($cryptoArticles),
                'currentPrice' => $btcPrice,
                'articles' => $cryptoArticles
            ]
        ];
    }

    private function getGoldPrice()
    {
        try {
            $response = Http::get('https://giavang.now/api/prices', [
                'type' => 'XAUUSD'
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
                'symbol' => 'BTCUSDT'
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
            "https://www.nasdaq.com/feed/rssoutbound?category=Commodities",
            "https://news.google.com/rss/search?q=gold+price+OR+XAUUSD+OR+precious+metals&hl=en&gl=US&ceid=US:en",
            "https://www.fxstreet.com/rss/news?f=gold",
            "https://www.fxstreet.com/rss/news",
            "https://feeds.bloomberg.com/markets/news.rss",
            "https://www.investing.com/rss/news_1.rss",
            "https://www.investing.com/rss/news_285.rss",
            "https://finance.yahoo.com/rss/",

            // Crypto
            "https://www.coindesk.com/arc/outboundfeeds/rss/",
            "https://cointelegraph.com/rss",
            "https://decrypt.co/feed",
            "https://cryptoslate.com/feed/",
            "https://www.ethnews.com/rss",
            "https://app.chaingpt.org/rssfeeds.xml",
        ];
    }

    /**
     * Filters and scores news articles based on keywords and recency.
     *
     * @param array $articles Let's assume structure: [['title' => '', 'contentSnippet' => '', 'link' => '', 'pubDate' => ''], ...]
     * @param array $keywords Discovered keywords mapped to score weight
     * @param string $scanMode 'title' or 'full' Let's know if we scan just the title or content as well
     * @param DateTime $twoDaysAgo Start date of timeframe
     * @param DateTime $now End date of timeframe
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
                : strtolower($title . " " . $content);

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
                'score' => $score
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
        if (empty($url) || !is_string($url)) {
            return false;
        }
        return preg_match('/^https?:\/\/.+/i', $url) === 1;
    }
}