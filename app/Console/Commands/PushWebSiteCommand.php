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
                Log::info('平台:'.$platform->platform_name.' 推送成功');
                $this->info('平台:'.$platform->platform_name.' 推送成功');
                sleep(5);
                $this->info('等待5秒后继续推送下一个平台');
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

        $sourceBaseDir = config('lpadmin.push.source_base_dir');
        $destinationDir = config('lpadmin.push.destination_dir');

        $sourceDir = rtrim($sourceBaseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $platformName;

        if(!file_exists($sourceDir)){
            Log::info('平台目录不存在:'.$sourceDir);
            return false;
        }

        if(!file_exists($destinationDir)){
            throw new \RuntimeException('目标Git仓库目录不存在: '.$destinationDir);
        }

        $gitBranch= strtolower($platformName);
        $this->info('推送分支:'.$platformName);

        chdir($destinationDir);

        $this->execGitCommand('git reset --hard HEAD', '重置本地仓库到最新提交');
        $this->execGitCommand('git clean -fd', '清除本地未跟踪文件');
        $this->execGitCommand('git checkout '.$gitBranch, '切换到分支:'.$gitBranch);
        $this->execGitCommand('git clean -fd', '清除本地未跟踪文件');
        $this->execGitCommand('git pull origin '.$gitBranch, '拉取远程仓库最新代码');

        $this->deleteDirectory($destinationDir);
        $this->info('删除原始数据成功');

        $this->execGitCommand('git add .', '添加所有更改到暂存区');

        $commitText='System auto delete platform code time:'.date('Y-m-d H:i:s');
        $this->execGitCommand('git commit -m "'.$commitText.'"', '提交更改到本地仓库');
        $this->execGitCommand('git push origin '.$gitBranch, '推送到远程仓库');

        $this->info('复制最新的数据到目标目录');
        $this->copyFile($sourceDir, $destinationDir);

        $this->execGitCommand('git add .', '添加所有更改到暂存区');

        $commitText='System auto update platform code time:'.date('Y-m-d H:i:s');
        $this->execGitCommand('git commit -m "'.$commitText.'"', '提交更改到本地仓库');
        $this->execGitCommand('git push origin '.$gitBranch, '推送到远程仓库');

        return 'Code pushed successfully.';
    }

    protected function execGitCommand(string $command, string $description): string
    {
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);

        $outputStr = implode("\n", $output);

        if ($returnCode !== 0) {
            $errorMsg = "执行失败 [{$description}]: command={$command}, output={$outputStr}";
            Log::error($errorMsg);
            $this->error($errorMsg);
            throw new \RuntimeException($errorMsg);
        }

        Log::info($description . ' => ' . $command);
        $this->info($description);
        Log::info('输出: ' . $outputStr);

        return $outputStr;
    }

    public function copyFile($sourcePath, $destinationPath) {
        $files = new Filesystem();
        if (!$files->isDirectory($sourcePath)) {
            throw new \InvalidArgumentException("源文件夹不存在: {$sourcePath}");
        }

        if (!$files->exists($destinationPath)) {
            $files->makeDirectory($destinationPath, 0755, true);
        }

        $items = $files->allFiles($sourcePath);

        foreach ($items as $item) {
            $target = str_replace($sourcePath, $destinationPath, $item->getPathname());
            $targetDir = dirname($target);
            $files->ensureDirectoryExists($targetDir, 0755, true);
            if ($item->isDir()) {
                if (!$files->exists($target)) {
                    $files->makeDirectory($target, 0755, true);
                }
            } else {
                $files->copy($item->getPathname(), $target);
                Log::info('文件复制成功:'.$target);
                $this->info('文件复制成功:'.$target);
            }
        }
        Log::info('文件复制完成');
        $this->info('文件复制完成');
        return true;
    }

    function deleteDirectory($dirPath) {
        $files = new \Illuminate\Filesystem\Filesystem();

        if (!$files->exists($dirPath)) {
            return true;
        }

        foreach ($files->directories($dirPath) as $subDir) {
            $subDirName = basename($subDir);
            if ($subDirName === '.git') {
                continue;
            }
            $this->info('删除目录: ' . $subDir);
            $files->deleteDirectory($subDir);
        }

        foreach ($files->files($dirPath) as $file) {
            $this->info('删除文件: ' . $file->getPathname());
            $files->delete($file->getPathname());
        }

        return true;
    }

}
