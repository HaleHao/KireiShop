<?php
/**
 * 会员信息表
 */
namespace App\Http\Models ;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class GoodsCategory extends Model {
    use ModelTree, AdminBuilder;

    protected $table = 'shop_goods_category';
    protected $primaryKey = 'id' ;
    protected $perPage = 15 ;
    protected $guarded = [];

    //一个品牌对应多个商品
//    public function goods() {
//        return $this->hasMany(Goods::class , 'bid' , 'bid');
//    }
    public function __construct(array $attributes = []) {
        $this->setOrderColumn('sort' );
        $this->setTitleColumn('title' );
        $this->setParentColumn( 'category_id') ;

        parent::__construct( $attributes ) ;
    }
}