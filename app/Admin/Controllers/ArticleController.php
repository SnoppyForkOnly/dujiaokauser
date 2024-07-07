<?php

namespace App\Admin\Controllers;

use App\Models\ArticleCategory as ArticleCategory;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Admin\Repositories\Articles;

/**

 */
class ArticleController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Articles(['category']), function (Grid $grid) {
            $grid->model()->orderBy('id', 'DESC');
            $grid->column('id');
            $grid->column('picture', '文章图片')->image('', 80, 80);
            $grid->column('keywords');
            $grid->column('description');
            $grid->column('category.category_name', admin_trans('article.fields.category_id'));
            $grid->column('link');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Articles(['category']), function (Show $show) {
            $show->field('id');
            $show->field('title');
            $show->field('keywords');
            $show->field('description');
            $show->field('category.category_name', admin_trans('article.fields.category_id'));
            $show->field('link');
            $show->field('picture')->image();
            $show->field('content');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Articles(), function (Form $form) {
            $form->display('id')->required();
            $form->text('title')->required();
            $form->text('keywords')->required();
            $form->text('description')->required();
            $form->select('category_id')->options(
                ArticleCategory::query()->pluck('category_name', 'id')
            )->required();
            $form->text('link')->default(" ")->help(admin_trans('article.helps.link'));
          $form->image('picture','文章缩略图')->autoUpload()->uniqueName()->help(admin_trans('可不上传，为默认图片'));
             $form->url('picture_url')->help(admin_trans('图片URL'));
            $form->saving(function (Form $form) {
                if($form->picture_url)
                    $form->picture = $form->picture_url;
                $form->deleteInput('picture_url');
            });
            $form->editor('content')->required();
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
