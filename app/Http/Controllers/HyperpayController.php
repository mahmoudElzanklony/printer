<?php

namespace App\Http\Controllers;

use App\Http\Enum\OrderStatuesEnum;
use App\Models\orders;
use App\Models\orders_tracking;
use App\Services\External\Payments\HyperpayClient;
use App\Services\Messages;
use Illuminate\Http\Request;

class HyperpayController extends Controller
{
    public function callback(Request $request)
    {
        $client = new HyperpayClient();

        $result = null;
        if ($request->filled('resourcePath')) {
            $result = $client->getPaymentResultByResourcePath($request->get('resourcePath'));
        } elseif ($request->filled('id')) {
            $result = $client->getPaymentResultById($request->get('id'));
        }

        if (!is_array($result)) {
            return Messages::error(__('errors.payment_failed'));
        }

        $resultCode = $result['result']['code'] ?? null;
        $orderId = (int) ($result['merchantTransactionId'] ?? null);

        if (!$orderId) {
            return Messages::error(__('errors.not_found_data'));
        }

        $order = orders::query()->with('payment')->find($orderId);
        if (!$order) {
            return Messages::error(__('errors.not_found_data'));
        }

        if (HyperpayClient::isSuccessful($resultCode)) {      // TO Review
            $order->update(['status' => OrderStatuesEnum::delivery]);
            orders_tracking::query()->create([
                'order_id' => $order->id,
                'status' => OrderStatuesEnum::pending,
            ]);

            return Messages::success(__('messages.saved_successfully'));
        }

        return Messages::error(__('errors.payment_failed'));
    }
}
