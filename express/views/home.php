<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
express_js();
$vue = new Vue();
?>
<?php 
$vue->search_date = [
  '今天',
  '昨天',
  '本周',
  '上周',
  //'上上周',
  '本月',
  '上月',
  //'上上月',
  '本年'=>'今年', 
  '上年'=>'去年',
  //'上上年',
  /*'最近一个月',
  '最近两个月',
  '最近三个月',
  '第一季度', 
  '第二季度', 
  '第三季度', 
  '第四季度', */ 
]; 
//时间之间的禁止选择
//$vue->start_date = '2023-11-01';
$vue->add_date();
?>
 
<div class="wrap" id="app"> 
	<h1 class="wp-heading-inline">快递</h1> 
	<a href="/wp-admin/admin.php?page=express_add_link" class="page-title-action"><?= __("寄件")?></a>
	<hr class="wp-header-end">

	<div class="tablenav top">
		<div class="alignleft actions bulkactions"> 
			<el-select  style="width: 200px;" placeholder="状态"  @change="search" size="small" v-model="where.status">
				<el-option
			      v-for="(v,k) in status" 
			      :label="v.label"
			      :value="v.value">
			    </el-option> 
			</el-select> 
		</div>
		<div class="alignleft actions">
			<el-input  size="small" style="width:200px;" v-model="where.wq" @input="search" placeholder="订单号、收件人、手机号"> </el-input>
			<el-date-picker size="small" @change="search" v-model="where.date" value-format="yyyy-MM-dd" :picker-options="pickerOptions" size="medium" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"  >
			</el-date-picker>

			<el-button type="primary" size="small"  @click="search"  >搜索</el-button>		
			<el-button type="" size="small"  @click="reset_search"  >重置</el-button>		
			<el-button type="" size="small"  @click="export_excel">导出Excel</el-button>	 
			<el-button type="danger" size="small"  @click="export_excel_tody_img">复制今天截图</el-button>
			<el-button type="" size="small"  @click="export_excel_tody">导出今天Excel</el-button>
			<el-button type="" size="small"  @click="loop_wuliu">刷新物流信息</el-button>		 
			<el-button v-if="selection_num>0"   type="primary" size="small"  @click="do_selection">打印面单</el-button>	 
			<el-button v-if="has_printers&&cur_auto_printer" type="success" size="small"  @click="open_auto_print_set">自动打印</el-button>	
			<el-button v-if="has_printers&&!cur_auto_printer" type="" size="small"  @click="open_auto_print_set">设置自动打印</el-button>	
 	
		</div>  
	</div>  
	<br>
	<el-table   @selection-change="selection" :height="h"
	    :data="list"  
	    border
	    style="width: 100%">
	    <el-table-column  v-if="has_printers"
	      type="selection"
	      width="55">
	    </el-table-column>
	    <el-table-column fixed="left"
	      prop="index"
	      label="序号"
	      width="80">
	    </el-table-column>
	    <el-table-column fixed="left"
	      prop="wl_order_num"
	      label="快递单号"
	      width="180" style="position: relative;">
	      <template slot-scope="scope" > 
	      	<div >
	      		<i class="fa el-icon-printer" title="已打印" v-if="scope.row.has_printer==1" style="color:blue;"></i>
	      	 
		      	<span @click="click_copy(scope.row)" title="点击可复制" :class="scope.row.is_lock?'red':''" >{{scope.row.wl_order_num}}
		      		<i class="fa el-icon-document-copy" style="margin-left: 4px;"></i> 
		      	</span> 

		      	<span v-if="scope.row.sub_num > 0" style="position: absolute;top:0px;right: 30px;">
		      		子单：{{scope.row.sub_num}}个
		      	</span>

	      	</div>
	      </template>
	    </el-table-column> 
	    <el-table-column
	      
	      label="公司" :show-overflow-tooltip="true"
	      width="130">
	      <template slot-scope="scope">
	       <span :title="scope.row.customer_address.com_title">{{scope.row.customer_address.com_title}} </span>	
	      </template>
	    </el-table-column> 
	    <el-table-column
	      prop="address" width="100px" :show-overflow-tooltip="true"
	      label="收件人">
	      <template slot-scope="scope">
	       <span :title="scope.row.customer_address.com_title">{{scope.row.customer_address.contact}} </span>	
	      </template>
	    </el-table-column>
	    <el-table-column
	      prop="address" width="126px"
	      label="手机号">
	      <template slot-scope="scope">
	      	{{scope.row.customer_address.mobile}} 
	      </template>
	    </el-table-column>

	    <el-table-column
	      prop="address" :show-overflow-tooltip="true"
	      label="收件地址" width="">
	      <template slot-scope="scope"> 
	       <span >{{scope.row.customer_address.address_full}}</span>  
	      </template>
	    </el-table-column> 

	    <el-table-column
	      prop="name"
	      label="状态"
	      width="100">
	      <template slot-scope="scope"> 
            	<span  :style="'color: '+scope.row.status_color">{{scope.row.status_txt}}</span> 
	      </template>      	
	    </el-table-column> 

	    <el-table-column
	      prop="name"
	      label="快递公司"
	      width="100">
	      <template slot-scope="scope"> 
	      	<div>
           <span>{{scope.row.type_text}}</span>  
          </div>
	      </template>      	
	    </el-table-column>
	     <el-table-column
	      prop="name"
	      label="付款方式"
	      width="130">
	      <template slot-scope="scope" style="position:relative;">  
	            <span type="text" :class="'p_'+scope.row.pay_method">{{scope.row.payment_text_short}} </span> 
	          	<div style="position:absolute;top:0px;left:0px;font-size:10px;">{{scope.row.monthly_text}}</div>
         
	      </template>      	
	    </el-table-column>

	    

	    <el-table-column
	      prop="amount"
	      label="运费"
	      width="100">
	      <template slot-scope="scope" >
	      	<div v-if="scope.row.amount > 0">
	      		<el-tooltip class="item" effect="dark" :content="scope.row.amount_tip" placement="left">
				      <el-button type="text" v-if="scope.row.amount>0">￥{{scope.row.amount}}</el-button>
				      <el-button type="text" v-else style="color:red;">￥{{scope.row.amount}}</el-button>
				    </el-tooltip> 
	      	</div>
	      	
	      </template>
	    </el-table-column> 
	    
	    <el-table-column
	      prop="created_short"
	      label="创建时间"
	      width="117">
	      <template slot-scope="scope"> 
	      	<el-tooltip class="item" effect="dark" :content="scope.row.created_at" placement="left">
		      <el-button type="text" style="color:#666;"> {{scope.row.created_short}} </el-button>
		  	</el-tooltip>
		  </template>
	    </el-table-column> 
	    <el-table-column
	      prop="user_name"
	      label="创建人"
	      width="150">
	    </el-table-column>
	    <el-table-column
	      prop="sub_num"
	      label="子单号数"
	      width="80">
	      <template slot-scope="scope"> 
	      	<span v-if="scope.row.sub_num > 0" style="color:#000;">{{scope.row.sub_num}}</span>
	      	<span v-else></span>
	      </template>
	    </el-table-column>

	    <el-table-column fixed="right"
	      prop=""
	      label="操作"
	      width="80">
	      <template slot-scope="scope"> 
	      	<el-dropdown @command="dropdown_click($event,scope.row)" v-if="scope.row.status>0">
			  <span class="el-dropdown-link">
			    操作<i class="el-icon-arrow-down el-icon--right"></i>
			  </span>
			  <el-dropdown-menu slot="dropdown">
			    <el-dropdown-item command="view_wuliu">查看物流</el-dropdown-item>
			    <el-dropdown-item v-if='scope.row.pdf_url && has_printers' command="open_printer">打印面单</el-dropdown-item>
			    <el-dropdown-item v-else command="open_pdf">打印面单</el-dropdown-item>
			    <el-dropdown-item command="edit" v-if="!scope.row.is_lock">修改</el-dropdown-item>
			    <el-dropdown-item v-if="scope.row.support_add_sub" command="open_sub">设置子单数</el-dropdown-item> 
			    <el-dropdown-item v-if="scope.row.status < 10 && !scope.row.is_lock" command="close_order">取消</el-dropdown-item> 
			  </el-dropdown-menu>
			</el-dropdown> 
	      </template>
	    </el-table-column>
	</el-table>

 
	<div style="margin-top: 10px;">
		<el-pagination @current-change="change" :current-page="where.page"
		  background
		  :page-size="per_page"
		  layout="total,prev, pager, next"
		  :total="total">
		</el-pagination>
	</div>


	<el-dialog
	  title="物流信息" :close-on-click-modal="false"
	  :visible.sync="is_show"
	  width="800px" >  

	  <el-tabs  type="card" v-model="active_tab">
	    <el-tab-pane  :label="vv.mailNo" :name="vv.mailNo" v-for="vv in wuliu_list">
	    	<div v-if="vv.routes">
		  		<div v-for="v in vv.routes" style="display:flex;justify-content:space-between;align-items: center;
		  		margin-bottom: 10px;
		  		">
		  			<!--<span>{{v.acceptAddress}}</span>-->
		  			<div>{{v.remark}}</div>
		  			<div style="width: 180px;margin-left: 20px;text-align: right;">{{v.acceptTime}}</div>
		  		</div>
		  </div>
	    </el-tab-pane> 
	  </el-tabs>
 
	</el-dialog>

	<el-dialog :close-on-click-modal="false" @close="close"
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

	<el-dialog :close-on-click-modal="false"
	  title="设置子单数"
	  :visible.sync="is_sub"
	  width="300px" 
	  > 
	  <br> 
	  <h3>
	  	{{row.wl_order_num}}
	  </h3>
	  <el-input type="number" v-model="sub_num" placeholder="子单总数量"></el-input>
	  <p style="color:#333;font-size: 10px;">子单总数量为所有子单数量，如一共需要10个包裹，此处值即为9</p>
	  <p> 
	  	<el-button v-if="is_disabled_sub" disabled type="info"	>处理中</el-button>
	  	<el-button v-else @click="add_sub" type="primary"	>确认</el-button>
	  </p>
 	
	</el-dialog>

	<input type="" name="copy_input" id="copy_input" style="display:none;">


	<el-dialog
	  title="提示" :show-close="false" :close-on-click-modal="false"
	  :visible.sync="is_close"
	  width="500px"
	  :before-close="close_order_pop">
	  <h3>运单号：{{row.wl_order_num}}</h3>
	  <table class="pure-table pure-table-horizontal pure-table-bordered" style="width:100%;">
		    <thead>
		        <tr>
		            <th>运单号</th>
		            <th></th> 
		        </tr>
		    </thead>
		    <tbody>
		        <tr v-if="row.customer_address && row.customer_address.com_title">
		            <td>公司</td>
		            <td>{{row.customer_address.com_title}}</td> 
		        </tr> 
		        <tr v-if="row.customer_address">
		            <td >收件人</td>
		            <td style="font-weight: bold;font-size: 18px;">{{row.customer_address.contact}}</td> 
		        </tr> 
		        <tr v-if="row.customer_address">
		            <td>电话</td>
		            <td>{{row.customer_address.mobile}}</td> 
		        </tr> 
		    </tbody>
	  </table> 
	  <p>
	  	{{row.created_at}}
	  </p>
	  <span slot="footer" class="dialog-footer">
	    <el-button @click="is_close = false" size="small">取 消</el-button>
	    <el-button type="danger" @click="confirm_close_order" size="small">确认取消，该操作不可回退</el-button>
	  </span>
	</el-dialog>


	<el-dialog :close-on-click-modal="false" 
	  title="自动打印设置"
	  :visible.sync="show_auto_printer"
	  width="500px" 
	  > 
	  <br> 
	  <el-select v-model="selected_printer" placeholder="请选择" style="width:400px;"  >
	    <el-option
	      v-for="item in printers"
	      :key="item.name"
	      :label="item.name"
	      :value="item.name">
	    </el-option>
	  </el-select> 
	  <div v-if="cur_auto_printer" style="margin-top:20px;">
	  		当前已启用自动打印，打印机名称：<b>{{cur_auto_printer}}</b>
	  </div>
	  <p>
	  	<el-alert :closable="false"
		    title="选择正确的打印机，并点击确认自动打印按钮，下次寄件时，将直接发起打印。"
		    type="info">
		</el-alert>
	  </p>
	  <p>
	  	<el-button @click="confirm_auto_print_set" size="small" type="primary">确认自动打印</el-button>
	  	<el-button @click="close_auto_print_set" size="small" type="danger" style="margin-left:20px;">取消自动打印</el-button>
	  </p>
		
	</el-dialog>

