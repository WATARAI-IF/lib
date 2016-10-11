<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// Exception発生時の処理を定義しておきます
//		Exception情報を使ってエラー画面の表示とログファイルへの書き込みを行います。
//		DEBUG_MODEが1の場合は、更にログファイルに書きこまれます。
//
//--------------------------------------------------------------------
// @filename	exception.php
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
// class MsgException			共通エラー画面に指定したメッセージを表示させるためのclass
// exceptionHandler				共通エラー画面のテンプレートを使ってエラー情報を表示する
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

/*
設定方法
setup系ファイル内(SetUpPC.php/SetUpXML.php等)で

↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

require_once( DIR_LIB_COMMON.'exception.php');					// Exception系class
set_exception_handler(array("exceptionHandler", "printError"));		// 例外ハンドラを設定

↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

とか設定しておきましょう
*/


require_once( 'config.php');
require_once( DIR_LIB_COMMON.'define.php');
require_once( DIR_LIB_COMMON.'Logger/Text.php');					// Log系
require_once( DIR_LIB_COMMON . 'Template/MyTemplateEngine.php');			// Template系
require_once("XML/Serializer.php");

//MsgExceptionをchatchした場合は、指定されたメッセージを表示します。
//それ以外のExceptionは固定メッセージを表示します。
class MsgException extends exception {}


//補足されなかったExceptinを処理します
//エラーの時にどうしたいかによってメソッドの設定を変えましょう。
class ExceptionHandler{

