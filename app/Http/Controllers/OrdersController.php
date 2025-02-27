<?php

namespace App\Http\Controllers;

use App\Actions\HandleRefundMoneyAction;
use App\Actions\OrdersWithAllDataAction;
use App\Actions\UserVerficationCheck;
use App\Actions\ValidateCouponAction;
use App\Actions\VerifyAccess;
use App\Filters\EndDateFilter;
use App\Filters\orders\RateOrderFilter;
use App\Filters\orders\StatusOrderFilter;
use App\Filters\StartDateFilter;
use App\Http\Enum\OrderStatuesEnum;
use App\Http\patterns\builder\OrderBuilder;
use App\Http\patterns\builder\RemoveOrderItemBuilder;
use App\Http\patterns\strategy\payment\PaymentInterface;
use App\Http\Requests\orderItemsFormRequest;
use App\Http\Requests\ordersFormRequest;
use App\Http\Requests\orderStatusFormRequest;
use App\Http\Resources\CouponResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderStatusResource;
use App\Models\orders_tracking;
use App\Models\saved_locations;
use App\Services\Messages;
use App\Services\OrderStatuesService;
use App\Services\WalletUserService;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    //
    public $payment_obj;

    public function __construct(PaymentInterface $payment)
    {
        $this->payment_obj = $payment;
    }

    public function index(Request $request)
    {
        VerifyAccess::execute('pi pi-cart-plus|/orders|read');
        $data = OrdersWithAllDataAction::get();
        $output = app(Pipeline::class)
            ->send($data)
            ->through([
                StartDateFilter::class,
                EndDateFilter::class,
                StatusOrderFilter::class,
                RateOrderFilter::class,
            ])
            ->thenReturn()
            ->paginate(request('limit') ?? 10);

        return OrderResource::collection($output);
    }

    public function create(ordersFormRequest $request, $type = 'order')
    {

        // get data after validation
        $data = $request->validated();

        // check if user acc is verified or not
        if (! (UserVerficationCheck::check())) {
            return Messages::error(__('errors.account_not_verified'), 401);
        }
        // check if location_id is sent or not
        if (! (isset($data['location_id']))) {
            $saved = saved_locations::activeLocation()->activeUser()
                ->firstOrFailWithCustomError(__('errors.not_found_location'));
            $data['location_id'] = $saved->id;
        }

        $base_info_order = collect($data)->except('items', 'coupon_serial', 'payment');
        $builder = new OrderBuilder($base_info_order, $data['items'], $data['payment'], $data['coupon_serial'] ?? null, $this->payment_obj);
        $order_action = $builder->initOrder($type)
            ->prepare_status()
            ->save_items()
            ->validate_coupon($type)
            ->save_payment($type);

        return $order_action;
    }

    public function update_status(orderStatusFormRequest $request)
    {
        VerifyAccess::execute('pi pi-cart-plus|/orders|update');

        $data = $request->validated();
        // check order doesn't have status before
        $check = orders_tracking::query()
            ->where('order_id', $data['order_id'])
            ->where('status', '=', $data['status'])
            ->failWhenFoundResult(__('errors.status_exist_select_another'));
        if (is_bool($check)) {
            $status = orders_tracking::query()->create($data);

            return Messages::success(__('messages.saved_successfully'), OrderStatusResource::make($status));
        }

        return $check;
    }

    public function remove_item(orderItemsFormRequest $request)
    {
        VerifyAccess::execute('pi pi-cart-plus|/orders|update');
        $data = $request->validated();
        $obj = new RemoveOrderItemBuilder($data);
        if (is_bool($obj->init())) {
            return $obj->detect_full_cost()->cancel_item()->handle_payment();
        }

        return $obj->init();
    }

    public function cancel(Request $request)
    {
        VerifyAccess::execute('pi pi-cart-plus|/orders|update');
        $request->merge(['status' => OrderStatuesEnum::cancelled->value]);
        DB::beginTransaction();
        if (request()->filled('order_id')) {
            // get order info
            $order = OrdersWithAllDataAction::get()
                ->when(auth()->user()->roleName() == 'client',
                    fn ($e) => $e->where('user_id', '=', auth()->id())
                )
                ->where('id', '=', request('order_id'))
                ->with('last_status')
                ->FailIfNotFound(__('errors.not_found_data'));
            // check order in pending mode or review mode
            if (OrderStatuesService::check($order->last_status, [OrderStatuesEnum::pending->value, OrderStatuesEnum::review->value])) {
                // cancel this order

                orders_tracking::query()->create($request->all());
                // return refund to client
                WalletUserService::add_money_to_user_acc($order->payment->money);

                // cancel current order
                $order->status = OrderStatuesEnum::cancelled;
                $order->save();
                // handle refund order
                HandleRefundMoneyAction::handle($order, $order->payment->money);
                // return success message
                DB::commit();

                return Messages::success(__('messages.operation_done_successfully'));
            } else {
                // you cant cancel this order
                return Messages::error(__('errors.order_status_not_accept_to_be_cancelled'));
            }
        }
    }

    public function validate_coupon()
    {
        $data = ValidateCouponAction::validate(request('number'));
        if (isset($data->original['errors'])) {
            return $data;
        }

        return CouponResource::make($data);
    }
}
