<?php
require_once("config.php");

require_once(DIR_LIB_FUNCTION."myForm/formController.php");
require_once(DIR_LIB_FUNCTION."myForm/sampleValidate.php");
require_once(DIR_LIB_FUNCTION."myForm/changeValue.php");

require_once( DIR_LIB_COMMON . 'const_array.php' );					// 定数系

class sampleForm extends formController
{
	protected function setPageLists()
	{
		//ページリスト(遷移順)
		$this->page_list = array(
			"agree.php"			=> FORM_INPUT_PAGE,
			"info.php"			=> FORM_INPUT_PAGE,
			"info_man.php"		=> FORM_INPUT_PAGE,
			"name.php"			=> FORM_INPUT_PAGE,
			"password.php"		=> FORM_INPUT_PAGE,
			"confirm.php"		=> FORM_CONFIRM_PAGE,
			"complete.php"		=> FORM_COMPLETE_PAGE,
		);

		//formのページをスキップする規則
		// methodはこのファイルの下の方にでも作っておいてください
		$this->page_skip_rules = array(
			"info_man.php"					=> array(
													array("method" => "isEqual",		"answer" => "gender_id",		"arg" => array(2),		"bool" => true),					// 女性ならスキップ
//													array("method" => "isEqual",		"answer" => "gender_id",		"arg" => array(1),						),					// 女性ならスキップ
			),
		);
	}



	protected function setObjects()
	{
		// バリデーション用オブジェクト設定
		$this->validate_object = new sampleValidate($this->db);

		// パラメータ自動変換用オブジェクト設定
		$this->change_value_object = new changeValue();
	}



