<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// 動作チェック用のclassです。
// ログやベンチマーク等をファイルに出力します。
// 	・便宜上、Benchmark_Timerを継承します
// 	・config.php のDEBUG_MODEが 1 の場合のみ出力されます。
// 	・結果は /data/test_log/ に出力されます。
// 		予めディレクトリを作成し、適切な書き込み権限を付与しましょう。
//
//--------------------------------------------------------------------
// @filename	LoggerBenchmark.php
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
//	benchMarkLogger				ベンチマークを結果を記録します。PEARのBenchmark_Timerと組み合わせて使用します。staticな呼び出しはできません。
//	printBenchMark				ベンチマークをHTML表示します。PEARのBenchmark_Timerと組み合わせて使用します。staticな呼び出しはできません。
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/



require_once ('config.php');
require_once (DIR_LIB_PEAR . 'Benchmark/Timer.php');

class LoggerBenchmark extends Benchmark_Timer{


/****************************************************************************************************
ベンチマークを取ります
	結果は
	/data/log_benchmark/bm_YYYYMMDD.txt
	に出力されます。

	出力内容は左から
	スクリプト名、実行日時、STARTからの処理時間、処理時間をグラフ化?したもの
	です。
	出力された内容を見ればわかるとは思います。
****************************************************************************************************/

	function writeBenchmark()
	{
		if(DEBUG_MODE != 1)		return;

		$profiling = $this->getProfiling();

		//"Start","Stop"は必須
		$start = $stop = 0;
		if(is_array($profiling)){
			foreach($profiling as $value){
				if(isset($value['name']) && $value['name']==='Start'){
					$start = 1;
				}
				if(isset($value['name']) && $value['name']==='Stop'){
					$stop = 1;
					$total = $value['total'];
				}
			}
		}

		if($start == 0 || $stop == 0)		return;

		$filename = "bm_" . date("Ymd") . ".txt";

		$create = 0;
		if(!file_exists(DIR_LOG_BENCHMARK . $filename) ){
			$create = 1;
		}

		//もしかしてディレクトリ作ってなかったりした場合もあるのでエラー抑止
		$fp = @fopen(DIR_LOG_BENCHMARK . $filename , "a+");
		if($fp == false){
			return;
		}

		//最初はヘッダ行書きます
		if($create === 1){
			fwrite($fp, "スクリプト名                                                    , メソッド ,   PID   , IPアドレス      ,          実行日時         , 処理時間[sec], malloc量[KB], 実メモリ量[KB], 0.01秒単位(切捨)のグラフ\n");
		}

		list($usec, $sec) = explode(" ", microtime());
		$usec = sprintf("%06d", $usec*1000000);
		fwrite($fp, str_pad($_SERVER['PHP_SELF'],64) . ", " . 
					str_pad((isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:""),9) . ", " . 
					str_pad(getmypid(),8) . ", " . 
					str_pad((isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:""),16) . ", " . 
					date("Y/m/d H:i:s") . "." . 
					$usec . ", " . 
					sprintf("    %9.4f", $total) . ", " . 
					sprintf("  %10.1f", memory_get_peak_usage()/1000) . ", " . 
					sprintf("    %10.1f", memory_get_peak_usage(true)/1000) . ", ");
		foreach($profiling as $value){
			if($value['name']==='Start')	continue;

			$repeat = floor($value['diff']/0.01);
			if($repeat >= 1000)				$repeat = 1000;
			$chr = substr($value['name'], 0, 1);
			if($value['name']==='Stop')		$chr = '*';

			$g = str_repeat( $chr, $repeat );
			fwrite($fp, $g);
		}
		fwrite($fp, "\n");
		fclose($fp); 

		@chmod(DIR_LOG_BENCHMARK . $filename, 0666);
	}


/****************************************************************************************************
ベンチマークをHTML表示します。
-----------------------------------------------------------------------------------------------------
使い方
-----------------------------------------------------------------------------------------------------
	//START
	$timer = new LoggerBenchmark();
	$timer->start();
	//START

	//........

	//MARK
	$timer->setMarker('1');
	//MARK

	//........

	//MARK
	$timer->setMarker('2');
	//MARK

	//........

	//END
	$timer->stop();
	$timer->printBenchmark();
	//END
****************************************************************************************************/
	function printBenchmark()
	{
//		if(DEBUG_MODE != 1)		return;

		$profiling = $this->getProfiling();

		//"Start","Stop"は必須
		$start = $stop = 0;
		if(is_array($profiling)){
			foreach($profiling as $value){
				if(isset($value['name']) && $value['name']==='Start'){
					$start = 1;
				}
				if(isset($value['name']) && $value['name']==='Stop'){
					$stop = 1;
					$total = $value['total'];
				}
			}
		}

		if($start == 0 || $stop == 0)		return;

		print "---------- BenchMark Start ----------\n";
		printf("Total	%8.6f[msec]\n", $total*1000);
		foreach($profiling as $value){

			if($value['name']==='Start'){
				printf("[%s]\n", $value['name']) ;
				continue;
			}else{
				printf(" |	%8.6f\n", $value['diff']*1000) ;
				printf("[%s]\n", $value['name']) ;
			}
		}
		printf("Total	%8.6f[msec]\n", $total*1000);
		print "---------- BenchMark Stop ----------\n";

		print("<BR>\n");
	}

	function resultBenchmark()
	{
//		if(DEBUG_MODE != 1)		return;

		$profiling = $this->getProfiling();

		//"Start","Stop"は必須
		$start = $stop = 0;
		if(is_array($profiling)){
			foreach($profiling as $value){
				if(isset($value['name']) && $value['name']==='Start'){
					$start = 1;
				}
				if(isset($value['name']) && $value['name']==='Stop'){
					$stop = 1;
					$total = $value['total'];
				}
			}
		}

		if($start == 0 || $stop == 0)		return;

		$result = null;
		$result .= "---------- BenchMark Start ----------\n";
		$result .= sprintf("Total	%8.6f[msec]\n", $total*1000);
		foreach($profiling as $value){

			if($value['name']==='Start'){
				$result .= sprintf("[%s]\n", $value['name']) ;
				continue;
			}else{
				$result .= sprintf(" |	%8.6f\n", $value['diff']*1000) ;
				$result .= sprintf("[%s]\n", $value['name']) ;
			}
		}
		$result .= sprintf("Total	%8.6f[msec]\n", $total*1000);
		$result .= "---------- BenchMark Stop ----------\n";

		return $result;
	}

}


?>