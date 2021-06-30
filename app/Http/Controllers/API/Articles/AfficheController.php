<?php

namespace App\Http\Controllers\API\Articles;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Http\Resources\AfficheResource;
use App\Models\Affiche;
use App\Services\Traits\MyPage;
use Exception;
use Illuminate\Http\Request;

class AfficheController extends Controller
{
    
    use MyPage;
    
    /**
     * Description:公告列表
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/6/30 0030
     */
    public function getList(Request $request)
    {
        $page = $request->input('page');
        $page_size = $request->input('page_size');
        try {
            $Affiche = new Affiche();
            $list = $Affiche->where('is_del', '=', '0')->orderBy('id', 'desc');
            $list = $this->page($list, $page, $page_size);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess(AfficheResource::collection($list));
    }
    
    /**
     * Description:公告详情
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/6/30 0030
     */
    public function getDetails(Request $request)
    {
        $id = $request->input('id');
        try {
            $info = Affiche::find($id);
            if (empty($info)) {
                throw new Exception('公告不存在或已删除');
            }
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($info);
    }
}
