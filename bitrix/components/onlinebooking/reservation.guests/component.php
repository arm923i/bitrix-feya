<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!function_exists('plural_form')) {
  function plural_form($number, $after)
  {
    $cases = array(2, 0, 1, 1, 1, 2);
    echo $number . ' ' . $after[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
  }
}

if (!function_exists('isDate')) {
  function isDate($string)
  {
    $matches = array();
    $pattern = '/^([0-9]{1,2})\\.([0-9]{1,2})\\.([0-9]{4})$/';
    if (!preg_match($pattern, $string, $matches)) return false;
    if (!checkdate($matches[2], $matches[1], $matches[3])) return false;
    return true;
  }
}

if (!CModule::IncludeModule("iblock")) {
  echo GetMessage("IBLOCK_NOT_INCLUDE");
  return;
} elseif (!CModule::IncludeModule("gotech.hotelonline")) {
  ShowError(GetMessage("ONLINEBOOKING_MODULE_NOT_INSTALLED"));
  return;
} else {

  define("start_time_object", microtime(true));

  $is_another_payer = false;

  if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount) && $_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount > 0) {
    $arResult['LOGGED_USER_DISCOUNT'] = $_SESSION["AUTH_CLIENT_DATA"]->RoomDiscount;
  }

  if (empty($_REQUEST["send_booking"]))
    $_SESSION["SERVICES_BOOKING"] = array();
  else {
    if (!$_REQUEST['pay_fio']) {
      $is_another_payer = true;
    }
    if (($_REQUEST['another_pay_lastname'] || $_REQUEST['another_pay_name']) && !$_REQUEST['pay_fio'])
      $_REQUEST['pay_fio'] = $_REQUEST['another_pay_lastname'] . " " . $_REQUEST['another_pay_name'] . " " . $_REQUEST['another_pay_secondname'];
  }

  $curLang = OnlineBookingSupport::getLanguage();
  if ($this->includeComponentLang("", $curLang) == NULL) {
    __IncludeLang(dirname(__FILE__) . "/lang/" . $curLang . "/component.php");
  }
  if (empty($_SESSION["NUMBERS_BOOKING"][$arParams["ID_HOTEL"]]["NUMBERS"])) {
    header('Location: ' . COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER') . "?" . SID);
    return;
  } else {


    $temp = array_values($_SESSION["NUMBERS_BOOKING"][$arParams["ID_HOTEL"]]["NUMBERS"]);
    $_SESSION["NUMBERS_BOOKING"][$arParams["ID_HOTEL"]]["NUMBERS"] = $temp;

    $iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
    $hotels = CIBlockElement::GetList(
      array(),
      array("IBLOCK_ID" => $iblock_id_hotel, "ID" => $arParams["ID_HOTEL"]),
      false,
      false,
      array(
        "PROPERTY_HOURS_ENABLE", "PROPERTY_FIO", "PROPERTY_PHONE_NECESSARY", "PROPERTY_HOTEL_ERROR_TEXT_RU", "PROPERTY_HOTEL_ERROR_TEXT_EN",
        "PROPERTY_LABEL_PHONE", "PROPERTY_LABEL_EMAIL", "PROPERTY_LABEL_PHONE_EN", "PROPERTY_LABEL_EMAIL_EN",
        "PROPERTY_AGREE_BRON_TEXT_RU", "PROPERTY_AGREE_BRON_TEXT_EN", "PROPERTY_HOTEL_AGREEMENT", "PROPERTY_HOTEL_NAME_EN_VALUE",
        "PROPERTY_CURRENCY", "NAME"
      )
    );
    if ($hotel = $hotels->GetNext()) {
      $arResult['HOTEL_NAME'] = OnlineBookingSupport::getLanguage() == 'en' ? $hotel['PROPERTY_HOTEL_NAME_EN_VALUE'] : $hotel['NAME'];
      $agreement_enum = CIBlockPropertyEnum::GetByID($hotel["PROPERTY_HOTEL_AGREEMENT_ENUM_ID"]);
      $arResult["AGREEMENT_ID"] = $agreement_enum["XML_ID"];

      if (!empty($hotel["PROPERTY_CURRENCY_VALUE"]) && $hotel["PROPERTY_CURRENCY_VALUE"] != NULL) {
        $arResult["CURRENCY"] = $hotel["PROPERTY_CURRENCY_ENUM_ID"];
        $arResult["CURRENCY_NAME"] = $hotel["PROPERTY_CURRENCY_VALUE"];
      }

      $arResult["HOURS_ENABLE"] = $hotel["PROPERTY_HOURS_ENABLE_VALUE"] ? true : false;
      $arResult["FIO"] = $hotel["PROPERTY_FIO_VALUE"] ? true : false;
      $arResult["PHONE_NECESSARY"] = $hotel["PROPERTY_PHONE_NECESSARY_VALUE"] ? true : false;
      $arResult["EMAIL_NECESSARY"] = $hotel["PROPERTY_EMAIL_NECESSARY_VALUE"] ? true : false;
      $arResult["DATE_OF_BIRTH_NECESSARY"] = $hotel["PROPERTY_DATE_OF_BIRTH_NECESSARY_VALUE"] ? true : false;

      $arResult['LABEL_PHONE'] = $curLang == 'ru' ? $hotel["PROPERTY_LABEL_PHONE_VALUE"] : $hotel["PROPERTY_LABEL_PHONE_EN_VALUE"];
      $arResult['LABEL_EMAIL'] = $curLang == 'ru' ? $hotel["PROPERTY_LABEL_EMAIL_VALUE"] : $hotel["PROPERTY_LABEL_EMAIL_EN_VALUE"];

      $arResult['AGREE_BRON_TEXT'] = $curLang == 'ru' ? $hotel["~PROPERTY_AGREE_BRON_TEXT_RU_VALUE"] : $hotel["~PROPERTY_AGREE_BRON_TEXT_EN_VALUE"];

      $arResult['ERROR_TEXT'] = $hotel["PROPERTY_HOTEL_ERROR_TEXT_" . strtoupper($curLang) . "_VALUE"] ?: 'Error!';

    }
    if ($arResult["HOURS_ENABLE"]) {
      foreach ($_SESSION["NUMBERS_BOOKING"][$arParams["ID_HOTEL"]]["NUMBERS"] as &$arNumber) {
        $arNumber["PeriodFrom"] = $_SESSION["PeriodFrom"] . " " . $_SESSION["TimeFrom"];
        $arNumber["PeriodTo"] = date("d.m.Y H:i", AddToTimeStamp(array("HH" => $_SESSION["hour"]), MakeTimeStamp($_SESSION["PeriodFrom"] . " " . $_SESSION["TimeFrom"] . ":00", "DD.MM.YYYY HH:MI:SS")));
      }
    }
    $arResult["GUESTS"] = $_SESSION["NUMBERS_BOOKING"][$arParams["ID_HOTEL"]]["NUMBERS"];
    if (!empty($_REQUEST["phone"]))
      $_SESSION["phone"] = $_REQUEST["phone"];
    if (!empty($_REQUEST["email"]))
      $_SESSION["email"] = $_REQUEST["email"];
    $start_date = $arResult["GUESTS"][0]["PeriodFrom"];
    foreach ($arResult["GUESTS"] as $people) {
      if (strtotime($people["PeriodFrom"]) < strtotime($start_date))
        $start_date = $people["PeriodFrom"];
    }
    $arRoomRates = array();
    $id_iblock_numbers = COption::GetOptionString('gotech.hotelonline', 'NUMBER_IBLOCK_ID');
    $id_hotel_property = COption::GetOptionString('gotech.hotelonline', 'NUMBERHOTEL');
    $id_number_code_property = COption::GetOptionString('gotech.hotelonline', 'NUMBERCODE');
    $id_number_en_name = COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMEEN');
    COption::SetOptionString('gotech.hotelonline', 'NUMBERNAMERU', "NAME");
    if (COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU') == "NAME")
      $id_number_ru_name = "NAME";
    else
      $id_number_ru_name = "PROPERTY_" . COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU');
    $lastPayMethods = "";
    $arPayMethods = Array();
    foreach ($arResult["GUESTS"] as $key_number => $number) {
      if ($number["PaymentMethodCodesAllowedOnline"] != $lastPayMethods) {
        $arPayMethods[] = $number["PaymentMethodCodesAllowedOnline"];
      }
      $arRoomRates[] = $number["RoomRateCode"];
      unset($filter);
      unset($arSelect);
      unset($dbEl);
      unset($res);
      $filter = array(
        "IBLOCK_ID" => $id_iblock_numbers,
        "ACTIVE" => "Y",
        $id_number_code_property => $number["RoomTypeCode"],
        $id_hotel_property => $arParams["ID_HOTEL"]
      );
      $arSelect = array(
        $id_hotel_property,
        $id_number_code_property,
        $id_number_en_name,
        $id_number_ru_name,
        'PREVIEW_PICTURE', //
      );

      $dbEl = CIBlockElement::GetList(array(), $filter, false, false, $arSelect);
      if ($res = $dbEl->GetNext()) {
        if ($curLang == "en") {
          $arResult["GUESTS"][$key_number]["RoomName"] = $res[$id_number_en_name . "_VALUE"];
        }
        if ($curLang == "ru" || !$arResult["GUESTS"][$key_number]["RoomName"]) {
          if ($id_number_ru_name == "NAME")
            $arResult["GUESTS"][$key_number]["RoomName"] = $res[$id_number_ru_name];
          else
            $arResult["GUESTS"][$key_number]["RoomName"] = $res[$id_number_ru_name . '_VALUE'];
        }

        $arResult["GUESTS"][$key_number]["Picture"] = CFile::ResizeImageGet($res['PREVIEW_PICTURE'], array('width' => 290, 'height' => 200), BX_RESIZE_IMAGE_PROPORTIONAL, true);
      } else {
        if ($curLang == "en") {
          $arResult["GUESTS"][$key_number]["RoomName"] = $number["RoomNameEn"];
        } else {
          $arResult["GUESTS"][$key_number]["RoomName"] = $number["RoomName"];
        }
      }
      $lastPayMethods = $number["PaymentMethodCodesAllowedOnline"];
    }
    $lastSplits = NULL;
    foreach ($arPayMethods as $pm) {

      $splits = explode(",", $pm);
      $splits = preg_replace('/\s+/', '', $splits);
      if ($lastSplits == NULL) {
        $lastSplits = $splits;
        $resultSplits = $splits;
      } else {
        $resultSplits = array_intersect($splits, $lastSplits);
      }
    }

    $arResult["FIELDS_FOR_AGREEMENT"] = array(GetMessage("surname"), GetMessage("name"));

    $arResult["MIN_TRANSFER"] = $start_date;
    $arResult["FIELD"]["typeOfAccommodation"] = GetMessage("type_of_accommodation");
    $arResult["FIELD"]["periodFrom"] = GetMessage("periodFrom");
    $arResult["FIELD"]["periodTo"] = GetMessage("periodTo");
    $arResult["FIELD"]["surname"] = GetMessage("surname");
    $arResult["FIELD"]["name"] = GetMessage("name");
    $arResult["FIELD"]["allotmentCode"] = "";
    $arResult["FIELD"]["RoomRateCode"] = "";
    $arResult["FIELD"]["RoomCode"] = "";
    $arResult["FIELD"]["PaymentMethodCodesAllowedOnline"] = "";
    if ($curLang != "en" || 1) {
      $arResult["FIELD"]["secondName"] = GetMessage("second_name");
      $arResult["FIELDS_FOR_AGREEMENT"][] = GetMessage("second_name");
    }
    $arResult["FIELDS_FOR_AGREEMENT"][] = GetMessage("CUSTOMER_EMAIL");
    $arResult["FIELDS_FOR_AGREEMENT"][] = GetMessage("CUSTOMER_PHONE");
    $arResult["FIELD"]["RoomType"] = GetMessage("RoomType");
    $hotels = CIBlockElement::GetList(
      array(),
      array("IBLOCK_ID" => $iblock_id_hotel, "ID" => $arParams["ID_HOTEL"]),
      false,
      false,
      array(
        "ID",
        "NAME",
        "PROPERTY_BIRTHDAY",
        "PROPERTY_CITIZENSHIP",
        "PROPERTY_NUMBER_OF_CAR",
        "PROPERTY_HOTEL_CODE",
        "PROPERTY_HOTEL_NAME_EN",
        "PROPERTY_FIO",
        "PROPERTY_PHONE_NECESSARY",
        "PROPERTY_EMAIL_NECESSARY",
        "PROPERTY_DATE_OF_BIRTH_NECESSARY",
        "PROPERTY_REMARKS",
        "PROPERTY_SMS_IS_ENABLED",
        "PROPERTY_DETAIL_TERMS_LINK",
        "PROPERTY_DETAIL_TERMS_RU",
        "PROPERTY_DETAIL_TERMS_EN",
        "PROPERTY_SHOW_PAYMENT_METHODS_ON_GUESTS_PAGE",
        "PROPERTY_HOTEL_TIME_FROM",
        "PROPERTY_HOTEL_TIME",
        "PROPERTY_INPUT_GUEST_PASSPORT_DATA",
        "PROPERTY_INPUT_PAYER_PASSPORT_DATA",
        "PROPERTY_INPUT_GUEST_ADDRESS",
        "PROPERTY_INPUT_PAYER_ADDRESS"
      )
    );
    if ($hotel = $hotels->GetNext()) {
      $arResult['HOTEL_TIME_IN'] = $hotel['PROPERTY_HOTEL_TIME_FROM_VALUE'];
      $arResult['HOTEL_TIME_OUT'] = $hotel['PROPERTY_HOTEL_TIME_VALUE'];

      $arResult['INPUT_GUEST_PASSPORT_DATA'] = !!$hotel['PROPERTY_INPUT_GUEST_PASSPORT_DATA_VALUE'];
      $arResult['INPUT_PAYER_PASSPORT_DATA'] = !!$hotel['PROPERTY_INPUT_PAYER_PASSPORT_DATA_VALUE'];
      $arResult['INPUT_GUEST_ADDRESS'] = !!$hotel['PROPERTY_INPUT_GUEST_ADDRESS_VALUE'];
      $arResult['INPUT_PAYER_ADDRESS'] = !!$hotel['PROPERTY_INPUT_PAYER_ADDRESS_VALUE'];

      if ($arResult['INPUT_GUEST_PASSPORT_DATA']) {
        $arResult["FIELDS_FOR_AGREEMENT"][] = GetMessage("PASSPORT_DATA");
      }
      if ($arResult['INPUT_GUEST_ADDRESS']) {
        $arResult["FIELDS_FOR_AGREEMENT"][] = GetMessage("CUSTOMER_ADDRESS");
      }

      $filter = array(
        "IBLOCK_CODE" => "service",
        "PROPERTY_SERVICEHOTEL" => $hotel["ID"],
        "ACTIVE" => "Y",
      );

      $arResult["Services"] = array();
      $arResult['Services0'] = array();
      $dbEl = CIBlockElement::GetList(array("SORT" => "ASC"), $filter);
      while ($obEl = $dbEl->GetNextElement()) {
        unset($fields);
        unset($props);
        $fields = $obEl->GetFields();
        $props = $obEl->GetProperties();
        $activeFromDateTime = New DateTime($fields["ACTIVE_FROM"]);
        $activeToDateTime = New DateTime($fields["ACTIVE_TO"]);
        $datetime1 = new DateTime($arResult["GUESTS"][0]["PeriodFrom"]);
        $datetime2 = new DateTime($arResult["GUESTS"][0]["PeriodTo"]);
        $interval = $datetime1->diff($datetime2);

        $service["Id"] = $fields["ID"];
        if ($curLang == "ru")
          $service["Name"] = $fields["NAME"];
        else
          $service["Name"] = $props["SERVICENAMEEN"]["VALUE"];
        $service["Text"] = $fields["PREVIEW_TEXT"];
        $service["Code"] = $props["SERVICECODE"]["VALUE"];
        $service["Hotel"] = $props["SERVICEHOTEL"]["VALUE"];
        $service["Popular"] = $props["POPULAR"]["VALUE"] == 'Yes' ? 'Y' : $props["POPULAR"]["VALUE"];
        $service["Hotel_id"] = $hotel["ID"];
        $service["IsSection"] = "N";
        $service["InSection"] = ($fields["IN_SECTIONS"] == "Y" && $fields["IBLOCK_SECTION_ID"] != NULL) ? "Y" : "N";
        $service["Picture"] = CFile::ResizeImageGet(
          $fields["PREVIEW_PICTURE"],
          array('width' => 120, 'height' => 120),
          BX_RESIZE_IMAGE_EXACT,
          true
        );
        $service['IsTransfer'] = $props['IS_TRANSFER']['VALUE'] ? true : false;
        $arResult['Services0'][$fields["ID"]] = $fields["NAME"];

        if ($fields["IN_SECTIONS"] == "Y" && $fields["IBLOCK_SECTION_ID"] != NULL) {
          if (isset($arResult["Services"]["section_" . $fields["IBLOCK_SECTION_ID"]]["Services"])) {
            $arResult["Services"]["section_" . $fields["IBLOCK_SECTION_ID"]]["Services"][] = $service;
          } else {
            $db_old_groups = CIBlockElement::GetElementGroups($fields["ID"], true, array('IBLOCK_ID', 'ID', 'NAME', 'SORT_CODE', 'DESCRIPTION', 'PICTURE', 'UF_SVG_ICON'));
            while ($ar_group = $db_old_groups->Fetch()) {


              $section["Id"] = $fields["IBLOCK_SECTION_ID"];
              $section["Sort"] = $ar_group["SORT_CODE"];
              $section["Name"] = $ar_group["NAME"];
              $section["Description"] = $ar_group["DESCRIPTION"];
              $section["Text"] = "";
              $section["Code"] = "";
              $section["Hotel"] = "";
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
      function sortServices($a, $b)
      {
        return intval($a["Sort"]) > intval($b["Sort"]);
      }

      uasort($arResult["Services"], 'sortServices');

      $arResult['SERVICES_PRICES'] = array();
      $arResult['SERVICES_NAMES'] = array();
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
            if (!$n) {
              unset($arResult['Services'][$key]['Services'][$key2]);
            } else {
              $arResult['Services'][$key]['Services'][$key2]['prices'] = array();

              $go0 = false;

              while ($ob = $service_price_res->GetNextElement()):
                $fields = $ob->GetFields();
                $props = $ob->GetProperties();

                $activeFromDateTime = New DateTime($fields["ACTIVE_FROM"]);
                $activeToDateTime = New DateTime($fields["ACTIVE_TO"]);
                $intervals = array();
                foreach ($arResult["GUESTS"] as $j => $g):
                  $datetime1 = new DateTime($arResult["GUESTS"][$j]["PeriodFrom"]);
                  $datetime2 = new DateTime($arResult["GUESTS"][$j]["PeriodTo"]);
                  $intervals[] = $datetime1->diff($datetime2)->days;
                endforeach;

                $interval = max($intervals);

                if ((empty($props["SERVICEMINLOS"]["VALUE"]) Or intval($props["SERVICEMINLOS"]["VALUE"]) <= $interval)
                  && (empty($props["SERVICEMAXLOS"]["VALUE"]) or intval($props["SERVICEMAXLOS"]["VALUE"]) >= $interval)
                ) {
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
                      $service['IsPricePerNight'] = $props['PRICE_PER_NIGHT']['VALUE'] ? true : false;

                      if (!$service["Discount"] && isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->ServicesDiscount) && $_SESSION["AUTH_CLIENT_DATA"]->ServicesDiscount > 0) {
                        $service["Discount"] = $_SESSION["AUTH_CLIENT_DATA"]->ServicesDiscount;
                        //if (isset($_SESSION["AUTH_CLIENT_DATA"]->ServicesDiscount) && isset($_SESSION["AUTH_CLIENT_DATA"]->ServicesDiscount->ServiceDiscountRow)) {
                        //foreach ($_SESSION["AUTH_CLIENT_DATA"]->ServicesDiscount->ServiceDiscountRow as $s_discount) {
                        //if ($s_discount->ServiceGroup == $serv["Name"]) {
                        //$service["Discount"] = $s_discount->Discount;
                        //}
                        //}
                        //}

                      }
                      if ($service["Discount"]) {
                        $service["OldPrice"] = $props["SERVICEPRICE"]["VALUE"];
                        $service["Price"] = floatval($service["Price"]) * (100 - $service["Discount"]) / 100;
                      }

                      $arResult['SERVICES_NAMES'][$fields["ID"]] = $arResult['Services0'][$s['Id']];

                      $go = true;
                      if (($service["MinLOS"] && $service["Price"]) || $s['IsTransfer']):
                        $s['Discount'] = $service["Discount"];
                        $s['prices'][] = $service;
                      else:
                        $s['Id'] = $service["Id"];
                        $s['Price'] = $service["Price"];
                        $s["OldPrice"] = $service["OldPrice"];
                        $s['Code'] = $service["Code"];
                        $s['Discount'] = $service["Discount"];
                        $s['NumberToGuest'] = $service["NumberToGuest"];
                        $s['NumberToRoom'] = $service["NumberToRoom"];
                        $s['AgeFrom'] = $service["AgeFrom"];
                        $s['AgeTo'] = $service["AgeTo"];
                        $s['IsPricePerNight'] = $service["IsPricePerNight"];
                      endif;

                      $arResult['SERVICES_PRICES'][$service['Id']] = $service['Price'];

                      $arResult['Services'][$key]['Services'][$key2] = $s;
                    }
                  }
                }


              endwhile;

              if (!$go0) {
                unset($arResult['Services'][$key]['Services'][$key2]);
              }
            }
          endforeach;
        endif;
      endforeach;

      $hotel_code = $hotel["PROPERTY_HOTEL_CODE_VALUE"];
      if ($hotel["PROPERTY_BIRTHDAY_VALUE"]) {
        $arResult["FIELD"]["birthday"] = GetMessage("birthday");
        $arResult["FIELDS_FOR_AGREEMENT"][] = GetMessage("birthday");
      }
      if ($hotel["PROPERTY_CITIZENSHIP_VALUE"]) {
        $res = CIBlockElement::GetList(array("SORT" => "ASC"), array("IBLOCK_CODE" => "citizenship", "ACTIVE" => "Y"), false, false, array("NAME", "PROPERTY_ISO", "PROPERTY_NAME_EN"));
        while ($result = $res->GetNext()) {
          if ($curLang == "ru") {
            if (!empty($result["NAME"]))
              $arResult["CITIZENSHIP"][$result["PROPERTY_ISO_VALUE"]] = $result["NAME"];
          } elseif ($curLang == "en") {
            if (!empty($result["PROPERTY_NAME_EN_VALUE"]))
              $arResult["CITIZENSHIP"][$result["PROPERTY_ISO_VALUE"]] = $result["PROPERTY_NAME_EN_VALUE"];
          }
        }
        if (!empty($arResult["CITIZENSHIP"])) {
          $arResult["FIELD"]["citizenship"] = GetMessage("citizenship");
          $arResult["FIELDS_FOR_AGREEMENT"][] = GetMessage("citizenship");
        }
      }
      if ($hotel["PROPERTY_NUMBER_OF_CAR_VALUE"]) {
        $arResult["NUMBER_OF_CAR"] = 1;
      }
      if ($hotel["PROPERTY_REMARKS_VALUE"]) {
        $arResult["SHOW_REMARKS"] = 1;
      }
      if ($hotel["PROPERTY_SMS_IS_ENABLED_VALUE"]) {
        $arResult["SMS_IS_ENABLED"] = 1;
      }
      if ($hotel["PROPERTY_FIO_VALUE"]) {
        $always_fio = 1;
      } else $always_fio = 0;
      if ($hotel["PROPERTY_DATE_OF_BIRTH_NECESSARY_VALUE"]) {
        $always_date_of_birth = 1;
        $arResult["DATE_OF_BIRTH_NECESSARY"] = 1;
      } else $always_date_of_birth = 0;
      if ($hotel["PROPERTY_PHONE_NECESSARY_VALUE"]) {
        $arResult["PHONE_NECESSARY"] = 1;
      } else $arResult["PHONE_NECESSARY"] = 0;
      if ($hotel["PROPERTY_EMAIL_NECESSARY_VALUE"]) {
        $arResult["EMAIL_NECESSARY"] = 1;
      } else $arResult["EMAIL_NECESSARY"] = 0;
      if ($hotel["PROPERTY_SHOW_PAYMENT_METHODS_ON_GUESTS_PAGE_VALUE"]) {
        $arResult["SHOW_PAYMENT_METHODS"] = 1;
      } else $arResult["SHOW_PAYMENT_METHODS"] = 0;
      $arResult["Terms"] = $hotel["PROPERTY_DETAIL_TERMS_LINK_VALUE"];
      $arResult["HOTEL"]["ID"] = $hotel["ID"];
      if ($curLang == "ru") {
        $APPLICATION->SetTitle($hotel["NAME"] . GetMessage("TITLE_NAME"));
        $arResult["DetailTerms"] = $hotel["PROPERTY_DETAIL_TERMS_RU_VALUE"]["TEXT"];
      } elseif ($curLang == "en") {
        $APPLICATION->SetTitle($hotel["PROPERTY_HOTEL_NAME_EN_VALUE"] . GetMessage("TITLE_NAME"));
        $arResult["DetailTerms"] = $hotel["PROPERTY_DETAIL_TERMS_EN_VALUE"]["TEXT"];
      }
      $arResult["DetailTerms"] = str_replace("\\n", "<br/>", $arResult["DetailTerms"]);
      $arResult["DetailTerms"] = str_replace(array('&lt;', '&gt;'), array('<', '>'), $arResult["DetailTerms"]);
    }

    $arResult["DOC_TYPES"] = array();
    $arResult["DOC_PHOTO_TYPES"] = array();
    $res = CIBlockElement::GetList(array("SORT" => "ASC"), array("IBLOCK_CODE" => "document_types", "ACTIVE" => "Y"), false, false, array("NAME", "PROPERTY_DOC_CODE", "PROPERTY_DOC_NAMEEN", "PROPERTY_DOC_PHOTO_TYPES"));
    while ($result = $res->GetNext()) {
      if ($curLang == "ru") {
        if (!empty($result["NAME"]))
          $arResult["DOC_TYPES"][$result["PROPERTY_DOC_CODE_VALUE"]] = $result["NAME"];
      } elseif ($curLang == "en") {
        if (!empty($result["PROPERTY_DOC_NAMEEN_VALUE"]))
          $arResult["DOC_TYPES"][$result["PROPERTY_DOC_CODE_VALUE"]] = $result["PROPERTY_DOC_NAMEEN_VALUE"];
      }
      if (!isset($arResult["DOC_PHOTO_TYPES"][$result["PROPERTY_DOC_CODE_VALUE"]])) {
        $arResult["DOC_PHOTO_TYPES"][$result["PROPERTY_DOC_CODE_VALUE"]] = array();
      }
      if(isset($result["PROPERTY_DOC_PHOTO_TYPES_VALUE"])) {
        $arResult["DOC_PHOTO_TYPES"][$result["PROPERTY_DOC_CODE_VALUE"]][] = $result["PROPERTY_DOC_PHOTO_TYPES_VALUE"];
      }
    }

    // ���������� ��������� ��� ������ ������ MySql ��� �� �������������� join'� ������ 61 �������.
    // � ������� �� ��� �������.. ��� ������� �������� ������� �� ������� ����� �������
    $hotels = CIBlockElement::GetList(
      array(),
      array("IBLOCK_ID" => $iblock_id_hotel, "ID" => $arParams["ID_HOTEL"]),
      false,
      false,
      array(
        "ID",
        "PROPERTY_ADDRESS_WEB_SERVICE",
        "PROPERTY_HOTEL_OUTPUT_CODE",
        "PROPERTY_HOTEL_ROOM_RATE",
        "PROPERTY_HOTEL_ROOM_QUOTA",
        "PROPERTY_HOTEL_RESERVATION_STATUS",
        "PROPERTY_HOTEL_CUSTOMER",
        "PROPERTY_HOTEL_CONTRACT",
        "PROPERTY_HOURS_ENABLE",
        "PROPERTY_HOTEL_TIME",
        "PROPERTY_HOTEL_TIME_FROM",
        "PROPERTY_SOAP_LOGIN",
        "PROPERTY_SOAP_PASSWORD"
      )
    );
    if ($hotel = $hotels->GetNext()) {
      $arResult["HOURS_ENABLE"] = $hotel["PROPERTY_HOURS_ENABLE_VALUE"] ? true : false;
      //��������� ��� �������� � ���-������
      //����� ���-������� - AddressWebservice
      if (strlen($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]))
        $AddressWebservice = trim($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]);
      else $AddressWebservice = trim(COption::GetOptionString('gotech.hotelonline', 'AddressWebservice'));
      //��� ������� ������� - OutputCode
      if (strlen($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]))
        $OutputCode = trim($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]);
      else $OutputCode = trim(COption::GetOptionString('gotech.hotelonline', 'OutputCode'));
      //��� ������ - RoomRate
      if (strlen($hotel["PROPERTY_HOTEL_ROOM_RATE_VALUE"]))
        $RoomRate = trim($hotel["PROPERTY_HOTEL_ROOM_RATE_VALUE"]);
      else $RoomRate = trim(COption::GetOptionString('gotech.hotelonline', 'RoomRate'));
      //��� ����� - RoomQuota
      if (strlen($hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"]))
        $RoomQuota = trim($hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"]);
      else $RoomQuota = trim(COption::GetOptionString('gotech.hotelonline', 'RoomQuota'));
      //��� ������� ����� - ReservationStatus
      if (strlen($hotel["PROPERTY_HOTEL_RESERVATION_STATUS_VALUE"]))
        $ReservationStatus = trim($hotel["PROPERTY_HOTEL_RESERVATION_STATUS_VALUE"]);
      else $ReservationStatus = trim(COption::GetOptionString('gotech.hotelonline', 'ReservationStatus'));
      //��� ����������� - Customer
      if (strlen($hotel["PROPERTY_HOTEL_CUSTOMER_VALUE"]))
        $Customer = trim($hotel["PROPERTY_HOTEL_CUSTOMER_VALUE"]);
      else $Customer = trim(COption::GetOptionString('gotech.hotelonline', 'Customer'));
      //��� �������� ����������� - Contract
      if (strlen($hotel["PROPERTY_HOTEL_CONTRACT_VALUE"]))
        $Contract = trim($hotel["PROPERTY_HOTEL_CONTRACT_VALUE"]);
      else $Contract = trim(COption::GetOptionString('gotech.hotelonline', 'Contract'));
      if (!empty($hotel["PROPERTY_HOTEL_TIME_VALUE"])) {
        $pos = strpos($hotel["PROPERTY_HOTEL_TIME_VALUE"], ":");
        if ($pos) {
          $time = substr($hotel["PROPERTY_HOTEL_TIME_VALUE"], $pos - 2, 2) . ":" . substr($hotel["PROPERTY_HOTEL_TIME_VALUE"], $pos + 1, 2) . ":00";
        } else
          $time = date("H:i:s", $hotel["PROPERTY_HOTEL_TIME_VALUE"]);
      } else $time = "00:00:00";
      if (!empty($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"])) {
        $pos = strpos($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"], ":");
        if ($pos) {
          $timeFrom = substr($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"], $pos - 2, 2) . ":" . substr($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"], $pos + 1, 2) . ":00";
        } else
          $timeFrom = date("H:i:s", $hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"]);
      } else $timeFrom = "00:00:00";
      if(!empty($hotel["PROPERTY_SOAP_LOGIN_VALUE"]))
        $SOAP_LOGIN = trim($hotel["PROPERTY_SOAP_LOGIN_VALUE"]);
      else
        $SOAP_LOGIN = "";
      if(!empty($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]))
        $SOAP_PASSWORD = trim($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]);
      else
        $SOAP_PASSWORD = "";
    }
    $country = $_SESSION["country"] ? $_SESSION["country"] : OnlineBookingSupport::getCountry();
    if (isset($arResult["CITIZENSHIP"]))
      foreach ($arResult["CITIZENSHIP"] as $key => $CITIZENSHIP)
        if ($key == $country)
          $arResult["CITIZENSHIP_SELECTED"] = $country;
    if (empty($arResult["CITIZENSHIP_SELECTED"]))
      $arResult["CITIZENSHIP_SELECTED"] = "RU";
    $arResult["IS_AGENT"] = isUserAgent();

    if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer) && $_SESSION["AUTH_CLIENT_DATA"]->IsCustomer && $_SESSION["AUTH_CLIENT_DATA"]->ProfileCode) {
      $Customer = $_SESSION["AUTH_CLIENT_DATA"]->ProfileCode;
    }

    ini_set("soap.wsdl_cache_enabled", intval(COption::GetOptionString('gotech.hotelonline', 'SOAPCache', 0)));
    $soap_params = array('trace' => 1);
    if (!empty($SOAP_LOGIN) && !empty($SOAP_PASSWORD)) {
      $soap_params['login'] = $SOAP_LOGIN;
      $soap_params['password'] = $SOAP_PASSWORD;
    }
    $soapclient = new SoapClient($AddressWebservice, $soap_params); // with auth
    //Find client
    $phone = "";
    if (!empty($_REQUEST["phone"])) {
      $phone = htmlspecialcharsEx($_REQUEST["phone"]);
    } elseif (!empty($_SESSION["phone"])) {
      $phone = htmlspecialcharsEx($_SESSION["phone"]);
    }
    $email = "";
    if (!empty($_REQUEST["email"])) {
      $email = htmlspecialcharsEx($_REQUEST["email"]);
    } elseif (!empty($_SESSION["email"])) {
      $email = htmlspecialcharsEx($_SESSION["email"]);
    }
    $arResult["CLIENT"] = array();
    if (!empty($email) || !empty($phone)) {
      $clientDataRequest = array(
        "Phone" => $phone,
        "EMail" => $email
      );
      $result = $soapclient->GetClientByPhoneAndEMail($clientDataRequest);
      $arResult["CLIENT"] = $result->return;
    }
    // ������������ ��������� ������� ������
    $pay_method_id = "";
    if (isset($_REQUEST["payment_methods_radiobuttons"]) && !empty($_REQUEST["payment_methods_radiobuttons"]) && $_REQUEST["payment_methods_radiobuttons"] != "on") {
      $splits = explode("-", $_REQUEST["payment_methods_radiobuttons"]);
      if (isset($splits[5]) && !empty($splits[5])) {
        $pay_method_id = $splits[5];
      }
    }

    $arSelect = Array("ID", "NAME", "DETAIL_TEXT", "PREVIEW_TEXT", "PROPERTY_CITIZENSHIP", "PROPERTY_NAME_EN", "PROPERTY_PAYMENT_SYSTEM", "PROPERTY_FIRST_NIGHT", "PROPERTY_DISCOUNT", "PROPERTY_IS_RECEIPT", "PROPERTY_IS_CASH", "PROPERTY_IS_LEGAL", "PROPERTY_STATUS_CODE", "ACTIVE_FROM", "ACTIVE_TO", "PROPERTY_MIN_NIGHT");
    $arFilter = Array("IBLOCK_ID" => COption::GetOptionInt('gotech.hotelonline', 'PAYMENT_METHODS_IBLOCK_ID'), "ACTIVE" => "Y", "PROPERTY_HOTEL" => $arParams["ID_HOTEL"], "CODE" => $resultSplits);
    $res = CIBlockElement::GetList(Array("SORT" => "ASC"), $arFilter, false, Array("nPageSize" => 50), $arSelect);
    while ($rz = $res->GetNextElement()) {
      $arFields = $rz->GetFields();

      $activeFromDateTime = New DateTime($arFields["ACTIVE_FROM"]);
      $activeFromDateTime->setTime(0,0,0);
      $activeToDateTime = New DateTime($arFields["ACTIVE_TO"]);
      $activeToDateTime->setTime(0,0,0);

      $need_continue = true;
      foreach ($arResult["GUESTS"] as $j => $g):
        $datetime1 = new DateTime($arResult["GUESTS"][$j]["PeriodFrom"]);
        $datetime2 = new DateTime($arResult["GUESTS"][$j]["PeriodTo"]);
        $duration = $datetime1->diff($datetime2)->days;

        if (empty($arFields["ACTIVE_TO"]) || empty($arFields["ACTIVE_FROM"]) || (!empty($arFields["ACTIVE_TO"]) && !empty($arFields["ACTIVE_FROM"]) && ($activeToDateTime >= $datetime2 || $activeFromDateTime <= $datetime1))) {
          $need_continue = false;
        }
        if (!$arFields["PROPERTY_MIN_NIGHT_VALUE"] || ($arFields["PROPERTY_MIN_NIGHT_VALUE"] && intval($arFields["PROPERTY_MIN_NIGHT_VALUE"]) <= $duration)) {
          $need_continue = false;
        } else {
          $need_continue = true;
        }
      endforeach;

      if ($need_continue) {
        continue;
      }

      if ($curLang == "ru")
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
      $arResult["PAYMENT_METHODS"][$arFields["ID"]]["STATUS_CODE"] = $arFields["PROPERTY_STATUS_CODE_VALUE"];
      if (!empty($pay_method_id) && $pay_method_id == $arFields["ID"] && !empty($arFields["PROPERTY_STATUS_CODE_VALUE"])) {
        $ReservationStatus = $arFields["PROPERTY_STATUS_CODE_VALUE"];
      }
      if (!is_array($arResult["PAYMENT_METHODS"][$arFields["ID"]]["CITIZENSHIP"])) {
        $arResult["PAYMENT_METHODS"][$arFields["ID"]]["CITIZENSHIP"] = array();
      }
      if (!empty($arFields["PROPERTY_CITIZENSHIP_VALUE"])) {
        $dbElCitizenships = CIBlockElement::GetList(array(), array(
          "IBLOCK_CODE" => "citizenship",
          "ACTIVE" => "Y",
          "ID" => $arFields["PROPERTY_CITIZENSHIP_VALUE"],
        ));
        if ($citin_el = $dbElCitizenships->GetNextElement()) {
          unset($props);
          $props = $citin_el->GetProperties();
          $arResult["PAYMENT_METHODS"][$arFields["ID"]]["CITIZENSHIP"][] = $props["ISO"]["VALUE"];
        }
      }
    }


    $arResult["ERRORS"] = array();
    if (!empty($_REQUEST["send_booking"])) {
      if ((isset($_REQUEST["agree"]) && !$_REQUEST["agree"]) || !isset($_REQUEST["agree"]))
        $arResult["ERRORS"][] = "agree";
      if ((isset($_REQUEST["subscribe"]) && !$_REQUEST["subscribe"]) || !isset($_REQUEST["subscribe"]))
        $arResult["ERRORS"][] = "subscribe";
      if (isset($_REQUEST["is_transfer"]) && $_REQUEST["is_transfer"]) {
        if (!$_REQUEST["TransferTime"]) {
          $arResult["ERRORS"][] = "transfer_time";
          if (strtotime($_REQUEST["TransferDate"]) < strtotime(date('d.m.Y'))) {
            $arResult["ERRORS"][] = "transfer_date";
          }
        } else {
          if (strtotime($_REQUEST["TransferDate"] . " " . $_REQUEST["TransferTime"]) <= strtotime(date('d.m.Y H:i'))) {
            $arResult["ERRORS"][] = "transfer_date";
          }
        }
      }
      if (!$arResult["IS_AGENT"]) {
        if (!$_REQUEST["email"] && $arResult["EMAIL_NECESSARY"] == 1)
          $arResult["ERRORS"][] = "email";
        if (!$_REQUEST["phone"] && $arResult["PHONE_NECESSARY"] == 1)
          $arResult["ERRORS"][] = "phone";
        if (!$_REQUEST["pay_fio"])
          $arResult["ERRORS"][] = "pay_fio";
        /*
        if(!$_REQUEST["email_0_0"] && $arResult["EMAIL_NECESSARY"] == 1)
          $arResult["ERRORS"][] = "email0";
        if(!$_REQUEST["phone_0_0"] && $arResult["PHONE_NECESSARY"] == 1)
          $arResult["ERRORS"][] = "phone0";
        */

        if (!$_REQUEST["email"] && !$_REQUEST["phone"] && $arResult["PHONE_NECESSARY"] != 1 && $arResult["EMAIL_NECESSARY"] != 1)
          $arResult["ERRORS"][] = "email";
        if (isset($_REQUEST["email"]) && !empty($_REQUEST["email"]) && !filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL)) {
          $arResult["ERRORS"][] = "email";
        }
      }
      /*
      if(!empty($_REQUEST["sms"]) && !$_REQUEST["phone"]){
        $arResult["ERRORS"][] = "phone";
        $arResult["ERRORS"][] = "sms";
      }
      */

      foreach ($_REQUEST as $key => $request) {
        if (strstr($key, 'firstname')) {
          $t = explode('firstname', $key);
          $_REQUEST['name' . $t[1]] = $request;

        }
      }

      foreach ($_REQUEST as $key => $request) {
        unset($ar_req);
        $ar_req = explode("_", $key);
        foreach ($arResult["FIELD"] as $k => $field) {
          if ($ar_req[0] == $k || $ar_req[0] == "Age") {
            if ($ar_req[0] == 'birthday' || $ar_req[0] == 'periodFrom' || $ar_req[0] == 'periodTo') {
              if (isset($request)) {
                if ($request) {
                  $birthday = explode('.', $request);

                  if ($arResult["HOURS_ENABLE"]) {
                    if (in_array($ar_req[0], array('periodFrom', 'periodTo'))) {
                      $vspom_array[$ar_req[1]][$ar_req[2]][$ar_req[0]] = substr($birthday[2], 0, 4) . "-" . $birthday[1] . "-" . $birthday[0] . "T" . substr($birthday[2], 5) . ":00";
                    } else {
                      $vspom_array[$ar_req[1]][$ar_req[2]][$ar_req[0]] = $birthday[2] . "-" . $birthday[1] . "-" . $birthday[0] . "T00:00:00";
                    }
                  } else {
                    if ($ar_req[0] == 'periodFrom') {
                      $vspom_array[$ar_req[1]][$ar_req[2]][$ar_req[0]] = $birthday[2] . "-" . $birthday[1] . "-" . $birthday[0] . "T" . $timeFrom;
                    } else if ($ar_req[0] == 'periodTo') {
                      $vspom_array[$ar_req[1]][$ar_req[2]][$ar_req[0]] = $birthday[2] . "-" . $birthday[1] . "-" . $birthday[0] . "T" . $time;
                    } else {
                      $vspom_array[$ar_req[1]][$ar_req[2]][$ar_req[0]] = $birthday[2] . "-" . $birthday[1] . "-" . $birthday[0] . "T00:00:00";
                    }
                  }
                } else {
                  $vspom_array[$ar_req[1]][$ar_req[2]][$ar_req[0]] = "0001-01-01T00:00:00";
                }
              } else {
                $vspom_array[$ar_req[1]][$ar_req[2]][$ar_req[0]] = "0001-01-01T00:00:00";
              }
            } else
              $vspom_array[$ar_req[1]][$ar_req[2]][$ar_req[0]] = $request;

            if ($arResult["HOURS_ENABLE"]) {
              $vspom_array[$ar_req[1]][$ar_req[2]]["key"] = $ar_req[1] . "_" . $ar_req[2];
            }
          }
        }
        if ($ar_req[0] == "guid")
          $vspom_array[$ar_req[1]][$ar_req[2]]["guid"] = $request;
        if ($ar_req[0] == "patronymic")
          $vspom_array[$ar_req[1]][$ar_req[2]]["patronymic"] = $request;
        if ($ar_req[0] == "ClientIdentityDocumentType")
          $vspom_array[$ar_req[1]][$ar_req[2]]["ClientIdentityDocumentType"] = $request;
        if ($ar_req[0] == "ClientIdentityDocumentSeries")
          $vspom_array[$ar_req[1]][$ar_req[2]]["ClientIdentityDocumentSeries"] = $request;
        if ($ar_req[0] == "ClientIdentityDocumentUnitCode")
          $vspom_array[$ar_req[1]][$ar_req[2]]["ClientIdentityDocumentUnitCode"] = $request;
        if ($ar_req[0] == "ClientIdentityDocumentIssuedBy")
          $vspom_array[$ar_req[1]][$ar_req[2]]["ClientIdentityDocumentIssuedBy"] = $request;
        if ($ar_req[0] == "ClientIdentityDocumentIssueDate") {
          if ($request) {
            $date = explode('.', $request);
            if ($ar_req[0] == 'periodFrom') {
              $vspom_array[$ar_req[1]][$ar_req[2]][$ar_req[0]] = $date[2] . "-" . $date[1] . "-" . $date[0] . "T" . $timeFrom;
            } else {
              $vspom_array[$ar_req[1]][$ar_req[2]][$ar_req[0]] = $date[2] . "-" . $date[1] . "-" . $date[0] . "T" . $time;
            }
          } else {
            $vspom_array[$ar_req[1]][$ar_req[2]][$ar_req[0]] = "0001-01-01T00:00:00";
          }
        }
        if ($ar_req[0] == "ClientIdentityDocumentNumber")
          $vspom_array[$ar_req[1]][$ar_req[2]]["ClientIdentityDocumentNumber"] = $request;
        if ($ar_req[0] == "address")
          $vspom_array[$ar_req[1]][$ar_req[2]]["address"] = $request;
        if ($ar_req[0] == "mimetype")
          $vspom_array[$ar_req[2]][$ar_req[3]]["PictureType_".$ar_req[1]] = $request;
        if ($ar_req[0] == "base64")
          $vspom_array[$ar_req[2]][$ar_req[3]]["PictureData_".$ar_req[1]] = $request;
      }
      $arRes["Guests"] = array();

      foreach ($vspom_array as $number => $ar) {
        foreach ($ar as $k => &$v)
          $v['por_n_room'] = $number . '_' . $k;
        $arRes["Guests"] = array_merge($arRes["Guests"], $ar);
      }

      foreach ($arRes["Guests"] as $id => $g) {
        if ($always_fio == 1) {
          if (empty($g["surname"])) {
            $arResult["ERRORS"][] = "surname" . $id;
          }
          if (empty($g["name"])) {
            $arResult["ERRORS"][] = "name" . $id;
          }
        }

        if ($g["birthday"] != "0001-01-01T00:00:00" && !empty($g["birthday"])) {
          $t = explode('T', $g["birthday"]);
          $t = $t[0];
          $t = explode('-', $t);
          $d = $t[2] . '.' . $t[1] . '.' . $t[0];


          if (!isDate($d))
            $arResult["ERRORS"][] = "birthday" . $id;
        } else if (empty($g["birthday"]) || $g["birthday"] == "0001-01-01T00:00:00") {
          if (!empty($g["surname"]) && $always_date_of_birth == 1)
            $arResult["ERRORS"][] = "birthday" . $id;
        }


        if (!empty($g["Age"]) && $g["Age"] != "0" && 0) {
          $birthday = $g["birthday"];
          if ($birthday) {
            // �������� ����, �����, ��� �� ���� ��������
            $bDay = substr($birthday, 8, 2);
            $bMonth = substr($birthday, 5, 2);
            $bYear = substr($birthday, 0, 4);
            // ������� ����, �����, ���
            $cDay = substr($g["periodFrom"], 8, 2);
            $cMonth = substr($g["periodFrom"], 5, 2);
            $cYear = substr($g["periodFrom"], 0, 4);

            if (($cMonth > $bMonth) || ($cMonth == $bMonth && $cDay >= $bDay)) {
              $age = ($cYear - $bYear);
            } else {
              $age = ($cYear - $bYear - 1);
            }
            if ($age != $g["Age"]) {
              $arResult["ERRORS"][] = "birthday" . $id;
              $arResult["ERRORS"][] = "age" . $id;
            }
          }
        }
      }
      if ($always_fio != 1) {
        if (empty($arRes["Guests"][0]["surname"]))
          $arResult["ERRORS"][] = "surname0";
        if (empty($arRes["Guests"][0]["name"]))
          $arResult["ERRORS"][] = "name0";
      }
      $arResult["EXTRA_SERVICES"] = array();
      $extra_services_count = 0;
      foreach ($_SESSION["SERVICES_BOOKING"] as $key => $service) {

        $filter = array(
          "IBLOCK_CODE" => "services_prices",
          "ACTIVE" => "Y",
          "ID" => $service['Id'],
        );

        $dbEl = CIBlockElement::GetList(array(), $filter);
        if ($res = $dbEl->GetNextElement()) {
          unset($fields);
          unset($props);
          $fields = $res->GetFields();
          $props = $res->GetProperties();

          if (empty($props["SERVICEPRICE"]["VALUE"]) || $props["SERVICEPRICE"]["VALUE"] == 0) {
            $servicePrice = 0;
          } else {
            $servicePrice = $props["SERVICEPRICE"]["VALUE"];
          }

          $extra_services_count++;
          if ($service["IsTransfer"]) {
            $arResult["EXTRA_SERVICES"][$service['GuestID']][] = array(
              "Service" => $service["Code"],
              "Price" => $servicePrice,
              "Quantity" => 1,
              "Remarks" => $service['GuestID'] . ', ' . $service['Id'],
              "Currency" => $_REQUEST["total_sum_currency"],
              "OrderType" => 'Transfer',
              "Department" => 'Transfer',
              "TransferRemarks" => $service['TransferRemarks'],
              "Destination" => $fields["NAME"],
              "TransferType" => $service["Code"],
              "Sum" => $servicePrice,
              "OrderDate" => OnlineBookingSupport::getDateFormat($service['TransferDate'] . " " . $service['TransferTime']),
              "PassengersNumber" => 1,
              "TransferChildseats" => $service['TransferChildseats']
            );
          } else {
            $arResult["EXTRA_SERVICES"][$service['GuestID']][] = array(
              "Service" => $service["Code"],
              "Price" => $servicePrice,
              "Quantity" => 1,
              "Remarks" => $service['GuestID'] . ', ' . $service['Id'],
              "Currency" => $_REQUEST["total_sum_currency"],
              "Sum" => $servicePrice
            );
          }
        }
      }

      if (empty($arResult["ERRORS"])) {
        if ($USER->IsAuthorized())
          $login = CUser::GetLogin();
        else $login = "";

        if (isset($arResult["AGREEMENT_ID"]) && !empty($arResult["AGREEMENT_ID"])) {
          \Bitrix\Main\UserConsent\Consent::addByContext($arResult["AGREEMENT_ID"]);
        }

        if (!$login && $_SESSION['sn'] && $_SESSION['sn_id']) {
          if ($_SESSION['sn'] == 'fb')
            $login = 'https://www.facebook.com/' . $_SESSION['sn_id'];
          else if ($_SESSION['sn'] == 'vk')
            $login = 'http://vk.com/id' . $_SESSION['sn_id'];
        }

        if ($arResult["IS_AGENT"]) {
          $paymentMethod = "";
        } else {
          $paymentMethod = COption::GetOptionString('gotech.hotelonline', 'paymentMethod', 'CC');
          if (!$paymentMethod) {
            $paymentMethod = "CC";
          }
        }

        if ($is_another_payer) {
          $payer_birthday = $_REQUEST["payer_birthday"];
          if ($payer_birthday) {
            $payer_birthday_arr = explode('.', $payer_birthday);
            $payer_birthday = $payer_birthday_arr[2] . "-" . $payer_birthday_arr[1] . "-" . $payer_birthday_arr[0] . "T00:00:00";
          } else {
            $payer_birthday = "0001-01-01T00:00:00";
          }
          $payer_document_date = $_REQUEST["payer_document_date"];
          if ($payer_document_date) {
            $payer_document_date_arr = explode('.', $payer_document_date);
            $payer_document_date = $payer_document_date_arr[2] . "-" . $payer_document_date_arr[1] . "-" . $payer_document_date_arr[0] . "T00:00:00";
          } else {
            $payer_document_date = "0001-01-01T00:00:00";
          }
          $cutomer_data = array(
            "ClientCode" => "",
            "ClientLastName" => $_REQUEST["another_pay_lastname"] ? $_REQUEST["another_pay_lastname"] : "",
            "ClientFirstName" => $_REQUEST["another_pay_name"] ? $_REQUEST["another_pay_name"] : "",
            "ClientSecondName" => $_REQUEST["another_pay_secondname"] ? $_REQUEST["another_pay_secondname"] : "",
            "ClientSex" => "",
            "ClientCitizenship" => $_REQUEST["payer_citizenship"] ? $_REQUEST["payer_citizenship"] : "",
            "ClientBirthDate" => $payer_birthday,
            "ClientPhone" => $_REQUEST["phone"],
            "ClientFax" => "",
            "ClientEMail" => $_REQUEST["email"],
            "ClientIdentityDocumentType" => $_REQUEST["payer_document_type"] ? $_REQUEST["payer_document_type"] : "",
            "ClientIdentityDocumentNumber" => $_REQUEST["payer_passport_number"] ? $_REQUEST["payer_passport_number"] : "",
            "ClientIdentityDocumentSeries" => $_REQUEST["payer_passport_series"] ? $_REQUEST["payer_passport_series"] : "",
            "ClientIdentityDocumentUnitCode" => $_REQUEST["payer_passport_unit_code"] ? $_REQUEST["payer_passport_unit_code"] : "",
            "ClientIdentityDocumentIssuedBy" => $_REQUEST["payer_passport_issued_by"] ? $_REQUEST["payer_passport_issued_by"] : "",
            "ClientIdentityDocumentValidToDate" => "0001-01-01T00:00:00",
            "ClientIdentityDocumentIssueDate" => $payer_document_date,
            "Address" => $_REQUEST["payer_address"] ? $_REQUEST["payer_address"] : "",
            "ClientRemarks" => "",
            "ClientSendSMS" => $_REQUEST["sms"] ? true : false,
            "PlaceOfBirth" => ""
          );
        } else {
          $cutomer_data = array();
        }
        foreach ($arRes["Guests"] as $key => $guest) {
          $client_data = array(
            "ClientCode" => "",
            "ClientLastName" => $guest["surname"] ? $guest["surname"] : "",
            "ClientFirstName" => $guest["name"] ? $guest["name"] : "",
            "ClientSecondName" => $guest["patronymic"] ? $guest["patronymic"] : "",
            "ClientSex" => "",
            "ClientCitizenship" => $guest["citizenship"] ? $guest["citizenship"] : "",
            "ClientBirthDate" => $guest["birthday"] ? $guest["birthday"] : "0001-01-01T00:00:00",
            "ClientPhone" => ($key == 0) ? $_REQUEST["phone"] : "",
            "ClientFax" => "",
            "ClientEMail" => ($key == 0) ? $_REQUEST["email"] : "",
            "ClientIdentityDocumentType" => $guest["ClientIdentityDocumentType"] ? $guest["ClientIdentityDocumentType"] : "",
            "ClientIdentityDocumentNumber" => $guest["ClientIdentityDocumentNumber"] ? $guest["ClientIdentityDocumentNumber"] : "",
            "ClientIdentityDocumentSeries" => $guest["ClientIdentityDocumentSeries"] ? $guest["ClientIdentityDocumentSeries"] : "",
            "ClientIdentityDocumentUnitCode" => $guest["ClientIdentityDocumentUnitCode"] ? $guest["ClientIdentityDocumentUnitCode"] : "",
            "ClientIdentityDocumentIssuedBy" => $guest["ClientIdentityDocumentIssuedBy"] ? $guest["ClientIdentityDocumentIssuedBy"] : "",
            "ClientIdentityDocumentValidToDate" => "0001-01-01T00:00:00",
            "ClientIdentityDocumentIssueDate" => $guest["ClientIdentityDocumentIssueDate"] ? $guest["ClientIdentityDocumentIssueDate"] : "0001-01-01T00:00:00",
            "Address" => $guest["address"] ? $guest["address"] : "",
            "ClientRemarks" => "",
            "ClientSendSMS" => $_REQUEST["sms"] ? true : false,
            "PlaceOfBirth" => ""
          );
          if (trim($guest['surname'] . " " . $guest['name']) == trim($_REQUEST['pay_fio'])) {
            $client_data["ClientIdentityDocumentIssuedBy"] = $_REQUEST["payer_passport_issued_by"] ? $_REQUEST["payer_passport_issued_by"] : $client_data["ClientIdentityDocumentIssuedBy"];
            $cutomer_data = array();
            foreach ($client_data as $in_key => $val) {
              $cutomer_data[$in_key] = $val;
            }
          }
          $new_cutomer_data = array();
          foreach ($cutomer_data as $in_key => $val) {
            $new_cutomer_data[$in_key] = $val;
          }
          $dop_fields = array(
            "ReservationCode" => OnlineBookingSupport::GUID(),
            "GroupCode" => "",
            "GroupDescription" => "",
            "GroupCustomer" => "",
            //"GroupClient" => $_REQUEST['pay_fio'],
            "ReservationStatus" => $ReservationStatus,
            "PeriodFrom" => $guest["periodFrom"],
            "PeriodTo" => $guest["periodTo"],
            "Hotel" => $hotel_code,
            "RoomType" => $guest["RoomType"],
            "AccommodationType" => $guest["typeOfAccommodation"],
            "ClientType" => "",
            "RoomQuota" => $guest["allotmentCode"] ? $guest["allotmentCode"] : $RoomQuota,
            "RoomRate" => $guest["RoomRateCode"] ? $guest["RoomRateCode"] : $RoomRate,
            "Customer" => $Customer,
            "Contract" => $Contract,
            "Agent" => "",
            "ContactPerson" => "",
            "NumberOfRooms" => 1,
            "NumberOfPersons" => 1,
            "Client" => $client_data,
            "ReservationRemarks" => $_REQUEST["additional_wishes"],
            "Car" => $_REQUEST["avto_number"],
            "PlannedPaymentMethod" => $paymentMethod,
            "ExternalSystemCode" => $OutputCode,
            "DoPosting" => true,
            "PromoCode" => $_SESSION["promo_code"],
            "Room" => $guest["RoomCode"] ? $guest["RoomCode"] : $guest["guid"],
            "ChargeExtraServices" => array("ChargeExtraServiceRow" => $arResult["EXTRA_SERVICES"][$guest['por_n_room']]),
            "CustomerData" => array(
              "Phone" => $_REQUEST["phone"],
              "Email" => $_REQUEST["email"],
              "TIN" => "",
              "IndividualCustomer" => $new_cutomer_data
            ),
            "IsUpgrade" => false
          );
          $WriteExternalGroupReservationRow[] = $dop_fields;
        }

        //Add record to reservations table
        $arReservationsID = array();
        foreach ($WriteExternalGroupReservationRow as $key => $reserv) {
          unset($arFields);
          $arFields = array(
            "bitrix_hotel_code" => $arParams["ID_HOTEL"]
          , "1c_group_code" => ""
          , "1c_hotel_code" => $reserv["Hotel"]
          , "guest_fullname" => ""
          , "guest_email" => ""
          , "guest_phone" => ""
          , "check_in_date" => date("d.m.Y H:i:s", strtotime($reserv["PeriodFrom"]))
          , "check_out_date" => date("d.m.Y H:i:s", strtotime($reserv["PeriodTo"]))
          , "1c_room_uuid" => $reserv["Room"]
          , "1c_room_type_code" => $reserv["RoomType"]
          , "1c_room_type_description" => ""
          , "1c_accommodation_type_code" => $reserv["AccommodationType"]
          , "1c_accommodation_type_description" => ""
          , "1c_rate_code" => $reserv["RoomRate"]
          , "1c_quota_code" => $reserv["RoomQuota"]
          , "number_of_services" => $extra_services_count
          , "document_date" => date("d.m.Y H:i:s")
          , "1c_status" => "NEW"
          , "sum" => ""
          , "total" => ""
          , "currency" => ""
          , "error_text" => ""
          );
          $ID = OnlineBookingSupport::db_add('ob_gotech_reservations', $arFields);
          $arReservationsID[] = $ID;
        }
        if (!empty($_REQUEST["TransferDate"]) && !empty($_REQUEST["TransferTime"]))
          $TransferTime = OnlineBookingSupport::getDateFormat($_REQUEST["TransferDate"] . " " . $_REQUEST["TransferTime"]);
        $ab = array(
          "WriteExternalGroupReservationRows" => array(
            "WriteExternalGroupReservationRow" => $WriteExternalGroupReservationRow,
            "Login" => $login,
            "TransferBooked" => $_REQUEST["is_transfer"] ? true : false,
            "TransferTime" => $_REQUEST["is_transfer"] ? $TransferTime : "0001-01-01T00:00:00",
            "TransferPlace" => $_REQUEST["is_transfer"] ? $_REQUEST["TransferPlace"] : "",
            "TransferRemarks" => $_REQUEST["is_transfer"] ? $_REQUEST["TransferRemarks"] : "",
            'RequestAttributes' => array(
              'SessionID' => $_REQUEST['SessionID'],
              'UserID' => $_REQUEST['UserID'],
              'utm_source' => $_REQUEST['utm_source'],
              'utm_medium' => $_REQUEST['utm_medium'],
              'utm_campaign' => $_REQUEST['utm_campaign']
            )
            //"ChargeExtraServices" => array("ChargeExtraServiceRow" => $arResult["EXTRA_SERVICES"])
          ),
          "Language" => strtoupper($curLang)
        );
        $result = $soapclient->WriteExternalGroupReservation($ab);


        if ($result->return->ErrorDescription) {
          $arResult["WS_ERROR"] = $result->return->ErrorDescription;

          CEventLog::Add(array(
            "SEVERITY" => "SECURITY",
            "AUDIT_TYPE_ID" => "WriteExternalGroupReservation",
            "MODULE_ID" => "main",
            "ITEM_ID" => "WriteExternalGroupReservation",
            "DESCRIPTION" => $arResult["WS_ERROR"],
          ));
        } else {
          unset($_SESSION["NUMBERS_BOOKING"][$arParams["ID_HOTEL"]]["NUMBERS"]);
          $_SESSION["NUMBERS_BOOKED"][$arParams["ID_HOTEL"]]["NUMBERS"] = $arResult["GUESTS"];
          $_SESSION["NUMBERS_BOOKED"]["info"]["hotel_code"] = $hotel_code;
          $_SESSION["NUMBERS_BOOKED"]["info"]["phone"] = $_REQUEST["phone"];
          $_SESSION["NUMBERS_BOOKED"]["info"]["email"] = $_REQUEST["email"];
          $_SESSION["NUMBERS_BOOKED"]["info"]["additional_wishes"] = $_REQUEST["additional_wishes"];
          $_SESSION["NUMBERS_BOOKED"]["info"]["avto_number"] = $_REQUEST["avto_number"];
          $_SESSION["NUMBERS_BOOKED"]["info"]["TransferDate"] = $_REQUEST["TransferDate"];
          $_SESSION["NUMBERS_BOOKED"]["info"]["TransferTime"] = $_REQUEST["TransferTime"];
          $_SESSION["NUMBERS_BOOKED"]["info"]["TransferPlace"] = $_REQUEST["TransferPlace"];
          $_SESSION["NUMBERS_BOOKED"]["info"]["TransferRemarks"] = $_REQUEST["TransferRemarks"];
          $_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"] = $result->return;
        }
        //Update reservation in table


        if (is_array($result->return->ExternalReservationStatusRow)) {
          $ind = 0;
          foreach ($result->return->ExternalReservationStatusRow as $key => $guest) {
            if (isset($arReservationsID[$ind])) {
              unset($arFields);
              if (!empty($result->return->ErrorDescription)) {
                $arFields = array(
                  "1c_status" => "'FAIL'"
                , "error_text" => "'" . $result->return->ErrorDescription . "'"
                );
                //Update reservation in table
                $res = OnlineBookingSupport::db_update('ob_gotech_reservations', $arFields, "WHERE id=" . $arReservationsID[$ind]);
              } else {
                $arFields = array(
                  "guest_fullname" => "'" . $result->return->UUID . "'"
                , "1c_group_code" => "'" . $guest->GuestGroup . "'"
                , "guest_email" => ""
                , "guest_phone" => ""
                , "1c_room_type_code" => "'" . $guest->RoomTypeCode . "'"
                , "1c_room_type_description" => "'" . $guest->RoomTypeDescription . "'"
                , "1c_accommodation_type_code" => "'" . $guest->AccommodationTypeCode . "'"
                , "1c_accommodation_type_description" => "'" . $guest->AccommodationTypeDescription . "'"
                , "1c_status" => "'OK'"
                , "sum" => "'" . $guest->Sum . "'"
                , "total" => "'" . $result->return->TotalSum . "'"
                , "currency" => "'" . $result->return->Currency . "'"
                , "error_text" => "'" . $result->return->ErrorDescription . "'"
                );
                //Update reservation in table
                $res = OnlineBookingSupport::db_update('ob_gotech_reservations', $arFields, "WHERE id=" . $arReservationsID[$ind]);
              }
            }
            $ind++;
          }


          if (isset($guest) && !empty($guest->GuestGroup)) {
            if (isset($_REQUEST["payment_methods_radiobuttons"]) && !empty($_REQUEST["payment_methods_radiobuttons"]) && $_REQUEST["payment_methods_radiobuttons"] != "on") {
              $splits = explode("-", $_REQUEST["payment_methods_radiobuttons"]);
              if (!empty($splits[0]) && isset($splits[2]) && $splits[2] != "1") {
                $add_link = "";
                if (isset($splits[4]) && $splits[4] == '1') {
                  $add_link = "&customer_description=" . $_REQUEST["customer_description"] . "&customer_address=" . $_REQUEST["customer_address"] . "&customer_phone=" . $_REQUEST["customer_phone"] . "&customer_email=" . $_REQUEST["customer_email"] . "&customer_tin=" . $_REQUEST["customer_tin"] . "&customer_kpp=" . $_REQUEST["customer_kpp"];
                } else {
                  $add_link = '&Language=' . $curLang;
                }

//                $bonuses_sum = $_REQUEST["final_bonuses_payment_sum"];

                $out_sum = floatval($splits[1]) / 100;
//                $bonuses_trans_id = "";
//                if ($bonuses_sum) {
//                  $out_sum = $out_sum - floatval($bonuses_sum);
//                  $bonuses_trans_id = $_REQUEST["final_bonuses_payment_trans_id"];
//                }

                if ($out_sum > 0) {
                  setcookie('gotech_cur_lang', $curLang, time() + 86400, '/');

                  $link = COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER') . "payment/payment.php?inv_id=" . $guest->GuestGroup . "&amp;hotel_id=" . $arParams["ID_HOTEL"] . "&amp;client_code=" . $guest->GuestCode . "&amp;Currency=" . $guest->Currency . "&amp;CurrencyCode=" . $guest->CurrencyCode . "&amp;inv_desc=" . $guest->GuestFullName . "&amp;first_name=" . $guest->GuestFirstName . "&amp;last_name=" . $guest->GuestLastName . "&amp;lang=" . $curLang . "&amp;email=" . $guest->GuestEMail . "&amp;phone=" . $guest->GuestPhone . "&amp;hotel=" . $hotel_code . "&amp;hotel_name=" . $arResult["HOTEL_NAME"] . "&amp;pay_sys=" . $splits[0] . "&amp;pay_method_id=" . $splits[5] . "&out_summ=" . $out_sum . "&uuid=" . $result->return->UUID . $add_link;

                  LocalRedirect($link);
                }
              }
            }
          }
        } else {
          foreach ($arReservationsID as $resKey => $reserv) {
            unset($arFields);
            if (!empty($result->return->ErrorDescription)) {
              $arFields = array(
                "1c_status" => "'FAIL'"
              , "error_text" => "'" . $result->return->ErrorDescription . "'"
              );
              //Update reservation in table
              $res = OnlineBookingSupport::db_update('ob_gotech_reservations', $arFields, "WHERE id=" . $reserv);
            } else {
              $arFields = array(
                "guest_fullname" => "'" . $result->return->UUID . "'"
              , "1c_group_code" => "'" . $result->return->GuestGroup . "'"
              , "guest_email" => ""
              , "guest_phone" => ""
              , "1c_room_type_code" => "'" . $result->return->ExternalReservationStatusRow->RoomTypeCode . "'"
              , "1c_room_type_description" => "'" . $result->return->ExternalReservationStatusRow->RoomTypeDescription . "'"
              , "1c_accommodation_type_code" => "'" . $result->return->ExternalReservationStatusRow->AccommodationTypeCode . "'"
              , "1c_accommodation_type_description" => "'" . $result->return->ExternalReservationStatusRow->AccommodationTypeDescription . "'"
              , "1c_status" => "'OK'"
              , "sum" => "'" . $result->return->ExternalReservationStatusRow->Sum . "'"
              , "total" => "'" . $result->return->TotalSum . "'"
              , "currency" => "'" . $result->return->Currency . "'"
              , "error_text" => "'" . $result->return->ErrorDescription . "'"
              );
              //Update reservation in table
              $res = OnlineBookingSupport::db_update('ob_gotech_reservations', $arFields, "WHERE id=" . $reserv);
            }
          }
          if (!empty($result->return->GuestGroup)) {
            if (isset($_REQUEST["payment_methods_radiobuttons"]) && !empty($_REQUEST["payment_methods_radiobuttons"]) && $_REQUEST["payment_methods_radiobuttons"] != "on") {
              $splits = explode("-", $_REQUEST["payment_methods_radiobuttons"]);
              if (!empty($splits[0]) && isset($splits[2]) && $splits[2] != "1") {
                $add_link = "";
                if (isset($splits[4]) && $splits[4] == '1') {
                  $add_link = "&customer_description=" . $_REQUEST["customer_description"] . "&customer_address=" . $_REQUEST["customer_address"] . "&customer_phone=" . $_REQUEST["customer_phone"] . "&customer_email=" . $_REQUEST["customer_email"] . "&customer_tin=" . $_REQUEST["customer_tin"] . "&customer_kpp=" . $_REQUEST["customer_kpp"];
                }
                $link = COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER') . "payment/payment.php?inv_id=" . $result->return->GuestGroup . "&amp;hotel_id=" . $arParams["ID_HOTEL"] . "&amp;client_code=" . $result->return->GuestCode . "&amp;Currency=" . $result->return->Currency . "&amp;CurrencyCode=" . $result->return->CurrencyCode . "&amp;inv_desc=" . $result->return->GuestFullName . "&amp;first_name=" . $result->return->GuestFirstName . "&amp;last_name=" . $result->return->GuestLastName . "&amp;lang=" . $curLang . "&amp;email=" . $result->return->GuestEMail . "&amp;phone=" . $result->return->GuestPhone . "&amp;hotel=" . $hotel_code . "&amp;hotel_name=" . $arResult["HOTEL_NAME"] . "&amp;pay_sys=" . $splits[0] . "&amp;pay_method_id=" . $splits[5] . "&out_summ=" . (floatval($splits[1]) / 100) . "&uuid=" . $result->return->UUID . $add_link;

                LocalRedirect($link);
              }
            }
          }
        }
      }
    }

    /***************************************************/
    foreach ($arResult['GUESTS'] as $key_number => $room):
      $room_rates[] = $room['RoomRateCode'];
    endforeach;

    $res = CIBlockElement::GetList(array(), array('IBLOCK_CODE' => 'rate', 'PROPERTY_RATE_CODE' => $room_rates), false, false, array('*', 'PROPERTY_*'));
    $arResult['ROOM_RATES'] = array();
    while ($ob = $res->GetNextElement()):

      $arr = $ob->GetFields();
      $arr['PROPS'] = $ob->GetProperties();
      if ($curLang == 'en')
        $arr['PROPS']['SERVICES_NAMES'] = $arr['PROPS']['SERVICES_NAMES_EN'];
      $arr['IMAGES'] = array();
      foreach ($arr['PROPS']['SERVICES_IMAGES']['VALUE'] as $k => $v):
        $arr['IMAGES'][] = CFile::ResizeImageGet($v, array('width' => 24, 'height' => 24), BX_RESIZE_IMAGE_PROPORTIONAL, true);
      endforeach;

      $arResult['ROOM_RATES'][$arr['PROPS']['RATE_CODE']['VALUE']] = $arr;

    endwhile;
    /***************************************************/

    //printf("� ������ ������ � �� ����� ������ %.5f c��",microtime(true)-start_time_object);
    if (empty($arResult["ERRORS"]) && empty($arResult["WS_ERROR"]) && !empty($_REQUEST["send_booking"]) && !empty($_SESSION["NUMBERS_BOOKED"]["info"]["Result_booking"]))
      LocalRedirect($APPLICATION->GetCurPageParam("reservation=yes", array("booking")));
    $this->IncludeComponentTemplate();
  }
}
function isUserAgent()
{
  global $USER;
  if ($USER->IsAuthorized()) {
    //if(CModule::IncludeModule("gotech.hotelonlineoffice")) {
    if (in_array(COption::GetOptionInt('gotech.hotelonline', 'USER_AGENT_GROUP'), $USER->GetUserGroupArray()))
      return true;
    else
      return false;
    //}else
    //	return false;
  } else
    return false;
}

?>