</div>

<?php  
$vue->data("list","[]");  
$vue->data("last_page","");  
$vue->data("per_page",10);  
$vue->data("total","");  
$vue->data("wuliu_list","[]");
$vue->data("active_tab","");
$vue->data("h","");

$vue->data("status","[]");
$vue->method("click_copy(row)"," 
	let express_num = row.express_num;
	let dd = ''; 
	console.log(express_num);
	for(let i in express_num){
		if(express_num[i].waybillType != 3){
			let s = express_num[i].waybillNo;
			dd += s +' ';
		}
	}
	if(!dd){
	    dd = row.wl_order_num;
	} 
	dd = dd+' '+row.customer_address.contact+' '+row.customer_address.mobile;
	navigator.clipboard.writeText(dd);
	this.\$message.success('复制成功');
"); 
$vue->created(['load()','get_auto_printer()']); 
$vue->method("search()","
	this.where.page = 1;
	this.load();
");
$vue->method("reset_search()","
	this.where = {page:1};
	this.load();
");

$vue->method("dropdown_click(e,row)","
switch(e){
	case 'view_wuliu':
		return this.view_wuliu(row);
		break;
	case 'open_printer':
		return this.open_printer(row);
		break;
	case 'open_pdf':
		return this.open_pdf(row);
		break;
	case 'edit':
		window.location.href = '/wp-admin/admin.php?page=express_add_link&id='+row.id;
		return;
		break;
	case 'open_sub':
		return this.open_sub(row);
		break;
	case 'close_order':
		return this.close_order(row);
		break;	 
}
");

$vue->data('is_close',false);
$vue->method("close_order_pop()","
	this.row = {};
	this.is_close = false;
");
$vue->method("close_order(row)","
	this.row = row; 
	this.is_close = true;
");

$vue->method("confirm_close_order()","
let d = {};
d.action = 'express_del_express';  
d.id = this.row.id;
d.type = this.row.type;
$.post('".admin_url( 'admin-ajax.php' )."',d, function(res) { 
	".vue_message()." 
	_this.is_close = false;
    _this.load();
},'json'); 
");

$vue->method("loop_wuliu()","
let d = {};
d.action = 'express_loop_wuliu';   
$.post('".admin_url( 'admin-ajax.php' )."',d, function(res) { 
	".vue_message()."  
    _this.load();
},'json'); 
");



$vue->method("loop_money()","
let d = {};
d.action = 'express_get_money';   
$.post('".admin_url( 'admin-ajax.php' )."',d, function(res) {  
    _this.load();
},'json'); 
"); 



$vue->data("selection_num",0);
$vue->data("selection_val","[]");
$vue->method("selection(val)","
	this.selection_val = val;
	this.selection_num = this.selection_val.length; 
"); 
$vue->method("do_selection()","
	if(this.selection_num <= 0){return;}
	let dd = [];
	let id_in = [];
	this.show_printer = true; 
	this.printer_pdf_url_arr = [];
	for(let i in this.selection_val){
		let row = this.selection_val[i]; 
		id_in.push(row.id);
		let express_num = row.express_num;  
		for(let i in express_num){
			if(express_num[i].pdf_url){
				dd.push(express_num[i].pdf_url); 
			}
		}
	} 
	console.log('dd',dd);
	console.log('id_in',id_in);
	this.printer_pdf_url_arr = dd;  
	$.post('".admin_url( 'admin-ajax.php' )."',{
		action:'express_tag_has_printer',
	  data:{id:id_in}
	}, function(res) {  
		_this.send_pusher();
	},'json'); 

");

$vue->method("close()","
	
");
$vue->method("change(e)","
	this.where.page = e;
	this.load();
");
$vue->method("load()","  
    this.h = window.screen.height - 360;
	this.where.per_page = this.per_page;
	this.where.action = 'express_get_list';
	$.post('".admin_url( 'admin-ajax.php' )."',this.where, function(res) { 
		 _this.list  = res.data;
		 _this.last_page = res.last_page;
		 _this.per_page  = res.per_page;
		 _this.total     = res.total;
		 _this.status = res.status; 
	},'json'); 
	let j = Cookies.get('select_printer');
	if(j){
		this.selected_printer = j;
	}
	this.close_order_real();
	this.get_money();
");
$vue->method("export_excel()","   
    let w = JSON.parse(JSON.stringify(this.where));
	w.action = 'express_get_list';
	w.method = 'export';
	w.is_today = '';
	$.post('".admin_url( 'admin-ajax.php' )."',w, function(res) { 
		if(res.code == 0){
			if(_this.has_printers){
				window.parent.postMessage({ type: 'open_excel', content:{url:res.data}}, '*');
			}else{
				window.open(res.data);		
			} 
		}else{
			_this.\$message.error(res.msg);
		}
	},'json');  
");

$vue->method("export_excel_tody()","   
    let w = JSON.parse(JSON.stringify(this.where));
	w.action = 'express_get_list';
	w.method = 'export';
	w.is_today = 1;
	$.post('".admin_url( 'admin-ajax.php' )."',w, function(res) { 
		if(res.code == 0){
			if(_this.has_printers){
				window.parent.postMessage({ type: 'open_excel', content:{url:res.data}}, '*');
			}else{
				window.open(res.data);		
			} 
		}else{

		}
	},'json');  
");

$vue->method("export_excel_tody_img()","   
    app.\$message.info('截图中请不要离开页面，稍微提示成功后再操作，以免截图失败');
    let w = JSON.parse(JSON.stringify(this.where));
	w.action = 'express_get_list';
	w.is_img = '1';
	w.method = 'export';
	w.is_today = 1;
	$.post('".admin_url( 'admin-ajax.php' )."',w, function(res) { 
		if(res.code == 0){
		    app.copy_base64_data(res.data);
		    app.\$message.success('复制图片成功，打开微信再执行 ctrl+v');
		}else{

		}
	},'json');  
");

$vue->method("copy_base64_data(data)","   
    location.origin.includes(`https://`) || Message.error(`图片复制功能不可用`);
    data = data.split(';base64,'); let type = data[0].split('data:')[1]; data = data[1]; 
    let bytes = atob(data), ab = new ArrayBuffer(bytes.length), ua = new Uint8Array(ab);
    [...Array(bytes.length)].forEach((v, i) => ua[i] = bytes.charCodeAt(i));
    let blob = new Blob([ab], { type }); 
    navigator.clipboard.write([new ClipboardItem({ [type]: blob })]);
");
 


$vue->method("view_wuliu(r)","
    this.row  = r;
	let where = r;
	where.action = 'express_view_wuliu';
	$.post('".admin_url( 'admin-ajax.php' )."',where, function(res) { 
		 _this.wuliu_list  = res.data; 
		 _this.is_show = true;
		 _this.active_tab = res.active_tab;
	},'json'); 
");
$vue->data("loop",""); 

$vue->method("get_money()"," 
	let w = this.where;
	w.action = 'express_get_money';
	$.post('".admin_url( 'admin-ajax.php' )."',w, function(res) {  
	},'json'); 
");

$vue->method("send_pusher()"," 
	$.post('".admin_url( 'admin-ajax.php' )."',{action:'express_pusher'}, function(res) { 
 			
	},'json'); 
");

$vue->method("close_order_real()"," 
	return false;
	$.post('".admin_url( 'admin-ajax.php' )."',{action:'express_close_order_real'}, function(res) { 
 			
	},'json'); 
");




$vue->data("has_printers",false);
$vue->data("show_printer",false);
$vue->data("printer_pdf_url","");
$vue->data("printer_pdf_url_arr","[]");
$vue->data("printers","[]");
$vue->method("open_pdf(row)","
let pdf = row.pdf_url; 
window.open(pdf,'_blank');
");
$vue->method("open_printer(row)","
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
		_this.send_pusher();
	},'json'); 
");
$vue->data("selected_printer","");
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
	 window.parent.postMessage({ type: 'do_print', content:{pdf: this.printer_pdf_url,printer:cur_printer}}, '*');	 
	 this.show_printer = false;
	 this.\$message.success('发起打印请求成功');

");
$vue->data("is_sub",false);
$vue->data("is_disabled_sub",false);
$vue->data("sub_num",'');

$vue->method("open_sub(row)","
	let new_row = JSON.parse(JSON.stringify(row));
	this.row = new_row;
	this.sub_num = 1; 
	this.is_sub = true;
	this.is_disabled_sub = false;
");

$vue->method("add_sub()"," 
	if(!this.sub_num || this.sub_num < 0){
		this.\$message.error('请填写子单号数量');
		return;
	}
	this.row.action = 'express_add_sub_express';
	this.row.sub_num = this.sub_num;
	this.is_disabled_sub = true;
	$.post('".admin_url( 'admin-ajax.php' )."',this.row, function(res) {   
		 ".vue_message()."
		 _this.load();
		 _this.is_sub = false;
		 _this.is_disabled_sub = true;
	},'json');   
");


$vue->method("sub_trace()"," 
	let d = {};
	d.action = 'express_sub_trace'; 
	$.post('".admin_url( 'admin-ajax.php' )."',d, function(res) {    
	},'json');   
");

$vue->data("show_auto_printer",false);
$vue->method("open_auto_print_set","
	this.get_auto_printer();
	this.show_auto_printer = true;
");

$vue->method("confirm_auto_print_set","
	this.show_auto_printer = false;
	let d = {};
	d.action = 'express_auto_print';
	d.type = 'confirm';
	d.printer = this.selected_printer;
	$.post('".admin_url( 'admin-ajax.php' )."',d, function(res) {   
		 ".vue_message()." 
		 app.get_auto_printer();
	},'json');    

");
$vue->method("close_auto_print_set","
	this.show_auto_printer = false;
	let d = {};
	d.action = 'express_auto_print';
	d.type = 'close';
	d.printer = this.selected_printer;
	$.post('".admin_url( 'admin-ajax.php' )."',d, function(res) {   
		 ".vue_message()." 
		 app.get_auto_printer();
	},'json');    
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

</script>


<script>
    // 示例：接收来自主进程的消息
    window.addEventListener('message', (event) => {  
        let type = event.data.type ; 
        if(type == 'get_version'){
            console.log("version:"+event.data.data);
        }
        if(type == 'printers'){
        		app.printers = event.data.data;
        		console.log('vue printers',app.printers);
        		app.has_printers = true;
        }
        if(type == 'print_ok'){
        	console.log('打印完成');
        }
        if(type == 'print_error'){
        	console.log('打印失败');
        }
        if(type == 'print_failed'){
        	console.log('下载PDF文件失败');
        }
    }); 
    // 发送获取打印机列表的请求
    window.parent.postMessage('get_printers', '*');
    //取版本号
    window.parent.postMessage('get_version', '*');
</script>


<script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
<script type="text/javascript">
var pusher = new Pusher("<?=get_config("PUSHER_APP_KEY")?>", {
  cluster: "<?=get_config("PUSHER_APP_CLUSTER")?>",
});
var channel = pusher.subscribe("xa_express");
channel.bind("notice", (data) => {
   console.log(data);
   if(data.xa_reload_page == 100){
   		app.load();
   }
});
</script>

<style type="text/css">
	.p_1{
		color:#409EFF;
	}
	.p_2 {
		color: green;
	}
	.red{
		color: cornflowerblue;
	}
</style>
