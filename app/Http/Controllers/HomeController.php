<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Libraries\TwitchLib;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HomeController extends Controller {

    public function index(Request $request) {
        if (Auth::check()) {
            $user = Auth::user();
            $twitchLib = new TwitchLib;
            $data['user'] = $request->user();
            $data['subs'] = $twitchLib->getWebhookSubscriptions();
            $streams = $twitchLib->getStreams($user->fav_streamer_id);
            if (count($streams['data']) and $streams['data'][0] and $streams['data']['0']['type'] == 'live') {
                $data['status'] = 'LIVE';
            } else {
                $data['status'] = 'OFFLINE';
            }
            return view('home.logged_in', $data);
        } else {
            return view('home.index');
        }
    }

    public function login_with_twitch(Request $request) {
        $twitchLib = new TwitchLib;
        $url = $twitchLib->getAuthorizationUrl();
        return redirect($url);
    }

    public function twitch_recirect(Request $request) {
        $code = $request->query('code');
        $state = $request->query('state');

        $checkState = session()->get('state');
        if (strlen($code) and $state == $checkState) {
            try {
                $twitchLib = new TwitchLib;
                $response = $twitchLib->getAceessToken($code);
                $accessToken = $response['access_token'];
                $expiresIn = $response['expires_in'];
                $refreshToken = $response['refresh_token'];

                $userInfo = $twitchLib->getUserInfo($accessToken);
                if (count($userInfo['data'])) {
                    $userInfo = $userInfo['data'][0];
                    $user = User::where('twitch_id', $userInfo['id'])->where('email', $userInfo['email'])->first();
                    if (!$user) {
                        $user = new User;
                    }
                    $user->twitch_id = $userInfo['id'];
                    $user->twitch_login = $userInfo['login'];
                    $user->name = $userInfo['display_name'];
                    $user->profile_image_url = $userInfo['profile_image_url'];
                    $user->email = $userInfo['email'];
                    $user->access_token = $accessToken;
                    $user->expires_at = time() + $expiresIn;
                    $user->refresh_token = $refreshToken;
                    $user->save();
                    Auth::login($user);
                    return redirect()->route('home');
                } else {
                    echo "User not found";
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        exit;
    }

    public function logout() {
        Auth::logout();
        session()->flush();
        return redirect()->route('home');
    }

    public function set_favorite_streamer(Request $request) {
        $fav_streamer = $request->fav_streamer;
        if (strlen($fav_streamer)) {
            $twitchLib = new TwitchLib;
            $streamerInfo = $twitchLib->getUserInfoFromLogin($fav_streamer);
            if (count($streamerInfo['data'])) {
                $user = Auth::user();
                $user->fav_streamer = $fav_streamer;
                $user->fav_streamer_id = $streamerInfo['data'][0]['id'];
                $user->fav_streamer_image_url = $streamerInfo['data'][0]['profile_image_url'];
                $user->save();
                Auth::login($user);

                $twitchLib->setWebhooks($user->id, $user->fav_streamer_id);
            }
        }
        return redirect()->route('home');
    }

    public function view_favorite_streamer(Request $request) {
        $data['user'] = $request->user();
        return view('home.view_favorite_streamer', $data);
    }

    public function twitch_webhook(Request $request, $type = null) {
        file_put_contents('test.txt', file_get_contents('test.txt') . "\n\n" . date('Y-m-d H:i:s') . "::" . $_SERVER['REQUEST_METHOD'] . "::" . $type . "::" . print_r($request->all(), true));
        if ($request->input('hub_challenge')) {
//            file_put_contents('test.txt', file_get_contents('test.txt') . "\n\n" . date('Y-m-d H:i:s') . "::" . $request->input('hub_challenge'));
            return response($request->input('hub_challenge'));
        } else {
            return response('OK');
        }
        exit;
    }

}
