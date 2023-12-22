<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<h3>https://open.yto.net.cn/</h3>
 
<table class="form-table">
	<tbody>
		<tr class="">
			<th>
				<label for="num" title="">顾客编码</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_yt_customer_key"  > 
			</td>
		</tr> 
		<tr class="">
			<th>
				<label for="num" title="">客户密钥</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_yt_customer_secret"  > 
			</td>
		</tr>  
		<tr class="">
			<th>
				<label for="sandbox">沙盒</label>
			</th>
			<td>
				<el-checkbox id="sandbox" true-label="1" false-label="0" v-model="form.express_yt_status"   > </el-checkbox> 
			</td>
		</tr> 
		 
		<tr class="" >
			<th>
				<label for="num2">默认付款方式<span style="color:red;">*</span></label>
			</th>
			<td>  
				<el-radio size="small" v-model="form.express_yt_default_pay_method" :label="k" border v-for="(v,k) in pay_method">{{v}}</el-radio> 
			</td>
		</tr>  
		<tr class="" >
			<th>
				<label for="sandbox_code2">默认产品类别<span style="color:red;">*</span></label>
			</th>
			<td>
				<el-select size="small"  v-model="form.express_yt_default_express_type_id" filterable placeholder="请选择">
				    <el-option
				      v-for="(v,k) in express_type"
				      :key="k"
				      :label="v"
				      :value="k">
				    </el-option>
				</el-select>  
			</td>
		</tr> 

	</tbody>
</table>

<?php 
$express_type = 'yt';  
$vue_key = [ 
	'express_yt_customer_key',
	'express_yt_customer_secret',
	'express_yt_status',
	'express_yt_sandbox_key',
	'express_yt_key',
	'express_yt_printer',  
	'express_yt_default_pay_method',
	'express_yt_default_express_type_id', 
];
?>