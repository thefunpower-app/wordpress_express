<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php   
$vue = new Vue();
express_js();
$id = $_GET['id'];

$default = express_support_default();
$all = express_support_kd();
if($all){
	$count = count($all);	
}else{
	$count = 0;
} 
if($id){
	$one = db_get_one("express_order","*",['id'=>$id]);
}
?>


<div class="wrap" id="app"> 
	<!-- <h1 class="wp-heading-inline">
		<?php if($id){?> 修改寄件 <?php }else{?>
			寄件
		<?php }?>
		<a href="/wp-admin/admin.php?page=express/wordpress.php" class="page-title-action"><?= __("返回")?></a>
	</h1>  -->

	<?php if($count == 0){?>
	  <br>
	  <el-alert :closable="false" show-icon
	    title="未配置支持的快递，请先完成配置！"
	    type="error">
	  </el-alert>
	<?php }else{?>
	
	
	
		<el-row>
		  <el-col :span="12">
		  	<h2 class="wp-heading-inline"><span title="收件人信息" style="background:red;border-radius: 50%;padding:5px 8px;color:#FFF;">收</span>
		  		<el-button style="margin-left: 10px;" @click="open_address_list" size="small" >地址簿</el-button>
		  		<el-button style="margin-left: 10px;" @click="open_address(1)" size="small">智能填写</el-button>
		  	</h2> 
			<table class="form-table">
				<tbody>
					<tr class="">
						<th>
							<label for="card">公司名称</label>
						</th>
						<td>
							<el-input size="small" id="card" type="text"  v-model="receiver.com_title"  > </el-input>
						</td>
					</tr>  
					
					<tr class="">
						<th>
							<label for="card">收件人<span style="color:red;">*</span></label>
						</th>
						<td>
							<el-input size="small" id="card" type="text"  v-model="receiver.contact"  > </el-input>
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="num">联系电话<span style="color:red;">*</span></label>
						</th>
						<td>
							<el-input size="small" id="num" type="text"  v-model="receiver.mobile" > </el-input>
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="province">省市区<span style="color:red;">*</span></label>
						</th>
						<td>
							<el-cascader filterable id="province" size="small" v-model="receiver.arr" :options="cascader" 
							placeholder="省市区" style="width:300px;"
						       :props="{value:'label'}">
						     <template #default="{  data }">
						       <span>{{ data.label }}</span>
						     </template>
						   </el-cascader>  
						</td>
					</tr>  
					<tr class="" >
						<th>
							<label for="sandbox_code">详细地址<span style="color:red;">*</span></label>
						</th>
						<td>
							<el-input size="small" type="text" id="sandbox_code" v-model="receiver.address" style="width:300px;" > </el-input>
						</td>
					</tr> 
					 
				</tbody>
			</table>
		  </el-col>
		  <el-col :span="12">
		  	<h2 class="wp-heading-inline"><span title="寄件人信息" style="background:#333;border-radius: 50%;padding:5px 8px;color:#FFF;">寄</span>
		  		<el-button style="margin-left: 10px;" @click="open_address(2)" size="small" >智能填写</el-button>
		  	</h2> 
			<table class="form-table">
				<tbody>
					<tr class="">
						<th>
							<label for="card1">寄件人<span style="color:red;">*</span></label>
						</th>
						<td>
							<el-input size="small" id="card1" type="text" v-model="fahuo.contact"   > </el-input>
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="num1">联系电话<span style="color:red;">*</span></label>
						</th>
						<td>
							<el-input size="small" id="num1" type="text"  v-model="fahuo.mobile" > </el-input>
						</td>
					</tr>  
					<tr class="">
						<th>
							<label for="province">省市区<span style="color:red;">*</span></label>
						</th>
						<td>
							<el-cascader filterable id="province" size="small" v-model="fahuo.arr" :options="cascader" 
							placeholder="省市区" style="width:300px;"
						       :props="{value:'label'}">
						     <template #default="{  data }">
						       <span>{{ data.label }}</span>
						     </template>
						   </el-cascader>  
						</td>
					</tr>  
					<tr class="" >
						<th>
							<label for="sandbox_code1">详细地址<span style="color:red;">*</span></label>
						</th>
						<td>
							<el-input size="small" type="text" id="sandbox_code1" v-model="fahuo.address" style="width:300px;" > </el-input>
						</td>
					</tr> 
					 
				</tbody>
			</table>
		  </el-col>
		</el-row>
		<el-row>
		  	<el-col :span="20">
				<h2 class="wp-heading-inline">物品信息</h2> 
				<table class="form-table">
					<tbody>
						<tr class="">
							<th>
								<label for="card2">物品名称<span style="color:red;">*</span></label>
							</th>
							<td>
								<div style="display:flex;align-items: center;">
								<el-input size="small" id="card2" type="text" v-model="form.name"   > </el-input> 
									<?php 
										$goods = get_config("express_sf_default_goods_name");
										if($goods){
											$goods = string_to_array($goods);
										}
										if($goods){
									?>
									<div style="margin-left: 5px;">
										<?php 
											foreach($goods as $v){
										?> 
										<el-tag style="cursor:pointer;" @click="set_goods('<?=trim($v)?>')"><?=$v?></el-tag>
										<?php }?>
									</div>
									<?php }?> 
								</div>
							</td>
						</tr>

						<tr class="" v-if="is_sign_back">
							<th>
								<label for="num">签回单 </label>
							</th>
							<td>  
								<el-radio size="small"   label="1" type="text" v-model="form.is_sign_back">是</el-radio>
								<el-radio size="small"  label="2" type="text" v-model="form.is_sign_back">否</el-radio>
							</td>
						</tr>  


						<tr class="">
							<th>
								<label for="num">包裹数量<span style="color:red;">*</span></label>
							</th>
							<td>
								<el-input size="small" :disabled="disable_num_input" ref='num' type="num" max="50" id="num" type="text" v-model="form.num"   > </el-input>
							</td>
						</tr>  

						<tr class="">
							<th>
								<label for="num2">快递公司<span style="color:red;">*</span></label>
							</th>
							<td>
								<el-radio size="small"  @change="get_express_type" v-model="form.type" :label="k" border v-for="(v,k) in type">{{v}}</el-radio> 
							</td>
						</tr>  
						<tr class="" v-if="form.type">
							<th>
								<label for="num2">付款方式<span style="color:red;">*</span></label>
							</th>
							<td> 
								
								<el-radio size="small" v-model="form.pay_method" :label="k" border v-for="(v,k) in pay_method">{{v}}</el-radio>
		 
							</td>
						</tr>  
						<tr class="" v-if="form.type" >
							<th>
								<label for="sandbox_code2">产品类别<span style="color:red;">*</span></label>
							</th>
							<td>
								<el-select size="small"  v-model="form.express_type_id" filterable placeholder="请选择">
								    <el-option
								      v-for="(v,k) in express_type"
								      :key="k"
								      :label="v"
								      :value="k">
								    </el-option>
								</el-select>  
							</td>
						</tr> 
						<?php if(get_config('express_support_enter_billcode') == 1){?>
						<tr class="" v-if="form.type">
							<th>
								<label for="y_order_num" title="已有运单号且未被使用">运单号</label>
							</th>
							<td>   
		 						<el-input size="small"  id="y_order_num" type="text" v-model="form.y_order_num"> </el-input> 
							</td>
						</tr>   
						<?php }?>
					</tbody>
				</table>	
			</el-col>
			 
		</el-row> 
		<div class="submit" style="position: absolute;bottom:5px;"> 
			<?php if($id){?>
				<?php if($one['status'] == 100){?>
					<el-button type="info"  disabled  name="submit" size="small" >订单已完成</el-button>
				<?php }else{?>
					<?php if($one['status'] >= 10){?>
						<el-button type="info"  disabled  name="submit" size="small" >订单不支持修改</el-button>
					<?php }else{?>
						<el-alert :closable="false" show-icon
		            	    title="修改订单将重新生成运单号、重新打印面单！"
		            	    type="warning">
		            	</el-alert><br>
		            	<?php if($one['has_printer'] == 1){?>
		            		<el-popconfirm @confirm="save()"
								  title="面单已打印，确认修改吗？"
								>
								<el-button type="primary" slot="reference" size="small" >
							  	 面单已打印，确认修改吗？
							  	</el-button>
							</el-popconfirm>
						<?php }else{?> 
				  			<el-button type="primary" v-if="!disabled" @click="save" name="submit" size="small" style="margin-right:20px;" > 
			            		修改
					  		</el-button> 
				  		<?php }?>

			            
					  	<el-button type="info" v-else disabled  name="submit" size="small" >数据提交中……</el-button>
					<?php }?>
				 <?php }?>
			<?php }else {?> 
				<div v-if="cur_auto_printer">
					<el-button type="primary" v-if="!disabled" @click="save" name="submit" size="small" style="margin-right:20px;" > 
			  			保存，面单将自动打印 
				  	</el-button>
				  	<el-button type="info" v-else disabled  name="submit" size="small" >数据提交中……</el-button>
				</div>
				<div v-else>
					<el-button type="" v-if="!disabled" @click="save" name="submit" size="small" style="margin-right:20px;" > 
			  			保存 
				  	</el-button>
				  	<el-button type="info" v-else disabled  name="submit" size="small" >数据提交中……</el-button>
				  	<el-button type="primary" v-if="!disabled" @click="save_and_printer" name="submit" size="small" style="margin-right:20px;" > 
			  			保存并打印 
				  	</el-button>
				</div>
                
            <?php }?>
           
		  	

		  	<?php if($id && $one['status'] < 10){?>
		  	<el-popconfirm @confirm="del()"
			  title="确定取消吗？"
			> 
			  <el-button type="danger" slot="reference" size="small" style="margin-left: 30px;" >
			  	<?php if($one['has_printer'] == 1){?>
			  		取消订单（面单已打印，请谨慎操作！）
				<?php }else{?>
				  	取消订单
				<?php }?>
				</el-button>
			</el-popconfirm> 
		  	<?php }?>

	  	</div> 
	  <?php }?>

	<el-dialog :close-on-click-modal="false"
	  title="智能填写"
	  :visible.sync="is_open_address"
	  width="500px" >
	  <div style="position:relative;">
		  <el-button size="small" type="warning" @click="get_paster()" style="position: absolute;bottom: 10px;right: 50px;">粘贴自剪切板</el-button>
		  <textarea ref="address" rows="10" cols="60" placeholder="粘贴信息，自动拆分姓名、电话和地址
