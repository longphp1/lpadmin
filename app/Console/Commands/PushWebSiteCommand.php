<?php

namespace App\Console\Commands;


use App\Models\LPadmin\Website\WebsitePlatform;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;


class PushWebSiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     * 执行命令 php artisan push:website
     *
     * @var string
     */
    protected $signature = 'push:website';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '推送网站到远程仓库';

    public $fileName = '';
    public $site_id = 1;
    public $siteConfig = [];

    public $initProductImage = false;

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
        $platformList = WebsitePlatform::where('status', 'update_success')->get();
        foreach ($platformList as $platform) {

            Log::info('推送网站:'.$platform->platform_name);
            try {
                if($platform->status !== 'update_success'){
                    Log::info('平台ID:'.$platform->platform_name.' 未更新成功');
                    //continue;
                }
                $this->pushCode($platform->platform_name);
                $platform->status = 'push_success';
                $platform->push_at = \Carbon\Carbon::now()->toDateTimeString();
                $platform->save();
                Log::info('平台ID:'.$platform->platform_name.' 推送成功');
            } catch (\Exception $e) {
                $this->error('平台ID:'.$platform->platform_name.' 推送失败 error:'.$e->getMessage());
                Log::error('平台ID:'.$platform->platform_name.' 推送失败 error:'.$e->getMessage());
            }
        }

        return 0;
    }




    public function pushCode($platformName)
    {
        //ini_set('max_execution_time', '1200');

        $sourceDir = 'D:/phpstudy_pro/WWW/lpadmin/public/'.$platformName;

        if(!file_exists($sourceDir)){
            Log::info('平台目录不存在:'.$sourceDir);
            return false;
        }

        $destinationDir='D:\phpstudy_pro\WWW\taoobuy';

        $gitBranch= strtolower($platformName);
        $this->info('推送分支:'.$platformName);
        // 定位到 Git 仓库的目录
        chdir($destinationDir); // 修改为你的项目路径

        //$gitBranch='test';

        exec('git reset --hard HEAD');
        Log::info('重置本地仓库到最新提交');
        $this->info('重置本地仓库到最新提交');
        exec('git clean -fd');
        Log::info('清除本地未跟踪文件');
        $this->info('清除本地未跟踪文件');
        exec('git checkout '.$gitBranch);
        Log::info('切换到分支:'.$gitBranch);
        $this->info('切换到分支:'.$gitBranch);
        exec('git pull origin '.$gitBranch);
        Log::info('拉取远程仓库最新代码');
        $this->info('拉取远程仓库最新代码');
        $this->copyFile($sourceDir,$destinationDir);

        // 添加所有更改到暂存区
        exec('git add .');
        Log::info('添加所有更改到暂存区');
        $this->info('添加所有更改到暂存区');
        $commitText='System auto update platform code time:'.date('Y-m-d H:i:s');
        // 提交更改到本地仓库
        exec('git commit -m "'.$commitText.'"');
        Log::info('提交更改到本地仓库:'.$commitText);
        $this->info('提交更改到本地仓库:'.$commitText);
        // 推送到远程仓库，例如 origin 的 $gitBranch 分支
        exec('git push origin '.$gitBranch);
        Log::info('推送到远程仓库:'.$gitBranch);
        $this->info('推送到远程仓库:'.$gitBranch);
        return 'Code pushed successfully.';
    }

    public function copyFile($sourcePath, $destinationPath) {
        $files = new Filesystem();
        // 确保源文件夹存在
        if (!$files->isDirectory($sourcePath)) {
            throw new \InvalidArgumentException("源文件夹不存在: {$sourcePath}");
        }

        // 创建目标文件夹（如果不存在）
        if (!$files->exists($destinationPath)) {
            $files->makeDirectory($destinationPath, 0755, true);
        }

        // 获取源文件夹中的所有文件和子文件夹
        $items = $files->allFiles($sourcePath);

        foreach ($items as $item) {
            // 构建目标文件路径
            $target = str_replace($sourcePath, $destinationPath, $item->getPathname());
            // 确保目标文件所在目录存在（关键修复：创建父目录）
            $targetDir = dirname($target);
            $files->ensureDirectoryExists($targetDir, 0755, true);
            // 如果是文件夹，先创建目标文件夹
            if ($item->isDir()) {
                if (!$files->exists($target)) {
                    $files->makeDirectory($target, 0755, true);
                }
            } else {
                // 如果是文件，直接复制（会覆盖已存在的文件）
                $files->copy($item->getPathname(), $target);
                Log::info('文件复制成功:'.$target);
                $this->info('文件复制成功:'.$target);
            }
        }
        Log::info('文件复制完成');
        $this->info('文件复制完成');
        return true; // 复制成功
    }

}

