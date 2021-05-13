var dialog = ({
    init:function(){
        var self = this;
        if ( self.html_obj ) {
            return true;
        }
        
        self.html_obj = $('<div id="_dialog"></div>');
        self.dialog_url = $('<div id="_dialog_url"></div>').appendTo(self.html_obj);
        self.dialog_html = $('<div id="_dialog_html"></div>').appendTo(self.html_obj);
        self.dialog_tip = $('<div id="_dialog_tip"></div>').appendTo(self.html_obj);
        self.dialog_loading = $('<div id="_dialog_loading"><p><span></span></p><p>Loading...</p></div>').appendTo(self.html_obj);
        self.html_obj.appendTo('body');
    },
    hide:function(){
        var self = this;
        if ( self.dialog_url_box ) {
            self.dialog_url_box.dialog("close");
        }
        if ( self.dialog_tip_box ) {
            self.dialog_tip_box.dialog("close");
        }
        if ( self.dialog_loading_box ) {
            self.dialog_loading_box.dialog("close");
        }
        if ( self.dialog_html_box ) {
            self.dialog_html_box.dialog("close");
        }
    },
    show:function(label){
        var self = this;
        switch(label) {
            case 'url':
                if ( self.dialog_url_box ) {
                    self.dialog_url_box.dialog("open");
                }
                if ( self.dialog_tip_box ) {
                    self.dialog_tip_box.dialog("close");
                }
                if ( self.dialog_loading_box ) {
                    self.dialog_loading_box.dialog("close");
                }
                if ( self.dialog_html_box ) {
                    self.dialog_html_box.dialog("close");
                }
                break;
            case 'tip':
                if ( self.dialog_url_box ) {
                    self.dialog_url_box.dialog("close");
                }
                if ( self.dialog_tip_box ) {
                    self.dialog_tip_box.dialog("open");
                }
                if ( self.dialog_loading_box ) {
                    self.dialog_loading_box.dialog("close");
                }
                if ( self.dialog_html_box ) {
                    self.dialog_html_box.dialog("close");
                }
                break;
            case 'loading':
                if ( self.dialog_url_box ) {
                    self.dialog_url_box.dialog("close");
                }
                if ( self.dialog_tip_box ) {
                    self.dialog_tip_box.dialog("close");
                }
                if ( self.dialog_loading_box ) {
                    self.dialog_loading_box.dialog("open");
                }
                if ( self.dialog_html_box ) {
                    self.dialog_html_box.dialog("close");
                }
                break;
            case 'html':
                if ( self.dialog_url_box ) {
                    self.dialog_url_box.dialog("close");
                }
                if ( self.dialog_tip_box ) {
                    self.dialog_tip_box.dialog("close");
                }
                if ( self.dialog_loading_box ) {
                    self.dialog_loading_box.dialog("close");
                }
                if ( self.dialog_html_box ) {
                    self.dialog_html_box.dialog("open");
                }
                break;
        }
    },
    url:function(opt){
        var self = this;
        self.init();
        self.dialog_url_opt = opt;
        self.loading();
        self.dialog_url.empty();
        self.dialog_url_opt.dataType = self.dialog_url_opt.dataType || 'html';
        $.ajax({
            type: "GET",
            dataType : self.dialog_url_opt.dataType,
            url: self.dialog_url_opt.url
        }).done(function( rs ) {
            var html = '';
            if ( self.dialog_url_opt.dataType == 'jsonp' ) {
                html = rs.data.html;
            } else {
                html = rs;
            }
            self.dialog_url_opt.width = self.dialog_url_opt.width || 750;
            self.dialog_url_opt.height = self.dialog_url_opt.height || 'auto';
            self.dialog_url_opt.modal = self.dialog_url_opt.modal || true;
            self.dialog_url_opt.title = self.dialog_url_opt.title || '';
            self.dialog_url_opt.resizable = self.dialog_url_opt.resizable || false;
            self.dialog_url_opt.open = self.dialog_url_opt.open || false;
            self.dialog_url_opt.close = self.dialog_url_opt.close || false;
            self.dialog_url_opt.buttons = self.dialog_url_opt.buttons || false;
            self.dialog_url_box = self.dialog_url.html(html).dialog({
                dialogClass: '_dialog_url',
                width: self.dialog_url_opt.width ,
                height: self.dialog_url_opt.height ,
                modal: self.dialog_url_opt.modal,
                title: self.dialog_url_opt.title,
                resizable: self.dialog_url_opt.resizable,
                draggable: false,
                closeOnEscape: false,
                buttons:self.dialog_url_opt.buttons,
                open: function(event, ui){
                    if ( !self.dialog_url_opt.title ) {
                        $("._dialog_url").find(".ui-dialog-titlebar").hide();
                    } else {
                        $("._dialog_url").find(".ui-dialog-titlebar").show();
                    }
                    if( self.dialog_url_opt.open ) {
                        setTimeout(function(){
                            self.dialog_url_opt.open();
                        },300);
                    }
                },
                close:function(event, ui){
                    if ( self.dialog_url_opt.close ) {
                        self.dialog_url_opt.close();
                    }
                }
            });
            self.show('url');
        });
    },
    tip:function(_opt){
        var self = this;
        self.init();
        self.show('tip');
        var opt;
        if ( typeof _opt=='object' ) {
            opt = _opt;
        } else {
            opt = {msg:_opt};
        }
        self.dialog_tip_opt = opt;
        var html = '<p><b>'+self.dialog_tip_opt.msg+'</b></p>';
        var buttons = {"確定": function() { $( this ).dialog( "close" ); } };
        self.dialog_tip_opt.buttons = self.dialog_tip_opt.buttons || buttons;
        self.dialog_tip_opt.width = self.dialog_tip_opt.width || 400;
        self.dialog_tip_opt.height = self.dialog_tip_opt.height || 'auto';
        self.dialog_tip_box = self.dialog_tip.html(html).dialog({
            dialogClass: '_dialog_tip',
            width: self.dialog_tip_opt.width,
            height:self.dialog_tip_opt.height,
            modal: true,
            resizable: false,
            draggable: false,
            closeOnEscape: false,
            buttons:self.dialog_tip_opt.buttons,
            open: function(event, ui){
                if (self.dialog_tip_opt.open) {
                    self.dialog_tip_opt.open();
                }
                $("._dialog_tip").find(".ui-dialog-titlebar").hide();
            }
        });
    },
    html: function(html){
        var self = this;
        self.init();
        self.show('html');

        self.dialog_html_box = self.dialog_html.html(html).dialog({
            dialogClass: '_dialog_html',
            width: 'auto',
            height:'auto',
            modal: true,
            resizable: false,
            draggable: false,
            closeOnEscape: false,
            buttons:false,
            open: function(event, ui){
                $("._dialog_html").find(".ui-dialog-titlebar").hide();
            }
        });
    },
    loading:function(){
        var self = this;
        self.init();
        self.show('loading');
        self.dialog_loading_box = self.dialog_loading.dialog({
            dialogClass: '_dialog_loading',
            width: 400,
            modal: true,
            resizable: false,
            draggable: false,
            closeOnEscape: false,
            open: function(event, ui){
                $("._dialog_loading").find(".ui-dialog-titlebar").hide();
            }
        });
    }
});
$(function() {
    /**
     * 
    dialog.loading();
    dialog.hide();
    dialog.show('url');
    dialog.url({
        url:'/index/test'
    });
    dialog.url({
        url:'/index/test',
        title:'1223'
    });
     
    dialog.tip({msg:'操作成功'});
    
    dialog.tip({
        msg:'是否要刪除?',
        buttons:{"確定": function() { alert(1); },"取消": function() {  dialog.hide(); } }
    });
    
    dialog.url({
        url:'/index/test',
        //title:'1223',
        buttons:{"確定": function() { dialog.hide(); } }
    });
    **/
});
