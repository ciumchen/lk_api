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
<style>
    #div1{text-align:center;margin: 100px auto;}
    table{margin: 50px auto;}
    table,tr,td{border:1px solid black;}
    .td1{width: 130px;}
    td{height: 70px;}
</style>
<body>


<div class="mui-content" id="content">
    <div>
        <div id="div1">
            <form action="kcUserShopJf" method="get">
            <table width="600px"height="300px">
                <comption style="font-size: 30px;font-weight:bold">手动扣除用户消费者积分</comption>
                <tr>
                    <td class="td1">用户uid</td>
                    <td><input type="text" name="uid"></td>
                </tr>

                <tr>
                    <td class="td1">积分类别</td>
                    <td>
                        <label><input name="role" type="radio" value="1" checked/>消费者积分</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <label><input name="role" type="radio" value="2" />商家积分</label>
                    </td>
                </tr>
                <tr>
                    <td class="td1">扣除数量</td>
                    <td><input type="text" name="num" placeholder="例如：输入1表示扣除1积分，-1表示添加1积分"></td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 20px"><input type="submit" name="submit" style="width: 220px;height: 40px;background: #7db2ec !important;"></td>
                </tr>
{{--                <tr>--}}
{{--                    <td colspan="2" style="text-align: left;padding: 5px">--}}
{{--                        <span>扣除用户积分接口使用说明：<br/>--}}
{{--                            积分类别--}}
{{--                            role=1表示删除消费者积分，role=2表示删除商家积分<br/>--}}
{{--                            num=要删除的积分<br/><br/>--}}
{{--                        </span>--}}
{{--                    </td>--}}
{{--                </tr>--}}
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
