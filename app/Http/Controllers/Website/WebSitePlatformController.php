<?php

namespace App\Http\Controllers\Website;

use App\Events\GenerateWebSiteEvent;
use App\Http\Controllers\LPadmin\BaseController;
use App\Models\LPadmin\Website\HomeMenu;
use App\Models\LPadmin\Website\WebSiteConfig;
use App\Models\LPadmin\Website\WebsitePlatform;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebSitePlatformController extends BaseController
{
    /**
     * Display a listing of the resource.
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = WebsitePlatform::query()->with('siteConfig');
            // 搜索条件
            if ($request->filled('platform')) {
                $query->where('platform', 'like', '%' . $request->websiteName . '%');
            }
            if ($request->filled('platform_name')) {
                $query->where('platform_name', 'like', '%' . $request->websiteName . '%');
            }
            // 创建时间范围搜索
            if ($request->filled('created_at') && is_array($request->created_at)) {
                $dates = $request->created_at;
                if (!empty($dates[0])) {
                    $query->where('created_at', '>=', $dates[0] . ' 00:00:00');
                }
                if (!empty($dates[1])) {
                    $query->where('created_at', '<=', $dates[1] . ' 23:59:59');
                }
            }
            $field = $request->get('field', 'id');
            $order = $request->get('order', 'desc');
            $query->orderBy($field, $order);

            $page  = $request->get('page', 1);
            $limit = $request->get('limit', 15);

            $websiteList = $query->paginate($limit, ['*'], 'page', $page);
            $data        = [];

            foreach ($websiteList as $website) {
                $item   = [
                    'id'               => $website->id,
                    'site_id'          => $website->siteConfig->site_id,
                    'site_name'        => $this->cleanUtf8($website->siteConfig->name),
                    'platform'         => $this->cleanUtf8($website->platform),
                    'platform_name'    => $this->cleanUtf8($website->platform_name),
                    'platform_url'     => $this->cleanUtf8($website->platform_url),
                    'discord_url'      => $website->discord_url,
                    'meta_keyword'     => $website->meta_keyword,
                    'meta_description' => $website->meta_description,
                    'meta_title'       => $website->meta_title,
                    'status'           => $website->status,
                    'push_at'          => $website->push_at,
                    'created_at'       => $website->created_at,
                    'updated_at'       => $website->updated_at,
                ];
                $data[] = $item;
            }
            return response()->json([
                'code'  => 0,
                'msg'   => '',
                'count' => $websiteList->total(),
                'data'  => $data,
            ]);
        }
        return view('website.platform.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $siteConfigList = WebSiteConfig::query()->select(['id', 'name'])->get();
        return view('website.platform.create', compact('siteConfigList'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data   = $request->validate([
            'site_id'          => 'sometimes|nullable|integer',
            'platform'         => 'required|string',
            'platform_name'    => 'sometimes|nullable|string',
            'platform_url'     => 'sometimes|nullable|string',
            'discord_url'      => 'sometimes|nullable|string',
            'meta_keyword'     => 'sometimes|nullable|string',
            'meta_description' => 'sometimes|nullable|string',
            'meta_title'       => 'sometimes|nullable|string',
            'status'           => 'sometimes|string',
            'push_at'          => 'sometimes|string',
        ]);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $result = WebsitePlatform::create($data);
        return $this->success($result);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        $data = WebsitePlatform::query()->find($id);
        return $this->success($data);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Request $request, $id)
    {
        $data           = WebsitePlatform::query()->find($id);
        $siteConfigList = WebSiteConfig::query()->select(['id', 'name'])->get();
        return view('website.platform.edit', compact('data', 'siteConfigList'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $data     = $request->validate([
            'site_id'          => 'sometimes|nullable|integer',
            'platform'         => 'required|string',
            'platform_name'    => 'sometimes|nullable|string',
            'platform_url'     => 'sometimes|nullable|string',
            'discord_url'      => 'sometimes|nullable|string',
            'meta_keyword'     => 'sometimes|nullable|string',
            'meta_description' => 'sometimes|nullable|string',
            'meta_title'       => 'sometimes|nullable|string',
            'status'           => 'sometimes|string',
            'push_at'          => 'sometimes|string',

        ]);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $homeData = WebsitePlatform::query()->find($id);
        if ($homeData) {
            $result = WebsitePlatform::query()->where('id', $id)->update($data);
            return $this->success($result);
        }
        return $this->error('数据不存在');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return mixed
     */
    public function destroy(Request $request, $id)
    {
        $result = WebsitePlatform::query()->where('id', $id)->delete();
        return $this->success($result);
    }

    public function updatePlatform(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return $this->error('请选择要删除的商品');
        }
        WebsitePlatform::whereIn('id', $ids)->update(['status' => 'update_pending','updated_at'=>Carbon::now()->toDateTimeString()]);
        //Event::dispatch(new GenerateWebSiteEvent());
        return $this->success([]);
    }

    public function pushPlatform(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return $this->error('请选择要删除的商品');
        }
        $platformList = WebsitePlatform::query()->whereIn('id', $ids)->get();
        foreach ($platformList as $platform) {
            if($platform->status !== 'update_success'){
                Log::info('平台ID:'.$platform->platform_name.' 未更新成功');
                continue;
            }
            $this->pushCode($platform->platform_name);
            $platform->status = 'push_success';
            $platform->push_at = Carbon::now()->toDateTimeString();
            $platform->save();
        }
        return $this->success([]);
    }

    public function pushCode($platformName)
    {
        $gitDir = 'D:/phpstudy_pro/WWW/lpadmin/public/'.$platformName;

        $gitBranch= $platformName;
        // 定位到 Git 仓库的目录
        chdir($gitDir); // 修改为你的项目路径

        exec('git checkout '.$gitBranch);

        // 添加所有更改到暂存区
        exec('git add .');

        // 提交更改到本地仓库
        exec('git commit -m "Update platform code"');

        // 推送到远程仓库，例如 origin 的 $gitBranch 分支
        exec('git push origin '.$gitBranch);

        return 'Code pushed successfully.';
    }
}
