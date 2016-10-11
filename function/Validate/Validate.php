<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// validate関連はここにまとめておきます。
//
//--------------------------------------------------------------------
// @filename	Validate.php
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
// class FunctionValidate
//	maxLength()				文字数が最大値を超えていないかチェックする
//	minLength()				文字数が最小値を超えていないかチェックする
//	isEqual()				変数が一致するかチェックする
//	notNgword()				NGワードリストに一致するかどうかチェックする
//	notNull()				NULLもしくは空かどうかチェックする
//	isMailAddress()			メールアドレス形式かどうかチェックする
//	isMobileMailAddress()	ドメインからモバイルのメールアドレスかどうかチェックする
//	isZipCode()				正しい郵便番号形式かチェックする
//	isTelNumber()			正しい電話番号形式かチェックする
//	inEnum()				リスト内に存在するかどうかのチェック
//	isInt()					整数値かどうかのチェック
//	isNumber()				数値かどうかのチェック
//	isString()				文字列かどうかのチェック
//	isAlphanumeric()		半角英数かどうかのチェック
//	notWrongChar()			機種依存文字が含まれているかどうかのチェック
//	notHankaku()			半角カナが含まれているかどうかのチェック
//	isDate()				日付データとして正しいかチェック
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

require_once("config.php");
require_once( DIR_LIB_COMMON."define.php" );

class FunctionValidate{

	/*********************************************************************
	*   関数名：maxLength()
	*   概要  ：文字数が最大値を超えていないかチェックする
	*   引数  ：$str          チェック対象文字列
	*           $max_length   許容される最大文字数
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function maxLength($value, $length) {
		return mb_strlen($value) <= $length;
	}


	/*********************************************************************
	*   関数名：minLength()
	*   概要  ：文字数が最小値を超えていないかチェックする
	*   引数  ：$str          チェック対象文字列
	*           $max_length   許容される最小文字数
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function minLength($value, $length) {
		return mb_strlen($value) >= $length;
	}


	/*********************************************************************
	*   関数名：lengthBetween()
	*   概要  ：変数の長さのチェック
	*           
	*   引数  ：$value         チェック対象
	*           $min_length    許容される最大文字数
	*           $max_length    許容される最小文字数
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function lengthBetween($value, $min_length, $max_length){
		return (mb_strlen($value) <= $max_length) && (mb_strlen($value) >= $min_length);
	}


	/*********************************************************************
	*   関数名：isUnderMax()
	*   概要  ：最大値を超えていないかチェックする
	*   引数  ：$value        チェック対象
	*           $max          許容される最大
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function isUnderMax($value, $max) {
		return $value <= $max;
	}


	/*********************************************************************
	*   関数名：isOverMin()
	*   概要  ：最小値を超えていないかチェックする
	*   引数  ：$value        チェック対象
	*           $min          許容される最小
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function isOverMin($value, $min) {
		return $value >= $min;
	}


	/*********************************************************************
	*   関数名：isBetween()
	*   概要  ：変数の範囲チェック
	*           
	*   引数  ：$value         チェック対象
	*           $min           許容される最大
	*           $max           許容される最小
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function isBetween($value, $min, $max){
		return ($value <= $max) && ($value >= $min);
	}


	/*********************************************************************
	*   関数名：isEqual()
	*   概要  ：変数が一致するかチェックする
	*   引数  ：$value1    ひとつめ
	*           $value2    ふたつめ
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function isEqual($value1, $value2, $strict = false){
		if($strict){
			return $value1 === $value2;
		}
		return $value1 == $value2;
	}


	/*********************************************************************
	*   関数名：isNotEmpty()
	*   概要  ：変数が空かチェックする
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function isNotEmpty($value){
		return !empty($value);
	}


	/*********************************************************************
	*   関数名：notNgword()
	*   概要  ：NGワードリストに一致するかどうかチェックする
	*   引数  ：$str          チェック対象文字列
	*           $ng_list      NGワードリスト配列 or NGワードリストファイル名
	*           $full_match   false:部分一致でチェック	true:完全一致でチェック => 省略時は部分一致
	*  -------------------------------------------------------------------
	*   戻り値：true	NGワードなし
	*           false	NGワードあり
	*********************************************************************/
	static function notNgword($str, $ng_list, $full_match=true) {
		if(is_string($ng_list) && file_exists($ng_list)){
			//NGワードファイル
			$ng_list = file($ng_list);
		}

		//NGワードのリスト作成
		$ng_list_trim = array();
		foreach($ng_list as $ng_word){
			if(mb_strlen(trim($ng_word)) > 0)		array_push($ng_list_trim, trim($ng_word));
		}

		$encoded = mb_convert_encoding($str, DEFAULT_ENCODE);

		//完全一致チェック
		if($full_match === true){
			if(in_array($encoded, $ng_list_trim)){
				return false;
			}
		}else{
			foreach ( $ng_list_trim as $value ) {
				//なんか、正規表現でやったら上手いこといかなかった
				if ( substr_count($encoded, $value) != 0 ){
					return false;
				}
			}
		}

		return true;
	}


