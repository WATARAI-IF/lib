<?php

require_once('config.php');

require_once(DIR_LIB_FUNCTION.'myForm/formDefine.php');

define("FORM_SESSION_KEY",			"form");			// Form関連のSessionのキー
define("FORM_TOKEN_KEY",			"token");			// 確認用tokenのキー
define("FORM_ANSWER_KEY",			"answer");			// 回答のキー
define("FORM_ERR_MSG_KEY",			"err_msg");			// エラーメッセージのキー

define("BLOCK_LIST_SUFIX",		"_list");
define("ERROR_MESSAGE_SUFIX",	"_message");

class formController
{
	protected	$fields;				// 入力値を定義します
	protected	$validations;			// Validationルールを定義します
	protected	$validate_object;		// Validation のobject

	protected	$default_place_holder;	//テンプレートのプレースホルダー名

	private $answer_list;
	private $err_msg_list;
	private $token;

	private $class_name;		//class名 セッションのキーで使用(一意性を保つため)

	private $first_flag;		//

	function __construct($place_holder=array()){
		$this->class_name = get_class($this);

		$this->default_place_holder = array(
					FORM_INPUT_TYPE_RADIO	=> array(	"value"	=> "value",		"valuename"	=> "valuename",		"checked"	=> "checked",	"checked_word"	=>"checked"	),
					FORM_INPUT_TYPE_SELECT	=> array(	"value"	=> "value",		"valuename"	=> "valuename",		"selected"	=> "selected",	"selected_word"	=>"selected"	),
					FORM_INPUT_TYPE_CHECK	=> array(	"value"	=> "value",		"valuename"	=> "valuename",		"checked"	=> "checked",	"checked_word"	=>"checked"	),
		);

		//お好みのものに置き換えます
		foreach($place_holder as $key=>$value){
			if(isset($this->default_place_holder[$key])){
				$this->default_place_holder[$key] = array_merge($this->default_place_holder[$key], $value);
			}
		}

		$this->first_flag		= false;

		// フォーム用のsessionを初期化
		//まずは全体
		if(!isset($_SESSION[FORM_SESSION_KEY])){
			$_SESSION[FORM_SESSION_KEY] = array();
		}

		if(!isset($_SESSION[FORM_SESSION_KEY][$this->class_name])){
			$_SESSION[FORM_SESSION_KEY][$this->class_name] = array();

			$this->first_flag		= true;		//初めてですね
		}

		//そして回答保存部分
		if(!isset($_SESSION[FORM_SESSION_KEY][$this->class_name][FORM_ANSWER_KEY])){
			$_SESSION[FORM_SESSION_KEY][$this->class_name][FORM_ANSWER_KEY] = array();
		}

		//そして回答保存部分
		if(!isset($_SESSION[FORM_SESSION_KEY][$this->class_name][FORM_ERR_MSG_KEY])){
			$_SESSION[FORM_SESSION_KEY][$this->class_name][FORM_ERR_MSG_KEY] = array();
		}

		//最後にtoken部分
		if(!isset($_SESSION[FORM_SESSION_KEY][$this->class_name][FORM_TOKEN_KEY])){
			$_SESSION[FORM_SESSION_KEY][$this->class_name][FORM_TOKEN_KEY] = null;
		}

		// sessionの内容にアクセスできるように設定
		$this->answer_list = &$_SESSION[FORM_SESSION_KEY][$this->class_name][FORM_ANSWER_KEY];
		$this->err_msg_list = &$_SESSION[FORM_SESSION_KEY][$this->class_name][FORM_ERR_MSG_KEY];
		$this->token = &$_SESSION[FORM_SESSION_KEY][$this->class_name][FORM_TOKEN_KEY];

	}



	// フォームの変数名リスト
	function getFieldNames()
	{
		return array_keys($this->fields);
	}



