<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
if (!CModule::IncludeModule("iblock")) {
    echo GetMessage("IBLOCK_NOT_INCLUDE");
    return;
} elseif (!CModule::IncludeModule("gotech.hotelonline")) {
    ShowError(GetMessage("ONLINEBOOKING_MODULE_NOT_INSTALLED"));
    return;
} else {
    if (!function_exists('filter_var')) {
        function filter_var($value, $filter_type)
        {
            return $value;
        }
        //if you want that function to work, you need PHP version >= 5.2.0 and enable "filter" extension
    }
    if (isset($_REQUEST["error_text"]) && !empty($_REQUEST["error_text"])) {
        $arResult["ERROR"] = $_REQUEST["error_text"];
    }

    if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount) && $_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount > 0) {
        $arResult['LOGGED_USER_DISCOUNT'] = $_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount;
    }

    $APPLICATION->IncludeComponent("onlinebooking:reservation.header", "");
    $language = OnlineBookingSupport::getLanguage();
    if ($this->includeComponentLang("", $language) == NULL) {
        __IncludeLang(dirname(__FILE__) . "/lang/" . $language . "/component.php");
    }
    $iblock_id_numbers = COption::GetOptionInt('gotech.hotelonline', 'NUMBER_IBLOCK_ID');
    $NUMBERHOTEL = COption::GetOptionString('gotech.hotelonline', 'NUMBERHOTEL');
    $NUMBERCODE = COption::GetOptionString('gotech.hotelonline', 'NUMBERCODE');
    $iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
    $HotelCode1C = "";
    $choosenHotelID = COption::GetOptionInt('gotech.hotelonline', 'CHOOSEN_HOTEL_ID');
    if (isset($_REQUEST["hotel"]) && !empty($_REQUEST["hotel"]) && substr($_REQUEST["hotel"], 0, 1) != "0") {
        $hotel_id = $_REQUEST["hotel"];
    } elseif (isset($_REQUEST["hotel_code"]) && !empty($_REQUEST["hotel_code"]) && substr($_REQUEST["hotel_code"], 0, 1) != "0") {
        $hotel_id = $_REQUEST["hotel_code"];
    } elseif (isset($arParams["HOTEL_CODE"]) && !empty($arParams["HOTEL_CODE"]) && substr($arParams["HOTEL_CODE"], 0, 1) != "0") {
        $hotel_id = $arParams["HOTEL_CODE"];
    } elseif (!empty($_SESSION["HOTEL_ID"])) {
        $hotel_id = $_SESSION["HOTEL_ID"];
    } elseif (!empty($choosenHotelID)) {
        $hotel_id = $choosenHotelID;
    } else {
        $hotel_id = "";
    }
    if (!empty($hotel_id)) {
        $arFilter = array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y",
            array(
                "LOGIC" => "OR",
                array("ID" => $hotel_id),
                array("PROPERTY_HOTEL_CODE" => $hotel_id),
            ),
        );
    } else {
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
            "DETAIL_PICTURE", //new
            "PROPERTY_HOTEL_NAME_EN",
            "PROPERTY_HOTEL_ADDRESS_RU",
            "PROPERTY_HOTEL_ADDRESS_EN",
            "PROPERTY_HOTEL_PHONE",
            "PROPERTY_HOTEL_FAX",
            "PROPERTY_HOTEL_MAIL",
            "PROPERTY_HOTEL_WALK_RU",
            "PROPERTY_HOTEL_WALK_EN",
            "PROPERTY_HOTEL_TIME",
            "PROPERTY_HOTEL_TIME_FROM",
            "PROPERTY_HOTEL_CODE",
            "PROPERTY_HOTEL_MAX_ADULT",
            "PROPERTY_HOTEL_MAX_CHILDREN",
            "PROPERTY_HOTEL_ROOM_QUOTA",
            "PROPERTY_HOTEL_ANNULATION_STATUS",
            "PROPERTY_HOTEL_CLIENT_CAN_CHANGE_RESERVATION",
            "PROPERTY_DO_ANNULATION_WITH_PREPAYMENT",
            "PROPERTY_SHOW_FREE_CANCEL",
            "PROPERTY_DATE_OF_BIRTH_NECESSARY",
            "PROPERTY_BIRTHDAY",
            "PROPERTY_HOURS_ENABLE",
            "PROPERTY_PAYMENT_METHOD_TEXT",
            "PROPERTY_PAYMENT_METHOD_TEXT_EN",
            "PROPERTY_INPUT_GUEST_PASSPORT_DATA",
            "PROPERTY_INPUT_GUEST_ADDRESS",
            "PREVIEW_TEXT",
            "DETAIL_TEXT",
            "PROPERTY_SOAP_LOGIN",
            "PROPERTY_SOAP_PASSWORD")
    );
    while ($hotel = $hotels->GetNext()) {

        $dimg = CFile::ResizeImageGet($hotel['DETAIL_PICTURE'], array('width' => 127, 'height' => 83), BX_RESIZE_IMAGE_EXACT, true);

        $arResult['HotelName'] = $language == 'en' ? $hotel['PROPERTY_HOTEL_NAME_EN_VALUE'] : $hotel['NAME'];

        if ($language == "ru")
            $hotel_wsdl[] = array(
                "ID" => $hotel["ID"],
                "CODE" => $hotel["PROPERTY_HOTEL_CODE_VALUE"],
                "NAME" => $hotel["~NAME"],
                "PICTURE" => $dimg,
                "WSDL" => trim($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]) ? $hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"] : trim(COption::GetOptionString('gotech.hotelonline', 'AddressWebservice')),
                "OUTPUT_CODE" => trim($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]) ? $hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"] : COption::GetOptionString('gotech.hotelonline', 'OutputCode'),
                "HOTEL_ADDRESS" => $hotel["PROPERTY_HOTEL_ADDRESS_RU_VALUE"],
                "HOTEL_PHONE" => $hotel["PROPERTY_HOTEL_PHONE_VALUE"],
                "HOTEL_FAX" => $hotel["PROPERTY_HOTEL_FAX_VALUE"],
                "HOTEL_MAIL" => $hotel["PROPERTY_HOTEL_MAIL_VALUE"],
                "HOTEL_WALK" => $hotel["PROPERTY_HOTEL_WALK_RU_VALUE"],
                "HOTEL_TIME" => $hotel["PROPERTY_HOTEL_TIME_VALUE"],
                "HOTEL_TIME_FROM" => $hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"],
                "HOTEL_MAX_ADULT" => $hotel["PROPERTY_HOTEL_MAX_ADULT_VALUE"],
                "HOTEL_MAX_CHILDREN" => $hotel["PROPERTY_HOTEL_MAX_CHILDREN_VALUE"],
                "ROOM_QUOTA" => $hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"],
                "ANNULATION_STATUS_CODE" => $hotel["PROPERTY_HOTEL_ANNULATION_STATUS_VALUE"],
                "DO_CLIENT_CAN_CHANGE_RESERVATION" => $hotel["PROPERTY_HOTEL_CLIENT_CAN_CHANGE_RESERVATION_VALUE"],
                "BAN_TO_CHANGE_RESERVATION_DAYS" => $hotel["PROPERTY_BAN_TO_CHANGE_RESERVATION_DAYS_VALUE"],
                "RESERVATIONS_CONDITIONS" => $hotel["PREVIEW_TEXT"],
                "DO_ANNULATION_WITH_PREPAYMENT" => $hotel["PROPERTY_DO_ANNULATION_WITH_PREPAYMENT_VALUE"],
                "SHOW_FREE_CANCEL" => $hotel["PROPERTY_SHOW_FREE_CANCEL_VALUE"],
                "PROPERTY_DATE_OF_BIRTH_NECESSARY" => $hotel["PROPERTY_DATE_OF_BIRTH_NECESSARY_VALUE"],
                "PROPERTY_BIRTHDAY" => $hotel["PROPERTY_BIRTHDAY_VALUE"],
                "PAYMENT_METHOD_TEXT" => $hotel["PROPERTY_PAYMENT_METHOD_TEXT_VALUE"],
            );
        elseif ($language == "en")
            $hotel_wsdl[] = array(
                "ID" => $hotel["ID"],
                "CODE" => $hotel["PROPERTY_HOTEL_CODE_VALUE"],
                "NAME" => $hotel["PROPERTY_HOTEL_NAME_EN_VALUE"],
                "PICTURE" => $dimg,
                "WSDL" => trim($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]) ? $hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"] : trim(COption::GetOptionString('gotech.hotelonline', 'AddressWebservice')),
                "OUTPUT_CODE" => trim($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]) ? $hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"] : COption::GetOptionString('gotech.hotelonline', 'OutputCode'),
                "HOTEL_ADDRESS" => $hotel["PROPERTY_HOTEL_ADDRESS_EN_VALUE"],
                "HOTEL_PHONE" => $hotel["PROPERTY_HOTEL_PHONE_VALUE"],
                "HOTEL_FAX" => $hotel["PROPERTY_HOTEL_FAX_VALUE"],
                "HOTEL_MAIL" => $hotel["PROPERTY_HOTEL_MAIL_VALUE"],
                "HOTEL_WALK" => $hotel["PROPERTY_HOTEL_WALK_EN_VALUE"],
                "HOTEL_TIME" => $hotel["PROPERTY_HOTEL_TIME_VALUE"],
                "HOTEL_MAX_ADULT" => $hotel["PROPERTY_HOTEL_MAX_ADULT_VALUE"],
                "HOTEL_MAX_CHILDREN" => $hotel["PROPERTY_HOTEL_MAX_CHILDREN_VALUE"],
                "ROOM_QUOTA" => $hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"],
                "ANNULATION_STATUS_CODE" => $hotel["PROPERTY_HOTEL_ANNULATION_STATUS_VALUE"],
                "DO_CLIENT_CAN_CHANGE_RESERVATION" => $hotel["PROPERTY_HOTEL_CLIENT_CAN_CHANGE_RESERVATION_VALUE"],
                "BAN_TO_CHANGE_RESERVATION_DAYS" => $hotel["PROPERTY_BAN_TO_CHANGE_RESERVATION_DAYS_VALUE"],
                "RESERVATIONS_CONDITIONS" => $hotel["DETAIL_TEXT"],
                "DO_ANNULATION_WITH_PREPAYMENT" => $hotel["PROPERTY_DO_ANNULATION_WITH_PREPAYMENT_VALUE"],
                "SHOW_FREE_CANCEL" => $hotel["PROPERTY_SHOW_FREE_CANCEL_VALUE"],
                "PROPERTY_DATE_OF_BIRTH_NECESSARY" => $hotel["PROPERTY_DATE_OF_BIRTH_NECESSARY_VALUE"],
                "PROPERTY_BIRTHDAY" => $hotel["PROPERTY_BIRTHDAY_VALUE"],
                "PAYMENT_METHOD_TEXT" => $hotel["PROPERTY_PAYMENT_METHOD_TEXT_EN_VALUE"],
            );
        $arResult["HOURS_ENABLE"] = $hotel["PROPERTY_HOURS_ENABLE_VALUE"] ? true : false;
        $arResult['INPUT_GUEST_PASSPORT_DATA'] = !!$hotel['PROPERTY_INPUT_GUEST_PASSPORT_DATA_VALUE'];
        $arResult['INPUT_GUEST_ADDRESS'] = !!$hotel['PROPERTY_INPUT_GUEST_ADDRESS_VALUE'];
        if(!empty($hotel["PROPERTY_SOAP_LOGIN_VALUE"]))
            $SOAP_LOGIN = trim($hotel["PROPERTY_SOAP_LOGIN_VALUE"]);
        else
            $SOAP_LOGIN = "";
        if(!empty($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]))
            $SOAP_PASSWORD = trim($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]);
        else
            $SOAP_PASSWORD = "";
    }
    $arResult["HOTELS"] = $hotel_wsdl;


    $arResult["DOC_TYPES"] = array();
    $arResult["DOC_PHOTO_TYPES"] = array();
    $res = CIBlockElement::GetList(array("SORT" => "ASC"), array("IBLOCK_CODE" => "document_types", "ACTIVE" => "Y"), false, false, array("NAME", "PROPERTY_DOC_CODE", "PROPERTY_DOC_NAMEEN", "PROPERTY_DOC_PHOTO_TYPES"));
    while ($result = $res->GetNext()) {
        if ($language == "ru") {
            if (!empty($result["NAME"]))
                $arResult["DOC_TYPES"][$result["PROPERTY_DOC_CODE_VALUE"]] = $result["NAME"];
        } elseif ($language == "en") {
            if (!empty($result["PROPERTY_DOC_NAMEEN_VALUE"]))
                $arResult["DOC_TYPES"][$result["PROPERTY_DOC_CODE_VALUE"]] = $result["PROPERTY_DOC_NAMEEN_VALUE"];
        }
        if (!isset($arResult["DOC_PHOTO_TYPES"][$result["PROPERTY_DOC_CODE_VALUE"]])) {
            $arResult["DOC_PHOTO_TYPES"][$result["PROPERTY_DOC_CODE_VALUE"]] = array();
        }
        if (isset($result["PROPERTY_DOC_PHOTO_TYPES_VALUE"])) {
            $arResult["DOC_PHOTO_TYPES"][$result["PROPERTY_DOC_CODE_VALUE"]][] = $result["PROPERTY_DOC_PHOTO_TYPES_VALUE"];
        }
    }

    // ��������� ������ ������ �� ����������� ���������
    $arResult["DO_EXPRESS"] = false;
    if (CModule::IncludeModule("gotech.expresscheckin")) {
        $OnlineBookingSupport = new OnlineBookingSupport();
        $res = $OnlineBookingSupport->checkVersion('1.1.0');
        $arResult["DO_EXPRESS"] = !$res;
    }

    $arResult["ROOM_QUOTA"] = "";
    $arResult["HOTEL_MAX_ADULT"] = "";
    $arResult["HOTEL_MAX_CHILDREN"] = "";
    $arResult["ANNULATION_STATUS_CODE"] = "";
    $arResult["BAN_TO_CHANGE_RESERVATION_DAYS"] = 0;
    $arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] = "";
    $arResult["SHOW_FREE_CANCEL"] = "";
    $arResult["PROPERTY_DATE_OF_BIRTH_NECESSARY"] = 0;
    $arResult["PROPERTY_BIRTHDAY"] = 0;
    foreach ($arResult["HOTELS"] as $hotel) {
        if (
            (
                ($hotel["ID"] == $_REQUEST["hotel_code"] && substr($_REQUEST["hotel_code"], 0, 1) != "0") ||
                (substr($_REQUEST["hotel_code"], 0, 1) == "0" && $hotel["CODE"] == $_REQUEST["hotel_code"])
            )
            ||
            (
                ($hotel["ID"] == $_REQUEST["hotel"] && substr($_REQUEST["hotel"], 0, 1) != "0") ||
                (substr($_REQUEST["hotel"], 0, 1) == "0" && $hotel["CODE"] == $_REQUEST["hotel"])
            )
            ||
            (
                ($hotel["ID"] == $arParams["HOTEL_CODE"] && substr($arParams["HOTEL_CODE"], 0, 1) != "0") ||
                (substr($arParams["HOTEL_CODE"], 0, 1) == "0" && $hotel["CODE"] == $arParams["HOTEL_CODE"])
            )
            ||
            (
                ($hotel["ID"] == $_SESSION["HOTEL_ID"] && substr($_SESSION["HOTEL_ID"], 0, 1) != "0") ||
                (substr($_SESSION["HOTEL_ID"], 0, 1) == "0" && $hotel["CODE"] == $_SESSION["HOTEL_ID"])
            )
        ) {
            $OutputCode = $hotel["OUTPUT_CODE"];
            $WSDL = $hotel["WSDL"];
            $arResult["BOOKING_HOTEL"] = $hotel;
            $HotelCode1C = $hotel["CODE"];
            $arResult["HOTEL_MAX_ADULT"] = $hotel["HOTEL_MAX_ADULT"];
            $arResult["HOTEL_MAX_CHILDREN"] = $hotel["HOTEL_MAX_CHILDREN"];
            $arResult["ROOM_QUOTA"] = $hotel["ROOM_QUOTA"];
            $arResult["ANNULATION_STATUS_CODE"] = $hotel["ANNULATION_STATUS_CODE"];
            $arResult["BAN_TO_CHANGE_RESERVATION_DAYS"] = $hotel["BAN_TO_CHANGE_RESERVATION_DAYS"] ? intval($hotel["BAN_TO_CHANGE_RESERVATION_DAYS"]) : 0;
            $arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] = $hotel["DO_CLIENT_CAN_CHANGE_RESERVATION"] ? true : false;
            $arResult["SHOW_FREE_CANCEL"] = $hotel["PROPERTY_SHOW_FREE_CANCEL"] ? true : false;
            if ($hotel["PROPERTY_DATE_OF_BIRTH_NECESSARY"]) {
                $arResult["PROPERTY_DATE_OF_BIRTH_NECESSARY"] = 1;
            }
            if ($hotel["PROPERTY_BIRTHDAY"]) {
                $arResult["PROPERTY_BIRTHDAY"] = 1;
            }
            $arResult["PAYMENT_METHOD_TEXT"] = $hotel["PAYMENT_METHOD_TEXT"];
            $APPLICATION->SetTitle($hotel["NAME"] . " " . GetMessage("TITLE_MODULE"));
        } elseif (!isset($_REQUEST["hotel_code"]))
            $APPLICATION->SetTitle($arResult["HOTELS"][0]["NAME"] . " " . GetMessage("TITLE_MODULE"));
    }
    $arResult["WSDL"] = $WSDL;
    $arResult["HotelCode"] = $HotelCode1C;
    $arResult["OutputCode"] = $OutputCode;


    if (isUserAgent()) {
        $paymentMethod = "";
    } else {
        $paymentMethod = COption::GetOptionString('gotech.hotelonline', 'paymentMethod', 'CC');
        if (!$paymentMethod) {
            $paymentMethod = "CC";
        }
    }
    $arResult["PaymentMethod"] = $paymentMethod;
    $isFromPayment = false;
    if (isset($arParams["UUID"]) && !empty($arParams["UUID"]) && strlen($arParams["UUID"]) == 36 || (isset($_REQUEST["uuid"]) && !empty($_REQUEST["uuid"]) && strlen($_REQUEST["uuid"]) == 36)) {
        $isFromPayment = true;
        if (isset($arParams["UUID"]) && !empty($arParams["UUID"]) && strlen($arParams["UUID"]) == 36) {
            $uuid = $arParams["UUID"];
        } else {
            $uuid = $_REQUEST["uuid"];
        }
    }

    $arResult["IS_FROM_PAYMENT"] = $isFromPayment;
    if ((isset($_REQUEST["search"]) && $_REQUEST["search"] == "Y") || ($isFromPayment)) {
        if ($isFromPayment) {
            sleep(2);
        }
        $ar_group = $USER->GetUserGroup($USER->GetID());
        if (!$isFromPayment) {
            if (empty($_REQUEST["reservation"]))
                $arResult["ERROR"] = GetMessage("NUMBER_RESERVATION_ALWAYS");
            elseif (empty($_REQUEST["data"])) {
                if (!$USER->IsAuthorized() || !in_array(COption::GetOptionint('gotech.hotelonline', 'USER_AGENT_GROUP'), $ar_group)) {
                    $arResult["ERROR"] = GetMessage("PHONE_EMAIL_ALWAYS");
                }
            }
        }
        if (empty($arResult["ERROR"])) {
            ini_set("soap.wsdl_cache_enabled", 0);
            ini_set("soap.wsdl_cache_ttl", "0");
            $soap_params = array('trace' => 1);
            if (!empty($SOAP_LOGIN) && !empty($SOAP_PASSWORD)) {
                $soap_params['login'] = $SOAP_LOGIN;
                $soap_params['password'] = $SOAP_PASSWORD;
            }
            $soapclient = new SoapClient(trim($WSDL), $soap_params); // with auth
            if ($USER->IsAuthorized())
                $login = CUser::GetLogin();
            else $login = "";
            $email = "";
            $phone = "";
            if (!$isFromPayment) {
                if (filter_var($_REQUEST["data"], FILTER_VALIDATE_EMAIL)) {
                    $email = $_REQUEST["data"];
                    $phone = "";
                } else {
                    $email = "";
                    $phone = $_REQUEST["data"];
                }
            }
            $arResult["login"] = $login;
            $query = array(
                "EMail" => $email,
                "Phone" => $phone,
                "Login" => $login,
                "Hotel" => $HotelCode1C,
                "GuestGroupCode" => $isFromPayment ? $uuid : ($_REQUEST["reservation"] ? $_REQUEST["reservation"] : $_SESSION["search_reservation"]),
                "ExternalSystemCode" => $OutputCode,
                "LanguageCode" => strtoupper($language)
            );
            $result = $soapclient->GetGroupReservationDetails($query);

            if ($result && $_REQUEST["search"] == "Y") {
                $_SESSION["search_email"] = $email;
                $_SESSION["search_phone"] = $phone;
                $_SESSION["search_reservation"] = $isFromPayment ? "" : $_REQUEST["reservation"];
            }
            if (!empty($result->return->ErrorDescription)) {
                if (isset($result->return->GuestGroup) && !empty($result->return->GuestGroup)) {
                    $arResult["ERROR"] = $result->return->ErrorDescription;
                } else {
                    $arResult["ERROR"] = GetMessage("RESERVATION_IS_NOT_FOUND");
                }
            } else {
                if ($result->return->HotelCode == $arResult["BOOKING_HOTEL"]["CODE"] or !isset($result->return->HotelCode)) {
                    if (!is_array($result->return->ExternalReservationStatusRow))
                        $result_array[] = $result->return->ExternalReservationStatusRow;
                    else
                        $result_array = $result->return->ExternalReservationStatusRow;
                    $minCheckInDate = "";
                    $maxCheckOutDate = "";
                    $maxDuration = 0;

                    $lastReservationNumber = "";
                    foreach ($result_array as $g) {
                        if ($g->Duration > $maxDuration) {
                            $maxDuration = $g->Duration;
                        }
                        if ($lastReservationNumber != $g->ReservationNumber) {
                            $minCheckInDate = "";
                            $maxCheckOutDate = "";
                            $lastReservationNumber = $g->ReservationNumber;
                        }
                        if (!$g->ExtReservationCode) {
                            $arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] = false;
                        }

                        if (!isset($guests[$g->ReservationNumber]["UUID"]) || empty($guests[$g->ReservationNumber]["UUID"])) {
                            $guests[$g->ReservationNumber]["UUID"] = $g->UUID;
                        }
                        $guests[$g->ReservationNumber]["RoomTypeCode"] = $g->RoomTypeCode;
                        $guests[$g->ReservationNumber]["RoomRateDescription"] = $g->RoomRateDescription;
                        $guests[$g->ReservationNumber]["RoomRateCode"] = $g->RoomRateCode;
                        $guests[$g->ReservationNumber]["RoomQuotaCode"] = $g->RoomQuotaCode;


                        // DO REQUEST TO BITRIX
                        $filter = array(
                            "IBLOCK_ID" => $iblock_id_numbers,
                            $NUMBERHOTEL => $hotel_id,
                            "ACTIVE" => "Y",
                            $NUMBERCODE => $g->RoomTypeCode
                        );

                        $dbEl = CIBlockElement::GetList(array(), $filter);
                        if ($obEl = $dbEl->GetNextElement()) {
                            unset($fields);
                            unset($props);
                            $fields = $obEl->GetFields();
                            $props = $obEl->GetProperties();

                            if ($language == 'ru') {
                                if (COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU') == "NAME" || !COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU'))
                                    $guests[$g->ReservationNumber]["RoomTypeDescription"] = $fields["NAME"];
                                else $guests[$g->ReservationNumber]["RoomTypeDescription"] = $props[COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU')]["VALUE"];
                                if (!$guests[$g->ReservationNumber]["RoomTypeDescription"])
                                    $guests[$g->ReservationNumber]["RoomTypeDescription"] = str_replace("\"", "'", $g->RoomTypeDescription);
                                if (!empty($props["NUMBERLINK"]["VALUE"])) {
                                    $guests[$g->ReservationNumber]["RoomTypeLink"] = $props["NUMBERLINK"]["VALUE"];
                                } else $guests[$g->ReservationNumber]["RoomTypeLink"] = "";


                            } else {
                                if (!empty($props["NUMBERNAMEEN"]["VALUE"])) {
                                    $guests[$g->ReservationNumber]["RoomTypeDescription"] = $props["NUMBERNAMEEN"]["VALUE"];
                                }
                                if (!$guests[$g->ReservationNumber]["RoomTypeDescription"])
                                    $guests[$g->ReservationNumber]["RoomTypeDescription"] = $room["Name"];
                                if (!$guests[$g->ReservationNumber]["RoomTypeDescription"])
                                    $guests[$g->ReservationNumber]["RoomTypeDescription"] = str_replace("\"", "'", $g->RoomTypeDescription);
                                if (!empty($props["NUMBERLINK_EN"]["VALUE"])) {
                                    $guests[$g->ReservationNumber]["RoomTypeLink"] = $props["NUMBERLINK_EN"]["VALUE"];
                                } else $guests[$g->ReservationNumber]["RoomTypeLink"] = "";
                            }
                        } else {
                            if ($language == 'ru') {
                                $guests[$g->ReservationNumber]["RoomTypeDescription"] = str_replace("\"", "'", $g->RoomTypeDescription);
                                $guests[$g->ReservationNumber]["RoomTypeLink"] = OnlineBookingSupport::getProtocol() . str_replace('http://', '', $g->RoomTypeInfoLink);
                            } else {
                                $guests[$g->ReservationNumber]["RoomTypeDescription"] = str_replace("\"", "'", $g->RoomTypeDescription);
                                $guests[$g->ReservationNumber]["RoomTypeLink"] = OnlineBookingSupport::getProtocol() . str_replace('http://', '', $g->RoomTypeInfoLink);
                            }
                        }
                        $currCheckInDate = OnlineBookingSupport::getDateFromFormat($g->CheckInDate);
                        if (!$minCheckInDate || strtotime($minCheckInDate) >= strtotime($currCheckInDate)) {
                            $minCheckInDate = $currCheckInDate;
                            if ($arResult['HOURS_ENABLE']) {
                                $guests[$g->ReservationNumber]["CheckInDate"] = OnlineBookingSupport::getDateFromFormatWithTime($g->CheckInDate);
                            } else {
                                $guests[$g->ReservationNumber]["CheckInDate"] = $minCheckInDate;
                            }
                            $guests[$g->ReservationNumber]["CheckInDateIn1CFormat"] = $g->CheckInDate;
                        }
                        $currCheckOutDate = OnlineBookingSupport::getDateFromFormat($g->CheckOutDate);
                        if (!$maxCheckOutDate || strtotime($maxCheckOutDate) <= strtotime($currCheckOutDate)) {
                            $maxCheckOutDate = $currCheckOutDate;
                            if ($arResult['HOURS_ENABLE']) {
                                $guests[$g->ReservationNumber]["CheckOutDate"] = OnlineBookingSupport::getDateFromFormatWithTime($g->CheckOutDate);
                            } else {
                                $guests[$g->ReservationNumber]["CheckOutDate"] = $maxCheckOutDate;
                            }
                            $guests[$g->ReservationNumber]["CheckOutDateIn1CFormat"] = $g->CheckOutDate;
                        }
                        $guests[$g->ReservationNumber]["Customer"] = $g->Customer;
                        $guests[$g->ReservationNumber]["CustomerLegacyName"] = $g->CustomerLegacyName;
                        $guests[$g->ReservationNumber]["CustomerLegacyAddress"] = $g->CustomerLegacyAddress;
                        $guests[$g->ReservationNumber]["CustomerTIN"] = $g->CustomerTIN;
                        $guests[$g->ReservationNumber]["CustomerKPP"] = $g->CustomerKPP;
                        $guests[$g->ReservationNumber]["CustomerEMail"] = $g->CustomerEMail;
                        $guests[$g->ReservationNumber]["CustomerPhone"] = $g->CustomerPhone;
                        $guests[$g->ReservationNumber]["Contract"] = $g->Contract;
                        if (!empty($g->ExtraServices->ExtraServiceRow)) {
                            if (is_array($g->ExtraServices->ExtraServiceRow))
                                $ExtraServiceRow = $g->ExtraServices->ExtraServiceRow;
                            else
                                $ExtraServiceRow[] = $g->ExtraServices->ExtraServiceRow;
                            foreach ($ExtraServiceRow as $row) {
                                $arResult["TRANSFER"][] = ($row->ServiceDescription) . "; " . (empty($row->Remarks) ? "" : $row->Remarks . ";") . ($row->Price) . " " . ($row->Currency);
                            }
                        }

                        $guests[$g->ReservationNumber]["Picture"] = CFile::ResizeImageGet($fields['PREVIEW_PICTURE'], array('width' => 127, 'height' => 121), BX_RESIZE_IMAGE_EXACT, true);


                    }

                    $date_in0 = '';
                    $date_out0 = '';

                    $room_ind = 0;
                    $guest_ind = 0;

                    foreach ($guests as $key => $value) {
                        foreach ($result_array as $guest) {
                            if ($key == $guest->ReservationNumber &&
                                $value["RoomTypeCode"] == $guest->RoomTypeCode) {
                                $datetime1 = new DateTime(OnlineBookingSupport::getDateFromFormat($guest->GuestBirthDate));
                                $datetime2 = new DateTime(OnlineBookingSupport::getDateFromFormat($guest->CheckInDate));

                                if (!$date_in0)
                                    $date_in0 = new DateTime(OnlineBookingSupport::getDateFromFormat($guest->CheckInDate));
                                if (!$date_out0)
                                    $date_out0 = new DateTime(OnlineBookingSupport::getDateFromFormat($guest->CheckOutDate));


                                $add_text = "";
                                if (($value["CheckInDate"] != OnlineBookingSupport::getDateFromFormat($guest->CheckInDate) &&
                                        ($arResult["HOURS_ENABLE"] && $value["CheckInDate"] != OnlineBookingSupport::getDateFromFormat($guest->CheckInDate) . " " . substr($g->CheckInDate, strpos($g->CheckInDate, "T") + 1, 5))
                                    ) || ($value["CheckOutDate"] != OnlineBookingSupport::getDateFromFormat($guest->CheckOutDate) ||
                                        ($arResult["HOURS_ENABLE"] && $value["CheckOutDate"] != OnlineBookingSupport::getDateFromFormat($guest->CheckOutDate) . " " . substr($g->CheckOutDate, strpos($g->CheckOutDate, "T") + 1, 5)))) {
                                    $add_text = "(" . OnlineBookingSupport::getDateFromFormat($guest->CheckInDate) . " " . $arResult['HOTELS'][0]['HOTEL_TIME_FROM'] . " - " . OnlineBookingSupport::getDateFromFormat($guest->CheckOutDate) . " " . $arResult['HOTELS'][0]['HOTEL_TIME'] . ")";
                                }

                                $extraServices = array();
                                if (isset($guest->ExtraServices->ExtraServiceRow)) {
                                    if (is_array($guest->ExtraServices->ExtraServiceRow)) {
                                        array_merge($extraServices, $guest->ExtraServices->ExtraServiceRow);
                                    } else {
                                        $extraServices[] = $guest->ExtraServices->ExtraServiceRow;
                                    }
                                }

                                if (isset($guest->ExtraCharges->ChargeExtraServiceRow)) {
                                    if (is_array($guest->ExtraCharges->ChargeExtraServiceRow)) {
                                        array_merge($extraServices, $guest->ExtraCharges->ChargeExtraServiceRow);
                                    } else {
                                        $extraServices[] = $guest->ExtraCharges->ChargeExtraServiceRow;
                                    }
                                }

                                if (isset($guest->UpgradeAvailable) && isset($guest->UpgradeAvailable->UpgradeRoomTypeCode)) {
                                    $upgradeRoom = array();
                                    $upgradeRoom["Amount"] = $guest->UpgradeAvailable->UpgradeAmount;
                                    $upgradeRoom["RoomTypeCode"] = $guest->UpgradeAvailable->UpgradeRoomTypeCode;
                                    $upgradeRoom["RoomTypeDescription"] = str_replace("\"", "'", $guest->UpgradeAvailable->UpgradeRoomTypeDescription);
                                    $upgradeRoom["RoomRateCode"] = $guest->UpgradeAvailable->UpgradeRoomRateCode;
                                    if (!is_array($guest->UpgradeAvailable->UpgradeAccommodationTypesList->AccommodationType))
                                        $upgradeRoom["AccommodationTypesList"] = array($guest->UpgradeAvailable->UpgradeAccommodationTypesList->AccommodationType);
                                    else
                                        $upgradeRoom["AccommodationTypesList"] = $guest->UpgradeAvailable->UpgradeAccommodationTypesList->AccommodationType;
                                    $upgradeRoom["RoomTypeLink"] = "";
                                    $upgradeRoom["information_text"] = "";
                                    $upgradeRoom["Picture"] = array();

                                    $filter = array(
                                        "IBLOCK_ID" => $iblock_id_numbers,
                                        $NUMBERHOTEL => $hotel_id,
                                        "ACTIVE" => "Y",
                                        $NUMBERCODE => trim($guest->UpgradeAvailable->UpgradeRoomTypeCode)
                                    );

                                    $dbEl = CIBlockElement::GetList(array(), $filter);
                                    if ($obEl = $dbEl->GetNextElement()) {
                                        unset($fields);
                                        unset($props);
                                        $fields = $obEl->GetFields();
                                        $props = $obEl->GetProperties();

                                        $upgradeRoom["Picture"] = CFile::ResizeImageGet($fields['PREVIEW_PICTURE'], array('width' => 60, 'height' => 60), BX_RESIZE_IMAGE_EXACT, true);
                                        $upgradeRoom["MainPicture"] = CFile::ResizeImageGet($fields['PREVIEW_PICTURE'], array('width' => 620, 'height' => 420), BX_RESIZE_IMAGE_EXACT, true);

                                        if ($language == 'ru') {
                                            if (COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU') == "NAME" || !COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU'))
                                                $upgradeRoom["RoomTypeDescription"] = $fields["NAME"];
                                            else $upgradeRoom["RoomTypeDescription"] = $props[COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU')]["VALUE"];
                                            if (!empty($props["NUMBERLINK"]["VALUE"])) {
                                                $upgradeRoom["RoomTypeLink"] = $props["NUMBERLINK"]["VALUE"];
                                            } else $upgradeRoom["RoomTypeLink"] = "";
                                            $upgradeRoom["information_text"] = $fields["PREVIEW_TEXT"];
                                        } else {
                                            if (!empty($props["NUMBERNAMEEN"]["VALUE"])) {
                                                $upgradeRoom["RoomTypeDescription"] = $props["NUMBERNAMEEN"]["VALUE"];
                                            }
                                            if (!empty($props["NUMBERLINK_EN"]["VALUE"])) {
                                                $upgradeRoom["RoomTypeLink"] = $props["NUMBERLINK_EN"]["VALUE"];
                                            } else $upgradeRoom["RoomTypeLink"] = "";
                                            $upgradeRoom["information_text"] = $fields["DETAIL_TEXT"];
                                        }
                                    }
                                    $guests[$key]["UpgradeRoom"] = $upgradeRoom;
                                }

                                $interval = $datetime1->diff($datetime2);
                                $guest_element = array(
                                    "Id" => $room_ind . "_" . $guest_ind,
                                    "Code" => $guest->GuestCode,
                                    "FullName" => $guest->GuestFullName,
                                    "FirstName" => $guest->GuestFirstName,
                                    "SecondName" => $guest->GuestSecondName,
                                    "LastName" => $guest->GuestLastName,
                                    "BirthDate" => OnlineBookingSupport::getDateFromFormat($guest->GuestBirthDate),
                                    "IsChild" => $interval->y >= 18 ? false : true,
                                    "Age" => $interval->y >= 18 ? 0 : $interval->y,
                                    "GUID" => $guest->ExtReservationCode,
                                    "RoomQuota" => $guest->RoomQuotaCode,
                                    "AccTypeCode" => $guest->AccommodationTypeCode,
                                    "ResStatusCode" => $guest->ReservationStatusCode,
                                    "ExtraServices" => $extraServices,
                                    "AddText" => $add_text,
                                    "ClientIdentityDocumentType" => $guest->GuestIdentityDocumentType ? $guest->GuestIdentityDocumentType : "",
                                    "ClientIdentityDocumentNumber" => $guest->GuestIdentityDocumentNumber ? $guest->GuestIdentityDocumentNumber : "",
                                    "ClientIdentityDocumentSeries" => $guest->GuestIdentityDocumentSeries ? $guest->GuestIdentityDocumentSeries : "",
                                    "ClientIdentityDocumentUnitCode" => $guest->GuestIdentityDocumentUnitCode ? $guest->GuestIdentityDocumentUnitCode : "",
                                    "ClientIdentityDocumentIssuedBy" => $guest->GuestIdentityDocumentIssuedBy ? $guest->GuestIdentityDocumentIssuedBy : "",
                                    "ClientIdentityDocumentIssueDate" => $guest->GuestIdentityDocumentIssueDate ? OnlineBookingSupport::getDateFromFormat($guest->GuestIdentityDocumentIssueDate) : "",
                                    "Address" => $guest->GuestAddress ? $guest->GuestAddress : "",
                                );
                                $guests[$key]["Guests"][] = $guest_element;
                                $guests[$key]["Cost"] = $guests[$key]["Cost"] + $guest->Sum;
                                if ($guest->CurrencyCode == 643) {
                                    $guests[$key]["Currency"] = "<span class='gotech_ruble'>a</span>";
                                } elseif ($guest->CurrencyCode == 840) {
                                    $guests[$key]["Currency"] = '$';
                                } elseif ($guest->CurrencyCode == 978) {
                                    $guests[$key]["Currency"] = '&euro;';
                                } elseif ($guest->CurrencyCode == 417) {
                                    $guests[$key]["Currency"] = 'KGS';
                                } else {
                                    $guests[$key]["Currency"] = "<span class='gotech_ruble'>a</span>";
                                }
                            }
                            $guest_ind++;
                        }
                        $room_ind++;
                    }
                    $arResult["BOOKING"] = $guests;

                    $arResult["BonusesLimit"] = isset($result->return->BonusesLimit) ? $result->return->BonusesLimit : null;

                    if ($result->return->CurrencyCode == 643) {
                        $arResult["Currency"] = "<span class='gotech_ruble'>a</span>";
                        $arResult["CurrencyPayCode"] = "RUB";
                    } elseif ($result->return->CurrencyCode == 840) {
                        $arResult["Currency"] = '$';
                        $arResult["CurrencyPayCode"] = "USD";
                    } elseif ($result->return->CurrencyCode == 978) {
                        $arResult["Currency"] = '&euro;';
                        $arResult["CurrencyPayCode"] = "EUR";
                    } elseif ($result->return->CurrencyCode == 417) {
                        $arResult["Currency"] = 'KGS';
                        $arResult["CurrencyPayCode"] = "KGS";
                    } else {
                        $arResult["Currency"] = "<span class='gotech_ruble'>a</span>";
                        $arResult["CurrencyPayCode"] = "RUB";
                    }

                    /*?><pre><?=var_dump($result)?></pre><?/**/

                    $arResult["HotelCode"] = $result->return->HotelCode;
                    $arResult["GuestGroup"] = $result->return->GuestGroup;
                    $arResult["ExtGuestGroupCode"] = $result->return->ExtGuestGroupCode;
                    $arResult["CurrencyCode"] = $result->return->CurrencyCode;
                    $arResult["GuestCode"] = $result->return->GuestCode;
                    $arResult["GuestFullName"] = $result->return->GuestFullName;
                    $arResult["GuestFirstName"] = $result->return->GuestFirstName;
                    $arResult["GuestLastName"] = $result->return->GuestLastName;
                    $arResult["TotalSum"] = $result->return->TotalSum;
                    $arResult["BalanceAmount"] = $result->return->BalanceAmount;
                    $arResult["AlreadyPaid"] = $result->return->TotalSum - $result->return->BalanceAmount;
                    $arResult["TotalSumPresentation"] = $result->return->TotalSumPresentation;
                    $arResult["BalanceAmountPresentation"] = $result->return->BalanceAmountPresentation;
                    $arResult["FirstDaySum"] = $result->return->FirstDaySum;
                    $arResult["FirstDaySumPresentation"] = $result->return->FirstDaySumPresentation;
                    $arResult["GUESTS"] = $array;
                    $arResult["TOTAL_SUM"] = $result->return->TotalSum . ' ' . $arResult["Currency"];
                    $arResult["BALANCE_AMOUNT"] = $result->return->BalanceAmount . ' ' . $arResult["Currency"];
                    $arResult["CONTACT_PERSON"] = $result->return->GuestFullName;
                    $arResult["CONTACT_PERSON_PHONE"] = $result->return->GuestPhone;
                    $arResult["CONTACT_PERSON_FAX"] = $result->return->GuestFax;
                    $arResult["CONTACT_PERSON_EMAIL"] = $result->return->GuestEMail;
                    $arResult["UUID"] = $result->return->UUID;
                    $arResult["ReservationConditions"] = $result->return->ReservationConditions;
                    $arResult["ReservationConditionsShort"] = $result->return->ReservationConditionsShort;
                    $arResult["ReservationConditionsOnline"] = $result->return->ReservationConditionsOnline;
                    $arResult["PaymentMethodCodesAllowedOnline"] = $result->return->PaymentMethodCodesAllowedOnline;
                    $arResult["RoomRateCode"] = $result->return->RoomRateCode;
                    $arResult["RoomRateDescription"] = $result->return->RoomRateDescription;
                    $arResult["CustomerName"] = $result->return->Customer;
                    $arResult["CustomerLegacyAddress"] = $result->return->CustomerLegacyAddress;
                    $arResult["CustomerTIN"] = $result->return->CustomerTIN;
                    $arResult["CustomerKPP"] = $result->return->CustomerKPP;
                    $arResult["CustomerEMail"] = $result->return->CustomerEMail;
                    $arResult["CustomerPhone"] = $result->return->CustomerPhone;

                    $arResult["CancelFeeDate"] = $result->return->CancelFeeDate;
                    $arResult["CancelFeeAmount"] = $result->return->CancelFeeAmount;


                    $arResult["CheckDate"] = "";
                    if (isset($result->return->CheckDate) && !empty($result->return->CheckDate) && $result->return->CheckDate != '0001-01-01') {
                        $arResult["CheckDate"] = OnlineBookingSupport::getDateFromFormat($result->return->CheckDate);
                        if ($minCheckInDate && strtotime($minCheckInDate) < strtotime($arResult["CheckDate"])) {
                            $arResult["CheckDate"] = $minCheckInDate;
                        }
                    }
                    if ($arResult["TotalSum"] == $arResult["BalanceAmount"]) {
                        $arResult["DO_ANNULATION"] = true;
                    } else if ($arResult["BOOKING_HOTEL"]["DO_ANNULATION_WITH_PREPAYMENT"]) {
                        $arResult["DO_ANNULATION"] = true;
                    } else {
                        $arResult["DO_ANNULATION"] = false;
                    }
                    if ($result->return->CurrencyCode == "978") {
                        $arResult["CurrencySymbol"] = "�";
                    } elseif ($result->return->CurrencyCode == "840") {
                        $arResult["CurrencySymbol"] = "$";
                    } elseif ($result->return->CurrencyCode == "417") {
                        $arResult["CurrencySymbol"] = "KGS";
                    } else {
                        $arResult["CurrencySymbol"] = "<span class='gotech_ruble'>a</span>";
                    }
                    // ������������ ��������� ������� ������
                    $splits = explode(",", $arResult["PaymentMethodCodesAllowedOnline"]);
                    $resultSplits = preg_replace('/\s+/', '', $splits);
                    $arSelect = Array("ID", "NAME", "DETAIL_TEXT", "PREVIEW_TEXT", "PROPERTY_NAME_EN", "PROPERTY_PAYMENT_SYSTEM",
                        "PROPERTY_FIRST_NIGHT", "PROPERTY_DISCOUNT", "PROPERTY_IS_RECEIPT", "PROPERTY_IS_CASH",
                        "PROPERTY_IS_LEGAL", "PROPERTY_NOT_SHOW", "ACTIVE_FROM", "ACTIVE_TO", "PROPERTY_MIN_NIGHT");
                    $arFilter = Array("IBLOCK_ID" => COption::GetOptionInt('gotech.hotelonline', 'PAYMENT_METHODS_IBLOCK_ID'), "ACTIVE" => "Y", "PROPERTY_HOTEL" => $arResult["BOOKING_HOTEL"]["ID"], "CODE" => $resultSplits);
                    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize" => 50), $arSelect);
                    while ($rz = $res->GetNextElement()) {
                        $arFields = $rz->GetFields();
                        if ($arFields["PROPERTY_NOT_SHOW_VALUE"] == 'Yes') {
                            continue;
                        }

                        $activeFromDateTime = New DateTime($arFields["ACTIVE_FROM"]);
                        $activeFromDateTime->setTime(0,0,0);
                        $activeToDateTime = New DateTime($arFields["ACTIVE_TO"]);
                        $activeToDateTime->setTime(0,0,0);
                        if (!empty($arFields["ACTIVE_TO"]) && !empty($arFields["ACTIVE_FROM"]) && ($activeToDateTime < new DateTime($maxCheckOutDate) || $activeFromDateTime > new DateTime($minCheckInDate))) {
                            continue;
                        }
                        if ($arFields["PROPERTY_MIN_NIGHT_VALUE"] && intval($arFields["PROPERTY_MIN_NIGHT_VALUE"]) > $maxDuration) {
                            continue;
                        }
                        if ($language == "ru")
                            $arResult["PAYMENT_METHODS"][$arFields["ID"]]["NAME"] = $arFields["NAME"];
                        else
                            $arResult["PAYMENT_METHODS"][$arFields["ID"]]["NAME"] = $arFields["PROPERTY_NAME_EN_VALUE"];
                        if ($arFields["PROPERTY_IS_RECEIPT_VALUE"] == 'Yes')
                            $arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_RECEIPT"] = 1;
                        else
                            $arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_RECEIPT"] = 0;
                        if ($arFields["PROPERTY_IS_CASH_VALUE"] == 'Yes')
                            $arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_CASH"] = 1;
                        else
                            $arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_CASH"] = 0;
                        if ($arFields["PROPERTY_IS_LEGAL_VALUE"] == 'Yes')
                            $arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_LEGAL"] = 1;
                        else
                            $arResult["PAYMENT_METHODS"][$arFields["ID"]]["IS_LEGAL"] = 0;
                        $arResult["PAYMENT_METHODS"][$arFields["ID"]]["PAYMENT_SYSTEM"] = $arFields["PROPERTY_PAYMENT_SYSTEM_VALUE"];
                        $arResult["PAYMENT_METHODS"][$arFields["ID"]]["FIRST_NIGHT"] = $arFields["PROPERTY_FIRST_NIGHT_VALUE"];
                        $arResult["PAYMENT_METHODS"][$arFields["ID"]]["DISCOUNT"] = $arFields["PROPERTY_DISCOUNT_VALUE"];
                        if (!empty($arFields["PREVIEW_TEXT"]))
                            $arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"] = $arFields["PREVIEW_TEXT"];
                        else
                            $arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"] = $arFields["DETAIL_TEXT"];
                        $arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"] = str_replace("\\n", "<br/>", $arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"]);
                        $arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"] = str_replace(array('&lt;', '&gt;'), array('<', '>'), $arResult["PAYMENT_METHODS"][$arFields["ID"]]["DETAILS"]);
                    }

                    $its_new_reservation = false;
                    $res = OnlineBookingSupport::db_get('ob_gotech_reservations', "WHERE 1c_group_code='" . $result->return->GuestGroup . "' AND 1c_status='OK'");
                    while ($dbRow = $res->fetch()) {
                        $its_new_reservation = true;
                        $arFields = array(
                            "1c_status" => "'OK_S'"
                        );
                        //Update reservation in table
                        OnlineBookingSupport::db_update('ob_gotech_reservations', $arFields, "WHERE id=" . $dbRow['id']);
                    }

                    if ($its_new_reservation) {
                        ?>
                        <script>
                            if (typeof dataLayer != 'undefined') {
                                var dataLayer = [];
                                dataLayer.push({
                                    'ecommerce': {
                                        'purchase': {
                                            'actionField': {
                                                'id': '<?=$result->return->GuestGroup?>',
                                                'revenue': '<?=number_format($arResult["TotalSum"], 2, '.', '')?>',
                                                'currency': 'RUB',
                                            },
                                            'products': [
                                                <?foreach ($arResult["BOOKING"] as $rkey => $number):?>
                                                <?
                                                if (isset($number["ReservationConditionsShort"]) && !empty($number["ReservationConditionsShort"])) {
                                                    $roomRate = $number["ReservationConditionsShort"];
                                                } else {
                                                    $roomRate = $number["RoomRateDescription"];
                                                }
                                                $nights = (strtotime($number['CheckOutDate']) - strtotime($number['CheckInDate'])) / (60 * 60 * 24);
                                                if ($arResult['HOURS_ENABLE'])
                                                    $nights = (strtotime($number['CheckOutDate']) - strtotime($number['CheckInDate'])) / (60 * 60);
                                                ?>
                                                {
                                                    'name': '<?= $number["RoomTypeDescription"] ?>',
                                                    'id': '<?= $rkey ?>',
                                                    'price': '<?= number_format($number["Cost"] / $nights, 2, '.', '') ?>',
                                                    'category': '<?= $roomRate ?>',
                                                    'quantity': <?=$nights?>,
                                                },
                                                <?endforeach;?>
                                            ]
                                        }
                                    }
                                });
                            }
                        </script>
                        <?
                    }


                } else {
                    $arResult["ERROR"] = GetMessage("HOTEL_CODE_NO");
                }
            }
        }


        /*******************************************************************************/

        if (empty($arResult["ERROR"])) {

            $filter = array(
                "IBLOCK_CODE" => "service",
                "PROPERTY_SERVICEHOTEL" => $hotel["ID"],
                "ACTIVE" => "Y",
            );
            $curLang = OnlineBookingSupport::getLanguage();
            $arResult["Services"] = array();
            $arResult['Services0'] = array();
            $dbEl = CIBlockElement::GetList(array("SORT" => "ASC"), $filter);
            while ($obEl = $dbEl->GetNextElement()) {
                unset($fields);
                unset($props);
                $fields = $obEl->GetFields();
                $props = $obEl->GetProperties();

                $service["Id"] = $fields["ID"];
                if ($curLang == "ru")
                    $service["Name"] = $fields["NAME"];
                else
                    $service["Name"] = $props["SERVICENAMEEN"]["VALUE"];
                $service["Text"] = $fields["PREVIEW_TEXT"];
                //$service["Price"] = $props["SERVICEPRICE"]["VALUE"];
                $service["Code"] = $props["SERVICECODE"]["VALUE"];
                $service["Hotel"] = $props["SERVICEHOTEL"]["VALUE"];
                //$service["AgeFrom"] = $props["SERVICEAGEFROM"]["VALUE"];
                //$service["AgeTo"] = $props["SERVICEAGETO"]["VALUE"];
                //$service["NumberToGuest"] = $props["SERVICEGUESTS"]["VALUE"];
                //$service["NumberToRoom"] = $props["SERVICEROOMS"]["VALUE"];
                $service["Popular"] = $props["POPULAR"]["VALUE"];
                $service["Hotel_id"] = $hotel["ID"];
                $service["IsSection"] = "N";
                $service["InSection"] = ($fields["IN_SECTIONS"] == "Y" && $fields["IBLOCK_SECTION_ID"] != NULL) ? "Y" : "N";
                $service["Picture"] = CFile::ResizeImageGet(
                    $fields["PREVIEW_PICTURE"],
                    array('width' => 120, 'height' => 120),
                    BX_RESIZE_IMAGE_EXACT,
                    true
                );
                //$service['Days'] = $props['SERVICEDAYS']['VALUE'];
                $service['IsTransfer'] = $props['IS_TRANSFER']['VALUE'] ? true : false;


                $arResult['Services0'][$fields["ID"]] = $fields["NAME"];


                if ($fields["IN_SECTIONS"] == "Y" && $fields["IBLOCK_SECTION_ID"] != NULL) {
                    if (isset($arResult["Services"]["section_" . $fields["IBLOCK_SECTION_ID"]]["Services"])) {
                        $arResult["Services"]["section_" . $fields["IBLOCK_SECTION_ID"]]["Services"][] = $service;
                    } else {
                        $db_old_groups = CIBlockElement::GetElementGroups($fields["ID"], true, array('IBLOCK_ID', 'ID', 'NAME', 'DESCRIPTION', 'PICTURE', 'UF_SVG_ICON'));
                        while ($ar_group = $db_old_groups->Fetch()) {


                            $section["Id"] = $fields["IBLOCK_SECTION_ID"];
                            $section["Name"] = $ar_group["NAME"];
                            $section["Description"] = $ar_group["DESCRIPTION"];
                            $section["Text"] = "";
                            //$section["Price"] = "";
                            $section["Code"] = "";
                            $section["Hotel"] = "";
                            //$section["AgeFrom"] = "";
                            //$section["AgeTo"] = "";
                            //$section["NumberToGuest"] = "";
                            //$section["NumberToRoom"] = "";
                            $section["Hotel_id"] = "";
                            $section["IsSection"] = "N";
                            $section["InSection"] = "Y";
                            $section["Svg"] = CFile::GetPath($ar_group["UF_SVG_ICON"]);

                            $section["Picture"] = CFile::ResizeImageGet(
                                $ar_group["PICTURE"],
                                array('width' => 150, 'height' => 150),
                                BX_RESIZE_IMAGE_EXACT,
                                true
                            );
                            $section["Services"] = array();
                        }
                        $section["Services"][] = $service;
                        $arResult["Services"]["section_" . $section["Id"]] = $section;
                    }
                } else {
                    $arResult["Services"][] = $service;
                }


            }


            $arResult['SERVICES_PRICES'] = array();
            $arResult['SERVICES_NAMES'] = array();
            //echo var_dump($arResult['Services']);
            foreach ($arResult['Services'] as $key => $serv):
                if (substr($key, 0, 7) == "section"):
                    foreach ($serv['Services'] as $key2 => $s):
                        $filter = array(
                            'IBLOCK_CODE' => 'services_prices',
                            'PROPERTY_SERVICE_ID' => $s['Id'],
                            'ACTIVE' => 'Y',
                        );
                        $service_price_res = CIBlockElement::GetList(array('PROPERTY_SERVICEMINLOS' => 'ASC', 'NAME' => 'ASC'), $filter);
                        $n = $service_price_res->SelectedRowsCount();
                        if (!$n):
                            unset($arResult['Services'][$key]['Services'][$key2]);
                        else:
                            $arResult['Services'][$key]['Services'][$key2]['prices'] = array();

                            $go0 = false;
                            while ($ob = $service_price_res->GetNextElement()):
                                $fields = $ob->GetFields();
                                $props = $ob->GetProperties();

                                $activeFromDateTime = New DateTime($fields["ACTIVE_FROM"]);
                                $activeToDateTime = New DateTime($fields["ACTIVE_TO"]);
                                //$datetime1 = new DateTime($date_in0);
                                //$datetime2 = new DateTime($date_out0);
                                $datetime1 = $date_in0;
                                $datetime2 = $date_out0;

                                $interval = $datetime1->diff($datetime2);

                                if ((empty($props["SERVICEMINLOS"]["VALUE"]) Or intval($props["SERVICEMINLOS"]["VALUE"]) <= $interval->days)
                                    && (empty($props["SERVICEMAXLOS"]["VALUE"]) or intval($props["SERVICEMAXLOS"]["VALUE"]) >= $interval->days)) {
                                    if (((!empty($fields["ACTIVE_TO"]) && $activeToDateTime >= $datetime1) || empty($fields["ACTIVE_TO"])) && ((!empty($fields["ACTIVE_FROM"]) && $activeFromDateTime <= $datetime1) || empty($fields["ACTIVE_FROM"]))) {
                                        if (empty($props["SERVICERATE"]["VALUE"]) or in_array($props["SERVICERATE"]["VALUE"], $arRoomRates)) {

                                            $go0 = true;


                                            $service = array();
                                            $service["Id"] = $fields["ID"];
                                            $service["Name"] = $curLang == 'ru' ? $fields["NAME"] : ($props["SERVICENAMEEN"]['VALUE'] ?: $fields["NAME"]);
                                            $service["Price"] = $props["SERVICEPRICE"]["VALUE"];
                                            $service["Code"] = $props["SERVICECODE"]["VALUE"] ?: $s['Code'];
                                            $service["AgeFrom"] = $props["SERVICEAGEFROM"]["VALUE"];
                                            $service["AgeTo"] = $props["SERVICEAGETO"]["VALUE"];
                                            $service["NumberToGuest"] = $props["SERVICEGUESTS"]["VALUE"];
                                            $service["NumberToRoom"] = $props["SERVICEROOMS"]["VALUE"];
                                            $service["Discount"] = $props["SERVICEDISCOUNT"]["VALUE"];
                                            $service["MinLOS"] = $props["SERVICEMINLOS"]["VALUE"];
                                            $service["Days"] = $props["SERVICEMINLOS"]["VALUE"];
                                            $service["MaxLOS"] = $props["SERVICEMAXLOS"]["VALUE"];

                                            if (!$service["Discount"] && isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount) && $_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount > 0) {
                                                $service["Discount"] = $_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount;
                                                $service["OldPrice"] = $props["SERVICEPRICE"]["VALUE"];
                                                $service["Price"] = floatval($service["Price"]) * (100 - $_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount) / 100;
                                            }

                                            $arResult['SERVICES_NAMES'][$fields["ID"]] = $arResult['Services0'][$s['Id']];

                                            //$s['prices'][] = $service;
                                            //$arResult['Services'][$key]['Services'][$key2]['prices'][] = $service;
                                            //if($n > 1):
                                            $go = true;
                                            if (($service["MinLOS"] && $service["Price"]) || $s['IsTransfer']):
                                                $s['Dicsount'] = $service["Discount"];
                                                $s['prices'][] = $service;
                                            else:
                                                $s['Id'] = $service["Id"];
                                                $s['Price'] = $service["Price"];
                                                $s['Code'] = $service["Code"];
                                                $s['Discount'] = $service["Discount"];
                                                $s['NumberToGuest'] = $service["NumberToGuest"];
                                                $s['NumberToRoom'] = $service["NumberToRoom"];
                                                $s['AgeFrom'] = $service["AgeFrom"];
                                                $s['AgeTo'] = $service["AgeTo"];
                                            endif;

                                            //if(!count($s['prices']) && $service["MinLOS"]) { $go = false;}  //???

                                            $arResult['SERVICES_PRICES'][$service['Id']] = $service['Price'];

                                            $arResult['Services'][$key]['Services'][$key2] = $s;

                                            //echo var_dump($service);

                                        }
                                    }
                                }


                            endwhile;

                            if (!$go0)
                                unset($arResult['Services'][$key]['Services'][$key2]);

                        endif;

                    endforeach;
                endif;
            endforeach;
            //echo var_dump($arResult['Services']);


        }
        /*****************************************************************************/

    } elseif (isset($_REQUEST["cancel"]) && !empty($_REQUEST["cancel"]) && $_REQUEST["cancel"] == "Y") {
        if (!$isFromPayment) {
            if (empty($_REQUEST["reservation"]))
                $arResult["ERROR"] = GetMessage("NUMBER_RESERVATION_ALWAYS");
            elseif (empty($_REQUEST["data"]))
                $arResult["ERROR"] = GetMessage("PHONE_EMAIL_ALWAYS");
        } else {
            $arResult["ERROR"] = GetMessage("NUMBER_RESERVATION_ALWAYS");
        }
        if (empty($arResult["ERROR"])) {

            $soap_params = array('trace' => 1);
            if (!empty($SOAP_LOGIN) && !empty($SOAP_PASSWORD)) {
                $soap_params['login'] = $SOAP_LOGIN;
                $soap_params['password'] = $SOAP_PASSWORD;
            }
            $soapclient = new SoapClient(trim($WSDL), $soap_params); // with auth
            $query = array(
                "Hotel" => $HotelCode1C,
                "GuestGroupCode" => $isFromPayment ? $uuid : ($_REQUEST["reservation"] ? $_REQUEST["reservation"] : $_SESSION["search_reservation"]),
                "ExternalSystemCode" => $OutputCode,
                "LanguageCode" => mb_strtoupper($language),
                "Reason" => "CanceledByUser"
            );
            $result = $soapclient->CancelGroupReservation($query);
            if (empty($result->return))
                $arResult["SUCCESS"] = GetMessage("DELETE_RESERVATION");
            else $arResult["ERROR"] = $result->return;
        }
    }
    if ($isFromPayment) {
        ?>
        <script>
            if (typeof yaCounter != 'undefined') {
                yaCounter.reachGoal('DO_PAY');
            }
            if (typeof dataLayer != 'undefined') {
                dataLayer.push({
                    event: "VirtualPageview",
                    virtualPageURL: "/virtual/sendOrder",
                    virtualPageTitle: "Шаг 5 – Заказ",
                    virtualPageHotel: <?=$hotel_id?>
                });
            }
            if (typeof ga != 'undefined') {
                ga('send', 'event', 'BUTTON', 'DO_PAY');
            }
        </script>
        <?
    }
    $this->IncludeComponentTemplate();
}
function isUserAgent()
{
    global $USER;
    if ($USER->IsAuthorized()) {
        if (CModule::IncludeModule("gotech.hotelonline")) {
            if (in_array(COption::GetOptionInt('gotech.hotelonline', 'USER_AGENT_GROUP'), $USER->GetUserGroupArray()))
                return true;
            else
                return false;
        } else
            return false;
    } else
        return false;
}

?>
