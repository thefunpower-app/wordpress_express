<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<h3> https://open.sf-express.com/ </h3>
<table class="form-table">
	<tbody>
		<tr class="">
			<th>
				<label for="card">月结卡号</label>
			</th>
			<td>
				<input id="card" type="text" v-model="form.express_sf_yuejie"   > 
			</td>
		</tr>  
		<tr class="">
			<th>
				<label for="num" title="业务对接->开发者对接->开发者应用">顾客编码</label>
			</th>
			<td>
				<input id="num" type="text" v-model="form.express_sf_customer_key"  > 
			</td>
		</tr> 
		
		<tr class="">
			<th>
				<label for="sandbox">沙盒</label>
			</th>
			<td>
				<el-checkbox id="sandbox" true-label="1" false-label="0" v-model="form.express_sf_status"   > </el-checkbox> 
			</td>
		</tr> 
		<tr class="" v-if="form.express_sf_status == 1">
			<th>
				<label for="sandbox_code">沙箱校验码</label>
			</th>
			<td>
				<input type="text" id="sandbox_code" v-model="form.express_sf_sandbox_key" > 
			</td>
		</tr> 
		<tr class="" v-else>
			<th>
				<label for="code">生产校验码</label>
			</th>
			<td>
				<input type="text" id="code" v-model="form.express_sf_key" > 
			</td>
		</tr> 
		<tr class="">
			<th>
				<label for="tmp">云打印模板编码</label>
			</th>
			<td>
				<input type="text"  id="tmp" v-model="form.express_sf_printer"> 
			</td>
		</tr>   

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
				<el-radio size="small" v-model="form.express_sf_default_pay_method" :label="k" border v-for="(v,k) in pay_method">{{v}}</el-radio> 
			</td>
		</tr>  
		<tr class="" >
			<th>
				<label for="sandbox_code2">默认产品类别<span style="color:red;">*</span></label>
			</th>
			<td>
				<el-select size="small"  v-model="form.express_sf_default_express_type_id" filterable placeholder="请选择">
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
$express_type = 'sf';  
$vue_key = [
	'express_sf_default_goods_name',
	'express_sf_yuejie',
	'express_sf_customer_key',
	'express_sf_status',
	'express_sf_sandbox_key',
	'express_sf_key',
	'express_sf_printer',  
	'express_sf_default_pay_method',
	'express_sf_default_express_type_id', 
];
?>