	protected function setVaridations()
	{
		//Validationルール
		$this->validations		= array(
			"agree.php"	=> array(
				"email_error"	=> array(
						array(	"message" => "「メールアドレス」を入力してください。",				"method" => NOT_NULL_CHECK_METHOD,		"answer" => "email",																	),
						array(	"message" => "「メールアドレス」が正しくありません。",				"method" => "isMailAddress",			"answer" => "email",																	),
						array(	"message" => "確認用メールアドレスと一致しません。",				"method" => "isEqual",					"answer" => array("email", "email_confirm"),												 ),
				),
				"agree_error"		=> array(
						array(	"message" => "利用規約に同意してください",							"method" => NOT_NULL_CHECK_METHOD,		"answer" => "agree",					),
				),
			),

			"name.php"	=> array(
				"name_error"		=> array(
						array(	"message" => "「姓」を入力してください。",							"method" => NOT_NULL_CHECK_METHOD,		"answer" => "last_name",																	),
						array(	"message" => "「名」を入力してください。",							"method" => NOT_NULL_CHECK_METHOD,		"answer" => "first_name",																	),
						array(	"message" => "「姓」は８文字までです。",							"method" => "lengthBetween",			"answer" => "last_name",			"arg" => array(1, 8),								),
						array(	"message" => "「名」は８文字までです。",							"method" => "lengthBetween",			"answer" => "first_name",			"arg" => array(1, 8),								),
				),
				"kana_error"		=> array(
						array(	"message" => "「姓(カナ)」を入力してください。",					"method" => NOT_NULL_CHECK_METHOD,		"answer" => "last_kana",										),
						array(	"message" => "「名(カナ)」を入力してください。",					"method" => NOT_NULL_CHECK_METHOD,		"answer" => "first_kana",										),
						array(	"message" => "「姓(カナ)」は８文字までです。",						"method" => "lengthBetween",			"answer" => "last_kana",			"arg" => array(1, 8),								),
						array(	"message" => "「名(カナ)」は８文字までです。",						"method" => "lengthBetween",			"answer" => "first_kana",			"arg" => array(1, 8),								),
						array(	"message" => "「姓(カナ)」はカタカナで入力してください。",			"method" => "isKana",					"answer" => "last_kana",											),
						array(	"message" => "「名(カナ)」はカタカナで入力してください。",			"method" => "isKana",					"answer" => "first_kana",											),
				),
			),

			"info.php"	=> array(
				"gender_id_error"	=> array(
						array(	"message" => "「性別」を選択してください。",						"method" => NOT_NULL_CHECK_METHOD,		"answer" => "gender_id",																),
						array(	"message" => "「性別」が正しくありません。",						"method" => "isSelected",				"answer" => "gender_id",						 ),
				),
				"birthday_error"	=> array(
						array(	"skip" => true,														"method" => "isEqual",					"answer" => "gender_id",			"arg" => array(1),		),
						array(	"message" => "「年」を選択してください",							"method" => NOT_NULL_CHECK_METHOD,		"answer" => "birthday_year",																),
						array(	"message" => "「月」を選択してください",							"method" => NOT_NULL_CHECK_METHOD,		"answer" => "birthday_month",																),
						array(	"message" => "「日」を選択してください",							"method" => NOT_NULL_CHECK_METHOD,		"answer" => "birthday_day",																),
						array(	"message" => "「年」を選択してください。",							"method" => "isSelected",				"answer" => "birthday_year",						 ),
						array(	"message" => "「月」を選択してください。",							"method" => "isSelected",				"answer" => "birthday_month",						 ),
						array(	"message" => "「日」を選択してください。",							"method" => "isSelected",				"answer" => "birthday_day",							 ),
						array(	"message" => "無効な日付です",										"method" => "isDate",					"answer" => array("birthday_year", "birthday_month", "birthday_day")		),
				),
			),

			"info_man.php"	=> array(
				"birthday_error"	=> array(
						array(	"message" => "「年」を選択してください",							"method" => NOT_NULL_CHECK_METHOD,		"answer" => "birthday_year",																),
						array(	"message" => "「月」を選択してください",							"method" => NOT_NULL_CHECK_METHOD,		"answer" => "birthday_month",																),
						array(	"message" => "「日」を選択してください",							"method" => NOT_NULL_CHECK_METHOD,		"answer" => "birthday_day",																),
						array(	"message" => "「年」を選択してください。",							"method" => "isSelected",				"answer" => "birthday_year",						 ),
						array(	"message" => "「月」を選択してください。",							"method" => "isSelected",				"answer" => "birthday_month",						 ),
						array(	"message" => "「日」を選択してください。",							"method" => "isSelected",				"answer" => "birthday_day",							 ),
						array(	"message" => "無効な日付です",										"method" => "isDate",					"answer" => array("birthday_year", "birthday_month", "birthday_day")		),
				),
				"team_error"		=> array(
						array(	"message" => "一つ以上選択してください",							"method" => NOT_NULL_CHECK_METHOD,		"answer" => "team",					),
				),
			),

			"password.php"	=> array(
				"password_error"	=> array(
						array(	"message" => "「パスワード」を入力してください。",					"method" => NOT_NULL_CHECK_METHOD,		"answer" => "password",																	),
						array(	"message" => "「パスワード」は半角英数で設定してください。",		"method" => "isAlphanumeric",			"answer" => "password",												),
						array(	"message" => "「パスワード」は８～１６文字で設定してください。",	"method" => "lengthBetween",			"answer" => "password",		"arg" => array(8, 16),															),
						array(	"message" => "確認用パスワードと一致しません。",					"method" => "isEqual",					"answer" => array("password", "password_confirm"),																					 ),
				),
			),
		);
	}



