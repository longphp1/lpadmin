<?php

namespace App\Console\Commands;

use App\Models\LPadmin\Website\MenuConfig;
use App\Models\LPadmin\Website\ProductMenu;
use App\Models\LPadmin\Website\ProductMenuLocal;
use App\Models\LPadmin\Website\WebSiteConfig;
use App\Models\LPadmin\Website\WebsitePlatform;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class GenerateWebSiteCommandNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:website_new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成网站';

    public $fileName = '';
    public $site_id = 1;
    public $siteConfig = [];

    public $initProductImage = false;

    public $websiteName = 'Taoobuy';

    public $websiteTitle = 'Taoobuy';
    public $websiteUrl = 'https://www.taoobuy.com/en_US';  //https://www.taoobuy.com/en_US  https://cnfans.com  //https://acbuy.com/home   https://mulebuy.com/  https://kakobuy.com/


    public $websiteNameList = ['Kakobuy' => 'Kakobuy', 'Taoobuy' => 'Taoobuy', 'Cnfans' => 'Cnfans', 'Acbuy' => 'Acbuy', 'Mulebuy' => 'Mulebuy'];
    public $websiteUrlList = ['Kakobuy' => 'https://kakobuy.com', 'Taoobuy' => 'https://www.taoobuy.com/en_US', 'Cnfans' => 'https://cnfans.com', 'Acbuy' => 'https://acbuy.com/home', 'Mulebuy' => 'https://mulebuy.com'];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {

        //where('status', 'update_pending')->
        $platformList = WebsitePlatform::get();
        foreach ($platformList as $platform) {
            $this->site_id     = $platform->site_id;
            $this->websiteName = $platform->platform_name;
            $this->websiteUrl  = $platform->platform_url;
            $this->websiteTitle  = $platform->meta_title;
            $this->siteConfig  = WebSiteConfig::query()->find($platform->site_id);
            if (empty($this->websiteName) || empty($this->websiteUrl)){
                $this->error('请输入网站名称和网站URL');
                return 0;
            }
            Log::info('生成网站:'.$this->websiteName.' 网站URL:'.$this->websiteUrl);
            try {
                $this->generateWeb();
                $platform->status='update_success';
                $platform->save();
            } catch (\Exception $e) {
                $this->error('生成网站:'.$this->websiteName.' 网站URL:'.$this->websiteUrl.' error:'.$e->getMessage());
            }
        }

        return 0;
    }



    public function generateWeb()
    {
        if(!$this->initProductImage){
            $this->productDownloadImage();
            $this->initProductImage = true;
        }
        $this->copyWebsitFile();
        $this->createHomeHtml();
        $this->createProductHtml();
        $this->createProductDetailHtml();
        $this->reloadHowTo();
        $this->reloadJs();
        $this->renameIco();
        $this->info('success');
    }

    public function copyWebsitFile()
    {
        $this->fileName  = $this->websiteName;
        $sourcePath      = public_path('baseNew');
        $destinationPath = public_path($this->fileName);
        $this->deleteDirectory($destinationPath);
        // 确保目标目录存在
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        // 复制目录及其所有内容
        File::copyDirectory($sourcePath, $destinationPath);
        Log::info('复制目录及其所有内容:'.$sourcePath.' 到 '.$destinationPath.' 成功');
    }

    protected function compressHTML($html)
    {
        $search = [
            '/\>[^\S ]+/s',         // 删除标签后多余的空格
            '/[^\S ]+\</s',         // 删除标签前多余的空格
            '/(\s)+/s',              // 将多个空格合并为一个
            '/<!--(.|\s)*?-->/',     // 删除 HTML 注释
            '/\n+/',                 // 移除换行
            '/\r+/',                 // 移除回车
        ];

        $replace = [
            '>',
            '<',
            '\\1',
            '',
            ' ',
            ' ',
        ];

        return preg_replace($search, $replace, $html);
    }

    public function createHtml($filePath, $fileName, $content)
    {

        $baseDir      = public_path($this->fileName);
        $baseFilePath = $baseDir . '/' . $filePath;
        // 创建文件夹
        if (!empty($filePath) && !file_exists($baseFilePath)) {
            $this->info('创建文件夹:' . $baseFilePath);
            Log::info('创建文件夹:' . $baseFilePath);
            mkdir($baseFilePath, 0777, true);
        }
        $filePathName = $filePath . '/' . $fileName;
        $path         = $baseFilePath . '/' . $fileName;
        // 创建文件
        if (!file_exists($path)) {
            touch($path);
        }
        //对html内容进行压缩：
        $content = $this->compressHTML($content);
        if (file_put_contents($path, $content) !== false) {
            $this->info('fileName:' . $filePathName . ',创建成功并写入内容');
            Log::info('fileName:' . $filePathName . ',创建成功并写入内容');
        } else {
            $this->info('fileName:' . $filePathName . ',创建失败');
            Log::info('fileName:' . $filePathName . ',创建失败');
        }
    }


    public function createHomeHtml()
    {
        $brandList   = ProductMenu::query()->where('site_id', $this->site_id)->select('brand')->groupBy('brand')->pluck('brand')->toArray();
        $brandList[] = 'All';
        foreach ($brandList as $brand) {
            if ($brand == 'All') {
                $productList = ProductMenu::query()->where('site_id', $this->site_id)->orderBy('id', 'desc')->orderBy('updated_at', 'desc')->get();
            } else {
                $productList = ProductMenu::query()->where('site_id', $this->site_id)->where('brand', $brand)->orderBy('id', 'desc')->orderBy('updated_at', 'desc')->get();
            }
            $total       = count($productList);
            $maxPage     = ceil(count($productList) / 50);
            $currentPage = 1;
            $brandList   = array_unique($brandList);
            $productList->chunk(50)->each(function ($item, $key) use ($brand, $maxPage, &$currentPage, $brandList, $total) {
                $menu_url = 'home';
                $html     = $this->productMenu($menu_url, 'Home', $item, $brand, $brandList, $maxPage, $currentPage, $total);
                if ($currentPage == 1) {
                    if ($brand == 'All') {
                        $filePath = '';
                    } else {
                        $filePath = 'item-type/' . $menu_url . '/' . $brand;
                    }
                    $fileName = 'index.html';
                    $this->createHtml($filePath, $fileName, $html);
                } else {
                    $filePath = 'item-type/' . $menu_url . '/' . $brand . '/' . $currentPage;
                    $fileName = 'index.html';
                    $this->createHtml($filePath, $fileName, $html);
                }
                $currentPage++;
            });
        }
        Log::info('创建首页HTML完成');
    }

    public function createProductHtml()
    {
        $productMenuList = MenuConfig::query()->where('site_id', $this->site_id)->where('type', 2)->get();
        foreach ($productMenuList as $productMenu) {
            $brandList   = ProductMenu::query()->where('site_id', $this->site_id)->where('menu_id', $productMenu->id)->select('brand')->groupBy('brand')->pluck('brand')->toArray();
            $brandList[] = 'All';
            foreach ($brandList as $brand) {
                if ($brand == 'All') {
                    $productList = ProductMenu::query()->where('site_id', $this->site_id)->where('menu_id', $productMenu->id)->orderBy('id', 'desc')->orderBy('updated_at', 'desc')->get();
                } else {
                    $productList = ProductMenu::query()->where('site_id', $this->site_id)->where('menu_id', $productMenu->id)->where('brand', $brand)->orderBy('id', 'desc')->orderBy('updated_at', 'desc')->get();
                }
                $total       = count($productList);
                $maxPage     = ceil(count($productList) / 50);
                $currentPage = 1;
                $brandList   = array_unique($brandList);
                $productList->chunk(50)->each(function ($item, $key) use ($productMenu, $brand, $maxPage, &$currentPage, $brandList, $total) {
                    $html = $this->productMenu($productMenu->menu_url, $productMenu->menu_name, $item, $brand, $brandList, $maxPage, $currentPage, $total);
                    if ($currentPage == 1) {
                        $filePath = 'item-type/' . $productMenu->menu_url . '/' . $brand;
                        $fileName = 'index.html';
                        $this->createHtml($filePath, $fileName, $html);
                    } else {
                        $filePath = 'item-type/' . $productMenu->menu_url . '/' . $brand . '/' . $currentPage;
                        $fileName = 'index.html';
                        $this->createHtml($filePath, $fileName, $html);
                    }
                    $currentPage++;
                });

            }
        }
        Log::info('创建商品分类HTML完成');

    }

    public function productMenu($menu_url, $menu_name, $item, $brand, $brandList, $maxPage, $currentPage, $total)
    {
        $faqTextHtml = '';
        if (strtolower($menu_name) == 'home' && strtolower($brand) == 'all') {
            $faqTextHtml = $this->getFaqText();
        }
        $headerTextHtml  = $this->getHeaderText($menu_name);
        $homeMenuHtml    = $this->getHomeMenu($menu_name);
        $brandHtml       = $this->getBrandText($menu_url, $brandList);
        $productListHtml = $this->getProductList($item);
        $pageHtml        = $this->productPage($currentPage, $maxPage, $menu_url, $brand, $total);
        $tile            = $this->websiteName . ' spreadsheet 2025 Fall/' . $brand;
        $html            = '<!DOCTYPE html>
<html lang="en">
<!-- common header -->

<head>
    <meta charset="UTF-8">
    <title>' . $tile . '</title>
    <link rel="stylesheet" href="/css/home.css">
    <link rel="stylesheet" href="/css/yang.css">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta name="keywords">
    <meta name="description" content="The website is a ' . $this->websiteName . ' spreadsheet containing over 5,000+ ' . $this->websiteName . ' popular Chinese products. New and cheap products are updated every day. You can use &quot;Ctrl+D&quot; to add this ' . $this->websiteName . ' spreadsheet to your bookmarks list. You need to “join discord” to receive messages or communicate with us in time.">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?80e72cd0fe68297e0ee13c3172ccf734";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();

    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-ZTW5W4FHW4"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag("js", new Date());

        gtag("config", "G-ZTW5W4FHW4");
    </script>
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "p92zvqhoq7");
    </script>
