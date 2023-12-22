<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<h3>https://open.jtexpress.com.cn/</h3>

<table class="form-table">
	<tbody> 
		<tr class="">
			<th>
				<label for="sandbox">沙盒</label>
			</th>
			<td>
				<el-checkbox id="sandbox" true-label="1" false-label="0" v-model="form.express_jt_status"   > </el-checkbox> 
			</td>
		</tr> 
		<template v-if="form.express_jt_status == 1">
			<tr class="" >
				<th>
					沙箱apiAccount
				</th>
				<td>
					<input type="text" id="sandbox_code" v-model="form.express_jt_sandbox_key" > 
				</td>
			</tr> 
			<tr class="" >
				<th>
					privateKey
				</th>
				<td>
					<input type="text" id="sandbox_code" v-model="form.express_jt_sandbox_private_key" > 
				</td>
			</tr> 

			<tr class="" >
				<th>
					客户编码
				</th>
				<td>
					<input type="text" id="sandbox_code" v-model="form.express_jt_sandbox_customer_code" > 
				</td>
			</tr> 
			<tr class="" >
				<th>
					密码
				</th>
				<td>
					<input type="text" id="sandbox_code" v-model="form.express_jt_sandbox_pwd" > 
				</td>
			</tr>  
		
		</template>
		<template v-else>
			<tr class="" >
				<th title="极兔开放平台应用唯一身份标识。">
					apiAccount
				</th>
				<td>
					<input type="text" id="sandbox_code" v-model="form.express_jt_key" > 
				</td>
			</tr> 
			<tr class="" title="极兔开放平台应用唯一认证key。" >
				<th>
					privateKey
				</th>
				<td>
					<input type="text" id="sandbox_code" v-model="form.express_jt_private_key" > 
				</td>
			</tr> 

			<tr class="" >
				<th title="客户唯一编码。客户编码和客户密码可联系出货网点提供">
					客户编码
				</th>
				<td>
					<input type="text" id="sandbox_code" v-model="form.express_jt_customer_code" > 
				</td>
			</tr> 
			<tr class="" title="网点为客户开通的客户编码对应的密码。" >
				<th>
					客户密码
				</th>
				<td>
					<input type="text" id="sandbox_code" v-model="form.express_jt_pwd" > 
				</td>
			</tr>
		</template> 
		<tr class="">
			<th>
				<label for="num2">默认配置</label>
			</th>
			<td>
				 
			</td>
		</tr>  
		<tr class="" >
			<th>
				<label for="num2">默认付款方式<span style="color:red;">*</span></label>
			</th>
			<td>  
				<el-radio size="small" v-model="form.express_jt_default_pay_method" :label="k" border v-for="(v,k) in pay_method">{{v}}</el-radio> 
			</td>
		</tr>  
		<tr class="" >
			<th>
				<label for="sandbox_code2">默认产品类别<span style="color:red;">*</span></label>
			</th>
			<td>
				<el-select size="small"  v-model="form.express_jt_default_express_type_id" filterable placeholder="请选择">
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
$express_type = 'jt'; 
$vue_key = [
	'express_jt_status',
	'express_jt_sandbox_key',
	'express_jt_key',
	'express_jt_sandbox_customer_code',
	'express_jt_customer_code',
	'express_jt_sandbox_pwd',
	'express_jt_pwd',
	'express_jt_sandbox_private_key', 
	'express_jt_private_key', 
	'express_jt_default_pay_method',
	'express_jt_default_express_type_id',
]; 
?>