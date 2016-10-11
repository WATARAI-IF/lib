<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// データベースを使ったセッション処理の関数を定義し、
//	その後、セッションの初期処理を実行します
//
//--------------------------------------------------------------------
// @filename	pdo_session_db.php
// @create		2011-12-01
// @author		n.ooseki
//
// Subversionのcommit情報
//   $Date$
//   $Rev$
//   $Author$
//
//--------------------------------------------------------------------
// List of Function
//--------------------------------------------------------------------
// pdo_session_open			connect処理
// pdo_session_close		disconnect処理
// pdo_session_read			セッション情報読み込み処理
// pdo_session_write		session情報をテーブルへ書き込む
// pdo_session_destroy		セッション情報削除処理
// pdo_session_gc			ガーベジコレクション実行処理
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

require_once('config.php');


define ("USE_SESSION_TABLE_NAME", "session_table" );


//PDO オブジェクト初期化
$session_pdo = null;

/**
* 概要【open】
*　 db connect処理
* 説明
*		画面表示の先頭にて呼出されdbコネクションを行う。
*		また、本Connectｵﾌﾞｼﾞｪｸﾄは以後全てのDB更新処理にて使用。
* @param string $save_path
* @param string $session_name
* @access public
*/
function pdo_session_open($save_path, $session_name){
	global $session_pdo;

	$session_pdo = null;

	// MySQLサーバへ接続
	$session_pdo = new PDO( "mysql:dbname=".DB_NAME.";host=".DB_HOST, DB_USER, DB_PASS,
									array(
//									PDO::ATTR_PERSISTENT => true,			//持続的接続ON
									PDO::ATTR_PERSISTENT => false,			//持続的接続OFF	transaction処理やTemporary tableを使うので、余計なリスクは避けておきます
//									PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET utf8;'
								)
						);

	// エラーは例外処理に
	$session_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/*
	// PHP 5.3.6より前のバージョンの PDO MySQL で charset を指定する
	if(version_compare(phpversion(), '5.3.6', '<=')){
		$session_pdo->query('SET NAMES utf8');
	}
*/

	return true;

}

/**
* 概要【close】
*　 db disconnect処理
* 説明
*		画面表示の最後にて呼出されdbコネクションの切断を行う。
* @access public
*/
function pdo_session_close(){
	global $session_pdo;

	$session_pdo = null;

	return true;
}

/**
* 概要【read】
*　 セッション情報読み込み処理
* 説明
*		画面表示のopenの次に呼出されsession情報取得を行う。
* @param string $id
* @access public
*/
function pdo_session_read($id){
	global $session_pdo;

	//DBに接続されてないよ
	if($session_pdo == null){
		return array();
	}

	$session_table = USE_SESSION_TABLE_NAME;

	$sql = <<< SQL_END
SELECT session_value 
FROM {$session_table}
WHERE session_id = :session_id
;
SQL_END;

	$sth = $session_pdo->prepare($sql);
	$sth->bindParam(':session_id',	$id);
	$sth->execute();

	$sess_data = $sth->fetch(PDO::FETCH_ASSOC);

	return $sess_data["session_value"];
}

/**
* 概要【write】
*　 セッション情報書込み処理
* 説明
*		session情報をテーブルへ書き込む
* @param string $id
* @param string $sess_data
* @access public
*/
function pdo_session_write($id, $sess_data){
	global $session_pdo;

	//DBに接続されてないよ
	if($session_pdo == null){
		return true;
	}

	$session_table = USE_SESSION_TABLE_NAME;

	$sql = <<< SQL_END
INSERT INTO {$session_table} (session_id, session_value)
VALUES (:session_id, :session_value)
  ON DUPLICATE KEY 
  UPDATE session_value = :session_value,
		updated_at = NOW()
;
SQL_END;

	$server = serialize(array(	"REQUEST_TIME"		=> (isset($_SERVER["REQUEST_TIME"])?$_SERVER["REQUEST_TIME"]:null),
								"PHP_SELF"			=> (isset($_SERVER["PHP_SELF"])?$_SERVER["PHP_SELF"]:null),
								"REQUEST_METHOD"	=> (isset($_SERVER["REQUEST_METHOD"])?$_SERVER["REQUEST_METHOD"]:null),
								"REMOTE_ADDR"		=> (isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:null),
								"SERVER_ADDR"		=> (isset($_SERVER["SERVER_ADDR"])?$_SERVER["SERVER_ADDR"]:null),
								"Process ID"		=> getmypid()
							));

	//万が一$serverにコメント閉じ文字列があるとやばいので削除
	$server = str_replace( "*/", "", $server );

	$sth = $session_pdo->prepare("/* " . __FUNCTION__ . " [([" . $_SERVER['PHP_SELF'] . "])] ([(" . $server . ")]) */ " . $sql);
	$sth->bindParam(':session_id',	$id);
	$sth->bindParam(':session_value',	$sess_data);
	$sth->execute();

	return true;
}

/**
* 概要【destroy】
*　 セッション情報削除処理
* 説明
*		session情報をテーブルより削除
* @param string $id
* @access public
*/
function pdo_session_destroy($id){
	global $session_pdo;

	//DBに接続されてないよ
	if($session_pdo == null){
		return true;
	}

	$session_table = USE_SESSION_TABLE_NAME;

	$sql = <<< SQL_END
DELETE 
FROM {$session_table} 
WHERE session_id = :session_id
;
SQL_END;

	$sth = $session_pdo->prepare($sql);
	$sth->bindParam(':session_id',	$id);
	$sth->execute();

	return true;
}

/**
* 概要【gc】
*　 ガーベジコレクション実行処理
* 説明
*		セッションテーブルに残ったごみ情報を削除
* @param string $id
* @access public
*/
function pdo_session_gc($maxlifetime=SESSION_GC_MAX_LIFE_TIME){
	global $session_pdo;

	//DBに接続されてないよ
	if($session_pdo == null){
		return true;
	}

	$session_table = USE_SESSION_TABLE_NAME;

	$sql = <<< SQL_END
DELETE FROM {$session_table}  
WHERE updated_at < DATE_SUB(SYSDATE(),INTERVAL :maxlifetime SECOND)
;
SQL_END;

	$sth = $session_pdo->prepare($sql);
	$sth->bindParam(':maxlifetime', $maxlifetime);
	$sth->execute();

	return true;
}



function pdo_session_start($session_id = false){
	//セッション関係の関数定義
	if(session_set_save_handler("pdo_session_open", "pdo_session_close", "pdo_session_read", "pdo_session_write", "pdo_session_destroy", "pdo_session_gc") != true){
		throw new Exception("session_set_save_handler() Failed");
	}

	//スクリプト終了時のDBへのセッション書き込み
	register_shutdown_function('session_write_close');

	if($session_id){
		session_id($session_id);
	}

	if(session_start() != true ){
		throw new Exception("session_start() Failed");
	}

	//不正なセッションIDかチェック
	if(!isset($_SESSION['mysession'])) {
		session_regenerate_id(true);
		$_SESSION['mysession'] = true;
	}

	//セッションIDをチェック
	// session.hash_bits_per_character = 5 の場合は32けたじゃないよ
	//if((session_id()=="") || (strlen(session_id())!=32) || (!ctype_alnum(session_id())))
	if((session_id()=="") || (!ctype_alnum(session_id())))
	{
		throw new Exception("Invalid Session ID");
		exit;
	}
}


?>