</head>

<body data-value="" data-value1="-1" data-value2="-1">

  <header>
        <div class="container flex" >
            <div class="auto">
                <a href="/" class="logo">
                    <h1>' . $this->websiteName . ' Spreadsheet</h1>
                </a>
            </div>
            <div class="nav">
                <a href="#faq">FAQ</a>
                <a href="/howTo">How-To</a>
                <a href="' . $this->websiteUrl . '" target="_blank">Get more discounts</a>
                <a href="' . $this->websiteUrl . '" class="btn">
                    <span class="inner">Join the ' . $this->websiteName . '</span>
                </a>

            </div>
        </div>
    </header>

  <section class="products">
    ' . $headerTextHtml . $homeMenuHtml . $brandHtml . $productListHtml . $pageHtml . '
  </section>
  <!-- common faq -->
  ' . $faqTextHtml . '
  <!-- common help -->
  <section class="help">
        <div class="need">NEED HELP?</div>
        <div class="join">Join the community for 24/7 support!</div>
        <a href="' . $this->websiteUrl . '" class="discord">Join the ' . $this->websiteName . '</a>
    </section>
  <!-- common footer -->
  <footer>
        <div class="container" id="footer">
            <div>&copy; ' . $this->websiteName . ' Spreadsheet. 2025</div>
            <div>
                <img src="/img/discord.png" alt="">
                <img src="/img/reddit.png" alt="">
            </div>
        </div>
    </footer>
  <div class="join-discord" id="join-discord">
    <div class="title">WHY JOIN DISCORD</div>
    <img src="/img/close-black.png" width="20" id="discord-close" alt="">
    <div class="lines">
      <div>Send product photos to our customer service, and they"ll help you find what you"re looking for！</div>
      <div>Ready to place an order? contact our customer service on discord to get a more discount！</div>
    </div>
    <a href="' . $this->websiteUrl . '" class="link">Join  ' . $this->websiteName . '</a>
  </div>
  <script type="text/javascript" src="/js/site.js"></script>
