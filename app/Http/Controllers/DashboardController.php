<?php

namespace App\Http\Controllers;

use App\Actions\OrdersWithAllDataAction;
use App\Filters\EndDateFilter;
use App\Filters\orders\StatusOrderFilter;
use App\Filters\StartDateFilter;
use App\Filters\users\UserNameFilter;
use App\Filters\users\WalletFilter;
use App\Http\Enum\SendingNotificationEnum;
use App\Http\patterns\strategy\Messages\MessagesInterface;
use App\Http\Requests\controlWalletFormRequest;
use App\Http\Requests\notificationsScheduleFormRequest;
use App\Http\Requests\taxFormRequest;
use App\Http\Resources\UserResource;
use App\Models\notifications_data_schedule;
use App\Models\notifications_data_schedule_users;
use App\Models\taxes;
use App\Models\User;
use App\Services\Messages;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //

    public function users()
    {
        $data = User::query()->orderBy('id','DESC');
        $output  = app(Pipeline::class)
            ->send($data)
            ->through([
                StartDateFilter::class,
                EndDateFilter::class,
                WalletFilter::class,
                UserNameFilter::class
            ])
            ->thenReturn()
            ->paginate(10);
        return UserResource::collection($output);
    }

    public function add_money_to_wallet(controlWalletFormRequest $request)
    {
        $data = $request->validated();
        $user = User::query()->find($data['user_id']);
        // add wallet to user
        $user->update([
            'wallet'=>$user->wallet + $data['money']
        ]);
        return Messages::success(__('messages.operation_done_successfully'));
    }

    public function update_tax(taxFormRequest $request)
    {
        $data = $request->validated();

        taxes::query()->updateOrCreate($data,$data);
        //
        return Messages::success(__('messages.operation_done_successfully'));
    }

    public function create_notification_content(notificationsScheduleFormRequest $request , MessagesInterface $messageObj)
    {
        $data = $request->validated();
        DB::beginTransaction();
        // create content first
        $noti = notifications_data_schedule::query()->updateOrCreate([
            'id' => request('id') ?? null
        ], [
            'content' => $data['content']
        ]);
        // send to users
        foreach($data['users'] as $user){
            notifications_data_schedule_users::query()->create([
                'user_id'=>$user,
                'notification_data_schedule_id'=>$noti->id,
            ]);
            // send notification
            $data_info = [
              'message'=>$data['content'],
              'user'=>User::query()->find($user)
            ];
            if($data['sending_type'] == SendingNotificationEnum::email){
                $data['title'] = 'اشعار من الادارة';
            }
            $messageObj->send($data_info);
        }
        DB::commit();

        return Messages::success(__('messages.operation_done_successfully'));
    }
}