	/*********************************************************************
	*   関数名：notNull()
	*   概要  ：NULLもしくは空かどうかチェックする
	*   引数  ：$value          チェック対象文字列
	*  -------------------------------------------------------------------
	*   戻り値：true	有効な文字列
	*           false	無効な文字列
	*********************************************************************/
	static function notNull($value) {
		if($value === null){
			return false;
		}

		if(is_string($value)){
			if(mb_strlen($value) == 0){
				return false;
			}
		}

		return true;
	}


	/*********************************************************************
	*   関数名：isMailAddress()
	*   概要  ：メールアドレス形式かどうかチェックする
	*   引数  ：$value          チェック対象文字列
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function isMailAddress($value)
	{
// ↓↓↓↓↓ げんみつな方法 ↓↓↓↓↓
//		if( filter_var($value, FILTER_VALIDATE_EMAIL) !== $value ){
//			return false;
//		}
// ↑↑↑↑↑ げんみつな方法 ↑↑↑↑↑



// ↓↓↓↓↓ ゆるーい方法 ↓↓↓↓↓
		if(!self::maxLength($value, 255)){
			return false;
		}

		$value_arr = explode('@', $value);

		if(count($value_arr) != 2){
			return false;
		}

		if(!preg_match('/^[-0-9a-zA-Z\/'.preg_quote("!#$%&'=~|^`{}*+.?_").']+$/', $value_arr[0])){
			return false;
		}

		$value = 'mail@'.$value_arr[1];

		if( filter_var($value, FILTER_VALIDATE_EMAIL) !== $value ){
			return false;
		}
// ↑↑↑↑↑ ゆるーい方法 ↑↑↑↑↑

		return true;
	}


	/*********************************************************************
	*   関数名：isMobileMailAddress()
	*   概要  ：ドメインからモバイルのメールアドレスかどうかチェックする
	*   引数  ：$value          チェック対象文字列
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG	形式がメールアドレス形式でない場合もfalseです
	*********************************************************************/
	static function isMobileMailAddress($value)
	{
		if(!self::isMailAddress($value)){
			return false;
		}

		list($username, $domain) = explode('@', $value);

		$mobileDomain = array(
			'docomo.ne.jp',
			'docomo-camera.ne.jp',
			'mopera.net',
			'dwmail.jp',
			'ezweb.ne.jp',
			'biz.ezweb.ne.jp',
			'ido.ne.jp',
			'softbank.ne.jp',
			'i.softbank.jp',
			'disney.ne.jp',
			'd.vodafone.ne.jp',
			'h.vodafone.ne.jp',
			't.vodafone.ne.jp',
			'c.vodafone.ne.jp',
			'r.vodafone.ne.jp',
			'k.vodafone.ne.jp',
			'n.vodafone.ne.jp',
			's.vodafone.ne.jp',
			'q.vodafone.ne.jp',
			'pdx.ne.jp',
			'willcom.com',
			'emnet.ne.jp',
			'vertuclub.ne.jp',
		);

		return (in_array($domain, $mobileDomain)) ? true : false;
	}


	/*********************************************************************
	*   関数名：isZipCode()
	*   概要  ：正しい郵便番号形式かチェックする
	*   引数  ：$value		  チェック対象
	*           $hyphen       ハイフン許可するかどうか
	*  -------------------------------------------------------------------
	*   戻り値：true	正しい郵便番号形式
	*           false   正しくない郵便番号形式
	*********************************************************************/
	static function isZipCode($value, $hyphen=false)
	{
		if($hyphen){
			if(preg_match('/^\d{3}\-\d{4}$/', $value)) {
				return true;
			}
		}else{
			if(preg_match('/^\d{7}$/', $value)) {
				return true;
			}
		}

		return false;
	}


