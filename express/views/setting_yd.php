<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<h3>http://open.yundaex.com</h3>

<table class="form-table">
	<tbody>
		<tr class="">
			<th>
				<label for="num" title="">韵达白马账号</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_yd_customer_key"  > 
			</td>
		</tr> 
		<tr class="">
			<th>
				<label for="num" title="">韵达白马账号的联调密码</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_yd_customer_secret"  > 
			</td>
		</tr>  
		<tr class="">
			<th>
				<label for="sandbox">沙盒</label>
			</th>
			<td>
				<el-checkbox id="sandbox" true-label="1" false-label="0" v-model="form.express_yd_status"   > </el-checkbox> 
			</td>
		</tr> 
		<template v-if="form.express_yd_status == 1">
			<tr class="">
				<th>
					<label for="num" title="">沙箱appKey</label>
				</th>
				<td>
					<input id="num" type="text" v-model="form.express_yd_sandbox_key"  > 
				</td>
			</tr> 
			<tr class="">
				<th>
					<label for="num" title="">沙箱appSecret</label>
				</th>
				<td>
					<input id="num" type="text" v-model="form.express_yd_sandbox_secret"  > 
				</td>
			</tr>  
		</template>
		<template v-else>
		<tr class="">
			<th>
				<label for="num" title="">appKey</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_yd_key"  > 
			</td>
		</tr> 
		<tr class="">
			<th>
				<label for="num" title="">appSecret</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_yd_secret"  > 
			</td>
		</tr>  
		</template>

		
		 
		<tr class="" >
			<th>
				<label for="num2">默认付款方式<span style="color:red;">*</span></label>
			</th>
			<td>  
				<el-radio size="small" v-model="form.express_yd_default_pay_method" :label="k" border v-for="(v,k) in pay_method">{{v}}</el-radio> 
			</td>
		</tr>  
		<tr class="" >
			<th>
				<label for="sandbox_code2">默认产品类别<span style="color:red;">*</span></label>
			</th>
			<td>
				<el-select size="small"  v-model="form.express_yd_default_express_type_id" filterable placeholder="请选择">
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
$express_type = 'yd';  
$vue_key = [ 
	'express_yd_customer_key',
	'express_yd_customer_secret',
	'express_yd_status',
	'express_yd_sandbox_key',
	'express_yd_key',
	'express_yd_sandbox_secret',
	'express_yd_secret',
	'express_yd_printer', 
	'express_yd_hide_menu',
	'express_yd_hide_top_menu',
	'express_yd_clean_dash',
	'express_yd_default_pay_method',
	'express_yd_default_express_type_id', 
];
?>