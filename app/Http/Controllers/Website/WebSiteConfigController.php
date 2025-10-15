<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\LPadmin\BaseController;
use App\Models\LPadmin\Website\WebSiteConfig;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class WebSiteConfigController extends BaseController
{
    /**
     * Display a listing of the resource.
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {
            $query = WebSiteConfig::query();
            // 搜索条件
            if ($request->filled('websiteName')) {
                $query->where('name', 'like', '%' . $request->websiteName . '%');
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
            // 格式化数据
            $data = [];
            foreach ($websiteList->items() as $website) {
                $item   = [
                    'id'               => $website->id,
                    'name'             => $this->cleanUtf8($website->name),
                    'domain'           => $this->cleanUtf8($website->domain),
                    'meta_keyword'     => $this->cleanUtf8($website->meta_keyword),
                    'meta_description' => $this->cleanUtf8($website->meta_description),
                    'title'            => $this->cleanUtf8($website->title),
                    'min_logo'         => $website->min_logo,
                    'apple_touch_logo' => $website->apple_touch_logo,
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
        return view('website.config.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('website.config.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data   = $request->validate([
            'name'             => 'required|string',
            'domain'           => 'sometimes|nullable|string',
            'min_logo'         => 'sometimes|nullable|string',
            'apple_touch_logo' => 'sometimes|nullable|string',
            'meta_keyword'     => 'sometimes|nullable|string',
            'meta_description' => 'sometimes|nullable|string',
            'title'            => 'sometimes|nullable|string',
        ]);
        $result = WebSiteConfig::create($data);
        return $this->success(null, '创建成功');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        $data = WebSiteConfig::query()->find($id);
        return $this->success($data);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $data = WebSiteConfig::query()->find($id);
        return view('website.config.edit', compact('data'));
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
            'name'             => 'required|string',
            'domain'           => 'sometimes|nullable|string',
            'min_logo'         => 'sometimes|nullable|string',
            'apple_touch_logo' => 'sometimes|nullable|string',
            'meta_keyword'     => 'sometimes|nullable|string',
            'meta_description' => 'sometimes|nullable|string',
            'title'            => 'sometimes|nullable|string',
        ]);
        $homeData = WebSiteConfig::query()->find($id);
        if ($homeData) {
            $result = WebSiteConfig::query()->where('id', $id)->update($data);
            return $this->success(null, '更新成功');
        }
        return $this->error('更新失败: ');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return mixed
     */
    public function destroy(Request $request, $id)
    {
        WebSiteConfig::query()->where('id', $id)->delete();
        return $this->success(null, '删除成功');
    }

    public function webSiteList(Request $request)
    {
        $siteConfigList = WebSiteConfig::query()->select(['id', 'name'])->get();
        return $this->success($siteConfigList);
    }
}
