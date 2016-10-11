<?php
require_once(DIR_LIB_FUNCTION.'Crypt/AES.php');

define('CHECK_DIGIT_STR', 'mycrypt');

class myAES extends AES{

	/*********************************************************************
	 *   概要  ：暗号化
	 *   引数  ：$value 暗号化するデータ
	 *		   $key   
	 *		   $iv	
	 *  -------------------------------------------------------------------
	 *   戻り値：暗号化された文字列
	 *********************************************************************/
	public static function encrypt($value, $key, $iv, $url_encode=true)
	{
		// 複合時に、正常に複合できたか確認できるよう、前後にCHECK_DIGIT(文字列)を埋め込んでおきます
		$value = CHECK_DIGIT_STR.$value.CHECK_DIGIT_STR;

		$result = parent::encrypt($value, $key, $iv);

		if($url_encode){
			// メールの本文に書き込むURLなのでencodeしておきます
			return urlencode(base64_encode($result));
		}

		return base64_encode($result);
	}

	/*********************************************************************
	 *   概要  ：複合
	 *   引数  ：$value 複合するデータ
	 *		   $key   
	 *		   $iv	
	 *  -------------------------------------------------------------------
	 *   戻り値：複合された文字列
	 *********************************************************************/
	public static function decrypt($value, $key, $iv)
	{
//		$value = base64_decode(urldecode($value));
		$value = base64_decode($value);

		$result = trim(parent::decrypt($value, $key, $iv));

		// CHECK_DIGIT(文字列)で正常に複合できたか確認します
		$dec_str = preg_replace("/^".CHECK_DIGIT_STR."(.*)".CHECK_DIGIT_STR."$/", '\1', $result);
		if($result == $dec_str){
			return false;
		}

		return $dec_str;
	}

}