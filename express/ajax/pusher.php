<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php  
send_pusher(['xa_reload_page'=>100],$channel='xa_express',$event='notice');
json_success();