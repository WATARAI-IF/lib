<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// XML出力処理で必要なファイル群のrequireと、初期処理を行います
//
//--------------------------------------------------------------------
// @filename	SetUpXML.php
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

// Exceptionが投げられた時の処理を定義しておきます
// スクリプト内でExceptionがcatchされなかった場合、exceptionHandler::errorJSON()に捕捉させます。
set_exception_handler(array("ExceptionHandler", "errorJSON"));		// 例外ハンドラを設定


/* Session使う場合
// Session関係を初期化しておきます。
require_once( DIR_LIB_COMMON.'pdo_session_db.php');			// PDO SESSION DB系class定義と初期化処理

pdo_session_start();
*/


?>
