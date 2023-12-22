<?php
/**
 * @package Express
 * @version 1.0.0
 */
/*
Plugin Name: 快递
Plugin URI: http://wordpress.org/plugins/express/
Description:  集成发送快递、报表统计
Author: Sun Kang
Version: 1.0.0
Author URI: http://wordpress.org/
*/ 
include __DIR__.'/boot.php'; 
g_init_wordpress(); 
// 移除网站后台的版本号
function express_remove_version_footer() {
    remove_filter('update_footer', 'core_update_footer');
}
add_action('admin_init', 'express_remove_version_footer');

function express_remove_admin_footer_text() {
    echo '';
}
add_filter('admin_footer_text', 'express_remove_admin_footer_text');

function express_css() {
	echo "
	<style type='text/css'>
	  
	.el-pagination{
		padding-left:0 !important;
	}
	.el-dialog__body { 
	    padding-top: 0 !important;
	}
	.el-pagination.is-background .btn-next, .el-pagination.is-background .btn-prev, .el-pagination.is-background .el-pager li {
    	margin: 0 0px !important;
	}
	input[type=color], input[type=date], input[type=datetime-local], input[type=datetime], input[type=email], input[type=month], input[type=number], input[type=password], input[type=search], input[type=tel], input[type=text], input[type=time], input[type=url], input[type=week], select, textarea {
		    border: 1px solid #DCDFE6;
	}
	input.readonly, input[readonly], textarea.readonly, textarea[readonly] {
	    background-color: #fff;
	}

	.php-error #adminmenuback, .php-error #adminmenuwrap {
	    margin-top: 0em !important;
	}

	#adminmenu, #adminmenuback, #adminmenuwrap { 
	    height: 96vh;
	}

	.el-radio.is-bordered+.el-radio.is-bordered {
	    margin-left: 0px !important;
	}

	.el-radio--small.is-bordered { 
	    padding-right: 10px !important;
	    margin-right: 5px !important;
	}


	/*.el-table  td,
	.el-table  th {
	    padding: 0px 0 !important;
	} 
	.el-table .cell { 
	    line-height: 25px; 
	    height: 25px;
	}
	.el-table tbody .el-button{
		padding:0;
	}*/
	</style>
	";
}