	/*********************************************************************
	*   関数名：isTelNumber()
	*   概要  ：正しい電話番号形式かチェックする
	*   引数  ：$value		  チェック対象
	*           $hyphen       ハイフン許可するかどうか
	*  -------------------------------------------------------------------
	*   戻り値：true	正しい郵便番号形式
	*           false   正しくない郵便番号形式
	*********************************************************************/
	static function isTelNumber($value, $hyphen=false)
	{
		if($hyphen){
			if(preg_match('/^0\d{1}-\d{4}-\d{4}$/', $value)
				|| preg_match('/^0\d{2}-\d{3,4}-\d{4}$/', $value)
				|| preg_match('/^0\d{3}-\d{2}-\d{4}$/', $value)){
				return true;
			}
		}else{
			if(preg_match('/^0\d{9,11}$/', $value)){
				return true;
			}
		}

		return false;
	}


	/*********************************************************************
	*   関数名：inEnum()
	*   概要  ：リスト内に存在するかどうかのチェック
	*           in_array()と同じだけど、インターフェース統一のために作っておきます
	*   引数  ：$value          チェック対象文字列
	*           $list           enumのリスト配列
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function inEnum($value, $list, $strict = false)
	{
		return in_array($value, $list, $strict);
	}


	/*********************************************************************
	*   関数名：isInt()
	*   概要  ：整数値かどうかのチェック
	*           
	*   引数  ：$value          チェック対象
	*           $border["max"]  許容される最大値(省略可)
	*           $border["min"]  許容される最小値(省略可)
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function isInt($value, $border=array())
	{
		//型チェック
		if (!preg_match('/^[-+]{0,1}\d+$/', $value)) {
			return false;
		}

		//最大値チェック
		if( isset($border["max"]) && is_int($border["max"]) ){
			if($value > $border["max"]){
				return false;
			}
		}

		//最小値チェック
		if( isset($border["min"]) && is_int($border["min"]) ){
			if($value < $border["min"]){
				return false;
			}
		}

		return true;
	}


	/*********************************************************************
	*   関数名：isNumber()
	*   概要  ：数値かどうかのチェック
	*           
	*   引数  ：$value          チェック対象
	*           $border["max"]  許容される最大値(省略可)
	*           $border["min"]  許容される最小値(省略可)
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function isNumber($value, $border=array())
	{
		//型チェック
/*
		if(!is_numeric($value)){
			return false;
		}
*/
		if(!preg_match("/^[0-9]+$/", $value)){
			return false;
		}

		//最大値チェック
		if( isset($border["max"]) && is_numeric($border["max"]) ){
			if($value > $border["max"]){
				return false;
			}
		}

		//最小値チェック
		if( isset($border["min"]) && is_numeric($border["min"]) ){
			if($value < $border["min"]){
				return false;
			}
		}

		return true;
	}


	/*********************************************************************
	*   関数名：isString()
	*   概要  ：文字列かどうかのチェック
	*           
	*   引数  ：$value          チェック対象
	*           $length["max"]  許容される最大文字数(省略可)
	*           $length["min"]  許容される最小文字数(省略可)
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function isString($value, $length=array())
	{
		//型チェック
		if(!is_string($value)){
			return false;
		}

		//最大文字数チェック
		if( isset($length["max"]) && is_numeric($length["max"]) ){
			if(mb_strlen($value) > $length["max"]){
				return false;
			}
		}

		//最小文字数チェック
		if( isset($length["min"]) && is_numeric($length["min"]) ){
			if(mb_strlen($value) < $length["min"]){
				return false;
			}
		}

		return true;
	}


	/*********************************************************************
	*   関数名：isAlphanumeric()
	*   概要  ：半角英数かどうかのチェック
	*           
	*   引数  ：$value          チェック対象
	*           $length["max"]  許容される最大文字数(省略可)
	*           $length["min"]  許容される最小文字数(省略可)
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	NG
	*********************************************************************/
	static function isAlphanumeric($value, $length=array())
	{
		//型チェック
		if(!is_string($value)){
			return false;
		}

		//半角英数チェック
		if(!preg_match("/^[a-zA-Z0-9]+$/", $value)){
			return false;
		}

		//最大文字数チェック
		if( isset($length["max"]) && is_numeric($length["max"]) ){
			if(mb_strlen($value) > $length["max"]){
				return false;
			}
		}

		//最小文字数チェック
		if( isset($length["min"]) && is_numeric($length["min"]) ){
			if(mb_strlen($value) < $length["min"]){
				return false;
			}
		}

		return true;
	}


	/*********************************************************************
	*   関数名：notWrongChar()
	*   概要  ：ダメな文字コードが含まれているかどうかのチェック
	*           指定されたエンコードリストに変換して行って、一周して同じかどうかで判断します。
	*           ちょっと微妙な処理だけど、少しでも文字化け回避のために
	*   引数  ：$value          チェック対象
	*           $default_encode valueの文字コード
	*           $encodes        チェックする文字コード
	*  -------------------------------------------------------------------
	*   戻り値：true	NG含まない
	*           false	NG含む
	*********************************************************************/
