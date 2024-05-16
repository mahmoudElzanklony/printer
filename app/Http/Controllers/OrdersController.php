<?php

namespace App\Http\Controllers;

use App\Actions\OrdersWithAllDataAction;
use App\Actions\ValidateCouponAction;
use App\Filters\EndDateFilter;
use App\Filters\LimitFilter;
use App\Filters\orders\StatusOrderFilter;
use App\Filters\ServiceIdFilter;
use App\Filters\StartDateFilter;
use App\Http\Controllers\Filters\NameFilter;
use App\Http\patterns\builder\OrderBuilder;
use App\Http\patterns\builder\RemoveOrderItemBuilder;
use App\Http\patterns\strategy\payment\PaymentInterface;
use App\Http\Requests\orderItemsFormRequest;
use App\Http\Requests\ordersFormRequest;
use App\Http\Requests\orderStatusFormRequest;
use App\Http\Resources\CouponResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderStatusResource;
use App\Models\orders;
use App\Models\orders_tracking;
use App\Services\Messages;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

class OrdersController extends Controller
{
    //
    public $payment_obj;
    public function __construct(PaymentInterface $payment)
    {
        $this->payment_obj = $payment;
    }

    public function index()
    {
        $data = OrdersWithAllDataAction::get();
        $output  = app(Pipeline::class)
            ->send($data)
            ->through([
                StartDateFilter::class,
                EndDateFilter::class,
                StatusOrderFilter::class
            ])
            ->thenReturn()
            ->paginate(10);
        return OrderResource::collection($output);
    }
    public function create(ordersFormRequest $request)
    {

        $data =  $request->validated();
        $base_info_order = collect($data)->except('items','coupon_serial','payment');
        $builder = new OrderBuilder($base_info_order,$data['items'],$data['payment'],$data['coupon_serial'] ?? null,$this->payment_obj);

        try {
            $order_action = $builder->initOrder()
                                    ->prepare_status()
                                    ->save_items()
                                    ->validate_coupon()
                                    ->save_payment();
            return $order_action;
        }catch (\Throwable $e){
            return Messages::error($e->getMessage());
        }
        /*$data = $request->validated();
        return $data;*/
    }

    public function update_status(orderStatusFormRequest $request)
    {
        $data = $request->validated();
        // check order doesn't have status before
        $check = orders_tracking::query()->where('status','=',$data['status'])->failWhenFoundResult(__('errors.status_exist_select_another'));
        if(is_bool($check)) {
            $status = orders_tracking::query()->create($data);
            return Messages::success(__('messages.saved_successfully'), OrderStatusResource::make($status));
        }
        return $check;
    }

    public function remove_item(orderItemsFormRequest $request)
    {
        $data = $request->validated();
        $obj = new RemoveOrderItemBuilder($data);
        if(is_bool($obj->init())){
            return $obj->detect_full_cost()->cancel_item()->handle_payment();
        }
        return $obj->init($data);
    }

    public function validate_coupon()
    {
        $data =  ValidateCouponAction::validate(request('number'));
        if(isset($data->original['errors'])){
            return $data;
        }
        return CouponResource::make($data);
    }
}
