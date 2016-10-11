<?php

/**
 * MyTemplateEngine.php
 * Templateエンジンクラス
 *
 * @author	ooseki
 */

require_once ('config.php');


class MyTemplateEngine{

	const ENCODE = DEFAULT_ENCODE;	//デフォルトの出力文字コード。ASCII, JIS, UTF-8, eucJP, SJIS のどれかにして
	const BRCODE = '<BR />';		//改行コードの変換文字列
	const SUFFIX = 'html';			//デフォルトのファイル識別子

	private $root_path;
	private $template_path;

	//表示用オプション
	// インスタンス化時および、専用メソッドで設定します
	private $options = array(
				"encoding"					=> self::ENCODE,		// 表示する際の文字コード
				"br_tag"					=> self::BRCODE,		// 改行部分に挿入する変換タグ
				"html_suffix"				=> self::SUFFIX,		// テンプレートファイルのdefault識別子

				"include"					=> true,				// SSIのinclude/include virtualに対応します
	);

	private $template = null;
	private $values = null;

	function __construct( $root_path=DIR_HTDOCS, $template_path=DIR_TEMPLATE, $options=array() )
	{
		// 表示文字コードが定義されていたらそれを採用
		if(defined("DEFAULT_OUTPUT_ENCODE")){
			$this->options["encoding"] = DEFAULT_OUTPUT_ENCODE;
		}

		//さらに指定されたオプションでうわがき
		$this->options = array_merge($this->options, $options);

		$this->root_path = $root_path;
		$this->template_path = $template_path;
	}


	//テンプレートを変数に入れておきます
	function setTemplate( $template, $template_encoding=null )
	{
		//Template文字コード判別
		if(empty($template_encoding)){
			$template_encoding		= mb_detect_encoding($template, "ASCII,JIS,UTF-8,eucJP,SJIS");
		}

		if($this->options["encoding"] != $template_encoding){
			//Template文字コード変換
			$template = mb_convert_encoding($template, $this->options["encoding"], $template_encoding);
		}

		//templateに入れておきます
		$this->template = $template;

		return true;
	}


	//テンプレートファイルの内容を変数に入れておきます
	function loadTemplatefile( $filename=null, $file_encoding=null )
	{
		//phpのファイル情報
		$script_pathinfo = pathinfo($_SERVER['SCRIPT_FILENAME']);
//		$default_template_dir = str_replace($this->root_path, $this->template_path, $script_pathinfo["dirname"]);
		$default_template_dir = str_replace(rtrim($this->root_path,"/"), rtrim($this->template_path,"/"), $script_pathinfo["dirname"]);

		//探してみるテンプレートファイル
		$template_search_list = array();

		//ファイルが指定されている場合
		if(!empty($filename)){
			$pathinfo = pathinfo($filename);

			//ファイルは絶対パスで指定されてますよ
			if(preg_match('/^\//', $pathinfo["dirname"]) != 0){
				//じゃぁ、それでいいよ
				$template_search_list[] = $filename;
			}
			//ファイルは相対パスで指定されてますよ
			else{
				//この場合は最初に$this->template_pathをさがして、そのあとにカレントディレクトリを探します
				$template_search_list[] = sprintf("%s/%s", $default_template_dir, $filename);
				$template_search_list[] = sprintf("%s/%s", $script_pathinfo["dirname"], $filename);
			}
		}
		//ファイルが指定されていない場合
		else{
			//この場合は最初に$this->template_pathをさがして、そのあとにカレントディレクトリを探します
			$template_search_list[] = sprintf("%s/%s.%s", $default_template_dir, $script_pathinfo["filename"], $this->options["html_suffix"]);
			$template_search_list[] = sprintf("%s/%s.%s", $script_pathinfo["dirname"], $script_pathinfo["filename"], $this->options["html_suffix"]);
		}

		$template = false;

		//ではファイルを探してみましょう
		foreach($template_search_list as $template_search){
			//templateファイル読み込み
			if(file_exists($template_search)){
				//あったよ
				$template = file_get_contents($template_search);
				break;
			}
		}

		if($template == false && DEBUG_MODE==1){
			$template = "テンプレートファイル【". implode("】【", $template_search_list) ."】が読み込めませんでした。";
		}

		return $this->setTemplate( $template, $file_encoding );
	}


