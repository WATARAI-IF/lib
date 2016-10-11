<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// $_REQUESTの内容をトリミングしてくれます
//
//--------------------------------------------------------------------
// @filename	Controller.php
// @create		2011-12-01
// @author		n.ooseki
//
// Subversionのcommit情報
//   $Date$
//   $Rev$
//   $Author$
//
//--------------------------------------------------------------------
// List of Function		すべてstaticな呼び出しは不可です
//--------------------------------------------------------------------
// class Request_Controller
//	__construct				コンストラクタ
// getRequestParams			トリミングした結果を返します
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

require_once("config.php");

class ParameterRequest {
	var $_data = Array();
	private $encoding = DEFAULT_ENCODE;		// 文字コード

	/**
	 * コンストラクタ
	 *		引数：	文字コード
	 *				対象とするパラメータ(php.iniの"variables_order"と同様文字列。ただし、"G","P","C"のみ)
	 *					"G"	$_GET
	 *					"P"	$_POST
	 *					"C"	$_COOKIE
	 */
	public function __construct($code=null, $target="GP") {
		$this->_data = array();

		// 文字コードが指定されていたらそれを採用
		if($code != null){
			$this->encoding = $code;
		}elseif(defined("DEFAULT_OUTPUT_ENCODE")){
			$this->encoding = DEFAULT_OUTPUT_ENCODE;
		}

		for($i=0; $i<strlen($target); $i++){
			$mode = substr ( $target , $i, 1);

			//一応、小文字でも受け付けるようにします
			switch($mode){
				case "G":
				case "g":
					$this->_data = array_merge($this->_data, $_GET);
					break;
				case "P":
				case "p":
					$this->_data = array_merge($this->_data, $_POST);
					break;
				case "C":
				case "c":
					$this->_data = array_merge($this->_data, $_COOKIE);
					break;
			}
		}

		if (!is_array($this->_data)) $this->_data = array($this->_data);

		//全要素変換
		array_walk_recursive($this->_data, array("ParameterRequest","trimRequest"), array("code"=>$this->encoding));
	}

	function trimRequest( &$item, $key, $param ){
		if( $param["code"] !== DEFAULT_ENCODE ){
			$item = mb_convert_encoding($item, DEFAULT_ENCODE, $param["code"] );
		}
		$item = trim($item);

//		$item = preg_replace('/^[　\0\r\n\s\t]*(.*)[　\0\r\n\s\t]*$/um', '$1', $item);
		$item = preg_replace('/^[　\0\r\n\s\t]*(.*?)[　\0\r\n\s\t]*$/u', '$1', $item);
//		if ($param["hankana"]) $item = mb_convert_kana($item, 'KVasn');
	}

	/**
	 * get
	 * getterﾒｿｯﾄﾞ
	 * @param  string $req_name
	 * @return string params[$req_name]
	 * @access public
	 */
	public function getRequest($name) {
		if(!isset($this->_data[$name])){
			return null;
		}

		return $this->_data[$name];
	}

	/**
	 * getParams
	 * getterﾒｿｯﾄﾞ
	 * @return array params
	 * @access public
	 */
	public function getRequestParams(){
		return $this->_data;
	}
 
}
?>