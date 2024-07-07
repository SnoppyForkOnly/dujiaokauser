<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\User;
use App\Models\Goods;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Str;

class UserController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new User(['invite_user']), function (Grid $grid) {
            $grid->column('id');
            $grid->column('email');
            $grid->column('money');
            $grid->column('grade');
            $grid->column('last_ip');
            $grid->column('status');
            $grid->column('invite_code');
            $grid->column('pid');
            $grid->column('invite_user.email', '邀请者');
            $grid->column('created_at');
         
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('email');
                $filter->equal('invite_code');
                $filter->equal('pid')->select(\App\Models\User::query()->pluck('email', 'id'));
                $filter->like('remark');
            });
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
        return Show::make($id, new User(), function (Show $show) {
            $show->field('id');
            $show->field('email');
            $show->field('password');
            $show->field('money');
            $show->field('grade');
            $show->field('last_ip');
            $show->field('last_login');
            $show->field('register_at');
            $show->field('status');
            $show->field('invite_code');
            $show->field('pid');
            $show->field('remark');
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
        return Form::make(new User(), function (Form $form) {
            $form->display('id');
            $form->email('email')->required()->rules(function (Form $form) {
                // 如果不是编辑状态，则添加字段唯一验证
                if (!$id = $form->model()->id) {
                    return 'unique:users,email';
                }
            });
            $form->text('password')->value('')->placeholder('留空代表不改变');
            $form->decimal('money')->required()->default(0);
      
            $form->decimal('grade')->required()->default(0)->help(admin_trans('代理等级默认为0不开启商品批发功能,最高到3级'));
            $form->switch('status');
            $form->text('invite_code');
            $form->select('pid')->options(
                \App\Models\User::query()->pluck('email', 'id')
            )->default(0);
            $form->text('remark');
            $form->saving(function (Form $form) {
                
                if ($form->isEditing() && $form->password) {
                    $form->password = bcrypt($form->password);
                } elseif ($form->isCreating()) {
                    $form->password = $form->password ? bcrypt($form->password) : bcrypt(123456);
                    if(is_null($form->invite_code)){
                        $form->invite_code = Str::random(8);
                    }
                } else {
                    $form->deleteInput('password');
                }
                if (is_null($form->username)) {
                    $form->username = $form->email;
                }
                if (is_null($form->pid)) {
                    $form->pid = 0;
                }
            });
        });
    }
}
