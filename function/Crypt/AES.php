<?php

class AES{

	/*********************************************************************
	 *   概要  ：暗号化
	 *   引数  ：$value 暗号化するデータ
	 *		   $key   
	 *		   $iv	
	 *  -------------------------------------------------------------------
	 *   戻り値：暗号化された文字列
	 *********************************************************************/
	public static function encrypt($value, $key, $iv){
		$td = self::openModule($key, $iv);
		if(!$td){
			return false;
		}

		$result = mcrypt_generic($td, $value);
		self::closeModule($td);

		return $result;
	}

	/*********************************************************************
	 *   概要  ：複合
	 *   引数  ：$value 複合するデータ
	 *		   $key   
	 *		   $iv	
	 *  -------------------------------------------------------------------
	 *   戻り値：複合された文字列
	 *********************************************************************/
	public static function decrypt($value, $key, $iv){
		$td = self::openModule($key, $iv);
		if(!$td){
			return false;
		}

		$result = mdecrypt_generic($td, $value);
		self::closeModule($td);

		return $result;
	}

	private static function openModule($key, $iv){
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_ECB, '');
		$init_result = mcrypt_generic_init($td, $key, $iv);
		if($init_result === false || $init_result < 0){
			return false;
		}
		return $td;
	}

	private static function closeModule($td){
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
	}

}