<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
// SQL実行系
//	execSQL( $sql, $params=array(), $log = true )								SQLを実行します
//	fetch( $mode=PDO::FETCH_ASSOC )												直前にexecした結果からfetchします
//	fetchAll( $mode=PDO::FETCH_ASSOC )											直前にexecした結果からすべてfetchします
//	closeCursor()																cursorをcloseします
//	selectAll( $sql, $params, $column_name, $key_column_list, $max_raw )		SQLのSELECT結果を２次元配列で返します
//	selectAllPrimaryKey( $sql, $params=array(), $column_name=null )				SQLのSELECT結果をPrimaryKeyの多次元配列で返します
//	selectOne( $sql, $params=array(), $column_name=null )						SQLのSELECT結果一件を返します
//
// SQL不要系
//	getAllList($column_name=null, $key_column_list=array() )					全データを配列で返します
//	getAllListPrimaryKey($column_name=null )									全データをPrimaryKeyの多次元配列で返します
//	getListByValues($values, $column_name=null, $key_column_list=array() )		引数配列に一致するものを複数探します
//	getListPrimaryKeyByValues($values, $column_name=null )						引数配列に一致するものを複数探します
//	getOneByValues($values, $column_name=null )									引数配列に一致するものを一件探します
//	getRowCount()																データ数いただき
//	getLastInsertId( $column = null )											AutoIncrement値いただき
//	insertOne( $value_list=array(), $ignore_flag=false )						１レコード挿入
//	upsertOne( $value_list )													レコード挿入/更新
//	insertRecord( $data_list, $ignore_flag=false )								複数レコード挿入
//	upsertRecord( $data_list )													複数レコード挿入/更新
//
// 補助系
//	makeInsertSqlParts( $value_list, $ignore_list=array())						INSERT文を書くためのSQLの部品を作ります
//	makeUpdateSqlParts($value_list, $ignore_list=array())						UPDATE文を書くためのSQLの部品を作ります
//	sanitizeWildCard($value)													ワイルドカードを使った検索時、検索文字列にワールドカード("%","_")が含まれているとマズいのでサニタイズするための関数です
//	getTableColumnList($table_name)												テーブル内のカラムのリストを頂きまゆゆ
//	existsTableColumn($table_name, $column_name)								テーブル内に指定された名前のカラムが存在するかちぇっくします
//	getTableStatus($table_name=null)											テーブルのステータスいただきます
//	convertToNgram($subject, $n=NGRAM_SEARCH_WORD_LENGTH, $separator=)			n-gramの文字列に変換する関数です
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

/****************************************************************************************************************/
//SQL実行時に
//
//	$this->stmt = $this->pdo_obj->prepare("/* " . __FUNCTION__ . " @ [" . $_SERVER['PHP_SELF'] . "] */ " . $sql);
//
//としているのは、ログからSLOW QUERYを解析する際にどこで呼ばれたものかを探しやすくするためです。
//****************************************************************************************************************/

require_once("config.php");

require_once( DIR_LIB_COMMON.'Logger/Text.php');			// Log系class

define("NGRAM_SEARCH_WORD_LENGTH", 2);


class DBCommon
{
	protected $pdo_obj;			// PDO インスタンス
	protected $stmt;			// PDOStatement オブジェクト
	protected $table_name;		// テーブル名
	protected $column_list;		// カラム一覧とっておきます
	protected $primary_key_list;			// PKカラム一覧とっておきます

	function __construct($pdo_obj, $table_name=null, $create_sql=null) 
	{
		$this->pdo_obj = $pdo_obj;

		//テーブルがないのならつくればいいじゃない
		if($create_sql != null){
			$this->execSQL($create_sql);
		}

		$this->table_name = $table_name;
		$this->column_list = array();
	}

	function temporary_construct($pdo_obj) 
	{
		$this->pdo_obj = $pdo_obj;
	}


