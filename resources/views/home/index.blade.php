<!DOCTYPE html>
<html>
    <head>
        <title>Login Using Twitch</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style type="text/css">
            .btn{
                display: inline-block;
                border: 1px solid #DDD;
                padding: 10px;
                background: #EEEEEE;
                color: #333333;
                font-family: arial;
                text-decoration: none;
            }
            .btn:hover{
                background: #DDD;
            }
        </style>
    </head>
    <body>
        <div style="text-align: center;">
            <a class="btn" href="{{route('login_with_twitch')}}">Login with Twitch</a>
        </div>
    </body>
</html>
