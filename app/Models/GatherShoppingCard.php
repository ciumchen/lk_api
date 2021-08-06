<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatherShoppingCard extends Model
{
    use HasFactory;

    protected $table = 'gather_shopping_card';

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
