<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) {
	ShowError(GetMessage("IBLOCK_NOT_INCLUDE"));
	return;
}
elseif(!CModule::IncludeModule("gotech.hotelonline")) {
	ShowError(GetMessage("ONLINEBOOKING_MODULE_NOT_INSTALLED"));	
	return;
}
else {
	$language = OnlineBookingSupport::getLanguage();
	if($this->includeComponentLang("", $language) == NULL){
		__IncludeLang(dirname(__FILE__)."/lang/".$language."/component.php");
	}
	if(isset($_REQUEST["email"]) && !empty($_REQUEST["email"]) && isset($_REQUEST["hcode"]) && !empty($_REQUEST["hcode"])){
		$rsUser = CUser::GetByLogin($_REQUEST["email"]);
		if(!$rsUser->Fetch())
		{
			$iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
			$arFilter = array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y", "PROPERTY_HOTEL_CODE" => $_REQUEST["hcode"]);
			$hotels = CIBlockElement::GetList(
				array(),
				$arFilter,
				false,
				false,
				array(
					"ID", 
					"PROPERTY_ADDRESS_WEB_SERVICE", 
					"NAME"
				)
			);
			$WSDL = trim(COption::GetOptionString('gotech.hotelonline', 'AddressWebservice'));
			while($hotel = $hotels->GetNext()) {
				$WSDL = trim($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"])? $hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"] : trim(COption::GetOptionString('gotech.hotelonline', 'AddressWebservice'));
			}
			$q = array(
					"HotelCode" => trim($_REQUEST["hcode"]),
					"ExternalSystemCode" => "1CBITRIX",
					"EMail" => trim($_REQUEST["email"])
			);
            $soap_params = array('trace' => 1);
            $soapclient = new SoapClient(trim($WSDL), $soap_params);
			$result = $soapclient->GetAgentDetails($q);
			if($result->return->ErrorDescription)							
				//$arResult["ERROR"] = $result->return->ErrorDescription;
				$arResult["RESULT"] = $result->return;
			else {
				$arResult["RESULT"] = $result->return;
			}
		} else {
			  $arResult["ERROR"] = GetMessage("USER_ISSET");
		}
	}else{
		$arResult["ERROR"] = GetMessage("WRONG_QUERY");
	}
	
	$this->IncludeComponentTemplate();
}
?>