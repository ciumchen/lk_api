var host='https://lk.catspawvideo.com';
var oss_host="https://static.catspawvideo.com/";
var is_plus = false;
var wgt_var = "1.0.1";
var app_version = "1.0.1";

function getToken()
{
    //优先使用storage
    if(is_plus && typeof plus.storage != "undefined")
    {
		var  bb = plus.storage.getItem('u_token');
		if(!bb){
			return '';
		}
        var token = plus.storage.getItem("u_token");
        if(token)
        {
            localStorage.setItem("u_token",token);
            return token;
        }
        else
        {
			var  aa = localStorage.getItem('u_token');
			if(!aa){
				return '';
			}
            var token = localStorage.getItem("u_token");
            if(token)
            {
                //保存token
                plus.storage.setItem("u_token",token);
            }
            return token;

        }
    }
	var  aa = localStorage.getItem('u_token');
	if(!aa){
		return "";
	}
	var token = localStorage.getItem("u_token");
	return token;

}
function onOpenPage(pageUrl, pageId, needLogin)
{

	if (!pageUrl) {
		return mui.toast('此功能暂未开放');
	}
	if (needLogin == undefined) {
		needLogin = false;
	}
	if (needLogin) {
		userJump(pageUrl, pageId);
	} else {
		mui.openWindow({
			url: pageUrl,
			id: pageId,
			show: {
				autoShow: true,
				aniShow: 'slide-in-right',
			},
			waiting: {
				autoShow: false,
			}
		});
	}
}
function userJump(url,id)
{
	if(localStorage.uid)
	{
		mui.openWindow({
			url: url,
			id: id,
			show:{
				autoShow:true,
				aniShow:'slide-in-right',
			},
			waiting:{
				autoShow:false,
			}
		});
	}else{
		mui.openWindow({
			url: "login/login.html",
			id: "login.html",
			show:{
				autoShow:true,
				aniShow:'slide-in-right',
			},
			waiting:{
				autoShow:false,
			}
		});
	}
}
/**
 * 保存图片
 * @param {Object} url
 */
function saveImage(url) {
    var imgUrl = url;
    var timestamp = (new Date()).valueOf();
    var downLoader = plus.downloader.createDownload(imgUrl, {
        method: 'GET',
        filename: '_downloads/image/' + timestamp + '.png'
    }, function (download, status) {
        var fileName = download.filename;
		/**
		 * 保存至本地相册
		 */
        plus.gallery.save(fileName, function () {
            mui.toast("保存成功,请在手机相册查看");
        });
    });

	/**
	 * 开始下载任务
	 */
    try {
        downLoader.start();
    } catch (e) {
        //TODO handle the exception
        mui.toast("点击保存到相册");
    }
}
var update_host = "https://qkfileapp.oss-cn-hongkong.aliyuncs.com/app/org.qkfile/";

/**
 * app升级
 */
function app_update()
{
    if(mui.os.android)
    {
        var version = plus.runtime.version;

        mui.ajax(update_host+"app.update.json",{

            data:{
                'version':version,
                'platform':'android',
                "r":Math.random()
            },
            dataType:'json',//服务器返回json格式数据
            type:'get',//HTTP请求类型
            success:function(data){
                if(data.version > version)
                {

                    var app = data;
                    var url = app.url; // 下载文件地址

                    if(data.forcibly == "1")
                    {
                        var dtask = plus.downloader.createDownload( url, {}, function ( d, status ) {
                            if ( status == 200 ) { // 下载成功
                                var path = d.filename;
                                plus.runtime.install(path);  // 安装下载的apk文件
                            } else {//下载失败
                                mui.alert( "Download failed: " + status );
                            }
                        });
                        dtask.start();
                    }
                    else
                    {
                        plus.nativeUI.confirm( data.msg, function(e){
                            if(e.index==0)
                            {
                                var dtask = plus.downloader.createDownload( url, {}, function ( d, status ) {
                                    if ( status == 200 ) { // 下载成功
                                        var path = d.filename;
                                        plus.runtime.install(path);  // 安装下载的apk文件
                                    } else {//下载失败
                                        mui.alert( "Download failed: " + status );
                                    }
                                });
                                dtask.start();
                            }
                        }, "app更新", ["更新","取消"] );
                    }
                }
                else
				{
                    wgt_update();
				}
            },
            error:function () {
                wgt_update();
            }

        });
    }
	else
	{
		var version = plus.runtime.version;

        mui.ajax(update_host +"ios.update.json",{

            data:{
                'version':version,
                'platform':'ios',
                "r":Math.random()
            },
            dataType:'json',//服务器返回json格式数据
            type:'get',//HTTP请求类型
            success:function(data){

                if(data.version > version)
                {
                    mui.alert("发现新的版本，点击下载",function(){
                        window.location.href = data.url;
                    })
                }
                else
                {
                    try {
                        wgt_update();
                    }
                    catch (e) {
                        console.log(e)
                    }
                }
            }
        });
	}
}


