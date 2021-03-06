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
    #div1{text-align:center;margin: 20px 0 50px 0;}
    #div2{text-align:center;}
    table{margin: 10px auto;}
    table,tr,td{border:1px solid black;}
    .td1{width: 130px;}
    td{height: 70px;}
    select{border: #cccccc 1px solid !important;}
    .td2{text-align: left;}
    .span1{color: red;font-weight: bold;}
</style>
<body>


<div class="mui-content" id="content">
    <div>
        <div id="div1">
            <form action="jsQkSetOneUserLk" method="get">
            <table width="600px"height="300px">
                <comption style="font-size: 30px;font-weight:bold">手动清空用户积分和Lk</comption>
                <tr>
                    <td>当前lk单价：</td>
                    <td>{{ $data['lkdj'] }}</td>
                </tr>
                <tr>
                    <td class="td1">清空用户UID</td>
                    <td style="text-align: left">
                        <select name="uid">
                            <option value="2">用户uid=2</option>
                            <option value="6">用户uid=6</option>
                            <option value="15873">用户uid=15873</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><span class="span1">操作步骤：</span></td>
                    <td class="td2"><span class="span1">
                        步骤一：先修改消费者lk单价为0，<br>
                        步骤二：晚上0点分红完成后再清空用户的积分和lk<br>
                        步骤三：将消费者lk单价修改回来</span>

                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding: 20px">
                        <button onclick="javascript:return warning()" style="width: 220px;height: 40px;background: #7db2ec !important;color:#fff">一键清空</button>
{{--                        <input type="submit" name="submit" style="width: 220px;height: 40px;background: #7db2ec !important;" value="一键清空"></td>--}}
                </tr>

            </table>
            </form>
        </div>
        <div id="div2">
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

<script>
    function warning(){
        if(confirm('确定要清空用户积分和lk吗？')==true){
            return true;

        }else{
            return false;

        }

    }

</script>
</html>