	// 使い終わったSESSIONはきれいにしましょう
	function clearAll()
	{
		if(isset($_SESSION[FORM_SESSION_KEY][$this->class_name])){
			unset($_SESSION[FORM_SESSION_KEY][$this->class_name]);
		}
	}



	//はじめてきたかどうかの判定
	function isFirst()
	{
		return $this->first_flag;
	}



////////////////////////////////////////////////////////////////////////////////////
////////////////////////	確認用のtokenを制御します		////////////////////////
////////////////////////////////////////////////////////////////////////////////////


	//有効な回答を判定するためにtoken使います
	//そのためのメソッドを作っておきます

	//トークンの生成
	//回答内容で一意になるようにします
	private function generateToken($answer_list=null){
		if(is_null($answer_list)){
			$answer_list = $this->answer_list;
		}

		return md5(serialize($answer_list));
	}

	//トークンをセット
	private function setToken($validate=false){
		$this->token = $this->generateToken();

		//answerの中身が大丈夫か、ちゃんとチェックします
		if($validate){
			$error = $this->validateAnswer();

			//エラーがあるからtokenは発行できん
			if(!empty($error)){
				$this->token = null;
			}
		}
	}

	//トークンの取得
	function getToken(){
		return $this->token;
	}

	//トークンの破棄
	private function clearToken(){
		$this->token = null;
	}

	//トークンの確認
	//POSTとかで受け取ったtokenが正しいのか確認します
	function isValidToken($token)
	{
		// php5.3以前では isset($token[FORM_TOKEN_KEY])の挙動が違うので、is_array()をつけておきます

		//POSTの配列のまま来てもいいように判定
		if(is_array($token) && isset($token[FORM_TOKEN_KEY])){
			$token = $token[FORM_TOKEN_KEY];
		}

		//、token発行されてんのーーー？
		if(is_null($this->token)){
			return false;
		}

		//現在の回答によって生成されるトークンと確認
		if($token != $this->generateToken()){
			return false;
		}

		//バリデーション時に生成されたトークンと確認
		if($token != $this->token){
			return false;
		}

		return true;
	}



////////////////////////////////////////////////////////////////////////////////////
////////////////////////	回答内容を制御するかんじ		////////////////////////
////////////////////////////////////////////////////////////////////////////////////


	//回答の代入
	function setAnswer($parameter_list, $clear=false, $hissu_list=array())
	{
		if($clear){
			$this->answer_list = array();
		}

		$field_list = $this->getFieldNames();

		// checkboxは変数ごと来ないので、必須の場合は引数指定されます
		if(!is_array($hissu_list)){
			$hissu_list = array($hissu_list);
		}

		foreach($hissu_list as $value){
			if( !isset($this->answer_list[$value]) ){
				$this->answer_list[$value] = null;
			}
		}

		//パラメータのうち、それが対象のものだけを取り出し
		foreach($parameter_list as $key => $value){
			if(in_array($key, $field_list)){
				$this->answer_list[$key] = $value;
			}
		}

		//回答が更新されたらトークンは無効です
		$this->clearToken();

		//エラーリストもクリア
		$this->err_msg_list = array();
	}



	//回答の取得
	function getAnswer($field_name=null)
	{
		//項目指定されていないので全部
		if(is_null($field_name)){
			return $this->answer_list;
		}

		//項目指定されている場合
		if(isset($this->answer_list[$field_name])){
			return $this->answer_list[$field_name];
		}

		return null;
	}



	//回答の破棄
	function clearAnswer()
	{
		$this->answer_list = array();

		//エラーリストもクリア
		$this->err_msg_list = array();

		//回答が更新されたらトークンは無効です
		$this->clearToken();
	}



////////////////////////////////////////////////////////////////////////////////////
////////////////////////	FORM のプレースホルダー関係		////////////////////////
////////////////////////////////////////////////////////////////////////////////////