function wgt_update() {
    plus.runtime.getProperty(plus.runtime.appid,function(inf){
        var wgtVer=inf.version;
        wgt_var = inf.version;

        //如果差量更新失败，就更新增量
        //安卓和ios区分升级
        if(mui.os.android)
            wgt_url = update_host+"wgt.android.update.json";
        else
            wgt_url = update_host+"wgt.ios.update.json";

        mui.ajax({
            type: "get",
            async: false,
            url: wgt_url,
            dataType: "json",
            data: {version:wgtVer,r:Math.random()},
            success: function (data) {
                if(compareVersion(data.version_name,wgtVer) > 0)
                {
                    if(data.forcibly == "1")
                    {
                        update_wgt(data.url);
                    }
                    else
                    {
                        plus.nativeUI.confirm( data.msg, function(e){
                            if(e.index==0)
                            {
                                update_wgt(data.url);
                            }
                        }, "资源包更新", ["升级","取消"] );
                    }
                }
                else
                {
                    is_update=false;
                }
            }
        });
    });
}

/**
 * 升级wgt
 * @param url
 */
function update_wgt(url) {
    plus.io.resolveLocalFileSystemURL("_doc/update.wgt", function( entry ) {
        // 可通过entry对象操作test.html文件
        entry.remove();
    });

    plus.nativeUI.showWaiting("下载wgt资源更新文件...");
    plus.downloader.createDownload( url, {filename:"_doc/update.wgt"}, function(d,status){
        if ( status == 200 ) {
            install_wgt(d.filename)
        } else {
            alert("下载wgt失败！");
        }
    }).start();
    plus.nativeUI.closeWaiting();
}

/**
 * 安装wgt
 * @param filename
 */
function install_wgt(filename)
{
    plus.nativeUI.showWaiting("安装wgt资源文件...");
    plus.runtime.install(filename,{force:true},function(){
        plus.nativeUI.closeWaiting();
        plus.nativeUI.alert("应用资源更新完成！",function(){
            plus.runtime.restart();
        });
        plus.io.resolveLocalFileSystemURL(filename, function( entry ) {
            entry.remove()
        },function (e) {
            alert(e)
        });

    },function(e){
        alert("安装wgt文件失败["+e.code+"]："+e.message);
        plus.io.resolveLocalFileSystemURL(filename, function( entry ) {
            entry.remove()
        },function (e) {
            alert(e)
        });

    });
}

function compareVersion(v1, v2) {
    v1 = v1.split(".")
    v2 = v2.split(".")
    len = Math.max(v1.length, v2.length)

    while (v1.length < len) {
        v1.push("0")
    }
    while (v2.length < len) {
        v2.push("0")
    }

    for (i = 0; i < len; i++) {
        num1 = parseInt(v1[i])
        num2 = parseInt(v2[i])

        if (num1 > num2) {
            return 1
        } else if (num1 < num2) {
            return -1
        }
    }

    return 0
}

