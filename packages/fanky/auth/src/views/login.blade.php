<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js"> <!--<![endif]-->
<head>
    <title>{{ $title ?? 'Авторизация' }}</title>
    <meta charset="utf-8">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="Fanky.ru">
    <meta name="format-detection" content="telephone=no">
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=600">
    <meta name="cmsmagazine" content="18db2cabdd3bf9ea4cbca88401295164">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <style type="text/css">
        body{
            font-family:Arial, "Helvetica Neue", sans-serif;
            font-size:20px;
            line-height:1.4;
            color:#fff;
            background:#25556d;
            position:relative;
        }
        .form_login{
            padding:20px 20px 15px;
            border-radius:10px;
            background:#1a455b;

        }
        .form_login label{
            display:block;
            margin-bottom:10px;
        }
        .form_login span{
            display:block;
            font-weight:bold;
        }
        .central{
            right: 50%;
            -webkit-transform: translate(50%,30%);
            transform: translate(50%,30%);
            position: absolute;
        }
        input[type=text],input[type=password]{
            /*width:250px;*/
            padding:7px 20px;
            border-radius:10px;
            border:1px solid #bbb;
            font-size:20px;
            background:-webkit-linear-gradient(#fff, #e7e7e7);
            background:linear-gradient(#fff, #e7e7e7);
        }
        input[type=submit]{
            margin-top:10px;
            padding:7px 20px;
            border-radius:10px;
            font-size:20px;
            cursor:pointer;
            border:1px solid #d5d5d5;
            background:-webkit-linear-gradient(#fff, #e7e7e7);
            background:linear-gradient(#fff, #e7e7e7);
        }
        .logo{
            text-align:center;
        }
        .logo img{
            border-radius:5px;
        }
        .error{
            color:#ff0000;
            font-size:12px;
        }
    </style>
</head>
<body>



<div class="central">
    <form action="" class="form_login" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        @if(Request::has('redirect'))
            <input type="hidden" name="redirect" value="{{ Request::get('redirect') }}">
        @endif
        <label><span>Логин</span><input type="text" name="username" value=""></label>
        <label><span>Пароль</span><input type="password" name="password" value=""></label>
        <div class="error">{{ old('error') }}</div>
        <label><input type="checkbox" name="remember" value="1"> запомнить меня</label>
        <label><input type="submit" name="" value="Войти"></label>

    </form>
</div>




</body>
</html>