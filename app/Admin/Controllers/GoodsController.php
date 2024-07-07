<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;
use App\Admin\Repositories\Goods;
use App\Models\Carmis;
use App\Models\Coupon;
use App\Models\GoodsGroup as GoodsGroupModel;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\Goods as GoodsModel;
use App\Models\Pay as PayModel;//增加支付模块引入，避免报错
use App\Jobs\CreateGoodsPush;//增加上新通知
use App\Jobs\GoodsPriceReducePush;//增加商品降价通知
use Illuminate\Support\Facades\Cache;//增加相关缓存


class GoodsController extends AdminController
{


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Goods(['group', 'coupon']), function (Grid $grid) {
            $grid->model()->orderBy('id', 'DESC');
            $grid->column('id')->sortable();
            $grid->column('picture')->image('', 100, 100);
            $grid->column('gd_name');
            $grid->column('gd_description');
            $grid->column('gd_keywords');
            $grid->column('group.gp_name', admin_trans('goods.fields.group_id'));
            $grid->column('type')
                ->using(GoodsModel::getGoodsTypeMap())
                ->label([
                    GoodsModel::AUTOMATIC_DELIVERY => Admin::color()->success(),
                    GoodsModel::MANUAL_PROCESSING => Admin::color()->info(),
                ]);
            $grid->column('actual_price')->editable()->sortable();
             $grid->column('preselection')->editable()->sortable();
                  $grid->column('grade_3')->editable()->sortable();
                  $grid->column('grade_2')->editable()->sortable();
              $grid->column('grade_1')->editable()->sortable();
               
               
            $grid->column('in_stock')->display(function () {
                // 如果为自动发货，则加载库存卡密
                if ($this->type == GoodsModel::AUTOMATIC_DELIVERY) {
                    return Carmis::query()->where('goods_id', $this->id)
                        ->where('status', Carmis::STATUS_UNSOLD)
                        ->count();
                } else {
                    return $this->in_stock;
                }
            });
            $grid->column('sales_volume');
            $grid->column('ord')->editable()->sortable();
            $grid->column('is_open')->switch();
            $grid->column('open_rebate', '开启返利')->switch();
            $grid->column('rebate_rate', '返佣比例')->editable()->sortable();
            $grid->column('created_at')->sortable();
            $grid->column('updated_at');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('gd_name');
                $filter->equal('type')->select(GoodsModel::getGoodsTypeMap());
                $filter->equal('group_id')->select(GoodsGroupModel::query()->pluck('gp_name', 'id'));
                $filter->scope(admin_trans('dujiaoka.trashed'))->onlyTrashed();
                $filter->equal('coupon.coupons_id', admin_trans('goods.fields.coupon_id'))->select(
                    Coupon::query()->pluck('coupon', 'id')
                );
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if (request('_scope_') == admin_trans('dujiaoka.trashed')) {
                    $actions->append(new Restore(GoodsModel::class));
                }
            });
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                if (request('_scope_') == admin_trans('dujiaoka.trashed')) {
                    $batch->add(new BatchRestore(GoodsModel::class));
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
        return Show::make($id, new Goods(), function (Show $show) {
            $show->id('id');
            $show->field('gd_name');
            $show->field('gd_description');
            $show->field('gd_keywords');
            $show->field('picture')->image();
            $show->field('actual_price');
            $show->field('in_stock');
            $show->field('ord');
            $show->field('sales_volume');
            $show->field('type')->as(function ($type) {
                if ($type == GoodsModel::AUTOMATIC_DELIVERY) {
                    return admin_trans('goods.fields.automatic_delivery');
                } else {
                    return admin_trans('goods.fields.manual_processing');
                }
            });
            $show->field('preselection');
            $show->field('grade_3');
            $show->field('grade_2');
            $show->field('grade_1');
             $show->field('open_rebate', '开启返利');
               $show->field('rebate_rate', '返佣比例');
            $show->field('is_open')->as(function ($isOpen) {
                if ($isOpen == GoodsGroupModel::STATUS_OPEN) {
                    return admin_trans('dujiaoka.status_open');
                } else {
                    return admin_trans('dujiaoka.status_close');
                }
            });
            $show->wholesale_price_cnf()->unescape()->as(function ($wholesalePriceCnf) {
                return  "<textarea class=\"form-control field_wholesale_price_cnf _normal_\"  rows=\"10\" cols=\"30\">" . $wholesalePriceCnf . "</textarea>";
            });
            $show->other_ipu_cnf()->unescape()->as(function ($otherIpuCnf) {
                return  "<textarea class=\"form-control field_wholesale_price_cnf _normal_\"  rows=\"10\" cols=\"30\">" . $otherIpuCnf . "</textarea>";
            });
            $show->api_hook()->unescape()->as(function ($apiHook) {
                return  "<textarea class=\"form-control field_wholesale_price_cnf _normal_\"  rows=\"10\" cols=\"30\">" . $apiHook . "</textarea>";
            });;
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Goods(), function (Form $form) {
            $form->block(6, function (Form\BlockForm $form) {
                $form->text('gd_name')->required();
                $form->text('gd_description')->required();
                $form->text('gd_keywords')->required();
                $form->select('group_id')->options(
                    GoodsGroupModel::query()->pluck('gp_name', 'id')
                )->required();
                $form->image('picture')->autoUpload()->uniqueName()->help(admin_trans('goods.helps.picture'));
            $form->url('picture_url')->help(admin_trans('goods.helps.picture_url'));
                $form->radio('type')->options(GoodsModel::getGoodsTypeMap())->default(GoodsModel::AUTOMATIC_DELIVERY)->required();
                $form->currency('actual_price')->default(0)->required();
               $form->currency('grade_3')->default(0)->required();
               $form->currency('grade_2')->default(0)->required();
                 $form->currency('grade_1')->default(0)->required();
             $form->number('min_buy_count')->default(1)->help(admin_trans('代理最小购买数'));
              $form->number('max_buy_count')->default(1)->help(admin_trans('代理最大购买数'));
                $form->multipleSelect('payment_limit')
            ->options(PayModel::where('is_open', PayModel::STATUS_OPEN)->pluck('pay_name', 'id')->toArray())
            ->saving(function ($v) {return json_encode($v);})
            ->help(admin_trans('goods.helps.payment_limit'));
                $form->number('in_stock')->help(admin_trans('goods.helps.in_stock'));

            });
            $form->block(6, function (Form\BlockForm $form) {
                $form->number('sales_volume');
                $form->number('buy_limit_num')->help(admin_trans('goods.helps.buy_limit_num'));
                $form->number('min_buy_num', '最低购买数')->help(admin_trans('最低购买数量，0为不限制客户单次下单数'));
                $form->currency('preselection')->default(0)->help(admin_trans('自选卡密加价'));
                $form->textarea('other_ipu_cnf')->rows(3)->help(admin_trans('goods.helps.other_ipu_cnf'));
                $form->textarea('wholesale_price_cnf')->rows(3)->help(admin_trans('goods.helps.wholesale_price_cnf'));
                $form->textarea('api_hook')->rows(3);
                $form->number('ord')->default(1)->help(admin_trans('dujiaoka.ord'));
                $form->switch('open_rebate', '开启返利')->default(1);
                 $form->text('rebate_rate')->default(0)->help(admin_trans('返利百分比，你设置5就是5%'));
                $form->switch('is_open')->default(GoodsModel::STATUS_OPEN);
                $form->showFooter();
            });
            $form->block(12, function (Form\BlockForm $form) {
                $form->editor('buy_prompt');
                $form->editor('description');
            });

            $form->saving(function (Form $form) use (&$goodInfo) {
                
                  if($form->picture_url)
                    $form->picture = $form->picture_url;
                $form->deleteInput('picture_url');
                
           

                if ($form->isEditing()) {
                    $goodInfo = app('Service\GoodsService')->detail($form->model()->id);
                    Cache::put('goods:' . $goodInfo->id, $goodInfo, 30);
                }
            });
            $form->saved(function (Form $form, $result) {
                // 判断是否是新增操作
                if ($form->isCreating()) {
                    // 发送通知
                    CreateGoodsPush::dispatch($result);
                } else if ($form->isEditing()) {
                    if (!$form->actual_price) {
                        return;
                    }
                    $goodInfo = Cache::get('goods:' . $form->model()->id);
                    $old_price = floatval($goodInfo->actual_price);
                    $new_price = floatval($form->actual_price);
                    if ($new_price < $old_price) {
                        GoodsPriceReducePush::dispatch($goodInfo->id, $old_price, $new_price);
                    }
                }
            });
        });
    }
}