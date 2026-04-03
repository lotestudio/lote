<?php

namespace App\Service;

use App\Models\GoogleToken;
use Google\Client;
use Google\Service\Drive;
use RuntimeException;

class GoogleDrive
{
    protected Drive $service;

    public function __construct()
    {
        $client = self::makeClient();
        $token = GoogleToken::query()->latest('id')->first();

        if (! $token || empty($token->access_token)) {
            throw new RuntimeException('Липсва Google token. Първо направи OAuth login.');
        }

        $accessToken = [
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token,
            'expires_at' => $token->expires_at?->timestamp,
        ];

        $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            if (empty($token->refresh_token)) {
                throw new RuntimeException('Липсва refresh token. Нужно е ново OAuth влизане.');
            }

            $newToken = $client->fetchAccessTokenWithRefreshToken($token->refresh_token);

            if (isset($newToken['error'])) {
                throw new RuntimeException(
                    'Неуспешен refresh на token: '.($newToken['error_description'] ?? $newToken['error'])
                );
            }

            $token->access_token = $newToken['access_token'] ?? $token->access_token;

            if (! empty($newToken['refresh_token'])) {
                $token->refresh_token = $newToken['refresh_token'];
            }

            if (! empty($newToken['expires_in'])) {
                $token->expires_at = now()->addSeconds((int) $newToken['expires_in']);
            }

            if (! empty($newToken['token_type'])) {
                $token->token_type = $newToken['token_type'];
            }

            if (! empty($newToken['scope'])) {
                $token->scopes = is_string($newToken['scope'])
                    ? explode(' ', $newToken['scope'])
                    : $newToken['scope'];
            }

            $token->save();

            $client->setAccessToken([
                'access_token' => $token->access_token,
                'refresh_token' => $token->refresh_token,
                'expires_at' => $token->expires_at?->timestamp,
            ]);
        }

        $this->service = new Drive($client);
    }

    public function getService(): Drive
    {
        return $this->service;
    }

    public static function makeClient(): Client
    {
        $client = new Client;

        $client->setClientId(config('google.client_id'));
        $client->setClientSecret(config('google.client_secret'));
        $client->setRedirectUri(config('google.redirect_uri'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes(config('google.drive_scopes'));

        return $client;
    }
}
