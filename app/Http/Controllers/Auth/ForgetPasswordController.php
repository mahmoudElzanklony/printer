<?php

namespace App\Http\Controllers\Auth;

use App\Actions\DefaultInfoWithUser;
use App\Http\Controllers\Controller;
use App\Http\patterns\strategy\Messages\MessagesInterface;
use App\Http\Requests\messageFormRequest;
use App\Http\Requests\newPasswordFormRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Messages;

class ForgetPasswordController extends Controller
{
    //
    private $messageObj;

    public function __construct(MessagesInterface $obj)
    {
        $this->messageObj = $obj;
    }

    public function index(messageFormRequest $request)
    {
        $data = $request->validated();

        $user = $this->get_user($data);
        // in case user not found with this email or this phone
        if (isset($user->original['errors'])) {
            return $user;
        }
        $this->update_otp($user);
        $data['user'] = $user;
        $data['message'] = __('keywords.your_otp').' '.$user->otp_secret;
        // in case you send by email send title of your message
        if (request()->filled('email')) {
            $data['title'] = __('keywords.recovery_password');
        }
        $this->messageObj->send($data);

        return Messages::success(__('messages.operation_done_successfully'));

    }

    public function get_user($data)
    {
        if (array_key_exists('email', $data)) {
            $user = User::query()->where('email', '=', $data['email'])->firstOrFailWithCustomError(__('errors.not_found_user_with_this_email'));
        } elseif (array_key_exists('phone', $data)) {
            $user = User::query()->where('phone', '=', $data['phone'])
                ->where('otp_secret', '=', $data['otp_secret'])
                ->firstOrFailWithCustomError(__('errors.phone_or_otp_error'));
        }

        return $user;
    }

    public function update_otp($user)
    {
        $user->otp_secret = rand(1000, 9999);
        $user->save();
    }

    public function new_password(newPasswordFormRequest $request)
    {
        $data = $request->validated();
        if (request()->anyFilled('email', 'phone')) {
            $user = $this->get_user($request->validated());
            $user->password = request('password');
            $user->save();
            $user['token'] = $user->createToken($data['email'] ?? $data['phone'])->plainTextToken;
            array_merge($user->toArray(), DefaultInfoWithUser::execute($user)->toArray());

            return Messages::success(__('messages.saved_successfully'), UserResource::make($user));
        } else {
            return Messages::error('error at phone or email');
        }
    }
}
