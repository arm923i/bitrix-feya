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
	$lang = OnlineBookingSupport::getLanguage();
	if($this->includeComponentLang("", $lang) == NULL){
		__IncludeLang(dirname(__FILE__)."/lang/".$lang."/component.php");
	}
	$arSelect = array(
		'ID',
		'NAME',
		'PROPERTY_HOTEL_CODE',
		'PROPERTY_HOTEL_NAME_EN',
		'PROPERTY_HOTEL_ADDRESS_RU',
		'PROPERTY_HOTEL_ADDRESS_EN',
		'PROPERTY_HOTEL_PHONE',
		'PROPERTY_HOTEL_FAX',
		'PROPERTY_HOTEL_MAIL',
		'PROPERTY_HOTEL_WALK_RU',
		'PROPERTY_HOTEL_WALK_EN',
		'PROPERTY_HOTEL_TIME',
		'PROPERTY_ADDRESS_WEB_SERVICE',
		'PROPERTY_DO_ANNULATION_WITH_PREPAYMENT',
		'PROPERTY_SHOW_FREE_CANCEL',
		"PROPERTY_HOTEL_CLIENT_CAN_CHANGE_RESERVATION",
		'PROPERTY_HOTEL_OUTPUT_CODE',
        'PROPERTY_HOURS_ENABLE',
		'PREVIEW_TEXT',
		'DETAIL_TEXT',
		'PROPERTY_SOAP_LOGIN',
		'PROPERTY_SOAP_PASSWORD'
	);
	$choosenHotelID = COption::GetOptionInt('gotech.hotelonline', 'CHOOSEN_HOTEL_ID');
	if(isset($arParams["IBLOCK"])){
		$iblock_id_hotel = $arParams["IBLOCK"];
	}else{
		$iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
	}
	if(isset($arParams["ID_HOTEL"])){
		$arFilter = array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y", "ID" => $arParams["ID_HOTEL"]);
	}elseif(!empty($choosenHotelID)){
		$arFilter = array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y", "ID" => $choosenHotelID);
	}else{
		$arFilter = array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y");
	}
	$hotels = CIBlockElement::GetList(
		array(),
		$arFilter,
		false,
		false,
		$arSelect
	);
	$WSDL = '';
	$OutputCode = '';
	$HotelCode1C = '';
	$arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] = "";
	$arResult["SHOW_FREE_CANCEL"] = "";
	if($hotel = $hotels->GetNext()){
		if($lang == "ru")
			$arResult["hotel_info"] = array(
				"ID" => $hotel["ID"],
				"NAME" => $hotel["~NAME"],
				"HOTEL_ADDRESS" => $hotel["PROPERTY_HOTEL_ADDRESS_RU_VALUE"],
				"HOTEL_PHONE" => $hotel["PROPERTY_HOTEL_PHONE_VALUE"],
				"HOTEL_FAX" => $hotel["PROPERTY_HOTEL_FAX_VALUE"],
				"HOTEL_MAIL" => $hotel["PROPERTY_HOTEL_MAIL_VALUE"],
				"HOTEL_WALK" => $hotel["PROPERTY_HOTEL_WALK_RU_VALUE"],
				"HOTEL_TIME" => $hotel["PROPERTY_HOTEL_TIME_VALUE"],
				"RESERVATIONS_CONDITIONS" => $hotel["PREVIEW_TEXT"],
				"DO_ANNULATION_WITH_PREPAYMENT" => $hotel["PROPERTY_DO_ANNULATION_WITH_PREPAYMENT_VALUE"],
				"SHOW_FREE_CANCEL" => $hotel["PROPERTY_SHOW_FREE_CANCEL_VALUE"]
			);
		elseif($lang == "en")
			$arResult["hotel_info"] = array(
				"ID" => $hotel["ID"],
				"NAME" => $hotel["PROPERTY_HOTEL_NAME_EN_VALUE"],
				"HOTEL_ADDRESS" => $hotel["PROPERTY_HOTEL_ADDRESS_EN_VALUE"],
				"HOTEL_PHONE" => $hotel["PROPERTY_HOTEL_PHONE_VALUE"],
				"HOTEL_FAX" => $hotel["PROPERTY_HOTEL_FAX_VALUE"],
				"HOTEL_MAIL" => $hotel["PROPERTY_HOTEL_MAIL_VALUE"],
				"HOTEL_WALK" => $hotel["PROPERTY_HOTEL_WALK_EN_VALUE"],
				"HOTEL_TIME" => $hotel["PROPERTY_HOTEL_TIME_VALUE"],
				"RESERVATIONS_CONDITIONS" => $hotel["DETAIL_TEXT"],
				"DO_ANNULATION_WITH_PREPAYMENT" => $hotel["PROPERTY_DO_ANNULATION_WITH_PREPAYMENT_VALUE"],
				"SHOW_FREE_CANCEL" => $hotel["PROPERTY_SHOW_FREE_CANCEL_VALUE"]
			);
        $arResult["HOURS_ENABLE"] = $hotel["PROPERTY_HOURS_ENABLE_VALUE"]?true:false;
		$arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] = $hotel["PROPERTY_HOTEL_CLIENT_CAN_CHANGE_RESERVATION_VALUE"];
		$arResult["SHOW_FREE_CANCEL"] = $hotel["PROPERTY_SHOW_FREE_CANCEL_VALUE"];
		$HotelCode1C = $hotel["PROPERTY_HOTEL_CODE_VALUE"];
		$WSDL = $hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"];
		$OutputCode = $hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"];
		if(!empty($hotel["PROPERTY_SOAP_LOGIN_VALUE"]))
			$SOAP_LOGIN = trim($hotel["PROPERTY_SOAP_LOGIN_VALUE"]);
		else
			$SOAP_LOGIN = "";
		if(!empty($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]))
			$SOAP_PASSWORD = trim($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]);
		else
			$SOAP_PASSWORD = "";
	}
	if(isset($_REQUEST["cancel"]) && !empty($_REQUEST["cancel"]) && $_REQUEST["cancel"] == "Y") {
		if(empty($_REQUEST["reservation"]))
			$arResult["ERROR"] = GetMessage("NUMBER_RESERVATION_ALWAYS");
		elseif(empty($_REQUEST["email"]) && empty($_REQUEST["phone"]))
			$arResult["ERROR"] = GetMessage("PHONE_EMAIL_ALWAYS");
		if(empty($arResult["ERROR"])) {
			$soap_params = array('trace' => 1);
			if (!empty($SOAP_LOGIN) && !empty($SOAP_PASSWORD)) {
				$soap_params['login'] = $SOAP_LOGIN;
				$soap_params['password'] = $SOAP_PASSWORD;
			}
			$soapclient = new SoapClient($WSDL, $soap_params); // with auth
			$query = array(
				"Hotel" => $HotelCode1C,
				"GuestGroupCode" => $_REQUEST["reservation"],
				"ExternalSystemCode" => $OutputCode,
				"LanguageCode" => mb_strtoupper($lang),
				"Reason" => "CanceledByUser"
			);
			$result = $soapclient->CancelGroupReservation($query);
			if(empty($result->return))
				$arResult["SUCCESS"] = GetMessage("DELETE_RESERVATION");
			else $arResult["ERROR"] = $result->return;
		}
	}elseif(!empty($_SESSION["NUMBERS_BOOKED"][$arParams["ID_HOTEL"]]["NUMBERS"])) {
		foreach($_SESSION["NUMBERS_BOOKED"][$arParams["ID_HOTEL"]]["NUMBERS"] as $key => $NUMBERS_BOOKING) {
			$arResult["NUMBERS_BOOKING"][$NUMBERS_BOOKING["PeriodFrom"].' - '.$NUMBERS_BOOKING["PeriodTo"].' '.$NUMBERS_BOOKING["visitors"]]["PeriodFrom"] = $NUMBERS_BOOKING["PeriodFrom"];
			$arResult["NUMBERS_BOOKING"][$NUMBERS_BOOKING["PeriodFrom"].' - '.$NUMBERS_BOOKING["PeriodTo"].' '.$NUMBERS_BOOKING["visitors"]]["PeriodTo"] = $NUMBERS_BOOKING["PeriodTo"];
			$arResult["NUMBERS_BOOKING"][$NUMBERS_BOOKING["PeriodFrom"].' - '.$NUMBERS_BOOKING["PeriodTo"].' '.$NUMBERS_BOOKING["visitors"]]["visitors"] = $NUMBERS_BOOKING["visitors"];
			$arResult["NUMBERS_BOOKING"][$NUMBERS_BOOKING["PeriodFrom"].' - '.$NUMBERS_BOOKING["PeriodTo"].' '.$NUMBERS_BOOKING["visitors"]]["NUMBERS"][] = $NUMBERS_BOOKING;
		}
		$arResult["hotel_info"]["RESERVATIONS_CONDITIONS"] = str_replace("\\n", "<br/>", $arResult["hotel_info"]["RESERVATIONS_CONDITIONS"]);
		$arResult["hotel_info"]["RESERVATIONS_CONDITIONS"] = str_replace(array('&lt;', '&gt;'), array('<', '>'), $arResult["hotel_info"]["RESERVATIONS_CONDITIONS"]);
		$arResult["info_order"] = array(
			"phone" => $_SESSION["NUMBERS_BOOKED"]["info"]["phone"],
			"hotel_code" => $_SESSION["NUMBERS_BOOKED"]["info"]["hotel_code"],
			"email" => $_SESSION["NUMBERS_BOOKED"]["info"]["email"],
			"avto_number" => $_SESSION["NUMBERS_BOOKED"]["info"]["avto_number"],
			"additional_wishes" => $_SESSION["NUMBERS_BOOKED"]["info"]["additional_wishes"],
			"TransferDate" => $_SESSION["NUMBERS_BOOKED"]["info"]["TransferDate"],
			"TransferTime" => $_SESSION["NUMBERS_BOOKED"]["info"]["TransferTime"],
			"TransferPlace" => $_SESSION["NUMBERS_BOOKED"]["info"]["TransferPlace"],
			"TransferRemarks" => $_SESSION["NUMBERS_BOOKED"]["info"]["TransferRemarks"],
			"GuestGroup" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->GuestGroup,
      "GuestCode" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->GuestCode,
			"GuestFullName" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->GuestFullName,
      "GuestFirstName" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->GuestFirstName,
      "GuestLastName" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->GuestLastName,
			"GuestPhone" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->GuestPhone,
			"GuestFax" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->GuestFax,
			"GuestEMail" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->GuestEMail,
			"TotalSum" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->TotalSum,
			"TotalSumPresentation" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->TotalSumPresentation,
			"BalanceAmount" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->BalanceAmount,
			"BalanceAmountPresentation" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->BalanceAmountPresentation,
			"FirstDaySum" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->FirstDaySum,
			"FirstDaySumPresentation" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->FirstDaySumPresentation,
			"Currency" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->Currency,
			"CurrencyDescription" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->Currency,
			"CurrencyCode" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->CurrencyCode,
			"ExternalReservationStatusRow" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->ExternalReservationStatusRow,
			"UUID" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->UUID,
			"ReservationConditions" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->ReservationConditions,
			"ReservationConditionsShort" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->ReservationConditionsShort,
			"ReservationConditionsOnline" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->ReservationConditionsOnline,
			"PaymentMethodCodesAllowedOnline" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->PaymentMethodCodesAllowedOnline,
			"CustomerName" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->Customer,
			"CustomerLegacyAddress" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->CustomerLegacyAddress,
			"CustomerTIN" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->CustomerTIN,
			"CustomerKPP" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->CustomerKPP,
			"CustomerEMail" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->CustomerEMail,
			"CustomerPhone" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->CustomerPhone,
			"RoomRateCode" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->RoomRateCode,
			"RoomRateDescription" => $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->RoomRateDescription,
			"CheckDate" => ""
		);
		if($arResult["info_order"]["GuestPhone"] == 'undefined'){
			$arResult["info_order"]["GuestPhone"] = "";
		}
		if($arResult["info_order"]["GuestEMail"] == 'undefined'){
			$arResult["info_order"]["GuestEMail"] = "";
		}
		if($arResult["info_order"]["CurrencyCode"] == 643){
			$arResult["info_order"]["Currency"] = "<span class='gotech_ruble'>a</span>";
		}elseif($arResult["info_order"]["CurrencyCode"] == 840){
			$arResult["info_order"]["Currency"] = '$';
		}elseif($arResult["info_order"]["CurrencyCode"] == 978){
			$arResult["info_order"]["Currency"] = '&euro;';
    }elseif($arResult["info_order"]["CurrencyCode"] == 417){
      $arResult["info_order"]["Currency"] = 'KGS';
		}else{
			$arResult["info_order"]["Currency"] = "<span class='gotech_ruble'>a</span>";
		}
		if($arResult["info_order"]["TotalSum"] == $arResult["info_order"]["BalanceAmount"]){
			$arResult["DO_ANNULATION"] = true;
		}else if($arResult["hotel_info"]["DO_ANNULATION_WITH_PREPAYMENT"]){
			$arResult["DO_ANNULATION"] = true;
		}else{
			$arResult["DO_ANNULATION"] = false;
		}
		if(is_array($_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->ExternalReservationStatusRow))
			$ExternalReservationStatusRows = $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->ExternalReservationStatusRow;
		else
			$ExternalReservationStatusRows[] = $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->ExternalReservationStatusRow;


		$id_iblock_numbers = COption::GetOptionString('gotech.hotelonline', 'NUMBER_IBLOCK_ID');
		$id_hotel_property = COption::GetOptionString('gotech.hotelonline', 'NUMBERHOTEL');
		$id_number_code_property = COption::GetOptionString('gotech.hotelonline', 'NUMBERCODE');
		$id_number_en_name = COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMEEN');
		COption::SetOptionString('gotech.hotelonline', 'NUMBERNAMERU', "NAME");
		if(COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU') == "NAME")
			$id_number_ru_name  = "NAME";
		else
			$id_number_ru_name = "PROPERTY_".COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU');


		$lastPayMethods = "";
		$minCheckInDate = "";
		$arPayMethods = Array();
		foreach($ExternalReservationStatusRows as $g) {
			$roomTypeDescription = $g->RoomTypeDescription;
			if($g->PaymentMethodCodesAllowedOnline != $lastPayMethods) {
				$arPayMethods[] = $g->PaymentMethodCodesAllowedOnline;
			}
			$filter = array(
				"IBLOCK_ID" => $id_iblock_numbers,
				"ACTIVE" => "Y",
				$id_number_code_property => $g->RoomTypeCode,
				$id_hotel_property  => $arResult["hotel_info"]["ID"]
			);
			$arSelect = array(
				$id_hotel_property,
				$id_number_code_property,
				$id_number_en_name,
				$id_number_ru_name
			);

			$dbEl = CIBlockElement::GetList(array(), $filter, false, false, $arSelect);
			if($res = $dbEl->GetNext()) {
				if($lang == "en"){
					$roomTypeDescription = $res[$id_number_en_name."_VALUE"];
				}
				else
					if($id_number_ru_name == "NAME")
						$roomTypeDescription = $res[$id_number_ru_name];
					else
						$roomTypeDescription = $res[$id_number_ru_name.'_VALUE'];
			}

			if ($arResult["HOURS_ENABLE"]) {
                $currCheckInDate = OnlineBookingSupport::getDateFromFormat($g->CheckInDate) . " " . substr($g->CheckInDate, strpos($g->CheckInDate, "T") + 1, 5);
            } else {
                $currCheckInDate = OnlineBookingSupport::getDateFromFormat($g->CheckInDate);
            }
			if(!$minCheckInDate || strtotime($minCheckInDate)>strtotime($currCheckInDate)){
				$minCheckInDate = $currCheckInDate;
			}
            if ($arResult["HOURS_ENABLE"]) {
                $guests[$g->ReservationNumber] = array(
                    "RoomTypeCode" => $g->RoomTypeCode,
                    "RoomTypeDescription" => $roomTypeDescription,
                    "CheckInDate" => $currCheckInDate,
                    "CheckOutDate" => OnlineBookingSupport::getDateFromFormat($g->CheckOutDate) . " " . substr($g->CheckOutDate, strpos($g->CheckOutDate, "T") + 1, 5),
                );
            } else {
                $guests[$g->ReservationNumber] = array(
                    "RoomTypeCode" => $g->RoomTypeCode,
                    "RoomTypeDescription" => $roomTypeDescription,
                    "CheckInDate" => $currCheckInDate,
                    "CheckOutDate" => OnlineBookingSupport::getDateFromFormat($g->CheckOutDate)
                );
            }
			$lastPayMethods = $g->PaymentMethodCodesAllowedOnline;
		}

		if(isset($_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->CheckDate) && !empty($_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->CheckDate) && $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->CheckDate != '0001-01-01'){
			$arResult["info_order"]["CheckDate"] = OnlineBookingSupport::getDateFromFormat($_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]->CheckDate);
			if($minCheckInDate && strtotime($minCheckInDate)<strtotime($arResult["info_order"]["CheckDate"])){
				$arResult["info_order"]["CheckDate"] = $minCheckInDate;
			}
		}

		$lastSplits = NULL;
		foreach($arPayMethods as $pm) {

			$splits = explode(",", $pm);
			$splits = preg_replace('/\s+/', '', $splits);
			if($lastSplits == NULL){
				$lastSplits = $splits;
				$resultSplits = $splits;
			}else{
				$resultSplits = array_intersect($splits,$lastSplits);
			}
		}
		foreach($guests as $key => $value) {
			foreach($ExternalReservationStatusRows as $guest) {
                if($key == $guest->ReservationNumber &&
                    $value["RoomTypeCode"] == $guest->RoomTypeCode &&
                    ($value["CheckInDate"] == OnlineBookingSupport::getDateFromFormat($guest->CheckInDate) ||
                        ($arResult["HOURS_ENABLE"] && $value["CheckInDate"] == OnlineBookingSupport::getDateFromFormat($guest->CheckInDate) . " " . substr($g->CheckInDate, strpos($g->CheckInDate, "T") + 1, 5))
                    ) &&
                    ($value["CheckOutDate"] == OnlineBookingSupport::getDateFromFormat($guest->CheckOutDate) ||
                        ($arResult["HOURS_ENABLE"] && $value["CheckOutDate"] == OnlineBookingSupport::getDateFromFormat($guest->CheckOutDate) . " " . substr($g->CheckOutDate, strpos($g->CheckOutDate, "T") + 1, 5)))
                ) {
					$guests[$key]["Guests"][] = $guest->GuestFullName;
					$guests[$key]["Cost"] = $guests[$key]["Cost"] + $guest->Sum;
					if($guest->CurrencyCode == 643){
						$guests[$key]["Currency"] = "<span class='gotech_ruble'>a</span>";
					}elseif($guest->CurrencyCode == 840){
						$guests[$key]["Currency"] = '$';
					}elseif($guest->CurrencyCode == 978){
						$guests[$key]["Currency"] = '&euro;';
          }elseif($guest->CurrencyCode == 417){
            $guests[$key]["Currency"] = 'KGS';
					}else{
						$guests[$key]["Currency"] = "<span class='gotech_ruble'>a</span>";
					}
				}
			}
		}
		$arResult["BOOKING"] = $guests;
		if($arResult["info_order"]["ExternalReservationStatusRow"]->CurrencyCode == "978"){
			$arResult["CurrencySymbol"] = "&euro;";
		}elseif($arResult["info_order"]["ExternalReservationStatusRow"]->CurrencyCode == "840"){
			$arResult["CurrencySymbol"] = "$";
    }elseif($arResult["info_order"]["ExternalReservationStatusRow"]->CurrencyCode == "417"){
      $arResult["CurrencySymbol"] = "KGS";
		}else{
			$arResult["CurrencySymbol"] = "<span class='gotech_ruble'>a</span>";
		}
		// ������������ ��������� ������� ������
		$arSelect = Array("ID", "NAME", "DETAIL_TEXT", "PREVIEW_TEXT", "PROPERTY_NAME_EN", "PROPERTY_PAYMENT_SYSTEM", "PROPERTY_FIRST_NIGHT", "PROPERTY_DISCOUNT", "PROPERTY_IS_RECEIPT", "PROPERTY_IS_CASH", "PROPERTY_IS_LEGAL");
		$arFilter = Array("IBLOCK_ID"=>COption::GetOptionInt('gotech.hotelonline', 'PAYMENT_METHODS_IBLOCK_ID'), "ACTIVE"=>"Y", "PROPERTY_HOTEL"=>$arResult["hotel_info"]["ID"], "CODE"=>$resultSplits);
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
		while($rz = $res->GetNextElement()){
			$arFields = $rz->GetFields();
			if($lang == "ru")
				$arResult["PAYMENT_METHODS"][$arFields["ID"]]["NAME"] = $arFields["NAME"];
			else
				$arResult["PAYMENT_METHODS"][$arFields["ID"]]["NAME"] = $arFields["PROPERTY_NAME_EN_VALUE"];
			if($arFields["PROPERTY_IS_RECEIPT_VALUE"] == 'Yes')
				$arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_RECEIPT"] = 1;
			else
				$arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_RECEIPT"] = 0;
			if($arFields["PROPERTY_IS_CASH_VALUE"] == 'Yes')
				$arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_CASH"] = 1;
			else
				$arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_CASH"] = 0;
			if($arFields["PROPERTY_IS_LEGAL_VALUE"] == 'Yes')
				$arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_LEGAL"] = 1;
			else
				$arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_LEGAL"] = 0;
			$arResult["PAYMENT_METHODS"][$arFields["ID"]]["PAYMENT_SYSTEM"] = $arFields["PROPERTY_PAYMENT_SYSTEM_VALUE"];
			$arResult["PAYMENT_METHODS"][$arFields["ID"]]["FIRST_NIGHT"] = $arFields["PROPERTY_FIRST_NIGHT_VALUE"];
			$arResult["PAYMENT_METHODS"][$arFields["ID"]]["DISCOUNT"] = $arFields["PROPERTY_DISCOUNT_VALUE"];
			if(!empty($arFields["PREVIEW_TEXT"]))
				$arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"] = $arFields["PREVIEW_TEXT"];
			else
				$arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"] = $arFields["DETAIL_TEXT"];
			$arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"] = str_replace("\\n", "<br/>", $arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"]);
			$arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"] = str_replace(array('&lt;', '&gt;'), array('<', '>'), $arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"]);
		}
		if(!empty($arResult["hotel_info"]))
			$APPLICATION->SetTitle($arResult["hotel_info"]["NAME"].GetMessage("TITLE_MODULE"));
	}
	else{
        $protocol = OnlineBookingSupport::getProtocol();
		LocalRedirect($protocol.$_SERVER["SERVER_NAME"].COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')."index.php");
	}
	$this->IncludeComponentTemplate();
}
?>
