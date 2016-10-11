<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// 動作チェック用のclassです。
// ログやベンチマーク等をファイルに出力します。
// 	・便宜上、Benchmark_Timerを継承します
// 	・config.php のDEBUG_MODEが 1 の場合のみ出力されます。
// 	・結果は /data/test_log/ に出力されます。
// 		予めディレクトリを作成し、適切な書き込み権限を付与しましょう。
// 	・基本的にはPreviewでの確認や、エラーのトレースのためのものなので、
// 		デッドロック等のリスクを避けるため、flock等による排他制御は行いません。
// 	・さらに、これらの処理中にエラーが発生しても無視します
//
//--------------------------------------------------------------------
// @filename	mylogger.php
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
// class MyLoggerClass
//	writeMessage			logファイルにメッセージ書き込みます	staticに呼び出し可能です
//	writeErrorLog			logファイルにメッセージ書き込みます	staticに呼び出し可能です
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/



require_once ('config.php');

//-----------------------------------------------
// エラーログ debug_traceモード
// trueのほうが詳しい情報をログに落とすけど、ファイルサイズが大きくなりますよ
//-----------------------------------------------
	define( "LOGGERTEXT_ERROR_LOG_DEBUG_TRACE",		false );
//	define( "LOGGERTEXT_ERROR_LOG_DEBUG_TRACE",		true );

//-----------------------------------------------
// /lib/common/Logger/Text.phpで生成するログファイルのrotateサイズ
// 大きすぎると、圧縮するときにメモリーが足りなくなるので、数値を変える場合は要注意です
// いちおう、previewでは100*1024*1024での動作までは確認済みです
// 0を指定するとサイズ無制限です
//-----------------------------------------------
	define( "LOGGERTEXT_PHPLOG_ROTATE_SIZE",		10*1024*1024 );


class LoggerText{

/****************************************************************************************************
logファイルにメッセージ書き込みます
	staticに呼び出し可能です
	DEBUG_MODE=1のときだけ出力されます

LoggerText::writeMessage( $Message [, $value] )

引数：
	$Message			出力するメッセージ
	$value(省略可)		出力する変数(配列等でも可)

結果は
/data/log_message/log_YYYYMMDD.txt
に出力されます。
****************************************************************************************************/

	static function writeMessage( $Message, $value = null )
	{
		if(DEBUG_MODE != 1)		return;

		$filename = DIR_LOG_MESSAGE . "log_" . @date("Ymd") . ".txt";

		//ログファイルが大きすぎたら圧縮します
		self::log_rotate($filename);

		$fp = @fopen($filename , "a+");
		if($fp == false){
			return;
		}

//		@fwrite($fp, "----- <" . $_SERVER['PHP_SELF'] . "> - [" . @date("Y/m/d H:i:s") . "] - ". $Message. "\n");
		@fwrite($fp, sprintf("----- <%s> - [%s] - [Req:%s] - [PID:%s] - %s\n", $_SERVER['PHP_SELF'], @date("Y/m/d H:i:s"), $_SERVER['REQUEST_METHOD'], @getmypid(), $Message));

		if($value){
			@fwrite($fp, self::my_var_export($value));
			@fwrite($fp, "\n");
		}

		@fwrite($fp, "\n\n");

		@fclose($fp); 

		@chmod($filename, 0666);
	}

/****************************************************************************************************
logファイルにメッセージ書き込みます
ファイル名のprefixを指定します
	staticに呼び出し可能です
	DEBUG_MODE=1のときだけ出力されます

LoggerText::pwriteMessage( $prefix, $Message [, $value] )

引数：
	$Message			出力するメッセージ
	$value(省略可)		出力する変数(配列等でも可)

結果は
/data/log_message/log_YYYYMMDD.txt
に出力されます。
****************************************************************************************************/