	//********************************************************************
	//  関数名：execSQL()
	//  概要　：SQLを実行します
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function execSQL( $sql, $params=array(), $log = true )
	{
		if($log){
			LoggerText::pwriteMessage("sql", "execSQL SQL", $sql);
			if(count($params) > 0){
				LoggerText::pwriteMessage("sql", "execSQL parameter", $params);
			}
		}

//		$this->stmt = $this->pdo_obj->prepare("/* " . __FUNCTION__ . " @ [" . $_SERVER['PHP_SELF'] . "] */ " . $sql);
		$this->stmt = $this->pdo_obj->prepare($sql);

		foreach($params as $key=>$value){
			$type = PDO::PARAM_STR;
			switch(true){
				case is_bool($value) :
					$type = PDO::PARAM_BOOL;
					break;
				case is_null($value) :
					$type = PDO::PARAM_NULL;
					break;
				case is_int($value) :
					$type = PDO::PARAM_INT;
					break;
				case is_float($value) :
				case is_numeric($value) :
				case is_string($value) :
				default:
					$type = PDO::PARAM_STR;
					break;
			}
			$this->stmt->bindValue($key, $value, $type);
		}

		$this->stmt->execute();
		$row_cnt = $this->stmt->rowCount();

		//UPDATE/DELETEの場合は、作用した行数を返します
		return (is_numeric($row_cnt)?$row_cnt:true);
	}


	//********************************************************************
	//  関数名：fetch()
	//  概要　：直前にexecした結果からfetchします
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function fetch( $mode=PDO::FETCH_ASSOC )
	{
		return $this->stmt->fetch($mode);
	}


	//********************************************************************
	//  関数名：fetchAll()
	//  概要　：直前にexecした結果をすべてfetchします
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function fetchAll( $mode=PDO::FETCH_ASSOC )
	{
		return $this->stmt->fetchAll($mode);
	}


	//********************************************************************
	//  関数名：closeCursor()
	//  概要　：cursorをcloseします
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function closeCursor()
	{
		$this->stmt->closeCursor();
	}


	//********************************************************************
	//  関数名：selectAll()
	//  概要　：SQLのSELECT結果を配列で返します
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function selectAll( $sql, $params=array(), $column_name=null, $key_column_list=array(), $max_raw=null )
	{
		LoggerText::pwriteMessage("sql", "selectAll SQL", $sql);
		if(count($params) > 0){
			LoggerText::pwriteMessage("sql", "selectAll parameter", $params);
		}
//		$this->stmt = $this->pdo_obj->prepare("/* " . __FUNCTION__ . " @ [" . $_SERVER['PHP_SELF'] . "] */ " . $sql);
		$this->stmt = $this->pdo_obj->prepare($sql);

		// 複合キーとかに対応するために複数のリストを許可しています
		// keyとするカラムは配列にしましょう
		if(!is_array($key_column_list)){
			$key_column_list = array($key_column_list);
		}

		foreach($params as $key=>$value){
			$type = PDO::PARAM_STR;
			switch(true){
				case is_bool($value) :
					$type = PDO::PARAM_BOOL;
					break;
				case is_null($value) :
					$type = PDO::PARAM_NULL;
					break;
				case is_int($value) :
					$type = PDO::PARAM_INT;
					break;
				case is_float($value) :
				case is_numeric($value) :
				case is_string($value) :
				default:
					$type = PDO::PARAM_STR;
					break;
			}
			$this->stmt->bindValue($key, $value, $type);
		}

		$this->stmt->execute();

		$raw_no = 0;
		$result = array();
		//データサイズによってはこの辺ちょっと負荷が高くなります
		while( ($data = $this->stmt->fetch(PDO::FETCH_ASSOC)) ){
			// 単純な配列
			if(empty($key_column_list)){
				if($column_name == null){
					$result[] = $data;
				}elseif(isset($data[$column_name])){
					$result[] = $data[$column_name];
				}
			}
			// 指定カラムをキーにした配列
			else{
				$arr = &$result;
				foreach($key_column_list as $key_column){
					if(!isset($arr[$data[$key_column]])){
						$arr[$data[$key_column]] = array();
					}
					$arr = &$arr[$data[$key_column]];
				}

				if($column_name == null){
					$arr = $data;
				}elseif(isset($data[$column_name])){
					$arr = $data[$column_name];
				}
			}

			//最大行確認
			$raw_no++;
			if(!empty($max_raw) && $raw_no>=$max_raw){
				break;
			}
		}

		$this->stmt->closeCursor();

		return $result;
	}



