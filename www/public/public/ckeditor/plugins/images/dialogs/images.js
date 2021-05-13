CKEDITOR.dialog.add(
	    "images",
 	    function (b)
	    {
	        return {
	        	title:"图片",
                   minWidth:590,
 	            minHeight:300,
	            contents:[{
	                    id:"tab1",
 	                    label:"",
	                    title:"",
	                    expand:true,
 	                    padding:0,
	                    elements:[{
	                            type:"html",
 	                            html:initImageDlgInnerHtml() //对话框中要显示的内容，这里的代码将发在下面
 	                    }]
 	            }],
	            onOk: function(){ //对话框点击确定的时候调用该函数
	            	var D = this;
	            	var imes = getCkUploadImages();//获取上传的图片，用于取路径，将图片显示在富文本编辑框中
	            	$(imes).each(function(){
	            		D.imageElement = b.document.createElement('img');
						D.imageElement.setAttribute('alt', '');
						D.imageElement.setAttribute('_cke_saved_src', $(this).attr("src"));
						D.imageElement.setAttribute('src', $(this).attr("src"));
						D.commitContent(1, D.imageElement);
						if (!D.imageElement.getAttribute('style')){
							D.imageElement.removeAttribute('style');
						}
						b.insertElement(D.imageElement);
	            	});
	            },
	        	onLoad: function(){ //对话框初始化时调用
	        		initEventImageUpload(); //用于注册上传swfupload组件
	        	},
	        	onShow:function(){
	        		clearCkImageUpload(); //在对话框显示时作一些特殊处理
	        	}
 	        };
	    }
);
//编辑框初始化上传图片的回调----------自定义按钮插件
function initImageDlgInnerHtml(){ //这是在对话框中要显示的内容
	var iHtml = "<div style='float:left;width:100%'>上传到服务器上</div>" ;
		iHtml += "<div style='float:left;width:100%;' class='setUpload'>";
		iHtml += "<div style='float:left;height:24px;width:82px' class='su_img'><span id='ck_btn_id'>dssdf</span></div>";
		iHtml += "<div style='float:left' id='ck_fs_upload_progress'>未选择文件</div>";
		iHtml += "</div>";
		iHtml += "<div style='float:left;width:100%'><input id='stop_id' type='button' vlaue='终止'/><input id='ck_btn_start' class='cke_dialog_start_button_z' type='button' value='开始上传' style='float:left' onclick='ckUploadImageStart();'/></div>";
		iHtml += "<div id='ck_pic_div' style='float:left;width:100%'></div>";
	return iHtml;
}


function initEventImageUpload(){ //对上传控件的注册
	ckeditorInitSwfu("ck_fs_upload_progress","stop_id","ck_btn_id");
	$("#ck_fs_upload_progress").parent().find("object").css({"height":"24px","width":"82px"});
	$("#ck_btn_start").mouseover(function(){
		$(this).css({"cursor":"hand","background-position":"0 -1179px"});
	});
}
function clearCkImageUpload(){ //对对话框弹出时作特殊处理
	if($("#ck_fs_upload_progress").html().indexOf(".jpg") != -1){
		$("#ck_fs_upload_progress").html("");
	}
	$("#ck_pic_div").html("");
}
function getCkUploadImages(){
	return $("#ck_pic_div").find("img");
}

var ckSwfu; //初始化上传控件
function ckeditorInitSwfu(progress,btn,spanButtonPlaceHolder) {
	var uploadUrl = "${BasePath}/commodity_com/img/uploadCommodityImg.ihtml?type=1";
	//在firefox、chrome下，上传不能保留登录信息，所以必须加上jsessionid。
	var jsessionid = $.cookie("JSESSIONID");
	if(jsessionid) {
		uploadUrl += "?jsessionid="+jsessionid;
	}
	ckSwfu=new SWFUpload({
		upload_url : uploadUrl,
		flash_url : "${BasePath}/res/base/plugin/swfupload/swfupload.swf",
		file_size_limit : "4 MB",
		file_types : "*.jpg;*.png;*.gif;*.jpeg;*.bmp",
		file_types_description : "Web Image Files",
		file_queue_limit : 0,
		custom_settings : {
			progressTarget : progress,
			cancelButtonId : btn
		},
		debug: false,
		
		button_image_url : "${BasePath}/res/base/plugin/swfupload/button_notext.png",
		button_placeholder_id : spanButtonPlaceHolder,
		button_text: "<span class='btnText'>上传图片</span>",
		button_width: 81,
		button_height: 24,
		button_text_top_padding: 2,
		button_text_left_padding: 20,
		button_text_style: '.btnText{color:#666666;}',
		button_cursor:SWFUpload.CURSOR.HAND,
		
		file_queued_handler : fileQueuedCk,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogCompleteCk,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccessCk,
		upload_complete_handler : uploadComplete,
		queue_complete_handler : queueComplete
	});
};
//开始上传图片
function ckUploadImageStart(obj){
	ckSwfu.startUpload();
}
//回调重写
function fileQueuedCk(file) {
	try {
		if($("#ck_fs_upload_progress").html().indexOf(".jpg") == -1){
			$("#ck_fs_upload_progress").html("");
		}
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("Pending...");
		progress.toggleCancel(true, this);
		$(progress.fileProgressWrapper).css("display","none");
		$("#ck_fs_upload_progress").append(" "+file.name);
	} catch (ex) {
		this.debug(ex);
	}

}
//回调重写，上传成功后
function uploadSuccessCk(file, serverData) {
	try {
		var progress = new FileProgress(file, swfu.customSettings.progressTarget);
		//progress.setComplete();
		//progress.setStatus("上传成功！");
		//progress.toggleCancel(false);
		$(progress.fileProgressWrapper).css("display","none");
		var json = eval("("+serverData+")");
		
	} catch (ex) {
	
	}
}
//回调重写，主要是设置参数，如果需要的参数没有，就清空上传的文件，为了解决下次选择会上传没有参数时的图片
function fileDialogCompleteCk(numFilesSelected, numFilesQueued) {
	try {
		var commoNo = $("#commoNo").val();
  		var brandNo = $("#brand option:selected").val();
  		var catNo = $("#thirdCommon option:selected").val();
  		//初始化上传图片
  		if(brandNo != "" && commoNo != "" && catNo != "") {
  			this.addPostParam("commoNo",commoNo);
  			this.addPostParam("thirdCatNo",catNo);
  			this.addPostParam("brandNo",brandNo);
  			if (numFilesSelected > 0) {
				document.getElementById(this.customSettings.cancelButtonId).disabled = false;
			}
  		} else {
  			for(var i=0;i<numFilesSelected;i++){
  				var promitId = this.customSettings.progressTarget;
	  			$("#"+promitId).find("*").remove();
	  			var fileId = this.getFile().id;
	  			this.cancelUpload(fileId,false);
  			}
  			$("#ck_fs_upload_progress").html("");
  			alert("请选择分类和品牌！");
  		}
	} catch (ex)  {
        this.debug(ex);
	}
}