	static function pwriteMessage( $prefix, $Message, $value = null )
	{
		if(DEBUG_MODE != 1)		return;

		$filename = DIR_LOG_MESSAGE . $prefix . "_" . @date("Ymd") . ".txt";

		//ログファイルが大きすぎたら圧縮します
		self::log_rotate($filename);

		$fp = @fopen($filename , "a+");
		if($fp == false){
			return;
		}

//		@fwrite($fp, "----- <" . $_SERVER['PHP_SELF'] . "> - [" . @date("Y/m/d H:i:s") . "] - ". $Message. "\n");
		@fwrite($fp, sprintf("----- <%s> - [%s] - [Req:%s] - [PID:%s] - %s\n", $_SERVER['PHP_SELF'], @date("Y/m/d H:i:s"), $_SERVER['REQUEST_METHOD'], @getmypid(), $Message));

		if($value){
			@fwrite($fp, self::my_var_export($value));
			@fwrite($fp, "\n");
		}

		@fwrite($fp, "\n\n");

		@fclose($fp); 

		@chmod($filename, 0666);
	}


/****************************************************************************************************
logファイルにエラーメッセージ書き込みます
	staticに呼び出し可能です
	DEBUG_MODEにかかわらず出力されます

LoggerText::writeErrorLog( $Message [, $value] )

引数：
	$Message			出力するメッセージ
	$value(省略可)		出力する変数(配列等でも可)

結果は
/data/log_error/err_YYYYMMDD.txt
に出力されます。
****************************************************************************************************/

	static function writeErrorLog( $Message, $value=null, $trace_mode=LOGGERTEXT_ERROR_LOG_DEBUG_TRACE )
	{
		$filename = DIR_LOG_ERROR . "err_" . @date("Ymd") . ".txt";

		//ログファイルが大きすぎたら圧縮します
		self::log_rotate($filename);

		$fp = @fopen($filename , "a+");
		if($fp == false){
			return;
		}

//		@fwrite($fp, "----- <" . $_SERVER['PHP_SELF'] . "> - [" . @date("Y/m/d H:i:s") . "] - ". $Message. "\n");
		@fwrite($fp, sprintf("----- <%s> - [%s] - [Req:%s] - [PID:%s] - %s\n", $_SERVER['PHP_SELF'], @date("Y/m/d H:i:s"), $_SERVER['REQUEST_METHOD'], @getmypid(), $Message));

		//debug_backtrace
		//$valueとかも含まれます
		if($trace_mode){
			@fwrite($fp, self::my_var_export(array("debug_backtrace"=>@debug_backtrace())));
			@fwrite($fp, "\n\n");
		}else{
			@fwrite($fp, self::my_var_export($value));
			@fwrite($fp, "\n\n");
		}

		@fwrite($fp, "\n\n");

		@fclose($fp); 

		@chmod($filename, 0666);
	}


/****************************************************************************************************
logファイルにエラーメッセージ書き込みます
ファイル名のprefixを指定します
	staticに呼び出し可能です
	DEBUG_MODEにかかわらず出力されます

LoggerText::pwriteErrorLog( $prefix, $Message [, $value] )

引数：
	$Message			出力するメッセージ
	$value(省略可)		出力する変数(配列等でも可)

結果は
/data/log_message/log_YYYYMMDD.txt
に出力されます。
****************************************************************************************************/

	static function pwriteErrorLog( $prefix, $Message, $value = null, $trace_mode=LOGGERTEXT_ERROR_LOG_DEBUG_TRACE )
	{
		$filename = DIR_LOG_ERROR . $prefix . "_" . @date("Ymd") . ".txt";

		//ログファイルが大きすぎたら圧縮します
		self::log_rotate($filename);

		$fp = @fopen($filename , "a+");
		if($fp == false){
			return;
		}

//		@fwrite($fp, "----- <" . $_SERVER['PHP_SELF'] . "> - [" . @date("Y/m/d H:i:s") . "] - [PID:" . @getmypid() . "]". $Message . "\n");
		@fwrite($fp, sprintf("----- <%s> - [%s] - [Req:%s] - [PID:%s] - %s\n", $_SERVER['PHP_SELF'], @date("Y/m/d H:i:s"), $_SERVER['REQUEST_METHOD'], @getmypid(), $Message));

		//debug_backtrace
		//$valueとかも含まれます
		if($trace_mode){
			@fwrite($fp, self::my_var_export(array("debug_backtrace"=>@debug_backtrace())));
			@fwrite($fp, "\n\n");
		}else{
			@fwrite($fp, self::my_var_export($value));
			@fwrite($fp, "\n\n");
		}

		@fwrite($fp, "\n\n");

		@fclose($fp); 

		@chmod($filename, 0666);
	}