	//********************************************************************
	//  関数名：selectAllPrimaryKey()
	//  概要　：SQLのSELECT結果を配列で返します
	//			その際、配列のkeyにはPKを使用します
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function selectAllPrimaryKey( $sql, $params=array(), $column_name=null )
	{
		if(empty($this->table_name)){
			return array();
		}

		$result = $this->selectAll( $sql, $params, $column_name, $this->getPrimaryKeyList() );

		return $result;
	}


	//********************************************************************
	//  関数名：selectOne()
	//  概要　：SQLのSELECT結果一件を返します
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function selectOne( $sql, $params=array(), $column_name=null )
	{
		LoggerText::pwriteMessage("sql", "selectOne SQL", $sql);
		if(count($params) > 0){
			LoggerText::pwriteMessage("sql", "selectOne parameter", $params);
		}

//		$this->stmt = $this->pdo_obj->prepare("/* " . __FUNCTION__ . " @ [" . $_SERVER['PHP_SELF'] . "] */ " . $sql);
		$this->stmt = $this->pdo_obj->prepare($sql);

		foreach($params as $key=>$value){
			$type = PDO::PARAM_STR;
			switch(true){
				case is_bool($value) :
					$type = PDO::PARAM_BOOL;
					break;
				case is_null($value) :
					$type = PDO::PARAM_NULL;
					break;
				case is_int($value) :
					$type = PDO::PARAM_INT;
					break;
				case is_float($value) :
				case is_numeric($value) :
				case is_string($value) :
				default:
					$type = PDO::PARAM_STR;
					break;
			}
			$this->stmt->bindValue($key, $value, $type);
		}

		$this->stmt->execute();

		if($result = $this->stmt->fetch(PDO::FETCH_ASSOC)){
			$this->stmt->closeCursor();

			// カラム名指定あり
			if($column_name != null){
				return (isset($result[$column_name])?$result[$column_name]:null);
			}
			else{
				return $result;
			}
		}
		$this->stmt->closeCursor();

		return ($column_name!=null?null:false);
	}


	//********************************************************************
	//  関数名：getAllList()
	//  概要　：全データをPKの配列で返します
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function getAllList($column_name=null, $key_column_list=array() )
	{
		if(empty($this->table_name)){
			return array();
		}

		// 複合キーとかに対応するために複数のリストを許可しています
		// keyとするカラムは配列にしましょう
		if(!is_array($key_column_list)){
			$key_column_list = array($key_column_list);
		}

		$table_name = $this->table_name;
		$columns = implode(",", $this->getColumnList());

		if(!empty($key_column_list)){
			$orders = implode(",", $key_column_list);
		}
		// キーを指定されなかったら、PKの順
		else{
			$orders = implode(",", $this->getPrimaryKeyList());
		}

		$sql = <<< SQL_END
SELECT
	{$columns}
FROM
	{$table_name}
ORDER BY
	{$orders}
;
SQL_END;

		return $this->selectAll( $sql, array(), $column_name, $key_column_list );
	}



	//********************************************************************
	//  関数名：getAllListPrimaryKey()
	//  概要　：全データをPKの配列で返します
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function getAllListPrimaryKey($column_name=null )
	{
		return $this->getAllList( $column_name, $this->getPrimaryKeyList() );

	}



