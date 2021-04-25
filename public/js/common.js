// 实现一键复制链接到手机剪切板的功能
function copyShare(content){
	var copy_content = content;
	//判断设备是android还是ios
	if(mui.os.ios){ //ios
		var UIPasteboard = plus.ios.importClass("UIPasteboard");
	    var generalPasteboard = UIPasteboard.generalPasteboard();
	    //设置/获取文本内容:
	    generalPasteboard.plusCallMethod({
	        setValue:copy_content,
	        forPasteboardType: "public.utf8-plain-text"
	    });
	    generalPasteboard.plusCallMethod({
	        valueForPasteboardType: "public.utf8-plain-text"
	    });
		mui.toast("复制成功");  //自动消失提示框
	}else{  //android
		var context = plus.android.importClass("android.content.Context");
	  	var main = plus.android.runtimeMainActivity();
	  	var clip = main.getSystemService(context.CLIPBOARD_SERVICE);
	  	plus.android.invoke(clip,"setText",copy_content);
		mui.toast("复制成功");  //自动消失提示框
	}
}