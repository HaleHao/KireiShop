<?php
/**
 * 会员信息表
 */
namespace App\Http\Models ;

use Illuminate\Database\Eloquent\Model;

class GoodsImage extends Model {
	protected $table = 'shop_goods_image';
    protected $fillable = [
        'id' , 'gid' , 'name' , 'image_path'
    ] ;

}