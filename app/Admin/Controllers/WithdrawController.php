<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Withdraw;
use App\Models\User;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class WithdrawController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Withdraw(['user']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('user.email', '用户邮箱');
            $grid->column('amount');
            $grid->column('account', '账号类型')->display(function ($account){
                switch ($account){
                    case "alipay":
                        return "支付宝";
                    case "wechat":
                        return "微信";
                    default:
                        return "USDT";
                }
            });
            $grid->column('address')->copyable();
            $grid->column('type')->using([1 => '转余额', 2 => '申请提现'])->label([
                1 => Admin::color()->primary(),
                2 => Admin::color()->success(),
            ]);
            $grid->column('status')->select([0 => '审核中', 1 => '已完成'])->sortable();
            $grid->column('created_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $users = User::query()->pluck('email', 'id');
                $filter->equal('id');
                $filter->equal('user_id')->select($users);
                $filter->equal('type')->select([1 => '转余额', 2 => '申请提现']);
                $filter->equal('status')->select([1 => '审核中', 2 => '已完成']);
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
        return Show::make($id, new Withdraw(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('amount');
            $show->field('type');
            $show->field('address');
            $show->field('status');
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
        return Form::make(new Withdraw(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('amount');
            $form->text('type');
            $form->text('address');
            $form->text('status');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
