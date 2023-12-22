
window.addEventListener('message', (event) => {  
    let type = event.data.type ;
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