	//********************************************************************
	//  関数名：getListByValues()
	//  概要　：引数配列に一致するものを複数探します
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function getListByValues($values, $column_name=null, $key_column_list=array() )
	{
		if(empty($this->table_name)){
			return array();
		}

		// 複合キーとかに対応するために複数のリストを許可しています
		// keyとするカラムは配列にしましょう
		if(!is_array($key_column_list)){
			$key_column_list = array($key_column_list);
		}

		$table_name = $this->table_name;
		$column_list = $this->getColumnList();
		$columns = implode(",", $column_list);

		$params = array();
		$where_list = array();
		$wheres = "";
		$orders = "";
		foreach($values as $name=>$value){
			if(in_array($name, $column_list)){
				$params[sprintf(":%s", $name)] = $value;
				$where_list[] = sprintf(" %s = :%s ", $name, $name);
				$order_list[] = $name;
			}
		}

		if(!empty($where_list)){
			$wheres = " WHERE " . implode(" AND ", $where_list) . " ";
		}

		if(!empty($key_column_list)){
			$orders = implode(",", $key_column_list);
		}
		// キーを指定されなかったら、PKの順
		else{
			$orders = implode(",", $this->getPrimaryKeyList());
		}

		$sql = <<< SQL_END
SELECT
	{$columns}
FROM
	{$table_name}
{$wheres}
ORDER BY
	{$orders}
;
SQL_END;

		return $this->selectAll( $sql, $params, $column_name, $key_column_list );
	}



	//********************************************************************
	//  関数名：getListPrimaryKeyByValues()
	//  概要　：引数配列に一致するものを複数探します
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function getListPrimaryKeyByValues($values, $column_name=null )
	{
		return $this->getListByValues( $values, $column_name, $this->getPrimaryKeyList() );
	}


	//********************************************************************
	//  関数名：getOneByValues()
	//  概要　：引数配列に一致するものを一件探します
	//
	//     パラメータ    R/W            内容
	//		$sql
	//		$params
	// -----------------+---+---------------------------------------------
	//  戻り値： true/false
	//********************************************************************
	function getOneByValues($values, $column_name=null )
	{
		if(empty($this->table_name)){
			return false;
		}


		$table_name = $this->table_name;
		$column_list = $this->getColumnList();
		$columns = implode(",", $column_list);

		$params = array();
		$where_list = array();
		$wheres = "";
		foreach($values as $name=>$value){
			if(in_array($name, $column_list)){
				$params[sprintf(":%s", $name)] = $value;
				$where_list[] = sprintf(" %s = :%s ", $name, $name);
			}
		}

		if(!empty($where_list)){
			$wheres = " WHERE " . implode(" AND ", $where_list) . " ";
		}

		$sql = <<< SQL_END
SELECT
	{$columns}
FROM
	{$table_name}
{$wheres}
;
SQL_END;

		return $this->selectOne( $sql, $params, $column_name );

	}


	//********************************************************************
	//  関数名：makeInsertSqlParts()
	//  概要　：INSERT文を書くためのSQLの部品を作ります
	//
	//     パラメータ    R/W            内容
	//		$value_list		key   ： INSERTしたいパラメータのカラム名
	//						value ： INSERTしたいパラメータの値
	//		$ignore_list	value ： INSERTしたくないパラメータの値
	//						で構成された配列
	// -----------------+---+---------------------------------------------
	//  戻り値： 
	//********************************************************************
	function makeInsertSqlParts( $value_list, $ignore_list=array())
	{
		// まずは設定するカラムを探して、組み立てます
		$columns_list = array();
		$values_list = array();
		$params = array();
		foreach($value_list as $name=>$value){
			//無視リストにあるものは無視
			if(in_array($name, $ignore_list)){
				continue;
			}
			//引数でもらった配列のkeyがカラム名と一致していたらINSERTするよ
			if(in_array($name, $this->getColumnList())){
				$columns_list[] = " {$name} ";
				$values_list[] = " :{$name} ";
				$params[":{$name}"] = $value;
			}
		}

		$columns = implode(",", $columns_list);
		$values = implode(",", $values_list);

		return array($columns, $values, $params);
	}


