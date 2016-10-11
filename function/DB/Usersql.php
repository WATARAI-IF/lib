<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// DB関係のclass
//	必要に応じて追加して行きましょう
//
//--------------------------------------------------------------------
// @filename	dbmysql.php
// @create		2011-12-01
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
//class DB_Usersql
//
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

require_once("config.php");
require_once( DIR_LIB_FUNCTION.'DB/Connect.php');
require_once( DIR_LIB_FUNCTION.'DB/Common.php');


class DBUsersql extends DBConnect
{
	public $common;

	function __construct() 
	{
		parent::__construct();

	}
}