<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// PCサイトで必要なファイル群のrequireと、初期処理を行います
//
//--------------------------------------------------------------------
// @filename	SetUpPC.php
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

require_once( DIR_LIB.'SetUpCommon.php' );			// いろいろ初期処理

ini_set("session.gc_maxlifetime", 3600);

//共通エラー画面のテンプレートファイル
define('ERROR_TEMPLATE', ERROR_TEMPLATE_ADMIN);

// Exceptionが投げられた時の処理を定義しておきます
// スクリプト内でExceptionがcatchされなかった場合、exceptionHandler::printError()に捕捉させます。
set_exception_handler(array("ExceptionHandler", "printError"));		// 例外ハンドラを設定


// Session関係を初期化しておきます。
require_once( DIR_LIB_COMMON.'pdo_session_db.php');			// PDO SESSION DB系class定義と初期化処理

pdo_session_start();

?>
