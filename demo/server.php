<?php

exit('ok');



   $ReturnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "transaction_id" =>  $_REQUEST["transaction_id"], // 支付流水号
            "status" => $_REQUEST["status"],
        );
      
        $Md5key = "lvjip0x4sqeni4h69pzbpgorp3u2ea3w";

        file_put_contents("notify.txt", http_build_query($_REQUEST)."\n", FILE_APPEND);

        file_put_contents("notify.txt", json_encode($ReturnArray)."\n", FILE_APPEND);
		ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        if ($sign == $_REQUEST["sign"]) {
			
            if ($_REQUEST["status"] == "1") {
				   $str = "交易成功！订单号：".$_REQUEST["orderid"];
                   file_put_contents("success.txt",$str."\n", FILE_APPEND);
				   exit("ok");
            }
        }

?>