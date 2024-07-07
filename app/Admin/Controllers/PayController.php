<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;
use App\Admin\Repositories\Pay;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\Pay as PayModel;

class PayController extends AdminController
{


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Pay(), function (Grid $grid) {
            $grid->column('id')->editable()->sortable();
            $grid->column('pay_name')->editable()->sortable();
            $grid->column('pay_check');
            $grid->column('pay_fee')->editable()->sortable();
            $grid->column('is_openfee','手续费开关')->switch();
            $grid->column('is_openhui','强制汇率开关')->switch();
            $grid->column('pay_operation','汇率预算符号')->editable()->sortable();
            
                 $grid->column('pay_qhuilv','汇率比例')->editable()->sortable();
            
            $grid->column('pay_method')->select(PayModel::getMethodMap());
            $grid->column('merchant_id')->limit(20);
            $grid->column('pay_client')->select(PayModel::getClientMap());
            $grid->column('pay_handleroute');
            $grid->column('is_open')->switch();
            $grid->disableDeleteButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('pay_check');
                $filter->like('pay_name');
                $filter->scope(admin_trans('dujiaoka.trashed'))->onlyTrashed();
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if (request('_scope_') == admin_trans('dujiaoka.trashed')) {
                    $actions->append(new Restore(PayModel::class));
                }
            });
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                if (request('_scope_') == admin_trans('dujiaoka.trashed')) {
                    $batch->add(new BatchRestore(PayModel::class));
                }
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
        return Show::make($id, new Pay(), function (Show $show) {
              $show->field('id');
            $show->field('pay_name');
            $show->field('merchant_id');
            $show->field('merchant_key');
            $show->field('merchant_pem');
            $show->field('pay_check');
            $show->fielf('pay_fee');
             $show->field('is_openfee', '手续费开关')->as(function ($isOpen) {
                if ($isOpen == PayModel::STATUS_OPEN) {
                    return admin_trans('dujiaoka.status_open');
                } else {
                    return admin_trans('dujiaoka.status_close');
                }
            });
              $show->field('is_openhui', '汇率开关')->as(function ($isOpen) {
                if ($isOpen == PayModel::STATUS_OPEN) {
                    return admin_trans('dujiaoka.status_open');
                } else {
                    return admin_trans('dujiaoka.status_close');
                }
            });
             $show->fielf('pay_operation', '汇率符号');
                          $show->fielf('pay_qhuilv', '汇率比例');
           
           
           
            $show->field('pay_client')->as(function ($payClient) {
                if ($payClient == PayModel::PAY_CLIENT_PC) {
                    return admin_trans('pay.fields.pay_client_pc');
                } else {
                    return admin_trans('pay.fields.pay_client_mobile');
                }
            });
            $show->field('pay_handleroute');
            $show->field('pay_method')->as(function ($payMethod) {
                if ($payMethod == PayModel::METHOD_JUMP) {
                    return admin_trans('pay.fields.method_jump');
                } else {
                    return admin_trans('pay.fields.method_scan');
                }
            });
            $show->field('is_open')->as(function ($isOpen) {
                if ($isOpen == PayModel::STATUS_OPEN) {
                    return admin_trans('dujiaoka.status_open');
                } else {
                    return admin_trans('dujiaoka.status_close');
                }
            });
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
        return Form::make(new Pay(), function (Form $form) {
            $form->display('id');
            $form->text('pay_name')->required();
            $form->currency('pay_fee')->default(0)->help(__('pay.helps.pay_fee'))->required();
             $form->switch('is_openfee','手续费开关')->default(PayModel::STATUS_OPEN);
                     $form->switch('is_openhui','强制汇率开关')->default(PayModel::STATUS_OPEN);
               $form->text('pay_operation','汇率符号')->default('*')->help(__('汇率符号，默认是 *，根据您的网站单位来更改'))->required();
            $form->currency('pay_qhuilv','汇率比例')->default(0)->help(__('设置汇率比例，默认不设置是1:1'))->required();
         
     
            $form->text('merchant_id')->required();
            $form->textarea('merchant_key');
            $form->textarea('merchant_pem')->required();
            $form->text('pay_check')->required();
            $form->radio('pay_client')
                ->options(PayModel::getClientMap())
                ->default(PayModel::PAY_CLIENT_PC)
                ->required();
            $form->radio('pay_method')
                ->options(PayModel::getMethodMap())
                ->default(PayModel::METHOD_JUMP)
                ->required();
            $form->text('pay_handleroute')->required();
            $form->switch('is_open')->default(PayModel::STATUS_OPEN);
            $form->display('created_at');
            $form->display('updated_at');
            $form->disableDeleteButton();
        });
    }
}
