## wordpress插件依赖包

wp-config.php 在 `if ( ! defined( 'ABSPATH' ) ) {` 之前，添加配置

~~~
global $g_redis_config;
$g_redis_config['host'] = '127.0.0.1';
$g_redis_config['port'] = 6379;
$g_redis_config['auth'] = ''; 
~~~

禁用更新

https://wordpress.org/plugins/disable-updates/

