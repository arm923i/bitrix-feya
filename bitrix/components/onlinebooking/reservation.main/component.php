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
	unset($_SESSION["AvailableRooms"]);
	unset($_SESSION["ReservationConditions"]);
	unset($_SESSION["PeriodFrom"]);
	unset($_SESSION["PeriodTo"]);
	unset($_SESSION["Night"]);
	unset($_SESSION["Adults"]);
	unset($_SESSION["Children"]);
	unset($_SESSION["NUMBER"]);
	unset($_SESSION["NUMBER_RES"]);
	unset($_SESSION["search_email"]);
	unset($_SESSION["search_phone"]);
	unset($_SESSION["search_reservation"]);
	unset($_SESSION["promo_code"]);
	unset($_SESSION["NUMBERS_BOOKING"]);
	unset($_SESSION["NUMBERS_BOOKED"]);
	$iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
	$hotels = CIBlockElement::GetList(
		array(),
		array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y"),
		false,
		false,
		array("ID", "NAME", "PROPERTY_HOTEL_NAME_EN")
	);
	while($res = $hotels->GetNext())
		if(OnlineBookingSupport::getLanguage() == "ru")
			$arResult["HOTELS"][] = array(
				"ID" => $res["ID"],
				"HREF" => $APPLICATION->GetCurPageParam("hotel=".$res["ID"]),
				"NAME" => $res["NAME"]			
			);
		elseif(OnlineBookingSupport::getLanguage() == "en")
			$arResult["HOTELS"][] = array(
				"ID" => $res["ID"],
				"HREF" => $APPLICATION->GetCurPageParam("hotel=".$res["ID"]),
				"NAME" => $res["PROPERTY_HOTEL_NAME_EN_VALUE"]			
			);
	$this->IncludeComponentTemplate();	
}
?>