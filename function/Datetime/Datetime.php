<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// validate関連はここにまとめておきます。
//
//--------------------------------------------------------------------
// @filename	Datetime.php
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
// class FunctionDatetime
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

require_once('config.php');

class FunctionDatetime{

	/*********************************************************************
	*   関数名：virtualTime()
	*   概要  ：現在時刻を返します
	*   引数  ：
	*			デバッグモードOnでかつ、
	*			DIR_DATA . "virtual_time.txt
	*			の一行目に時刻が記述されていたら、その時間を返します
	*  -------------------------------------------------------------------
	*   戻り値：timestamp
	*********************************************************************/

	static function virtualTime( $timestamp=null)
	{
		if($timestamp == null){
			$virtual_time = time();
		}else{
			$virtual_time = $timestamp;
		}

		//時間調整機能 ON
		if(DEBUG_MODE == 1){
			$handle = @fopen(DIR_DATA . "virtual_time.txt", "r");
//			$handle = @fopen(DIR_ROOT . "data/virtual_time.txt", "r");
			if ($handle) {
				if(($buffer = fgets($handle)) !== false) {

					if( strtotime($buffer) != false ){
						$virtual_time = strtotime($buffer);
					}
				}
				fclose($handle);
			}
		}

		return $virtual_time;
	}


	// virtualTimeを日付形式で返します
	/*********************************************************************
	*   関数名：virtualDateTime()
	*   概要  ：virtualTimeを指定フォーマットで返します
	*   引数  ：
	*  -------------------------------------------------------------------
	*   戻り値：bool
	*********************************************************************/
	static function virtualDateTime( $format="Y/m/d H:i:s" )
	{
		return date($format, self::virtualTime());
	}



	/*********************************************************************
	*   関数名：isOnTime()
	*   概要  ：時間が範囲内かどうか確認します
	*   引数  ：
	*  -------------------------------------------------------------------
	*   戻り値：bool
	*********************************************************************/
	static function isOnTime($from_timestamp=null, $to_timestamp=null)
	{
		$timestamp = self::virtualTime();

		//始まりの確認
		if(!empty($from_timestamp) && $timestamp < $from_timestamp){
			return false;
		}

		//終わりの確認
		if(!empty($to_timestamp) && $timestamp >= $to_timestamp){
			return false;
		}

		return true;
	}



	/*********************************************************************
	*   関数名：isVirtualTime()
	*   概要  ：virtualTimeが実時刻を返すかどうか
	*   引数  ：
	*			デバッグモードOnでかつ、
	*			DIR_DATA . "virtual_time.txt
	*			の一行目に時刻が記述されていたら、true
	*  -------------------------------------------------------------------
	*   戻り値：timestamp
	*********************************************************************/

	static function isVirtualTime( $timestamp=null)
	{
		$is_virtual = false;

		if(DEBUG_MODE == 1){
			$handle = @fopen(DIR_DATA . "virtual_time.txt", "r");
			if ($handle) {
				if(($buffer = fgets($handle)) !== false) {
					//たまたま一致する場合もあるけど．．．
					if( strtotime($buffer) != time() ){
						$is_virtual = true;
					}
				}
				fclose($handle);
			}
		}

		return $is_virtual;
	}


	/*********************************************************************
	*   関数名：yyyymm2array()
	*   概要  ：YYYYMM形式の年月を配列にします
	*   引数  ：YYYYMM形式
	*  -------------------------------------------------------------------
	*   戻り値：配列
	*         ： year
	*         ： month
	*********************************************************************/
	static function yyyymm2array( $yyyymm )
	{
		$datetime = strptime($yyyymm, "%Y%m");

		if($datetime == false){
			return false;
		}

		$time_array = array(
			"year"		=> intval($datetime["tm_year"] + 1900),
			"month"		=> intval($datetime["tm_mon"] + 1),
		);

		//年月の妥当性チェック
		if(checkdate($time_array["month"], 1, $time_array["year"]) == false){
			return false;
		}

		return $time_array;
	}


