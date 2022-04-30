<?php

/*
Plugin Name: Cocoon 拡張プラグイン
Description: Cocoonテーマをカスタマイズする際に作成した小物類です。
Version: 1.0.0
Author: ポンコツマスター
Author URI: https://ponkotsu-web.net
*/

if ( !defined( 'ABSPATH' ) ) exit;


// 定義値設定
define('COCOON_EXTENSION_SHORT_NAME', 'CEx');
define('COCOON_EXTENSION_WIDGET_NAME', '['.COCOON_EXTENSION_SHORT_NAME.']');

define( 'CEx_PLUGIN_BASENAME', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
define( 'CEx_PLUGIN_PATH', dirname( __FILE__ ) );
define( 'CEx_PLUGIN_URL', plugins_url( '', CEx_PLUGIN_BASENAME ) );

$plugin_data = get_file_data( CEx_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'cocoon-extension-by-ponkotsu.php', array( 'Version' => 'Version' ) );
define('CEx_PLUGIN_VERSION', $plugin_data['Version']);


// ディレクトリパス設定
$widget_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'widget';

// 共通関数
//ウィジェットエントリーカードリンクタグの取得
if ( !function_exists( 'is_cocoon_stylesheet' ) ):
function is_cocoon_stylesheet(){
  $template = esc_html( get_template() );
  $stylesheet = esc_html( get_stylesheet() );
  if ( strcmp( 'cocoon-master', $template ) != 0 ) return false;
  if ( strcmp( 'cocoon-master', $stylesheet ) != 0 && strcmp( 'cocoon-child-master', $stylesheet ) != 0) return false;
  return true;
}
endif;

//ファイル読み込み
require_once $widget_dir . DIRECTORY_SEPARATOR . 'util.php';
require_once $widget_dir . DIRECTORY_SEPARATOR . 'card-category-list.php';


