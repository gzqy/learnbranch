 <?php include __DIR__.'/../Public/common.php';?>
 <?php $controller->loadAssets('resource/detail/newShop/css/nav.js')?>
  <?php $controller->loadAssets('resource/detail/newShop/css/nav.css')?>
   <?php $controller->loadAssets('resource/detail/newShop/css/right.css')?>
   <?php $controller->loadAssets('resource/detail/newShop/css/tab.css')?>
   <?php $controller->loadAssets('resource/detail/newShop/css/search.css')?>
   <?php $controller->loadAssets('resource/detail/newShop/css/list.css')?>
   <?php $controller->loadAssets('resource/detail/newShop/css/style.css')?> 

 <div class="content" >
    <div class="con" >
        <dl class="nav">
            <dt><b>●</b>音频播放</dt>
                    <dd class="bg"><a href="javascript:;"><b>●</b>音频播放</a></dd>
        </dl>
        <div class="right" >                       
            <div class="vertical segment" >
                <p>
                    <object id="Player1" width="640" height="<?php if($file_type == 'wmv') { echo '480'; } else { echo '100'; } ?>" classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6"
                    codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6 ,4,7,1112"
                    align="baseline" border="0" standby="Loading Player..."
                    type="application/x-oleobject">                
                        <param name="FileName" value="">
                        <param name="autoStart" value="true">
                        <param name="invokeURLs" value="false">
                        <param name="playCount" value="100">
                        <param name="defaultFrame" value="datawindow">
                    </object>
                </p>
                <div class=" vertical segment">
                    <div class="field" >
                        <div class=" small  labeled  input">
                            <button class="ui"  href="javascript:void(0)" id="_play_btn" onclick="PlayFile();" >开始播放</button>
                        </div>

                    </div>
                </div>
                <input type="hidden" value="<?=$play_file?>" id="play_file" >
            </div>
        </div>
    </div>
</div>  
        <?php $controller->loadAssets('resource/js/device.js')?>
    </body>
</html>
