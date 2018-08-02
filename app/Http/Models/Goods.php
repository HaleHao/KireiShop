<?php
/**
 * 会员信息表
 */
namespace App\Http\Models ;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model {
	protected $table = 'shop_goods';
	protected $primaryKey = 'gid' ;
    protected $perPage = 15 ;
    protected $guarded = [];

    //一个商品对应多张图片
    public function images() {
        return $this->hasMany(GoodsImage::class , 'gid' , 'gid');
    }

    //一个商品对应一个品牌
    public function brand() {
        return $this->belongsTo( GoodsBrand::class , 'bid' , 'bid' );
    }
}