	/*********************************************************************
	*   関数名：yyyymmdd2array()
	*   概要  ：YYYYMMDD形式の年月日を配列にします
	*   引数  ：YYYYMMDD形式
	*  -------------------------------------------------------------------
	*   戻り値：配列
	*         ： year
	*         ： month
	*********************************************************************/
	static function yyyymmdd2array( $yyyymmdd )
	{
		$datetime = strptime($yyyymmdd, "%Y%m%d");

		if($datetime == false){
			return false;
		}

		$time_array = array(
			"year"		=> intval($datetime["tm_year"] + 1900),
			"month"		=> intval($datetime["tm_mon"] + 1),
			"day"		=> intval($datetime["tm_mday"]),
			"wday"		=> intval($datetime["tm_wday"]),
		);

		//年月日の妥当性チェック
		if(checkdate($time_array["month"], $time_array["day"], $time_array["year"]) == false){
			return false;
		}

		return $time_array;
	}


	/*********************************************************************
	*   関数名：yyyymmddhhiiss2array()
	*   概要  ：YYYYMMDDHHiiss形式の年月日時分秒を配列にします
	*   引数  ：YYYYMMDD形式
	*  -------------------------------------------------------------------
	*   戻り値：配列
	*         ： year
	*         ： month
	*********************************************************************/
	static function yyyymmddhhiiss2array( $yyyymmddhhiiss )
	{
		$datetime = strptime($yyyymmddhhiiss, "%Y%m%d%H%M%S");

		if($datetime == false){
			return false;
		}

		$time_array = array(
			"year"		=> intval($datetime["tm_year"] + 1900),
			"month"		=> intval($datetime["tm_mon"] + 1),
			"day"		=> intval($datetime["tm_mday"]),
			"hour"		=> intval($datetime["tm_hour"]),
			"min"		=> intval($datetime["tm_min"]),
			"sec"		=> intval($datetime["tm_sec"]),
			"wday"		=> intval($datetime["tm_wday"]),
		);

		//年月日の妥当性チェック
		if(checkdate($time_array["month"], $time_array["day"], $time_array["year"]) == false){
			return false;
		}

		//時分秒の妥当性チェック
		if( $time_array["hour"] < 0 || $time_array["hour"] >= 24
			|| $time_array["min"] < 0 || $time_array["min"] >= 60
			|| $time_array["sec"] < 0 || $time_array["sec"] > 60		//うるう秒があるかもしれないので60秒は許容
			){
			return false;
		}

		return $time_array;
	}


	/*********************************************************************
	*   関数名：timestamp2array()
	*   概要  ：timestampを配列にします
	*   引数  ：timestamp
	*  -------------------------------------------------------------------
	*   戻り値：配列
	*         ： year
	*         ： month
	*********************************************************************/
	static function timestamp2array( $timestamp )
	{
		$time_array = array(
			"year"		=> intval(date("Y", $timestamp)),
			"month"		=> intval(date("m", $timestamp)),
			"day"		=> intval(date("d", $timestamp)),
			"hour"		=> intval(date("H", $timestamp)),
			"min"		=> intval(date("i", $timestamp)),
			"sec"		=> intval(date("s", $timestamp)),
			"wday"		=> intval(date("w", $timestamp)),
		);

		return $time_array;
	}


	/*********************************************************************
	*   関数名：strtime2array()
	*   概要  ：文字列形式の時間(strtotime準拠)を配列にします
	*   引数  ：文字列形式の時間
	*  -------------------------------------------------------------------
	*   戻り値：配列
	*         ： year
	*         ： month
	*********************************************************************/
	static function strtime2array( $strtime )
	{
		$unixtime = strtotime($strtime);

		if($unixtime == false){
			return false;
		}

		return self::timestamp2array($unixtime);

	}



}

?>