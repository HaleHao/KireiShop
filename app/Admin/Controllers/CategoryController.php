<?php

namespace App\Admin\Controllers;

use App\Http\Models\GoodsCategory;
use Encore\Admin\Form;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;

class CategoryController extends Controller {

    use ModelForm ;

    public function index() {
        return Admin::content(function (Content $content) {
            $content->header( '分类管理' );
            $content->description( '列表' );

            $content->row(function (Row $row) {
                $row->column(12, $this->treeView()->render());
            });
        });
    }

    protected function treeView() {
        return GoodsCategory::tree(function (Tree $tree) {
            $tree->setView([
                'tree'      => 'admin::tree',
                'branch'    => 'admin::tree.branch',
            ]);

            $tree->branch(function ($branch) {
                $payload = "<strong>{$branch['title']}</strong>";

                if (!isset($branch['children'])) {
                    $uri = '';
                    $payload .= "&nbsp;&nbsp;&nbsp;<a href=\"$uri\" class=\"dd-nodrag\">$uri</a>";
                }
                return $payload;
            });
        });
    }


    public function create() {
        return Admin::content(function (Content $content) {
            $content->header( '商品分类管理' );
            $content->description( '新增' );

            $content->row($this->form() );
        });
    }

    /**
     * Edit interface.
     *
     * @param string $id
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header( '商品分类管理' );
            $content->description( '编辑' );

            $content->row($this->form()->edit($id));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        return GoodsCategory::form(function (Form $form) {
            $form->display('id', 'ID');

            $form->select('category_id', '上级类别')->options( GoodsCategory::selectOptions());
            $form->text('title', '分类名称')->rules('required');
            $form->icon('icon' , '分类图标');
            $form->number('sort', '排序')->rules('required|min:0' , [
                'required' => '请填写商品排序' ,
                'min' => '排序最小为0'
            ]);
            $form->text('description' , '分类介绍')->rules('required|min:0' , [
                'required' => '请填写商品分类介绍' ,
            ]);
            $form->image('image' , '分类图片');
            $states = [
                'on'  => ['value' => 1, 'text' => '是', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '否', 'color' => 'default'],
            ];
            $form->switch('status' , '是否激活')->states( $states )->default( 1 );
        });
    }

    public function destroy( $id ) {
        $count = GoodsCategory::where('category_id' , $id )->count();
        if( $count ) {
            return response()->json([
                'status'  => false,
                'message' => '请先删除下级分类',
            ]);
        }
        $count = GoodsCategory::where('category_id' , $id )->count();
        if( $count ) {
            return response()->json([
                'status'  => false,
                'message' => '请先删除下级分类',
            ]);
        }
        if ($this->form()->destroy($id)) {
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }
}