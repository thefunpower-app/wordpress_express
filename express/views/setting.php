<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php express_js()?>
<?php 
$all = express_support_kd();
$default = express_support_default();
$active = $_GET['tab']??$default;  
if($active){
	if(!$all || (is_array($all) && !$all[$active])){
		$active = '';
	}
}
$key = [
	
	'tx_map_key',

	'baidu_nlp_app_id',
  'baidu_nlp_app_key',
  'baidu_nlp_app_secret',
  'express_sf_default_goods_name',

  'express_sf_hide_menu',
	'express_sf_hide_top_menu',
	'express_sf_clean_dash',
	
  'pusher_app_key',
	'pusher_app_secret',
	'pusher_app_id',
	'pusher_app_cluster', 

	'express_support_enter_billcode',

]; 
$vue_key = [];
$express_type = '';
$vue = new Vue();
$vue->data("pay_method","[]");
$vue->data("express_type","[]");
?>

<div class="wrap" id="app"> 
	<h1 class="wp-heading-inline">配置
		<span style="font-size:12px;margin-left: 10px; color:#2271b1;cursor:pointer;" @click="show_kd">选择快递</span>
		<span style="font-size:12px;margin-left: 10px; color:#2271b1;cursor:pointer;" @click="show_config">系统配置</span>
	</h1> 

	<el-row> 
	  <el-col :span="20"> 

	  		<h2 class="nav-tab-wrapper"  id="sortable">
				<?php  
				foreach($all as $k=>$v){
					if(!$v){continue;}
				?>
			    <a href="?page=express_set_link&tab=<?=$k?>" class="sort nav-tab <?php if($active == $k){?>nav-tab-active<?php } ?>" tag="<?=$k?>" ><?=$v?></a>
			    <?php }?>
			</h2>
			<?php if(!$all){?>
			  <br>
	  		  <el-alert :closable="false" show-icon
			    title="请点击【选择快递】，加入支持的快递！"
			    type="error">
			  </el-alert>
	  		<?php }?>
			<?php  
			if($active){
				$file = __DIR__.'/setting_'.$active.'.php';
				$flag = true;
				if(file_exists($file)){
					include $file;
				}else{
					echo "<p>功能完善中，敬请期待……</p>";
					$flag = false;
				}	 
			?>  
			
		  	<p class="submit">
		  		<?php if($flag){?>
			  		<input type="button"  name="submit" class="button button-primary" @click="save" value="保存配置">
			  	<?php }else{?>
			  		<input type="button" disabled name="submit" class="button button-primary" value="保存配置">
			  	<?php }?>
		  	</p>
		  	<?php }?>
	  </el-col>
	</el-row>


<el-dialog
  title="选择快递支持的快递" :close-on-click-modal="false"
  :visible.sync="show_select"
  width="500px" > 
	<div>
	  <?php $all = express_support_kd_full(true); 
	  $c = get_config("express_support"); 
	  ?> 
	  <ul class="support">
	  <?php foreach($all as $k=>$v){?>
	  	<li>
	  		<div class="flex_between">	
	  			<div><?=$v?></div>
	  			<?php if($c && is_array($c) && in_array($k,$c)){?>
	  				<div class="del" @click="del('<?=$k?>')">移除</div>
	  			<?php }else{?>  
		  			<div class="add" @click="add('<?=$k?>')">加入</div>
		  		<?php }?>
	  		</div>
	  	</li>
	  <?php }?>
	  </ul>
	</div>
</el-dialog>

