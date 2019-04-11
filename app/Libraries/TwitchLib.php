<?php

namespace App\Libraries;

use GuzzleHttp\Client AS GuzzleClient;

class TwitchLib {

    private $clientId;
    private $clientSecret;
    private $redirectUrl;
    private $guzzleClient;
    private $subscriptionTimeout;

    function __construct() {
        $this->clientId = '72aze762lg9ai9nkp35bma0jmeskoj';
        $this->clientSecret = 'bq0qqk8s6m9wbckqb3yszbqnmu6lzp';
        $this->redirectUrl = route('twitch_recirect');
        $this->guzzleClient = new GuzzleClient();
        $this->subscriptionTimeout = 360;
    }

    public function getAuthorizationUrl() {
        $state = \Illuminate\Support\Str::random();
        session()->put('state', $state);
        $url = 'https://id.twitch.tv/oauth2/authorize?';
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => 'user_read user:read:email',
            'state' => $state,
            'response_type' => 'code',
//            'force_verify' => 'true'
        ];
        $url = $url . http_build_query($params);
        return $url;
    }

    public function getAceessToken($code) {
        $url = 'https://id.twitch.tv/oauth2/token';
        $postData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUrl,
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];

        $response = $this->postApiData($url, $postData);
        return $response;
    }

    public function getUserInfo($accessToken) {
        $url = 'https://api.twitch.tv/helix/users';
        $params = [
            'headers' => [
                'Client-ID' => $this->clientId,
                'Authorization' => 'Bearer ' . $accessToken
            ]
        ];
        $response = $this->getApiData($url, [], $params);
        return $response;
    }

    public function getUserInfoFromLogin($userLogin) {
        $url = 'https://api.twitch.tv/helix/users';
        $params = [
            'headers' => [
                'Client-ID' => $this->clientId
            ]
        ];
        $fields = ['login' => $userLogin];
        $response = $this->getApiData($url, $fields, $params);
        return $response;
    }

    public function getStreams($streamerId) {
        $url = 'https://api.twitch.tv/helix/streams';
        $params = [
            'headers' => [
                'Client-ID' => $this->clientId
            ]
        ];
        $fields = ['user_id' => $streamerId];
        $response = $this->getApiData($url, $fields, $params);
        return $response;
    }

    public function getFollowers($streamerId) {
        $url = 'https://api.twitch.tv/helix/users/follows';
        $params = [
            'headers' => [
                'Client-ID' => $this->clientId
            ]
        ];
        $fields = ['first' => 1, 'to_id' => $streamerId];
        $response = $this->getApiData($url, $fields, $params);
        return $response;
    }

    public function setWebhooks($userId, $streamerId) {
        $this->setFollowWebHook($streamerId);
        $this->setStreamWebHook($streamerId);
    }

    private function setFollowWebHook($streamerId) {
        $url = 'https://api.twitch.tv/helix/webhooks/hub';
        $params = [
            'headers' => [
                'Client-ID' => $this->clientId
            ]
        ];
        $fields = [
            'hub.callback' => route('twitch_webhook', ['type' => 'followers']),
            'hub.mode' => 'subscribe',
            'hub.topic' => 'https://api.twitch.tv/helix/users/follows?first=1&to_id=' . $streamerId,
            'hub.lease_seconds' => $this->subscriptionTimeout,
            'hub.secret' => env('WEBHOOK_SECRET'),
        ];
        $response = $this->postApiData($url, $fields, $params);
    }

    private function setStreamWebHook($streamerId) {
        $url = 'https://api.twitch.tv/helix/webhooks/hub';
        $params = [
            'headers' => [
                'Client-ID' => $this->clientId
            ]
        ];
        $fields = [
            'hub.callback' => route('twitch_webhook', ['type' => 'streams']),
            'hub.mode' => 'subscribe',
            'hub.topic' => 'https://api.twitch.tv/helix/streams?user_id=' . $streamerId,
            'hub.lease_seconds' => $this->subscriptionTimeout,
            'hub.secret' => env('WEBHOOK_SECRET'),
        ];
        $response = $this->postApiData($url, $fields, $params);
    }

    public function getWebhookSubscriptions() {
        $appToken = $this->getAppToken();
        $url = 'https://api.twitch.tv/helix/webhooks/subscriptions';
        $params = [
            'headers' => [
                'Client-ID' => $this->clientId,
                'Authorization' => 'Bearer ' . $appToken
            ]
        ];
        $response = $this->getApiData($url, [], $params);
        return $response;
    }

    public function getAppToken() {
        $url = 'https://id.twitch.tv/oauth2/token';
        $postData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials'
        ];

        $response = $this->postApiData($url, $postData);
        return $response['access_token'];
    }

    private function getApiData($url, $getData, $params = []) {
        $defautParams = [
//            'headers' => ['Authorization' => 'Bearer: ' . $this->apiToken],
            'verify' => false
        ];
        $params = array_merge($defautParams, $params);
        if (count($getData)) {
            $url = $url . '?' . http_build_query($getData);
        }
        try {
            $result = $this->guzzleClient->request('GET', $url, $params)->getBody()->getContents();
            $result = json_decode($result, true);
            return $result;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    private function postApiData($url, $postData, $params = []) {
        $defautParams = [
//            'headers' => ['Authorization' => 'Bearer: ' . $this->apiToken],
            'verify' => false
        ];
        $params = array_merge($defautParams, $params);
        $params['form_params'] = $postData;
        try {
            $result = $this->guzzleClient->request('POST', $url, $params)->getBody()->getContents();
            $result = json_decode($result, true);
            return $result;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

}