</body>

</html>';

        return $html;
    }


    public function getFaqText()
    {
        return '  <section class="faq" id="faq">
                    <div class="color-title">
                        <span>FAQ</span>
                    </div>
                    <div class="list" id="faq-list">

                    </div>
                </section>';
    }

    public function getHeaderText($menu_name)
    {
        if ($menu_name == 'Home') {
            return '<div class="color-title">
                        <span><a href="/">' . $this->websiteName . '</a></span>
                        <div class="textDesc">
                            <p>            1、The website is a ' . $this->websiteName . ' spreadsheet containing over 5,000+ ' . $this->websiteName . ' popular Chinese products. New and cheap products are updated every day.</p>
                            <p>            2、You can use "Ctrl+D" to add this ' . $this->websiteName . ' spreadsheet to your bookmarks list.</p>
                            <p>            3、You need to “join discord” to receive messages or communicate with us in time.</p>
                            <h4 class="special-offer">
                            <a href="' . $this->websiteUrl . '" target="_blank">            Special Partnership Offer(welcome Bonus): Register Now to Receive $140 in Coupons +10 off Shipping</a>
                            </h4>
                        </div>
                   </div>';
        }

        return '<div class="color-title">
                    <span><a href="?category=15">' . $menu_name . '</a></span>
                  <div class="textDesc">
                    <h1>
                      <p>This spreadsheet displays ' . $menu_name . '  from ' . $this->websiteName . '. We update this page with more Hoodies  daily.
                      <p>You can use &quot;Ctrl+D&quot; to add this ' . $this->websiteName . ' spreadsheet ' . $menu_name . ' list to your bookmarks list.</p>
                      <p>We sincerely wish you that you can find your favorite ' . $menu_name . '  on this ' . $this->websiteName . ' spreadsheet</p>
                    </h1>
                  </div>
                </div>';
    }


    public function getHomeMenu($menu_name)
    {
        $menuList = MenuConfig::query()->where('status', 0)->orderBy('range', 'asc')->get();
        $html     = '<div class="tab">';
        foreach ($menuList as $menu) {
            if ($menu->type != 2) {
                continue;
            }
            if ($menu->menu_name == 'Home') {
                $menuUrl = '/';
            } else {
                $menuUrl = '/item-type/' . $menu->menu_url . '/All/';
            }
            if ($menu->menu_name == $menu_name) {

                $html .= '<a href="' . $menuUrl . '" class="active"><h2>' . $menu->menu_name . '</h2></a>';
            } else {
                $html .= '<a href="' . $menuUrl . '" class="no"><h2>' . $menu->menu_name . '</h2></a>';
            }
        }
        $html .= '</div>';
        return $html;
    }

    public function getBrandText($menu_url, $brandList)
    {
        $len  = 18;
        $html = '<div class="tag container">
      <div class="flex title">
        <div>Product Categories</div>
        <a href="javascript:void(0)" id="more-collapse">More</a>
      </div>
      <div class="content">';
        $i    = 1;
        foreach ($brandList as $brand) {
            if ($i <= $len) {
                $html .= '<span><a href="/item-type/' . $menu_url . '/' . $brand . '"class="no">' . $brand . '</a></span>';
            } else {
                $html .= '<span class="more-tag"><a href="/item-type/' . $menu_url . '/' . $brand . '"class="no">' . $brand . '</a></span>';
            }
            $i++;
        }
        $html .= '</div></div>';
        return $html;
    }

    public function getProductList($productList)
    {
        $exitProductList = [];
        $html            = '<div class="list">';
        foreach ($productList as $product) {
            $newProductName = preg_replace('/[^a-zA-Z0-9 ]/', '', $product->name);
            $newProductName = strtolower(str_replace(' ', '-', $newProductName));
            if (in_array($newProductName, $exitProductList)) {
                continue;
            }
            $exitProductList[] = $newProductName;
            $productUrl        = "/products/" . $newProductName;
            $mainImg           = isset($product->local->main_img_path) && !empty($product->local->main_img_path) ? $product->local->main_img_path : $product->main_img;

            $html .= '<div href="" class="item">
                        <a href="' . $productUrl . '" target="_blank" class="thumbnail">
                          <img src="' . $mainImg . '" alt="af1 classic white air force one">
                        </a>
                        <div class="bottom">
                          <div class="name">
                            <a href="' . $productUrl . '" target="_blank">' . $product->name . '</a>
                          </div>
                          <div class="price">
                            <a href="' . $productUrl . '" target="_blank">$' . $product->price . '</a>
                          </div>

                          <a href="' . $productUrl . '" target="_blank" class="buy">View details and Buy on ' . $this->websiteName . '</a>
                          <a href="javascript:" class="join-btn">Join ' . $this->websiteName . ' for more discounts</a>
                        </div>
                      </div>';
        }
        $html .= '</div>';
        return $html;
    }

    public function productPage($currentPage, $maxPage, $menu_url, $brand, $total)
    {


        if ($currentPage == 1 || $maxPage == 1) {
            $nextPage    = $currentPage + 1;
            $nextPageUrl = "/item-type/" . $menu_url . "/" . $brand . "/" . $nextPage;
            $html        = '<div class="pagination container">
                              <span class="item">Total Page: ' . $maxPage . ' Total Elements: ' . $total . '</span>
                              <span class="item">' . $currentPage . '</span>
                              <a href="' . $nextPageUrl . '" class="item">Next</a>
                            </div>';
        } elseif ($currentPage == $maxPage) {
            $prePage    = $currentPage - 1;
            $prePageUrl = "/item-type/" . $menu_url . "/" . $brand . "/" . $prePage;
            $html       = '<div class="pagination container">
                              <span class="item">Total Page: ' . $maxPage . ' Total Elements: ' . $total . '</span>
                              <a href="' . $prePageUrl . '" class="item">Previous</a>
                            </div>';
        } else {
            $nextPage    = $currentPage + 1;
            $nextPageUrl = "/item-type/" . $menu_url . "/" . $brand . "/" . $nextPage;
            $prePage     = $currentPage - 1;
            if ($prePage == 1) {
                $prePageUrl = "/item-type/" . $menu_url . "/" . $brand;
            } else {
                $prePageUrl = "/item-type/" . $menu_url . "/" . $brand . "/" . $prePage;
            }

            $html = '<div class="pagination container">
                      <span class="item">Total Page: ' . $maxPage . ' Total Elements: ' . $total . '</span>
                      <a href="' . $prePageUrl . '" class="item">Previous</a>
                      <span class="item">' . $currentPage . '</span>
                      <a href="' . $nextPageUrl . '" class="item">Next</a>
                    </div>
                    ';

        }
        return $html;
    }

    public function createProductDetailHtml()
    {
        $productList = ProductMenu::query()->with(['local', 'menu'])->where('site_id', $this->site_id)->orderBy('id', 'desc')->orderBy('updated_at', 'desc')->get();

        foreach ($productList as $product) {
            $newProductName = preg_replace('/[^a-zA-Z0-9 ]/', '', $product->name);
            $newProductName = strtolower(str_replace(' ', '-', $newProductName));
            $html           = $this->productDetail($product, $newProductName, $product->menu->menu_name, $product->local);
            $filePath       = 'products/' . $newProductName;
            $fileName       = 'index.html';
            $this->createHtml($filePath, $fileName, $html);
        }
        Log::info('创建商品详情HTML完成');
    }

    public function productDetail($product, $productName, $menu_name, $productLocal)
    {
        $homeMenuHtml         = $this->getHomeMenu($menu_name);
        $recommendProductHtml = $this->recommendProductHtml($product, $menu_name);
        $paltformListHtml     = $this->getPaltformList($product);
        $mainImg              = $product->main_img;
        if (isset($productLocal->main_img_path) && !empty($productLocal->main_img_path)) {
            $mainImg = $productLocal->main_img_path;
        }
        $title = $productName . '-' . $product->brand . '-' . $menu_name;
        $html  = '<!DOCTYPE html>
<html lang="en">
<!-- common head -->
<head>
    <meta charset="UTF-8">
    <title>' . $title . '</title>
    <link rel="stylesheet" href="/css/home.css">
    <link rel="stylesheet" href="/css/yang.css">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta name="keywords">
    <meta name="description" content="You can buy this ' . $product->name . ' go to ' . $this->websiteName . '">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?80e72cd0fe68297e0ee13c3172ccf734";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();

    </script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-ZTW5W4FHW4"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag("js", new Date());

        gtag("config", "G-ZTW5W4FHW4");
    </script>
    <!-- 2024-12-05 added -->
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "p92zvqhoq7");
    </script>