	//includeファイルの内容を変数に入れておきます
	function getIncludefile( $filename, $virtual=true, $file_encoding=null )
	{
		//phpのファイル情報
		$script_pathinfo = pathinfo($_SERVER['SCRIPT_FILENAME']);
//		$default_template_dir = str_replace(DIR_HTDOCS, DIR_TEMPLATE, $script_pathinfo["dirname"]);
		$default_template_dir = str_replace(rtrim(DIR_HTDOCS,"/"), rtrim(DIR_TEMPLATE,"/"), $script_pathinfo["dirname"]);

		//探してみるテンプレートファイル
		$template_search_list = array();

		//virtual includeの場合
		if($virtual){
			$pathinfo = pathinfo($filename);

			//ファイルは絶対パスで指定されてますよ
			if(preg_match('/^\//', $pathinfo["dirname"]) != 0){
				//最初にDIR_TEMPLATEをさがして、そのあとにカレントディレクトリを探します
				$template_search_list[] = sprintf("%s%s", rtrim(DIR_TEMPLATE,"/"), $filename);
				$template_search_list[] = sprintf("%s%s", rtrim(DIR_HTDOCS,"/"), $filename);
			}
			//ファイルは相対パスで指定されてますよ
			else{
				//この場合は最初にDIR_TEMPLATEをさがして、そのあとにカレントディレクトリを探します
				$template_search_list[] = sprintf("%s/%s", $default_template_dir, $filename);
				$template_search_list[] = sprintf("%s/%s", $script_pathinfo["dirname"], $filename);
			}

			$template = false;

			//ではファイルを探してみましょう
			foreach($template_search_list as $template_search){
				//templateファイル読み込み
				if(file_exists($template_search)){
					//あったよ
					$template = file_get_contents($template_search);
					break;
				}
			}
		}
		//include
		else{
			$template = file_get_contents($filename);
		}


		if($template == false && DEBUG_MODE==1){
			$template = "includeファイル【{$filename}】が読み込めませんでした。";
		}

		//Template文字コード判別
		if(empty($template_encoding)){
			$template_encoding		= mb_detect_encoding($template, "ASCII,JIS,UTF-8,eucJP,SJIS");
		}

		if($this->options["encoding"] != $template_encoding){
			//Template文字コード変換
			$template = mb_convert_encoding($template, $this->options["encoding"], $template_encoding);
		}

		return $template;
	}


	/**
		showAll($values=array(), $ignore_escape_list=null, $add_br_list=null, $ignore_encode_list=null, $succeed_value=true)

		テンプレートファイルを使ってパースして表示してあげる

		引数	
				$values					置換配列
				$ignore_escape_list		Tagをサニタイズしない変数名リスト(配列)  trueを指定した場合はすべて
				$add_br_list			改行コードを改行タグに変換する変数名リスト(配列)
				$ignore_encode_list		文字コード変換しない変数名リスト(配列)
				$succeed_value			上位配列の変数を下位配列に継承するか

		戻り値	なし
	**/
	function showAll($values=array(), $ignore_escape_list=null, $add_br_list=null, $ignore_encode_list=null, $succeed_value=true)
	{
		//パースした結果を表示
		print $this->getAll($values, $ignore_escape_list, $add_br_list, $ignore_encode_list, $succeed_value);
	}


