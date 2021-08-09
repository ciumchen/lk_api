<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>来客-手动清空用户lk</title>
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
<style>
    #div1{text-align:center;margin: 100px auto;}
    table{margin: 50px auto;}
    table,tr,td{border:1px solid black;}
    .td1{width: 130px;}
    td{height: 70px;}
    select{border: #cccccc 1px solid !important;}
</style>
<body>


<div class="mui-content" id="content">
    <div>
        <div id="div1">
            <form action="jsQkSetUserLk" method="get">
            <table width="600px"height="300px">
                <comption style="font-size: 30px;font-weight:bold">手动清空用户积分和Lk</comption>
                <tr>
                    <td>当前lk单价：</td>
                    <td>{{ $data['lkdj'] }}</td>
                </tr>
                <tr>
                    <td class="td1">用户数据</td>
                    <td style="text-align: left">

                        <div>
                            <br/>用户是uid2和6当前积分和lk统计数据 <br><br/>
                            @foreach($data['userInfo'] as $key_2 => $arr)
                                @foreach($arr as $key => $value)
                                    {{$key}} === {{$value}}
                                    <br>
                                @endforeach<br/>
                            @endforeach
                        </div>

{{--                        <select name="uid">--}}
{{--                            <option value="2">用户uid=2</option>--}}
{{--                            <option value="6">用户uid=6</option>--}}
{{--                        </select>--}}
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding: 20px"><input type="submit" name="submit" style="width: 220px;height: 40px;background: #7db2ec !important;" value="一键清空"></td>
                </tr>

            </table>
            </form>
        </div>
        <div id="div1">
            <form action="setUserLkdj" method="get">
            <table width="600px"height="50px">
                <tr>
                    <td colspan="2" style="padding: 20px"><input type="submit" name="submit" style="width: 220px;height: 40px;background: #7db2ec !important;" value="一键设置lk单价为0"></td>
                </tr>

            </table>
            </form>
        </div>
    </div>
<div>

</div>
</div>

</body>

<script type="text/javascript">

</script>
</html>
