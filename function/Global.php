<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// いろんなとこで使えそうな関数を作っておきます。
//	コメントアウトしてくので、必要なものだけ有効にしましょう。
//
//--------------------------------------------------------------------
// @filename	Global.php
// @create		2011-12-01
// @author		n.ooseki
//
// Subversionのcommit情報
//   $Date$
//   $Rev$
//   $Author$
//
//--------------------------------------------------------------------
// List of Function		すべてstaticな呼び出し可能です
//--------------------------------------------------------------------
// class Function_Global
//	checkPHPversion				指定されたversionと現在使用中のPHPのVersionを比較する
//	getAge						誕生日から現在の年齢を取得する
//	arrayToCSV					２次元配列をCSV形式の文字列に変換します
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

require_once('config.php');

class FunctionGlobal{

	/*********************************************************************
	*   関数名：splitWords()
	*   概要  ：文字列を分割します。
	*   引数  ：
	*  -------------------------------------------------------------------
	*   戻り値：
	*********************************************************************/
	static function splitWords( $words )
	{
		$words = str_replace(array('　', '\t'), ' ', $words);	// 全角スペースやタブにも対応
		$words = preg_replace("/[\s]+/", " ", trim($words));	// trimして、連続スペースをひとつに

		return	array_unique(explode(' ', $words));			// 単語に分けて、一意にしておく
	}

	/*********************************************************************
	*   関数名：arrayToCSV()
	*   概要  ：２次元配列をCSV形式の文字列に変換します
	*           ファイル書き込みは行わないので、上位の処理でopen()/write()/close()して下さい
	*   引数  ：$data_array		変換対象の２次元配列
	*  -------------------------------------------------------------------
	*   戻り値：CSV形式に変換した文字列
	*********************************************************************/
	static function arrayToCsv($data_array) {
		//fputcsv()でCSVファイルを作れるのですが、これはファイルに対して書き込みを行います。
		//今回ファイルを生成する必要はないので、入出力ストリームはメモリを使用します
		$fp = fopen('php://memory', 'r+');

		//fputcsvを使ってCSVファイル(と言ってもメモリ上)を生成
		foreach( $data_array as $data ){
			fputcsv( $fp, $data );
		}

		rewind($fp); // ファイルポインタの位置を先頭に戻す

		$result = "";
		//なんかめんどくさいんですが、生成されたCSVファイル(と言ってもメモリ上)を一行づつ読み込みます
		while( $line = fgets($fp) ){
			$result .= $line;
		}
		fclose($fp);

		return $result;

	}


	/*********************************************************************
	*   関数名：csvFileToArray()
	*   概要  ：CSVファイルを読み込んで２次元配列に変換します
	*           空行は無視してスキップされます
	*
	*   引数  ：$csvname		対象となるCSVファイル
	*           $header			１行目(ヘッダ行)をスキップするかどうか 省略時 = false(スキップしません)
	*  -------------------------------------------------------------------
	*   戻り値：
	*           失敗したらfalseを返します
	*********************************************************************/
	static function csvFileToArray($csvname, $header=false) {
		if(($fp = fopen($csvname, 'r')) === false){
			return false;
		}

		$def_local = setlocale(LC_ALL, 0);
		setlocale(LC_ALL,'ja_JP');

		//ヘッダー行スキップ
		if($header === true){
			fgetcsv($fp);
		}

		$result = array();
		while (($data = fgetcsv($fp)) !== FALSE) {
			//空行の場合はnullを返します
			if($data == null){
				continue;
			}

			$result[] = $data;
		}

		fclose($fp);
		setlocale(LC_ALL, $def_local);

		return $result;

	}
}

?>