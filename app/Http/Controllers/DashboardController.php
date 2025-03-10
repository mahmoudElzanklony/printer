<?php

namespace App\Http\Controllers;

use App\Actions\AddToWalletHistoryAction;
use App\Actions\VerifyAccess;
use App\Filters\EndDateFilter;
use App\Filters\StartDateFilter;
use App\Filters\users\UserNameFilter;
use App\Filters\users\WalletFilter;
use App\Http\Enum\OrderStatuesEnum;
use App\Http\Enum\SendingNotificationEnum;
use App\Http\patterns\strategy\Messages\MessagesInterface;
use App\Http\Requests\controlWalletFormRequest;
use App\Http\Requests\notificationsScheduleFormRequest;
use App\Http\Requests\taxFormRequest;
use App\Http\Requests\userFormRequest;
use App\Http\Resources\UserResource;
use App\Models\notifications_data_schedule;
use App\Models\notifications_data_schedule_users;
use App\Models\orders;
use App\Models\payments;
use App\Models\roles;
use App\Models\taxes;
use App\Models\User;
use App\Services\Messages;
use Carbon\Carbon;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //

    public function users()
    {
        VerifyAccess::execute('pi pi-users|/users|read');
        $data = User::query()->orderBy('id', 'DESC');
        $output = app(Pipeline::class)
            ->send($data)
            ->through([
                StartDateFilter::class,
                EndDateFilter::class,
                WalletFilter::class,
                UserNameFilter::class,
            ])
            ->thenReturn()
            ->paginate(request('limit') ?? 10);

        return UserResource::collection($output);
    }

    public function orders()
    {
        $data = [
            'pending' => orders::query()->whereHas('last_status', fn ($e) => $e->where('status', OrderStatuesEnum::pending))->count(),
            'delivery' => orders::query()->whereHas('last_status', fn ($e) => $e->where('status', OrderStatuesEnum::delivery))->count(),
            'cancelled' => orders::query()->whereHas('last_status', fn ($e) => $e->where('status', OrderStatuesEnum::cancelled))->count(),
            'review' => orders::query()->whereHas('last_status', fn ($e) => $e->where('status', OrderStatuesEnum::review))->count(),
            'completed' => orders::query()->whereHas('last_status', fn ($e) => $e->where('status', OrderStatuesEnum::completed))->count(),
            'printing' => orders::query()->whereHas('last_status', fn ($e) => $e->where('status', OrderStatuesEnum::printing))->count(),
        ];

        return Messages::success('', $data);
    }

    public function orders_summary()
    {
        $output = [];
        for ($i = 0; $i < 12; $i++) {
            $month = Carbon::parse((request('year') ?? date('Y')).'-'.($i + 1).'-01')->firstOfMonth()->addDay();
            $value = payments::query()->where('paymentable_type', '=', 'App\Models\orders')
                ->whereMonth('created_at', intval($i + 1))
                //  ->whereYear('created_at',request('year') ?? date('Y'))
                ->sum('money');
            $output[$i] = ['placeholder' => $month, 'value' => floatval($value)];
        }

        return $output;
    }

    public function money_wallet($data, $type = 'plus')
    {
        VerifyAccess::execute('pi pi-users|/users|update');
        $user = User::query()->find($data['user_id']);
        // add wallet to user
        if ($type == 'plus') {
            $user->update([
                'wallet' => $user->wallet + $data['money'],
            ]);
        } else {
            $user->update([
                'wallet' => $user->wallet - $data['money'],
            ]);
        }
        AddToWalletHistoryAction::save($data['money'], $type, 'admin', $data['user_id']);

        return Messages::success(__('messages.operation_done_successfully'));
    }

    public function take_money_from_wallet(controlWalletFormRequest $request)
    {

        $data = $request->validated();

        return $this->money_wallet($data, 'min');
    }

    public function add_money_to_wallet(controlWalletFormRequest $request)
    {
        $data = $request->validated();

        return $this->money_wallet($data, 'plus');
    }

    public function update_tax(taxFormRequest $request)
    {
        VerifyAccess::execute('pi pi-money-bill|/tax-value|update');
        $data = $request->validated();

        taxes::query()->update($data, $data);

        //
        return Messages::success(__('messages.operation_done_successfully'));
    }

    public function get_tax()
    {

        VerifyAccess::execute('pi pi-money-bill|/tax-value|read');

        $data = taxes::query()->first();

        //
        return $data;
    }

    public function create_notification_content(notificationsScheduleFormRequest $request, MessagesInterface $messageObj)
    {
        $data = $request->validated();
        DB::beginTransaction();
        // create content first
        $noti = notifications_data_schedule::query()->updateOrCreate([
            'id' => request('id') ?? null,
        ], [
            'content' => $data['content'],
        ]);
        // send to users
        foreach ($data['users'] as $user) {
            notifications_data_schedule_users::query()->create([
                'user_id' => $user,
                'notification_data_schedule_id' => $noti->id,
            ]);
            // send notification
            $data_info = [
                'message' => $data['content'],
                'user' => User::query()->find($user),
            ];
            if ($data['sending_type'] == SendingNotificationEnum::email->value) {
                $data_info['title'] = 'اشعار من الادارة';
            }
            $messageObj->send($data_info);
        }
        DB::commit();

        return Messages::success(__('messages.operation_done_successfully'));
    }

    public function add_employee(userFormRequest $request)
    {
        if (auth()->user()->roleName() == 'admin') {
            DB::beginTransaction();
            $data = $request->validated();
            $data['otp_secret'] = rand(1000, 9999);
            $user = User::query()->create($data);
            $user->assignRole(roles::query()->find($data['role_id'])->name);

            $user->createToken($data['email'])->plainTextToken;
            DB::commit();

            return Messages::success(__('messages.operation_done_successfully'));
        }
        abort(Messages::error('You do not have permission to do this action.'));
    }
}