示例：顺小，139********，广东省深圳市南山区xx路xx号2栋C座106" v-model="form_address"></textarea> 
	  </div>
	  <p style="color: red;margin-top:10px;">识别后，请检查拆分地址信息是否准确，如有遗漏请及时补充!</p>
	  <div style="margin-top:10px;text-align:center;">
	  	<el-button style="width: 200px;" type="button" v-if="form_address" @click="save_address" name="submit" class="button button-primary" >识别</el-button> 
	  	<el-button style="width: 200px;" disabled type="button" v-else @click="save_address" name="submit" class="button button-primary" >识别 </el-button>
	  </div>
	</el-dialog>

	<el-dialog
	  title="地址簿" :close-on-click-modal="false"
	  :visible.sync="is_address_list"
	  width="1200px" > 
	   <div style="margin-bottom: 10px;">
	   	<el-input  autoComplete='off' size="small" style="width:300px;" v-model="where_address.wq" placeholder="输入收件人、电话或地址进行搜索" type="text"	@input="get_address_list"></el-input>
	   	<el-button size="small" @click="get_address_list" type="primary" style="margin-left: 20px;">搜索</el-button>
	   	<el-button size="small" @click="reset" type="info">重置</el-button>
	   	<el-button size="small" @click="add_new_addr" type="primary">添加</el-button>
	   	<el-button size="small" @click="import_excel" type="warning">导入Excel</el-button>
	   	<el-button size="small" @click="download" type="info">下载Excel模板</el-button>
	   </div>
	   <el-table  @cell-click="click_table"  :height="h"
	    :data="address_list"  
	    border
	    style="width: 100%">
	    <el-table-column
	      prop="com_title"
	      label="公司名称"
	      width="250">
	    </el-table-column>
	    
	    <el-table-column
	      prop="contact"
	      label="收件人"
	      width="100">
	    </el-table-column>
	    <el-table-column
	      prop="mobile"
	      label="联系电话"
	      width="130">
	    </el-table-column> 
	    <el-table-column
	      prop="address"
	      label="地址">
	      <template slot-scope="scope">
	      	{{scope.row.province}}{{scope.row.county}}{{scope.row.address}}
	      </template>
	    </el-table-column>
	    <el-table-column
	      prop="province"
	      label="操作"
	      width="80">
	      <template slot-scope="scope"> 
	      	<el-button type="text" @click.stop="edit(scope.row)">编辑</el-button> 
	      </template>
	    </el-table-column>
	  </el-table>

	  <p>
	  	<el-pagination page-size="20" :current-page="where_address.page"
		  background @current-change="change"
		  layout="prev, pager, next"
		  :total="total">
		</el-pagination>
	  </p>


	</el-dialog>



	<el-dialog :close-on-click-modal="false"
	  title="地址簿"
	  :visible.sync="is_edit_address"
	  width="800px" >
		  <table class="form-table">
			<tbody>
				<tr class="">
					<th>
						<label for="com">公司名称</label>
					</th>
					<td>
						<el-input size="small" id="com" type="text"  v-model="row.com_title"  > </el-input>
					</td>
				</tr>
				<tr class="">
					<th>
						<label for="card1">收件人<span style="color:red;">*</span></label>
					</th>
					<td>
						<el-input size="small" id="card1" type="text" v-model="row.contact"   > </el-input>
					</td>
				</tr>  
				<tr class="">
					<th>
						<label for="num1">联系电话<span style="color:red;">*</span></label>
					</th>
					<td>
						<el-input size="small" id="num1" type="text"  v-model="row.mobile" > </el-input>
					</td>
				</tr>  
				<tr class="">
					<th>
						<label for="province">省市区<span style="color:red;">*</span></label>
					</th>
					<td>
						<el-cascader filterable id="province" size="small" v-model="row.arr" :options="cascader" 
						placeholder="省市区" style="width:300px;"
					       :props="{value:'label'}">
					     <template #default="{  data }">
					       <span>{{ data.label }}</span>
					     </template>
					   </el-cascader>  
					</td>
				</tr>  
				<tr class="" >
					<th>
						<label for="sandbox_code1">详细地址<span style="color:red;">*</span></label>
					</th>
					<td>
						<el-input size="small" type="text" id="sandbox_code1" v-model="row.address" style="width:300px;" > </el-input>
					</td>
				</tr> 
				 
			</tbody>
		</table>

		<div style="margin-top:10px;">
		  	<el-button type="primary" @click="save_edit"  size="small" style="margin-right: 20px;">保存</el-button> 

		  	<el-popconfirm @confirm="del_edit()" v-if='row.id'
			  title="确定删除吗？"
			>
			  <el-button type="danger" slot="reference" size="small" style="margin-right: 20px;"  >删除</el-button>
			</el-popconfirm> 

		  	<el-button type="info" @click="close_edit" name="submit" size="small" >取消</el-button> 
		</div>
	</el-dialog>


	<el-dialog :close-on-click-modal="false"
	  title="导入地址簿"
	  :visible.sync="show_import"
	  width="1200px" >
	  	  <h3>预计以下收件地址将导入系统</h3>
		  <el-table   :height="h"
		    :data="import_list"  
		    border
		    style="width: 100%">
		    <el-table-column
		      type="index"
		      label="序号"
		      width="100">
		    </el-table-column> 
		    <el-table-column
		      prop="收件公司"
		      label="收件公司"
		      width="250">
		    </el-table-column> 
		    <el-table-column
		      prop="收件人"
		      label="收件人"
		      width="100">
		    </el-table-column>
		    <el-table-column
		      prop="收件人手机"
		      label="收件人手机"
		      width="130">
		    </el-table-column> 
		    <el-table-column
		      prop="收件人地址"
		      label="收件人地址"> 
		    </el-table-column>
		    <el-table-column
		      prop="province"
		      label="操作"
		      width="80">
		      <template slot-scope="scope"> 
		      	<el-button type="text" @click="import_del(scope.$index)">删除</el-button> 
		      </template>
		    </el-table-column>
		  </el-table>

		<div style="margin-top:10px;">
		  	<el-button type="info" v-if="on_import" disabled  size="small" style="margin-right: 20px;">数据导入中，可正常关闭窗口执行其他操作</el-button>  
		  	<el-button type="primary" v-else @click="import_save"  size="small" style="margin-right: 20px;">确认导入数据</el-button>  
		</div>
	</el-dialog>

	<input type="file" id="fileInput" style="display:none;" accept=".xls,.xlsx"/>


	<el-dialog :close-on-click-modal="false"  
	  title="选择打印机"
	  :visible.sync="show_printer"
	  width="300px" 
	  > 
	  <br> 
	  <el-select v-model="selected_printer" placeholder="请选择"  >
	    <el-option
	      v-for="item in printers"
	      :key="item.name"
	      :label="item.name"
	      :value="item.name">
	    </el-option>
	  </el-select>

	  <p>
	  	<el-button @click="do_printer" type="primary"	>确认打印</el-button>
	  </p>
		
	</el-dialog> 

