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
	if (!function_exists('filter_var')){
		function filter_var($value, $filter_type) {
			return $value;
		}
		//if you want that function to work, you need PHP version >= 5.2.0 and enable "filter" extension
	}
	if(isset($_REQUEST["error_text"]) && !empty($_REQUEST["error_text"])){
		$arResult["ERROR"] = $_REQUEST["error_text"];
	}
	$APPLICATION->IncludeComponent("onlinebooking:reservation.header", "");
	$language = OnlineBookingSupport::getLanguage();
	if($this->includeComponentLang("", $language) == NULL){
		__IncludeLang(dirname(__FILE__)."/lang/".$language."/component.php");
	}
	$iblock_id_numbers = COption::GetOptionInt('gotech.hotelonline', 'NUMBER_IBLOCK_ID');
	$NUMBERHOTEL = COption::GetOptionString('gotech.hotelonline', 'NUMBERHOTEL');
	$NUMBERCODE = COption::GetOptionString('gotech.hotelonline', 'NUMBERCODE');
	$iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
	$HotelCode1C = "";
	$choosenHotelID = COption::GetOptionInt('gotech.hotelonline', 'CHOOSEN_HOTEL_ID');
	
	if(!in_array(COption::GetOptionInt('gotech.hotelonline', 'USER_AGENT_GROUP'),$USER->GetUserGroupArray()))
		die('Access denied');
	
	if(isset($_REQUEST["hotel"]) && !empty($_REQUEST["hotel"]) && substr($_REQUEST["hotel"], 0, 1) != "0"){
		$hotel_id = $_REQUEST["hotel"];
	}elseif(isset($_REQUEST["hotel_code"]) && !empty($_REQUEST["hotel_code"]) && substr($_REQUEST["hotel_code"], 0, 1) != "0"){
		$hotel_id = $_REQUEST["hotel_code"];
	}elseif(isset($arParams["HOTEL_CODE"]) && !empty($arParams["HOTEL_CODE"]) && substr($arParams["HOTEL_CODE"], 0, 1) != "0"){
		$hotel_id = $arParams["HOTEL_CODE"];
	}elseif(!empty($_SESSION["HOTEL_ID"])){
		$hotel_id = $_SESSION["HOTEL_ID"];
	}elseif(!empty($choosenHotelID)){
		$hotel_id = $choosenHotelID;
	}else{
		$hotel_id = "";
	}
	if(!empty($hotel_id)){
		$arFilter = array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y", "ID" => $hotel_id);
	}else{
		$arFilter = array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y");
	}
	$hotels = CIBlockElement::GetList(
		array(),
		$arFilter,
		false,
		false,
		array(
			"ID", 
			"PROPERTY_ADDRESS_WEB_SERVICE", 
			"NAME",
			"PROPERTY_HOTEL_CODE",
			'PROPERTY_HOTEL_OUTPUT_CODE',
            'PROPERTY_CURRENCY',
            'PROPERTY_SOAP_LOGIN',
            'PROPERTY_SOAP_PASSWORD'
		)
	);
	
	$arResult['HOTEL_ID'] = $hotel_id;
	while($hotel = $hotels->GetNext()) {
		
		$hotel_code = $hotel['PROPERTY_HOTEL_CODE_VALUE'];
		$hotel_wsdl = $hotel['PROPERTY_ADDRESS_WEB_SERVICE_VALUE'];
		$outputCode = $hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"];

        if(!empty($hotel["PROPERTY_CURRENCY_VALUE"]) && $hotel["PROPERTY_CURRENCY_VALUE"] != NULL) {
          $arResult["CURRENCY"] = $hotel["PROPERTY_CURRENCY_ENUM_ID"];
          $arResult["CURRENCY_NAME"] = $hotel["PROPERTY_CURRENCY_VALUE"];
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
	
	// ��������� ������ ������ �� ����������� ���������
	$arResult["DO_EXPRESS"] = false;
	if(CModule::IncludeModule("gotech.expresscheckin")){
		$OnlineBookingSupport = new OnlineBookingSupport();
		$res = $OnlineBookingSupport->checkVersion('1.1.0');
		$arResult["DO_EXPRESS"] = !$res;
	}

    $soap_params = array('trace' => 1);
    if (!empty($SOAP_LOGIN) && !empty($SOAP_PASSWORD)) {
        $soap_params['login'] = $SOAP_LOGIN;
        $soap_params['password'] = $SOAP_PASSWORD;
    }
    $soapclient = new SoapClient($hotel_wsdl, $soap_params); // with auth
	$query = array(		
		"Hotel" => $hotel_code,
		"Login" => $USER->GetLogin(),
		"ExternalSystemCode" => $outputCode,
		"LanguageCode" => mb_strtoupper($language),
	);	
	
	$result = $soapclient->GetActiveGroupsList($query);
	$data = $result->return;
	
	/*?><pre><?echo var_dump($data);?></pre><?/**/
	
	$arResult['BOOKING'] = array();
    if (is_object($data->GuestGroupDetails)) {
        $item = array();
        $item['Id'] = $data->GuestGroupDetails->GuestGroup;
        $item['Status'] = 'Reservation';
        $item['FullName'] = $data->GuestGroupDetails->GuestFullName;
        $item['PeriodFrom'] = date('d.m.Y',strtotime($data->GuestGroupDetails->CheckInDate));
        $item['PeriodTo'] = date('d.m.Y',strtotime($data->GuestGroupDetails->CheckOutDate));
        $item['Nights'] = $data->GuestGroupDetails->Duration;
        $item['Guests'] = $data->GuestGroupDetails->NumberOfPersons;
        $item['Amount'] = $data->GuestGroupDetails->Amount;
        $item['Balance'] = $data->GuestGroupDetails->Balance;
        $item['Email'] = $data->GuestGroupDetails->Email;

        $arResult['BOOKING'][] = $item;
    } else {
        foreach($data->GuestGroupDetails as $k => $booking):

            $item = array();
            $item['Id'] = $booking->GuestGroup;
            $item['Status'] = 'Reservation';
            $item['FullName'] = $booking->GuestFullName;
            $item['PeriodFrom'] = date('d.m.Y',strtotime($booking->CheckInDate));
            $item['PeriodTo'] = date('d.m.Y',strtotime($booking->CheckOutDate));
            $item['Nights'] = $booking->Duration;
            $item['Guests'] = $booking->NumberOfPersons;
            $item['Amount'] = $booking->Amount;
            $item['Balance'] = $booking->Balance;
            $item['Email'] = $booking->Email;

            $arResult['BOOKING'][] = $item;
        endforeach;
    }
	
	$this->IncludeComponentTemplate();
}
function isUserAgent() {
	global $USER;
	if($USER->IsAuthorized()) {
		//if(CModule::IncludeModule("gotech.hotelonlineoffice")) {
			if(in_array(COption::GetOptionInt('gotech.hotelonline', 'USER_AGENT_GROUP'), $USER->GetUserGroupArray()))
				return true;
			else 
				return false;
		//}else
		//	return false;
	}
	else
		return false;
}
?>