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

});