	//テンプレートエンジン用のパラメータリスト生成
	//入力画面用
	function generateFormList($set_answer=true)
	{
		//エラーメッセージ
		$form_list = $this->err_msg_list;

		//一つずつコツコツと作っていきましょう
		foreach($this->fields as $field_name=>$value){
			$form_list = array_merge($form_list, $this->generateFormItem($field_name, $set_answer));
		}

		return $form_list;
	}


	//テンプレートエンジン用のパラメータリスト生成
	//確認画面用
	function generateConfirmList()
	{
		$form_list = array(FORM_TOKEN_KEY => $this->getToken());

		//一つずつコツコツと作っていきましょう
		foreach($this->fields as $field_name=>$value){
			$form_list = array_merge($form_list, $this->generateConfirmItem($field_name));
		}

		return $form_list;
	}



	//入力画面用
	private function generateFormItem($field_name, $set_answer=true)
	{
		$form = array();

		//変数名が正しいのか確認
		if(!isset($this->fields[$field_name])){
			return $form;
		}

		$setting = $this->fields[$field_name];

		//回答をセットする？
		// 指定がない場合はセットします
		if(isset($setting["set_form_item"]) && !$setting["set_form_item"]){
			$set_answer = false;
		}

		//テンプレート表示でBLOCKを使う場合のBLOCK名
		$block_name = $field_name . BLOCK_LIST_SUFIX;

		switch($setting["input_type"])
		{
			case FORM_INPUT_TYPE_OTHER:
				$form[$field_name] = (($set_answer && isset($this->answer_list[$field_name]))?$this->answer_list[$field_name]:null);

				break;

			case FORM_INPUT_TYPE_SELECT:
				$form[$block_name] = array();

				foreach($this->fields[$field_name]["option_list"] as $key=>$value){
					$form[$block_name][] = array(
												$this->default_place_holder[FORM_INPUT_TYPE_SELECT]["value"]		=> $key,
												$this->default_place_holder[FORM_INPUT_TYPE_SELECT]["valuename"]	=> $value,
												$this->default_place_holder[FORM_INPUT_TYPE_SELECT]["selected"]		=> 
														(($set_answer && ((isset($this->answer_list[$field_name]) && $this->answer_list[$field_name]==$key) || (!isset($this->answer_list[$field_name]) && isset($this->fields[$field_name]["default"]) && $this->fields[$field_name]["default"]==$key)))?$this->default_place_holder[FORM_INPUT_TYPE_SELECT]["selected_word"]:null),
											);
				}
				break;

			case FORM_INPUT_TYPE_RADIO:
				$form[$block_name] = array();

				foreach($this->fields[$field_name]["option_list"] as $key=>$value){
					$form[$block_name][] = array(
												$this->default_place_holder[FORM_INPUT_TYPE_RADIO]["value"]		=> $key,
												$this->default_place_holder[FORM_INPUT_TYPE_RADIO]["valuename"]	=> $value,
												$this->default_place_holder[FORM_INPUT_TYPE_RADIO]["checked"]		=> 
														(($set_answer && ((isset($this->answer_list[$field_name]) && $this->answer_list[$field_name]==$key) || (!isset($this->answer_list[$field_name]) && isset($this->fields[$field_name]["default"]) && $this->fields[$field_name]["default"]==$key)))?$this->default_place_holder[FORM_INPUT_TYPE_RADIO]["checked_word"]:null),
											);
				}

				break;

			case FORM_INPUT_TYPE_CHECK:		// チェックボックスの値は配列でいただく想定です
				$form[$block_name] = array();

				foreach($this->fields[$field_name]["option_list"] as $key=>$value){
					$form[$block_name][] = array(
												$this->default_place_holder[FORM_INPUT_TYPE_CHECK]["value"]		=> $key,
												$this->default_place_holder[FORM_INPUT_TYPE_CHECK]["valuename"]	=> $value,
												$this->default_place_holder[FORM_INPUT_TYPE_CHECK]["checked"]		=> 
//														(($set_answer && isset($this->answer_list[$field_name][$key]) && $this->answer_list[$field_name][$key]==$key)?"checked":null),
														(($set_answer && isset($this->answer_list[$field_name]) && in_array($key, $this->answer_list[$field_name]))?$this->default_place_holder[FORM_INPUT_TYPE_CHECK]["checked_word"]:null),
											);
				}

				break;

			default:
				break;
		}

		return $form;
	}



