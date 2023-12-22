<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<h3>https://open.zto.com/</h3>

<table class="form-table">
	<tbody>
		<tr class="">
			<th>
				<label for="num" title="">电子面单账号</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_zto_customer_key"  > 
			</td>
		</tr> 
		<tr class="">
			<th>
				<label for="num" title="">电子面单密码</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_zto_customer_secret"  > 
			</td>
		</tr>  
		<tr class="">
			<th>
				<label for="num" title="">网点【如 上海徐汇】</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_zto_site_code"  > 
			</td>
		</tr>  

		<tr class="">
			<th>
				<label for="sandbox">沙盒</label>
			</th>
			<td>
				<el-checkbox id="sandbox" true-label="1" false-label="0" v-model="form.express_zto_status"   > </el-checkbox> 
			</td>
		</tr> 
		<template v-if="form.express_zto_status == 1">
			<tr class="">
				<th>
					<label for="num" title="">沙箱appKey</label>
				</th>
				<td>
					<input id="num" type="text" v-model="form.express_zto_sandbox_key"  > 
				</td>
			</tr> 
			<tr class="">
				<th>
					<label for="num" title="">沙箱appSecret</label>
				</th>
				<td>
					<input id="num" type="text" v-model="form.express_zto_sandbox_secret"  > 
				</td>
			</tr>  
		</template>
		<template v-else>
		<tr class="">
			<th>
				<label for="num" title="">appKey</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_zto_key"  > 
			</td>
		</tr> 
		<tr class="">
			<th>
				<label for="num" title="">appSecret</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_zto_secret"  > 
			</td>
		</tr>  
		</template> 
		 
		<tr class="" >
			<th>
				<label for="num2">默认付款方式<span style="color:red;">*</span></label>
			</th>
			<td>  
				<el-radio size="small" v-model="form.express_zto_default_pay_method" :label="k" border v-for="(v,k) in pay_method">{{v}}</el-radio> 
			</td>
		</tr>  
		<tr class="" >
			<th>
				<label for="sandbox_code2">默认产品类别<span style="color:red;">*</span></label>
			</th>
			<td>
				<el-select size="small"  v-model="form.express_zto_default_express_type_id" filterable placeholder="请选择">
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
$express_type = 'zto';  
$vue_key = [ 
	'express_zto_customer_key',
	'express_zto_customer_secret',
	'express_zto_status',
	'express_zto_sandbox_key',
	'express_zto_key',
	'express_zto_sandbox_secret',
	'express_zto_secret', 
	'express_zto_default_pay_method',
	'express_zto_default_express_type_id', 
	'express_zto_code',
	'express_zto_site_code',
];
?>