<?php

namespace App\Admin\Controllers;

use App\Http\Models\AuthMember;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;


use App\Http\Models\Member ;
use Encore\Admin\Grid\Displayers\Actions;
use Illuminate\Support\Facades\Input;
use Encore\Admin\Form\Builder;

use Encore\Admin\Grid\Filter;

class AuthMemberController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('用户认证');
            $content->description('列表');

            $content->body($this->grid());
        });
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid( AuthMember::class, function (Grid $grid) {
			$grid->model()->orderBy('id' , 'desc');
            $grid->id('ID')->sortable();
			$grid->real_name('真实姓名');
			$grid->id_card('身份证号码');
			$grid->status('认证状态')->display( function( $v ){
				return data_get( config('global.auth_member_status') , $v , '' );
			} )->label();
            $grid->create_time('提交时间')->display( function( $v ){
            	return $v ? date('Y-m-d H:i:s' , $v ) : '' ;
            });
            //$grid->updated_at();
            $grid->disableRowSelector();
            $grid->disableExport();
//            $grid->disableCreateButton();
            $grid->actions( function( Actions $action ){ 
            	$action->disableDelete();
            	$action->disableEdit();
//            	$url = route('admin.authuser.edit' , ['id' => $action->row->id ] );
//            	$action->append("<a href='{$url}'><i class='fa fa-globe'></i></a>");
            });
            
            $grid->filter( function( Filter $filter ){
            	$filter->disableIdFilter();
            	$filter->like('real_name' , '真实姓名');
            	$filter->like('id_card' , '身份证号码');
            	$filter->equal('status' , '认证状态')->select( config('global.auth_member_status') );
            });
            
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('用户认证');
            $content->description('编辑');
            $form = $this->form();

            $info = AuthMember::find( $id ) ;

            if( $info->status === 0 ) {
                $form->radio('status' , '审核状态')->options( config('global.auth_member_status')  );
                $form->text('reason' , '审核理由')->rules('required_if:status,2' , [
                    'required' => '审核理由必填写'
                ] );

            } else {
                $form->display('status' , '审核状态')->with(  function( $v ){
                    return $v ? data_get( config('global.auth_member_status' ) , $v ) : '' ;
                });
                $form->display('reason' , '审核理由');
                $form->disableSubmit();
            }
            $content->body( $form->edit( $id ) );
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('用户认证');
            $content->description('列表');

            $content->body($this->form());
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form( AuthMember::class, function (Form $form) {

            $form->display('id', 'ID');
			$form->display('real_name' , '姓名');
			$form->display('id_no' , '身份证号');
			$form->display('id_front_pic' , '身份证正面图')->with( function( $v ){
				$src = config('global.file_domain') . $v ;
				$html = '<a href="'. $src .'" target="_blank" >
						<img src="' . $src . '" width="100%" height="" />
					</a>';
				return $html ;
			} );
			$form->display('id_back_pic' , '身份证背面图')->with( function( $v ){
				$src = config('global.file_domain') . $v ;
				$html = '<a href="'. $src .'" target="_blank" >
					<img src="' . $src . '" width="100%" height="" />
				</a>';
				return $html ;
			} );
			$form->display('id_hand_pic' , '手持身份证图')->with( function( $v ){
				$src = config('global.file_domain') . $v ;
				$html = '<a href="'. $src .'" target="_blank" >
				<img src="' . $src . '" width="100%" height="" />
			</a>';
				return $html ;
			} );
			$form->display('member.phone_mob' , '手机号码');
			$form->display('create_time' , '申请时间')->with( function( $v ){
				return $v ? date('Y-m-d H:i:s' , $v ) : '' ;
			});
			
			
			$form->saving( function( Form $form ){
				$data = request()->all();
				if( data_get( $data , 'status' ) == 1 ) {
					//审核通过
					return $this->_allow( $form->model() , $data );
				} elseif( data_get( $data , 'status' ) == 2 ) {
					//审核不通过
					return $this->_notAllow( $form->model() , $data );
				}
				admin_toastr(trans('无状态改变'));
				$url = Input::get(Builder::PREVIOUS_URL_KEY) ?: $form->resource(0);
				return redirect($url);
			});
        });
    }
    
    protected function _allow( $model , $data ) {
    		//检查身份证号码是不是重复了
    		$count = AuthUser::where('id_no' , $model->id_no )->where('status' , 1 )->count();
    		if( $count ) {
    			admin_toastr( '当前身份证已经认证了' , 'error' );
    			return back()->withInput();
    		}
    		$model->status = 1 ;
    		$model->reason = data_get( $data , 'reason');
    		$model->auth_time = time();
    		if( $model->save() ) {
    			$member = Member::findOrFail( $model->uid );
    			$member->auth_status = 1 ;
    			$member->save();
    			//发送论证消息
    			\DB::table('vcm_message')->insert([
    					'uid' => $model->uid ,
    					'usertype' => 'member' ,
    					'event' => 'auth.allow' ,
    					'name' => '实名认证审核通过' ,
    					'content' => '实名认证审核通过' ,
    					'status' => 0 ,
    					'created_at' => date('Y-m-d H:i:s') ,
    					'updated_at' => date('Y_m-d H:i:s')
    
    			]);
    			//如果是通过了则要送积分
    			$isOpenScore = Option::getByName( 'is_score' );
    			if( $isOpenScore ) {
    				$score = Option::getByName( 'member_auth' );
    				//赠送积分
    				\DB::table('vcm_score_log')->insert([
    						'uid' => $model->uid ,
    						'user_type' => 'member' ,
    						'event' => 'auth' ,
    						'obj_id' => $model->id ,
    						'score' => $score ,
    						'create_time' => time() ,
    						'created_at' => date('Y-m-d H:i:s') ,
    						'updated_at' => date('Y_m-d H:i:s')
    							
    				]);
    				\DB::table('vcm_member')->where('uid' , $model->uid )->update([
    						'score' => \DB::raw("score+" . $score )
    				]);
    					
    			}
    			//TODO 
    			//event(new \App\Events\userActionEvent( AuthUser::class , $model->id, 'auth.pass', '将用户' . $model->real_name .'的认证设置为通过' ));
    			admin_toastr(trans('审核认证通过信息修改完成'));
				$url = Input::get(Builder::PREVIOUS_URL_KEY) ?: $this->form()->resource(0);
				return redirect($url);
    		}
    		admin_toastr(trans('审核认证通过信息修改失败'));
    		return back()->withInput();
    }
    
    protected function _notAllow( $model , $data ) {
    	$reason = request()->input('reason' , '') ;
    	if( !$reason ) {
    		admin_toastr(trans('请填写审核不通过的理由') , 'error');
    		return back()->withInput();
    	}
    	$reason = date ( 'Y-m-d H:i' ) . '审核不通过,原因是' . $reason;
		$model->reason = $reason;
		$model->status = 2;
		$model->auth_time = time ();
		if ($model->save ()) {
			
			// 发送论证消息
			\DB::table ( 'vcm_message' )->insert ( [ 
					'uid' => $model->uid,
					'usertype' => 'member',
					'event' => 'auth.disallow',
					'name' => '实名认证审核不通过',
					'content' => '实名认证审核不通过',
					'status' => 0,
					'created_at' => date ( 'Y-m-d H:i:s' ),
					'updated_at' => date ( 'Y_m-d H:i:s' ) 
			] );
			//TODO 
			//event ( new \App\Events\userActionEvent ( AuthUser::class , $model->id, 'auth.notpass', '将用户' . $model->real_name . '的认证设置为不通过' ) );
			admin_toastr(trans('审核不通过信息修改完成'));
    		$url = Input::get(Builder::PREVIOUS_URL_KEY) ?: $this->form()->resource(0);
    		return redirect($url);
		}
		admin_toastr(trans('审核不通过修改信息失败'));
    	return back()->withInput();
	}
}
