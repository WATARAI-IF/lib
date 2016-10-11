<?php
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//--------------------------------------------------------------------
// メール送信用のclass
//
//--------------------------------------------------------------------
// @filename	SendController.php
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
// class Mail_SendController 
//	Mail_SendController				構築子。メンバ変数を初期化する。
//	addTo							送信先を追加する。
//	addCc							Cc先を追加する。
//	addBcc							Bcc先を追加する。
//	setFrom							信元を設定する。
//	setReplyTo						ReplyToを設定する。
//	setSubject						Subjectを設定する。
//	setBody							本文を設定する。
//	addAttachment					添付ファイルを追加する。
//	sendMail						メールを送信する。
//
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/

//--- 使用例 ---//
/**
	$mail = new MailSend();										// インスタンス化して
	$mail->addTo("hiroto.tsuchiya@i-studio.co.jp", "to土屋");			// 送信先追加
	$mail->addTo("nobuhito.oozeki@i-studio.co.jp", "to大関");			// 送信先追加
	$mail->addCc("nobuhito.oozeki@i-studio.co.jp", "cc大関");			// Cc追加
	$mail->addBcc("nobuhito.oozeki@i-studio.co.jp", "bcc大関");			// Bcc先追加
	$mail->setFrom("nobuhito.oozeki@i-studio.co.jp", "from大関");		// From設定
	$mail->setReplyTo("hiroto.tsuchiya@i-studio.co.jp", "土屋");		// 返信先設定
	$mail->addAttachment("mail/Attachment.txt");						// 添付ファイル追加
	$mail->addAttachment("mail/Attachment.xls");						// 添付ファイル追加
	$mail->setSubject("SPAMの題名です");								// 題名設定
	$mail->setBody("bodyの本文です");									// 本文設定

	$mail->sendMail();													//送信
**/


require_once('config.php');

require_once(DIR_LIB_PEAR . 'Mail.php');
require_once(DIR_LIB_PEAR . 'Mail/mime.php');


class MailSendController
{

//---------------------------------------------------------------------------------------------------------------------------//


	var $encoding;			// 送信先アドレス
	var $To;			// 送信先アドレス
	var $Cc;			// ccアドレス
	var $Bcc;			// Bccアドレス
	var $From;			// 送信元アドレス
	var $ReplyTo;		// 指定がない場合は送信者
//	var $ReturnPath;	// 指定がない場合は送信者
	var $Subject;		// 題名
	var $Body;			// 本文
	var $Attachment;	// 添付ファイル

	/*********************************************************************
	*   関数名：SendMailClass()
	*   概要　：構築子。メンバ変数を初期化する。
	*
	*      パラメータ    R/W            内容
	*  -----------------+---+---------------------------------------------
	*   戻り値：なし
	*********************************************************************/
	function __construct($encoding=DEFAULT_ENCODE) 
	{
		$this->encoding = $encoding;
		$this->To = array();
		$this->Cc = array();
		$this->Bcc = array();
		$this->From = null;
		$this->ReplyTo = null;
//		$this->ReturnPath = null;
		$this->Subject = null;
		$this->Body = null;
		$this->Attachment = array();
	}

	/*********************************************************************
	*   関数名：addTo()
	*   概要　：送信先を追加する。
	*
	*      パラメータ    R/W            内容
	*  -----------------+---+---------------------------------------------
	*   $address          R   送信先メールアドレス(内容の精査は行いません)
	*   $name             R   送信先表示名(詳しい規定がよくわからないので、「"」「<」「>」は使えないです)
	*  -------------------------------------------------------------------
	*   戻り値：
	*********************************************************************/
	function addTo($address, $name=null)
	{
		//表示名チェック
		$name = $this->checkName($name);
		array_push($this->To, array("address"=>$address, "name"=>$name));

		return true;
	}

	function resetTo()
	{
		$this->To = array();

		return true;
	}

	function setTo($address, $name=null)
	{
		$this->resetTo();
		$this->addTo($address, $name);

		return true;
	}



	/*********************************************************************
	*   関数名：addCc()
	*   概要　：Cc先を追加する。
	*
	*      パラメータ    R/W            内容
	*  -----------------+---+---------------------------------------------
	*   $address          R   Ccメールアドレス(内容の精査は行いません)
	*   $name             R   Cc表示名(詳しい規定がよくわからないので、「"」「<」「>」は使えないです)
	*  -------------------------------------------------------------------
	*   戻り値：
	*********************************************************************/
	function addCc($address, $name=null)
	{
		//表示名チェック
		$name = $this->checkName($name);
		array_push($this->Cc, array("address"=>$address, "name"=>$name));

		return true;
	}

	function resetCc()
	{
		$this->Cc = array();

		return true;
	}

