<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>来客-{{$title}}</title>
    <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1,viewport-fit=cover,maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link href="/css/mui.css" rel="stylesheet" />
    <link href="/css/app.css" rel="stylesheet" />
    <link href="/css/login.css" rel="stylesheet" />
    <style>
        .download-btn img{
            width: 100%;
        }
        .download-btn{
            width: 80%;
            margin: 1rem auto;
        }
    </style>
</head>
<body>


<div class="mui-content" id="content">
    <div >
        <img src="/images/download-bg.png" alt="" width="100%">
        @if($android_app)
        <!--<div class="download-btn"><a href="{{env('OSS_URL').$android_app}}" target="_blank" ><img src="/images/android_app.png" alt="" ></a></div>-->
        <div class="download-btn"><a href="https://static.catspawvideo.com{{$android_app}}" target="_blank" ><img src="/images/android_app.png" alt="" ></a></div>
        @endif

    </div>
</div>

</body>

<script type="text/javascript">

</script>
</html>
