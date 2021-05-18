<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetsLogsResources;
use App\Models\AssetsLogs;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class AssetsController extends Controller
{
    /**
     * 获取资产记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssetsLogs(Request $request)
    {
        $pageSize = $request->input('pageSize',10);
        $assets_name = $request->input('assets_name');

        $data = (new AssetsLogs())
            ->where("uid", $request->user()->id)
            ->where("assets_name", $assets_name)
            ->orderBy('id', 'desc')
            ->latest('id')
            ->forPage(Paginator::resolveCurrentPage('page'), $pageSize)
            ->get();

        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => AssetsLogsResources::collection($data)]);
    }
}