</div>




<?php 
$vue->data("disable_num_input",false);
$load_script = '';
$receiver     = $_GET['receiver'];
$woo_order_id = $_GET['woo_order_id']?:'';
$vue->data("woo_order_id",$woo_order_id);
if($receiver){
	$receiver = json_decode(base64_decode(urldecode($receiver)),true);
	$address = $receiver['address'];
	if($address){
		$arr = get_baidu_nlp_address($address); 
		$receiver['address'] = $arr['street'];
		$receiver['arr'] = [$arr['province'],$arr['city'],$arr['region']];
		$load_script .="
			this.receiver = ".json_encode($receiver).";
		";
	} 
}
$vue->method("set_goods(v)","
this.\$set(this.form,'name',v);
");
$vue->method("download()","
	window.open('/wp-content/plugins/express/tool/template/CustomerAddress.xlsx');
");
$vue->data("h","");
$vue->data("disabled",false);
$vue->data("fahuo","{}");
$vue->data("row","{}");
$vue->data("total","");
$vue->data("receiver","{}");
$vue->data("pay_method","[]");
$vue->data("express_type","[]");
$type_arr = express_support_kd();
$first_type =  '';
foreach($type_arr as $k=>$v){
	if(!$first_type){
		$first_type = $k;
	}
} 
$vue->data("type",json_encode($type_arr)); 
$vue->data("cascader","[]");
$vue->data("is_open_address",0);
$vue->data("form_address","");
$vue->data("wq","");
$vue->data("is_address_list",false);
$vue->data("address_list",false);
$vue->data("is_edit_address",false);
$vue->data("where_address","{page:1}");
$vue->data("has_printers",false);
$vue->data("show_printer",false);
$vue->data("printer_pdf_url","");
$vue->data("printer_pdf_url_arr","[]");
$vue->data("printers","[]");
$vue->data("is_show_printer",false);
$vue->method("reset()","
	this.where_address.page = 1;
	this.where_address.wq = '';
	this.get_address_list();
");
$vue->method("edit(v)","
	this.is_address_list = false;
	this.row = v; 
	this.is_edit_address = true;
");
$vue->method("close_edit()","
this.is_edit_address = false;
this.is_address_list = true;
this.get_address_list();
");

$vue->method("add_new_addr()","
this.is_edit_address = true;
this.row = {};
");
$vue->method("add_sub()"," 
	this.row.action = 'express_add_sub_express';
	$.post('".admin_url( 'admin-ajax.php' )."',this.row, function(res) {   
		 ".vue_message()."
	},'json');   
");

$vue->method("save_edit()"," 
	this.row.action = 'express_save_address';
	$.post('".admin_url( 'admin-ajax.php' )."',this.row, function(res) {   
		 ".vue_message()."
		 if(res.code == 0){
		 	_this.close_edit(); 
		 }
	},'json');   
");
$vue->method("del_edit()"," 
	this.row.action = 'express_del_address';
	$.post('".admin_url( 'admin-ajax.php' )."',this.row, function(res) {   
		 ".vue_message()."
		 if(res.code == 0){
		 	_this.close_edit(); 
		 }
	},'json');   
");
$vue->method("open_address_list()","
	this.is_address_list = true;
	this.where_address = {page:1};
	this.get_address_list();
");
$vue->method("get_address_list()"," 
	this.where_address.action = 'express_get_address_list';
	$.post('".admin_url( 'admin-ajax.php' )."',this.where_address, function(res) {   
		_this.address_list = res.data; 
		_this.total = res.total; 
	},'json');   
");
$vue->method("change(e)","
	this.where_address.page = e;
	this.get_address_list(); 
");
$vue->method("click_table(row)"," 
	_this.\$set(_this.receiver,'contact',row.contact);
	_this.\$set(_this.receiver,'mobile',row.mobile);
	_this.\$set(_this.receiver,'arr',row.arr); 
	_this.\$set(_this.receiver,'address',row.address); 
	_this.\$set(_this.receiver,'com_title',row.com_title); 
	_this.is_address_list = false; 
");

$vue->method("get_cascader()"," 
	$.post('".admin_url( 'admin-ajax.php' )."',{action:'express_get_cascader'}, function(res) {   
		_this.cascader = res;  
	},'json');   
");
$vue->method("open_address(v)","
	this.is_open_address = v;
	this.\$nextTick(()=>{
		_this.\$refs['address'].select();
	});
");

$vue->method("send_pusher()"," 
	$.post('".admin_url( 'admin-ajax.php' )."',{action:'express_pusher'}, function(res) { 
 			
	},'json'); 
");

$vue->method("get_last_sender()"," 
	$.post('".admin_url( 'admin-ajax.php' )."',{action:'express_get_last_sender'}, function(res) {   
		if(res.code == 0){
			_this.fahuo = res.data;
		}
	},'json');  

");




$vue->method("save_address()"," 
	$.post('".admin_url( 'admin-ajax.php' )."',{action:'express_get_address',data:this.form_address}, function(res) {  
		if(_this.is_open_address == 1 && res.code == 0){
			_this.\$set(_this.receiver,'contact',res.data.name);
			_this.\$set(_this.receiver,'mobile',res.data.mobile);
			_this.\$set(_this.receiver,'arr',[res.data.province,res.data.city,res.data.region]); 
			_this.\$set(_this.receiver,'address',res.data.street); 
			_this.\$set(_this.receiver,'com_title',res.data.com_title);  
			_this.is_open_address = 0; 
			_this.form_address = '';
		}else if(_this.is_open_address == 2 && res.code == 0){
			_this.\$set(_this.fahuo,'contact',res.data.name);
			_this.\$set(_this.fahuo,'mobile',res.data.mobile);
			_this.\$set(_this.fahuo,'arr',[res.data.province,res.data.city,res.data.region]); 
			_this.\$set(_this.fahuo,'address',res.data.street); 
			_this.is_open_address = 0; 
			_this.form_address = '';
		}
	},'json');  

");



$jump = "";
if($id){ 
	$load_script = "
		$.post('".admin_url( 'admin-ajax.php' )."',{action:'express_get_list_one',id:".$id."}, function(res) { 
			 _this.receiver = res.data.customer_address;
			 _this.fahuo = res.data.fahuo_address;
			 _this.form = {
			 	id:res.data.id,
			 	name:res.data.name,
			 	pay_method:res.data.pay_method,
			 	express_type_id:res.data.express_type_id,
			 	type:res.data.type,
			 	num:res.data.num,
			 	y_order_num:res.data.y_order_num,
			 	is_sign_back:res.data.is_sign_back, 
			 };
			 _this.get_express_type();

		},'json');  
	";
	$jump = "
	setTimeout(function(){
		window.location.href = '/wp-admin/admin.php?page=express/wordpress.php';
	},800);
	";
}else{
	$express_rem_goods_name = cookie("express_rem_goods_name");
	if($express_rem_goods_name){
		$load_script .= "
		this.\$set(this.form,'name','".addslashes($express_rem_goods_name)."');
		";
	}

	$load_script .= " 
	this.\$set(this.form,'type','".$first_type."');
	this.\$set(this.form,'is_sign_back','2'); 
	this.get_express_type();
	";
}
$load_script .= "
this.h = ($(window).height() - 400 )+'px';
let j = Cookies.get('select_printer');
if(j){
	this.selected_printer = j;
}
this.get_auto_printer();
" ;
$vue->data("selected_printer",""); 
if(!$id){
	$load_script .="
	this.\$set(_this.form,'num',1);
	"; 
}
$vue->method("load()",$load_script);
$vue->created(['load()','get_cascader()','get_last_sender()']);
$vue->data("is_sign_back",false);
$vue->method("get_express_type()","
	if(!this.form.type){return;}
	$.post('".admin_url( 'admin-ajax.php' )."',{action:'express_get_type',key:this.form.type}, function(res) { 
		if(res.code == 0)   { 
			let d = res.data;
			_this.pay_method   = d.pay_method;
		 	_this.express_type = d.express_type;  
 			if(d.default_pay_method){ 
		 		_this.\$set(_this.form,'pay_method',d.default_pay_method);
		 	}
		 	if(d.default_express_type){
		 		_this.\$set(_this.form,'express_type_id',d.default_express_type);
		 	} 
		 	if(res.is_sign_back){
		 		app.is_sign_back = true;
		 	}else{
		 		app.is_sign_back = false;
		 		app.\$set(app.form,'is_sign_back','2');
		 	} 
		 	if(res.is_sub){
		 		app.disable_num_input = false;
		 	}else{
		 		app.\$set(app.form,'num',1);
		 		app.disable_num_input = true;
		 	}
		 	console.log('pay_method',_this.pay_method);
		}else {
			_this.pay_method = [];
			_this.express_type = []; 
			console.log('error pay_method'); 
		} 
		 
	},'json'); 
"); 
$vue->method("save()","
this.disabled = true;
this.form.action = 'express_save_express';
this.form.customer_address = this.receiver;
this.form.fahuo_address = this.fahuo; 
this.form.woo_order_id = '".$woo_order_id."';
$.post('".admin_url( 'admin-ajax.php' )."',this.form, function(res) {  
	".vue_message()."
	if(res.code == 0){
		_this.\$set(_this.form,'name','');
		_this.\$set(_this.form,'num',1);
		if(app.cur_auto_printer){
			app.printer_pdf_url = res.data.pdf_url;
			app.selected_printer = app.cur_auto_printer;
			app.do_printer();
			console.log('自动打印中……'); 
			_this.disabled = false;
		}else {
			if(_this.is_show_printer){
				_this.row = res.data;
				_this.open_printer();
				_this.send_pusher();
				_this.disabled = false;
				return;
			}else{
				_this.send_pusher();
				_this.disabled = false;
			}
			if(_this.form.woo_order_id){
				window.location.href = '/wp-admin/edit.php?post_type=shop_order';
			}
		} 
		".$jump." 
	} else{
		_this.disabled = false;
	}
},'json');
");

$vue->method("save_and_printer()","
this.is_show_printer = true;
this.save();
");

$vue->method("del()"," 
this.form.action = 'express_del_express';
this.form.customer_address = this.receiver;
this.form.fahuo_address = this.fahuo; 
$.post('".admin_url( 'admin-ajax.php' )."',this.form, function(res) { 
	".vue_message()."
	if(res.code == 0){
		_this.form = {};
		".$jump."
	} 
},'json'); 
");


$vue->method("import_excel()","
	$('#fileInput').trigger('click');
");

$vue->data('show_import',false);
$vue->data('import_list',"[]");

$vue->method("import_del(index)","
	console.log('index:'+index);
	this.import_list.splice(index,1);
");
$vue->data("on_import","false");
$vue->method("import_save()","  
this.on_import = true;
$.post('".admin_url( 'admin-ajax.php' )."',{
	action:'express_import_save',
    data:this.import_list
}, function(res) { 
	".vue_message()."
	_this.on_import = false;
	if(res.code == 0){
		_this.show_import = false;
		_this.import_list = [];
		_this.get_address_list();
	} 
},'json'); 
");
$vue->method("open_printer()","
	let row = this.row;
	this.selection_val       = [];
	this.printer_pdf_url_arr = [];
	this.selection_num = 0; 
	let pdf = row.pdf_url; 
	this.show_printer = true;
	this.printer_pdf_url = pdf;
	let express_num = row.express_num;
	let dd = []; 
	console.log(express_num);
	for(let i in express_num){
		if(express_num[i].pdf_url){
			dd.push(express_num[i].pdf_url);
		}
	}
	console.log('单个打印',dd);
	this.printer_pdf_url_arr = dd; 
	$.post('".admin_url( 'admin-ajax.php' )."',{
		action:'express_tag_has_printer',
	    data:{id:row.id}
	}, function(res) {  
	},'json'); 
");
$vue->method("do_printer()","
	 if(!this.selected_printer){
	 	this.\$message.error('请选择打印机');
	 	return;
	 }
	 Cookies.set('select_printer', this.selected_printer, { expires: 365, path: '' });
	 console.log(this.printer_pdf_url);
	 console.log('will printer pdf urls',this.printer_pdf_url_arr);
	 let cur_printer = {};
	 for(let i in this.printers){
	 	if(this.printers[i].name == this.selected_printer){
	 		cur_printer = this.printers[i];
	 	}
	 } 
	 let length = this.printer_pdf_url_arr.length; 
	 if(this.printer_pdf_url_arr && length > 0){ 
	 	for(let i in this.printer_pdf_url_arr){
	 		console.log(this.printer_pdf_url_arr[i]);
	 		window.parent.postMessage({ type: 'do_print', content:{pdf: this.printer_pdf_url_arr[i],printer:cur_printer}}, '*');
	 	}
	 }else{
	 	window.parent.postMessage({ type: 'do_print', content:{pdf: this.printer_pdf_url,printer:cur_printer}}, '*');	
	 } 
	 this.show_printer = false;
	 this.\$message.success('打印中，请注意查看');
	 setTimeout(()=>{
	 	window.location.href = '/wp-admin/admin.php?page=express/wordpress.php';
	 },1000);

");

$vue->method("get_paster()","
if(navigator.clipboard){
  	navigator.clipboard.readText()
    .then(text => {
    	console.log(text)
    	app.form_address = text;
    })
    .catch(error => console.log('获取剪贴板内容失败：', error));
} else {
  console.log('当前浏览器不支持Clipboard API');
}
");

$vue->data("cur_auto_printer","");
$vue->method("get_auto_printer"," 
	let d = {};
	d.action = 'express_get_auto_print'; 
	$.post('".admin_url( 'admin-ajax.php' )."',d, function(res) {   
		 app.cur_auto_printer = res.data;
	},'json');    
");

?> 

<script type="text/javascript">
	<?=$vue->run()?>

	$(function() {
		var fileInput = document.getElementById('fileInput');
		fileInput.addEventListener('change', function(e) {
		  var file = e.target.files[0];
		  var reader = new FileReader();
		  reader.onloadend = function() {
		    var data = new Uint8Array(reader.result);
		    var workbook = XLSX.read(data, { type: 'array' });
		    var sheet1 = workbook.Sheets['Sheet1'];
			var data = XLSX.utils.sheet_to_json(sheet1); 
			if(data && data.length > 0){
				if(!data[0]['收件人']){
					app.$message.error('模板模式异常');
					return;
				}

				app.show_import = true;
				app.import_list = data;
				app.on_import = false;
				reader = null;
			}else{
				app.$message.error('模板模式异常');
			}
		  };
		  reader.readAsArrayBuffer(file);
		}, false);
	});
 
    <?php include __DIR__.'/js_listen_printer.php';?>

</script>

<style type="text/css">
	.el-input{width:200px;}
</style>
