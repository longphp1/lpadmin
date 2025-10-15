<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\LPadmin\BaseController;
use App\Models\LPadmin\Website\HomeMenu;
use App\Models\LPadmin\Website\WebSiteConfig;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class WebSiteHomeController extends BaseController
{
    /**
     * Display a listing of the resource.
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = HomeMenu::query()->with('siteConfig');
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
            $data        = [];

            foreach ($websiteList as $website) {
                $item   = [
                    'id'              => $website->id,
                    'name'            => $this->cleanUtf8($website->siteConfig->name),
                    'top_head'        => $this->cleanUtf8($website->top_head),
                    'large_head'      => $this->cleanUtf8($website->large_head),
                    'top_head_bottom' => $this->cleanUtf8($website->top_head_bottom),
                    'header_content'  => $this->cleanUtf8($website->header_content),
                    'middle_qr_code'  => $website->middle_qr_code,
                    'middle_qr_title' => $website->middle_qr_title,
                    'middle_logo'     => $website->middle_logo,
                    'middle_content'  => $website->middle_content,
                    'content'         => $website->content,
                    'created_at'      => $website->created_at,
                    'updated_at'      => $website->updated_at,
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
        return view('website.home.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $siteConfigList = WebSiteConfig::query()->select(['id', 'name'])->get();
        return view('website.home.create', compact('siteConfigList'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data   = $request->validate([
            'top_head'        => 'sometimes|nullable|string',
            'large_head'      => 'required|string',
            'top_head_bottom' => 'sometimes|nullable|string',
            'header_content'  => 'sometimes|nullable|string',
            'middle_qr_code'  => 'sometimes|nullable|string',
            'middle_qr_title' => 'sometimes|nullable|string',
            'middle_logo'     => 'sometimes|nullable|string',
            'middle_content'  => 'sometimes|nullable|string',
            'content'         => 'sometimes|string',
        ]);
        $result = HomeMenu::create($data);
        return $this->success($result);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        $data = HomeMenu::query()->find($id);
        return $this->success($data);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Request $request, $id)
    {
        $data           = HomeMenu::query()->find($id);
        $siteConfigList = WebSiteConfig::query()->select(['id', 'name'])->get();
        return view('website.home.edit', compact('data', 'siteConfigList'));
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
            'site_id'         => 'required|integer',
            'top_head'        => 'sometimes|nullable|string',
            'large_head'      => 'required|string',
            'top_head_bottom' => 'sometimes|nullable|string',
            'header_content'  => 'sometimes|nullable|string',
            'middle_qr_code'  => 'sometimes|nullable|string',
            'middle_qr_title' => 'sometimes|nullable|string',
            'middle_logo'     => 'sometimes|nullable|string',
            'middle_content'  => 'sometimes|nullable|string',
            'content'         => 'sometimes|string',

        ]);
        $homeData = HomeMenu::query()->find($id);
        if ($homeData) {
            $result = HomeMenu::query()->where('id', $id)->update($data);
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
        $result = HomeMenu::query()->where('id', $id)->delete();
        return $this->success($result);
    }
}
