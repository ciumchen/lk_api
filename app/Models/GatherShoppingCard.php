<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Exceptions\LogicException;

class GatherShoppingCard extends Model
{
    use HasFactory;

    protected $table = 'gather_shopping_card';

    protected $fillable = [
        'gid',
        'uid',
        'guid',
        'money',
        'status',
        'created_at',
        'updated_at',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**新增拼团
     * @param array $data
     * @return mixed
     * @throws LogicException
     */
    public function setGatherShoppingCard (array $data)
    {
        return GatherShoppingCard::insert($data);
    }


}
