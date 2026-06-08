<?php
/*
Plugin Name: امنیت هومانا (Houmana Security)
Plugin URI: https://houmana.ir
Description: پلاگین اختصاصی برای مخفی‌سازی ردپای تکنولوژی، حذف ورژن‌ها و افزایش امنیت صفحه ورود.
Version: 1.5
Author: هومن نصرالهی
Author URI: https://houmana.ir
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// ۱. تنظیمات آپدیت خودکار از گیت‌هاب
$puc_path = plugin_dir_path( __FILE__ ) . 'plugin-update-checker-5.7/plugin-update-checker.php';

if ( file_exists( $puc_path ) ) {
    require_once $puc_path;
    $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/r0z30ya/houmana-security/', 
        __FILE__, 
        'houmana-security' 
    );
    // تعیین شاخه اصلی
    $myUpdateChecker->setBranch('main');
}

// ۲. حذف ردپای وردپرس و امنیت
remove_action('wp_head', 'wp_generator');

function houmana_remove_wp_version_strings($src){
    if (strpos($src,'ver=') !== false){
        $src = remove_query_arg('ver',$src);
    }
    return $src;
}
add_filter('script_loader_src','houmana_remove_wp_version_strings',15);
add_filter('style_loader_src','houmana_remove_wp_version_strings',15);

remove_action('wp_head','rsd_link');
remove_action('wp_head','wlwmanifest_link');
remove_action('wp_head','wp_shortlink_wp_head');

// حذف نسخه المنتور
add_action('init',function(){
    if(class_exists('\Elementor\Utils')){
        remove_action('wp_head',[\Elementor\Utils::class,'print_generator_tag']);
    }
});

// حذف نوت رنک مث
add_filter('rank_math/frontend/remove_credit_notice','__return_true');

// تغییر پیام خطای ورود
function houmana_custom_login_errors(){
    return 'خطا: اطلاعات وارد شده صحیح نیست.';
}
add_filter('login_errors','houmana_custom_login_errors');

// غیرفعال سازی XML-RPC Pingback
add_filter('xmlrpc_methods',function($methods){
    unset($methods['pingback.ping']);
    return $methods;
});

// جلوگیری از Author Enumeration (امنیت نام کاربری)
if(!is_admin()){
    if(!empty($_SERVER['QUERY_STRING']) && preg_match('/author=([0-9]*)/i',$_SERVER['QUERY_STRING'])){
        die('Access Denied');
    }
    add_filter('redirect_canonical','houmana_check_enum',10,2);
}

function houmana_check_enum($redirect,$request){
    if(preg_match('/\?author=([0-9]*)(\/*)/i',$request)){
        die('Access Denied');
    }
    return $redirect;
}

