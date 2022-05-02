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
	$APPLICATION->IncludeComponent("onlinebooking:reservation.header", "");
	if($this->includeComponentLang("", OnlineBookingSupport::getLanguage()) == NULL){
		__IncludeLang(dirname(__FILE__)."/lang/".OnlineBookingSupport::getLanguage()."/component.php");
	}
	$APPLICATION->SetTitle(GetMessage("TITLE_MODULE"));
	
	$arResult["HOTEL"] = $arParams["HOTEL_CODE"]?$arParams["HOTEL_CODE"]:COption::GetOptionInt('gotech.hotelonline', 'CHOOSEN_HOTEL_ID');
	
	$arResult["UUID"] = "";
	if(isset($_REQUEST["uuid"]) && !empty($_REQUEST["uuid"])){
		$arResult["UUID"] = $_REQUEST["uuid"];
	}elseif(isset($arParams["UUID"]) && !empty($arParams["UUID"])){
		$arResult["UUID"] = $arParams["UUID"];
	}
	
	$iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
	if(!$arParams["HOTEL_CODE"])
		$filter = array("IBLOCK_ID" => $iblock_id_hotel);
	else
		$filter = array("IBLOCK_ID" => $iblock_id_hotel, "PROPERTY_HOTEL_CODE" => $arParams["HOTEL_CODE"]);
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
			'PROPERTY_DO_FAIL_PAY_ANNULATION',
            'PROPERTY_SOAP_LOGIN',
            'PROPERTY_SOAP_PASSWORD'
		)
	);
	if($hotel = $hotels->GetNext()) {
		if(!empty($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]))
			$WSDL = trim($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]);
		else 
			$WSDL = trim(COption::GetOptionString('gotech.hotelonline', 'AddressWebservice'));
		if(!empty($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]))
			$OutputCode = trim($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]);
		else 
			$OutputCode = trim(COption::GetOptionString('gotech.hotelonline', 'OutputCode'));
		if(!empty($hotel["PROPERTY_DO_FAIL_PAY_ANNULATION_VALUE"]) && $hotel["PROPERTY_DO_FAIL_PAY_ANNULATION_VALUE"]=='Yes')
			$doAnnulation = True;
		else 
			$doAnnulation = False;
        if(!empty($hotel["PROPERTY_SOAP_LOGIN_VALUE"]))
            $SOAP_LOGIN = trim($hotel["PROPERTY_SOAP_LOGIN_VALUE"]);
        else
            $SOAP_LOGIN = "";
        if(!empty($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]))
            $SOAP_PASSWORD = trim($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]);
        else
            $SOAP_PASSWORD = "";
	}

	$reason = "CanceledPayment";
  if ($arParams["REASON"]) {
    $reason = $arParams["REASON"];
  }
	
	if($doAnnulation){
    $query = array(
      "GuestGroupCode" => $arParams["ORDER_ID"],
      "ExternalSystemCode" => $OutputCode,
      "LanguageCode" => OnlineBookingSupport::getLanguage(),
      "Reason" => $reason
    );

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
      "ITEM_ID" => "1C_fail_pay_start",
      "DESCRIPTION" => $strRes,
    ));

    $soap_params = array('trace' => 1);
    if (!empty($SOAP_LOGIN) && !empty($SOAP_PASSWORD)) {
        $soap_params['login'] = $SOAP_LOGIN;
        $soap_params['password'] = $SOAP_PASSWORD;
    }
    $soapclient = new SoapClient(trim($WSDL), $soap_params); // with auth
    $result = $soapclient->CancelGroupReservation($query);
    CEventLog::Add(array(
      "SEVERITY" => "SECURITY",
      "AUDIT_TYPE_ID" => "PAYMENT",
      "MODULE_ID" => "main",
      "ITEM_ID" => "1C_fail_pay_end",
      "DESCRIPTION" => "OrderID: ".$arParams["ORDER_ID"]."; Sum: ".$arParams["OUT_SUM"]."; Error: ".$result->return,
    ));
		if(empty($result->return)) {
      $arResult["SUCCESS"] = GetMessage("DELETE_RESERVATION");

      if ($arParams["ALFABANK_REFERENCE"]) {
        $arFields = array(
          "PMS_status"=> "success"
        ,"PMS_error"=> ""
        ,"PMS_time"=> date("d.m.Y H:i:s")
        );

        //Update payment in table
        $res = OnlineBookingSupport::db_update('ob_credit_orders', $arFields, "WHERE reference='".$arParams["ALFABANK_REFERENCE"]."'");
      }
    } else {
      $arResult["ERROR"] = $result->return;

      if ($arParams["ALFABANK_REFERENCE"]) {
        $arFields = array(
          "PMS_status"=> "error"
        ,"PMS_error"=> $result->return
        ,"PMS_time"=> date("d.m.Y H:i:s")
        );

        //Update payment in table
        $res = OnlineBookingSupport::db_update('ob_credit_orders', $arFields, "WHERE reference='".$arParams["ALFABANK_REFERENCE"]."'");
      }
    }
	}
	if ($arResult["UUID"] && $arParams["NEED_REDIRECT"]) {
    LocalRedirect(OnlineBookingSupport::getProtocol().$_SERVER["SERVER_NAME"].COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')."my.php?hotel_code=".$arResult["HOTEL"]."&uuid=".$arResult["UUID"]);
  } else if(isset($arResult["SUCCESS"]) || isset($arResult["ERROR"])){
		$this->IncludeComponentTemplate();
	}
}
?>