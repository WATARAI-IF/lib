<?php
require_once( 'config.php');
require_once( DIR_LIB_COMMON . 'define.php');

require_once(DIR_LIB_PEAR . "XML/Unserializer.php");

define('XML_DIRECTORY_PATH',			DIR_DATA . "xml/");			// XMLの格納場所

class FunctionXmlData{

	/*********************************************************************
	*   関数名：getXml()
	*   概要  ：XML読み込んで配列形式にします
	*			parseのコストを抑えるためserializeデータをファイルに保存しておきます
	*   引数  ：
	*********************************************************************/
	static function getXml($filename, $forceEnum=null)
	{
		$file_path = XML_DIRECTORY_PATH . $filename;
		$serialized_path = $file_path . ".serialized";

		// パフォーマンス上げるために、XMLの内容はserializeして別ファイルに保存してあります
		if( file_exists($serialized_path) && filemtime($serialized_path) > filemtime($file_path)){
			if(($serialized = file_get_contents($serialized_path)) !== false ){
				if(($xml_data = unserialize($serialized)) !== false){
					return $xml_data;
				}
			}
		}

		// XMLファイルが更新されるか、seliarizeファイルが存在しない場合に、serializeファイルを更新します
		$options = array(
							"complexType" => "array",							// データは配列形式で
							"parseAttributes" => true,							// 属性もパースします
		);
		if(!empty($forceEnum)){
			$options["forceEnum"] = $forceEnum;
		}

		$xml = new XML_Unserializer($options);

		// XMLを読込みます
		if( $xml->unserialize($file_path, true) == false ){
			return array();
		}

		// パースします
		$xml_data = $xml->getUnserializedData();

		// serializeファイルに保存します
		@file_put_contents($serialized_path, serialize($xml_data));

		return $xml_data;
	}




}
