<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// 定数を定義しておきます
//	汎用性を考えて配列をつかったほうが良いものはliteral.phpを使います。
//	
//--------------------------------------------------------------------
// @filename	literal.php
// @create		2011-12-01
// @author		n.ooseki
//
// Subversionのcommit情報
//   $Date$
//   $Rev$
//   $Author$
//
//--------------------------------------------------------------------
// List of Function
//--------------------------------------------------------------------
// 
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

require_once( 'config.php' );

// 共通エラー画面のテンプレート
define('ERROR_TEMPLATE_PC', DIR_TEMPLATE . "error/index.html");
define('ERROR_TEMPLATE_MB', DIR_TEMPLATE . "error/m/index.html");
define('ERROR_TEMPLATE_SP', DIR_TEMPLATE . "error/index.html");

define("DEFAULT_EXCEPTION_MESSAGE", "システムエラーが発生しました。");

//端末タイプ
define('DEVICE_PC', 1);
define('DEVICE_SP', 2);
define('DEVICE_MB', 3);