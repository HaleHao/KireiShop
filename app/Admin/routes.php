<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
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
