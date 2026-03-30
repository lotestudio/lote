<?php

namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GoogleDriveController extends Controller
{
    public function redirect()
    {
        $client = $this->makeClient();

        return redirect()->away($client->createAuthUrl());
    }

    public function callback(Request $request): Renderable
    {
        if ($request->filled('error')) {
            abort(400, 'Google OAuth error: '.$request->string('error'));
        }

        $code = $request->string('code')->toString();

        if ($code === '') {
            abort(400, 'Missing authorization code.');
        }

        $client = $this->makeClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            abort(400, 'Token exchange failed: '.($token['error_description'] ?? $token['error']));
        }

        Cache::put('google_drive_token', $token, now()->addDay());

        return view('google.redirect', ['token' => $token]);
    }

    private function makeClient(): GoogleClient
    {
        $client = new GoogleClient;

        $client->setClientId(config('google.client_id'));
        $client->setClientSecret(config('google.client_secret'));
        $client->setRedirectUri(config('google.redirect_uri'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes(config('google.drive_scopes'));

        return $client;
    }
}