</head>
<body>
<!-- common header -->
<header>
        <div class="container flex" >
            <div class="auto">

                <a href="/" class="logo1">
                    ' . $this->websiteName . ' Spreadsheet
                </a>
            </div>
            <div class="nav">
                <a href="#faq">FAQ</a>
                <a href="/howTo">How-To</a>
                <a href="' . $this->websiteUrl . '" target="_blank">Get more discounts</a>
                <a href="' . $this->websiteUrl . '" class="btn">
                    <span class="inner">Join the ' . $this->websiteName . '</span>
                </a>

            </div>
        </div>
    </header>
<section class="breadcrumbs">
  <div class="container">
    <h3>
    <a href="/">' . $this->websiteName . ' Spreadsheet</a>
    <span>/</span>
    <a href="/?category=10" >' . $menu_name . '</a>
    <span>/</span>
    <a href="/?category=10&amp;brand=2" >' . $product->brand . '</a>
    <span>/</span>
    <span>' . $product->name . '</span>
  </h3>
  </div>
</section>
<section class="products">
  <div class="color-title">
    <span><a href="/?category=10&amp;brand=2" >' . $product->brand . '</a></span>
    <h1>You can buy this ' . $product->name . ' go to ' . $this->websiteName . '</h1>
  </div>

  ' . $homeMenuHtml . '

  <div class="detail" >
    <div class="detail-img">
      <img src="' . $mainImg . '" alt="' . $product->name . '">
    </div>
    <div class="detail-info">
      <div class="best">Best Selling</div>
      <div class="price">$' . $product->price . '</div>
      <h1 class="name">' . $product->name . '</h1>
      <a href="#" class="btn buy" id="toggleBtn">Select Buy On Platform</a>
      <a href="javascript:copyText(&#39;' . $product->source_product_url . '&#39;)" class="btn copy">Copy original link</a>
      <a href="javascript:" class="join-btn btn">Join discord for more discounts</a>
      ' . $paltformListHtml . '
    </div>
  </div>
  <div class="container extension">
    <div class="text">Important！ How to bypass <br> “Non - purchasable item” warning automatically!</div>
    <a href="https://chromewebstore.google.com/detail/risk-reminder-remover/afgegfoedkeffjkkkjkbpnegceleakeo?hl=zh-CN&utm_source=ext_sidebar" class="link" target="_blank" >
      <img src="/img/chrome.svg" alt="">
      <span>Download chrome extension</span>
    </a>
  </div>