	/**
		getAll($values=array(), $ignore_escape_list=null, $add_br_list=null, $ignore_encode_list=null, $succeed_value=true)

		テンプレートファイルを使ってパースしてあげる

		引数	
				$values					置換配列
				$ignore_escape_list		Tagをサニタイズしない変数名リスト(配列)  trueを指定した場合はすべて
				$add_br_list			改行コードを改行タグに変換する変数名リスト(配列)
				$ignore_encode_list		文字コード変換しない変数名リスト(配列)
				$succeed_value			上位配列の変数を下位配列に継承するか

		戻り値	パースされた文字列
	**/
	function getAll($values=array(), $ignore_escape_list=null, $add_br_list=null, $ignore_encode_list=null, $succeed_value=true)
	{
		//テンプレートファイルから読み込んでおいて
		if($this->template == null){
			$this->loadTemplatefile();
		}

		//変数をセットして
		$this->setValues($values, $ignore_escape_list, $add_br_list, $ignore_encode_list);

		//パースした結果を返します
		return $this->parse($succeed_value);
	}


	//parseするよー
	function parse($succeed_value)
	{
		$parsed = $this->parse_block($this->template, $this->values, $succeed_value, false);

		return $parsed;
	}


	//値をセットしておきます
	private function setValues($values, $ignore_escape_list=null, $add_br_list=null, $ignore_encode_list=null)
	{
		$this->values = $this->changeValues($values, $ignore_escape_list, $add_br_list, $ignore_encode_list);
	}



	/**
		changeValues($values, $ignore_escape_list=null, $add_br_list=null)

		バインド変数を再帰的に置換する

		引数	$values				変換対象データ配列(多次元配列も可)
				$ignore_escape_list		Tagをサニタイズしない変数名リスト(配列)  trueを指定した場合はすべて
				$add_br_list			改行コードを改行タグに変換するバインド変数名リスト(配列)

		戻り値	true	成功
				false	失敗
	**/
	private function changeValues($values, $ignore_escape_list=null, $add_br_list=null, $ignore_encode_list=null)
	{
		if($ignore_escape_list !== true && $ignore_escape_list && !is_array($ignore_escape_list)){		$ignore_escape_list	= array($ignore_escape_list);		}
		if($add_br_list && !is_array($add_br_list)){					$add_br_list		= array($add_br_list);				}
		if($ignore_encode_list && !is_array($ignore_encode_list)){		$ignore_encode_list = array($ignore_encode_list);		}

		//文字コード変換してから
		$this->encodeValues($values, $ignore_encode_list);
		//エスケープして
		if($ignore_escape_list !== true){
			$this->escapeTag($values, $ignore_escape_list);
		}
		//brタグ挿入
		$this->addBrTag($values, $add_br_list);

		return $values;
	}



	/**
		addBrTag( &$list, $keylist=null )

		改行コードをHTMLの改行文字に変換します
		引数で受け取った配列について、再帰的に実行されます。
		引数	$list			変換対象データの配列(多次元も可)
				$keylist		変換対象となるkeyのリスト。指定されない場合、変換しませんよ。
	**/
	private function addBrTag( &$list, $keylist=null )
	{
		foreach($list as $key=>$value){
			// 値が配列だったら再帰呼び出しで、一階層進める
			if(is_array($value)){
				$this->addBrTag($list[$key], $keylist);
			}else{
				// keyがリストに含まれる場合は変換
				if(is_array($keylist) && in_array($key, $keylist) ){
					//nl2brだと<BR>タグが指定できないので、これで
					$list[$key] = str_replace(array("\r\n","\r","\n"), array($this->options["br_tag"], $this->options["br_tag"], $this->options["br_tag"]), $list[$key]);
				}
			}
		}
	}