	function setCc($address, $name=null)
	{
		$this->resetCc();
		$this->addCc($address, $name);

		return true;
	}

	/*********************************************************************
	*   関数名：addBcc()
	*   概要　：Bcc先を追加する。
	*
	*      パラメータ    R/W            内容
	*  -----------------+---+---------------------------------------------
	*   $address          R   Ccメールアドレス(内容の精査は行いません)
	*   $name             R   Cc表示名(詳しい規定がよくわからないので、「"」「<」「>」は使えないです)
	*  -------------------------------------------------------------------
	*   戻り値：
	*********************************************************************/
	function addBcc($address, $name=null)
	{
		//表示名チェック
		$name = $this->checkName($name);
		array_push($this->Bcc, array("address"=>$address, "name"=>$name));

		return true;
	}

	function resetBcc()
	{
		$this->Bcc = array();

		return true;
	}

	function setBcc($address, $name=null)
	{
		$this->resetBcc();
		$this->addBcc($address, $name);

		return true;
	}

	/*********************************************************************
	*   関数名：setFrom()
	*   概要　：送信元を設定する。
	*
	*      パラメータ    R/W            内容
	*  -----------------+---+---------------------------------------------
	*   $address          R   送信元メールアドレス(内容の精査は行いません)
	*   $name             R   送信元表示名(詳しい規定がよくわからないので、「"」「<」「>」は使えないです)
	*  -------------------------------------------------------------------
	*   戻り値：
	*********************************************************************/
	function setFrom($address, $name=null)
	{
		//表示名チェック
		$name = $this->checkName($name);
		$this->From = array("address"=>$address, "name"=>$name);

		return true;
	}


	/*********************************************************************
	*   関数名：setReplyTo()
	*   概要　：ReplyToを設定する。
	*
	*      パラメータ    R/W            内容
	*  -----------------+---+---------------------------------------------
	*   $address          R   送信元メールアドレス(内容の精査は行いません)
	*   $name             R   送信元表示名(詳しい規定がよくわからないので、「"」「<」「>」は使えないです)
	*  -------------------------------------------------------------------
	*   戻り値：
	*********************************************************************/
	function setReplyTo($address, $name=null)
	{
		//表示名チェック
		$name = $this->checkName($name);
		$this->ReplyTo = array("address"=>$address, "name"=>$name);

		return true;
	}


	/*********************************************************************
	*   関数名：setSubject()
	*   概要　：Subjectを設定する。
	*
	*      パラメータ    R/W            内容
	*  -----------------+---+---------------------------------------------
	*   $subject          R   メールの題名
	*  -------------------------------------------------------------------
	*   戻り値：
	*********************************************************************/
	function setSubject($subject=null)
	{
		$this->Subject = $subject;

		return true;
	}


	/*********************************************************************
	*   関数名：setBody()
	*   概要　：本文を設定する。
	*
	*      パラメータ    R/W            内容
	*  -----------------+---+---------------------------------------------
	*   $body          R   メールの本文
	*  -------------------------------------------------------------------
	*   戻り値：
	*********************************************************************/
	function setBody($body=null)
	{
		$this->Body = $body;

		return true;
	}


	/*********************************************************************
	*   関数名：addAttachment()
	*   概要　：添付ファイルを追加する。
	*
	*      パラメータ    R/W            内容
	*  -----------------+---+---------------------------------------------
	*   $file            R   添付ファイル
	*   $c_type          R   コンテントタイプ
	*  -------------------------------------------------------------------
	*   戻り値：
	*********************************************************************/
	function addAttachment($file, $c_type='application/octet-stream')
	{
		array_push($this->Attachment, array("file"=>$file, "c_type"=>$c_type));

		return true;
	}


