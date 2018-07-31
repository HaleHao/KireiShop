<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use App\Http\Models\Member ;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Grid\Filter;

class MemberController extends Controller
{
    use ModelForm ;

    //会员列表
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('会员管理');
            $content->description('用户注册列表');
            $content->row($this->grid());

        });
    }

    protected function grid()
    {
       return Admin::grid( Member::class, function (Grid $grid) {
            $grid->model()->orderBy('uid' , 'desc' );
            $grid->uid('ID')->sortable();
            $grid->real_name('真实姓名');
            $grid->user_name('用户名');
            $grid->phone_mob('手机号码');
            $grid->age('年龄');
            $grid->gender('性别')->display( function( $v ){
                return trim( $v ) != '' ? data_get( config('global.gender' ) , $v , '' ) : '未知';
            });
            $grid->auth_status('认证状态')->display(function ($v) {
                return data_get(config('global.auth_status'), $v);
            })->label();
            $grid->create_time('注册时间')->display(function($v) {
                return $v ? date('Y-m-d' , $v ) : '' ;
            });
           $grid->disableCreateButton();
           $grid->disableRowSelector();
           $grid->disableExport();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                // $actions->disableEdit();
                $url = route('admin.member.show' , ['id' => $actions->row->uid ] );;
                $actions->append("<a href='{$url}'><i class='fa fa-eye'></i></a>");
            });
           $grid->filter( function( Filter $filter ){
               $filter->equal('phone_mob' , '手机号码');
               $filter->like('real_name' , '真实姓名');
               $filter->equal('auth_status' , '审核状态')->select( config('global.auth_status') );
           });
        });
    }


    public function show( $id ) {
        return Admin::content(function (Content $content) use ($id) {
        
            $content->header('会员管理');
            $content->description('查看');
            $content->body($this->_show()->view($id));
        });
    }
    
    protected function _show() {
        return Admin::form( Member::class, function (Form $form) {
            $form->display('phone_mob' , '手机号码');
            $form->display('real_name' , "真实姓名");
            $form->display('gender' , '性别')->with( function( $v ){
                return data_get( config('global.gender') , $v ) ;
            } );
            $form->display('age','年龄');
            $form->display('avatar' , '用户头像')->with( function( $v ){

                $html = '<a href="'.  asset('uploads/' .$v) .'" target="_blank" >
                        <img src="' . asset('uploads/'.$v) . '" width="150" height="" />
                    </a>';
                return $html ;
            } );
            $form->display('email' , 'Email');
            $form->display('auth_status' , '认证状态')->with( function( $v ){
                return data_get( config('global.auth_status' ) , $v ) ;
            }) ;
            $form->display('address' , '居住地址');
            $form->display('brithday' , '出生日期')->with( function( $v ){
                return $v ? date('Y-m-d' , $v ) : '' ;
            });

        });
    }

    public function edit( $id ) {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('会员管理');
            $content->description('编辑');

            $content->body($this->form()->edit($id));
        });
    }

    protected function form() {
        return Admin::form( Member::class, function (Form $form) {
            $form->text('phone_mob' , '手机号码')->rules('required' , [
                'required' => '请填写手机号码'
            ]);
            $form->password('password' , '用户密码');
            $form->text('real_name' , "真实姓名");
            $form->image('avatar' , '用户头像');
            $form->display('auth_status' , '认证状态')->with( function( $v ){
                return data_get( config('global.auth_status' ) , $v ) ;
            }) ;
        });
    }
}
