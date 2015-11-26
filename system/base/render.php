<?php
/***************************************************************************
 * NFFQ for qiong
 * by fivebit.com
 **************************************************************************/

final class nffq_render {
	public static function renderResult($data,$method="php", $template='') {
		switch ($method) {
		case 'resource':
		case 'location':
			header("Location: ".$data);
			exit(0);
		case 'smarty':
			self::smartyRender($data,$template);
			break;
		case 'email':
			self::emailRender($data,$template);
			break;
		case 'csv':
		case 'xls':
			self::csvRender($data,$method);
			break;
		case 'ajax':
		case 'json':
			self::jsonRender($data);
			break;
		case 'php':
		default:
			break;
		}
	}

	// smarty模板渲染
	private static function smartyRender($data,$template, $display=true) {
        $sm = load_class('smarty',"compose");
        $st = $sm->display($template,$data,$display);
        return $st;
	}

	// email模板渲染
	private static function emailRender($template) {
		require_once(MT_EXT_PATH."/PHPMailer_v5.1/class.phpmailer.php");

		//1. 检查必填字段
		$data = &Env::$resData;
		$errhead = "Email发送失败：";
		if (!isset($data["from"])) throw new Exception("$errhead 未设置发信人");
		$from = $data["from"];
		if (!isset($data["to"])) throw new Exception("$errhead 未设置收信人");
		$to = $data["to"];
		if (!isset($data["subject"])) throw new Exception("$errhead 未填写邮件标题");
		$subject = toUTF8($data["subject"]);

		//2. 渲染邮件内容
		$content = self::smartyRender($template, false);

		//3. 发送邮件
		$mail = new PHPMailer();
		$mail->SetFrom($from, '');
		$addrs = explode(",", $to);
		foreach ($addrs as $addr) {
			$mail->AddAddress($addr, '');
		}
		if (isset($data["cc"])) {
			$ccs = explode(",", $data["cc"]);
			foreach ($ccs as $cc) {
				$mail->AddCC($cc, '');
			}
		}
		$mail->Subject = $subject;  //!< 设置邮件标题
		$mail->MsgHTML($content);   //!< 设置邮件内容
		$mail->AltBody = "To view the message, please use an HTML compatible email viewer!";
		$mail->Charset='UTF-8';
		$mail->SetLanguage("zh_cn");
		if(!$mail->Send()) {
			throw new Exception("$errhead 网络异常");
		}

		echo "邮件发送成功！<hr>";
		echo "From: $from<br>";
		echo "To: $to<br>";
		if (isset($data["cc"]))
			echo "Cc: ".$data["cc"]."<br>";
		echo "Subject: ".iconv("UTF-8","GBK//ignore",$subject);
	}
	// csv,xls文件渲染（下载）
	private static function csvRender($data,$suffix) {
		if (!isset($data["title"])) {
			throw new Exception("data[\"title\"] is not set");
		}
		$file = $data["title"].".".$suffix;
		header("Content-type: text/plain");
		header('Content-Disposition: attachment; filename="'.$file.'"');
		$sp = ($suffix=="xls") ? "\t" : ",";
		if (isset($data["head"])) 
			echo implode($sp, $data["head"])."\n";
		if (isset($data["lists"])){
			foreach ($data["lists"] as $item){
				echo implode($sp, $item)."\n";
            }
        }
	}

	// json,ajax格式渲染（ajax）
	private static function jsonRender($data) {
        if(!is_array($data)){
            $data = array('data'=>$data);
        }
		if (!isset($data["retcode"])) {
			$data["retcode"] = 0;
		}
		if (!isset($data["retmsg"])) {
			$data["retmsg"] = "";
		}
		echo json_encode($data);
	}
}
?>
