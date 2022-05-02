<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if ($arParams["REQUEST"]) {
    $_REQUEST = $arParams["REQUEST"];
}


if(!CModule::IncludeModule("iblock")) {
    echo GetMessage("IBLOCK_NOT_INCLUDE");
    return;
}
elseif(!CModule::IncludeModule("gotech.hotelonline")) {
    ShowError(GetMessage("ONLINEBOOKING_MODULE_NOT_INSTALLED"));
    return;
}
else {
    define("start_time", microtime(true));
    $arResult["language"] = $_REQUEST["language"] ? $_REQUEST["language"]:OnlineBookingSupport::getLanguage();
    if($this->includeComponentLang("", $arResult["language"]) == NULL){
        __IncludeLang(dirname(__FILE__)."/lang/".$arResult["language"]."/component.php");
    }

    if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount) && $_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount > 0) {
        $arResult['LOGGED_USER_DISCOUNT'] = $_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount;
    }

    if(!function_exists("getMonthNameById")){
        function getMonthNameById($id){
            switch ($id) {
                case 1:
                    return GetMessage('january');
                    break;
                case 2:
                    return GetMessage('february');
                    break;
                case 3:
                    return GetMessage('march');
                    break;
                case 4:
                    return GetMessage('april');
                    break;
                case 5:
                    return GetMessage('may');
                    break;
                case 6:
                    return GetMessage('june');
                    break;
                case 7:
                    return GetMessage('july');
                    break;
                case 8:
                    return GetMessage('august');
                    break;
                case 9:
                    return GetMessage('september');
                    break;
                case 10:
                    return GetMessage('october');
                    break;
                case 11:
                    return GetMessage('november');
                    break;
                case 12:
                    return GetMessage('december');
                    break;
            }
            return "";
        }
    }
    if(!function_exists("getTrimMonthNameById")){
        function getTrimMonthNameById($id){
            switch ($id) {
                case 1:
                    return GetMessage('jan');
                    break;
                case 2:
                    return GetMessage('feb');
                    break;
                case 3:
                    return GetMessage('mar');
                    break;
                case 4:
                    return GetMessage('apr');
                    break;
                case 5:
                    return GetMessage('may');
                    break;
                case 6:
                    return GetMessage('jun');
                    break;
                case 7:
                    return GetMessage('jul');
                    break;
                case 8:
                    return GetMessage('aug');
                    break;
                case 9:
                    return GetMessage('sep');
                    break;
                case 10:
                    return GetMessage('oct');
                    break;
                case 11:
                    return GetMessage('nov');
                    break;
                case 12:
                    return GetMessage('dec');
                    break;
            }
            return "";
        }
    }
    $start = microtime(true);
    if(!isset($arParams["TYPE"]) || $arParams["TYPE"] != "SERVICE"){

        if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount) && $_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount > 0) {
            $arResult['LOGGED_USER_DISCOUNT'] = $_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount;
        }

        if(!empty($_REQUEST) && isset($_REQUEST["send_data"]) && $_REQUEST["send_data"] == "Y") {

            if(isset($_REQUEST["PeriodFrom"]))
                $explodePeriodFrom = explode('.', $_REQUEST["PeriodFrom"]);
            if(isset($_REQUEST["PeriodTo"]))
                $explodePeriodTo = explode('.', $_REQUEST["PeriodTo"]);

            if(!empty($_REQUEST["hotel_id"])) {
                $res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID'), "ID" => $_REQUEST["hotel_id"]), false, false,
                    array('NAME', 'PREVIEW_TEXT',"PROPERTY_HOURS_ENABLE","PROPERTY_HOTEL_MAX_CHILDREN","PROPERTY_HOTEL_MAX_ADULT","PROPERTY_HOTEL_TIME_FROM","PROPERTY_HOTEL_TIME","PROPERTY_CRUISE_MODE"));
                if ($hotel = $res->GetNext()) {
                    $arResult["HOURS_ENABLE"] = $hotel["PROPERTY_HOURS_ENABLE_VALUE"] ? true : false;
                    $arResult["HOTEL_MAX_CHILDREN"] = $hotel["PROPERTY_HOTEL_MAX_CHILDREN_VALUE"];
                    $arResult["HOTEL_TIME_FROM"] = $hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"];
                    $arResult["HOTEL_TIME"] = $hotel["PROPERTY_HOTEL_TIME_VALUE"];
                    $arResult["RCOND"] = $hotel["PREVIEW_TEXT"];
                    $arResult["HOTEL_NAME"] = $hotel["NAME"];
                    $arResult['CRUISE_MODE'] = $hotel["PROPERTY_CRUISE_MODE_VALUE"] ? true : false;
                }
            }
            $_SESSION["PeriodFrom"] = $_REQUEST["PeriodFrom"];
            if ($arResult["HOURS_ENABLE"]) {
                $_SESSION["TimeFrom"] = $_REQUEST["TimeFrom"];
                $_SESSION["hour"] = $_REQUEST["hour"];
                $_SESSION["PeriodTo"] = date("d.m.Y", AddToTimeStamp(array("HH" => $_REQUEST["hour"]), MakeTimeStamp($_REQUEST["PeriodFrom"] . " " . $_REQUEST["TimeFrom"] . ":00", "DD.MM.YYYY HH:MI:SS")));
            } else {
                $_SESSION["PeriodTo"] = $_REQUEST["PeriodTo"];
            }

            $arResult['depart_city_label'] = "";
            if (isset($_REQUEST['depart_city']) && !empty($_REQUEST['depart_city'])) {
                $filter = array(
                    "IBLOCK_CODE" => "depart_cities",
                    "ID" => $_REQUEST['depart_city'],
                    "ACTIVE" => "Y",
                );

                $dbEl = CIBlockElement::GetList(array("SORT"=>"ASC"), $filter);
                while($obEl = $dbEl->GetNextElement())
                {
                    unset($fields);
                    unset($props);
                    $fields = $obEl->GetFields();
                    $props = $obEl->GetProperties();

                    $arResult['depart_city_label'] = OnlineBookingSupport::getLanguage() == "ru" ? $fields["NAME"] : $props["CITY_NAMEEN"]["VALUE"];
                }
            }

            $age_children = array();
            if(isset($_REQUEST['children']) && !empty($_REQUEST['children'])){
                foreach($_REQUEST as $key => $req) {
                    $re = explode('_', $key);
                    if($re[0] == 'childrenYear') {
                        if(isset($_REQUEST['shChildrenYear_'.$re[1]]) && $_REQUEST['shChildrenYear_'.$re[1]] == 'true'){
                            $age_children[] = intval($req);
                            // if(intval($req) > 0)
                            // $age_children[] = intval($req);
                            // else
                            // $arResult["ERROR"] = GetMessage("ERROR_CHILDREN_AGE");
                        }
                    }
                }
            }
            if(isset($_REQUEST["adults"])) $adults = intval($_REQUEST["adults"]);
            else $adults = 0;
            if($adults == 0)
                $adults = 1;
            if(isset($_REQUEST["children"])) $children = intval($_REQUEST["children"]);
            else $children = 0;
            if($children != count($age_children))
                $arResult["ERROR"] = GetMessage("NO_ALL_AGE_FOR_CHILDREN");
            if(empty($arResult["ERROR"])){
                $guests = array(
                    'Adults' => array('Quantity' => $adults),
                    'Kids' => array('Quantity' => $children, 'Age' => $age_children),
                    'RequestAttributes' => array(
                        'SessionID' => $_REQUEST['SessionID'],
                        'UserID' => $_REQUEST['UserID'],
                        'utm_source' => $_REQUEST['utm_source'],
                        'utm_medium' => $_REQUEST['utm_medium'],
                        'utm_campaign' => $_REQUEST['utm_campaign']
                    )
                );
                $_SESSION["Guests"] = $guests;
                $phone = $_REQUEST["phone"];
                if(!empty($_REQUEST["embeded_contact_person_phone"])) {
                    $phone = $_REQUEST["embeded_contact_person_phone"];
                }

                if(!empty($_REQUEST["email"]) && check_email($_REQUEST["email"]))
                    $email = $_REQUEST["email"];
                elseif(!empty($_REQUEST["embeded_contact_person_email"]) && check_email($_REQUEST["embeded_contact_person_email"]))
                    $email = $_REQUEST["embeded_contact_person_email"];
                elseif(!empty($_REQUEST["email"]))
                    $arResult["ERROR"] = GetMessage('ERROR_MAIL');
                if($USER->IsAuthorized())
                    $login = CUser::GetLogin();
                else $login = "";

                if (!$login && !empty($_REQUEST["embeded_login"])) {
                    $login = $_REQUEST["embeded_login"];
                }

                //$_SESSION['sn'] = NULL;
                /*?><pre><?echo var_dump($_SESSION);?></pre><?/**/
                if(!$login && $_SESSION['sn'] && $_SESSION['sn_id'])
                {
                    if($_SESSION['sn'] == 'fb')
                        $login = 'https://www.facebook.com/'.$_SESSION['sn_id'];
                    else if($_SESSION['sn'] == 'vk')
                        $login = 'http://vk.com/id'.$_SESSION['sn_id'];
                }

                $currency = 0;
                $currencyName = "";
                $hotelCode = 0;
                $roomRate = "";
                $roomQuota = "";
                $outputCode = "";
                $WSDL = "";
                $showEconomy = true;
                if(!empty($_REQUEST["hotel_id"]))
                {
                    $res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID'), "ID" => $_REQUEST["hotel_id"]), false, false,
                        array("PROPERTY_HOTEL_TIME", "PROPERTY_HOTEL_TIME_FROM", "PROPERTY_CURRENCY", "PROPERTY_HOURS_ENABLE", "PROPERTY_HOTEL_CODE", "PROPERTY_HOTEL_OUTPUT_CODE", "PROPERTY_HOTEL_ROOM_RATE", "PROPERTY_HOTEL_ROOM_QUOTA", "PROPERTY_ADDRESS_WEB_SERVICE", "PROPERTY_HOTEL_SHOW_ECONOMY", "PROPERTY_HOTEL_ERROR_TEXT_RU", "PROPERTY_HOTEL_ERROR_TEXT_EN", "PROPERTY_SHOW_AVAILABILITY_ALL_ROOMS", "PROPERTY_AVAILABILITY_DAYS_PERIOD_RANGE_DAYS", "PROPERTY_SOAP_LOGIN", "PROPERTY_SOAP_PASSWORD"));
                    if($hotel = $res->GetNext()) {
                        $arResult["HOURS_ENABLE"] = $hotel["PROPERTY_HOURS_ENABLE_VALUE"] ? true : false;
                        if($hotel["PROPERTY_HOTEL_SHOW_ECONOMY_VALUE"]){
                            $showEconomy = true;
                        }else{
                            $showEconomy = false;
                        }
                        if(!empty($hotel["PROPERTY_CURRENCY_VALUE"]) && $hotel["PROPERTY_CURRENCY_VALUE"] != NULL){
                            $currency = $hotel["PROPERTY_CURRENCY_ENUM_ID"];
                            $currencyName = $hotel["PROPERTY_CURRENCY_VALUE"];
                        }else $currency = 0;
                        if(!empty($hotel["PROPERTY_HOTEL_TIME_VALUE"])){
                            $pos = strpos($hotel["PROPERTY_HOTEL_TIME_VALUE"], ":");
                            if($pos){
                                $time = substr($hotel["PROPERTY_HOTEL_TIME_VALUE"],$pos-2, 2).":".substr($hotel["PROPERTY_HOTEL_TIME_VALUE"],$pos+1, 2).":00";
                            }else
                                $time = date("H:i:s", $hotel["PROPERTY_HOTEL_TIME_VALUE"]);
                        }else $time = "00:00:00";
                        if(!empty($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"])){
                            $pos = strpos($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"], ":");
                            if($pos){
                                $timeFrom = substr($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"],$pos-2, 2).":".substr($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"],$pos+1, 2).":00";
                            }else
                                $timeFrom = date("H:i:s", $hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"]);
                        }else $timeFrom = "00:00:00";
                        if(!empty($hotel["PROPERTY_HOTEL_CODE_VALUE"])){
                            $hotelCode = $hotel["PROPERTY_HOTEL_CODE_VALUE"];
                        }else $hotelCode = "";
                        if ($_REQUEST['embeded'] == 'Y' && $_REQUEST['embeded_hotel_code']) {
                          $hotelCode = $_REQUEST['embeded_hotel_code'];
                        }
                        if(!empty($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"])){
                            $outputCode = $hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"];
                        }else $outputCode = "";
                        if ($_REQUEST['embeded'] == 'Y' && $_REQUEST['embeded_output_code']) {
                          $outputCode = $_REQUEST['embeded_output_code'];
                        }

                        if(!empty($hotel["PROPERTY_HOTEL_ROOM_RATE_VALUE"])){
                            $roomRate = $hotel["PROPERTY_HOTEL_ROOM_RATE_VALUE"];
                        }else $roomRate = "";
                        if ($_REQUEST['embeded'] == 'Y' && $_REQUEST['embeded_room_rate_code']) {
                          $roomRate = $_REQUEST['embeded_room_rate_code'];
                        }

                        if(!empty($hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"])){
                            $roomQuota = $hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"];
                        }else $roomQuota = "";
                        if ($_REQUEST['embeded'] == 'Y' && $_REQUEST['embeded_room_quota_code']) {
                          $roomQuota = $_REQUEST['embeded_room_quota_code'];
                        }

                        if(!empty($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"])){
                            $WSDL = $hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"];
                        }else $WSDL = "";

                        if ($arResult["language"] == 'en') {
                            if(!empty($hotel["PROPERTY_HOTEL_ERROR_TEXT_EN_VALUE"])){
                                $errorText = $hotel["PROPERTY_HOTEL_ERROR_TEXT_EN_VALUE"];
                            }else $errorText = "";
                        }else{
                            if(!empty($hotel["PROPERTY_HOTEL_ERROR_TEXT_RU_VALUE"])){
                                $errorText = $hotel["PROPERTY_HOTEL_ERROR_TEXT_RU_VALUE"];
                            }else $errorText = "";
                        }

                        if(!empty($hotel["PROPERTY_SHOW_AVAILABILITY_ALL_ROOMS_VALUE"])){
                            $arResult["SHOW_AVAILABILITY_ALL_ROOMS"] = true;
                        }else $arResult["SHOW_AVAILABILITY_ALL_ROOMS"] = false;

                        if(!empty($hotel["PROPERTY_AVAILABILITY_DAYS_PERIOD_RANGE_DAYS_VALUE"])){
                            $arResult["AVAILABILITY_DAYS_PERIOD_RANGE_DAYS"] = $hotel["PROPERTY_AVAILABILITY_DAYS_PERIOD_RANGE_DAYS_VALUE"];
                        }else $arResult["AVAILABILITY_DAYS_PERIOD_RANGE_DAYS"] = "2";

                        if(!empty($hotel["PROPERTY_SOAP_LOGIN_VALUE"]))
                            $SOAP_LOGIN = trim($hotel["PROPERTY_SOAP_LOGIN_VALUE"]);
                        else
                            $SOAP_LOGIN = "";
                        if(!empty($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]))
                            $SOAP_PASSWORD = trim($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]);
                        else
                            $SOAP_PASSWORD = "";

                    }
                    // Services
                    $filter = array(
                        "IBLOCK_CODE" => "service",
                        "PROPERTY_SERVICEHOTEL" => $_REQUEST["hotel_id"],
                        "ACTIVE" => "Y",
                    );

                    $arResult["Services"] = array();
                    $dbEl = CIBlockElement::GetList(array("SORT"=>"ASC"), $filter);
                    while($obEl = $dbEl->GetNextElement())
                    {
                        unset($fields);
                        unset($props);
                        $fields = $obEl->GetFields();
                        $props = $obEl->GetProperties();
                        $activeFromDateTime = New DateTime($fields["ACTIVE_FROM"]);
                        $activeToDateTime = New DateTime($fields["ACTIVE_TO"]);
                        $datetime1 = new DateTime($_REQUEST["PeriodFrom"]);
                        $datetime2 = new DateTime($_REQUEST["PeriodFrom"]);
                        $interval = $datetime1->diff($datetime2);
                        if((empty($props["SERVICEMINLOS"]["VALUE"]) Or intval($props["SERVICEMINLOS"]["VALUE"])<=$interval->days)
                            && (empty($props["SERVICEMAXLOS"]["VALUE"]) or intval($props["SERVICEMAXLOS"]["VALUE"])>=$interval->days)){
                            if(((!empty($fields["ACTIVE_TO"]) && $activeToDateTime >= $datetime1) || empty($fields["ACTIVE_TO"])) && ((!empty($fields["ACTIVE_FROM"]) && $activeFromDateTime <= $datetime1) || empty($fields["ACTIVE_FROM"]))){
                                if(empty($props["SERVICERATE"]["VALUE"]) or in_array($props["SERVICERATE"]["VALUE"], $arRoomRates)){
                                    $service["Id"] = $fields["ID"];
                                    if(OnlineBookingSupport::getLanguage() == "ru")
                                        $service["Name"] = $fields["NAME"];
                                    else
                                        $service["Name"] = $props["SERVICENAMEEN"]["VALUE"];
                                    $service["Text"] = $fields["PREVIEW_TEXT"];
                                    $service["Price"] = $props["SERVICEPRICE"]["VALUE"];
                                    $service["Code"] = $props["SERVICECODE"]["VALUE"];
                                    $service["Hotel"] = $props["SERVICEHOTEL"]["VALUE"];
                                    $service["Hotel_id"] = $_REQUEST["hotel_id"];
                                    $service["Picture"] = CFile::ResizeImageGet(
                                        $fields["PREVIEW_PICTURE"],
                                        array('width' => 112,'height' => 100),
                                        BX_RESIZE_IMAGE_EXACT,
                                        true
                                    );
                                    $arResult["Services"][] = $service;
                                }
                            }
                        }
                    }

                    // Photos
                    $filter = array(
                        "IBLOCK_CODE" => "photo",
                        "PROPERTY_HOTEL" => $_REQUEST["hotel_id"],
                        "ACTIVE" => "Y",
                    );

                    $arResult["Photos"] = array();
                    $dbEl = CIBlockElement::GetList(array("SORT"=>"ASC"), $filter);
                    while($obEl = $dbEl->GetNextElement())
                    {
                        unset($fields);
                        unset($props);
                        $fields = $obEl->GetFields();
                        $props = $obEl->GetProperties();

                        $photo["Id"] = $fields["ID"];
                        if(OnlineBookingSupport::getLanguage() == "ru"){
                            $photo["Name"] = $fields["NAME"];
                            $photo["Text"] = $fields["PREVIEW_TEXT"];
                        }else{
                            $photo["Name"] = $props["NAMEEN"]["VALUE"];
                            $photo["Text"] = $fields["DETAIL_TEXT"];
                        }
                        $photo["RoomId"] = $props["NUMBER"]["VALUE"];
                        $prevPicArray = CFile::GetFileArray($fields["PREVIEW_PICTURE"]);
                        $detPicArray = CFile::GetFileArray($fields["DETAIL_PICTURE"]);
                        $photo["preview_src"] = $prevPicArray["SRC"];
                        $photo["detail_src"] = $detPicArray["SRC"];
                        $arResult["Photos"][] = $photo;
                    }
                }

                $arResult["WSDL"] = trim($WSDL);

                $arResult["SHOW_ECONOMY"] = $showEconomy;
                $arResult["CURRENCY_ID"] = $currency;
                $arResult["CURRENCY_NAME"] = $currencyName;
                if(empty($arResult["ERROR"]))
                {
                    $roomsUsed = array();
                    if(isset($_SESSION["NUMBERS_BOOKING"])){
                        foreach($_SESSION["NUMBERS_BOOKING"][$_REQUEST["hotel_id"]]["NUMBERS"] as $key => $value) {
                            if(isset($roomsUsed[$value["RoomTypeCode"]])){
                                $roomsUsed[$value["RoomTypeCode"]] += 1;
                            }else{
                                $roomsUsed[$value["RoomTypeCode"]] = 1;
                            }
                        }
                    }
                    $arResult["RoomsUsed"] = $roomsUsed;

                    $x = array(
                        'Hotel' => $hotelCode,
                        'RoomRate' => ($_REQUEST['embeded'] == 'Y' && $_REQUEST['embeded_room_rate_code']) ? $_REQUEST['embeded_room_rate_code'] : '',//$roomRate,
                        'ClientType' => (isset($_REQUEST["client_type"]) && !empty($_REQUEST["client_type"])) ? $_REQUEST["client_type"] : "",
                        'RoomType' => ($_REQUEST['embeded'] == 'Y' && $_REQUEST['embeded_room_type_code']) ? $_REQUEST['embeded_room_type_code'] : ((isset($_REQUEST["room_type"]) && !empty($_REQUEST["room_type"])) ? $_REQUEST["room_type"] : ""),
                        'RoomQuota' => $roomQuota,
                        'PeriodFrom' => $explodePeriodFrom[2]."-".$explodePeriodFrom[1]."-".$explodePeriodFrom[0]."T".$timeFrom,
                        'PeriodTo' => $explodePeriodTo[2]."-".$explodePeriodTo[1]."-".$explodePeriodTo[0]."T".$time,
                        'ExternalSystemCode' => $outputCode,
                        'LanguageCode' => strtoupper($_REQUEST["language"]),
                        'EMail' => $email,
                        'Phone' => $phone,
                        'Login' => $login,
                        'PromoCode' => $_REQUEST["promo_code"],
                        'GuestsQuantity' => $guests,
                        'RequestAttributes' => array(
                            'SessionID' => $_REQUEST['SessionID'],
                            'UserID' => $_REQUEST['UserID'],
                            'utm_source' => $_REQUEST['utm_source'],
                            'utm_medium' => $_REQUEST['utm_medium'],
                            'utm_campaign' => $_REQUEST['utm_campaign']
                        ),
                        'ExtraParameters' => array(
                            'ReservationCode' => $_REQUEST['embeded'] == 'Y' && $_REQUEST['embeded_uuid'] ? $_REQUEST['embeded_uuid'] : '',
                            'Employee' => '',
                            'Customer' => '',
                            'Contract' => '',
                            'IsCustomer' => false,
                            'ProfileCode' => '',
                        )
                    );

                    if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->ProfileCode)) {
                        $x['ExtraParameters']['IsCustomer'] = $_SESSION["AUTH_CLIENT_DATA"]->IsCustomer;
                        $x['ExtraParameters']['ProfileCode'] = $_SESSION["AUTH_CLIENT_DATA"]->ProfileCode;
                    }


                    $pfrom = $_REQUEST["PeriodFrom"];
                    $pto = $_REQUEST["PeriodTo"];

                    if ($arResult["AVAILABILITY_DAYS_PERIOD_RANGE_DAYS"] || $arResult["AVAILABILITY_DAYS_PERIOD_RANGE_DAYS"] === "0") {
                        $plus_minus_days = intval($arResult["AVAILABILITY_DAYS_PERIOD_RANGE_DAYS"]);
                    } else {
                        $plus_minus_days = 2;
                    }

                    $new_pf = date('Y-m-d',(strtotime($pfrom) - $plus_minus_days * 60*60*24)).'T'.$arResult["HOTEL_TIME_FROM"].':00';
                    $new_pt = date('Y-m-d',(strtotime($pto) + ($plus_minus_days + 1) * 60*60*24)).'T'.$arResult["HOTEL_TIME"].':00';

                    if($arResult["HOURS_ENABLE"]) {
                        $pto = date("Y-m-d", AddToTimeStamp(array("HH" => $_REQUEST["hour"]), MakeTimeStamp($_REQUEST["PeriodFrom"] . " " . $_REQUEST["TimeFrom"] . ":00", "DD.MM.YYYY HH:MI:SS")));
                        $new_pf = date('Y-m-d',(strtotime($pfrom) - $plus_minus_days * 60*60*24)).'T00:00:00';
                        $new_pt = date('Y-m-d',(strtotime($pto) + ($plus_minus_days + 1) * 60*60*24)).'T23:59:59';
                    }

                    $today = date('d.m.Y',time());
                    $today = strtotime($today);

                    $new_pf_2 = strtotime($_REQUEST["PeriodFrom"]) - $plus_minus_days*60*60*24;

                    if($new_pf_2 < $today) {
                        $new_pf = date('Y-m-d') . 'T' . $arResult["HOTEL_TIME_FROM"] . ':00';
                    }



                    if($arResult["HOURS_ENABLE"]) {
                        $x['PeriodFrom'] = $explodePeriodFrom[2]."-".$explodePeriodFrom[1]."-".$explodePeriodFrom[0]."T".$_REQUEST["TimeFrom"] . ":00";
                        $x['PeriodTo'] = date("Y-m-d\TH:i:s", AddToTimeStamp(array("HH" => $_REQUEST["hour"]), MakeTimeStamp($_REQUEST["PeriodFrom"] . " " . $_REQUEST["TimeFrom"] . ":00", "DD.MM.YYYY HH:MI:SS")));
                    }
                    $_SESSION["email"] = $email;
                    $_SESSION["phone"] = $phone;
                    $_SESSION["promo_code"] = $_REQUEST["promo_code"];

                    define("start_time_object", microtime(true));
                    $OnlineBookingSupport = new OnlineBookingSupport();
                    $showError = !!$errorText;
                    $res = $OnlineBookingSupport->GetAvailableRoomTypes($x, $_REQUEST["hotel_id"], trim($WSDL), 620, 420, $roomsUsed, $pfrom, $pto, $new_pf, $new_pt, $_REQUEST['embeded'] == 'Y', $SOAP_LOGIN, $SOAP_PASSWORD);

                    if($res === false && $showError) {
                        $arResult["ERROR"] = $errorText;
                    }

                    $arResult["ReservationConditions"] = str_replace("\\n", "<br/>", $res["ReservationConditions"]);
                    $arResult["AvailableRooms"] = $res["AvailableRooms"];
                    $arResult["Periods"] = $res["Periods"];
                    $arResult["AvailableRoomsByCheckInPeriods"] = $res["AvailableRoomsByCheckInPeriods"];
                    $arResult["OtherPeriods"] = $res["OtherPeriods"];
                    $arResult["HOTEL"]["ID"] = $_REQUEST["hotel_id"];

                    /*******************************************************/

                    $rtc = array();
                    foreach($arResult["AvailableRooms"] as $key => $room):
                        $rtc[] = $room['RoomTypeCode'];
                    endforeach;
                    if (!count($rtc) && count($arResult["AvailableRoomsByCheckInPeriods"])) {
                        foreach($arResult["AvailableRoomsByCheckInPeriods"] as $key => $room):
                            $rtc[] = $room['RoomTypeCode'];
                        endforeach;
                    }

                    $iblock_id_numbers = COption::GetOptionInt('gotech.hotelonline', 'NUMBER_IBLOCK_ID');
                    $NUMBERHOTEL = COption::GetOptionString('gotech.hotelonline', 'NUMBERHOTEL');
                    $NUMBERCODE = COption::GetOptionString('gotech.hotelonline', 'NUMBERCODE');

                    unset($obEl);
                    unset($dbEl);
                    unset($filter);
                    $filter = array(
                        "IBLOCK_ID" => $iblock_id_numbers,
                        $NUMBERHOTEL  => $_REQUEST["hotel_id"],
                        "ACTIVE" => "Y",
                        '!'.$NUMBERCODE => $rtc
                    );

                    $pfrom = $_REQUEST["PeriodFrom"];
                    $pto = $_REQUEST["PeriodTo"];

                    $new_pf = date('Y-m-d',(strtotime($pfrom) - $plus_minus_days * 60*60*24)).'T'.$arResult["HOTEL_TIME_FROM"].':00';
                    $new_pt = date('Y-m-d',(strtotime($pto) + ($plus_minus_days + 1) * 60*60*24)).'T'.$arResult["HOTEL_TIME"].':00';

                    if($arResult["HOURS_ENABLE"]) {
                        $pto = date("Y-m-d", AddToTimeStamp(array("HH" => $_REQUEST["hour"]), MakeTimeStamp($_REQUEST["PeriodFrom"] . " " . $_REQUEST["TimeFrom"] . ":00", "DD.MM.YYYY HH:MI:SS")));
                        $new_pf = date('Y-m-d',(strtotime($pfrom) - $plus_minus_days * 60*60*24)).'T00:00:00';
                        $new_pt = date('Y-m-d',(strtotime($pto) + ($plus_minus_days + 1) * 60*60*24)).'T23:59:59';
                    }

                    $today = date('d.m.Y',time());
                    $today = strtotime($today);

                    $new_pf_2 = strtotime($_REQUEST["PeriodFrom"]) - $plus_minus_days*60*60*24;

                    if($new_pf_2 < $today) {
                        $new_pf = date('Y-m-d') . 'T' . $arResult["HOTEL_TIME_FROM"] . ':00';
                    }

                    $sold_rtc = array();
                    if ($_REQUEST['embeded'] == 'Y') {

                    } else {
                      $dbEl = CIBlockElement::GetList(array(), $filter);

                      while ($obEl = $dbEl->GetNextElement()) {
                        $fields = $obEl->GetFields();
                        $props = $obEl->GetProperties();
                        $guests = array(
                          'Adults' => array('Quantity' => $adults),
                          'Kids' => array('Quantity' => $children, 'Age' => $age_children),
                          'RequestAttributes' => array(
                            'SessionID' => $_REQUEST['SessionID'],
                            'UserID' => $_REQUEST['UserID'],
                            'utm_source' => $_REQUEST['utm_source'],
                            'utm_medium' => $_REQUEST['utm_medium'],
                            'utm_campaign' => $_REQUEST['utm_campaign']
                          )
                        );
                        $fields['Picture'] = CFile::ResizeImageGet(
                          $fields["PREVIEW_PICTURE"],
                          array('width' => 620, 'height' => 420),
                          BX_RESIZE_IMAGE_PROPORTIONAL,
                          true
                        );
                        $fields['information_text'] = $fields['PREVIEW_TEXT'];
                        $fields['RoomsAvailable'] = 0;
                        $fields['Name'] = $fields['NAME'];
                        $fields['Name_en'] = $props['NUMBERNAMEEN']['VALUE'];
                        $fields['IsBestSeller'] = !!$props['IS_BEST_SELLER']['VALUE'];
                        if (!$fields['Name_en'])
                          $fields['Name_en'] = $fields['Name'];
                        $fields['RoomTypeCode'] = $props['NUMBERCODE']['VALUE'];

                        $fields['MaxAdults'] = $props['MAX_ADULTS']['VALUE'];
                        $fields['MaxChildren'] = $props['MAX_CHILDREN']['VALUE'];
                        $fields['data'] = htmlspecialchars(json_encode(array(
                          "Hotel" => $_REQUEST["hotel_id"],
                          "RoomType" => $props['NUMBERCODE']['VALUE'],
                          // "Customer" => "",
                          // "Contract" => "",
                          // "Agent" => "",
                          "RoomQuota" => $roomQuota,
                          "PeriodFrom" => $new_pf,
                          "PeriodTo" => $new_pt,
                          "RoomRates" => [],
                          // "RoomRate" => $roomRate,//iconv('windows-1251','UTF-8','�'),
                          "ClientType" => "",
                          "GuestsQuantity" => $guests,
                          "ClientIsAuthorized" => false,
                          // "OutputRoomsVacant" => "true",
                          // "OutputBedsVacant" => "false",
                          // "OutputRoomsRemains" => "true",
                          // "OutputBedsRemains" => "false",
                          // "OutputRoomsInQuota" => "false",
                          // "OutputBedsInQuota" => "false",
                          // "OutputRoomsChargedInQuota" => "false",
                          // "OutputBedsChargedInQuota" => "false",
                          // "OutputRoomsReserved" => "true",
                          // "OutputBedsReserved" => "true",
                          // "OutputInHouseRooms" => "false",
                          // "OutputInHouseBeds" => "false",
                          "RealPeriodFrom" => $_REQUEST["PeriodFrom"],
                          "RealPeriodTo" => $_REQUEST["PeriodTo"],
                          "ExternalSystemCode" => "1CBITRIX",
                          "LanguageCode" => "RU",
                        )));

                        if (($fields['MaxAdults'] && intval($fields['MaxAdults']) < $adults) || ($fields['MaxAdults'] && $fields['MaxChildren'] && (intval($fields['MaxAdults']) + intval($fields['MaxChildren']) < $adults + $children))) {

                        } else {
                          $arResult["AvailableRooms"][] = $fields;
                          if (count($arResult["AvailableRoomsByCheckInPeriods"])) {
                            $arResult["AvailableRoomsByCheckInPeriods"][] = $fields;
                          }
                          $sold_rtc[] = $props['NUMBERCODE']['VALUE'];
                        }

                      }
                    }


                    //������������ �������� �������
                    $exPeriodFrom = explode(".", $_REQUEST["PeriodFrom"]);
                    $exPeriodTo = explode(".", $_REQUEST["PeriodTo"]);
                    if($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] == $exPeriodTo[1]){
                        $arResult["intPeriodFrom"] = $exPeriodFrom[0];
                        $arResult["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
                    }elseif($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] != $exPeriodTo[1]){
                        $arResult["intPeriodFrom"] = $exPeriodFrom[0]." ".getMonthNameById($exPeriodFrom[1]);
                        $arResult["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
                    }elseif($exPeriodFrom[2] != $exPeriodTo[2]){
                        $arResult["intPeriodFrom"] = $exPeriodFrom[0]." ".getMonthNameById($exPeriodFrom[1])." ".$exPeriodFrom[2];
                        $arResult["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
                    }

                    $arResult["RequestedPeriodFounded"] = false;
                    foreach($arResult["Periods"] as &$periodItem){
                        //������������ �������� �������
                        $exPeriodFrom = explode(".", $periodItem["PeriodFrom"]);
                        $exPeriodTo = explode(".", $periodItem["PeriodTo"]);
                        if($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] == $exPeriodTo[1]){
                            $periodItem["intPeriodFrom"] = $exPeriodFrom[0];
                            $periodItem["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
                        }elseif($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] != $exPeriodTo[1]){
                            $periodItem["intPeriodFrom"] = $exPeriodFrom[0]." ".getMonthNameById($exPeriodFrom[1]);
                            $periodItem["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
                        }elseif($exPeriodFrom[2] != $exPeriodTo[2]){
                            $periodItem["intPeriodFrom"] = $exPeriodFrom[0]." ".getMonthNameById($exPeriodFrom[1])." ".$exPeriodFrom[2];
                            $periodItem["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
                        }
                        if($_REQUEST["PeriodFrom"]." - ".$_REQUEST["PeriodTo"] == $periodItem["PeriodFromTo"]){
                            $arResult["RequestedPeriodFounded"] = true;
                        }
                    }
                    //����� ����� ��������� ������ �������� ��������
                    $endTime = microtime(true) - $start;
                }
            }
        }
    }else{
        $arResult["SERVICES"] = $arParams["SERVICES"];
        $arResult["ROOMS"] = $arParams["ROOMS"];
        $arResult["NIGHTS"] = $arParams["NIGHTS"];
        $arResult["CURRENCY"] = htmlspecialchars_decode($arParams["CURRENCY"]);

        $arResult['SHOW_SERVICES_AS_LIST'] = false;
        $hotel_id = !empty($_REQUEST["hotel_id"]) ? $_REQUEST["hotel_id"] : $_REQUEST["hotel"];
        if(!empty($hotel_id)) {
          $res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID'), "ID" => $hotel_id), false, false,
            array("PROPERTY_SHOW_SERVICES_AS_LIST"));
          if ($hotel = $res->GetNext()) {
            $arResult['SHOW_SERVICES_AS_LIST'] = $hotel["PROPERTY_SHOW_SERVICES_AS_LIST_VALUE"] ? true : false;
          }
        }

        CModule::IncludeModule('iblock');

        $res = CIBlock::GetList(
            array(),
            array('CODE'=>'service')
        );

        $ib = $res->Fetch();

        $arResult['SECTION_ICONS'] = array();
        $arResult['SECTION_EXPAND'] = array();

        $aaa = CIBlockSection::GetList(
            array(),
            array('IBLOCK_ID'=>$ib['ID']),
            false,
            array('ID','NAME','UF_*')
        );
        while($arr = $aaa->Fetch())
        {
            $arResult['SECTION_ICONS'][$arr['ID']] = CFile::GetPath($arr['UF_SVG_ICON']);
            $arResult['SECTION_EXPAND'][$arr['ID']] = $arr['UF_EXPAND_SECTION'];
        }


        $filter = array(
            "IBLOCK_CODE" => "service",
            "PROPERTY_SERVICEHOTEL" => $hotel["ID"],
            "ACTIVE" => "Y",
        );
    }
    $this->IncludeComponentTemplate();
}
?>
