<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// 携帯判定処理関係のclass
//	日々変わりゆくと思うので、必要に応じて編集して行きましょう
//
//--------------------------------------------------------------------
// @filename	AgentMobile.php
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
// getCarrierName			キャリアコード取得
// getUid					ユーザIDを取得
// isCellPhone				がらけー判定
// isSmartPhone				すまほ判定
// isTablet					たぶれっと判定
// isAndroid()				Android判定
// isIphone()				isIphone判定
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/



class UADividedevice
{
	const UD_PC			= 0;
	const UD_DOCOMO		= 1;
	const UD_SOFTBANK	= 2;
	const UD_AU			= 3;
	const UD_WILLCOM	= 4;
	const UD_ANDROID	= 5;
	const UD_IPHONE		= 6;
	const UD_SMARTPHONE	= 7;
	const UD_ANDROIDTAB	= 8;
	const UD_IPAD		= 9;


	//********************************************************************
	// [Function Name]	getCarrierName()
	// [Summary]		キャリアコード取得
	//--------------------------------------------------------------------
	// [Parameter]		NONE
	// [Return Value]	キャリアコード 上部でdefineしてあるもの
	//********************************************************************

	static function getCarrierName()
	{
		$carrier_code = self::UD_PC;

		if(!isset($_SERVER['HTTP_USER_AGENT'])){
			return $carrier_code;
		}

		// UserAgent取得
		$agent = $_SERVER['HTTP_USER_AGENT'];

		if ( substr_count ($agent, "Android") != 0 ) {
			if ( substr_count($agent, "Mobile") != 0 ) {
				if ( substr_count ($agent, "SC-01C") != 0 ) {
					$carrier_code = self::UD_ANDROIDTAB;
				}
				else{
					$carrier_code = self::UD_ANDROID;
				}
			}
			else{
				if ( substr_count ($agent, "L-06C") != 0 ) {
					$carrier_code = self::UD_ANDROID;
				}
				else{
					$carrier_code = self::UD_ANDROIDTAB;
				}
			}
		}
		elseif ( substr_count($agent, "iPhone") != 0 ) {
			$carrier_code = self::UD_IPHONE;
		}
		elseif ( substr_count($agent, "iPad") != 0 ) {
			$carrier_code = self::UD_IPAD;
		}
		elseif ( substr_count($agent, "iPod") != 0 ) {
			$carrier_code = self::UD_IPHONE;
		}
		elseif ( substr_count($agent, "BlackBerry") != 0 || substr_count($agent, "IEMobile") != 0 ) {
			$carrier_code = self::UD_SMARTPHONE;
		}
		elseif ( substr_count($agent, "DoCoMo") != 0 ) {
			$carrier_code = self::UD_DOCOMO;
		}
		elseif ( substr_count($agent, "J-PHONE") != 0 || substr_count($agent, "Vodafone") != 0 || substr_count($agent, "MOT") != 0 || substr_count($agent, "SoftBank") != 0 ) {
			$carrier_code = self::UD_SOFTBANK;
		}
		elseif ( substr_count($agent, "KDDI") != 0 || substr_count($agent, "UP.Browser") != 0 ) {
			$carrier_code = self::UD_AU;
		}
		elseif ( substr_count($agent, "PDXGW") != 0 || substr_count($agent, "DDIPOCKET") != 0 || substr_count($agent, "WILLCOM") != 0 ) {
			$carrier_code = self::UD_WILLCOM;
		}

		return $carrier_code;
	}



	//********************************************************************
	// [Function Name]	getUid()
	// [Summary]		ユーザIDを取得
	//--------------------------------------------------------------------
	// [Parameter]		None
	// [Return Value]	$uid(ユーザID)
	//********************************************************************
	static function getUid()
	{
		$carrier = self::getCarrierName();

		// docomo端末
		if ($carrier == self::UD_DOCOMO ) {
			if(isset($_SERVER["HTTP_X_DCMGUID"])){
				$uid = $_SERVER["HTTP_X_DCMGUID"];
			}else{
				$uid= false;
			}
/*
			preg_match("/^.+ser([0-9a-zA-Z]+).*$/", $_SERVER["HTTP_USER_AGENT"], $match);
			if(isset($match[1])){
				$uid = $match[1];
			}else{
				$uid= false;
			}
*/
		}
		// au端末
		elseif($carrier == self::UD_AU){
			$uid = $_SERVER["HTTP_X_UP_SUBNO"];
		}
		// SoftBank端末
		elseif($carrier == self::UD_SOFTBANK){
//			$uid = $_SERVER["HTTP_X_JPHONE_UID"];
			preg_match("/^.+\/(SN[0-9a-zA-Z]+).*$/", $_SERVER["HTTP_USER_AGENT"], $match);
			if(isset($match[1])){
				$uid = $match[1];
			}else{
				$uid= false;
			}
		}else{
			return false;
		}

		return $uid;
	}

