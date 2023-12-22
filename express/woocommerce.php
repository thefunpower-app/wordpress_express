<?php    
$woocommerce =  __DIR__.'/../woocommerce/woocommerce.php';
if(!file_exists($woocommerce)){
	return;
}
function express_update_order($actions, $order) { 
    $order_status = $order->get_status();
    if (is_admin() && $order_status === 'processing') { 
        $actions['sender_order_to_user'] = "发货"; 
    } 
    return $actions;
}
add_filter('woocommerce_order_actions', 'express_update_order', 10, 2);
 
function get_express_woo_order_link($with_jump = false) {
	global $post;
	$order_id = $post->ID;
	$order = wc_get_order($order_id);
	$user = $order->get_address('shipping');
	if($user){
		$receiver = [
			'com_title'=>$user['company'],
			'contact'=>$user['first_name'].$user['last_name'],
			'mobile'=>$user['phone'],
			'address'=>$user['city'].$user['address_1'],
		];
		$url = "/wp-admin/admin.php?page=express_add_link&woo_order_id=".$order_id."&receiver=".urlencode(base64_encode(json_encode($receiver)));
		if(!$with_jump){
			return $url;	
		}		
		header("Location: $url");
  		exit();
	}else{

	} 
}
function handle_sender_order_to_user()
{
	get_express_woo_order_link(true);
}
add_action('woocommerce_order_action_sender_order_to_user', 'handle_sender_order_to_user');

function woo_express_notes_append($order) {  
	    echo '<div id="shipping-info-section">';   
        echo '<h3>物流信息</h3>';
        echo '<p></p>'; 

	    echo '</div>'; 
}
 
//add_action( 'woocommerce_order_details_after_order_table', 'woo_express_notes_append' );
// 添加小工具
function express_add_custom_widget() {
    $screen = get_current_screen();
    global $post;
	$order_id = $post->ID;
	$order = wc_get_order($order_id); 
    $res   = db_get_one("express_order","*",['woo_order_id'=>$order_id]);  
    $wl_order_num  = $res['wl_order_num'];
    $wuliu_info = $res['wuliu_info'];
    // 仅在WooCommerce订单编辑页面显示小工具
    if ( $screen->id === 'shop_order' ) {
    	if($wl_order_num){
    		$title = '重新发货';
    		$url = '/wp-admin/admin.php?page=express_add_link&id='.$res['id']."&woo_order_id=".$order_id;
    	}else{
    		$title = '发货';
    		$url = get_express_woo_order_link().'&wl_number='.$wl_order_num;
    	}
        ?>
        <li class="wide">
        	<div style="display:flex;align-items: center;justify-content: space-between;">
        		<div><?php _e( '物流', 'express' ); ?></div> 
        		<div><a href="<?=$url?>"><?php _e( $title, 'express' ); ?></a></div>
        	</div>
        </li> 
        <?php if($wl_order_num){ foreach($wuliu_info as $v){?>
        <li class="wide">
        	<div style="display:flex;align-items: center;justify-content: space-between;"> 
	            <div><?php _e( '单号', 'express' ); ?>: <?=$v['mailNo']?></div> 
        		<div><a href="javascript:void(0);" class="layer_open"  tag="woo_express_<?=$v['mailNo']?>"  title="<?=$v['mailNo']?>"><?php _e( '查看', 'express' ); ?></a></div>
        	</div> 
        </li> 
        <?php }}?>

        <?php
    }
}
add_action( 'woocommerce_order_actions_end', 'express_add_custom_widget' ); 


add_action('woocommerce_order_details_before_order_table_items',function($order){ 
	$order_id = $order->get_id(); 
    $res   = db_get_one("express_order","*",['woo_order_id'=>$order_id]);  
    $wl_order_num  = $res['wl_order_num'];
    $wuliu_info = $res['wuliu_info']; 
?>
<?php if($wl_order_num){ foreach($wuliu_info as $v){?> 
			<tr>
				<th class="woocommerce-table__product-name product-name">
					<?php _e( '运单号', 'express' ); ?>
				</th>
				<th class="woocommerce-table__product-table product-total"><?=$v['mailNo']?>
					<a href="javascript:void(0);" class="layer_open" tag="woo_express_<?=$v['mailNo']?>" title="<?=$v['mailNo']?>">查看</a>
				</th>
			</tr> 
			<?php 
			if($v['routes']){  
				foreach($v['routes'] as $vv){
			?>
			<tr class="woocommerce-table__line-item order_item"> 
	 			<td><?=$vv['remark']?></td>
	 			<td><?=$vv['acceptTime']?> </td>
			</tr> 
 			<?php 
		 		}
		 	}?> 
   
<?php }}?>
<?php 

});
 
function wp_woo_express_footer(){
	$order_id = absint( get_query_var( 'view-order' ) );
	if(!$order_id){
		global $post;
		$order_id = $post->ID;
		$order = wc_get_order($order_id); 
		if(!$order){
			return;
		}
	}
	$order = wc_get_order($order_id);
	if($order){
		$res   = db_get_one("express_order","*",['woo_order_id'=>$order_id]);  
		$wl_order_num  = $res['wl_order_num'];
    	$wuliu_info = $res['wuliu_info']; 
?>
<?php if($wl_order_num){ foreach($wuliu_info as $v){?> 
	<div style="display:none; padding:20px;" id="woo_express_<?=$v['mailNo']?>"> 
	<?php 
	if($v['routes']){  
		foreach($v['routes'] as $vv){
	?>
	<tr class="woocommerce-table__line-item order_item"> 
			<td><?=$vv['remark']?></td>
			<td><?=$vv['acceptTime']?> </td>
	</tr> 
		<?php 
 		}
 	}?> 

<?php }}?>

<script type="text/javascript" src="/wp-content/plugins/core/node_modules/layui/dist/layui.js"></script>
<link rel='stylesheet'  href='/wp-content/plugins/core/node_modules/layui/dist/css/layui.css' media='all' />

<script type="text/javascript">
	jQuery(function(){
		jQuery("a.layer_open").click(function(){
			let title = jQuery(this).attr('title');  
			let tag   = jQuery(this).attr('tag');  
			layer.open({
				title:title,
		        type: 1,
		        offset:'auto',
		        shade: 0.6, // 不显示遮罩
		        content: jQuery('#'+tag), // 捕获的元素
		        end: function(){ 
		        }
		    });

		}); 
	});
</script>
<?php 
}}
add_action("wp_footer",'wp_woo_express_footer');
add_action("admin_footer",'wp_woo_express_footer');

/*
index.php?express_pop=1
*/
function add_express_url_1() {
    add_rewrite_rule( 'express_pop/$', 'index.php?express_pop=1', 'top' );
}
add_action( 'init', 'add_express_url_1' );

function express_url_vars_1( $query_vars ) {
    $query_vars[] = 'express_pop';
    return $query_vars;
}
add_filter( 'query_vars', 'express_url_vars_1' );

function handle_express_url() {
    $custom_function = get_query_var( 'express_pop' );    
    if ( $custom_function === '1' ) {
        // 调用您的自定义函数
        
        exit;
    }
}
add_action( 'template_redirect', 'handle_express_url' );