	//********************************************************************
	//  関数名：makeUpdateSqlParts()
	//  概要　：UPDATE文を書くためのSQLの部品を作ります
	//
	//     パラメータ    R/W            内容
	//		$value_list		key   ： INSERTしたいパラメータのカラム名
	//						value ： INSERTしたいパラメータの値
	//						で構成された配列
	// -----------------+---+---------------------------------------------
	//  戻り値： 
	//********************************************************************
	function makeUpdateSqlParts($value_list, $ignore_list=array())
	{
		// まずは設定するカラムを探して、組み立てます
		$set_list = array();
		$params = array();
		foreach($value_list as $name=>$value){
			if(in_array($name, $ignore_list)){
				continue;
			}
			if(in_array($name, $this->getColumnList())){
				$set_list[] = " $name=:{$name} ";
				$params[":{$name}"] = $value;
			}
		}

		$set_sql = implode(",", $set_list);

		return array($set_sql, $params);
	}



	//********************************************************************
	//  関数名：insertOne()
	//  概要　：１レコード挿入
	//
	//     パラメータ    R/W            内容
	//		$value_list		key   ： INSERTしたいパラメータのカラム名
	// -----------------+---+---------------------------------------------
	//  戻り値： 
	//********************************************************************
	function insertOne( $value_list=array(), $ignore_flag=false )
	{
		if(empty($this->table_name)){
			return false;
		}

		$ignore = "";

		if($ignore_flag){
			$ignore = " IGNORE ";
		}

		$table_name = $this->table_name;

		list($columns, $values, $params) = $this->makeInsertSqlParts( $value_list );

		$sql = <<< SQL_END
INSERT {$ignore} INTO {$table_name} (
{$columns}
	)
VALUES(
{$values}
	)
;
SQL_END;

		$this->execSQL( $sql, $params );

		return $this->pdo_obj->lastInsertId();

	}



	//********************************************************************
	//  関数名：upsertOne()
	//  概要　：１レコード挿入/更新
	//
	//     パラメータ    R/W            内容
	//		$data_list		1レコードの配列 or データ数×カラム数の2次元配列
	//						key   ： UPSERTしたいパラメータのカラム名
	//
	//		エラーチェックとかあんまりやってないです
	// -----------------+---+---------------------------------------------
	//  戻り値： 成功:true
	//********************************************************************
	function upsertOne( $value_list )
	{
		if(empty($this->table_name)){
			return false;
		}

		$table_name = $this->table_name;

		list($columns, $values, $params) = $this->makeInsertSqlParts( $value_list );
		list($updates, $upparams) = $this->makeUpdateSqlParts( $value_list );

		$sql = <<< SQL_END
INSERT INTO {$table_name} (
{$columns}
	)
VALUES(
{$values}
)
ON DUPLICATE KEY UPDATE
{$updates}
;
SQL_END;

		$this->execSQL( $sql, $params );

//		return true;
		return $this->pdo_obj->lastInsertId();
	}



	//********************************************************************
	//  関数名：insertRecord()
	//  概要　：複数レコード挿入
	//
	//     パラメータ    R/W            内容
	//		$data_list		1レコードの配列 or データ数×カラム数の2次元配列
	//						key   ： INSERTしたいパラメータのカラム名
	//
	//		エラーチェックとかあんまりやってないです
	// -----------------+---+---------------------------------------------
	//  戻り値： 成功:true
	//********************************************************************
	function insertRecord( $data_list, $ignore_flag=false )
	{
		if(empty($this->table_name)){
			return false;
		}

		if(count($data_list) == 0){
			return true;
		}

		//データをちょっと調べさせてもらいます
		list($data_list, $columns_list) = $this->analyzeInsertData($data_list);

		//もろもろの準備が整ったようなので、SQLを組み立てていきましょうか
		// テーブル名
		$table_name = $this->table_name;
		// IGNORE
		if($ignore_flag){
			$ignore = " IGNORE ";
		}else{
			$ignore = "";
		}
		// カラムリスト生成
		$columns = implode(", ", $columns_list);

		//データ数分のVALUEリストと,bindパラメータを作ります
		$value = array();
		$bind_value = array();
		$cnt = 0;
		foreach($data_list as $data){
			// 1レコードのVALUE
			$value[$cnt] = "( :" . implode("{$cnt}, :", $columns_list) . "{$cnt} )";

			// 1レコードのbindパラメータ
			foreach($columns_list as $column){
				$bind_value[":{$column}{$cnt}"] = $data[$column];
			}

			$cnt++;
		}

		$values = implode(",\n", $value);

		$sql = <<< SQL_END
INSERT {$ignore} INTO {$table_name} (
{$columns}
	)
VALUES
{$values}
;
SQL_END;

//print "<pre>";var_dump($sql, $bind_value);print "</pre>";

		return $this->execSQL( $sql, $bind_value );

	}


