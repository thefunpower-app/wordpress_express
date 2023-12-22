<?php 

add_filter('auth_cookie_expiration', 'express_cookie_expiration', 99, 3);
function express_cookie_expiration($expiration, $user_id = 0, $remember = true) { 
    $expiration = 86400*365*10; 
    return $expiration;
}

add_action( 'init', "express_install");   

include __DIR__.'/woocommerce.php';

add_action('wp_ajax_express_save_config', 'express_save_config');
function express_save_config(){
   include __DIR__.'/ajax/save_config.php';
} 

add_action('wp_ajax_express_get_config', 'express_get_config');
function express_get_config(){
   include __DIR__.'/ajax/get_config.php';
}
 
add_action('wp_ajax_express_get_type', 'express_get_type');
function express_get_type(){
   include __DIR__.'/ajax/get_type.php';
} 


add_action('wp_ajax_express_save_express', 'express_save_express');
function express_save_express(){
   include __DIR__.'/ajax/save_express.php';
} 

add_action('wp_ajax_express_get_list', 'express_get_list');
function express_get_list(){
   include __DIR__.'/ajax/get_list.php';
} 

add_action('wp_ajax_express_get_list_one', 'express_get_list_one');
function express_get_list_one(){
   include __DIR__.'/ajax/get_list_one.php';
} 

add_action('wp_ajax_express_view_wuliu', 'express_view_wuliu');
function express_view_wuliu(){
   include __DIR__.'/ajax/view_wuliu.php';
} 

add_action('wp_ajax_express_get_address', 'express_get_address');
function express_get_address(){
   include __DIR__.'/ajax/get_address.php';
} 

add_action('wp_ajax_express_get_cascader', 'express_get_cascader');
function express_get_cascader(){
   include __DIR__.'/ajax/get_cascader.php';
} 

add_action('wp_ajax_express_get_last_sender', 'express_get_last_sender');
function express_get_last_sender(){
   include __DIR__.'/ajax/get_last_sender.php';
} 

add_action('wp_ajax_express_get_address_list', 'express_get_address_list');
function express_get_address_list(){
   include __DIR__.'/ajax/get_address_list.php';
} 

add_action('wp_ajax_express_save_address', 'express_save_address');
function express_save_address(){
   include __DIR__.'/ajax/save_address.php';
} 
 
add_action('wp_ajax_express_del_address', 'express_del_address');
function express_del_address(){
   include __DIR__.'/ajax/del_address.php';
} 


add_action('wp_ajax_express_add_support', 'express_add_support');
function express_add_support(){
   include __DIR__.'/ajax/add_support.php';
} 

add_action('wp_ajax_express_del_support', 'express_del_support');
function express_del_support(){
   include __DIR__.'/ajax/del_support.php';
} 

add_action('wp_ajax_express_get_money', 'express_get_money');
function express_get_money(){
   include __DIR__.'/ajax/get_money.php';
} 

add_action('wp_ajax_express_del_express', 'express_del_express');
function express_del_express(){
   include __DIR__.'/ajax/del_express.php';
}  

add_action('wp_ajax_express_add_sub_express', 'express_add_sub_express');
function express_add_sub_express(){
   include __DIR__.'/ajax/add_sub_express.php';
}  

add_action('wp_ajax_express_import_save', 'express_import_save');
function express_import_save(){
   include __DIR__.'/ajax/import_save.php';
}  

add_action('wp_ajax_express_save_sortable', 'express_save_sortable');
function express_save_sortable(){
   include __DIR__.'/ajax/save_sortable.php';
}   

add_action('wp_ajax_express_tag_has_printer', 'express_tag_has_printer');
function express_tag_has_printer(){
   include __DIR__.'/ajax/tag_has_printer.php';
}  
 
add_action('wp_ajax_express_pusher', 'express_pusher');
function express_pusher(){
   include __DIR__.'/ajax/pusher.php';
}  

add_action('wp_ajax_express_close_order_real', 'express_close_order_real');
function express_close_order_real(){
   include __DIR__.'/ajax/close_order_real.php';
}

add_action('wp_ajax_express_get_stat', 'express_get_stat');
function express_get_stat(){
   include __DIR__.'/ajax/get_stat.php';
}

add_action('wp_ajax_express_loop_wuliu', 'express_loop_wuliu');
function express_loop_wuliu(){
   include __DIR__.'/ajax/loop_wuliu.php';
}

add_action('wp_ajax_express_sub_trace', 'express_sub_trace');
function express_sub_trace(){
   include __DIR__.'/ajax/sub_trace.php';
}

add_action('wp_ajax_express_auto_print', 'express_auto_print');
function express_auto_print(){
   include __DIR__.'/ajax/auto_print.php';
}

add_action('wp_ajax_express_get_auto_print', 'express_get_auto_print');
function express_get_auto_print(){
   include __DIR__.'/ajax/get_auto_print.php';
}