//	static function notWrongChar($value, $default_encode="UTF-8", $encodes=array("UTF-8", DEFAULT_ENCODE_MOBILE))
	static function notWrongChar($value, $default_encode="UTF-8", $encodes=array("UTF-8", "SJIS", "EUC-JP"))
//	static function notWrongChar($value, $default_encode="UTF-8", $encodes=array("UTF-8", "SJIS-win", "eucJP-win"))
	{
		$org_value = $encoded_value = $value;

		$pre_encode = $default_encode;
		foreach($encodes as $encode){
			if($encode == $default_encode)	continue;

			$encoded_value = mb_convert_encoding($encoded_value, $encode, $pre_encode);
			$pre_encode = $encode;
		}

		$encoded_value = mb_convert_encoding($encoded_value, $default_encode, $pre_encode);

		if($org_value !== $encoded_value){
			return false;
		}

		return true;
	}


	/*********************************************************************
	*   関数名：notHankaku()
	*   概要  ：半角カナが含まれているかどうかのチェック
	*   引数  ：$value          チェック対象
	*  -------------------------------------------------------------------
	*   戻り値：true	半角カナ含まない
	*           false	半角カナ含む
	*********************************************************************/
	static function notHankaku($value, $encode="UTF-8")
	{
		$org_value = $value;

		$value = mb_convert_kana($value, "K", $encode);

		if($org_value !== $value){
			return false;
		}

		return true;
	}


	/*********************************************************************
	*   関数名：isKana()
	*   概要  ：全角カナかどうかのチェック
	*   引数  ：$value          チェック対象
	*  -------------------------------------------------------------------
	*   戻り値：true	全角カナ
	*           false	全角カナ以外を含む
	*********************************************************************/
	static function isKana($value, $hiragana=false)
	{
		//ひらがなチェック
		if($hiragana){
			if (preg_match("/^[ぁ-ん]+$/u", $value)) {
				return true;
			}
		}
		//カタカナチェック
		else{
			if (preg_match("/^[ァ-ヶー]+$/u", $value)) {
				return true;
			}
		}

		return false;
	}


	/*********************************************************************
	*   関数名：not4byte()
	*   概要  ：４バイト文字(キャリア絵文字)のチェック
	*   引数  ：$value          チェック対象
	*  -------------------------------------------------------------------
	*   戻り値：true	OK
	*           false	キャリア絵文字を含む
	*********************************************************************/
	static function not4byte($value)
	{
		//4バイト文字(携帯絵文字とか)
		if(preg_match("/[\x{10000}-\x{10FFFF}]/u", $value))
//		if(preg_match("/[\xF0-\xF7][\x80-\xBF][\x80-\xBF][\x80-\xBF]/", $value))
		{
			return false;
		}

		return true;
	}


	/*********************************************************************
	*   概要  ：日付データとして正しいかチェック
	*   引数  ：$year         年
    *           $month        月
    *           $day          日 
	*  -------------------------------------------------------------------
	*   戻り値：true/false	
	*********************************************************************/
	static function isDate($year, $month, $day)
	{
		if( !self::notNull($year)  || !self::isInt($year)  ||
			!self::notNull($month) || !self::isInt($month) ||
			!self::notNull($day)   || !self::isInt($day)   || 
			!checkdate($month, $day, $year) )
		{
			return false;
		}

		return true;
	}


	/*********************************************************************
	*   概要  ：日付データとして正しいかチェック
	*   引数  ：$year         年
    *           $month        月
    *           $day          日 
	*  -------------------------------------------------------------------
	*   戻り値：true/false	
	*********************************************************************/
	static function isYYYYMMDD($yyyymmdd)
	{
		if( !self::notNull($yyyymmdd)  || !self::isInt($yyyymmdd) ){
			return false;
		}

		$datetime = strptime($yyyymmdd, "%Y%m%d");

		if($datetime == false){
			return false;
		}

		$time_array = array(
			"year"		=> intval($datetime["tm_year"] + 1900),
			"month"		=> intval($datetime["tm_mon"] + 1),
			"day"		=> intval($datetime["tm_mday"]),
		);

		//年月日の妥当性チェック
		if(checkdate($time_array["month"], $time_array["day"], $time_array["year"]) == false){
			return false;
		}

		return true;
	}

}

?>
