<?php
/**
 * 会员信息表
 */
namespace App\Http\Models ;

use Illuminate\Database\Eloquent\Model;

class GoodsBrand extends Model {
    protected $table = 'shop_goods_brand';
    protected $primaryKey = 'bid' ;
    protected $perPage = 15 ;
    protected $guarded = [];

    //一个品牌对应多个商品
    public function goods() {
        return $this->hasMany(Goods::class , 'bid' , 'bid');
    }
}