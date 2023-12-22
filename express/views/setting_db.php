<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<h3>http://dpopen.deppon.com</h3>

<table class="form-table">
	<tbody>
		<tr class="">
			<th>
				<label for="num" title="">顾客编码</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_db_customer_key"  > 
			</td>
		</tr> 
		<!-- <tr class="">
			<th>
				<label for="num" title="">客户密钥</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_db_customer_secret"  > 
			</td>
		</tr>  --> 
		<tr class="">
			<th>
				<label for="sandbox">沙盒</label>
			</th>
			<td>
				<el-checkbox id="sandbox" true-label="1" false-label="0" v-model="form.express_db_status"   > </el-checkbox> 
			</td>
		</tr> 
		<template v-if="form.express_db_status == 1">
			<tr class="">
				<th>
					<label for="num" title="">沙箱appkey</label>
				</th>
				<td>
					<input id="num" type="text" v-model="form.express_db_sandbox_key"  > 
				</td>
			</tr> 
			<tr class="">
				<th>
					<label for="num" title="">沙箱companyCode</label>
				</th>
				<td>
					<input id="num" type="text" v-model="form.express_db_sandbox_company_key"  > 
				</td>
			</tr> 
			<tr class="">
				<th>
					<label for="num" title="">沙箱sign值</label>
				</th>
				<td>
					<input id="num" type="text" v-model="form.express_db_sandbox_secret"  > 
				</td>
			</tr>  
		</template>
		<template v-else>
			<tr class="">
			<th>
				<label for="num" title="">appkey</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_db_key"  > 
			</td>
		</tr> 
		<tr class="">
			<th>
				<label for="num" title="">companyCode</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_db_company_key"  > 
			</td>
		</tr> 
		<tr class="">
			<th>
				<label for="num" title="">sign值</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_db_secret"  > 
			</td>
		</tr>  
		</template>

		
		 
		<tr class="" >
			<th>
				<label for="num2">默认付款方式<span style="color:red;">*</span></label>
			</th>
			<td>  
				<el-radio size="small" v-model="form.express_db_default_pay_method" :label="k" border v-for="(v,k) in pay_method">{{v}}</el-radio> 
			</td>
		</tr>  
		<tr class="" >
			<th>
				<label for="sandbox_code2">默认产品类别<span style="color:red;">*</span></label>
			</th>
			<td>
				<el-select size="small"  v-model="form.express_db_default_express_type_id" filterable placeholder="请选择">
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
$express_type = 'db';  
$vue_key = [ 
	'express_db_customer_key',
	'express_db_customer_secret',
	'express_db_status',
	'express_db_sandbox_key',
	'express_db_key',
	'express_db_sandbox_secret',
	'express_db_secret',  
	'express_db_default_pay_method',
	'express_db_default_express_type_id', 
	'express_db_sandbox_company_key',
	'express_db_company_key',
];
?>