	/**
		escapeTag( &$list, $ignorekeylist=null )

		htmlspecialcharsで変換します
		引数で受け取った配列について、再帰的に実行されます。
		引数	$list			変換対象データの配列(多次元も可)
				$ignorekeylist	変換対象外となるkeyのリスト。指定されない場合、すべてが対象。
	**/
	private function escapeTag( &$list, $ignorekeylist=null )
	{
		foreach($list as $key=>$value){
			// 値が配列だったら再帰呼び出しで、一階層進める
			if(is_array($value)){
				$this->escapeTag($list[$key], $ignorekeylist);
			}else{
				// key指定がない場合と、keyがignoreリストに含まれない場合は変換
				if(!(is_array($ignorekeylist) && in_array($key, $ignorekeylist)) ){
					$list[$key] = htmlspecialchars($list[$key], ENT_QUOTES, $this->options["encoding"]);
				}
			}
		}
	}


	/**
		encodeValues( &$list, $ignorekeylist=null )

		文字コード変換します
		引数で受け取った配列について、再帰的に実行されます。
		引数	$list			変換対象データの配列(多次元も可)
				$ignorekeylist		変換対象外となるkeyのリスト。指定されない場合、すべてが対象。
	**/
	private function encodeValues( &$list, $ignorekeylist=null )
	{
		foreach($list as $key=>$value){
			// 値が配列だったら再帰呼び出しで、一階層進める
			if(is_array($value)){
				$this->encodeValues($list[$key], $ignorekeylist);
			}else{
				// key指定がない場合と、keyがリストに含まれる場合は変換
				if($ignorekeylist == null ||
					(is_array($ignorekeylist) && !in_array($key, $ignorekeylist)) ){
					$list[$key] = mb_convert_encoding($list[$key], $this->options["encoding"], mb_detect_encoding($list[$key]));
				}
			}
		}
	}



