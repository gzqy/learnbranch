<div class="four wide column">
    <div class="verticalMenu">
        <div class="ui vertical pointing menu fluid">
            <a class="<?php  if( strtolower(\Esy\Routes::getAction()) == 'add' ) { echo 'active teal'; } ?>  item" href="/device/add">
                <i class="add icon"></i> 添加新设备
            </a>
            <a class="<?php if( strtolower(\Esy\Routes::getAction())=='index' ) { echo 'active teal'; } ?>  item" href="/device">
                <i class="ordered list icon"></i> 设备列表
            </a>
            <a class="<?php if( strtolower(\Esy\Routes::getAction())=='unregistered' ) { echo 'active teal'; } ?> item" href="/device/Unregistered">
                <i class="ordered list icon"></i> 未注册设备
            </a>
            <a class="<?php if( strtolower(\Esy\Routes::getAction())=='groupadd' ) { echo 'active teal'; } ?> item" href="/device/GroupAdd">
                <i class="add icon"></i> 添加设备组
            </a>
            <a class="<?php if( strtolower(\Esy\Routes::getAction())=='grouplist' ) { echo 'active teal'; } ?>  item" href="/device/GroupList">
                <i class="ordered list icon"></i> 设备组列表
            </a>
        </div>
    </div>
</div>