	//********************************************************************
	//  関数名：upsertRecord()
	//  概要　：複数レコード挿入/更新
	//
	//     パラメータ    R/W            内容
	//		$data_list		1レコードの配列 or データ数×カラム数の2次元配列
	//						key   ： UPSERTしたいパラメータのカラム名
	//
	//		エラーチェックとかあんまりやってないです
	// -----------------+---+---------------------------------------------
	//  戻り値： 成功:true
	//********************************************************************
	function upsertRecord( $data_list )
	{
		if(empty($this->table_name)){
			return false;
		}

		if(count($data_list) == 0){
			return true;
		}

		//データをちょっと調べさせてもらいます
		list($data_list, $columns_list) = $this->analyzeInsertData($data_list);

		//もろもろの準備が整ったようなので、SQLを組み立てていきましょうか
		// テーブル名
		$table_name = $this->table_name;
		// カラムリスト生成
		$columns = implode(", ", $columns_list);

		//データ数分のVALUEリストと,bindパラメータを作ります
		$value = array();
		$bind_value = array();
		$cnt = 0;
		foreach($data_list as $data){
			// 1レコードのVALUE
			$value[$cnt] = "( :" . implode("{$cnt}, :", $columns_list) . "{$cnt} )";

			// 1レコードのbindパラメータ
			foreach($columns_list as $column){
				$bind_value[":{$column}{$cnt}"] = $data[$column];
			}

			$cnt++;
		}

		$values = implode(",\n", $value);

		$update = array();
		foreach($columns_list as $column){
			$update[] = " {$column}=VALUES({$column}) ";
		}

		$updates = implode(", ", $update);

		$sql = <<< SQL_END
INSERT INTO {$table_name} (
{$columns}
	)
VALUES
{$values}
ON DUPLICATE KEY UPDATE
{$updates}
;
SQL_END;

//print "<pre>";var_dump($sql, $bind_value);print "</pre>";

		return $this->execSQL( $sql, $bind_value );
	}



	//********************************************************************
	//  関数名：getLastInsertId()
	//  概要　：
	//
	//     パラメータ    R/W            内容
	// -----------------+---+---------------------------------------------
	//  戻り値： 
	//********************************************************************
	function getLastInsertId( $column = null )
	{
		if($column != null){
			return $this->pdo_obj->lastInsertId($column);
		}

		return $this->pdo_obj->lastInsertId();
	}



	//********************************************************************
	//  関数名：getRowCount()
	//  概要　：
	//
	//     パラメータ    R/W            内容
	// -----------------+---+---------------------------------------------
	//  戻り値： 
	//********************************************************************
	function getRowCount()
	{
		if(empty($this->table_name)){
			return false;
		}

		$table_name = $this->table_name;

		$sql = <<< SQL_END
SELECT COUNT(*) AS cnt FROM {$table_name};
;
SQL_END;

		$result =  $this->selectOne( $sql );

		return $result["cnt"];
	}


	/*********************************************************************
	*   関数名：sanitizeWildCard()
	*   概要　：ワイルドカードを使った検索時、検索文字列にワールドカード("%","_")が含まれているとマズいのでサニタイズするための関数です
	*
	*   パラメータ   データ配列
	*			$value	対象文字列
	*
	*  -----------------+---+---------------------------------------------
	*   戻り値： リストの配列
	*
	*           PDOExceptionをthrowします
	*********************************************************************/
	function sanitizeWildCard($value)
	{
		// 円マーク(バックスラッシュ)をLIKE用に2重化
		$value = mb_ereg_replace('\\\\','\\\\',$value);

		// LIKEで使われるワイルドカード(%)をエスケープ処理
		$value = mb_ereg_replace('%','\%',$value);

		// LIKEで使われるワイルドカード(_)をエスケープ処理
		$value = mb_ereg_replace('_','\_',$value);

		return $value;
	}



