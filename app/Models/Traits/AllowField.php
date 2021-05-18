<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait AllowField
{

    /**
     * 过滤数据表不存在字段
     * @param $data
     * @param Model $Model
     * @return mixed
     */
    public function allowFiled($data, Model $Model)
    {
        $fields = Schema::getColumnListing($Model->getTable());
//        dump($Model->getTable());
//        dump('$fields');
//        dump($fields);
        $columns = array_keys($data);
//        dump('$columns');
//        dump($columns);
        $table_columns = array_intersect($columns, $fields);
        foreach ($data as $key => $row) {
            if (!in_array($key, $table_columns)) {
                unset($data[ $key ]);
            }
        }
        return $data;
    }


}
