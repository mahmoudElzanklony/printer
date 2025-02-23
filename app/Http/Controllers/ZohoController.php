<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ZohoController extends Controller
{
    //
    public function callback()
    {
        $code = request()->query('code');
        if (! $code) {
            return response()->json(['error' => 'Authorization code not found'], 400);
        }

        Log::info('Client ID: '.env('ZOHO_CLIENT_ID'));
        Log::info('Client Secret: '.env('ZOHO_CLIENT_SECRET'));
        Log::info('Redirect URI: '.env('ZOHO_REDIRECT_URI'));
        // Exchange the authorization code for an access token
        $client = new Client();
        $response = $client->post('https://accounts.zoho.com/oauth/v2/token', [
            'form_params' => [
                'code' => $code,
                'client_id' => env('ZOHO_CLIENT_ID'),
                'client_secret' => env('ZOHO_CLIENT_SECRET'),
                'redirect_uri' => env('ZOHO_REDIRECT_URI'),
                'grant_type' => 'authorization_code',
            ],
        ]);

        $tokens = json_decode($response->getBody(), true);
        dd($tokens);
        // Save the tokens to your database or .env file
        // Example: save the access token to .env
        file_put_contents(base_path('.env'), "\nZOHO_ACCESS_TOKEN=".$tokens['access_token'], FILE_APPEND);

        return response()->json(['message' => 'Authorization successful', 'tokens' => $tokens]);
    }
}