	/*********************************************************************
	*   関数名：getTableColumnList()
	*   概要　：テーブル内のカラムのリストを頂きまゆゆ
	*
	*   パラメータ   データ配列
	*			$table_name
	*
	*  -----------------+---+---------------------------------------------
	*   戻り値： リストの配列
	*
	*           PDOExceptionをthrowします
	*********************************************************************/
	function getTableColumnList($table_name=null)
	{
		if($table_name == null ){
			if($this->table_name == null){
				return array();
			}
			$table_name = $this->table_name;
		}

		//テーブル名ではプレイスフォルダ使えないので、一応エスケープします
		//と思ったけど、怪しいのは来ないだろうし、上手くいかないのでやめます。


		$sql = <<< SQL_END
DESCRIBE {$table_name}
;
SQL_END;

//		$this->stmt = $this->pdo_obj->prepare("/* " . __FUNCTION__ . " @ [" . $_SERVER['PHP_SELF'] . "] */ " . $sql);
		$this->stmt = $this->pdo_obj->prepare($sql);
		$this->stmt->execute();

		$columns = array();
		//結果取得
		while( ($column = $this->stmt->fetch(PDO::FETCH_ASSOC)) ){
			$columns[] = $column["Field"];
		}
		$this->stmt->closeCursor();

		return $columns;
	}


	/*********************************************************************
	*   関数名：existsTableColumn()
	*   概要　：テーブル内に指定された名前のカラムが存在するかちぇっくします
	*
	*   パラメータ   データ配列
	*			$table_name
	*			$column_name
	*
	*  -----------------+---+---------------------------------------------
	*   戻り値： あるよ : true
	*            ないよ : false
	*
	*           PDOExceptionをthrowします
	*********************************************************************/
	function existsTableColumn($table_name, $column_name)
	{
		$sql = <<< SQL_END
DESCRIBE {$table_name}
;
SQL_END;

//		$this->stmt = $this->pdo_obj->prepare("/* " . __FUNCTION__ . " @ [" . $_SERVER['PHP_SELF'] . "] */ " . $sql);
		$this->stmt = $this->pdo_obj->prepare($sql);
		$this->stmt->execute();

		//結果取得
		while( ($column = $this->stmt->fetch(PDO::FETCH_ASSOC)) ){
			if( $column["Field"] == $column_name || ($table_name . "." . $column["Field"] ) == $column_name ){
				$this->stmt->closeCursor();
				return true;
			}
		}
		$this->stmt->closeCursor();

		return false;
	}


	//********************************************************************
	//  関数名：getTableStatus()
	//  概要　：
	//
	//     パラメータ    R/W            内容
	// -----------------+---+---------------------------------------------
	//  戻り値： 
	//********************************************************************
	function getTableStatus($table_name=null)
	{
		if($table_name==null){
			if(empty($this->table_name)){
				return array();
			}
			$table_name = $this->table_name;
		}

		$sql = <<< SQL_END
SHOW TABLE STATUS LIKE "{$table_name}"
;
SQL_END;

//		$this->stmt = $this->pdo_obj->prepare("/* " . __FUNCTION__ . " @ [" . $_SERVER['PHP_SELF'] . "] */ " . $sql);
		$this->stmt = $this->pdo_obj->prepare($sql);

		return $this->stmt->selectAll( $sql );
	}



