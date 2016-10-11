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
//	equal()					変数が一致するかチェックする
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

require_once(DIR_LIB_FUNCTION . 'Validate/Validate.php');

class FunctionMyValidate extends FunctionValidate{
	protected	$db = null;					// DB接続

	function __construct($db=null){
// DB必須の場合は接続
//		if(is_null($db)){
//			$this->db =  new DBUsersql();
//		}else{
//			$this->db =  $db;
//		}

// DB必須でない場合はそのまま
		$this->db =  $db;

	}
}