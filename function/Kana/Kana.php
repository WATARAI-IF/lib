<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// validate関連はここにまとめておきます。
//
//--------------------------------------------------------------------
// @filename	Datetime.php
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
// class FunctionDatetime
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/



class FunctionKana{

	/*********************************************************************
	*   関数名：Roman2Kana()
	*   概要  ：ローマ字をカナに変換します(結構適当ですよ)
	*   引数  ：	$roman	ローマ字
	*				$mode	true	変換できなかったものは削除
	*						false	変換できなかったものはアルファベットのまま
	*  -------------------------------------------------------------------
	*   戻り値：変換後のカナ
	*********************************************************************/
	static function Roman2Kana( $str, $mode=false )
	{
		$str = strtolower($str);

		$roman_list = array(
			//長いやつ優先
			"xtsu",

			"bya",	"bye",	"byi",	"byo",	"byu",	"cha",	"che",	"chi",	"cho",	"chu",	"cya",	"cye",	"cyi",	"cyo",	"cyu",	"dha",	"dhe",	"dhi",	"dho",	"dhu",	"dya",
			"dye",	"dyi",	"dyo",	"dyu",	"gwa",	"gwe",	"gwi",	"gwo",	"gwu",	"gya",	"gye",	"gyi",	"gyo",	"gyu",	"hya",	"hye",	"hyi",	"hyo",	"hyu",	"jya",	"jye",
			"jyi",	"jyo",	"jyu",	"kwa",	"kwe",	"kwi",	"kwo",	"kwu",	"kya",	"kye",	"kyi",	"kyo",	"kyu",	"lwa",	"lya",	"lye",	"lyi",	"lyo",	"lyu",	"mya",	"mye",
			"myi",	"myo",	"myu",	"nya",	"nye",	"nyi",	"nyo",	"nyu",	"pya",	"pye",	"pyi",	"pyo",	"pyu",	"qya",	"qye",	"qyi",	"qyo",	"qyu",	"rya",	"rye",	"ryi",
			"ryo",	"ryu",	"sha",	"she",	"shi",	"sho",	"shu",	"sya",	"sye",	"syi",	"syo",	"syu",	"tha",	"the",	"thi",	"tho",	"thu",	"tsa",	"tse",	"tsi",	"tsu",
			"tso",	"tya",	"tye",	"tyi",	"tyo",	"tyu",	"vya",	"vyu",	"vyo",	"xtu",	"xwa",	"xya",	"xye",	"xyi",	"xyo",	"xyu",	"zya",	"zye",	"zyi",	"zyo",	"zyu",

			"nn",	"bb",	"cc",	"dd",	"ff",	"gg",	"hh",	"jj",	"kk",	"ll",	"mm",	"pp",	"qq",	"rr",	"ss",	"tt",	"vv",	"ww",	"xx",	"yy",	"zz",
			"ba",	"be",	"bi",	"bo",	"bu",	"ca",	"ce",	"ci",	"co",	"cu",	"da",	"de",	"di",	"do",	"du",	"fa",	"fe",	"fi",	"fo",	"fu",	"ga",
			"ge",	"gi",	"go",	"gu",	"ha",	"he",	"hi",	"ho",	"hu",	"ja",	"je",	"ji",	"jo",	"ju",	"ka",	"ke",	"ki",	"ko",	"ku",	"la",	"le",
			"li",	"lo",	"lu",	"ma",	"me",	"mi",	"mo",	"mu",	"n'",	"na",	"ne",	"ni",	"nn",	"no",	"nu",	"pa",	"pe",	"pi",	"po",	"pu",	"qa",
			"qe",	"qi",	"qo",	"qu",	"ra",	"re",	"ri",	"ro",	"ru",	"sa",	"se",	"si",	"so",	"su",	"ta",	"te",	"ti",	"to",	"tu",	"va",	"ve",
			"vi",	"vo",	"vu",	"wa",	"we",	"wi",	"wo",	"wu",	"xa",	"xe",	"xi",	"xo",	"xu",	"ya",	"ye",	"yi",	"yo",	"yu",	"za",	"ze",	"zi",
			"zo",	"zu",	

			"-",

			//母音は最後
			"a",	"e",	"i",	"o",	"u",	"n",
		);

		$kana_list	= array(
			"ッ",

			"ビャ",	"ビェ",	"ビィ",	"ビョ",	"ビュ",	"チャ",	"チェ",	"チ",	"チョ",	"チュ",	"チャ",	"チェ",	"チィ",	"チョ",	"チュ",	"デャ",	"デェ",	"ディ",	"デョ",	"デュ",	"ヂャ",
			"ヂェ",	"ヂィ",	"ヂョ",	"デュ",	"グァ",	"グェ",	"グィ",	"グォ",	"グ",	"ギャ",	"ギェ",	"ギィ",	"ギョ",	"ギュ",	"ヒャ",	"ヒェ",	"ヒィ",	"ヒョ",	"ヒュ",	"ジャ",	"ジェ",
			"ジィ",	"ジョ",	"ジュ",	"クヮ",	"クェ",	"クィ",	"クォ",	"ク",	"キャ",	"キェ",	"キィ",	"キョ",	"キュ",	"ヮ",	"ャ",	"ェ",	"ィ",	"ョ",	"ュ",	"ミャ",	"ミェ",
			"ミィ",	"ミョ",	"ミュ",	"ニャ",	"ニェ",	"ニィ",	"ニョ",	"ニュ",	"ピャ",	"ピェ",	"ピィ",	"ピョ",	"ピュ",	"クャ",	"クェ",	"クィ",	"クョ",	"クュ",	"リャ",	"リェ",	"リィ",
			"リョ",	"リュ",	"シャ",	"シェ",	"シ",	"ショ",	"シュ",	"シャ",	"シェ",	"シィ",	"ショ",	"シュ",	"テャ",	"テェ",	"ティ",	"テョ",	"テュ",	"ツァ",	"ツェ",	"ツィ",	"ツ",
			"ツォ",	"チャ",	"チェ",	"チィ",	"チョ",	"チュ",	"ヴャ",	"ヴュ",	"ヴョ",	"ッ",	"ヮ",	"ャ",	"ェ",	"ィ",	"ョ",	"ュ",	"ジャ",	"ジェ",	"ジィ",	"ジョ",	"ジュ",

			"ン",	"ッb",	"ッc",	"ッd",	"ッf",	"ッg",	"ッh",	"ッj",	"ッk",	"ッl",	"ッm",	"ッp",	"ッq",	"ッr",	"ッs",	"ッt",	"ッv",	"ッw",	"ッx",	"ッy",	"ッz",
			"バ",	"ベ",	"ビ",	"ボ",	"ブ",	"カ",	"セ",	"シ",	"コ",	"ク",	"ダ",	"デ",	"ヂ",	"ド",	"ヅ",	"ファ",	"フェ",	"フィ",	"フォ",	"フ",	"ガ",
			"ゲ",	"ギ",	"ゴ",	"グ",	"ハ",	"ヘ",	"ヒ",	"ホ",	"フ",	"ジャ",	"ジェ",	"ジ",	"ジョ",	"ジュ",	"カ",	"ケ",	"キ",	"コ",	"ク",	"ァ",	"ェ",
			"ィ",	"ォ",	"ゥ",	"マ",	"メ",	"ミ",	"モ",	"ム",	"ン",	"ナ",	"ネ",	"ニ",	"ン",	"ノ",	"ヌ",	"パ",	"ペ",	"ピ",	"ポ",	"プ",	"クァ",
			"クェ",	"クィ",	"クォ",	"ク",	"ラ",	"レ",	"リ",	"ロ",	"ル",	"サ",	"セ",	"シ",	"ソ",	"ス",	"タ",	"テ",	"チ",	"ト",	"ツ",	"ヴァ",	"ヴェ",
			"ヴィ",	"ヴォ",	"ヴ",	"ワ",	"ウェ",	"ウィ",	"ヲ",	"ウ",	"ァ",	"ェ",	"ィ",	"ォ",	"ゥ",	"ヤ",	"イェ",	"イ",	"ヨ",	"ユ",	"ザ",	"ゼ",	"ジ",
			"ゾ",	"ズ",

			"ー",

			//母音は最後
			"ア",	"エ",	"イ",	"オ",	"ウ",	"ン",
						);

		// へーんーーかーーーん
		$str = str_replace( $roman_list, $kana_list, $str);

		//残ったアルファベットと記号削除
		if($mode){
			$str = preg_replace("/[[:alpha:]]/", "", $str);
			$str = preg_replace("/[[:punct:]]/", "", $str);
		}

		return $str;
	}

}

?>