<el-dialog :close-on-click-modal="false" top="60px"
  title="配置"
  :visible.sync="is_config"
  width="500px" > 
	<div>
	  	<table class="form-table">
				<tbody>  
					<tr class="">
						<th>
							<label for="baidu_nlp_app_id">收货地址智能识别</label>
						</th>
						<td>
							 
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="baidu_nlp_app_id">百度云AppID</label>
						</th>
						<td>
							<el-input id="baidu_nlp_app_id"  v-model="form.baidu_nlp_app_id"  style="width: 300px;" size="small"  > </el-input>
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="baidu_nlp_app_key">百度云API Key</label>
						</th>
						<td>
							<el-input id="baidu_nlp_app_key"  v-model="form.baidu_nlp_app_key"  style="width: 300px;" size="small"  > </el-input>
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="baidu_nlp_app_secret">百度云Secret Key</label>
						</th>
						<td>
							<el-input id="baidu_nlp_app_secret"  v-model="form.baidu_nlp_app_secret"  style="width: 300px;" size="small"  > </el-input>
						</td>
					</tr>  

					<tr class="">
						<th>
							<label for="baidu_nlp_app_id">实时订单更新</label>
						</th>
						<td>
							 www.pusher.com
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="pusher_app_key">pusher_app_key</label>
						</th>
						<td>
							<el-input id="pusher_app_key"  v-model="form.pusher_app_key"  style="width: 300px;" size="small"  > </el-input>
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="pusher_app_secret">pusher_app_secret</label>
						</th>
						<td>
							<el-input id="pusher_app_secret"  v-model="form.pusher_app_secret"  style="width: 300px;" size="small"  > </el-input>
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="pusher_app_id">pusher_app_id</label>
						</th>
						<td>
							<el-input id="pusher_app_id"  v-model="form.pusher_app_id"  style="width: 300px;" size="small"  > </el-input>
						</td>
					</tr>   
					<tr class="">
						<th>
							<label for="pusher_app_cluster">pusher_app_cluster</label>
						</th>
						<td>
							<el-input id="pusher_app_cluster"  v-model="form.pusher_app_cluster"  style="width: 300px;" size="small"  > </el-input>
						</td>
					</tr> 

					<tr class="">
						<th>
							<label for="baidu_nlp_app_id">常用配置</label>
						</th>
						<td>
							 
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="express_sf_default_goods_name">物品名称</label>
						</th>
						<td>
							<el-input id="express_sf_default_goods_name"  v-model="form.express_sf_default_goods_name"  style="width: 300px;" size="small"  > </el-input>
						</td>
					</tr>  
					
					<tr class="">
						<th>
							<label for="express_support_enter_billcode">支持先录入订单</label>
						</th>
						<td>
							<el-checkbox id="express_support_enter_billcode" true-label="1" false-label="0" v-model="form.express_support_enter_billcode"   > </el-checkbox>
						</td>
					</tr> 

					<tr class="">
						<th>
							<label for="express_sf_clean_dash">清空后台面板组件</label>
						</th>
						<td>
							<el-checkbox id="express_sf_clean_dash" true-label="1" false-label="0" v-model="form.express_sf_clean_dash"   > </el-checkbox>
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="express_sf_hide_top_menu">隐藏顶部菜单</label>
						</th>
						<td>
							<el-checkbox id="express_sf_hide_top_menu" true-label="1" false-label="0" v-model="form.express_sf_hide_top_menu"   > </el-checkbox>
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="express_sf_hide_menu">隐藏左侧菜单</label>
						</th>
						<td>
							<el-checkbox id="express_sf_hide_menu" true-label="1" false-label="0" v-model="form.express_sf_hide_menu"   > </el-checkbox>
						</td>
					</tr>  


				</tbody>
			</table>
			<p>
				<input type="button"  name="submit" class="button button-primary" @click="save" value="保存配置">
			</p>
	</div>
</el-dialog>

	

</div>

<?php 
if($vue_key){
	$key = array_merge($key,$vue_key);
}
$express_type = ucfirst($express_type)?:'Sf';
$vue->created(["get_express_type()"]);
$vue->method("get_express_type()","
	$.post('".admin_url( 'admin-ajax.php' )."',{action:'express_get_type',key:'".$express_type."'}, function(res) { 
		if(res.code == 0)   {
			_this.pay_method   = res.data.pay_method;
		 	_this.express_type = res.data.express_type;
		}else {
			_this.pay_method = [];
			_this.express_type = []; 
		}
		 
	},'json'); 
");

$vue->data("is_sandbox",true);
$vue->created(['get()']);
$vue->method("get()"," 
$.post('".admin_url( 'admin-ajax.php' )."',".json_encode(['key'=>$key,'action'=>'express_get_config']).", function(res) {    
	 _this.form = res.data;
},'json');
");
$vue->method("save()","
console.log(this.form)
this.form.action = 'express_save_config';
$.post('".admin_url( 'admin-ajax.php' )."',this.form, function(res) {    
	console.log(res);
	".vue_message()."
	_this.is_config = false;
},'json');
");

$vue->method("add(k)"," 
$.post('".admin_url( 'admin-ajax.php' )."',{action:'express_add_support',key:k}, function(res) {     
	".vue_message()."
	setTimeout(()=>{
		window.location.reload();
	},1000);
},'json');
");
$vue->method("del(k)"," 
$.post('".admin_url( 'admin-ajax.php' )."',{action:'express_del_support',key:k}, function(res) {     
	".vue_message()."
	setTimeout(()=>{
		window.location.reload();
	},1000);
},'json');
");
$vue->data("show_select",false);
$vue->method("show_kd()","
	this.show_select = !this.show_select;
");
$vue->data("is_config",false);
$vue->method("show_config","
	this.is_config = true;
");
?>

<script type="text/javascript">
	<?=$vue->run()?>

	$( function() {
    $( "#sortable" ).sortable({
    	stop: function( event, ui ) {
    		let list = [];
    		$("#sortable a.sort").each(function() {
    		   list.push($(this).attr('tag'));
    		});
    		$.post("<?=admin_url( 'admin-ajax.php' )?>",{data:list,action:'express_save_sortable'});
    	}
    });
  } ); 

</script>

<style type="text/css">
	.support li{
		height:30px;
		line-height: 30px;
		border-bottom: 1px solid #ccc;
	}
	.support{
		width:80%;
	}
	.support .flex_between{
		display: flex;
		justify-content:space-between;
		align-items:center;
	}
	.support .flex_between .add{color:#2271b1;cursor:pointer;}
	.support .flex_between .del{color:red;cursor:pointer;}
</style>