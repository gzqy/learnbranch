/*
* images Embed Plugin
*
* @author Jonnas Fonini <contato@fonini.net>
* @version 1.0.9
*/
( function() {
	CKEDITOR.plugins.add( 'images',
	{
		lang: [ 'en', 'pt', 'ja', 'hu', 'it', 'fr', 'tr', 'ru', 'de', 'ar', 'nl', 'pl', 'vi', 'zh'],
		init: function( editor )
		{
			editor.addCommand( 'images', new CKEDITOR.dialogCommand( 'images'));

			editor.ui.addButton( 'Images',
			{
				label : editor.lang.images.button,
				toolbar : 'insert',
				command : 'images',
				icon : this.path + 'images/icon.png'
			});

			CKEDITOR.dialog.add( 'images', function ( instance )
			{
				

				return {
					title : editor.lang.images.title,
					width : 750,
					height : 450,
                    resizable:CKEDITOR.DIALOG_RESIZE_BOTH,
					contents :
						[{
							id : 'imagesPlugin',
							expand : true,
							elements :
								[
								{
									type : 'html',
									html : initImageDlgInnerHtml()
                                    
								}
							]
						}
					],
                    onLoad : function () {

                        ckswfUpload();

                    },
                    onShow:function(){

                    },
					onOk: function()
					{
                        var content = $("#ckspanSWFUploadbox").html();
                        var instance = this.getParentEditor();
						instance.insertHtml( content );
						$("#ckspanSWFUploadbox").html('');
						$("#ckspanSWFUploadLoading").text('');
					}
				};
			});
		}
	});
})();



function ckswfUpload() {

    var TOKEN_SESSIONID = $("#_TOKEN_SESSIONID").val();
    var given_width=$("#_given_width").val();
    var settings = {
        flash_url : "/public/swfupload/swfupload.swf",
        upload_url: "/upload/swfupload",
        post_params: {
            "TOKEN_SESSIONID"  : TOKEN_SESSIONID,
            "given_width":given_width                   //imgage width
        },
        button_image_url:"/public/images/up_btn.jpg",
        button_width: "108",
        button_height: "31",
        button_placeholder_id : "ckspanSWFUploadBtn",
        button_window_mode: "transparent",
        file_queued_handler : function(){

        },
        file_queue_error_handler : function(){

        },
        file_dialog_complete_handler : function(){
            $("#ckspanSWFUploadLoading").html('lease select an image to upload...');
            this.startUpload();
        },
        
        upload_start_handler : function(file){
            $("#ckspanSWFUploadLoading").html('Uploading image, please wait...');
            return true;
        },
        upload_progress_handler : function(file){
            return true;
        },
        upload_error_handler : function(file, errorCode, message){
            alert(message);
        },
        upload_success_handler : function(file, server_data){
            var rep = $.parseJSON(server_data);
            
            if ( rep.status == 200 ) {
                var html  = '<p><img src="'+rep.src+'" /></p>';
                $("#ckspanSWFUploadbox").append(html);
            } else {

            }
        },
        upload_complete_handler: function(file){
            if (this.getStats().files_queued === 0) {
                $("#ckspanSWFUploadLoading").text('Upload Complete!');
            }
        },
        queue_complete_handler: function(file){

        }
    };
    ckswfu = new SWFUpload(settings);
}


function initImageDlgInnerHtml(){
	var iHtml = "<div style='height:480px;overflow:auto;'>" ;
		iHtml += "<div style='float:left;height:31px;width:108px;overflow:hidden;' ><span id='ckspanSWFUploadBtn'>loading...</span></div>";
        
		iHtml += "<div style='clear:both;'></div><div id='ckspanSWFUploadbox' style='width:720px;min-height:200px;max-height:400px;overflow:auto;margin: 5px;'></div>";
        iHtml += "<div style='clear:both;'></div><span id='ckspanSWFUploadLoading' style='float:left;overflow:hidden;margin: 8px 0 0 10px;;'></span>";
        iHtml += "</div>";
	return iHtml;
}