<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\LPadmin\BaseController;
use App\Models\LPadmin\Admin;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebSiteController extends BaseController
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request):  View|JsonResponse
    {
        if ($request->expectsJson()) {


            return response()->json([
                'code' => 0,
                'msg' => '',
                'count' => 0,
                'data' => [],
            ]);
        }
        return view('website.home.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(): View
    {
        return view('website.home.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id): View
    {
        return view('website.home.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id): View
    {
        return view('website.home.edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function generate(Request $request)
    {

    }
}
