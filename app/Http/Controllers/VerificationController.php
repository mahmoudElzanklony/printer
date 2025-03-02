<?php

namespace App\Http\Controllers;

use App\Actions\DefaultInfoWithUser;
use App\Actions\LoginByPhoneOrEmailAction;
use App\Http\patterns\ChainResponsabilites\verification\UpdateVerifiedTimeRes;
use App\Http\patterns\ChainResponsabilites\verification\VerifyOtpRes;
use App\Http\patterns\strategy\Register\VerificationInterface;
use App\Http\Requests\verifyFormRequest;
use App\Http\Resources\UserResource;
use App\Services\Messages;

class VerificationController extends Controller
{
    public function __construct(protected VerificationInterface $verification){}
    public function send_verification()
    {

        if(request()->filled('verifiable')){
            $code = $this->verification->verify(request()->verifiable,request('continue_process') ?? false);
            return Messages::success(__('messages.sending_verification'), ['code' => $code]);
        }
        return Messages::error('verifiable should be sent to request');
    }

    public function verify(verifyFormRequest $request)
    {

        $check_verification = new VerifyOtpRes();
        $update_time_verification = new UpdateVerifiedTimeRes();
        $check_verification->setNext($update_time_verification);
        // start chain process
        $check_verification->handle($request->validated());
        $user = LoginByPhoneOrEmailAction::login($request->email ?? $request->phone);
        array_merge($user->toArray(),DefaultInfoWithUser::execute($user)->toArray());

        return Messages::success(__('messages.activation_done'),UserResource::make($user));
    }
}
