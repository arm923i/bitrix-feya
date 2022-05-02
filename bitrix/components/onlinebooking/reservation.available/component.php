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
	$arResult["language"] = $_REQUEST["language"] ? $_REQUEST["language"]:OnlineBookingSupport::getLanguage();
	if($this->includeComponentLang("", $arResult["language"]) == NULL){
		__IncludeLang(dirname(__FILE__)."/lang/".$arResult["language"]."/component.php");
	}
	
	if(isset($arParams) && isset($arParams["hotel_id"]) && !empty($arParams["hotel_id"]) && isset($arParams["PeriodFrom"]) && !empty($arParams["PeriodFrom"]) && isset($arParams["PeriodTo"]) && !empty($arParams["PeriodTo"])){
		//Period from
		$periodFrom = $arParams["PeriodFrom"];
		$arPeriodFrom = explode(".", $arParams['PeriodFrom']);
		$arResult["PeriodFrom"] = $arPeriodFrom[2].".".$arPeriodFrom[1].".".$arPeriodFrom[0];
		//Period to
		$periodTo = $arParams["PeriodTo"];
		$arPeriodTo = explode(".", $arParams['PeriodTo']);
		$arResult["PeriodTo"] = $arPeriodTo[2].".".$arPeriodTo[1].".".$arPeriodTo[0];

		$hotelCode = "";
		$outputCode = "";
		$roomQuota = "";
		$roomRate = "";
		$WSDL = "";
		$arResult['ShowCalendar'] = "";
		if(!empty($arParams["hotel_id"]))
		{
			$res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID'), "ID" => $arParams["hotel_id"]), false, false,
			array("PROPERTY_HOTEL_CODE", "PROPERTY_HOTEL_OUTPUT_CODE", "PROPERTY_HOTEL_ROOM_RATE", "PROPERTY_HOTEL_ROOM_QUOTA", "PROPERTY_ADDRESS_WEB_SERVICE", "PROPERTY_SHOW_CALENDAR_WITH_AVAILABLE_DATES", "PROPERTY_SOAP_LOGIN", "PROPERTY_SOAP_PASSWORD"));
			if($hotel = $res->GetNext()) {
				if(!empty($hotel["PROPERTY_HOTEL_CODE_VALUE"])){
					$hotelCode = $hotel["PROPERTY_HOTEL_CODE_VALUE"];
				}else $hotelCode = "";
				if(!empty($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"])){
					$outputCode = $hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"];
				}else $outputCode = "";
				if(!empty($hotel["PROPERTY_HOTEL_ROOM_RATE_VALUE"])){
					$roomRate = $hotel["PROPERTY_HOTEL_ROOM_RATE_VALUE"];
				}else $roomRate = "";
				if(!empty($hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"])){
					$roomQuota = $hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"];
				}else $roomQuota = "";
				if(!empty($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"])){
					$WSDL = $hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"];
				}else $WSDL = "";
				if(!empty($hotel["PROPERTY_SHOW_CALENDAR_WITH_AVAILABLE_DATES_VALUE"])){
					$arResult['ShowCalendar'] = $hotel["PROPERTY_SHOW_CALENDAR_WITH_AVAILABLE_DATES_VALUE"];
				}else $arResult['ShowCalendar'] = "";
                if(!empty($hotel["PROPERTY_SOAP_LOGIN_VALUE"]))
                    $SOAP_LOGIN = trim($hotel["PROPERTY_SOAP_LOGIN_VALUE"]);
                else
                    $SOAP_LOGIN = "";
                if(!empty($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]))
                    $SOAP_PASSWORD = trim($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]);
                else
                    $SOAP_PASSWORD = "";
			}
		}
        $soap_params = array('trace' => 1);
        if (!empty($SOAP_LOGIN) && !empty($SOAP_PASSWORD)) {
            $soap_params['login'] = $SOAP_LOGIN;
            $soap_params['password'] = $SOAP_PASSWORD;
        }
		$soapclient = new SoapClient($WSDL, $soap_params); // with auth
		$roomsDataRequest = array(							
			"Hotel" => $hotelCode,
			"RoomType" => "",
			"PeriodFrom" => str_replace(" ", "T", date("Y-m-d H:i:s", strtotime(date("d.m.Y")))),
			"PeriodTo" => str_replace(" ", "T", date("Y-m-d H:i:s", strtotime(date("d.m.Y", time()+86400*200)))),
			"RoomRate" => $roomRate,
			"RoomQuota" => $roomQuota,
			"OutputRoomsVacant" => "true",
			"OutputRoomsRemains" => "true",
			"ExternalSystemCode" => $outputCode
		);
		$result = $soapclient->GetRoomInventoryBalance($roomsDataRequest);
		$r = $result->return;
		$rows = $r->RoomInventoryBalanceRow;
		$arResult["AvailableRows"] = $rows;
	}else{
		echo "Error!";
	}
	$this->IncludeComponentTemplate();
}
?>