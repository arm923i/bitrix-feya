<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) {
	echo GetMessage("IBLOCK_NOT_INCLUDE");
	return;
}
elseif(!CModule::IncludeModule("gotech.hotelonline")) {
	ShowError(GetMessage("ONLINEBOOKING_MODULE_NOT_INSTALLED"));
	return;
}
else {
	if($this->includeComponentLang("", OnlineBookingSupport::getLanguage()) == NULL){
		__IncludeLang(dirname(__FILE__)."/lang/".OnlineBookingSupport::getLanguage()."/component.php");
	}
	$APPLICATION->SetTitle(GetMessage("TITLE_MODULE"));
	$iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
	$arResult["UUID"] = "";
	if(isset($_REQUEST["uuid"]) && !empty($_REQUEST["uuid"])){
		$arResult["UUID"] = $_REQUEST["uuid"];
	}elseif(isset($arParams["UUID"]) && !empty($arParams["UUID"])){
		$arResult["UUID"] = $arParams["UUID"];
	}
	if(isset($arParams["RESULT"])){
		$arResult["SUCCESS"] = $arParams["RESULT"];
		$APPLICATION->IncludeComponent("onlinebooking:reservation.header", "");
		$this->IncludeComponentTemplate();
	}else{
		if(isset($_REQUEST["TransactionID"]) && !empty($_REQUEST["TransactionID"])){
			$strRes = "";
			foreach ( $_REQUEST as $key => $value ) {
				if($key == "OrderDescription"){
					$strRes .= " | ".$key.": ".urldecode($value);
				}else{
					$strRes .= " | ".$key.": ".$value;
				}
			}
			CEventLog::Add(array(
			 "SEVERITY" => "SECURITY",
			 "AUDIT_TYPE_ID" => "PAYMENT",
			 "MODULE_ID" => "main",
			 "ITEM_ID" => "payonline",
			 "DESCRIPTION" => $strRes,
			));
			//Add payment in table
			$arFields = array();
			$arFields["sum"] = $_REQUEST["OutSum"];
			$arFields["currency"] = $_REQUEST["PaymentCurrency"];
			$arFields["order_number"] = $_REQUEST["OrderId"];
			$arFields["order_date"] = date("d.m.Y H:i:s", strtotime($_REQUEST["DateTime"]));
			$arFields["status"] = 'PAID';
			$arFields["1c_status"] = 'NEW';
			$arFields["transaction_id"] = $_REQUEST["TransactionID"];
			$arFields["ancillary"] = $strRes;
			$ID = OnlineBookingSupport::db_add('ob_gotech_payments', $arFields);
			$arParams["PAYMENT_ID"] = $ID;
		}
		$hotelID = "";
		$HotelCode1C = "";
		if(!$arParams["HOTEL_CODE"]){
			$hotelID = COption::GetOptionInt('gotech.hotelonline', 'CHOOSEN_HOTEL_ID');
			if(!$hotelID){
				$filter = array("IBLOCK_ID" => $iblock_id_hotel);
			}else{
				$filter = array("IBLOCK_ID" => $iblock_id_hotel, "ID" => $hotelID);
			}
		}else{
			$HotelCode1C = $arParams["HOTEL_CODE"];
			$filter = array("IBLOCK_ID" => $iblock_id_hotel, "PROPERTY_HOTEL_CODE" => $arParams["HOTEL_CODE"]);
		}

		$hotels = CIBlockElement::GetList(
			array(),
			$filter,
			false,
			false,
			array(
				'ID',
				'PROPERTY_HOTEL_CODE',
				'PROPERTY_ADDRESS_WEB_SERVICE',
				'PROPERTY_HOTEL_OUTPUT_CODE',
				"PROPERTY_BONUS_SYSTEM_ENABLE",
				"PROPERTY_BONUS_SYSTEM_WEB_ADDRESS",
				"PROPERTY_BONUS_SYSTEM_HOTEL_TOKEN",
				"PROPERTY_SOAP_LOGIN",
				"PROPERTY_SOAP_PASSWORD",
			)
		);
		$hotelID = "";
		$bonusesWebAddress = "";
		$bonusesHotelToken = "";
		if($hotel = $hotels->GetNext()) {
			$hotelID = $hotel["ID"];
			//????? ???-??????? - AddressWebservice
			if(strlen($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]))
				$AddressWebservice = trim($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]);
			else $AddressWebservice = trim(COption::GetOptionString('gotech.hotelonline', 'AddressWebservice'));
			//??? ??????? ??????? - OutputCode
			if(strlen($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]))
				$OutputCode = trim($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]);
			else $OutputCode = trim(COption::GetOptionString('gotech.hotelonline', 'OutputCode'));
			//??? ????????? - HotelCode
			if(!empty($hotelID) && strlen($hotel["PROPERTY_HOTEL_CODE_VALUE"]))
				$HotelCode1C = trim($hotel["PROPERTY_HOTEL_CODE_VALUE"]);
			if ($hotel['PROPERTY_BONUS_SYSTEM_ENABLE_VALUE']) {
				$bonusesWebAddress = $hotel['PROPERTY_BONUS_SYSTEM_WEB_ADDRESS_VALUE'] . "/";
				$bonusesHotelToken = $hotel['PROPERTY_BONUS_SYSTEM_HOTEL_TOKEN_VALUE'];
			}
			if(!empty($hotel["PROPERTY_SOAP_LOGIN_VALUE"]))
				$SOAP_LOGIN = trim($hotel["PROPERTY_SOAP_LOGIN_VALUE"]);
			else
				$SOAP_LOGIN = "";
			if(!empty($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]))
				$SOAP_PASSWORD = trim($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]);
			else
				$SOAP_PASSWORD = "";
		}

		$arResult["HOTEL"] = $hotelID;

		$isExpress = false;
		if(CModule::IncludeModule("gotech.expresscheckin")){
			$OnlineBookingSupport = new OnlineBookingSupport();
			$isExpress = $OnlineBookingSupport->checkVersion('1.1.0');
		}

		$currency = 643;
		if(isset($arParams["CURRENCY"]) && !empty($arParams["CURRENCY"]))
			if($arParams["CURRENCY"] == "840" || $arParams["CURRENCY"] == "USD")
				$currency = 840;
			elseif($arParams["CURRENCY"] == "978" || $arParams["CURRENCY"] == "EUR")
				$currency = 978;
      elseif($arParams["CURRENCY"] == "417" || $arParams["CURRENCY"] == "KGS")
        $currency = 417;
			else
				$currency = 643;

		//$paymentMethod = COption::GetOptionString( 'gotech.hotelonline', 'paymentMethod', 'CC');
    if ($arParams["PAYMENT_METHOD"]) {
      $paymentMethod = $arParams["PAYMENT_METHOD"];
    }
		if(!$paymentMethod){
			//$paymentMethod = "CC";
			$paymentMethod = "online";
		}

		$ExternalPaymentData = "";
		$ApprovalCode = "";
		$remarks = "";
		if(isset($arParams["PAYMENT_SYSTEM"]) && !empty($arParams["PAYMENT_SYSTEM"])){
			$remarks = $arParams["PAYMENT_SYSTEM"];
		}
		if(isset($arParams["PAYMENT_DATA"]) && !empty($arParams["PAYMENT_DATA"])){
			$ExternalPaymentData = array(
					"CardNumber" => $arParams["PAYMENT_DATA"]["CardNumber"]
					,"OrderNumber" => $arParams["PAYMENT_DATA"]["OrderNumber"]
					,"CardHolder" => $arParams["PAYMENT_DATA"]["CardHolder"]
					,"Date" => OnlineBookingSupport::getDateFormat($arParams["PAYMENT_DATA"]["Date"])
					,"ExternalPaymentCode" => $arParams["PAYMENT_DATA"]["ExternalPaymentCode"]
			);
			$ApprovalCode = $arParams["PAYMENT_DATA"]["ApprovalCode"];
		}

		$query = array(
			"ReservationCode" => "",
			"GroupCode" => $arParams["ORDER_ID"],
			"ClientCode" => "",
			"PayerName" => "",
			"PaymentMethod" => $paymentMethod,
			"Sum" => $arParams["OUT_SUM"],
			"Currency" => $currency,
			"PaymentSection" => intval(COption::GetOptionString( 'gotech.hotelonline', 'paymentSection', 0)),
			"Hotel" => $HotelCode1C,
			"ExternalSystemCode" => $OutputCode,
			"ReferenceNumber" => "",
			"AuthorizationCode" => $ApprovalCode,
			"Remarks" => $remarks,
			"ExternalPaymentData" => $ExternalPaymentData
		);
		$arResult["HOTEL_CODE"] = $HotelCode1C;
		$strRes = "";
		foreach ( $query as $key => $value ) {
			if($key == "ExternalPaymentData" && is_array($value)){
				$strRes .= " | ".$key.": [";
				foreach ( $value as $inkey => $invalue ) {
					$strRes .= " | ".$inkey.": ".$invalue;
				}
				$strRes .= "]";
			}else{
				$strRes .= " | ".$key.": ".$value;
			}
		}
		CEventLog::Add(array(
		 "SEVERITY" => "SECURITY",
		 "AUDIT_TYPE_ID" => "PAYMENT",
		 "MODULE_ID" => "main",
		 "ITEM_ID" => "1C_start",
		 "DESCRIPTION" => $strRes,
		));
		ini_set("soap.wsdl_cache_enabled", intval(COption::GetOptionString('gotech.hotelonline', 'SOAPCache', 0)));
		$soap_params = array('trace' => 1);
		if (!empty($SOAP_LOGIN) && !empty($SOAP_PASSWORD)) {
			$soap_params['login'] = $SOAP_LOGIN;
			$soap_params['password'] = $SOAP_PASSWORD;
		}
		$soapclient = new SoapClient(trim($AddressWebservice), $soap_params); // with auth
		$result = $soapclient->WriteGuestGroupPaymentExt($query);
		CEventLog::Add(array(
		 "SEVERITY" => "SECURITY",
		 "AUDIT_TYPE_ID" => "PAYMENT",
		 "MODULE_ID" => "main",
		 "ITEM_ID" => "1C_end",
		 "DESCRIPTION" => "OrderID: ".$arParams["ORDER_ID"]."; Sum: ".$arParams["OUT_SUM"]."; Error: ".$result->return->ErrorDescription,
		));

		if(!empty($result->return->ErrorDescription)) {
      if ($arParams["ALFABANK_REFERENCE"]) {
        $arFields = array(
          "PMS_status"=> "error"
        ,"PMS_error"=> $result->return->ErrorDescription
        ,"PMS_time"=> date("d.m.Y H:i:s")
        );

        //Update payment in table
        $res = OnlineBookingSupport::db_update('ob_credit_orders', $arFields, "WHERE reference='".$arParams["ALFABANK_REFERENCE"]."'");
      }

      if ($arParams["BONUSES_TRANS_ID"] && !empty($arParams["BONUSES_TRANS_ID"]) && $arParams["PAYMENT_SYSTEM"] == 'Bonuses') {
        $arFields = array(
          "status"=> "error",
          "error_text"=> $result->return->ErrorDescription
        );

        //Update bonuses payment in table
        $res = OnlineBookingSupport::db_update('ob_gotech_bonuses_payments', $arFields, "WHERE trans_id='".$arParams["BONUSES_TRANS_ID"]."'");
      }

			$arResult["ERROR"] = $result->return->ErrorDescription;
			ShowError($arResult["ERROR"]);
		}
		else {
      if ($arParams["ALFABANK_REFERENCE"]) {
        $arFields = array(
          "PMS_status"=> "success"
        ,"PMS_error"=> ""
        ,"PMS_time"=> date("d.m.Y H:i:s")
        );

        //Update payment in table
        $res = OnlineBookingSupport::db_update('ob_gotech_payments', $arFields, "WHERE reference='".$arParams["ALFABANK_REFERENCE"]."'");
      }

      if ($arParams["BONUSES_SUM"] && !empty($arParams["BONUSES_SUM"]) && $arParams["BONUSES_CARD"] && !empty($arParams["BONUSES_CARD"]) && $arParams["PAYMENT_SYSTEM"] != 'Bonuses') {
        $ch = curl_init($bonusesWebAddress . "StartBonusesPayment/");

        $jsonData = array(
          'Token' => $bonusesHotelToken,
          'Card' => $arParams["BONUSES_CARD"],
          'Author' => '1CBITRIX',
          'GuestGroup' => $arParams["ORDER_ID"],
          'Amount' => $arParams["BONUSES_SUM"],
          'Source' => $_SERVER["SERVER_NAME"],
        );

        $jsonDataEncoded = json_encode($jsonData);

        CEventLog::Add(array(
          "SEVERITY" => "SECURITY",
          "AUDIT_TYPE_ID" => "PAYMENT",
          "MODULE_ID" => "main",
          "ITEM_ID" => "StartBonusesPayment",
          "DESCRIPTION" => "Web: " . $bonusesWebAddress . "StartBonusesPayment; " . $jsonDataEncoded,
        ));

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $output = curl_exec($ch);
        curl_close($ch);

        $output = json_decode($output);
        if ($output->Success) {
          $bonusesTransactionID = $output->TransactionID;

          $ExternalPaymentData = array(
            "ExternalPaymentCode" => $bonusesTransactionID
          );

          $query = array(
            "ReservationCode" => "",
            "GroupCode" => $arParams["ORDER_ID"],
            "ClientCode" => "",
            "PayerName" => "",
            "PaymentMethod" => "bonuses",
            "Sum" => $arParams["BONUSES_SUM"],
            "Currency" => $currency,
            "PaymentSection" => intval(COption::GetOptionString( 'gotech.hotelonline', 'paymentSection', 0)),
            "Hotel" => $HotelCode1C,
            "ExternalSystemCode" => $OutputCode,
            "ReferenceNumber" => "",
            "AuthorizationCode" => $ApprovalCode,
            "Remarks" => "",
            "ExternalPaymentData" => $ExternalPaymentData
          );
          $arResult["HOTEL_CODE"] = $HotelCode1C;
          $strRes = "";
          foreach ( $query as $key => $value ) {
            if($key == "ExternalPaymentData" && is_array($value)){
              $strRes .= " | ".$key.": [";
              foreach ( $value as $inkey => $invalue ) {
                $strRes .= " | ".$inkey.": ".$invalue;
              }
              $strRes .= "]";
            }else{
              $strRes .= " | ".$key.": ".$value;
            }
          }
          CEventLog::Add(array(
            "SEVERITY" => "SECURITY",
            "AUDIT_TYPE_ID" => "PAYMENT",
            "MODULE_ID" => "main",
            "ITEM_ID" => "1C_bonuses_start",
            "DESCRIPTION" => $strRes,
          ));
          $result = $soapclient->WriteGuestGroupPaymentExt($query);
          CEventLog::Add(array(
            "SEVERITY" => "SECURITY",
            "AUDIT_TYPE_ID" => "PAYMENT",
            "MODULE_ID" => "main",
            "ITEM_ID" => "1C_bonuses_end",
            "DESCRIPTION" => "OrderID: ".$arParams["ORDER_ID"]."; Sum: ".$arParams["BONUSES_SUM"]."; Error: ".$result->return->ErrorDescription,
          ));

        } else {
          $error = $output->Errors[0];

          CEventLog::Add(array(
            "SEVERITY" => "SECURITY",
            "AUDIT_TYPE_ID" => "PAYMENT",
            "MODULE_ID" => "main",
            "ITEM_ID" => "1C_bonuses",
            "DESCRIPTION" => "WebAddress:" . $bonusesWebAddress . " Error: " . print_r($output, true)
          ));
        }
      }

      if ($arParams["BONUSES_TRANS_ID"] && !empty($arParams["BONUSES_TRANS_ID"]) && $arParams["PAYMENT_SYSTEM"] == 'Bonuses') {
        $arFields = array(
          "status"=> "ok"
        );

        //Update bonuses payment in table
        $res = OnlineBookingSupport::db_update('ob_gotech_bonuses_payments', $arFields, "WHERE trans_id='".$arParams["BONUSES_TRANS_ID"]."'");
      }

			$arResult["SUCCESS"] = GetMessage("NUMBER_OF_RESERVATION").$arParams["ORDER_ID"].GetMessage("YOU_ID").$result->return->PaymentNumber.".";
			if($isExpress){
				$qrUUID = $result->return->UUID;
				if(isset($qrUUID) && !empty($qrUUID) && isset($result->return->Balance) && $result->return->Balance == 0){
					include($_SERVER['DOCUMENT_ROOT'].COption::GetOptionString('gotech.expresscheckin', 'PATH_TO_FOLDER').'tools/phpqrcode/qrlib.php');
					if (class_exists('QRcode')) {
						$qrCodeAddPath = COption::GetOptionString('gotech.expresscheckin', 'PATH_TO_QR_CODE');
						$qrCodePath = $_SERVER["DOCUMENT_ROOT"].$qrCodeAddPath;
						$qrCodeFileName = $qrUUID.'.png';
						$qrCodeUUID = $qrUUID;
						// outputs image directly into browser, as PNG stream
						QRcode::png($qrCodeUUID, $qrCodePath.$qrCodeFileName, 4, 4);

						if(isset($arParams["HOTEL_CODE"])){
							$hotelCode = $arParams["HOTEL_CODE"];
						}else{
							$hotelCode = "";
						}
						//send E-Mail
						$query = array(
							"HotelCode" => $hotelCode,
							"GroupCode" => $arParams["ORDER_ID"],
							"ExpressCheckInPath" => OnlineBookingSupport::getProtocol().$_SERVER["HTTP_HOST"].COption::GetOptionString('gotech.expresscheckin', 'PATH_TO_FOLDER'),
							"QRCodePath" => OnlineBookingSupport::getProtocol().$_SERVER["HTTP_HOST"].$qrCodeAddPath,
							"QRCodeFileName" => $qrCodeFileName,
							"IsExpress" => $isExpress,
							"ExtHotelID" => $hotelID
						);
						$result = $soapclient->SendExpressCheckInMessage($query);
						if(file_exists($qrCodePath.$qrCodeFileName)) unlink($qrCodePath.$qrCodeFileName);
					}
				}
			}
		}
		if(isset($arParams["PAYMENT_ID"]) && !empty($arParams["PAYMENT_ID"])){
			$arFields = array(
				"1c_status"=> empty($result->return->ErrorDescription)?"'OK'":"'FAIL'"
				,"1c_payment_code"=>"'".$result->return->PaymentNumber."'"
				,"1c_payment_date"=>"'".date("YmdHis")."'"
				,"1c_group_code"=>"'".$arParams["ORDER_ID"]."'"
				,"error_text"=>"'".$result->return->ErrorDescription."'"
			);
			//Update payment in table
			$res = OnlineBookingSupport::db_update('ob_gotech_payments', $arFields, "WHERE id=".$arParams["PAYMENT_ID"]);
		}else{
			$arFields = array(
				"status"=> "NOT_PAID"
				,"sum"=>$arParams["OUT_SUM"]
				,"currency"=>$arParams["CURRENCY"]
				,"1c_status"=> empty($result->return->ErrorDescription)?"OK":"FAIL"
				,"1c_payment_code"=>$result->return->PaymentNumber
				,"1c_payment_date"=>date("d.m.Y H:i:s")
				,"1c_group_code"=>$arParams["ORDER_ID"]
				,"error_text"=>$result->return->ErrorDescription
			);
			//Add payment in table
			$ID = OnlineBookingSupport::db_add('ob_gotech_payments', $arFields);
		}
		if(isset($arParams["SCRIPT_NAME"]) && !empty($arParams["SCRIPT_NAME"]) && empty($result->return->ErrorDescription)){
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/gotech.hotelonline/functions/PlatronIO.php");
			$PlatronIO = new PlatronIO();
			$strScriptName = $arParams["SCRIPT_NAME"];
			$strSecretKey = $arParams["SECRET_KEY"];
			$strSalt = $arParams["SALT"];
			$PlatronIO->makeResponse($strScriptName, $strSecretKey, "ok",
				"Order payed",$strSalt);

			unset($PlatronIO);
		}
		if(isset($arParams["IS_YANDEX"]) && $arParams["IS_YANDEX"] == true){
			$head = "paymentAvisoResponse";
			$techMessage = "";
			if(!empty($result->return->ErrorDescription)){
				$code = "1000";
				$techMessage = "������ ������ ������.";
			}else{
				$code = "0";
			}

			$dateISO = date("Y-m-d\TH:i:s").substr(date("O"), 0, 3).":".substr(date("O"), -2, 2);
			header("Content-Type: text/xml");
			header("Pragma: no-cache");
			$text = "<"."?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n";
			$shopId = COption::GetOptionString('gotech.hotelonline', 'yandex-SHOP_ID');
			$invoiceId = $arParams["YANDEX_INVOICE_ID"];

			if (strlen($head) > 0) // for common-HTTP 3.0. Will be empty if action is not supported yet or payment is not correct
			{
				$text .= "<".$head." performedDatetime=\"".$dateISO."\"";
				if (strlen($techMessage) > 0)
					$text .= " code=\"".$code."\" shopId=\"".$shopId."\" invoiceId=\"".$invoiceId."\" techMessage=\"".$techMessage."\"/>";
				else
					$text .= " code=\"".$code."\" shopId=\"".$shopId."\" invoiceId=\"".$invoiceId."\"/>";
			}

			CEventLog::Add(array(
			    "SEVERITY" => "SECURITY",
			    "AUDIT_TYPE_ID" => "PAYMENT",
			    "MODULE_ID" => "main",
			    "ITEM_ID" => "yandex_xml",
			    "DESCRIPTION" => $text,
			));

			echo $text;
		}elseif(isset($arParams["IS_GAZPROMBANK"]) && $arParams["IS_GAZPROMBANK"] == true){
			if(!empty($result->return->ErrorDescription)){
				$str = "<register-payment-response>".
				 " <result>".
				 " <code>2</code>".
				 " <desc>Unable to register payment result in merchant</desc>".
				 " </result>".
				 "</register-payment-response>";
			}else{
				$str = "<register-payment-response>".
				 " <result>".
				 " <code>1</code>".
				 " <desc>Payment result successfully registered by the merchant</desc>".
				 " </result>".
				 "</register-payment-response>";
			}

			CEventLog::Add(array(
				"SEVERITY" => "SECURITY",
				"AUDIT_TYPE_ID" => "PAYMENT",
				"MODULE_ID" => "main",
				"ITEM_ID" => "gazprombank_return_string",
				"DESCRIPTION" => $str,
			));

			echo $str;
    }elseif(isset($arParams["NO_REDIRECT"]) && $arParams["NO_REDIRECT"] == true) {
    }else{
			LocalRedirect(OnlineBookingSupport::getProtocol().$_SERVER["SERVER_NAME"].COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')."my.php?hotel_code=$hotelID&uuid=".$arResult["UUID"]);
		}
	}
}
?>
