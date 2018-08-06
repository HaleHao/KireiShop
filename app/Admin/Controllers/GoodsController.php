<?php

namespace App\Admin\Controllers;

use App\Http\Models\GoodsBrand;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Http\Models\Goods ;
use Encore\Admin\Grid\Filter;


class GoodsController extends Controller
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

            $content->header('商品管理');
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
        return Admin::grid(Goods::class, function (Grid $grid) {
            $grid->model()->with('brand')->orderBy('gid', 'desc');
            $grid->gid('ID')->sortable();
            $grid->title('商品名称')->limit(20);
            $grid->description('商品简介')->limit(20);
            $grid->column('brand.name' , '品牌名称')->display(function( $v ){
                return $v ? $v :'' ;
            })->limit(20);
            $grid->original_price('原价')->sortable();
            $grid->promotion_price('促销价')->sortable();
            $grid->hits('点击数')->sortable();
            $type = [
                'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
                'off' => ['value' => 0,'text' => '否', 'color' => 'danger']];
            $grid->is_putaway('是否上架')->switch( $type )->sortable();
            $grid->updated_at('最后更新')->sortable();
            $grid->disableExport();
//            $grid->exporter( new GoodsExporter() ) ;
//            $grid->disableRowSelector();
            //$grid->created_at();
            $grid->filter(function (Filter $filter) {
                $filter->disableIdFilter();
                $filter->equal('bid' , '品牌名称')->select(function () {
                    return GoodsBrand::pluck('name', 'bid');
                });
                $filter->like('name', '商品名称');
                $filter->between('original_price', '原价');
                $filter->between('promotion_price' , '促销价');

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

            $content->header('商品管理');
            $content->description('编辑');

            $content->body($this->form()->edit($id));
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

            $content->header('商品管理');
            $content->description('新增');

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
        return Admin::form( Goods::class, function (Form $form) {
            $form->tab( '基本信息', function( Form $form ){
                $form->text('title' , '商品名称')->rules('required|max:50' , [
                    'required' => '请填写商品名称' ,
                    'max' => '商品名称最长为50个字符'
                ]);
                $form->text('description' , '商品简介');
//                $form->select('sid' , '商品厂商')
//                    ->rules('required' , [
//                        'required' => '请选择商品厂商'
//                    ])
//                    ->options( function(){
//                        return Goodscate::where('pid' , 0 )->pluck('name' , 'cid' );
////                    })->load('cid' , '/admin/api/brand');
                $form->select('bid' , '商品品牌')->rules('required' , [
                    'required' => '请选择商品品牌'
                ])->options(function(){
                    return GoodsBrand::pluck('name' , 'bid' );
                });
                $form->currency('original_price' , '商品原价')->symbol("¥")->rules('required' , [
                    'required' => '请填写商品原价'
                ]);
                $form->currency('promotion_price' , '商品促销价')->symbol("¥")->rules('required' , [
                    'required' => '请填写商品促销价'
                ]);

                $form->text('barcode' , '商品条形码')->rules('required|numeric' , [
                    'required' => '请填写商品的条形码' ,
                    'numeric' => '条形码只能为数字' ,
                    'min' => '条形码格式不正确' ,
                    'max' => '条形码格式不正确'
                ]);

                $states = [
                    'on' => [
                        'value' => 1,
                        'text' => '是',
                        'color' => 'success'
                    ],
                    'off' => [
                        'value' => 0,
                        'text' => '否',
                        'color' => 'danger'
                    ]
                ];
                $form->switch('is_putaway' , '是否上架')->states( $states );
                $form->switch('is_new' , '是否新品')->states( $states );
                $form->switch('is_hot' , '是否热门')->states( $states );
                $form->image('cover' , '封面图');
                $form->editor('content','商品详情');
                $form->number('hits' , '点击次数') ;

            });
            $form->tab('商品图片' , function( Form $form ){
                $form->hasMany('images' , '相册', function ( Form\NestedForm $form) {
                    $form->text('title','图片描述');
                    $form->image('image_path' , '图片');

                });
            });
        });
    }
}
    /**
     * 商品的意见反馈
     */
//    public function feedback() {
//        return Admin::content(function (Content $content) {
//
//            $content->header('商品反馈管理');
//            $content->description('列表');
//
//            $content->body($this->feedbackgrid());
//        });
//    }
//
//    protected function feedbackgrid() {
//        return Admin::grid( GoodsFeedback::class, function (Grid $grid) {
//            $grid->model()->with('goods')->orderBy('id' , 'desc' );
//            $grid->model()->goods();
//            $grid->id('ID')->sortable();
//            $grid->column('goods.title' , '商品名称');
//            $grid->nickname('投诉人');
//            $grid->phone('手机号码');
//            $grid->content('反馈内容')->display( function( $v ){
//                return str_limit( $v , 50 );
//            } );
//            $grid->read ( '审核状态' )->display ( function ($v) {
//                return data_get ( config ( 'global.feedback_status' ), $v  , '' );
//            } );
//
//            $grid->disableRowSelector ();
//            // $grid->disableExport();
//            $grid->exporter( new GoodsFeedbackExporter() );
//            $grid->disableCreation ();
//            $grid->filter( function( Filter $filter ){
//                $filter->disableIdFilter();
//                $filter->where( function( $query ){
//                    $input = $this->input ;
//                    return $query->where('pid' , function( $query ) use( $input ) {
//                        $query->from('vcm_goods')->where('name' , 'like' , "%{$input}%")->select('gid');
//                    });
//                } , '商品名称');
//                $filter->where( function( $query ){
//                    $input = $this->input ;
//                    return $query->whereIn('pid' , function( $query ) use( $input ) {
//                        $query->from('vcm_goods')->where('sid' , $input )->select('gid');
//                    });
//                } , '商品厂商')->select( function(){
//                    return Goodscate::where('pid' , 0 )->pluck('name' , 'cid');
//                });
//                $filter->where( function( $query ){
//                    $input = $this->input ;
//                    return $query->whereIn('pid' , function( $query ) use( $input ) {
//                        $query->from('vcm_goods')->where('cid' , $input )->select('gid');
//                    });
//                } , '商品品牌')->select( function(){
//                    return Goodscate::where('pid' , '>' , 0 )->pluck('name' , 'cid');
//                });
//                $filter->between('created_at' , '提交日期')->date();
//
//            });
//            $grid->actions ( function ( $action) {
//                $action->disableDelete ();
//                $action->disableEdit ();
//                $url = route ( 'admin.goods.feedbackshow', [
//                    'id' => $action->row->id
//                ] );
//                $action->append ( "<a href='{$url}'><i class='fa fa-globe'></i></a>" );
//            } );
//        });
//    }
//
//    public function feedbackshow( $id ) {
//        return Admin::content(function (Content $content) use( $id )  {
//
//            $content->header('意见反馈管理');
//            $content->description('详情');
//            $form = $this->feedbackform()->view( $id );
//            if( $form->model()->status > 0 ) {
//                $time = $form->model()->deal_time ;
//                $form->display('deal_time')->with( function( $v ) use( $time ) {
//                    return $time  ? date('Y-m-d' , $time ) : '' ;
//                } );
//            }
//            $form->disableSubmit();
//            $form->disableReset();
//            $content->row( $form );
//            if( $form->model()->read == 0 ) {
//                $url = route('admin.goods.feedback.setread' , ['id' => $form->model()->id ] );
//                $pannel = "<div class='col-sm-off-2 col-sm-8'><a href='{$url}' class='btn btn-primary'>设置为已读</a></div>";
//                $content->row( $pannel );
//            }
//        });
//    }
//
//    protected function feedbackform() {
//        return Admin::form( GoodsFeedback::class, function (Form $form) {
//            // $form->display('store.company' , '商家名称') ;
//            $form->display ( 'nickname', '反馈人' );
//            $form->display ( 'phone', '联系电话' );
//            $form->display ( 'content', '投诉内容' );
//            $form->display ( 'cover', '图片' )->with ( function ($v) {
//                return "<img src='" . config('global.file_domain') . $v . "' width='100%' />" ;
//            } );
//            $form->display('read' , '处理状态')->with( function( $v ){
//                return data_get( config('global.feedback_status') , $v ) ;
//            } );
//            $form->display('create_time' , '投诉时间')->with( function( $v ){
//                return $v ? date('Y-m-d' , $v ) : $this->created_at ;
//            } );
//        }) ;
//    }
//
//    public function setRead( $id ) {
//        $feedback = GoodsFeedback::findOrFail( (int) $id );
//        $feedback->read = 1 ;
//        if( $feedback->save() ) {
//            //event(new \App\Events\userActionEvent('\App\Modules\Admin\Http\Feedback', $feedback->id, 'feedback.agree', '将编号为' . $feedback->id . '的建议反馈设置为采纳' ));
//            admin_toastr('处理完成');
//            return back();
//        }
//        admin_toastr('处理失败');
//        return back();
//    }
//}
