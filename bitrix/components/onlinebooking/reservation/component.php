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

    if ($arParams['TYPE'] == 'EMBEDED') {
      $_REQUEST['adults'] = $arParams['ADULTS'];
      $_REQUEST['kids'] = $arParams['KIDS'];

      foreach ($arParams['AGES'] as $key => $age) {
        $_REQUEST['kid'.$key] = $age;
      }

      $_REQUEST["datefrom"] = $arParams["PeriodFrom"];
      $_REQUEST["dateto"] = $arParams["PeriodTo"];
    }

    if ($arParams['TYPE'] != 'EMBEDED') {
      function isUserAgent()
      {
        global $USER;
        if ($USER->IsAuthorized()) {
          if (CModule::IncludeModule("gotech.hotelonlineoffice")) {
            if (in_array(COption::GetOptionInt('gotech.hotelonline', 'USER_AGENT_GROUP'), $USER->GetUserGroupArray()))
              return true;
            else
              return false;
          } else
            return false;
        } else
          return false;
      }
    }

    if($this->includeComponentLang("", OnlineBookingSupport::getLanguage()) == NULL){
        __IncludeLang(dirname(__FILE__)."/lang/".OnlineBookingSupport::getLanguage()."/component.php");
    }
    unset($_SESSION["NUMBERS_BOOKED"]);

    $arResult["IS_AGENT"] = isUserAgent();
    $iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
    $iblock_id_numbers = COption::GetOptionInt('gotech.hotelonline', 'NUMBER_IBLOCK_ID');
    if(!$arParams["ID_HOTEL"])
        $filter = array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y");
    else
        $filter = array("IBLOCK_ID" => $iblock_id_hotel, "ID" => $arParams["ID_HOTEL"], "ACTIVE" => "Y");
    $hotels = CIBlockElement::GetList(
        array(),
        $filter,
        false,
        false,
        array(
            "ID",
            "NAME",
            "PROPERTY_ADDRESS_WEB_SERVICE",
            "PROPERTY_HOTEL_CODE",
            "PROPERTY_HOTEL_NAME_EN",
            "PROPERTY_HOTEL_MAX_ADULT",
            "PROPERTY_HOTEL_MAX_CHILDREN",
            "PROPERTY_HOTEL_MAX_GUESTS",
            "PROPERTY_GUESTS_DEFAULT",
            "PROPERTY_HOURS_ENABLE",
            "PROPERTY_NIGHTS_MIN",
            "PROPERTY_HOURS_MIN",
            "PROPERTY_PROMO",
            "PROPERTY_BIRTHDAY",
            "PROPERTY_CITIZENSHIP",
            "PROPERTY_NUMBER_OF_CAR",
            "PROPERTY_HOTEL_OUTPUT_CODE",
            "PROPERTY_HOTEL_ROOM_RATE",
            "PROPERTY_HOTEL_ROOM_QUOTA",
            "PROPERTY_HOTEL_RESERVATION_STATUS",
            "PROPERTY_HOTEL_CUSTOMER",
            "PROPERTY_HOTEL_CONTRACT",
            "PROPERTY_HOTEL_SALES_BEGIN",
            "PROPERTY_HOTEL_SALES_END",
            "PROPERTY_HOTEL_ROOM_TYPE",
            "PROPERTY_HOTEL_CLIENT_TYPE",
            "PROPERTY_ADD_TEXT_RU",
            "PROPERTY_ADD_TEXT_EN",
            "PROPERTY_IGNORE_GET_START",
            "PROPERTY_CRUISE_MODE"
        )
    );
    $arResult["add_text"] = "";
    if($hotel = $hotels->GetNext())
    {

        $arResult['START_FROM_GET'] = $hotel["PROPERTY_IGNORE_GET_START_VALUE"] == 'Y' ? false : true;
        $arResult['CRUISE_MODE'] = $hotel["PROPERTY_CRUISE_MODE_VALUE"] ? true : false;

        //????? ???-??????? - AddressWebservice
        if(strlen($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]))
            $AddressWebservice = trim($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]);
        else $AddressWebservice = trim(COption::GetOptionString('gotech.hotelonline', 'AddressWebservice'));
        //??? ??????? ??????? - OutputCode
        if(strlen($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]))
            $OutputCode = trim($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]);
        else $OutputCode = trim(COption::GetOptionString('gotech.hotelonline', 'OutputCode'));
        //??? ?????? - RoomRate
        if(strlen($hotel["PROPERTY_HOTEL_ROOM_RATE_VALUE"]))
            $RoomRate = trim($hotel["PROPERTY_HOTEL_ROOM_RATE_VALUE"]);
        else $RoomRate = trim(COption::GetOptionString('gotech.hotelonline', 'RoomRate'));
        //??? ????? - RoomQuota
        if(strlen($hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"]))
            $RoomQuota = trim($hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"]);
        else $RoomQuota = trim(COption::GetOptionString('gotech.hotelonline', 'RoomQuota'));
        //??? ??????? ????? - ReservationStatus
        if(strlen($hotel["PROPERTY_HOTEL_RESERVATION_STATUS_VALUE"]))
            $ReservationStatus = trim($hotel["PROPERTY_HOTEL_RESERVATION_STATUS_VALUE"]);
        else $ReservationStatus = trim(COption::GetOptionString('gotech.hotelonline', 'ReservationStatus'));
        //??? ??????????? - Customer
        if(strlen($hotel["PROPERTY_HOTEL_CUSTOMER_VALUE"]))
            $Customer = trim($hotel["PROPERTY_HOTEL_CUSTOMER_VALUE"]);
        else $Customer = trim(COption::GetOptionString('gotech.hotelonline', 'Customer'));
        //??? ???????? ??????????? - Contract
        if(strlen($hotel["PROPERTY_HOTEL_CONTRACT_VALUE"]))
            $Contract = trim($hotel["PROPERTY_HOTEL_CONTRACT_VALUE"]);
        else $Contract = trim(COption::GetOptionString('gotech.hotelonline', 'Contract'));
        $arResult["HOTEL"] = array(
            "ID" => $hotel["ID"],
            "CODE" => $hotel["PROPERTY_HOTEL_CODE_VALUE"],
            "HOTEL_MAX_ADULT" => $hotel["PROPERTY_HOTEL_MAX_ADULT_VALUE"],
            "HOTEL_MAX_CHILDREN" => $hotel["PROPERTY_HOTEL_MAX_CHILDREN_VALUE"],
            "HOTEL_MAX_GUESTS" => $hotel["PROPERTY_HOTEL_MAX_GUESTS_VALUE"],
            "GUESTS_DEFAULT" => $hotel["PROPERTY_GUESTS_DEFAULT_VALUE"],
            "NIGHTS_MIN" => $hotel["PROPERTY_NIGHTS_MIN_VALUE"],
            "HOURS_ENABLE" => $hotel["PROPERTY_HOURS_ENABLE_VALUE"]?true:false,
            "HOURS_MIN" => $hotel["PROPERTY_HOURS_MIN_VALUE"],
            "PROMO" => $hotel["PROPERTY_PROMO_VALUE"]?"Y":"N",
            "BIRTHDAY" => $hotel["PROPERTY_BIRTHDAY_VALUE"]?"Y":"N",
            "CITIZENSHIP" => $hotel["PROPERTY_CITIZENSHIP_VALUE"]?"Y":"N",
            "NUMBER_OF_CAR" => $hotel["PROPERTY_NUMBER_OF_CAR_VALUE"]?"Y":"N",
            "AddressWebservice" => $AddressWebservice,
            "OutputCode" => $OutputCode,
            "RoomRate" => $RoomRate,
            "RoomQuota" => $RoomQuota,
            "ReservationStatus" => $ReservationStatus,
            "Customer" => $Customer,
            "Contract" => $Contract,
            "SalesBegin" => substr($hotel["PROPERTY_HOTEL_SALES_BEGIN_VALUE"], 0, 10),
            "SalesEnd" => substr($hotel["PROPERTY_HOTEL_SALES_END_VALUE"], 0, 10),
            "RoomType" => $hotel["PROPERTY_HOTEL_ROOM_TYPE_VALUE"] ? $hotel["PROPERTY_HOTEL_ROOM_TYPE_VALUE"] : "",
            "ClientType" => $hotel["PROPERTY_HOTEL_CLIENT_TYPE_VALUE"] ? $hotel["PROPERTY_HOTEL_CLIENT_TYPE_VALUE"] : ""
        );
        if(OnlineBookingSupport::getLanguage() == "ru")
            $APPLICATION->SetTitle($hotel["NAME"].GetMessage("TITLE_NAME"));
        elseif(OnlineBookingSupport::getLanguage() == "en")
            $APPLICATION->SetTitle($hotel["PROPERTY_HOTEL_NAME_EN_VALUE"].GetMessage("TITLE_NAME"));

        if(!file_exists("cached_wsdl.txt") || @filesize($_SERVER["DOCUMENT_ROOT"]."/cached_wsdl.txt") === 0){
            $result = file_put_contents("cached_wsdl.txt", @file_get_contents(trim($AddressWebservice)));
            if($result === 0) {
                $arFields = array(
                    "event" => "Caching WSDL"
                ,"data" => trim($AddressWebservice)
                ,"error_text" => "WSDL handling error"
                );
                $ID = OnlineBookingSupport::db_add('ob_gotech_errors', $arFields);
            }
        }
        if(OnlineBookingSupport::getLanguage() == 'ru'){
            if(strlen($hotel["~PROPERTY_ADD_TEXT_RU_VALUE"]["TEXT"]))
                $arResult["add_text"] = $hotel["~PROPERTY_ADD_TEXT_RU_VALUE"]["TEXT"];
        }else{
            if(strlen($hotel["PROPERTY_ADD_TEXT_EN_VALUE"]["TEXT"]))
                $arResult["add_text"] = $hotel["~PROPERTY_ADD_TEXT_EN_VALUE"]["TEXT"];
        }
    }
    else {
        ShowError(GetMessage("HOTEL_ID_ERROR"));
        return;
    }


    if ($arParams['TYPE'] != 'EMBEDED') {
      if ($_REQUEST['reservation'] && $_REQUEST['data'] && $_REQUEST['search'] == 'Y' && $_REQUEST['hotel_code'])
        LocalRedirect('/my.php?hotel_code=' . $_REQUEST["hotel_code"] . '&reservation=' . $_REQUEST['reservation'] . '&data=' . $_REQUEST['data'] . '&search=Y');

      if ($_REQUEST['UUID'])
        LocalRedirect('/my.php?hotel_code=' . $_REQUEST["hotel_code"] . '&UUID=' . $_REQUEST['UUID']);
    }
    if ($arResult['CRUISE_MODE']) {
        $filter = array(
            "IBLOCK_CODE" => "cruises",
            "ACTIVE" => "Y",
        );

        ini_set('xdebug.var_display_max_depth', 15);
        ini_set('xdebug.var_display_max_children', 256);
        ini_set('xdebug.var_display_max_data', 1024);

        $arResult["cruises"] = array();
        $dbEl = CIBlockElement::GetList(array("SORT"=>"ASC"), $filter);
        while($obEl = $dbEl->GetNextElement())
        {
            unset($fields);
            unset($props);
            $fields = $obEl->GetFields();
            $props = $obEl->GetProperties();
            if(new DateTime($props["CRUISE_FROM"]["VALUE"]) > new DateTime()) {
                $arResult["cruises"][$props["CRUISE_DEPART_CITY"]["VALUE"]][$props["CRUISE_FROM"]["VALUE"] ? $props["CRUISE_FROM"]["VALUE"] : "null"][$props["CRUISE_ARRIVE_CITY"]["VALUE"] ? $props["CRUISE_ARRIVE_CITY"]["VALUE"] : "null"][$props["CRUISE_TO"]["VALUE"] ? $props["CRUISE_TO"]["VALUE"] : "null"][] = array(
                    "cruise_name" => $fields["NAME"],
                    "cruise_id" => $fields["ID"],
                    "hotel" => $props["CRUISE_HOTEL"]["VALUE"],
                    "arrive_city_id" => $props["CRUISE_ARRIVE_CITY"]["VALUE"],
                    "arrive_city_name" => "",
                    "from" => $props["CRUISE_FROM"]["VALUE"] ? new DateTime($props["CRUISE_FROM"]["VALUE"]) : null,
                    "to" => $props["CRUISE_TO"]["VALUE"] ? new DateTime($props["CRUISE_TO"]["VALUE"]) : null,
                    "description" => $fields["PREVIEW_TEXT"]
                );
            }
        }

        // var_dump($arResult["cruises"]);


        $filter = array(
            "IBLOCK_CODE" => "depart_cities",
            "ID" => array_keys($arResult["cruises"]),
            "ACTIVE" => "Y",
        );

        $arResult["depart_cities"] = array();
        $dbEl = CIBlockElement::GetList(array("SORT"=>"ASC"), $filter);
        while($obEl = $dbEl->GetNextElement())
        {
            unset($fields);
            unset($props);
            $fields = $obEl->GetFields();
            $props = $obEl->GetProperties();

            $arResult["depart_cities"][] = array(
                "id" => $fields["ID"],
                "name" => OnlineBookingSupport::getLanguage() == "ru" ? $fields["NAME"] : $props["CITY_NAMEEN"]["VALUE"]
            );
        }

        foreach($arResult["cruises"] as $city_id => &$depart_dates) {
            foreach($depart_dates as $dep_date => &$arrive_cities) {
                foreach($arrive_cities as $arrive_city => &$arrive_dates) {
                    foreach($arrive_dates as &$cruises) {
                        foreach($cruises as &$cruise) {
                            if($cruise["arrive_city_id"]) {
                                $filter = array(
                                    "IBLOCK_CODE" => "depart_cities",
                                    "ID" => $cruise["arrive_city_id"],
                                    "ACTIVE" => "Y",
                                );
                                $dbEl = CIBlockElement::GetList(array("SORT"=>"ASC"), $filter);
                                while($obEl = $dbEl->GetNextElement())
                                {
                                    unset($fields);
                                    unset($props);
                                    $fields = $obEl->GetFields();
                                    $props = $obEl->GetProperties();
                                    $cruise["arrive_city_name"] = OnlineBookingSupport::getLanguage() == "ru" ? $fields["NAME"] : $props["CITY_NAMEEN"]["VALUE"];
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    $this->IncludeComponentTemplate();
}
?>