	//********************************************************************
	// [Function Name]	isPC()
	// [Summary]		PC判定
	//--------------------------------------------------------------------
	// [Parameter]		None
	// [Return Value]	true/false
	//********************************************************************
	static function isPC()
	{
		$carrier = self::getCarrierName();

		switch($carrier){
			case self::UD_DOCOMO :
			case self::UD_SOFTBANK :
			case self::UD_AU :
			case self::UD_WILLCOM :
			case self::UD_ANDROID :
			case self::UD_IPHONE :
			case self::UD_SMARTPHONE :
			case self::UD_ANDROIDTAB :
			case self::UD_IPAD :
				return false;
			case self::UD_PC :
			default :
				return true;
		}

		return false;
	}

	//********************************************************************
	// [Function Name]	isCellPhone()
	// [Summary]		がらけー判定
	//--------------------------------------------------------------------
	// [Parameter]		None
	// [Return Value]	true/false
	//********************************************************************
	static function isCellPhone()
	{
		$carrier = self::getCarrierName();

		switch($carrier){
			case self::UD_DOCOMO :
			case self::UD_SOFTBANK :
			case self::UD_AU :
			case self::UD_WILLCOM :
				return true;
			case self::UD_ANDROID :
			case self::UD_IPHONE :
			case self::UD_SMARTPHONE :
			case self::UD_ANDROIDTAB :
			case self::UD_IPAD :
			case self::UD_PC :
			default :
				return false;
		}

		return false;
	}


	//********************************************************************
	// [Function Name]	isSmartPhone()
	// [Summary]		すまほ判定
	//--------------------------------------------------------------------
	// [Parameter]		None
	// [Return Value]	true/false
	//********************************************************************
	static function isSmartPhone()
	{
		$carrier = self::getCarrierName();

		switch($carrier){
			case self::UD_ANDROID :
			case self::UD_IPHONE :
			case self::UD_SMARTPHONE :
				return true;
			case self::UD_DOCOMO :
			case self::UD_SOFTBANK :
			case self::UD_AU :
			case self::UD_WILLCOM :
			case self::UD_ANDROIDTAB :
			case self::UD_IPAD :
			case self::UD_PC :
			default :
				return false;
		}

		return false;
	}


	//********************************************************************
	// [Function Name]	isTablet()
	// [Summary]		たぶれっと判定
	//--------------------------------------------------------------------
	// [Parameter]		None
	// [Return Value]	true/false
	//********************************************************************
	static function isTablet()
	{
		$carrier = self::getCarrierName();

		switch($carrier){
			case self::UD_ANDROIDTAB :
			case self::UD_IPAD :
				return true;
			case self::UD_DOCOMO :
			case self::UD_SOFTBANK :
			case self::UD_AU :
			case self::UD_WILLCOM :
			case self::UD_ANDROID :
			case self::UD_IPHONE :
			case self::UD_SMARTPHONE :
			case self::UD_PC :
			default :
				return false;
		}

		return false;
	}


	//********************************************************************
	// [Function Name]	isAndroid()
	// [Summary]		Android判定
	//--------------------------------------------------------------------
	// [Parameter]		$tablet		タブレット端末を含むかどうか
	// [Return Value]	true/false
	//********************************************************************
	static function isAndroid( $tablet = true )
	{
		$carrier = self::getCarrierName();

		if($tablet){
			switch($carrier){
				case self::UD_ANDROID :
				case self::UD_ANDROIDTAB :
					return true;
				case self::UD_DOCOMO :
				case self::UD_SOFTBANK :
				case self::UD_AU :
				case self::UD_WILLCOM :
				case self::UD_IPHONE :
				case self::UD_IPAD :
				case self::UD_SMARTPHONE :
				case self::UD_PC :
				default :
					return false;
			}
		}else{
			switch($carrier){
				case self::UD_ANDROID :
					return true;
				case self::UD_DOCOMO :
				case self::UD_SOFTBANK :
				case self::UD_AU :
				case self::UD_WILLCOM :
				case self::UD_ANDROIDTAB :
				case self::UD_IPHONE :
				case self::UD_IPAD :
				case self::UD_SMARTPHONE :
				case self::UD_PC :
				default :
					return false;
			}
		}

		return false;
	}


	//********************************************************************
	// [Function Name]	isIphone()
	// [Summary]		isIphone判定
	//--------------------------------------------------------------------
	// [Parameter]		$tablet		タブレット端末を含むかどうか
	// [Return Value]	true/false
	//********************************************************************
	static function isIphone( $tablet = true )
	{
		$carrier = self::getCarrierName();

		if($tablet){
			switch($carrier){
				case self::UD_IPHONE :
				case self::UD_IPAD :
					return true;
				case self::UD_DOCOMO :
				case self::UD_SOFTBANK :
				case self::UD_AU :
				case self::UD_WILLCOM :
				case self::UD_ANDROID :
				case self::UD_ANDROIDTAB :
				case self::UD_SMARTPHONE :
				case self::UD_PC :
				default :
					return false;
			}
		}else{
			switch($carrier){
				case self::UD_IPHONE :
					return true;
				case self::UD_DOCOMO :
				case self::UD_SOFTBANK :
				case self::UD_AU :
				case self::UD_WILLCOM :
				case self::UD_ANDROID :
				case self::UD_ANDROIDTAB :
				case self::UD_IPAD :
				case self::UD_SMARTPHONE :
				case self::UD_PC :
				default :
					return false;
			}
		}

		return false;
	}
}
?>
