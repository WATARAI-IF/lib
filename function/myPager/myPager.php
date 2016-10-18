<?php

class myPager
{
	private $show_limit_options = array(
				"show_limit"			=> false,				// カレント付近以外の表示を制限するか
				"show_under_current"	=> 3,					// カレント前で表示するページ数
				"show_over_current"		=> 3,					// カレント後で表示するページ数
				"edge_add_list"		=> false,				// 最初と最後のページリストに追加するか
				"extra_over_edge"		=> true,				// 端を超えた分をもう一方に追加にするか
	);

	private $data_count;
	private $first = 1;
	private $last = 1;
	private $current;
	private $show_start;
	private $show_end;

	function __construct( $data_count, $data_count_page, $current, $show_limit_options=array() )
	{
		// データ数
		$this->data_count = $data_count;

		// 最後のページ
		if(!$this->isNoData()) {
			$this->last = (int)ceil($data_count / $data_count_page);
		}

		// 今のページ
		$this->current = $current;

		// 表示制限時のオプションを設定
		$this->setShowLimitOptions($show_limit_options);

		// 表示範囲を設定
		$this->setShowArea();
	}


	public function isNoData()
	{
		return ($this->data_count <= 0);
	}


	private function setShowLimitOptions($show_limit_options)
	{
		// オプションを設定
		$this->show_limit_options = array_merge($this->show_limit_options, $show_limit_options);

		// カレント付近の表示件数がMAX以上なら、全件表示で
		if(($this->show_limit_options["show_under_current"] + $this->show_limit_options["show_over_current"]) >= $this->last) {
			$this->show_limit_options["show_limit"] = false;
		}

		return true;
	}


	protected function setShowArea()
	{
		// 最初と最後のページを表示開始・終了位置に設定
		$this->show_start = $this->first;
		$this->show_end = $this->last;

		// データ数0件？
		if($this->isNoData()) {
			return true;
		}

		// 表示制限あり
		if($this->show_limit_options["show_limit"]) {
			// カレント付近を表示開始・終了位置に設定
			$this->show_start = $this->current - $this->show_limit_options["show_under_current"];
			$this->show_end = $this->current + $this->show_limit_options["show_over_current"];

			// 表示開始位置が最初のページ以下
			if($this->show_start < $this->first) {
				// 超えた分は表示終了位置のほうに上乗せ
				if($this->show_limit_options["extra_over_edge"]) {
					$this->show_end += $this->first - $this->show_start;
				}

				// 表示開始位置は最初のページ
				$this->show_start = $this->first;
			}
			// 表示終了位置が最後のページ以上
			elseif($this->show_end > $this->last) {
				// 超えた分は表示開始位置のほうに上乗せ
				if($this->show_limit_options["extra_over_edge"]) {
					$this->show_start -= $this->show_end - $this->last;
				}

				// 表示終了位置は最後のページ
				$this->show_end = $this->last;
			}
		}

		return true;
	}


	public function getAllPagerItems()
	{
		// ページリストをもらう
		$pager_items = array("page_list" => $this->getPageList());

		// 「次へ」とかのブロックをもらう
		$pager_items = $pager_items + $this->getShiftItems();

		return $pager_items;
	}


	public function getPageList()
	{
		$page_list = array();

		// データないなら空を返すよ
		if($this->isNoData()) {
			return $page_list;
		}

		// 表示範囲
		for($i = $this->show_start; $i <= $this->show_end; $i++) {
			$page_list[$i] = array("page_num" => $i);
		}

		// 表示制限あり
		if($this->show_limit_options["show_limit"]) {
			// 両端をリストに追加
			if(!$this->show_limit_options["edge_add_list"]) {
				// 最初のページが表示範囲に含まれてなければ追加
				if($this->first < $this->show_start) {
					$page_list[$this->first] = array("page_num" => $this->first);
				}

				// 最後のページが表示範囲に含まれてなければ追加
				if($this->last > $this->show_end) {
					$page_list[$this->last] = array("page_num" => $this->last);
				}

				// 最初から2番目のページが表示範囲に含まれていなければ省略ブロックを
				if(($this->first+1) < $this->show_start) {
					$page_list[$this->show_start]["HIDE_UNDER_SHOW_AREA_BLOCK"] = true;
				}

				// 最後から2番目のページが表示範囲に含まれていなければ省略ブロックを
				if(($this->last-1) > $this->show_end) {
					$page_list[$this->show_end]["HIDE_OVER_SHOW_AREA_BLOCK"] = true;
				}
			}
			else {
				// 最初のページが表示範囲に含まれていなければ省略ブロックを
				if($this->first < $this->show_start) {
					$page_list[$this->show_start]["HIDE_UNDER_SHOW_AREA_BLOCK"] = true;
				}

				// 最初のページが表示範囲に含まれていなければ省略ブロックを
				if($this->last > $this->show_end) {
					$page_list[$this->show_end]["HIDE_OVER_SHOW_AREA_BLOCK"] = true;
				}
			}
		}

		// 順番整理しましょう
		ksort($page_list);

		// ちなみにカレントはここです
		$page_list[$this->current]["CURRENT_BLOCK"] = true;

		return $page_list;
	}


	public function getShiftItems()
	{
		$items = array();

		// データないなら空を返すよ
		if($this->isNoData()) {
			return $items;
		}

		// カレントが最初のページじゃなければ一個前に飛べる
		if($this->current != $this->first) {
			$items["TO_PREV_BLOCK"] = true;
			$items["prev_page"] = ($this->current - 1);
		}

		// カレントが最終ページじゃなければ一個次に進める
		if($this->current != $this->last) {
			$items["TO_NEXT_BLOCK"] = true;
			$items["next_page"] = ($this->current + 1);
		}

		// 最初のページが表示範囲に含まれてなければ最初に戻れる
		if(($this->show_limit_options["show_limit"] && !$this->show_limit_options["edge_add_list"] && ($this->first < $this->show_start))) {
			$items["TO_FIRST_BLOCK"] = true;
			$items["first_page"] = $this->first;
		}

		// 最後のページが表示範囲に含まれてなければ最後に飛べる
		if(($this->show_limit_options["show_limit"] && !$this->show_limit_options["edge_add_list"] && ($this->last > $this->show_end))) {
			$items["TO_LAST_BLOCK"] = true;
			$items["last_page"] = $this->last;
		}

		return $items;
	}


	public function getTotalPageNum()
	{
		return $this->last;
	}
}
