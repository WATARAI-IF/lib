<?php
require_once('config.php');

class pagerController
{
	protected $options = array(
				"print_num"				=> 10,							// １ページに表示するデータ数
				"page_parameter"		=> "page_no",					// ページ番号のパラメータ名
				"prev_text"				=> "<前ページ",					// 前ページリンク文字
				"next_text"				=> "次ページ>",					// 次ページリンク文字
				"first_text"			=> "<<最初",					// 先頭ページリンク文字
				"last_text"				=> "最後>>",					// 最終ページリンク文字
				"page_separator"		=> "|",							// ページリンク間の文字
				"separator"				=> " ",							// 各パーツ間の文字
				"more_text"				=> "...",						// 「まだあるよ」ってやつ
				"page_link_max"			=> "10",						// 表示するリンクの最大数
				"empty_tag"				=> '""',						// データがない場合に返すタグ
				"empty_message"			=> false,						// 前や次、最初や最後がない場合にテキストを挿入するか
	);

	//pagerとして何がほしいの？
	protected $items = array(
		"first"			=> true,			// 最初のページ
		"prev"			=> false,			// 前のページ
		"page_link"		=> true,			// ページ番号
		"next"			=> false,			// 次のページ
		"last"			=> true,			// 最後のページ
	);

	protected $link_base = '<a href="{script_name}?{page_parameter}={page_no}{get_query}">{link_text}</a>';

	protected $get_params	= array();					// AタグにつけるGETパラメータ配列

	protected $script_name;								// スクリプト名
	protected $current_page = 1;		// 表示しているページ番号(最初のページが１)
	protected $data_num;				// ページングの対象となるデータ数
	protected $last_page = 1;			// 最終ページのページ番号

	function __construct($data_num, $options=array(), $link_base=null)
	{
		// オプション設定しておきます
		if(!empty($options)){
			$this->setOptions($options);
		}

		// おれはいったい誰なんだ
		$this->script_name = $_SERVER['SCRIPT_NAME'];

		// データ数が分からないと始まりませんよ
		$this->setDataNum($data_num);

		// 現在のページ番号設定
		if(isset($_REQUEST[$this->options["page_parameter"]])){
			$this->setCurrentPage((int)$_REQUEST[$this->options["page_parameter"]]);
		}else{
			$this->setCurrentPage(1);
		}

		if($link_base != null){
			$this->link_base = $link_base;
		}
	}


	//オプション設定
	function setOptions($options)
	{
		$this->options = array_merge($this->options, $options);
	}


	//全データ数設定
	function setDataNum($data_num)
	{
		// データ数設定
		$this->data_num = $data_num;

		// 最終ページ番号設定
		$this->last_page = (int)ceil($data_num/$this->options["print_num"]);
	}


	//現在ページを設定
	function setCurrentPage($current_page)
	{
		$this->current_page = min(max($this->current_page, $current_page), $this->last_page);
	}


	// 現在のページ番号を教えます
	function getCurrentPageNo()
	{
		return $this->current_page;
	}


	// 最後のページ番号を教えます
	function getLastPageNo()
	{
		return $this->last_page;
	}


	//最初のページか判定
	function isFirstPage()
	{
		return $this->current_page == 1;
	}


	//最後のページか判定
	function isLastPage()
	{
		return $this->current_page == $this->getLastPageNo();
	}


	//このページの情報を教えましょう
	function getPageInfo()
	{
		return array(
				/*	現在のページ番号	*/	"current_page"	=>	$this->current_page,
				/*	最後のページ番号	*/	"last_page"		=>	$this->last_page,
				/*	何件中				*/	"data_num"		=>	$this->data_num,
				/*	何件から			*/	"min_num"		=>	max(($this->current_page-1) * $this->options["print_num"]+1, 0),
				/*	何件まで			*/	"max_num"		=>	min($this->current_page * $this->options["print_num"], $this->data_num),
		);
	}


	//リンクのGETパラメータを設定
	protected function setGetParams($get_params=array(), $ignore_page_no=true)
	{
		// ページ番号部分はこちらで指定します
		if($ignore_page_no && isset($get_params[$this->options["page_parameter"]])){
			unset($get_params[$this->options["page_parameter"]]);
		}

		$this->get_params = $get_params;
	}


	//リンクのGETパラメータを削除
	function clearGetParams()
	{
		$this->get_params = array();
	}


	//pagerタグの出力設定
	function setItems($items)
	{
		$this->items = array_merge($this->items, $items);
	}


