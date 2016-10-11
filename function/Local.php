<?php
require_once( 'config.php');
require_once( DIR_LIB_COMMON . 'define.php');
require_once( DIR_LIB_FUNCTION . 'Global.php');					// 共通関数
require_once( DIR_LIB_FUNCTION . 'UA/Dividedevice.php');		// デバイス判定

class FunctionLocal{
	/*********************************************************************
	*   概要  ：リダイレクト先のURLを作成
	*   引数  ：URL 
	*		   セッションIDをつけるか
	*		   パラメータ
	*  -------------------------------------------------------------------
	*   戻り値：URL
	*********************************************************************/
	static function getRedirectUrl($url, $set_sessid=false, $query_data=array()){

		if($set_sessid){
			$query_data[session_name()] = session_id();
		}

		return $url.'?'.http_build_query($query_data);
	}



	/*********************************************************************
	*   概要  ：画像ファイル名からContent-typeを返す
	*   引数  ：URL 
	*		   セッションIDをつけるか
	*		   パラメータ
	*  -------------------------------------------------------------------
	*   戻り値：URL
	*********************************************************************/
	static function getImageContentType($filename)
	{
		$path_parts = pathinfo($filename);

		if(!isset($path_parts['extension'])){
			return null;
		}

		switch($path_parts['extension']){
			case	"jpeg":
			case	"jpg":
				return "jpeg";

			case	"png":
				return "png";

			case	"gif":
				return "gif";
		}

		return null;
	}


	/*********************************************************************
	*   概要  ：もらった数字が偶数かどうか判定します
	*   引数  ：
	*  -------------------------------------------------------------------
	*   戻り値：デバイスコード
	*********************************************************************/
	static function isEven($num)
	{
		// 偶数です
		if($num % 2 == 0) {
			return true;
		}

		// 奇数です
		return false;
	}
}