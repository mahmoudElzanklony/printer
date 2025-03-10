<?php

namespace App\Http\patterns\builder;

use App\Actions\AddFirstPageToPdfAction;
use App\Actions\AddToWalletHistoryAction;
use App\Actions\PaymentModalSave;
use App\Actions\ValidateCouponAction;
use App\Http\Enum\OrderStatuesEnum;
use App\Http\Resources\OrderResource;
use App\Models\orders;
use App\Models\orders_coupons;
use App\Models\orders_items;
use App\Models\orders_items_properties;
use App\Models\orders_tracking;
use App\Models\payments;
use App\Models\properties;
use App\Models\saved_properties_settings_answers;
use App\Models\services;
use App\Services\Messages;
use Illuminate\Support\Facades\DB;

class OrderBuilder
{
    private $base_order_info;

    private $items;

    private $coupon_number;

    private $payment;

    private $order;

    private $coupon = null;

    private $total_price_order = 0;

    private $payment_strategy;

    private $cart_exists = false;

    private $files_uploaded = [];

    public function __construct($base_order_info, $items, $payment, $coupon_number, $payment_strategy)
    {
        $this->base_order_info = collect($base_order_info)->toArray();
        $this->items = $items;
        $this->coupon_number = $coupon_number;
        $this->payment = $payment;
        $this->payment_strategy = $payment_strategy;
    }

    public function initOrder($type = 'order')
    {
        DB::beginTransaction();
        $this->base_order_info['note'] = json_encode(['system_refund' => '', 'client' => $this->base_order_info['note'] ?? ''], JSON_UNESCAPED_UNICODE);
        // create new order
        if ($type == 'order') {
            $this->base_order_info['status'] = 'working';
        } else {
            $this->base_order_info['status'] = OrderStatuesEnum::cart;
            // check if orders table has already cart
            $cart = $this->check_cart_exists();
            if ($cart) {
                $this->order = $cart;
                $this->cart_exists = true;

                return $this;
            }
        }
        $this->order = orders::query()->create($this->base_order_info);

        return $this;
    }

    public function check_cart_exists()
    {
        $cart = orders::query()->where('user_id', '=', auth()->id())
            ->where('status', '=', OrderStatuesEnum::cart)
            ->first();

        return $cart;
    }

    public function prepare_status()
    {
        if ($this->base_order_info['status'] == 'working') {
            orders_tracking::query()->create([
                'order_id' => $this->order->id,
                'status' => OrderStatuesEnum::pending,
            ]);
        }

        return $this;
    }

    public function save_items()
    {
        foreach ($this->items as $item) {
            $file = $this->check_upload_file($item['file']);
            array_push($this->files_uploaded, $file);
            $item['order_id'] = $this->order->id;
            $items_data = $this->prepare_order_item($item, $file);
            $order_item = orders_items::query()->create($items_data);
            // init properties price
            $total_properties_price = 0;
            // get properties that related to this order
            if (array_key_exists('saved_properties', $item)) {
                $item['properties'] = saved_properties_settings_answers::query()
                    ->where('saved_properties_settings_id', $item['saved_properties'])->select('property_id')->get()->toArray();
            }

            foreach ($item['properties'] as $property) {

                $property_item = $this->create_item_property($property, $order_item);
                $total_properties_price += $property_item->price;
            }

            $this->total_price_order += ($total_properties_price + $order_item->price) * $order_item->paper_number * $order_item->copies_number;
        }

        return $this;
    }

    public function validate_coupon($type = 'order')
    {
        if (isset($this->coupon_number)) {
            $coupon_data = ValidateCouponAction::validate($this->coupon_number);
            if (! (isset($coupon_data->original['errors']))) {
                $coupon_final_price_with_coupon_value = ValidateCouponAction::detectFinalPrice($this->total_price_order, $coupon_data);
                $this->total_price_order = $coupon_final_price_with_coupon_value['final_price'];
                // save coupon related to this order
                $this->save_coupon_to_order($coupon_data->id, $coupon_final_price_with_coupon_value['coupon_value']);
            }
        }

        /*if($type != 'order'){
            $this->load_relations();
            return Messages::success(__('messages.saved_successfully'),OrderResource::make($this->order));
        }*/
        return $this;

    }

    public function save_coupon_to_order($coupon_id, $coupon_value)
    {
        orders_coupons::query()->create([
            'order_id' => $this->order->id,
            'coupon_id' => $coupon_id,
            'coupon_value' => $coupon_value,
        ]);
    }

    public function load_relations()
    {
        $this->order->load('location');
        $this->order->load('items.properties.property');
        $this->order->load('user');
        $this->order->load('payment');
        $this->order->load('coupon_info');
        $this->order->load('last_status');
    }

    public function save_payment($type = 'order')
    {
        if ($this != 'order') {
            return $this->continue_payment();
        } else {
            $validate_payment = $this->payment_strategy->validate($this->total_price_order);
            if ($validate_payment === true) {
                return $this->continue_payment();
            } else {
                return $validate_payment;
            }
        }
    }

    public function check_upload_file($file)
    {
        $name = time().rand(0, 9999999999999).'_file.'.$file->getClientOriginalExtension();
        $file->move(public_path('orders_files/'), $name);

        return $name;
    }

    public function prepare_order_item($item, $file)
    {
        $items_data = collect($item)->except('properties', 'file');
        $items_data = collect($items_data)->toArray();
        $items_data['file'] = $file;
        $items_data['price'] = services::query()->find($items_data['service_id'])->price;

        return $items_data;
    }

    public function create_item_property($property, $order_item)
    {
        $property_item = properties::query()->find($property['property_id']);

        $property_obj = orders_items_properties::query()->create([
            'order_item_id' => $order_item->id,
            'property_id' => $property['property_id'],
            'price' => $property_item->price,
        ]);

        return $property_obj;
    }

    public function merge_files($files, $info)
    {
        foreach ($files as $file) {
            if ($file) {
                $existingPdfPath = public_path('orders_files/'.$file);

                // Your custom HTML content for the first page
                $newPageHtml = view('invoice', [
                    'order' => $info,
                ])->render();

                AddFirstPageToPdfAction::addFirstPageToPdf($existingPdfPath, $newPageHtml, $file);
            }
        }
    }

    public function continue_payment()
    {
        if ($this->cart_exists) {
            // update money
            $updated_payment = payments::query()->where('paymentable_id', '=', $this->order->id)->first();
            $this->total_price_order += $updated_payment->money;
        }
        PaymentModalSave::make($this->order->id, 'orders', $this->total_price_order, $this->payment['type'] ?? 'wallet', $updated_payment->id ?? null);
        DB::commit();
        if ($this->payment['type'] == 'wallet') {
            AddToWalletHistoryAction::save($this->total_price_order, 'min', 'order', auth()->id());
        }
        $this->load_relations();

        $this->merge_files($this->files_uploaded, $this->order);

        return Messages::success(__('messages.saved_successfully'), OrderResource::make($this->order));

    }
}