	/**
		parse_block($template, $values, $succeed_value, $accept_loop)

		これが今回のメインイベント

		引数	$template				テンプレート文字列
				$values					置換配列
				$succeed_value			上位配列の変数を下位配列に継承するか
				$accept_loop			全体の繰り返し置換を行うかどうか(ROOTの繰り返し処理を避けるため)のフラグ
				$include_list			SSIでincludeされているファイルのリスト(重複することによる無限ループ回避のため)

		戻り値	パースされた文字列
	**/
	private function parse_block($template, $values, $succeed_value, $accept_loop=true, $include_list=array())
	{
		//継承したーい、けど継承するものがない
		if(!is_array($succeed_value) && $succeed_value == true){
			$succeed_value = array();
		}

		//継承したーい
		if(is_array($succeed_value)){
			//配列以外くれ
			foreach($values as $key=>$val){
				if(!is_array($val)){
					$succeed_value[$key] = $val;
				}
			}
			$replace_list = $succeed_value;
		}
		else{
			$succeed_value = false;
			$replace_list = array();
			//配列以外くれ
			foreach($values as $key=>$val){
				if(!is_array($val)){
					$replace_list[$key] = $val;
				}
			}
		}

		$parsed = $template;

		//テンプレートからブロック要素を抜き出します
		//ブロック名は数字始まりNGよ
		$block_reg = '/<!--\s+BEGIN\s+([\.A-Za-z_-][\.0-9A-Za-z_-]+)\s+-->(.*?)<!--\s+END\s+\1\s+-->/sm';
		preg_match_all($block_reg, $template, $blocks, PREG_SET_ORDER);
		$parse_flag = false;

		//ブロック置換なの？
		foreach($blocks as $block){
			$block_all		= $block[0];		//ここにはブロック全体がはいってるよー
			$block_name		= $block[1];		//ここにはブロック名がはいってるよー
			$block_content	= $block[2];		//ここにはブロックの内容がはいってるよー


// preg_replaceだと、"$"のような文字が入っていたばあいうまくいかない
			if(isset($values[$block_name])){
				//ブロックには再帰呼び出しで対抗
				if(!empty($values[$block_name])){
					if(is_array($values[$block_name])){
						$parsed = str_replace($block_all, $this->parse_block($block_content, $values[$block_name], $succeed_value), $parsed);
//						$parsed = preg_replace("/".preg_quote($block_all,"/")."/", $this->parse_block($block_content, $values[$block_name], $succeed_value), $parsed, 1);

						$parse_flag = true;
					}
					//そのまま表示
					else{
						$parsed = str_replace($block_all, $block_content, $parsed);
//						$parsed = preg_replace("/".preg_quote($block_all,"/")."/", $block_content, $parsed, 1);

						$parse_flag = true;
					}
				}
				//ブロック指定が空の場合は削除
				else{
					$parsed = str_replace($block_all, "", $parsed);
//					$parsed = preg_replace("/".preg_quote($block_all,"/")."/", "", $parsed, 1);
				}
			}
			//ブロック指定がない場合は削除
			else{
				$parsed = str_replace($block_all, "", $parsed);
//				$parsed = preg_replace("/".preg_quote($block_all,"/")."/", "", $parsed, 1);
			}
		}

		//じゃぁ、繰り返しなの？
		if($accept_loop && !$parse_flag){
			$parse_str = null;
			foreach($values as $value){
				if(is_array($value) && !empty($value)){
					$parse_str .= $this->parse_block($template, $value, $succeed_value);
					$parse_flag = true;
				}
			}

			if($parse_flag){
				return $parse_str;
			}
		}
		// SSIのinclude virtualに対応します
		if($this->options["include"]){
			//include virtual
			$include_reg = '/<!--\s*#include\s+virtual\s*=\s*"(.*?)"\s*-->/sm';
			preg_match_all($include_reg, $template, $includes, PREG_SET_ORDER);

			foreach($includes as $include){
				$include_all		= $include[0];		//ここにはinclude全体がはいってるよー
				$include_file		= $include[1];		//ここにはincludeファイル名がはいってるよー

				//無限ループを(ある程度)防ぐために同じファイルのincludeはナシ
				if(in_array($include_file, $include_list)){
					continue;
				}

				// includeするファイルを読み込みます
				$include_content = $this->getIncludefile( $include_file, true );

				//無限include対策
				$include_list[] = $include_file;

				$parsed = str_replace($include_all, $this->parse_block($include_content, $values, $succeed_value, false, $include_list), $parsed);
			}

			//includel
			$include_reg = '/<!--\s*#include\s*=\s*"(.*?)"\s*-->/sm';
			preg_match_all($include_reg, $template, $includes, PREG_SET_ORDER);

			foreach($includes as $include){
				$include_all		= $include[0];		//ここにはinclude全体がはいってるよー
				$include_file		= $include[1];		//ここにはincludeファイル名がはいってるよー

				//無限ループを(ある程度)防ぐために同じファイルのincludeはナシ
				if(in_array($include_file, $include_list)){
					continue;
				}

				// includeするファイルを読み込みます
				$include_content = $this->getIncludefile( $include_file, false );

				//無限include対策
				$include_list[] = $include_file;

				$parsed = str_replace($include_all, $this->parse_block($include_content, $values, $succeed_value, false, $include_list), $parsed);
			}
		}

		//ブロックが終わったので、プレースホルダ―を探します
		//プレースホルダーを探して
		$placeholder_reg = '/\{([\.0-9A-Za-z_-]+)\}/sm';
		preg_match_all($placeholder_reg, $parsed, $placeholders, PREG_PATTERN_ORDER);

		//プレースホルダーの{}の中身リスト
		$placeholders = array_unique($placeholders[1]);

		foreach($placeholders as $placeholder){
			$parsed = str_replace("{".$placeholder."}", isset($replace_list[$placeholder])?$replace_list[$placeholder]:"", $parsed);
		}

		return $parsed;
	}



	static function array2bindarray($array_value, $key_name)
	{
		$bindarray = array();

		foreach($array_value as $key=>$value){
			$bindarray[$key] = array($key_name => $value);
		}

		return $bindarray;
	}

}

?>
