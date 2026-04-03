<?php

namespace App\Http\Controllers;

use App\Models\GoogleToken;
use App\Service\GoogleDrive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoogleDriveController extends Controller
{
    public function tmp()
    {

        $service = new GoogleDrive()->getService();

        $files = $service->files->listFiles([
            'pageSize' => 10,
            'fields' => 'files(id, name)',
        ]);

        foreach ($files->getFiles() as $file) {
            echo $file->getName().PHP_EOL;
        }

    }

    public function redirect()
    {
        $client = GoogleDrive::makeClient();

        return redirect()->away($client->createAuthUrl());
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            abort(400, 'Google OAuth error: '.$request->string('error'));
        }

        $code = $request->string('code')->toString();

        if ($code === '') {
            abort(400, 'Missing authorization code.');
        }

        $client = GoogleDrive::makeClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            abort(400, 'Token exchange failed: '.($token['error_description'] ?? $token['error']));
        }

        GoogleToken::query()->updateOrCreate(
            ['id' => 1],
            [
                'access_token' => $token['access_token'] ?? null,
                'refresh_token' => $token['refresh_token'] ?? null,
                'expires_at' => ! empty($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
                'scopes' => isset($token['scope'])
                    ? explode(' ', is_string($token['scope']) ? $token['scope'] : '')
                    : null,
                'token_type' => $token['token_type'] ?? null,
            ]
        );

        return redirect()->route('dashboard')->with('success', 'Google Drive connected successfully. Token Saved');
    }
}
