<!DOCTYPE html>
<html>
    <head>
        <title>Welcome {{$user->name}}</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>

        <div>
            <img width="50" src="{{$user->profile_image_url}}" alt="" />
        </div>
        <h2>Welcome {{$user->name}}</h2>
        <form method="post" action="{{route('set_favorite_streamer')}}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            @if(strlen($user->fav_streamer))
            <p>
                Your favorite streamer is 
                <a href="{{route('view_favorite_streamer')}}">
                    <img style="display: inline-block;" width="30" src="{{$user->fav_streamer_image_url}}" alt="" />
                </a>
                <a href="{{route('view_favorite_streamer')}}">
                    {{$user->fav_streamer}} [{{$status}}]
                </a>
            </p>
            <p>Update your favorite streamer:  <input type="text" name="fav_streamer" value="{{$user->fav_streamer}}" /></p>
            @else
            <p>Set your favorite streamer  <input type="text" name="fav_streamer" value="{{$user->fav_streamer}}" /></p>
            @endif
            <p><input type="submit" value="Set" /></p>
        </form>

        <!--<pre>{{ print_r($subs,true) }}</pre>-->
        <p>
            <a href="{{route('logout')}}">Logout</a>
        </p>
    </body>
</html>