	//確認画面用
	private function generateConfirmItem($field_name)
	{
		$form = array();

		//変数名が正しいのか確認
		if(!isset($this->fields[$field_name])){
			return $form;
		}

		$setting = $this->fields[$field_name];

		//テンプレート表示でBLOCKを使う場合のBLOCK名
		$block_name = $field_name . BLOCK_LIST_SUFIX;

		switch($setting["input_type"])
		{
			case FORM_INPUT_TYPE_OTHER:
				$form[$field_name] = (
										(isset($this->answer_list[$field_name])) ?
											$this->answer_list[$field_name] :
											null
										);

				break;

			case FORM_INPUT_TYPE_SELECT:
				$form[$field_name] = (
										((isset($this->answer_list[$field_name])) && isset($this->fields[$field_name]["option_list"][$this->answer_list[$field_name]])) ?
											$this->fields[$field_name]["option_list"][$this->answer_list[$field_name]] :
											null
										);

				break;

			case FORM_INPUT_TYPE_RADIO:
				$form[$field_name] = (
										((isset($this->answer_list[$field_name])) && isset($this->fields[$field_name]["option_list"][$this->answer_list[$field_name]])) ?
											$this->fields[$field_name]["option_list"][$this->answer_list[$field_name]] :
											null
										);

				break;

			case FORM_INPUT_TYPE_CHECK:		// チェックボックスの値は配列でいただく想定です
				if(!empty($this->answer_list[$field_name])){
					$form[$block_name] = array();

					foreach($this->answer_list[$field_name] as $answer){
						$form[$block_name][] = array(
													$field_name		=> (isset($this->fields[$field_name]["option_list"][$answer]) ?
																		$this->fields[$field_name]["option_list"][$answer] :
																		null),
										);
					}
				}

				break;

			default:
				break;
		}

		return $form;
	}



////////////////////////////////////////////////////////////////////////////////////
////////////////////////	Validationはしっかりやる事		////////////////////////
////////////////////////////////////////////////////////////////////////////////////


