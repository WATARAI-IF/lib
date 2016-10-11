<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// MBサイトで必要なファイル群のrequireと、初期処理を行います
//
//--------------------------------------------------------------------
// @filename	SetUpMB.php
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

ini_set('session.use_cookies', 0);				//sessionではcookie使いません
ini_set('session.use_only_cookies', 0);
ini_set('session.use_trans_sid', 1);

require_once( DIR_LIB.'SetUpCommon.php' );			// いろいろ初期処理

define('DEFAULT_OUTPUT_ENCODE', "SJIS-win");		// 表示の際の文字コード

// Ver 5.6 以降はこれやんないと文字化ける
if(version_compare(PHP_VERSION, '5.6.0') >= 0) {
	ini_set( 'default_charset', "SJIS-win");
}


//共通エラー画面のテンプレートファイル
define('ERROR_TEMPLATE', ERROR_TEMPLATE_MB);

// Exceptionが投げられた時の処理を定義しておきます
// スクリプト内でExceptionがcatchされなかった場合、exceptionHandler::printError()に捕捉させます。
set_exception_handler(array("ExceptionHandler", "printError"));		// 例外ハンドラを設定


// Session関係を初期化しておきます。
require_once( DIR_LIB_COMMON.'pdo_session_db.php');			// PDO SESSION DB系class定義と初期化処理

pdo_session_start();


?>