	static function dumpExceptionError($exception, $message=null)
	{
		//各種情報をログファイルに書きこんでおきます
		self::writeErrorLog($message,
					array(	"getMessage"		=>$exception->getMessage(),
							"getTraceAsString"	=>$exception->getTraceAsString(),
							"getCode"			=>$exception->getCode(),
							"_REQUEST"			=>(isset($_REQUEST)?$_REQUEST:null),
							"_GET"				=>(isset($_GET)?$_GET:null),
							"_POST"				=>(isset($_POST)?$_POST:null),
							"_COOKIE"			=>(isset($_COOKIE)?$_COOKIE:null),
							"_SESSION"			=>(isset($_SESSION)?$_SESSION:null),
							"_ENV"				=>(isset($_ENV)?$_ENV:null),
							"_SERVER"			=>(isset($_SERVER)?$_SERVER:null),
							"session_id()"		=>session_id(),
					)
		);
	}



/****************************************************************************************************
my_var_export
	標準のvar_exportが
		PHP Fatal error:  Nesting level too deep - recursive dependency?
	になる場合があるので、自前で対処
	staticに呼び出し可能です

引数：
	$value				出力変数

****************************************************************************************************/

	static private function my_var_export( $value )
	{

		return print_r( $value, true );


//Fatal error: Nesting level too deep - recursive dependency? in /var/www/lib/common/Logger/Text.php on line 265 Fatal error: Nesting level too deep - recursive dependency? in Unknown on line 0 
// ↑↑これが出たら
// ↓↓これはやめておこう
//		return var_export( $value, true );




//上記var_exportでいきたいとこだけど、だめな場合はこちら↓
//まぁ、これでもだめな場合はあるんだけどね

/*
		//出力のバッファリングを有効にして
		@ob_start();

		//出力
		@var_dump($value);

		//出力用バッファの内容を頂戴して
		$value_dump = @ob_get_contents();

		//出力用バッファの内容を消去し、出力のバッファリングをオフにします
		@ob_end_clean();

		//ちょっと見やすく整形
		$value_dump = @str_replace("=>\n", "=>", $value_dump);
		$value_dump = @str_replace("  ", "\t", $value_dump);

		return $value_dump;
*/

	}
/****************************************************************************************************
log_rotate
引数：

****************************************************************************************************/
	static private function log_rotate( $filename )
	{
		// filesize()の結果はキャッシュされちゃうので、クリアしておきます
		@clearstatcache();

		//ファイルがなかったり、小さい場合はスルー
		if(!@file_exists($filename) || LOGGERTEXT_PHPLOG_ROTATE_SIZE==0 || @filesize($filename) < LOGGERTEXT_PHPLOG_ROTATE_SIZE){
			return true;
		}

		//今あるzipファイルのファイル名をください
		$zip_list = @glob($filename . "_*.zip");

		//ない場合は、一応空配列に初期化
		if($zip_list == false)	$zip_list = array();

		//次に圧縮するファイル名を決定します
		for($zip_no=1; ;$zip_no++){
			$zip_name = @sprintf("%s_%d.zip", $filename, $zip_no);
			if(!@in_array($zip_name, $zip_list)){
				break;
			}
		}

		//それでは圧縮させていただきます

		// ZipArchiveクラス使えますか？
		if(@class_exists("ZipArchive")){
			$zip = new ZipArchive();

			// zipファイルのオープン
			//  ZipArchive::CREATE	アーカイブが存在しない場合に、作成します。 
			//  ZipArchive::EXCL		アーカイブが既に存在する場合はエラーとします。 
			if ($zip->open($zip_name, ZIPARCHIVE::CREATE | ZIPARCHIVE::EXCL) !== true){
				return false;
			}

			// ファイル追加
			$res = $zip->addFile($filename, @basename($filename));

			//上手にできましたか？
			if($res !== true){
				$zip->close();
				return false;
			}

			// ZIPファイルをクローズ
			$zip->close();

			//元のファイル削除
			@unlink($filename);

			return true;
		}else{
			// ZipArchiveが使えないのなら、仕方がないのでPEARのArchive_Zipで我慢します
			@include_once(DIR_LIB_PEAR . 'Archive/Zip.php');

			if(@class_exists("Archive_Zip")){
				$zip = new Archive_Zip($zip_name);
				$zip->create($filename, array('remove_path' => @dirname($filename)));

				//元のファイル削除
				@unlink($filename);

				return true;
			}
		}

		return false;
	}
}


?>