	/*********************************************************************
	*   関数名：convertToNgram()
	*   概要　：n-gramの文字列に変換する関数です
	*
	*   パラメータ   データ配列
	*			$subject		対象文字列
	*			$n				単語長(ngramのn)
	*			$delimiters		半角スペースの他に文節とする文字
	*
	*  -----------------+---+---------------------------------------------
	*   戻り値： リストの配列
	*
	*           PDOExceptionをthrowします
	*********************************************************************/
	function convertToNgram($subject, $n=NGRAM_SEARCH_WORD_LENGTH, $separator=array("　", "\t", "\n", "\r", "。", "、", "?", "？"))
	{
		// 半角カナ->全角カナ
		// 全角英数->半角英数
		$subject = mb_convert_kana($subject, "KVa");

		// 半角英数の文字列は分割したくないので先に取り出しておきます
		$placeholder_reg = '/([\.0-9A-Za-z_-]+)/sm';
		preg_match_all($placeholder_reg, $subject, $alpha_numeric_list, PREG_PATTERN_ORDER);

		// 半角英数の文字列は取り出したので、削除しておきます
		$subject = preg_replace($placeholder_reg, " ", $subject);

		$words = $alpha_numeric_list[1];

		$subject = str_replace($separator, " ", $subject);			// 文節文字を半角スペースに統一
		$subject = preg_replace("/[\s]+/", " ", trim($subject));	// trimして、連続スペースをひとつに
		$sentences = explode(' ', $subject);						// 文章ごとに分けて配列に入れます

		// 文章ごとにセットするよ
		foreach($sentences as $sentence){
			$sentence_len = mb_strlen($sentence);

			for($pos=0; $pos+$n<=$sentence_len; $pos++){
				$words[] = mb_substr($sentence, $pos, $n);
			}
		}

		return array_unique($words);			// 一意にしておく
	}


	//********************************************************************
	//  関数名：analyzeInsertData()
	//  概要　：レコード更新用データを調べます
	//
	//     パラメータ    R/W            内容
	//		$data_list		1レコードの配列 or データ数×カラム数の2次元配列
	//						key   ： INSERTしたいパラメータのカラム名
	//
	//		エラーチェックとかあんまりやってないです
	// -----------------+---+---------------------------------------------
	//  戻り値： 成功:true
	//********************************************************************
	private function analyzeInsertData( $data_list )
	{
		//データが単数か複数か判定します

		//1次元(単一レコード)か2次元(複数レコード)かしりたーーい
		// 最初の１レコードで判定しているのでforeachで回さなくてもいいんですが、
		// array_pop()とか使うとデータ削れちゃうので、いったんこれで
		foreach($data_list as $tmp_data){
			$sample_data = $tmp_data;
			break;
		}

		//1次元(単一レコード)なので２次元にします
		if(!is_array($tmp_data)){
			$sample_data = $data_list;
			$data_list = array($data_list);
		}

		// すでに$sample_dataには１レコード分のデータが入っています

		//データのキーのリストを頂戴します(レコードのカラム名候補)
		$data_keys = array_keys($sample_data);

		$columns_list = array();
		foreach($data_keys as $data_key){
			//カラム名と一致していたら有効
			if(in_array($data_key, $this->getColumnList())){
				$columns_list[] = $data_key;
			}
		}

		return array($data_list, $columns_list);
	}



	protected function setPrimaryKeyList()
	{
		if($this->table_name != null && empty($this->primary_key_list)){
			//まずはPKの情報をください
			$pk_sql = <<< SQL_END
SHOW INDEX 
FROM {$this->table_name}
SQL_END;

			$indexes = $this->selectAll($pk_sql);

			$pk = array();
			foreach($indexes as $column){
				if($column["Key_name"] == "PRIMARY"){
					$pk[$column["Seq_in_index"]] = $column["Column_name"];
				}
			}

			ksort($pk);

			$this->primary_key_list = $pk;
		}
	}


	protected function getPrimaryKeyList()
	{
		$this->setPrimaryKeyList();

		return	$this->primary_key_list;
	}


	protected function setColumnList()
	{
		if($this->table_name != null && empty($this->column_list)){
			$this->column_list = $this->getTableColumnList();		// カラム一覧とっておきます
		}
	}


	protected function getColumnList()
	{
		$this->setColumnList();

		return	$this->column_list;
	}

}
?>
