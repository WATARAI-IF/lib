<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// エラー発生時の処理を定義しておきます
//		DEBUG_MODEが1の場合は、更にログファイルに書きこまれます。
//
//--------------------------------------------------------------------
// @filename	error_handler.php
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


require_once( 'config.php');
require_once( DIR_LIB_COMMON.'Logger/Text.php');					// Log系


class ErrorHandler extends ErrorException{

	// 詳しくは http://php.net/manual/ja/errorfunc.constants.php
	protected static $error_mode = array(
		/* 1	*/		E_ERROR					=> array( 'title' => 'ERROR',				'comment' => '重大な実行時エラー。これは、メモリ確保に関する問題のように復帰で きないエラーを示します。スクリプトの実行は中断されます。'),
		/* 2	*/		E_WARNING				=> array( 'title' => 'WARNING',				'comment' => '実行時の警告 (致命的なエラーではない)。スクリプトの実行は中断さ れません。'),
		/* 4	*/		E_PARSE					=> array( 'title' => 'PARSING ERROR',		'comment' => 'コンパイル時のパースエラー。パースエラーはパーサでのみ生成されま す。'),
		/* 8	*/		E_NOTICE				=> array( 'title' => 'NOTICE',				'comment' => '実行時の警告。エラーを発しうる状況に遭遇したことを示す。 ただし通常のスクリプト実行の場合にもこの警告を発することがありうる。'),
		/* 16	*/		E_CORE_ERROR			=> array( 'title' => 'CORE ERROR',			'comment' => 'PHPの初期始動時点での致命的なエラー。E_ERRORに 似ているがPHPのコアによって発行される点が違う。'),
		/* 32	*/		E_CORE_WARNING			=> array( 'title' => 'CORE WARNING',		'comment' => '（致命的ではない）警告。PHPの初期始動時に発生する。 E_WARNINGに似ているがPHPのコアによって発行される 点が違う。'),
		/* 64	*/		E_COMPILE_ERROR			=> array( 'title' => 'COMPILE ERROR',		'comment' => 'コンパイル時の致命的なエラー。E_ERRORに 似ているがZendスクリプティングエンジンによって発行される点が違う。'),
		/* 128	*/		E_COMPILE_WARNING		=> array( 'title' => 'COMPILE WARNING',		'comment' => 'コンパイル時の警告（致命的ではない）。E_WARNINGに 似ているがZendスクリプティングエンジンによって発行される点が違う。'),
		/* 256	*/		E_USER_ERROR			=> array( 'title' => 'USER ERROR',			'comment' => 'ユーザーによって発行されるエラーメッセージ。E_ERROR に似ているがPHPコード上で trigger_error()関数を 使用した場合に発行される点が違う。'),
		/* 512	*/		E_USER_WARNING			=> array( 'title' => 'USER WARNING',		'comment' => 'ユーザーによって発行される警告メッセージ。E_WARNING に似ているがPHPコード上で trigger_error()関数を 使用した場合に発行される点が違う。'),
		/* 1024	*/		E_USER_NOTICE			=> array( 'title' => 'USER NOTICE',			'comment' => 'ユーザーによって発行される注意メッセージ。E_NOTICEに に似ているがPHPコード上で trigger_error()関数を 使用した場合に発行される点が違う。'),
		/* 2048	*/		E_STRICT				=> array( 'title' => 'STRICT',				'comment' => 'コードの相互運用性や互換性を維持するために PHP がコードの変更を提案する。 	PHP 5 より'),
		/* 4096	*/		E_RECOVERABLE_ERROR		=> array( 'title' => 'RECOVERABLE ERROR',	'comment' => 'キャッチできる致命的なエラー。危険なエラーが発生したが、 エンジンが不安定な状態になるほどではないことを表す。 ユーザー定義のハンドラでエラーがキャッチされなかった場合 ( set_error_handler() も参照ください) は、 E_ERROR として異常終了する。 	PHP 5.2.0 より'),
		/* 8192	*/		E_DEPRECATED			=> array( 'title' => 'DEPRECATED',			'comment' => '実行時の注意。これを有効にすると、 将来のバージョンで動作しなくなるコードについての警告を受け取ることができる。 	PHP 5.3.0 より'),
		/* 16384*/		E_USER_DEPRECATED		=> array( 'title' => 'USER DEPRECATED',		'comment' => 'ユーザー定義の警告メッセージ。これは E_DEPRECATED と同等だが、 PHP のコード上で関数 trigger_error() によって作成されるという点が異なる。 	PHP 5.3.0 より'),
	);


	// これが画面表示されるといろいろうるさいので無視
	public function isIgnoreError()
	{
		$ignore_error_code = array(
				E_STRICT,
				E_DEPRECATED,
		);

		$error_code = $this->getSeverity();

		// dump対象ファイルでもなくて、無視していいエラーコード
		if(!$this->isFileDumpLog() && in_array($error_code, $ignore_error_code)){
			return true;
		}

		return false;
	}


	// LogにDumpするfileかどうか
	public function isFileDumpLog()
	{
		$ignore_error_directory = array(
				DIR_LIB_PEAR,						// PEARもエラーはくときあるんだなぁ。そんなん無視
				DIR_LIB_FUNCTION . "Mail/",			// だって、だってなんだもん
			);

		$error_file = $this->getFile();

		// 無視ディレクトリに入ってる？
		foreach($ignore_error_directory as $directory){
			//無視？
			if(mb_strpos($error_file, $directory) === 0){
				return false;
			}
		}

		return true;
	}


	// エラー内容文字列Get
	public function getFullMessage()
	{
		return sprintf('%s in %s on line %u', $this->getMessage(), $this->getFile(), $this->getLine());
	}


	//エラーの種類から配列の内容Get
	public function ErrorCodeToString($key)
	{
		$error_code = $this->getSeverity();

		if ( array_key_exists($error_code, static::$error_mode) )
		{
			return static::$error_mode[$error_code][$key];
		}

		return 'UNKNOWN ERROR('.$error_code.')';
	}

}


function dumpErrorLog ( $errno, $errstr, $errfile, $errline, $errcontext )
{
	// エラー制御されている場合は無視です
	// @ でエラー出力を抑制していても、エラーハンドラがCallされる。
	// ただし、@ の場合は一時的に error_reporting() == 0 となるので
	// これをチェックすることで、@ の有り無しを判別する。
	if(error_reporting() == 0){
		return false;
	}

	$errorHandler = new ErrorHandler($errstr, 0, $errno, $errfile, $errline);

	//エラーをdumpする
	if( $errorHandler->isFileDumpLog()){
		//各種情報をログファイルに書きこんでおきます
		LoggerText::pwriteErrorLog("php_err", __FUNCTION__,
					array(
							"ERROR CODE"		=> sprintf("%s : %s", $errorHandler->ErrorCodeToString("title"), $errorHandler->ErrorCodeToString("comment") ),
							"SUMMARY"			=> $errorHandler->getFullMessage(),
							"DETAIL"			=> $errorHandler,
					)
		);

		// falseを返すと続けてシステムでのエラー処理が実行されます
//		return false;
	}

	// trueを返すとシステムでのエラー処理は実行されません
//	return true;


	// 無視していいエラー？
	// trueを返すとシステムでのエラー処理は実行されません
	// falseを返すと続けてシステムでのエラー処理が実行されます
	return $errorHandler->isIgnoreError();
}


?>