	protected function setFields($script_name)
	{
		$const_array	= new ConstArray();

		switch($script_name){
			case "agree.php":
				$this->fields	= array(		// フォームの項目リスト
					"email"					=> array(	"input_type"	=> FORM_INPUT_TYPE_OTHER,			"change_method"	=> "multibyte2Ascii",					"default"		=> "@i-studio.co.jp",			),
					"email_confirm"			=> array(	"input_type"	=> FORM_INPUT_TYPE_OTHER,		),
					"agree"					=> array(	"input_type"	=> FORM_INPUT_TYPE_CHECK_ONE,		"option_list"	=> array( 1=> "同意する"),				"set_form_item" => false,	),			// 入力画面に内容を引き継ぐかどうか
				);
				break;

			case "name.php":
				$this->fields	= array(		// フォームの項目リスト
					"last_name"				=> array(	"input_type"	=> FORM_INPUT_TYPE_OTHER,			"change_method"	=>array("ascii2Multibyte", "hankaku2Zenkaku"),						),
					"first_name"			=> array(	"input_type"	=> FORM_INPUT_TYPE_OTHER,			"change_method"	=>array("ascii2Multibyte", "hankaku2Zenkaku"),						),
					"last_kana"				=> array(	"input_type"	=> FORM_INPUT_TYPE_OTHER,			"change_method"	=>array("hankaku2Zenkaku", "hira2kata"),							),
					"first_kana"			=> array(	"input_type"	=> FORM_INPUT_TYPE_OTHER,			"change_method"	=>array("hankaku2Zenkaku", "hira2kata"),							),
				);
				break;

			case "info.php":
				$gender_list = $const_array->getArray("GENDER");
				$gender_en_list = $const_array->getArray("GENDER_EN");
				$birthday_year_list = array_combine(range(2016, 1900), range(2016, 1900));
				$birthday_month_list = array_combine(range(1, 12), range(1, 12));
				$birthday_day_list = array_combine(range(1, 31), range(1, 31));

				$this->fields	= array(		// フォームの項目リスト
					"gender_id"				=> array(	"input_type"	=> FORM_INPUT_TYPE_RADIO,			"option_list"	=> $gender_list,						"sub_option_list"	=> $gender_en_list,		"default"	=> 1	),
					"birthday_year"			=> array(	"input_type"	=> FORM_INPUT_TYPE_SELECT,			"option_list"	=> $birthday_year_list,					"default"		=> date("Y"),							),
					"birthday_month"		=> array(	"input_type"	=> FORM_INPUT_TYPE_SELECT,			"option_list"	=> $birthday_month_list,				"default"		=> date("n"),							),	
					"birthday_day"			=> array(	"input_type"	=> FORM_INPUT_TYPE_SELECT,			"option_list"	=> $birthday_day_list,					"default"		=> date("j"),							),
				);
				break;

			case "info_man.php":
				$birthday_year_list = array_combine(range(2016, 1900), range(2016, 1900));
				$birthday_month_list = array_combine(range(1, 12), range(1, 12));
				$birthday_day_list = array_combine(range(1, 31), range(1, 31));
				$team_list = array(1=>"Swallows", 2=>"読売", 3=>"阪神", 4=>"広島", 5=>"中日", 6=>"横浜");

				$this->fields	= array(		// フォームの項目リスト
					"birthday_year"			=> array(	"input_type"	=> FORM_INPUT_TYPE_SELECT,			"option_list"	=> $birthday_year_list,					"default"		=> date("Y"),							),
					"birthday_month"		=> array(	"input_type"	=> FORM_INPUT_TYPE_SELECT,			"option_list"	=> $birthday_month_list,				"default"		=> date("n"),							),	
					"birthday_day"			=> array(	"input_type"	=> FORM_INPUT_TYPE_SELECT,			"option_list"	=> $birthday_day_list,					"default"		=> date("j"),							),
					"team"					=> array(	"input_type"	=> FORM_INPUT_TYPE_CHECK_LIST,		"option_list"	=> $team_list,							"default"		=> array(1, 6)							),
				);
				break;

			case "password.php":
				$this->fields	= array(		// フォームの項目リスト
					"password"				=> array(	"input_type"	=> FORM_INPUT_TYPE_OTHER,		),
					"password_confirm"		=> array(	"input_type"	=> FORM_INPUT_TYPE_OTHER,			"set_form_item" => false,		),			// 入力画面に内容を引き継ぐかどうか
				);
				break;
		}
	}



}