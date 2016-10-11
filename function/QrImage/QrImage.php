<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// プロジェクト内で共通で使えそうな関数を作っておきます。
//	デフォルトで用意してあるのは適当なので、無視して最初から実装して行きましょう
//	いろんな案件で使えそうな共通関数はFunction/Global.phpで定義してあります。
//	ここではそれらを使う可能性があるのでrequire_onceします。
//--------------------------------------------------------------------
// @filename	Local.php
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
// class FunctionLocal
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

class QrImage{

	//****************************************************************
	// [Summary]    QR画像を生成します
	//****************************************************************
	static function makeQRImage($qr_parameter, $size=2)
	{
		require_once("Image/QRCode.php"); 

		$qr = new Image_QRCode(); 

		$option = array(
			"module_size"		=> $size,		// サイズ   => 1～19で指定
			"image_type"		=> "jpeg",		// 画像形式 => jpegかpngを指定
			"output_type"		=> "return",	// 出力方法 => displayかreturnで指定 returnの場合makeCodeで画像リソースが返される
			"error_correct"		=> "M"			// クオリティ(L<M<Q<H)を指定
		);

		$img_data = $qr->makeCode($qr_parameter, $option);

		//$img_dataは画像リソースなので、画像データに変換します
		//まず、出力をバファリングするように設定
		ob_start();
		//画像リソースから画像を表示 -> バッファリングされます
		imagejpeg($img_data);
		//バッファの内容(画像)取得
		$img_data = ob_get_contents();
		//バッファクリアしてバッファリングオフ
		ob_end_clean(); // delete buffer

		return $img_data;
	}
}

