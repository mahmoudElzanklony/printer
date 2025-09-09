<?php

namespace App\Http\Controllers;

use App\Actions\AddToWalletHistoryAction;
use App\Http\Enum\TransactionStatuesEnum;
use App\Http\Resources\WalletHistoryResource;
use App\Models\WalletRechargeTransaction;
use App\Services\External\Payments\HyperpayClient;
use App\Services\Messages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletRechargeController extends Controller
{
    public function __construct(protected HyperpayClient $client)
    {

    }

    public function initCheckoutTransaction(Request $request)
    {
        DB::beginTransaction();
        $request->validate(['money' => 'required|numeric|min:1']);
        $amount = number_format((float) \request('money'), 2, '.', '');
        $user = auth()->user();
        $email = $user?->email ?? '';
        $transaction = WalletRechargeTransaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'status' => TransactionStatuesEnum::pending,
            'provider' => 'hyperpay',
        ]);

        $checkout = $this->client->createCheckout([
            'amount' => $amount,
            'currency' => config('services.payments.hyperpay.currency', 'SAR'),
            'paymentType' => 'DB',
            'merchantTransactionId' => $transaction->id,
            'merchantInvoiceId' => $transaction->id,
            'extra' => [
                'customer.email' => $email,
                'customer.givenName' => $user?->username,
                'testMode' => 'EXTERNAL',
                'customParameters' => [   // TODO:: Remove These In production
                    '3DS2_enrolled' => true
                ],
                'integrity' => true
            ],
        ]);
        if ($checkout === false || empty($checkout['id'])) {
            return Messages::error(__('errors.payment_failed'));
        }
        $transaction->update(['checkout_id' => $checkout['id']]);
        DB::commit();
        return Messages::success(__('messages.saved_successfully'), [
            'provider' => 'hyperpay',
            'checkout_id' => $checkout['id'],
            'amount' => $amount,
            'currency' => config('services.payments.hyperpay.currency', 'SAR'),
            'transaction_id' => $transaction->id,
        ]);
    }

    public function validate_payment(Request $request)
    {
        DB::beginTransaction();
        $request->validate(['resource_path' => 'required|string']);
        $resourcePath = request('resource_path');
        $checkoutId = Str::between($resourcePath, 'checkouts/', '/payment');
        $transaction = WalletRechargeTransaction::where('checkout_id', $checkoutId)
            ->where('user_id', auth()->id())
            ->where('status', TransactionStatuesEnum::pending)
            ->firstOrFailWithCustomError(__('errors.not_found_data'));
        $paymentStatus = $this->client->getPaymentResultByResourcePath($resourcePath);
        if ($paymentStatus === false) {
            $transaction->update(['status' => TransactionStatuesEnum::failed]);
            DB::Commit();
            return Messages::error(__('errors.payment_failed'));
        } else {
            $user = auth()->user();
            $user->update([
                'wallet' => $user->wallet + $transaction->amount,
            ]);
            $transaction->update(['status' => TransactionStatuesEnum::completed]);
            $result = AddToWalletHistoryAction::save($transaction->amount, 'plus', 'charge', auth()->id());
            DB::commit();
            return Messages::success(__('messages.saved_successfully'), WalletHistoryResource::make($result));
        }

    }
}
