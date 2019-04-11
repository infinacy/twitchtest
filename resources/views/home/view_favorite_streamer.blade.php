<!DOCTYPE html>
<html>
    <head>
        <title>Welcome {{$user->name}}</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src= "https://player.twitch.tv/js/embed/v1.js"></script>
    </head>
    <body>
        <div id="video_frame" style="float: left;"></div>
        <div id="chat_frame" style="float: left;">
            <iframe frameborder="0"
                    scrolling="no"
                    id="chat_embed"
                    src="https://www.twitch.tv/embed/{{strtolower($user->fav_streamer)}}/chat"
                    height="500"
                    width="350">
            </iframe>
        </div>
        <br style="clear: both;" />
        <p>
            <a href="{{route('home')}}">Home</a> | <a href="{{route('logout')}}">Logout</a>
        </p>
        <script type="text/javascript">
            var options = {
                width: 640,
                height: 500,
                channel: "{{$user->fav_streamer}}"
            };
            var player = new Twitch.Player("video_frame", options);
            player.setVolume(0);
        </script>
    </body>
</html>