' . $recommendProductHtml . '
</section>
 <section class="help">
        <div class="need">NEED HELP?</div>
        <div class="join">Join the community for 24/7 support!</div>
        <a href="' . $this->websiteUrl . '" class="discord">Join the ' . $this->websiteName . '</a>
    </section>
<footer>
        <div class="container" id="footer">
            <div>&copy; ' . $this->websiteName . ' Spreadsheet. 2025</div>
            <div>
                <img src="/img/discord.png" alt="">
                <img src="/img/reddit.png" alt="">
            </div>
        </div>
    </footer>
<div class="join-discord" id="join-discord">
  <div class="title">WHY JOIN DISCORD</div>
  <img src="/img/close-black.png" width="20" id="discord-close" alt="">
  <div class="lines">
      <div>Send product photos to our customer service, and they"ll help you find what you"re looking for！</div>
      <div>Ready to place an order? contact our customer service on discord to get a more discount！</div>
  </div>
  <a href="' . $this->websiteUrl . '" class="link">Join ' . $this->websiteName . '</a>
</div>
<script type="text/javascript" src="/js/site.js"></script>
</body>
</html>
';
        return $html;
    }

    public function recommendProductHtml($product, $menu_name)
    {
        // 构建基础查询
        $baseQuery       = ProductMenu::query()
            ->with('local')
            ->where('site_id', $product->site_id)
            ->where('menu_id', $product->menu_id)
            ->where('id', '<>', $product->id)
            ->inRandomOrder();
        $otherBrandQuery = clone $baseQuery;

        // 先尝试获取同品牌产品
        $otherProductList = $baseQuery->where('brand', $product->brand)->get();
        $recommendTitle   = $menu_name;
        if ($otherProductList->count() < 5) {
            $lastLen              = 5 - $otherProductList->count();
            $recommendTitle       = 'Other';
            $lastProductList      = $otherBrandQuery->take($lastLen)->get();
            $recommendProductList = array_merge($otherProductList->toArray(), $lastProductList->toArray());
        } else {
            $recommendProductList = $otherProductList->take(5)->toArray();
        }


        $html = '<div class="container">
    <div class="sub-title">
      <a href="/?category=10&amp;brand=2">' . $recommendTitle . '  Product More</a>
    </div>
    <div class="related grid-5">';
        foreach ($recommendProductList as $item) {
            $newProductName = preg_replace('/[^a-zA-Z0-9 ]/', '', $item['name']);
            $newProductName = strtolower(str_replace(' ', '-', $newProductName));
            $productUrl     = 'products/' . $newProductName . '/';
            $mainImg        = isset($item['local']['main_img_path']) && !empty($item['local']['main_img_path']) ? $item['local']['main_img_path'] : $item['main_img'];

            $html .= '<div class="item">
        <a href="' . $productUrl . '" class="thumbnail" target="_blank">
          <img src="' . $mainImg . '"  alt="">
        </a>
        <div class="bottom">
          <div class="name">' . $item['name'] . '</div>
          <div class="price">$' . $item['price'] . '</div>
          <a href="" class="buy" target="_blank">View details</a>
        </div>
      </div>';
        }
        $html .= '</div>
  </div>';
        return $html;
    }

    public function getPaltformList($product)
    {
        $taooBuyUrl     = $product->tao_buy_link;
        $kakoBuyUrl     = 'https://kakobuy.com/item/details?url=' . $product->source_product_url;
        $mulebuyUrl     = 'https://mulebuy.com/product?id=' . $product->source_product_id . '&platform=WEIDIAN&searchInfo=' . $product->source_product_url;
        $joyagooUrl     = 'https://joyagoo.com/product?id=' . $product->source_product_id . '&platform=WEIDIAN';
        $oopbuyUrl      = 'https://oopbuy.com/product/weidian/' . $product->source_product_id;
        $hoobuyUrl      = 'https://hoobuy.com/product/2/' . $product->source_product_id;
        $cnfansUrl      = 'https://cnfans.com/product?id=' . $product->source_product_id . '&platform=WEIDIAN';
        $cssbuyUrl      = 'https://www.cssbuy.com/item-micro-' . $product->source_product_id . '.html';
        $acbuyUrl       = 'https://acbuy.com/product?id=' . $product->source_product_id . '&source=WD';
        $allchinabuyUrl = 'https://www.allchinabuy.com/en/page/buy/?nTag=Home-search&from=search-input&_search=url&position=&url=' . $product->source_product_url;
        $superbuyUrl    = 'https://www.superbuy.com/en/page/buy/?nTag=Home-search&from=search-input&url=' . $product->source_product_url;

        $html = '<div class="modal-overlay" id="modalOverlay">
            <div class="modal-content">
                <div class="modal-header">Please choose a  platform</div>
                <ul class="options-list">
                    <li class="option-item">
                        <a class="platform-info" target="_blank" href="' . $taooBuyUrl . '">
                            <span class="platform-letter">T</span>
                            <span>
                                <img src="/img/taoobuy.png" alt="TaooBuy" width="120" height="24">
                            </span>
                            <span class="platform-name">TaooBuy</span>
                            <span class="platform-badge">REC</span>
                        </a>
                        <a class="buy-btn" target="_blank" href="' . $taooBuyUrl . '">Buy Now</a>
                    </li>
                    <li class="option-item">
                        <a class="platform-info" target="_blank" href="' . $kakoBuyUrl . '">
                            <span class="platform-letter">K</span>
                            <span>
                                <img src="/img/kakobuy.png" alt="KakoBuy" width="120" height="24">
                            </span>
                            <span class="platform-name">KakoBuy</span>
                        </a>
                        <a class="buy-btn" target="_blank" href="' . $kakoBuyUrl . '">Buy Now</a>
                    </li>
                    <li class="option-item">
                        <a class="platform-info"  target="_blank" href="' . $mulebuyUrl . '">
                            <span class="platform-letter">M</span>
                            <span>
                                <img src="/img/mulebuy.png" alt="Mulebuy" width="120" height="24">
                            </span>
                            <span class="platform-name">Mulebuy</span>
                        </a>
                        <a class="buy-btn"  target="_blank" href="' . $mulebuyUrl . '">Buy Now</a>
                    </li>
                    <li class="option-item">
                        <a class="platform-info" target="_blank"  href="' . $joyagooUrl . '">
                            <span class="platform-letter">J</span>
                            <span>
                                <img src="/img/joyagoo.png" alt="JoyagooBuy" width="120" height="24">
                            </span>
                            <span class="platform-name">JoyagooBuy</span>
                        </a>
                        <a class="buy-btn" target="_blank" href="' . $joyagooUrl . '">Buy Now</a>
                    </li>
                    <li class="option-item">
                        <a class="platform-info"  target="_blank" href="' . $oopbuyUrl . '">
                            <span class="platform-letter">O</span>
                            <span>
                                <img src="/img/oopbuy.png" alt="oopBuy" width="120" height="24">
                            </span>
                            <span class="platform-name">oopBuy</span>
                        </a>
                        <a class="buy-btn" target="_blank" href="' . $oopbuyUrl . '">Buy Now</a>
                    </li>
                    <li class="option-item">
                        <a class="platform-info"  target="_blank" href="' . $hoobuyUrl . '">
                            <span class="platform-letter">H</span>
                            <span>
                                <img src="/img/hoobuy.png" alt="Hooby" width="120" height="24">
                            </span>
                            <span class="platform-name">Hooby</span>
                        </a>
                        <a class="buy-btn" target="_blank" href="' . $hoobuyUrl . '">Buy Now</a>
                    </li>
                    <li class="option-item">
                        <a class="platform-info"  target="_blank" href="' . $cnfansUrl . '">
                            <span class="platform-letter">C</span>
                            <span>
                                <img src="/img/cnfans.png" alt="CnfansBuy" width="120" height="24">
                            </span>
                            <span class="platform-name">CnfansBuy</span>
                        </a>
                        <a class="buy-btn"  target="_blank" href="' . $cnfansUrl . '">Buy Now</a>
                    </li>
                    <li class="option-item">
                        <a class="platform-info"  target="_blank" href="' . $cssbuyUrl . '">
                            <span class="platform-letter">C</span>
                            <span>
                                <img src="/img/css.png" alt="CssBuy" width="120" height="24">
                            </span>
                            <span class="platform-name">CssBuy</span>
                        </a>
                        <a class="buy-btn" target="_blank" href="' . $cssbuyUrl . '">Buy Now</a>
                    </li>
                    <li class="option-item">
                        <a class="platform-info"  target="_blank" href="' . $acbuyUrl . '">
                            <span class="platform-letter">A</span>
                            <span>
                                <img src="/img/acbuy.png" alt="AcBuy" width="120" height="24">
                            </span>
                            <span class="platform-name">AcBuy</span>
                        </a>
                        <a class="buy-btn" target="_blank" href="' . $acbuyUrl . '">Buy Now</a>
                    </li>
                    <li class="option-item">
                        <a class="platform-info"  target="_blank" href="' . $allchinabuyUrl . '">
                            <span class="platform-letter">A</span>
                            <span>
                                <img src="/img/allchinabuy.png" alt="AlChinaBuy" width="120" height="24">
                            </span>
                            <span class="platform-name">AlChinaBuy</span>
                        </a>
                        <a class="buy-btn"  target="_blank" href="' . $allchinabuyUrl . '">Buy Now</a>
                    </li>
                    <li class="option-item">
                        <a class="platform-info" target="_blank"  href="' . $superbuyUrl . '">
                            <span class="platform-letter">S</span>
                            <span>
                                <img src="/img/superbuy.png" alt="SuperBuy" width="120" height="24">
                            </span>
                            <span class="platform-name">SuperBuy</span>
                        </a>
                        <a class="buy-btn" target="_blank" href="' . $superbuyUrl . '">Buy Now</a>
                    </li>

                </ul>
                <div class="modal-footer">
                    <button class="close-btn">关闭</button>
                </div>
            </div>
        </div>';
        return $html;
    }

    public function productDownloadImage()
    {
        $productList = ProductMenu::query()->with('local')->where('site_id', $this->site_id)->get();
        $number      = 1;
        foreach ($productList as $product) {
            $this->info('number:' . $number);
            Log::info('number:' . $number);
            $newProductName = preg_replace('/[^a-zA-Z0-9 ]/', '', $product->name);
            $newProductName = strtolower(str_replace(' ', '-', $newProductName));
            $this->download($product->main_img, $newProductName, 'main.png');
            $mainPath = '/products/' . $newProductName . '/main.png';
            $range    = 1;
            //$imgList    = explode(',', $product->img);
            $imgPathArr = [];
            /*foreach ($imgList as $img) {
                $fileName = 'size-' . $range . '.png';
                $this->download($img, $newProductName, $fileName);
                $imgPathArr[] = '/products/' . $newProductName . '/' . $fileName;
                $range++;
            }*/
            $res = $this->productLocal($product->id, $mainPath, implode(',', $imgPathArr));
            $number++;
        }
    }

    public function productLocal($productId, $mainPath, $imgPath)
    {
        $productMenuLocal = ProductMenuLocal::query()->where('product_id', $productId)->first();
        if ($productMenuLocal && $productMenuLocal->id) {
            $productMenuLocal->main_img_path = $mainPath;
            $productMenuLocal->img_path      = $imgPath;
            $productMenuLocal->updated_at    = Carbon::now()->toDateTimeString();
            return $productMenuLocal->save();
        }

        $data = [
            'product_id'    => $productId,
            'main_img_path' => $mainPath,
            'img_path'      => $imgPath,
            'created_at'    => Carbon::now()->toDateTimeString(),
            'updated_at'    => Carbon::now()->toDateTimeString(),
        ];
        return ProductMenuLocal::query()->insert($data);
    }

    public function download($imageUrl, $savePath, $fileName)
    {
        try {
            $baseDir = public_path('baseNew/products');
// 获取图片内容
            $imageContent = file_get_contents($imageUrl);
            $filePath     = $baseDir . '/' . $savePath;
            if (!empty($filePath) && !file_exists($filePath)) {
                mkdir($filePath, 0777, true);
            }
            $newFilePath = $filePath . '/' . $fileName;
            if (!file_exists($newFilePath)) {
                touch($newFilePath);
            }
// 检查是否成功获取图片内容
            if ($imageContent === FALSE) {
                die('Error occurred while fetching the image.');
            }
// 将图片内容保存到本地文件
            $saveResult = file_put_contents($newFilePath, $imageContent);
            $this->info($newFilePath);
            Log::info($newFilePath);
// 检查是否成功保存文件
            if ($saveResult === FALSE) {
                return '';
            }
            return $newFilePath;
        } catch (\Exception $e) {
            $this->info($e->getMessage());
            Log::info($e->getMessage());
            return '';
        }

    }

    public function reloadHowTo()
    {
        $dir      = $this->websiteName;
        $howToDir = public_path($dir . '/howTo/index.html');
        $content  = file_get_contents($howToDir);
        $content  = str_replace('Kakobuy', $this->websiteName, $content);
        $content  = str_replace('replacePaltformUrl', $this->websiteUrl, $content);
        file_put_contents($howToDir, $content);
        Log::info('重新加载howTo完成');
        $this->info('重新加载howTo完成');
    }

    public function reloadJs()
    {
        $dir      = $this->websiteName;
        $howToDir = public_path($dir . '/js/site.js');
        $content  = file_get_contents($howToDir);
        $content  = str_replace('Kakobuy', $this->websiteName, $content);
        file_put_contents($howToDir, $content);
        Log::info('重新加载site.js完成');
        $this->info('重新加载site.js完成');
    }

    public function renameIco()
    {
        $dir     = $this->websiteName;
        $icoName = $this->websiteName . '.ico';
        $oldPath = public_path($dir . '/' . $icoName);
        // 检查原文件是否存在
        if (!file_exists($oldPath)) {
            throw new \RuntimeException("原文件不存在: {$oldPath}");
        }

        // 获取文件目录和扩展名
        $directory = dirname($oldPath);
        $extension = pathinfo($oldPath, PATHINFO_EXTENSION);

        // 构建新文件路径
        $newPath = $directory . '/favicon' . '.' . $extension;
        // 检查新文件名是否已存在
        if (file_exists($newPath)) {
            if (!unlink($newPath)) {
                throw new \RuntimeException("无法删除已存在的目标文件: {$newPath}");
            }
        }
        Log::info('重命名favicon完成');
        $this->info('重命名favicon完成');
        // 执行重命名
        return rename($oldPath, $newPath);

    }

    function deleteDirectory($dirPath) {
        $files = new \Illuminate\Filesystem\Filesystem(); // 使用 Laravel Filesystem 类

        if (!$files->exists($dirPath)) {
            $this->info('目录不存在: ' . $dirPath);
            return true;
        }

        // 清空目录内所有文件和子目录（保留目录本身）
        $files->cleanDirectory($dirPath);
        $this->info('成功删除目录下所有内容: ' . $dirPath);

        return true;
    }
}

