# 快递 

支持： 顺丰、德邦、中通、韵达、极兔、圆通

已上线： 顺丰、德邦、中通

内测中： 韵达、极兔、圆通

支持一票多件（子母件）的有： 顺丰、德邦、韵达


# 安装

先启用 `core` 再启用 `express`

建议安装插件 `Disable Updates` 禁止更新

### 插件

|  名称   | 插件地址  |
|  ----  | ----  |
| Simple Local Avatars  | https://cn.wordpress.org/plugins/simple-local-avatars/ |
| Gutenberg（古腾堡  | https://cn.wordpress.org/plugins/gutenberg/ |


### 配置redis

在`wp-config.php`中 `if ( ! defined( 'ABSPATH' ) ) {` 之前添加
~~~ 
define( 'WP_DEBUG', true); 
define( 'WP_DEBUG_LOG', true); 
define( 'WP_DEBUG_DISPLAY', true);
@ini_set( 'display_errors', 'On');
//redis通知
global $g_redis_config; 
$g_redis_config['host']  = '127.0.0.1';
$g_redis_config['port']  =  6379;
$g_redis_config['auth']  =  '';
~~~
