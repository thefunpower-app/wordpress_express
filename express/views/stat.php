<?php if(!defined('EXPRESS_VERSION')){die('Access Deny');}?>
<?php 
express_js();
$vue = new Vue();
$js  = '';
 


?>

<div id="app">  
	


	<div style="margin-top: 20px;">
		<el-row>
		  <el-col :span="6" style="padding-right:10px;">
		    <p>本月运费</p>
		    <el-table
                :data="yunfei1"
                border >
                <el-table-column
                  prop="type"
                  label="物流公司"
               >
                </el-table-column>
                <el-table-column
                  prop="amount"
                  label="运费" 
                  width="200">
                </el-table-column> 
            </el-table> 
			<br>
		   
		 	<p>上月运费</p>
		 	 <el-table
                :data="yunfei2"
                border >
                <el-table-column
                  prop="type"
                  label="物流公司"
               >
                </el-table-column>
                <el-table-column
                  prop="amount"
                  label="运费" 
                  width="200">
                </el-table-column> 
            </el-table>  
            
             <p>最近14天订单</p>
		 <!--    <p>-->
			<!-- 		<el-date-picker @change="load()"  v-model="where.date" value-format="yyyy-MM-dd" :picker-options="pickerOptions" size="	medium" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期">-->
			<!--		</el-date-picker>-->
			<!--</p>-->
			
			 <el-table
                :data="top"
                border
                style="width: 100%">
                <el-table-column
                  prop="date"
                  label="日期"
               >
                    
                </el-table-column>
                <el-table-column
                  prop="count"
                  label="订单数" 
                  width="">
                </el-table-column> 
                <el-table-column
                  prop="sum"
                  label="运费" 
                  width="">
                </el-table-column> 
            </el-table> 
            
		  </el-col> 
		  <el-col :span="18" > 
		  		<div id="main1" style="width: 90%;height:300px;"></div>
		  		
		  		<div id="main2" style="width: 90%;height:300px;"></div>
		  		
		  		<div id="year" style="width: 90%;height:300px;"></div>
		  		
		  		<div id="year_last" style="width: 90%;height:300px;"></div>
		  		
		  </el-col>
		</el-row> 
	</div> 
	
	<?php 
	$vue->search_date = [ 
	  '本周',
	  '上周',
	  '上上周',
	  '本月',
	  '上月',
	  '上上月',  
	  '最近一个月',
	  '最近两个月', 
	  '最近三个月', 
	];
	//限制在这个时间之前的不无法选择
	$vue->start_date = date("Y-m-d",time()-86400*93);

	$vue->add_date();


	$chats = echats(['id'=>'main1','width'=>600,'height'=>400],[
	    'title'=>[
	        'text'=>'本月'
	    ],
	    'tooltip'=>[
    		'trigger'=>'axis'
  		],
	    'yAxis'=>"js:{}",
	    'legend'=>[
	        'data'=>['订单量','运费']
	    ],
	    'xAxis'=>[
	        'data'=>"js:app.date1",
	    ],
	    'series'=>[
	        [
	            'name'=>'订单量',
	            'type'=>'line',
	            'data'=>"js:app.charts.count",
	            'itemStyle'=>[
	            	'color'=>'crimson',
	            ]
	        ],
	        [
	            'name'=>'运费',
	            'type'=>'line',
	            'data'=>"js:app.charts.sum",
	            'itemStyle'=>[
	            	'color'=>'dodgerblue',
	            ]
	        ]
	    ] 
	]);
	$js .= $chats['js']; 
	$chats = echats(['id'=>'main2','width'=>600,'height'=>400],[
	    'title'=>[
	        'text'=>'上月'
	    ],
	    'tooltip'=>[
    		'trigger'=>'axis'
  		],
	    'yAxis'=>"js:{}",
	    'legend'=>[
	        'data'=>['订单量','运费']
	    ],
	    'xAxis'=>[
	        'data'=>"js:app.date2",
	    ],
	    'series'=>[
	        [
	            'name'=>'订单量',
	            'type'=>'line',
	            'data'=>"js:app.charts_last.count",
	            'itemStyle'=>[
	            	'color'=>'crimson',
	            ]
	        ],
	        [
	            'name'=>'运费',
	            'type'=>'line',
	            'data'=>"js:app.charts_last.sum",
	            'itemStyle'=>[
	            	'color'=>'dodgerblue',
	            ]
	        ]
	    ] 
	]);
	
	$js .= $chats['js']; 
	
	$chats = echats(['id'=>'year','width'=>600,'height'=>400],[
	    'title'=>[
	        'text'=>'本年'
	    ],
	    'tooltip'=>[
    		'trigger'=>'axis'
  		],
	    'yAxis'=>"js:{}",
	    'legend'=>[
	        'data'=>['订单量','运费']
	    ],
	    'xAxis'=>[
	        'data'=>"js:app.date_year",
	    ],
	    'series'=>[
	        [
	            'name'=>'订单量',
	            'type'=>'line',
	            'data'=>"js:app.year.count",
	            'itemStyle'=>[
	            	'color'=>'crimson',
	            ]
	        ],
	        [
	            'name'=>'运费',
	            'type'=>'line',
	            'data'=>"js:app.year.sum",
	            'itemStyle'=>[
	            	'color'=>'dodgerblue',
	            ]
	        ]
	    ] 
	]);
	
	$js .= $chats['js']; 
	
	$chats = echats(['id'=>'year_last','width'=>600,'height'=>400],[
	    'title'=>[
	        'text'=>'去年'
	    ],
	    'tooltip'=>[
    		'trigger'=>'axis'
  		],
	    'yAxis'=>"js:{}",
	    'legend'=>[
	        'data'=>['订单量','运费']
	    ],
	    'xAxis'=>[
	        'data'=>"js:app.date_year_last",
	    ],
	    'series'=>[
	        [
	            'name'=>'订单量',
	            'type'=>'line',
	            'data'=>"js:app.year_last.count",
	            'itemStyle'=>[
	            	'color'=>'crimson',
	            ]
	        ],
	        [
	            'name'=>'运费',
	            'type'=>'line',
	            'data'=>"js:app.year_last.sum",
	            'itemStyle'=>[
	            	'color'=>'dodgerblue',
	            ]
	        ]
	    ] 
	]);
	
	$js .= $chats['js']; 
	

    $vue->data("year","{}"); 
    $vue->data("year_last","{}"); 
    $vue->data("charts_last","{}");
	$vue->data("top","[]");  
	$vue->data("charts","{}");  
	$vue->data("date1","[]");  
	$vue->data("date2","[]");  
	$vue->data("date_year","[]");  
	$vue->data("date_year_last","[]");  
	
	$vue->created(['load()']);
	$vue->data("yunfei1","{}");
	$vue->data("yunfei2","{}");
	$vue->method("load()"," 
		this.where.action = 'express_get_stat'; 
		$.post('".admin_url( 'admin-ajax.php' )."',this.where, function(res) {   
			 app.top = res.data.top;
			 app.yunfei1 = res.data.yunfei1;
			 app.yunfei2 = res.data.yunfei2;
			 app.charts = res.data.charts; 
			 app.date1 = res.data.charts.date; 
			 
			 app.charts_last = res.data.charts_last;  
			 app.date2 = res.data.charts_last.date; 
			 
			 app.year = res.data.year;  
			 app.date_year = res.data.year.date; 
			 
			 app.year_last = res.data.year_last;  
			 app.date_year_last = res.data.year_last.date; 
			 
			 if(!res.data.year_last_has){
			     $('#year_last').hide();
			 }
			 app.\$forceUpdate();
			 ".$js."
		},'json');   
	");


	
	?>
</div>

<script type="text/javascript">
	<?=$vue->run()?>   
	<?=$js?>
</script>