	/*********************************************************************
	*   関数名：sendMail()
	*   概要　：メールを送信する。
	*
	*      パラメータ    R/W            内容
	*  -----------------+---+---------------------------------------------
	*  -------------------------------------------------------------------
	*   戻り値：  false.失敗
	*********************************************************************/
	function sendMail( $mail_host="localhost")
	{
		try{
			//parameterチェック
			if(count($this->To) == 0)			throw new Exception("宛先不明エラー");
			if(!isset($this->From))				throw new Exception("送信者不明エラー");

			$headers = array();
			$targetEncoding = "iso-2022-jp";
//特殊文字対策
//携帯によっては化けちゃうので、不採用
//			$targetEncoding = "ISO-2022-JP-MS";

			//この辺よくわからないけど、おまじない的な感じで
			$headers['X-Mailer'] = "PHP-mailer";
			$headers['Content-Type'] = 'text/plain; charset="iso-2022-jp"';
			$headers['Content-Transfer-Encoding'] = '7bit';
			$headers['Mime-Version'] = '1.0';


/*
			// 元のエンコーディングを保存
			$orgEncoding = mb_internal_encoding();
			// 変換したい文字列のエンコーディングをセット
			mb_internal_encoding($targetEncoding); 
*/


			//Toを生成します
			foreach($this->To as $to){
				if(!isset($headers["To"]))	$headers["To"] = "";
				else						$headers["To"] .= ",";

				if(isset($to["name"])){
					$headers["To"] .= "\"".mb_encode_mimeheader(mb_convert_encoding($to["name"],$targetEncoding,$this->encoding), $targetEncoding)."\" <".$to["address"].">";
				}else{
					$headers["To"] .= $to["address"];
				}
			}

			//Ccを生成します
			foreach($this->Cc as $cc){
				if(!isset($headers["Cc"]))	$headers["Cc"] = "";
				else						$headers["Cc"] .= ",";

				if(isset($cc["name"])){
					$headers["Cc"] .= "\"".mb_encode_mimeheader(mb_convert_encoding($cc["name"],$targetEncoding,$this->encoding), $targetEncoding)."\" <".$cc["address"].">";
				}else{
					$headers["Cc"] .= $cc["address"];
				}
			}

			//Bccを生成します
			foreach($this->Bcc as $bcc){
				if(!isset($headers["Bcc"]))	$headers["Bcc"] = "";
				else						$headers["Bcc"] .= ",";

				if(isset($bcc["name"])){
					$headers["Bcc"] .= "\"".mb_encode_mimeheader(mb_convert_encoding($bcc["name"],$targetEncoding,$this->encoding), $targetEncoding)."\" <".$bcc["address"].">";
				}else{
					$headers["Bcc"] .= $bcc["address"];
				}
			}

			$recipients = $headers["To"];
			if(isset($headers["Cc"]))		$recipients .= "," . $headers["Cc"];
			if(isset($headers["Bcc"]))		$recipients .= "," . $headers["Bcc"];

			//Fromを生成します
			if(isset($this->From["name"])){
				$headers["From"] = "\"".mb_encode_mimeheader(mb_convert_encoding($this->From["name"],$targetEncoding,$this->encoding), $targetEncoding)."\" <".$this->From["address"].">";
			}else{
				$headers["From"] = $this->From["address"];
			}

			//ReplyToを生成します
			if(isset($this->ReplyTo)){
				if(isset($this->ReplyTo["name"])){
					$headers["Reply-to"] = "\"".mb_encode_mimeheader(mb_convert_encoding($this->ReplyTo["name"],$targetEncoding,$this->encoding), $targetEncoding)."\" <".$this->ReplyTo["address"].">";
				}else{
					$headers["Reply-to"] = $this->ReplyTo["address"];
				}
			}else{
			//指定がない場合は From を使用
				if(isset($this->From["name"])){
					$headers["Reply-to"] = "\"".mb_encode_mimeheader(mb_convert_encoding($this->From["name"],$targetEncoding,$this->encoding), $targetEncoding)."\" <".$this->From["address"].">";
				}else{
					$headers["Reply-to"] = $this->From["address"];
				}
			}


			$headers["Subject"] = mb_encode_mimeheader(mb_convert_encoding($this->Subject,$targetEncoding,$this->encoding), $targetEncoding);

/*
			// 保存しておいたエンコーディングに戻す
			mb_internal_encoding($orgEncoding);
*/

			$mail_object =& Mail::factory('smtp', array("host"=>$mail_host));

			$mime = new Mail_Mime("\n");

			if(isset($this->Body)){
				$body = mb_convert_encoding($this->Body,$targetEncoding,$this->encoding);
//				$body = $this->Body;
				$mime->setTxtBody($body);
			}

			//添付ファイル追加
			foreach($this->Attachment as $attachment){
				$mime->addAttachment($attachment["file"], $attachment["c_type"]);
			}

			$body_encode = array(
			  "head_charset" => $targetEncoding,
			  "text_charset" => $targetEncoding
//			  "text_charset" => $this->encoding
			);
			 
			$mime_body   = $mime->get($body_encode);
			$mime_header = $mime->headers($headers);
			//$recipientsはCc/Bcを含む送信先リスト
			$return = $mail_object->send($recipients, $mime_header, $mime_body);
//			$return = $mail_object->send($recipients, $mime_header, $body);

			if (PEAR::isError($return)){
	//			throw new Exception("メール送信エラー");
				return false;
			}
			return true;
		} catch(Exception $e) {
			return false;
		}
	}

	private function checkName($name=null)
	{
		//$nameに「"」「<」「>」のいずれかが含まれていた場合は無効(致命的では無いのでエラーにはしません)
		if( isset($name) && preg_match('/[\"<>]/', $name) )	$name = null;

		return $name;
	}
}


?>
