<?php

namespace App\Services\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait MyPage
{
    
    /**
     * Description: Api接口分页
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $model
     * @param  int                                    $page
     * @param  int                                    $pageSize
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @author lidong<947714443@qq.com>
     * @date   2021/6/30 0030
     */
    public function page(Builder $model, $page = 1, $pageSize = 10)
    {
        if (intval($page) <= 1) {
            $page = 1;
        }
        if (intval($pageSize) <= 1) {
            $pageSize = 10;
        }
        $skip = (intval($page) - 1) * intval($pageSize);
        return $model->skip($skip)->take($pageSize)->get();
    }
}