	// pagerのリンクを差し上げます。
	function getPagerTagList($get_params=null, $item_list=null)
	{
		if($this->data_num == 0){
			return array("empty_tag" => $this->options["empty_tag"]);
		}

		// pagerとして何を返すか
		if(is_array($item_list)){
			$this->setItems($item_list);
		}

		// GETパラメータで設定するやつを設定
		if(is_array($get_params)){
			$this->setGetParams($get_params);
		}

		//GETで渡すパラメータ部分を作っておきます
		$query = $this->getQuery();

		// リンクの共通部分だけ先に置換しておきます
		$link_base = str_replace(
							array("{script_name}",		"{page_parameter}",					"{get_query}"),
							array($this->script_name,	$this->options["page_parameter"],	$query),
					$this->link_base);


		$link_tags = array();

		foreach($this->items as $item=>$target){
			// 出力対象ではない
			if(!$target){
				continue;
			}

			switch($item){
				case "first":
					//最初のページ
					if($this->isFirstPage()){
						if($this->options["empty_message"]){
							$link_tags["first"] = $this->options["first_text"];
						}else{
							$link_tags["first"] = null;
						}
					}else{
						//tagを作ります
						$link_tags["first"] = str_replace(
											array( "{page_no}",				"{link_text}"),
											array( 1,						$this->options["first_text"]),
										$link_base);
					}
					break;
				case "prev":
					//前のページ
					if($this->isFirstPage()){
						if($this->options["empty_message"]){
							$link_tags["prev"] = $this->options["prev_text"];
						}else{
							$link_tags["prev"] = null;
						}
					}else{
						//tagを作ります
						$link_tags["prev"] = str_replace(
											array( "{page_no}",				"{link_text}"),
											array( $this->current_page-1,	$this->options["prev_text"]),
										$link_base);
					}
					break;
				case "page_link":
					// リンクの最初のページ番号を考えます
					$first_link_page = (int)max(1, min($this->last_page - $this->options["page_link_max"] + 1, $this->current_page - floor(($this->options["page_link_max"] - 1) / 2)));

					// リンクの最後のページ番号を考えます
					$last_link_page = (int)min($this->last_page, max($this->options["page_link_max"], $this->current_page + ceil(($this->options["page_link_max"] - 1) / 2)));

					$page_link_tags = array();

					if($first_link_page > 1 ){
						$page_link_tags[] = $this->options["more_text"];
					}

					// ページングするページ数分だけタグ生成
					for($page=$first_link_page; $page<=$last_link_page; $page++){
						//現在ページ
						if($page == $this->current_page){
							$page_link_tags[] = $page;
						}else{
							//tagを作ります
							$page_link_tags[] = str_replace(
													array( "{page_no}",		"{link_text}"),
													array( $page,			$page),
												$link_base);

						}
					}

					if($last_link_page < $this->last_page ){
						$page_link_tags[] = $this->options["more_text"];
					}

					$link_tags["page_link"] = implode($this->options["page_separator"], $page_link_tags);

					break;
				case "next":
					//次のページ
					if($this->isLastPage()){
						if($this->options["empty_message"]){
							$link_tags["next"] = $this->options["next_text"];
						}else{
							$link_tags["next"] = null;
						}
					}else{
						//tagを作ります
						$link_tags["next"] = str_replace(
											array( "{page_no}",				"{link_text}"),
											array( $this->current_page+1,	$this->options["next_text"]),
										$link_base);
					}
					break;
				case "last":
					//最後のページ
					if($this->isLastPage()){
						if($this->options["empty_message"]){
							$link_tags["last"] = $this->options["last_text"];
						}else{
							$link_tags["last"] = null;
						}
					}else{
						//tagを作ります
						$link_tags["last"] = str_replace(
											array( "{page_no}",			"{link_text}"),
											array( $this->last_page,	$this->options["last_text"]),
										$link_base);
					}
					break;
				default:
					break;
			}
		}

		return $link_tags;

	}



	// pagerのリンクを差し上げます。
	function getPagerTag($get_params=null, $item_list=null)
	{
		$link_tags = $this->getPagerTagList($get_params, $item_list);

		return implode($this->options["separator"], $link_tags);

	}





	protected function getQuery(){
		$query = "";

		if(empty($this->get_params)){
			return $query;
		}

		return "&" . http_build_query($this->get_params);
	}


	// MySQLのLIMIT句用パラメータ
	function getOffsetCount()
	{
		return array(
					"offset"		=> (int)max(($this->current_page-1) * $this->options["print_num"], 0),
					"count"			=> min($this->data_num, $this->data_num - ($this->current_page-1)*$this->options["print_num"], $this->options["print_num"]),
		);
	}

}
?>
