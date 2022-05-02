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
	$APPLICATION->SetTitle(GetMessage("TITLE"));
	
	if(empty($arParams["NUM_RESERVATION"]))
		$arResult["ERROR"] = GetMessage("NUMBER_RESERVATION_ALWAYS");
	elseif(empty($arParams["EMAIL"]) && empty($arParams["PHONE"]) && empty($arParams["LOGIN"]))
		$arResult["ERROR"] = GetMessage("PHONE_EMAIL_ALWAYS");
	if(empty($arResult["ERROR"])) {
		
		
		$hotels = CIBlockElement::GetList(
			array(),
			array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y", "ID" => $arParams["HOTEL_ID"]),
			false,
			false,
			array("ID", "PROPERTY_ADDRESS_WEB_SERVICE", "NAME")
		);
		if($hotel = $hotels->GetNext())
			if(!empty($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]))
				$WSDL = trim($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]);
			else
				$WSDL = trim(COption::GetOptionString('gotech.hotelonline', 'AddressWebservice'));

        $soap_params = array('trace' => 1);
        $soapclient = new SoapClient(trim($WSDL), $soap_params);
		$query = array(
			"EMail" => $arParams["EMAIL"],
			"Phone" => $arParams["PHONE"],
			"Login" => $arParams["LOGIN"],
			"GuestGroupCode" => $arParams["NUM_RESERVATION"],
			"ExternalSystemCode" => '1CBITRIX',
			"Language" => mb_strtoupper(OnlineBookingSupport::getLanguage())
		);			
		$result = $soapclient->GetGroupReservationDetails($query);	
		
		if(!empty($result->return->ErrorDescription))
			$arResult["ERROR"] = $result->return->ErrorDescription;
		else {
			if(is_array($result->return->ExternalReservationStatusRow))
				foreach($result->return->ExternalReservationStatusRow as $row_quest) {
					$periodFrom = explode('-', substr($row_quest->CheckInDate, 0, 10));
					$periodTo = explode('-', substr($row_quest->CheckOutDate, 0, 10));					
					$guest[] = array(
						'ReservationStatus' => $row_quest->ReservationStatus,
						'ReservationStatusDescription' => $row_quest->ReservationStatusDescription,
						'GuestFullName' => $row_quest->GuestFullName,
						'RoomType' => $row_quest->RoomType,
						'RoomTypeCode' => $row_quest->RoomTypeCode,
						'AccommodationType' => $row_quest->AccommodationType,
						'AccommodationTypeDescription' => $row_quest->AccommodationTypeDescription,
						'CheckInDate' => $periodFrom[2].'.'.$periodFrom[1].'.'.$periodFrom[0],
						'Duration' => $row_quest->Duration,
						'CheckOutDate' => $periodTo[2].'.'.$periodTo[1].'.'.$periodTo[0],
						'Currency' => $row_quest->Currency,
						'Sum' => $row_quest->Sum
					);
				}
			else {
				$periodFrom = explode('-', substr($result->return->ExternalReservationStatusRow->CheckInDate, 0, 10));
				$periodTo = explode('-', substr($result->return->ExternalReservationStatusRow->CheckOutDate, 0, 10));					
				$guest[] = array(
					'ReservationStatus' => $result->return->ExternalReservationStatusRow->ReservationStatus,
					'ReservationStatusDescription' => $result->return->ExternalReservationStatusRow->ReservationStatusDescription,
					'GuestFullName' => $result->return->ExternalReservationStatusRow->GuestFullName,
					'RoomType' => $result->return->ExternalReservationStatusRow->RoomType,
					'RoomTypeCode' => $result->return->ExternalReservationStatusRow->RoomTypeCode,
					'AccommodationType' => $result->return->ExternalReservationStatusRow->AccommodationType,
					'AccommodationTypeDescription' => $result->return->ExternalReservationStatusRow->AccommodationTypeDescription,
					'CheckInDate' => $periodFrom[2].'.'.$periodFrom[1].'.'.$periodFrom[0],
					'Duration' => $result->return->ExternalReservationStatusRow->Duration,
					'CheckOutDate' => $periodTo[2].'.'.$periodTo[1].'.'.$periodTo[0],
					'Currency' => $result->return->ExternalReservationStatusRow->Currency,
					'Sum' => $result->return->ExternalReservationStatusRow->Sum
				);
			}
			foreach($guest as $gu)
				$period[] = GetMessage("PERIOD_FROM").$gu['CheckInDate'].GetMessage("PERIOD_TO").$gu['CheckOutDate'];
			$period = array_unique($period);
			foreach($period as $date)
				foreach($guest as $ge) 
					if(GetMessage("PERIOD_FROM").$ge['CheckInDate'].GetMessage("PERIOD_TO").$ge['CheckOutDate'] == $date)
						$array[$date][] = $ge;
			$arResult["GUESTS"] = $array;
			$arResult["TOTAL_SUM"] = $result->return->TotalSum.' '.$result->return->Currency;
			$arResult["GUEST_GROUP"] = $result->return->GuestGroup;
			$arResult["HOTEL_NAME"] = $result->return->HotelName;
			$arResult["CONTACT_PERSON"] = $result->return->GuestFullName;
			$arResult["CONTACT_PERSON_PHONE"] = $result->return->GuestPhone;
			$arResult["CONTACT_PERSON_FAX"] = $result->return->GuestFax;
			$arResult["CONTACT_PERSON_EMAIL"] = $result->return->GuestEMail;
			$arResult["today"] = OnlineBookingSupport::getFullDate(OnlineBookingSupport::getLanguage());
		}
	}
	$this->IncludeComponentTemplate();
}
?>