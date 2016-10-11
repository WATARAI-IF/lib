<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// DB関係のclass
//	共通で使えるものだけを集めました
//	これを継承して使いましょう
//
//--------------------------------------------------------------------
// @filename	dbconnect.php
// @create		2011-12-07
// @author		n.ooseki
//
// Subversionのcommit情報
//   $Date$
//   $Rev$
//   $Author$
//
//--------------------------------------------------------------------
// List of Function		すべてstaticな呼び出しはできません
//--------------------------------------------------------------------
//class MySqlClass
//	__construct				コンストラクタ
//	Connect					DBと接続する
//	Disconnect				DBを切断する
//
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/


require_once("config.php");

require_once( DIR_LIB_COMMON.'Logger/Text.php');			// Log系class

class DBConnect 
{
	private $dbhost;	// DB名称
	private $dbuser;	// ユーザ
	private $dbpass;	// パスワード
	private $dbname;	// DB名
	public $pdo_obj;	// PDO インスタンス

	//********************************************************************
	//  関数名：MySqlClass()
	//  概要　：構築子。メンバ変数を初期化する。
	//
	//     パラメータ    R/W            内容
	// -----------------+---+---------------------------------------------
	//  $dbhost           R   サーバ名
	//  $dbuser           R   ユーザ名
	//  $dbpass           R   パスワード
	//  $dbname           R   ＤＢ名称
	// -------------------------------------------------------------------
	//  戻り値：なし
	//********************************************************************
	function __construct( $local_values=array(), $db=array() ) 
	{
		$this->dbhost = isset($db["dbhost"])?$db["dbhost"]:DB_HOST;
		$this->dbuser = isset($db["dbuser"])?$db["dbuser"]:DB_USER;
		$this->dbpass = isset($db["dbpass"])?$db["dbpass"]:DB_PASS;
		$this->dbname = isset($db["dbname"])?$db["dbname"]:DB_NAME;

		$this->Connect($local_values);
	}


	//********************************************************************
	//  関数名：Connect()
	//  概要　：DBと接続する。
	//
	//     パラメータ    R/W            内容
	// -----------------+---+---------------------------------------------
	//  $env              R   環境サービスクラス
	// -------------------------------------------------------------------
	//  戻り値：!false.接続ID  false.失敗
	//********************************************************************
	function Connect($local_values=array())
	{
LoggerText::pwriteMessage("sql", "Connect DB");

		// MySQLサーバへ接続
		$this->pdo_obj = new PDO(
							"mysql:dbname=".$this->dbname.";charset=utf8".";host=".$this->dbhost, $this->dbuser, $this->dbpass,
								array(
//									PDO::ATTR_PERSISTENT => true,			//持続的接続ON
									PDO::ATTR_PERSISTENT => false,			//持続的接続OFF	transaction処理やTemporary tableを使うので、余計なリスクは避けておきます
									PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET utf8;',

									PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,		// クエリのバッファリングを強制しない
								)
							);

		// エラーは例外処理に
		$this->pdo_obj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// PHP 5.3.6より前のバージョンの PDO MySQL で charset を指定する
		if(version_compare(phpversion(), '5.3.6', '<=')){
			$this->pdo_obj->query('SET NAMES utf8');
		}

		//valuesをSET
		foreach($local_values as $parameter=>$value){
			$this->setValue($parameter, $value);
		}

		return true;
	}


	//********************************************************************
	//  関数名：Disconnect()
	//  概要　：DBを切断する。
	// -------------------------------------------------------------------
	//  戻り値：true.切断成功  false.失敗
	//********************************************************************
	function Disconnect()
	{
LoggerText::pwriteMessage("sql", "Disonnect DB");

		// 切断
		$this->pdo_obj = null;
	}


	//********************************************************************
	//  関数名：beginTransaction()
	//  概要　：Transactionを開始する。
	// -------------------------------------------------------------------
	//  戻り値：true.切断成功  false.失敗
	//********************************************************************
	function beginTransaction()
	{
LoggerText::pwriteMessage("sql", "Begin Transaction");

		//MyISAMの時はトランザクション処理できないのでコメントアウトしておきます

		$this->pdo_obj->beginTransaction();
	}


	//********************************************************************
	//  関数名：commit()
	//  概要　：Commitする。
	// -------------------------------------------------------------------
	//  戻り値：true.切断成功  false.失敗
	//********************************************************************
	function commit()
	{
LoggerText::pwriteMessage("sql", "commit");

		//MyISAMの時はトランザクション処理できないのでコメントアウトしておきます

		$this->pdo_obj->commit();
	}


	//********************************************************************
	//  関数名：rollback()
	//  概要　：Rollbackする。
	// -------------------------------------------------------------------
	//  戻り値：true.切断成功  false.失敗
	//********************************************************************
	function rollback()
	{
LoggerText::pwriteMessage("sql", "rollback");

		//MyISAMの時はトランザクション処理できないのでコメントアウトしておきます

		$this->pdo_obj->rollback();
	}


	//********************************************************************
	//  関数名：setValue()
	//  概要　：パラメータを設定する。
	//
	//     パラメータ    R/W            内容
	// -----------------+---+---------------------------------------------
	// -------------------------------------------------------------------
	//  戻り値：!false.接続ID  false.失敗
	//********************************************************************
	function setValue($parameter, $value)
	{
LoggerText::pwriteMessage("sql", "SET {$parameter}=\"{$value}\"");

		$sql = <<< SQL_END
SET {$parameter} = :value
;
SQL_END;

//		$stmt = $this->pdo_obj->prepare("/* " . __FUNCTION__ . " @ [" . $_SERVER['PHP_SELF'] . "] */ " . $sql);
		$stmt = $this->pdo_obj->prepare($sql);

		$stmt->bindValue(":value", $value);
		$result = $stmt->execute();

		return true;
	}


	//********************************************************************
	//  関数名：execute()
	//  概要　：いちおう用意しておきます
	//
	//     パラメータ    R/W            内容
	// -----------------+---+---------------------------------------------
	// -------------------------------------------------------------------
	//  戻り値：!false.接続ID  false.失敗
	//********************************************************************
	function execute($sql)
	{
LoggerText::pwriteMessage("sql", "{$sql}");

//		$stmt = $this->pdo_obj->prepare("/* " . __FUNCTION__ . " @ [" . $_SERVER['PHP_SELF'] . "] */ " . $sql);
		$stmt = $this->pdo_obj->prepare($sql);

		$result = $stmt->execute();

		return true;
	}
}

?>
