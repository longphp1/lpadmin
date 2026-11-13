<?php

namespace App\Console\Commands;

use App\Models\LPadmin\Website\MenuConfig;
use App\Models\LPadmin\Website\ProductMenu;
use App\Models\LPadmin\Website\WebsitePlatform;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class GenerateNextWebsiteCommand extends Command
{
    protected $signature = 'generate:website_next {--path=/Users/xiaoxiaolu/work/tongxiao/globalBuy : Next.js 应用目录}';

    protected $description = '把商品数据导出为 Next.js 站点（写入 globalBuy，可部署到 Vercel）';

    public function handle(): int
    {
        $target = rtrim((string) $this->option('path'), '/');
        if (!File::exists($target . '/package.json')) {
            $this->error('目录不是 Next.js 项目: ' . $target);
            return 1;
        }

        $platforms = WebsitePlatform::query()->get();
        if ($platforms->isEmpty()) {
            $this->error('没有可生成的平台');
            return 1;
        }

        $catalogs = [];
        foreach ($platforms->pluck('site_id')->unique() as $siteId) {
            $catalogs[(string) $siteId] = $this->buildCatalog((int) $siteId);
            $this->info('站点数据 site_id=' . $siteId . ' 商品 ' . count($catalogs[(string) $siteId]['products']));
        }

        $sites = [];
        foreach ($platforms as $platform) {
            $slug = strtolower($platform->platform_name);
            $sites[] = [
                'slug'   => $slug,
                'name'   => $platform->platform_name,
                'url'    => $platform->platform_url,
                'title'  => $platform->meta_title ?: $platform->platform_name,
                'siteId' => (string) $platform->site_id,
            ];
            $this->copyFavicon($platform->platform_name, $target . '/public/favicons/' . $slug . '.ico');
        }

        $payload = [
            'pageSize'        => 50,
            'generatedAt'     => now()->toIso8601String(),
            'defaultLanguage' => 'en',
            'languages'       => [
                ['code' => 'en', 'label' => 'English'],
                ['code' => 'zh', 'label' => '中文'],
            ],
            'catalogs'        => $catalogs,
            'sites'           => $sites,
        ];

        File::ensureDirectoryExists($target . '/data');
        $jsonPath = $target . '/data/catalog.json';
        File::put($jsonPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info('写入 ' . $jsonPath);

        $this->syncDir(public_path('baseNew/css'), $target . '/public/css');
        $this->syncDir(public_path('baseNew/img'), $target . '/public/img');
        $this->syncDir(public_path('baseNew/fonts'), $target . '/public/fonts');
        $this->syncDir(public_path('baseNew/products'), $target . '/public/products');

        $this->info('Next.js 站点已更新: ' . $target);
        $this->line('本地预览: cd ' . $target . ' && npm run dev');
        $this->line('部署 Vercel 时，把该目录作为项目根目录。data/catalog.json 和 public 下的图片需要一起上传。');

        return 0;
    }

    private function buildCatalog(int $siteId): array
    {
        $menus = MenuConfig::query()
            ->where('site_id', $siteId)
            ->where('status', 0)
            ->where('type', 2)
            ->orderBy('range')
            ->get(['menu_name', 'menu_name_en', 'menu_url']);

        $products = ProductMenu::query()
            ->with(['local', 'menu'])
            ->where('site_id', $siteId)
            ->orderByDesc('id')
            ->get();

        $seen = [];
        $rows = [];
        foreach ($products as $product) {
            $slug = $this->productSlug($product->name, (int) $product->id);
            if (isset($seen[$slug])) {
                continue;
            }
            $seen[$slug] = true;
            $image = '';
            if (!empty($product->local->main_img_path)) {
                $image = $product->local->main_img_path;
            } elseif (!empty($product->main_img)) {
                $image = $product->main_img;
            }
            $rows[] = [
                'id'               => (int) $product->id,
                'slug'             => $slug,
                'name'             => (string) $product->name,
                'price'            => (string) $product->price,
                'brand'            => (string) ($product->brand ?: 'Other'),
                'menuName'         => (string) ($product->menu->menu_name ?? 'Home'),
                'menuUrl'          => (string) ($product->menu->menu_url ?? 'home'),
                'image'            => $image,
                'sourceProductId'  => (string) $product->source_product_id,
                'sourceProductUrl' => (string) $product->source_product_url,
                'taoBuyLink'       => (string) $product->tao_buy_link,
            ];
        }

        return [
            'menus'    => $menus->map(function ($menu) {
                return [
                    'name'   => $menu->menu_name,
                    'nameEn' => $menu->menu_name_en ?: $menu->menu_name,
                    'url'    => $menu->menu_url,
                ];
            })->values()->all(),
            'products' => $rows,
        ];
    }

    private function productSlug(string $name, int $id): string
    {
        $slug = preg_replace('/[^a-zA-Z0-9 ]/', '', $name);
        $slug = strtolower(str_replace(' ', '-', (string) $slug));
        $slug = trim($slug, '-');
        return $slug !== '' ? $slug : 'product-' . $id;
    }

    private function copyFavicon(string $platformName, string $dest): void
    {
        $source = public_path('baseNew/' . $platformName . '.ico');
        if (!File::exists($source)) {
            foreach (File::glob(public_path('baseNew/*.ico')) as $file) {
                if (strcasecmp(pathinfo($file, PATHINFO_FILENAME), $platformName) === 0) {
                    $source = $file;
                    break;
                }
            }
        }
        if (!File::exists($source)) {
            $this->warn('缺少图标: ' . $platformName);
            return;
        }
        File::ensureDirectoryExists(dirname($dest));
        File::copy($source, $dest);
    }

    private function syncDir(string $from, string $to): void
    {
        if (!File::isDirectory($from)) {
            $this->warn('跳过不存在的目录: ' . $from);
            return;
        }
        File::ensureDirectoryExists($to);
        $process = new Process(['rsync', '-a', '--delete', rtrim($from, '/') . '/', rtrim($to, '/') . '/']);
        $process->setTimeout(600);
        $process->mustRun();
        $this->info('同步 ' . $from . ' -> ' . $to);
    }
}
