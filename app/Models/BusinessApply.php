<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BusinessApply extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'business_apply';
    const DEFAULT_STATUS = 1;
    const BY_STATUS = 2;
    const REFUSE_STATUS = 3;

    public static $typeLabels = [
        self::DEFAULT_STATUS => '审核中',
        self::BY_STATUS => '审核通过',
        self::REFUSE_STATUS => '审核拒绝',

    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'img',
        'phone',
        'name',
        'work',
        'address',
        'remark',
        'img2',
    ];

    /**类别
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category(){

        return $this->hasOne(BusinessCategory::class, 'category_id');
    }

}