	//共通エラー画面を表示します
	//通常はこれを使うでしょう
	static function printError($exception) {

		//各種情報をログファイルに書きこんでおきます
		LoggerText::dumpExceptionError($exception, __FUNCTION__);

		$items = array();

		//デバッグ中
		if(DEBUG_MODE == 1)
//		if(false)
		{
			//各種情報を画面表示します。
			$items["ERROR_MESSAGE"] = "<pre>";
			$items["ERROR_MESSAGE"] .= "\n\nException Message\n" . $exception->getMessage();
			$items["ERROR_MESSAGE"] .= "\n\nException TraceAsString\n" . $exception->getTraceAsString();
			$items["ERROR_MESSAGE"] .= "\n\nException getCode\n" . $exception->getCode();
			$items["ERROR_MESSAGE"] .= "\n\n_REQUEST\n" . var_export((isset($_REQUEST)?$_REQUEST:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_GET\n" . var_export((isset($_GET)?$_GET:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_POST\n" . var_export((isset($_POST)?$_POST:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_COOKIE\n" . var_export((isset($_COOKIE)?$_COOKIE:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_SESSION\n" . var_export((isset($_SESSION)?$_SESSION:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_ENV\n" . var_export((isset($_ENV)?$_ENV:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_SERVER\n" . var_export((isset($_SERVER)?$_SERVER:null), true);
			$items["ERROR_MESSAGE"] .= "\nsession_id()\n" . var_export(session_id(), true);
			$items["ERROR_MESSAGE"] .= "\n\n</pre>";
		}
		//本物
		else{
			//MsgExceptionをchatchした場合は、指定されたメッセージを表示します。
			if(get_class($exception) === "MsgException"){
				if($exception->getMessage()){
					$items["ERROR_MESSAGE"] = $exception->getMessage();
				}
			}else{
				$items["ERROR_MESSAGE"] = DEFAULT_EXCEPTION_MESSAGE;
			}
		}

		$items["link_url"] = URL_ROOT;

		//共通エラー画面を表示します
		$tpl = new MyTemplateEngine();
		$tpl->loadTemplatefile(ERROR_TEMPLATE);
		$tpl->showAll($items, array("ERROR_MESSAGE"));
		exit;
	}

	//モバイル用共通エラー画面を表示します
	//文字コードだけ違います
	//ホントは引数とかで切り分けたいんだけど・・・
/*
	static function printErrorMobile($exception) {

		//各種情報をログファイルに書きこんでおきます
		LoggerText::dumpExceptionError($exception, __FUNCTION__);

		$items = array();

		//デバッグ中
		if(DEBUG_MODE == 1){
			//各種情報を画面表示します。
			$items["ERROR_MESSAGE"] = "<pre>";
			$items["ERROR_MESSAGE"] .= "\n\nException Message\n" . $exception->getMessage();
			$items["ERROR_MESSAGE"] .= "\n\nException TraceAsString\n" . $exception->getTraceAsString();
			$items["ERROR_MESSAGE"] .= "\n\nException getCode\n" . $exception->getCode();
			$items["ERROR_MESSAGE"] .= "\n\n_REQUEST\n" . var_export((isset($_REQUEST)?$_REQUEST:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_GET\n" . var_export((isset($_GET)?$_GET:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_POST\n" . var_export((isset($_POST)?$_POST:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_COOKIE\n" . var_export((isset($_COOKIE)?$_COOKIE:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_SESSION\n" . var_export((isset($_SESSION)?$_SESSION:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_ENV\n" . var_export((isset($_ENV)?$_ENV:null), true);
			$items["ERROR_MESSAGE"] .= "\n\n_SERVER\n" . var_export((isset($_SERVER)?$_SERVER:null), true);
			$items["ERROR_MESSAGE"] .= "\nsession_id()\n" . var_export(session_id(), true);
			$items["ERROR_MESSAGE"] .= "\n\n</pre>";
		}
		//本物
		else{
			//MsgExceptionをchatchした場合は、指定されたメッセージを表示します。
			if(get_class($exception) === "MsgException"){
				if($exception->getMessage()){
					$items["ERROR_MESSAGE"] = $exception->getMessage();
				}
			}else{
				$items["ERROR_MESSAGE"] = DEFAULT_EXCEPTION_MESSAGE;
			}
		}

		$items["link_url"] = URL_ROOT;

		//共通エラー画面を表示します
		$tpl = new MyTemplateEngine(DIR_HTDOCS, DIR_TEMPLATE, array("encoding"=>"SJIS"));
		$tpl->loadTemplatefile(ERROR_TEMPLATE);
		$tpl->showAll($items, array("ERROR_MESSAGE"));

		exit;
	}
*/


	//システムエラーXMLを返します
	static function errorXML($exception) {

		//各種情報をログファイルに書きこんでおきます
		LoggerText::dumpExceptionError($exception, __FUNCTION__);

		$data = array(
					"status" => -1
				);

		//MsgExceptionをchatchした場合は、指定されたメッセージを表示します。
		if(get_class($exception) === "MsgException"){
			if($exception->getMessage()){
				$data["message"] = $exception->getMessage();
			}
		}else{
			$data["message"] = DEFAULT_EXCEPTION_MESSAGE;
		}

		//XMLを組み立てます
		$options = array(	"mode"				=> "simplexml",			// 配列の場合、親のキー名をタグ名に使用
							"addDecl"			=> "true",				// xml version="1.0" タグをつけるかどうか
							"encoding"			=> "UTF-8",
							"rootName"			=> "root",				//ルートのタグ名
							"attributesArray"	=> "_attributes",
						);

		$Serializer = new XML_Serializer($options);
		$Serializer->serialize($data);
		$xml = $Serializer->getSerializedData();

		header('Pragma: ');
		header('Cache-Control: ');
//		header( "Content-Type: text/xml; Charset=utf-8" );
		header( "Content-Type: application/xml; Charset=utf-8" );
		print $xml;

		exit;
	}


	//システムエラーJSONを返します
	static function errorJSON($exception) {
		//各種情報をログファイルに書きこんでおきます
		LoggerText::dumpExceptionError($exception, __FUNCTION__);

		$data = array(
					"status" => 0
				);

		//MsgExceptionをchatchした場合は、指定されたメッセージを表示します。
		if(get_class($exception) === "MsgException"){
			if($exception->getMessage()){
				$data["message"] = $exception->getMessage();
			}
		}else{
			$data["message"] = DEFAULT_EXCEPTION_MESSAGE;
		}

		header('Pragma: ');
		header('Cache-Control: ');
//		header("Content-Type: text/javascript; charset=utf-8"); 
		header("Content-Type: application/json; charset=utf-8");
		print_r(json_encode($data));

		exit;
	}


	//ログに残して終了
	static function errorBin($exception) {
		//各種情報をログファイルに書きこんでおきます
		LoggerText::dumpExceptionError($exception, __FUNCTION__);

		exit;
	}

}

?>
