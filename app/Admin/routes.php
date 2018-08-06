<?php
//这里是后台的路由
use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    //前缀
    'prefix'        => config('admin.route.prefix'),
    //命名空间
    'namespace'     => config('admin.route.namespace'),
    //中间键
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->resource('member' , 'MemberController' , [
			'names' => [
					'show' => 'admin.member.show' ,
			] ,
	] );

    $router->resource('authmember' , 'AuthMemberController' , [
        'names' => [
            'edit' => 'admin.authmember.edit'
        ] ,
    ] );

    $router->resource('goods' , 'GoodsController');
    //品牌管里
    $router->resource('brand' , 'BrandController');
    //商城商品分类管理
    $router->resource('category' , 'CategoryController');
});
