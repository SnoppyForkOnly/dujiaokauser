<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\InviteUser;
use App\Models\User;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class InviteUserController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new InviteUser(['user', 'order']), function (Grid $grid) {
            $grid->column('order_id', '订单ID');
            $grid->column('user.email','返利用户');
            $grid->column('order.title', '订单名称');
            $grid->column('amount')->sortable();
            $grid->column('order.email', '支付用户');
            $grid->column('status')->using([0=>'可提现', 1=>'已提现'])->label([
                0 => Admin::color()->success(),
                1 => Admin::color()->info(),
            ])->sortable();
            $grid->column('created_at')->sortable();
            $grid->model()->orderByDesc('id');

            $grid->filter(function (Grid\Filter $filter) {
                $users = User::query()->pluck('email', 'id');
                $filter->equal('id');
                $filter->equal('user_id')->select($users);
                $filter->equal('order_id');
                $filter->equal('status')->select([0=>'可提现', 1=>'已提现']);
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
        return Show::make($id, new InviteUser(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('order_id');
            $show->field('account');
            $show->field('amount');
            $show->field('status');
            $show->field('withdraw_id');
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
        return Form::make(new InviteUser(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('order_id');
            $form->text('account');
            $form->text('amount');
            $form->text('status');
            $form->text('withdraw_id');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
