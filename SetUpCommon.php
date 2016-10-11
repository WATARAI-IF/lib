<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// 各種初期処理を行います
//	SetUpPC.phpとかSetUpMB.phpでのみ使用してください。
//
//--------------------------------------------------------------------
// @filename	SetUpCommon.php
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
// shutdown_write_benchmark		スクリプト終了時にベンチマーク情報をログファイルに記録する
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

require_once( 'config.php' );


//クライアントの接続が切断された際もスクリプトの実行は継続するようにします。
//falseの際の挙動は小難しくなるので、defaultはtrue
//////ignore_user_abort(true);

// php5.3.6 以下ではpreg_match とかで限界にいったりするので、
// php5.3.7以上のデフォルト値と同じにしておきます
//if( version_compare(PHP_VERSION, "5.3.7") == -1 )
if( ini_get("pcre.backtrack_limit") < 1000000)
{
	ini_set("pcre.backtrack_limit", 1000000);
}

ini_set("session.gc_maxlifetime", SESSION_GC_MAX_LIFE_TIME);

// 共通ファイル読み込み
require_once( DIR_LIB_COMMON.'define.php' );				// 定数系
require_once( DIR_LIB_COMMON.'Logger/Text.php');			// Log系class

require_once( DIR_LIB_COMMON.'exception.php');				// Exception系class
require_once( DIR_LIB_COMMON.'error_handler.php');			// error系class

//スクリプトエラーが発生した場合はログに記録しておきます
set_error_handler("dumpErrorLog");

//ここはテストの時だけ
if(DEBUG_MODE == 1){
	require_once( DIR_LIB_COMMON.'Logger/Benchmark.php');					// Log系

	//エラーレベル設定しておきます
	ini_set( 'display_errors', 1 );
//	error_reporting(E_ALL&~E_DEPRECATED);
	error_reporting(E_ALL);

	//SESSIONのGC確率を1/10に設定します
	ini_set("session.gc_probability", 1);
//	ini_set("session.gc_divisor", 10);
	ini_set("session.gc_divisor", 1000);

	//初期処理
	$_BENCHMARK_TIMER = new LoggerBenchmark();
	$_BENCHMARK_TIMER->start();


	//スクリプト終了時の処理 ベンチマーク書き込み
	register_shutdown_function(
		function ()
		{
			global $_BENCHMARK_TIMER;

			$_BENCHMARK_TIMER->stop();
			$_BENCHMARK_TIMER->writeBenchmark();
		}
	);


}

?>