	private function validateAnswer($rule_name=null)
	{
		$error_list = array();
		$varidation_rule_list = array();

		//どのvalidationをするんだい？

		//指定がない場合は全部やっちゃうよ
		if(is_null($rule_name)){
			foreach($this->validations as $varidation_rule){
				$varidation_rule_list = array_merge($varidation_rule_list, $varidation_rule);
			}
		}
		elseif(isset($this->validations[$rule_name])){
			$varidation_rule_list = $this->validations[$rule_name];
		}

		//エラー出す場所の分だけまわしまーす
		foreach($varidation_rule_list as $error=>$validate_rules){

			//Validationの分だけまわしまーす
			foreach($validate_rules as $rule){

				// 検証対象項目が指定されているパターン
				if(isset($rule["field"])){

					//fieldは単独
					if(!is_array($rule["field"])){
						// ものによっては配列なので
						switch($this->fields[$rule["field"]]["input_type"]){

							//これは配列
							case FORM_INPUT_TYPE_CHECK:
								$answers = (isset($this->answer_list[$rule["field"]])?$this->answer_list[$rule["field"]]:array());
								// NULLチェックの場合は配列が空でもダメ
								if($rule["method"] == NOT_NULL_CHECK_METHOD && empty($answers)){
										//テンプレート表示でmessageを表示する場所の名
										$error__holder = $error . ERROR_MESSAGE_SUFIX;
										$error_list[$error][$error__holder] = $rule["message"];
										continue 3;		// 次のruleに行きまーす
								}

								foreach($answers as $answer){
									$args = (isset($rule["arg"])?$rule["arg"]:array());

									// methodへの引数の先頭に検証したい値を追加
									array_unshift($args, $answer);

									// NULLチェックの場合
									if($rule["method"] == NOT_NULL_CHECK_METHOD){
										$result = call_user_func_array(array($this->validate_object, NOT_NULL_CHECK_METHOD), $args);
									}
									//NULLチェック以外の場合
									else{
										$not_null = call_user_func_array(array($this->validate_object, NOT_NULL_CHECK_METHOD), $args);

										$result = call_user_func_array(array($this->validate_object, $rule["method"]), $args);

										// nullかvalidate OK のときはOK
										$result = (!$not_null) || $result;
									}

									if(!$result){
										//テンプレート表示でmessageを表示する場所の名
										$error__holder = $error . ERROR_MESSAGE_SUFIX;
										$error_list[$error][$error__holder] = $rule["message"];
										continue 4;		// 次のruleに行きまーす
									}
								}

								break;

							default:
								$answer = (isset($this->answer_list[$rule["field"]])?$this->answer_list[$rule["field"]]:null);

								$args = (isset($rule["arg"])?$rule["arg"]:array());

								// methodへの引数の先頭に検証したい値を追加
								array_unshift($args, $answer);

								// NULLチェックの場合
								if($rule["method"] == NOT_NULL_CHECK_METHOD){
									$result = call_user_func_array(array($this->validate_object, NOT_NULL_CHECK_METHOD), $args);
								}
								//NULLチェック以外の場合
								else{
									$not_null = call_user_func_array(array($this->validate_object, NOT_NULL_CHECK_METHOD), $args);

									$result = call_user_func_array(array($this->validate_object, $rule["method"]), $args);

									// nullかvalidate OK のときはOK
									$result = (!$not_null) || $result;
								}

								if(!$result){
									//テンプレート表示でmessageを表示する場所の名
									$error__holder = $error . ERROR_MESSAGE_SUFIX;
									$error_list[$error][$error__holder] = $rule["message"];
									continue 3;		// 次のruleに行きまーす
								}

								break;
						}

					//fieldは複数
					}else{
						$answer = array();

						foreach($rule["field"] as $field_name){
							$answer[] = (isset($this->answer_list[$field_name])?$this->answer_list[$field_name]:null);
						}

						// methodへの引数配列を作成
						$args = array_merge($answer, (isset($rule["arg"])?$rule["arg"]:array()));

						$result = call_user_func_array(array($this->validate_object, $rule["method"]), $args);

						if(!$result){
							//テンプレート表示でmessageを表示する場所の名
							$error__holder = $error . ERROR_MESSAGE_SUFIX;
							$error_list[$error][$error__holder] = $rule["message"];
							continue 2;		// 次のruleに行きまーす
						}
					}
				}
				// 検証対象項目が指定されていないパターン
				else{
					$result = call_user_func_array(array($this->validate_object, $rule["method"]), array($this->answer_list));

					if(!$result){
						//テンプレート表示でmessageを表示する場所の名
						$error__holder = $error . ERROR_MESSAGE_SUFIX;
						$error_list[$error][$error__holder] = $rule["message"];
						continue 2;		// 次のruleに行きまーす
					}
				}
			}
		}

		return $error_list;
	}


	function answerCheck($rule_name=null)
	{
		$this->err_msg_list = array();

		//バリデーション
		$errors = $this->validateAnswer($rule_name);

		//エラーなし
		if(empty($errors)){
			// Tokenをセットしておきます
			$this->setToken();

			return true;
		}

		//エラーあり
		$this->err_msg_list = $errors;

		return false;
	}


	function getErrorList()
	{
		return $this->err_msg_list;
	}


}