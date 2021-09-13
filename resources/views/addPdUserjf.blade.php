<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>来客-手动添加排队订单积分</title>
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
            <form action="setPdUserOrderNo" method="get">
            <table width="600px"height="300px">
                <comption style="font-size: 30px;font-weight:bold">手动添加排队订单积分</comption>

                <tr>
                    <td class="td1">录单ID</td>
                    <td style="text-align: left">
                        <input type="text" name="oid">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 20px">
                        <button onclick="javascript:return warning()" style="width: 220px;height: 40px;background: #7db2ec !important;color:#fff">添加订单积分</button>
{{--                        <input type="submit" name="submit" style="width: 220px;height: 40px;background: #7db2ec !important;" value="一键清空"></td>--}}
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
        if(confirm('确定要手动添加排队订单积分吗？')==true){
            return true;

        }else{
            return false;

        }

    }

</script>
</html>
