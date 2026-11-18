<?php

namespace App\Services\Website;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class HiobuyProductService
{
    public function fetchByUrls(array $urls, int $concurrency = 10): array
    {
        $apiKey = config('services.hiobuy.key');
        if (empty($apiKey)) {
            throw new RuntimeException('未配置 HIOBUY_API_KEY，无法查询商品信息');
        }
        $endpoint = rtrim((string) config('services.hiobuy.base_url'), '/') . '/v1/products/detail';

        $validUrls = [];
        $skipped = [];
        foreach ($urls as $url) {
            $url = trim((string) $url);
            if ($url === '') {
                continue;
            }
            if (!preg_match('/^https?:\/\//i', $url)) {
                $url = 'https://' . $url;
            }
            $channel = $this->detectChannel($url);
            if ($channel === null) {
                $skipped[] = ['url' => $url, 'error' => '无法识别商品链接'];
                continue;
            }
            $hash = md5($url);
            if (!isset($validUrls[$hash])) {
                $validUrls[$hash] = ['url' => $url, 'channel' => $channel];
            }
        }

        $results = [];
        foreach (array_chunk($validUrls, $concurrency) as $chunk) {
            $pool = Http::pool(function ($pool) use ($apiKey, $endpoint, $chunk) {
                foreach ($chunk as $item) {
                    $pool->withToken($apiKey)
                        ->acceptJson()
                        ->asJson()
                        ->timeout(40)
                        ->post($endpoint, [
                            'channel'         => $item['channel'],
                            'url'             => $item['url'],
                            'language'        => 'en',
                            'response_format' => 'standard',
                        ]);
                }
            });

            foreach ($chunk as $idx => $item) {
                $response = $pool[$idx] ?? null;
                $results[] = $this->parsePoolResponse($item['url'], $response);
            }
        }

        return [
            'results' => $results,
            'skipped' => $skipped,
        ];
    }

    private function parsePoolResponse(string $url, $response): array
    {
        if ($response === null) {
            return ['url' => $url, 'success' => false, 'error' => '请求失败（无响应）'];
        }
        if ($response instanceof \Throwable) {
            return ['url' => $url, 'success' => false, 'error' => '请求异常：' . $response->getMessage()];
        }
        if (!$response->successful()) {
            $message = $response->json('message')
                ?? $response->json('error')
                ?? $response->json('msg')
                ?? ('接口返回 ' . $response->status());
            return ['url' => $url, 'success' => false, 'error' => is_string($message) ? $message : '商品接口查询失败'];
        }
        $product = $response->json('product');
        if (!is_array($product) || empty($product)) {
            return ['url' => $url, 'success' => false, 'error' => '接口未返回商品信息'];
        }
        try {
            return ['url' => $url, 'success' => true, 'product' => $this->mapProduct($product, $url)];
        } catch (\Throwable $e) {
            return ['url' => $url, 'success' => false, 'error' => $e->getMessage()];
        }
    }

    public function fetchByUrl(string $url): array
    {
        $url = trim($url);
        if ($url === '') {
            throw new RuntimeException('请输入商品链接');
        }
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }

        $channel = $this->detectChannel($url);
        if ($channel === null) {
            throw new RuntimeException('无法识别商品链接，请输入 1688、淘宝或微店的商品地址');
        }

        $apiKey = config('services.hiobuy.key');
        if (empty($apiKey)) {
            throw new RuntimeException('未配置 HIOBUY_API_KEY，无法查询商品信息');
        }

        $endpoint = rtrim((string) config('services.hiobuy.base_url'), '/') . '/v1/products/detail';
        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(40)
                ->post($endpoint, [
                    'channel'         => $channel,
                    'url'             => $url,
                    'language'        => 'en',
                    'response_format' => 'standard',
                ]);
        } catch (\Throwable $e) {
            throw new RuntimeException('商品接口请求失败：' . $e->getMessage());
        }

        if (!$response->successful()) {
            $message = $response->json('message')
                ?? $response->json('error')
                ?? $response->json('msg')
                ?? ('商品接口返回 ' . $response->status());
            throw new RuntimeException(is_string($message) ? $message : '商品接口查询失败');
        }

        $product = $response->json('product');
        if (!is_array($product) || empty($product)) {
            throw new RuntimeException('商品接口未返回商品信息');
        }

        return $this->mapProduct($product, $url);
    }

    public function detectChannel(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $haystack = strtolower($url);
        if (str_contains($host, '1688.com') || str_contains($haystack, '1688.com')) {
            return '1688';
        }
        if (str_contains($host, 'weidian.com') || str_contains($haystack, 'weidian.com')) {
            return 'weidian';
        }
        if (
            str_contains($host, 'taobao.com')
            || str_contains($host, 'tmall.com')
            || str_contains($host, 'tb.cn')
            || str_contains($haystack, 'taobao.com')
            || str_contains($haystack, 'tmall.com')
        ) {
            return 'taobao';
        }
        return null;
    }

    public function mapProduct(array $product, string $inputUrl): array
    {
        $title = is_array($product['title'] ?? null) ? $product['title'] : [];
        $name = trim((string) ($title['translated'] ?? ''));
        if ($name === '') {
            $name = trim((string) ($title['original'] ?? ''));
        }
        if ($name === '') {
            throw new RuntimeException('商品接口未返回商品名称');
        }

        $priceInfo = is_array($product['price'] ?? null) ? $product['price'] : [];
        $price = $priceInfo['promotion_amount']
            ?? $priceInfo['display_amount']
            ?? $priceInfo['original_amount']
            ?? '';
        if ($price === '' || $price === null) {
            throw new RuntimeException('商品接口未返回商品价格');
        }
        if (is_numeric($price)) {
            $price = rtrim(rtrim(number_format((float) $price, 2, '.', ''), '0'), '.');
        }

        $mainImg = '';
        $images = [];
        foreach ($product['images'] ?? [] as $image) {
            if (!is_array($image)) {
                continue;
            }
            $imageUrl = trim((string) ($image['url'] ?? ''));
            if ($imageUrl === '') {
                continue;
            }
            if ($mainImg === '' && ($image['type'] ?? '') === 'main') {
                $mainImg = $imageUrl;
                continue;
            }
            $images[] = $imageUrl;
        }
        if ($mainImg === '' && !empty($images)) {
            $mainImg = array_shift($images);
        }

        $channel = (string) ($product['channel'] ?? '');
        if ($channel === 'weidian') {
            $source = 'WD';
            $platform = 'weidian';
        } elseif ($channel === 'taobao') {
            $source = 'TB';
            $platform = 'taobao';
        } elseif ($channel === '1688') {
            $source = '1688';
            $platform = '1688';
        } else {
            $source = $channel;
            $platform = $channel;
        }

        $brand = '';
        $metadata = is_array($product['metadata'] ?? null) ? $product['metadata'] : [];
        if (!empty($metadata['brand']) && is_string($metadata['brand'])) {
            $brand = trim($metadata['brand']);
        }

        $sourceUrl = trim((string) ($product['source_url'] ?? ''));
        if ($sourceUrl === '') {
            $sourceUrl = $inputUrl;
        }

        return [
            'name'               => $name,
            'brand'              => $brand,
            'price'              => (string) $price,
            'source'             => $source,
            'source_product_id'  => (string) ($product['source_product_id'] ?? ''),
            'source_product_url' => $sourceUrl,
            'main_img'           => $mainImg,
            'img'                => implode(',', $images),
            'main_buy_link'      => $sourceUrl,
            'main_buy_platform'  => $platform,
        ];
    }
}
