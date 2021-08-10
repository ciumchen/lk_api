<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<a href="javascript:window.opener=null;window.open('','_self');window.close();">关闭</a>
@if($auth_status)
    这是一个{{dump($auth_status)}}
@else
    这TM是啥？{{ dump($auth_status)}}
@endif
</body>
</html>