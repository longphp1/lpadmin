<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\LPadmin\BaseController;
use App\Models\LPadmin\Website\MenuConfig;
use App\Models\LPadmin\Website\WebSiteConfig;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class WebSiteMenuController extends BaseController
{
    /**
     * Display a listing of the resource.
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {
            $query = MenuConfig::with(['siteConfig:id,name']);
            if ($request->filled('menu_name')) {
                $query->where('menu_name', 'like', '%' . $request->menu_name . '%');
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

            $menuList = $query->paginate($limit, ['*'], 'page', $page);
            // 格式化数据
            $data = [];
            foreach ($menuList->items() as $menu) {
                $item   = [
                    'id'               => $menu->id,
                    'site_name'        => $this->cleanUtf8($menu->siteConfig->name),
                    'menu_name'        => $this->cleanUtf8($menu->menu_name),
                    'menu_name_en'     => $this->cleanUtf8($menu->menu_name_en),
                    'min_logo'         => $menu->menu_url,
                    'range'            => $menu->range,
                    'type'             => $menu->type,
                    'status'           => $menu->status,
                    'meta_keyword'     => $menu->meta_keyword,
                    'meta_description' => $menu->meta_description,
                    'meta_title'       => $menu->meta_title,
                    'apple_touch_logo' => $menu->apple_touch_logo,
                    'created_at'       => $menu->created_at,
                    'updated_at'       => $menu->updated_at,
                ];
                $data[] = $item;
            }
            return response()->json([
                'code'  => 0,
                'msg'   => '',
                'count' => $menuList->total(),
                'data'  => $data,
            ]);
        }
        return view('website.menu.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $siteConfigList = WebSiteConfig::query()->select(['id', 'name'])->get();
        $menuType       = WebSiteConfig::$menuType;
        $statusList     = WebSiteConfig::$statusList;
        return view('website.menu.create', compact('siteConfigList', 'menuType', 'statusList'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data   = $request->validate([
            'site_id'          => 'required|integer',
            'menu_name'        => 'required|string',
            'menu_name_en'     => 'required|string',
            'menu_url'         => 'sometimes|nullable|string',
            'range'            => 'sometimes|nullable|integer',
            'type'             => 'sometimes|nullable|integer',
            'status'           => 'sometimes|nullable|integer',
            'meta_keyword'     => 'sometimes|nullable|string',
            'meta_description' => 'sometimes|nullable|string',
            'meta_title'       => 'sometimes|nullable|string',
        ]);
        $result = MenuConfig::create($data);
        return $this->success($result);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $data = MenuConfig::query()->find($id);
        return $this->success($data);
    }

    /**
     * Show the form for editing the specified resource.
     * @param Request $request
     * @return Renderable
     */
    public function edit(Request $request, $id)
    {
        $data           = MenuConfig::query()->find($id);
        $siteConfigList = WebSiteConfig::query()->select(['id', 'name'])->get();
        $menuType       = WebSiteConfig::$menuType;
        $statusList     = WebSiteConfig::$statusList;
        return view('website.menu.edit', compact('data', 'siteConfigList', 'menuType', 'statusList'));
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
            'site_id'          => 'required|integer',
            'menu_name'        => 'required|string',
            'menu_name_en'     => 'required|string',
            'menu_url'         => 'sometimes|nullable|string',
            'range'            => 'sometimes|nullable|integer',
            'type'             => 'sometimes|nullable|integer',
            'status'           => 'sometimes|nullable|integer',
            'meta_keyword'     => 'sometimes|nullable|string',
            'meta_description' => 'sometimes|nullable|string',
            'meta_title'       => 'sometimes|nullable|string',
        ]);
        $homeData = MenuConfig::query()->find($id);
        if ($homeData) {
            $result = MenuConfig::query()->where('id', $id)->update($data);
            return $this->success($result);
        }
        return $this->error('更新失败: 菜单不存在');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return mixed
     */
    public function destroy(Request $request, $id)
    {
        $result = MenuConfig::where('id', $id)->delete();
        return $this->success($result);
    }
}
