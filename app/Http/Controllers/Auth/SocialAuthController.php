<?php

namespace App\Http\Controllers\Auth;

use App\Actions\DefaultInfoWithUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\SocialAuth;
use App\Http\Resources\UserResource;
use App\Models\social_accounts;
use App\Models\User;
use App\Services\Messages;
use Illuminate\Support\Facades\Http;

class SocialAuthController extends Controller
{
    public function socialLogin(SocialAuth $request)
    {

        $provider = $request['provider'];
        $accessToken = $request['access_token'];

        // Validate token with Google or Facebook API
        $socialUser = $this->verifySocialToken($provider, $accessToken);

        if (! $socialUser) {
            return response()->json(['error' => 'Invalid social media token'], 401);
        }

        // Check if user exists
        $account = social_accounts::where('provider', $provider)
            ->where('provider_id', $socialUser['id'])
            ->first();

        if ($account) {
            $user = $account->user;
        } else {
            // Check if user with same email exists
            $user = User::where('email', $socialUser['email'])->first();

            if (! $user) {
                $user = User::create([
                    'name' => $socialUser['name'],
                    'email' => $socialUser['email'],
                ]);
            }

            // Link Social Account
            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $socialUser['id'],
                'access_token' => $accessToken,
            ]);
        }

        // Generate API Token
        $user['token'] = $user->createToken($socialUser['email'])->plainTextToken;

        array_merge($user->toArray(), DefaultInfoWithUser::execute($user)->toArray());

        return Messages::success(__('messages.login_successfully'), UserResource::make($user));
    }

    private function verifySocialToken($provider, $accessToken)
    {
        if ($provider === 'google') {
            $response = Http::get("https://www.googleapis.com/oauth2/v1/userinfo?access_token={$accessToken}");
        } elseif ($provider === 'facebook') {
            $response = Http::get("https://graph.facebook.com/me?fields=id,name,email,picture&access_token={$accessToken}");
        } else {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $socialUser = $response->json();
        dd($socialUser);

        return [
            'id' => $socialUser['id'],
            'name' => $socialUser['name'],
            'email' => $socialUser['email'] ?? null,
            'avatar' => $socialUser['picture']['data']['url'] ?? null,
        ];
    }
}