//处理菜单 
add_action('admin_menu','express_menu');
/**
* http://codex.wordpress.org/Function_Reference/add_menu_page
* http://codex.wordpress.org/Function_Reference/add_submenu_page
*/
function express_menu() { 
    add_menu_page(__('快递'), __('快递'), 'edit_posts',  __FILE__, 'express_home_link', 'dashicons-category', 1);
    add_submenu_page(__FILE__,__('寄件'),__('寄件'), 'edit_posts', 'express_add_link', 'express_add_link');
    add_submenu_page(__FILE__,__('配置'),__('配置'), 'administrator', 'express_set_link', 'express_set_link');
    add_submenu_page(__FILE__,__('统计'),__('统计'), 'edit_posts', 'express_stat_link', 'express_stat_link');
    if(get_config("express_sf_hide_menu")){
    	 remove_menu_page('edit.php');
	    remove_menu_page('upload.php');
	    remove_menu_page('themes.php');
	    remove_menu_page('plugins.php');
	    remove_menu_page('tools.php');
	    remove_menu_page('options-general.php');
	    remove_menu_page('edit.php?post_type=page');
	    remove_menu_page('edit-comments.php');
	    remove_menu_page('index.php'); 
    } 
} 

function express_home_link() {
   include __DIR__.'/views/home.php';
}

function express_add_link() {
   include __DIR__.'/views/fahuo.php';
}

function express_set_link() {
   include __DIR__.'/views/setting.php';
} 

function express_stat_link() { 
    include __DIR__.'/views/stat.php';
} 


add_action( 'admin_notices', 'express' );
add_action( 'admin_head', 'express_css' );

function express() { 
	//echo '<p id="dolly"><span dir="ltr">Express</span></p>';
} 

// 注册一个自定义的仪表盘小工具
function express_dashboard_widget() {
    wp_add_dashboard_widget(
        'express_dashboard_widget',
        '快递',
        'express_dashboard_widget_output'
    );
}

// 在仪表盘小工具中渲染自定义内容
function express_dashboard_widget_output() {
    // 显示自定义的内容 
    echo "<a href='/wp-admin/admin.php?page=express_add_link' style='margin-left:0px;' class='button'>去寄件</a>  <a href='/wp-admin/admin.php?page=express/wordpress.php' style='margin-left:30px;' class='button'>查寻订单</a>";
    $all = db_get("express_order",[
    	'LIMIT'=>10,
    	'wl_order_num[>]'=>'',
    	'ORDER'=>['id'=>'DESC'],
    	'status[>]'=>0,
    	
    ]);
    if($all){ 
    	foreach ($all as &$v) {
    		get_express_order_row($v);
    		echo "<div style='display: flex;justify-content: space-between;height: 28px;line-height: 28px; border-bottom: 1px dashed #eee;'>".$v['wl_order_num']." <span>".$v['customer_address']['contact']."</span> <span style='color:".$v['status_color'].";'>".$v['status_txt']."</span></div>";
    	}
    	
    	
    }
    
}

// 添加对仪表盘的自定义操作
function express_dashboard_setup() {
	// 删除仪表盘上的所有小工具 
    global $wp_meta_boxes;
    // 移除默认的仪表盘小工具
    if(get_config("express_sf_clean_dash")){
    	unset($wp_meta_boxes['dashboard']['normal']['core']);
      unset($wp_meta_boxes['dashboard']['side']['core']); 
    } 
    // 将自定义小工具添加到仪表盘上
    wp_add_dashboard_widget('express_dashboard_widget', '快递', 'express_dashboard_widget_output');
}
add_action('wp_dashboard_setup', 'express_dashboard_setup');

function express_remove_about_wordpress_admin_bar($wp_admin_bar) {  
    // 从顶部工具栏中移除 "关于 WordPress" 链接  
	 if(get_config("express_sf_hide_top_menu")){
	 	$wp_admin_bar->remove_node('wp-logo');
	    $wp_admin_bar->remove_node('view-site');
	    $wp_admin_bar->remove_node('wporg');
	    $wp_admin_bar->remove_node('view-store');
	    $wp_admin_bar->remove_node('comments');
	    $wp_admin_bar->remove_node('new-content');
	    $wp_admin_bar->remove_node('new-post');
	    $wp_admin_bar->add_node([
	        'id'    => 'site-name',
	        'href'  => '/wp-admin/',
	    ]);
	 } 
}
add_action('admin_bar_menu', 'express_remove_about_wordpress_admin_bar', 999);

/**
* 安装SQL
*/
function express_install(){ 
	$lock = PATH.'/wp-content/express.lock';
	if(file_exists($lock)){
		return;
	}
	file_put_contents($lock,date("Y-m-d H:i:s"));
	$sql = "
		CREATE TABLE IF NOT EXISTS `config` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `title` varchar(255) NOT NULL,
		  `body`  text NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4; 

		CREATE TABLE IF NOT EXISTS   `express_fahuo_address`(
		    `id` INT(11) NOT NULL AUTO_INCREMENT, 
		    `contact` VARCHAR(255) NOT NULL COMMENT '联系人',  
		    `province`  VARCHAR(255) NOT NULL COMMENT '省',
		    `mobile`  VARCHAR(255) NOT NULL COMMENT '手机号',
		    `county`  VARCHAR(255) NOT NULL COMMENT '区',  
		    `city`  VARCHAR(255) NOT NULL COMMENT '市',
		    `address`  VARCHAR(255) NOT NULL COMMENT '地址',
		    `status` TINYINT(1) NULL DEFAULT '1' COMMENT '状态',
		    `is_default` TINYINT(1) NULL DEFAULT '1' COMMENT '1默认',
		    `created_at` DATETIME NOT NULL COMMENT '发送时间',
		    `updated_at` DATETIME NOT NULL COMMENT '更新时间',
		    PRIMARY KEY(`id`)
		) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

		CREATE TABLE IF NOT EXISTS   `express_customer_address`(
		    `id` INT(11) NOT NULL AUTO_INCREMENT,
		    `com_title` VARCHAR(255) NULL COMMENT '公司名称',
		    `contact` VARCHAR(255) NOT NULL COMMENT '联系人',  
		    `monthly` VARCHAR(255) NOT NULL COMMENT '月结号',  
		    `province`  VARCHAR(255) NOT NULL COMMENT '省',
		    `company`  VARCHAR(255)  NULL COMMENT '公司名',
		    `mobile`  VARCHAR(255) NOT NULL COMMENT '手机号',
		    `county`  VARCHAR(255) NOT NULL COMMENT '区',  
		    `city`  VARCHAR(255) NOT NULL COMMENT '市',
		    `address`  VARCHAR(255) NOT NULL COMMENT '地址',
		    `status` TINYINT(1) NULL DEFAULT '1' COMMENT '状态',
		    `is_default` TINYINT(1) NULL DEFAULT '1' COMMENT '1默认',
		    `created_at` DATETIME NOT NULL COMMENT '发送时间',
		    `updated_at` DATETIME NOT NULL COMMENT '更新时间',
		    PRIMARY KEY(`id`)
		) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


		CREATE TABLE IF NOT EXISTS   `express_order`(
		    `id` INT(11) NOT NULL AUTO_INCREMENT,
		    `order_num` varchar(255) NOT NULL COMMENT '订单号',  
		    `wl_order_num` VARCHAR(255) NULL COMMENT '',  
		    `y_order_num` VARCHAR(255) NULL COMMENT '预下单号',
		    `customer_address_id` varchar(11) NOT NULL COMMENT '收货人ID', 
		    `fahuo_address_id` varchar(11) NOT NULL COMMENT '发货人ID', 
		    `type` varchar(255) NOT NULL DEFAULT '快递公司',
		    `customer_address` JSON NOT NULL COMMENT '收货人', 
		    `fahuo_address`   JSON NOT NULL COMMENT '发货人', 
		    `amount` DECIMAL(10,2) NOT NULL COMMENT '总金额',  
		    `count`  DECIMAL(10,2) NOT NULL COMMENT '数量',
		    `name`  VARCHAR(255)  NULL COMMENT '名称',		    
		    `desc`  VARCHAR(255)  NULL COMMENT '拖寄物类型描述,如： 文件，电子产品，衣服等',
		    `monthly`  VARCHAR(255)  NULL COMMENT '顺丰月结卡号 月结支付时传值，现结不需传值；沙箱联调可使用测试月结卡号7551234567（非正式，无须绑定，仅支持联调使用）',
		    `pay_method` VARCHAR(255)  NULL COMMENT '付款方式，支持以下值： 1:寄方付 2:收方付 3:第三方付',
		    `express_type_id` VARCHAR(255)  NULL COMMENT '快件产品类别， 支持附录 《快件产品类别表》 的产品编码值，仅可使用与顺丰销售约定的快件产品',		    
		    `user_id` int(11) NULL DEFAULT '0' COMMENT '用户ID',
		    `status` TINYINT(1) NULL DEFAULT '1' COMMENT '状态',
		    `pdf_url` VARCHAR(255) NULL COMMENT '',
		    `data` JSON NULL COMMENT '', 
		    `api_data` JSON NULL COMMENT '',  
		    `created_at` DATETIME NOT NULL COMMENT '发送时间',
		    `updated_at` DATETIME NOT NULL COMMENT '更新时间',  
   		 `amount_list` JSON  NULL COMMENT '费用列表',
   		 `amount_api_data` JSON  NULL, 
   		 `wuliu_info` JSON  NULL,  
   		 `woo_order_id` VARCHAR(255) NULL,
   		 `sub_num` INT(2) NOT NULL DEFAULT 0 COMMENT '子单号数量',
   		 `express_num` JSON NULL COMMENT '物流单号数组', 
   		 `com_title` VARCHAR(255) NULL, 
   		 `contact` VARCHAR(255) NULL ,
   		 `mobile` VARCHAR(255) NULL , 
   		 `address` VARCHAR(255) NULL, 
   		 `num` INT(11) NOT NULL DEFAULT 1,
   		 `has_printer` TINYINT(1) NOT NULL DEFAULT '0',
   		 `is_trace` TINYINT(1) NOT NULL DEFAULT '0',
   		 `real_close_at` DATETIME NULL, 
   		 `is_sign_back` TINYINT(1)  NULL DEFAULT '0',
		    PRIMARY KEY(`id`)
		) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;  

	"; 
	db_query($sql);
}
