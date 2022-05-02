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

  if ( $_SERVER['REQUEST_URI'] != strtolower ( $_SERVER['REQUEST_URI'] ) ) {
    $need_reload = false;
    $r_params = array();
    $new_params = "";
    foreach($_GET as $key => $val)
    {

      if ($new_params) {
        $new_params .= "&";
      }
      if ($key != strtolower($key)) {
        $need_reload = true;
        $new_params .= strtolower($key)."=".$val;
      } else {
        $new_params .= $key."=".$val;
      }
      $r_params[] = $key;
    }
    if ($need_reload) {
      $url = $APPLICATION->GetCurPageParam($new_params, $r_params);
      LocalRedirect($url);
    }
  }

	if($_REQUEST['change_password'] == 'yes' && $_REQUEST['USER_CHECKWORD'] && $_REQUEST['password_restore'] != 'Y')
	{
		$url = $APPLICATION->GetCurPageParam('password_restore=Y');
		LocalRedirect($url);
	}

    if($_REQUEST['logout'] == 'yes' && isset($_SESSION["AUTH_CLIENT_DATA"]))
    {
        unset($_SESSION["AUTH_CLIENT_DATA"]);
        unset($_SESSION["BONUS_AMOUNT"]);
        $url = $APPLICATION->GetCurPageParam("", array("logout"));
        LocalRedirect($url);
    }

	$fb_app_id = COption::GetOptionInt('gotech.hotelonline', 'facebook_app_id');
	$fb_app_secret = COption::GetOptionInt('gotech.hotelonline', 'facebook_app_secret');
	$vk_app_id = COption::GetOptionInt('gotech.hotelonline', 'vk_app_id');
	$vk_app_secret = COption::GetOptionString('gotech.hotelonline', 'vk_app_secret');

	$arResult['FB'] = false;
	if($fb_app_id && $fb_app_secret) $arResult['FB'] = true;
	$arResult['VK'] = false;
	if($vk_app_id && $vk_app_secret) $arResult['VK'] = true;

	if($_REQUEST['sn_logout'] == 'Y'):
		unset($_SESSION['sn']);
		unset($_SESSION['sn_id']);
		unset($_SESSION['sn_link']);
		unset($_SESSION['sn_name']);
		unset($_SESSION['sn_last_name']);
		unset($_SESSION['sn_email']);
		unset($_SESSION['sn_phone']);
		LocalRedirect($APPLICATION->GetCurPageParam('',array('sn_logout')));
	endif;

	if(!$USER->IsAuthorized())
	{
		//facebook
		$arResult['FACEBOOK_LOGIN_URL'] = OnlineBookingSupport::getFacebookLoginUrl();
		$arResult['VK_LOGIN_URL'] = OnlineBookingSupport::getVkLoginUrl();

		if($_REQUEST['sn'] && $_REQUEST['sn'] == 'facebook')
		{
			$fb_data = OnlineBookingSupport::facebookAuthRegister();

			if(is_array($fb_data))
			{
				$_SESSION['sn'] = 'fb';
				$_SESSION['sn_id'] = $fb_data['fbid'];
				$_SESSION['sn_link'] = 'http://facebook.com/'.$fb_data['fbid'];
				$_SESSION['sn_name'] = $fb_data['fbname'];
				$_SESSION['sn_last_name'] = $fb_data['fblname'];
				$_SESSION['sn_email'] = $fb_data['fbemail'];
				$_SESSION['sn_phone'] = $fb_data['fbphone'];

				$rsUser = $USER->GetByLogin($fb_data['fbemail']);
			}
		}

		if($_REQUEST['sn'] && $_REQUEST['sn'] == 'vk')
		{
			$vk_data = OnlineBookingSupport::getVkUserData();

			if(is_array($vk_data))
			{
				$_SESSION['sn'] = 'vk';
				$_SESSION['sn_id'] = $vk_data['id'];
				$_SESSION['sn_link'] = 'http://vk.com/'.$vk_data['uid'];
				$_SESSION['sn_name'] = $vk_data['first_name'];
				$_SESSION['sn_last_name'] = $vk_data['last_name'];
				$_SESSION['sn_email'] = $vk_data['email'];
				$_SESSION['sn_phone'] = $vk_data['phone'];

				$rsUser = $USER->GetByLogin($vk_data['email']);
			}

		}



	}



	if(isset($_REQUEST["lang"]) && !empty($_REQUEST["lang"]) && ($_REQUEST["lang"] == "ru" || $_REQUEST["lang"] == "en"))
	{
		COption::SetOptionString('gotech.hotelonline', 'LANG', $_REQUEST["lang"]);
		setcookie("language", "");
		setcookie("language", $_REQUEST["lang"]);
        $_SESSION["language"] = $_REQUEST["lang"];
	}else
		if($_SERVER["HTTP_REFERER"] == NULL)
		{
			COption::SetOptionString('gotech.hotelonline', 'LANG', "");
			setcookie("language", "");
            $_SESSION["language"] = "";
		}
	if($this->includeComponentLang("", OnlineBookingSupport::getLanguage()) == NULL){
		__IncludeLang(dirname(__FILE__)."/lang/".OnlineBookingSupport::getLanguage()."/component.php");
	}

	if(isset($_GET["language"])) {
		setcookie("language", "");
		setcookie("language", $_GET["language"]);
		LocalRedirect($APPLICATION->GetCurPageParam("", array("language")));
	}
	$arResult["today"] = OnlineBookingSupport::getFullDate(OnlineBookingSupport::getLanguage());
	if($USER->IsAuthorized()) {
		//if(CModule::IncludeModule("gotech.hotelonlineoffice")) {
			if(in_array(COption::GetOptionInt('gotech.hotelonline', 'USER_AGENT_GROUP'), $USER->GetUserGroupArray()))
				$arResult["USER_OFFICE"] = 1;
			else
				$arResult["USER_OFFICE"] = 0;
		//}else
		//	$arResult["USER_OFFICE"] = 0;
	}
	else
		$arResult["USER_OFFICE"] = 2;

	//if(!$_REQUEST["hotel"] && !$_SESSION["HOTEL_ID"] && $APPLICATION->GetCurPage() != '/' && (strripos($_SERVER["SCRIPT_NAME"], "index.php") === false || strripos($_SERVER["SCRIPT_NAME"], "password-restore.php") === false)){


	//echo $APPLICATION->GetCurPage();die;
	if(!$_REQUEST["hotel"] && !$_REQUEST["hotel_code"] && !$_SESSION["HOTEL_ID"]
    && $APPLICATION->GetCurPage() != COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER', "/")
    && $APPLICATION->GetCurPage() != '/'
    && $APPLICATION->GetCurPage() != COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER', "/").'history.php'
    && $APPLICATION->GetCurPage() != COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER', "/").'index.php'){
		LocalRedirect(COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER', ""));
	}
	$hotel_id = 0;
	if(isset($_SESSION["HOTEL_ID"]) && !empty($_SESSION["HOTEL_ID"])){
		$hotel_id = $_SESSION["HOTEL_ID"];
	}elseif(isset($_REQUEST["hotel"]) && !empty($_REQUEST["hotel"])){
		$hotel_id = $_REQUEST["hotel"];
	}elseif(isset($_REQUEST["hotel_code"]) && !empty($_REQUEST["hotel_code"])){
    $hotel_id = $_REQUEST["hotel_code"];
  }
  if (empty($_SESSION["HOTEL_ID"])) {
    setcookie("HOTEL_ID", $hotel_id);
    $_SESSION["HOTEL_ID"] = $hotel_id;
    COption::SetOptionInt('gotech.hotelonline', 'CHOOSEN_HOTEL_ID', $hotel_id);
  }
	$arResult["add_header_text"] = "";
	if(!empty($hotel_id)){
		$iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
		$filter = array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y",
      array(
        "LOGIC" => "OR",
        array("ID" => $hotel_id),
        array("PROPERTY_HOTEL_CODE" => $hotel_id),
      )
    );
		$hotels = CIBlockElement::GetList(
			array(),
			$filter,
			false,
			false,
			array(
				"ID",
				"NAME",
				"PROPERTY_ADD_HEADER_TEXT_RU",
				"PROPERTY_ADD_HEADER_TEXT_EN",
				"PROPERTY_SN_AUTH",
				"PROPERTY_HOTEL_SITE",
				"PROPERTY_HOTEL_NAME_EN",
				'PREVIEW_PICTURE',
                'PROPERTY_CURRENCY',
                'PROPERTY_USE_OFFICE_MODULE',
                "PROPERTY_ADDRESS_WEB_SERVICE",
                "PROPERTY_HOTEL_CODE",
                "PROPERTY_BONUS_SYSTEM_ENABLE",
                "PROPERTY_BONUS_SYSTEM_WEB_ADDRESS",
                "PROPERTY_BONUS_SYSTEM_HOTEL_TOKEN",
        "PROPERTY_HOTEL_TIME",
        "PROPERTY_HOTEL_TIME_FROM"
			)
		);

		if($hotel = $hotels->GetNext())
		{
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

      $arResult["TIME_FROM"] = $timeFrom;
      $arResult["TIME_TO"] = $time;

      $arResult["WSDL"] = trim($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"]);
      $arResult["HOTEL_CODE"] = $hotel["PROPERTY_HOTEL_CODE_VALUE"];

      if(!empty($hotel["PROPERTY_CURRENCY_VALUE"]) && $hotel["PROPERTY_CURRENCY_VALUE"] != NULL) {
        $arResult["CURRENCY"] = $hotel["PROPERTY_CURRENCY_ENUM_ID"];
        $arResult["CURRENCY_NAME"] = $hotel["PROPERTY_CURRENCY_VALUE"];
      }

			if(OnlineBookingSupport::getLanguage() == 'ru'){
				if(strlen($hotel["~PROPERTY_ADD_HEADER_TEXT_RU_VALUE"]["TEXT"]))
					$arResult["add_header_text"] = $hotel["~PROPERTY_ADD_HEADER_TEXT_RU_VALUE"]["TEXT"];
			}else{
				if(strlen($hotel["PROPERTY_ADD_HEADER_TEXT_EN_VALUE"]["TEXT"]))
					$arResult["add_header_text"] = $hotel["~PROPERTY_ADD_HEADER_TEXT_EN_VALUE"]["TEXT"];
			}

            $arResult['BONUS_SYSTEM_WEB_ADDRESS'] = "";
            $arResult['BONUS_SYSTEM_HOTEL_TOKEN'] = "";
			if ($hotel['PROPERTY_BONUS_SYSTEM_ENABLE_VALUE']) {
			    $arResult['BONUS_SYSTEM_WEB_ADDRESS'] = $hotel['PROPERTY_BONUS_SYSTEM_WEB_ADDRESS_VALUE'];
                $arResult['BONUS_SYSTEM_HOTEL_TOKEN'] = $hotel['PROPERTY_BONUS_SYSTEM_HOTEL_TOKEN_VALUE'];
            }

			$arResult['SN_AUTH'] = $hotel['PROPERTY_SN_AUTH_VALUE'] ? true : false;

            $arResult['USE_OFFICE_MODULE'] = $hotel['PROPERTY_USE_OFFICE_MODULE_VALUE'] ? true : false;

			$arResult['LOGO'] = CFile::ResizeImageGet($hotel['PREVIEW_PICTURE'], array('width'=>138, 'height'=>71), BX_RESIZE_IMAGE_PROPORTIONAL, true);

			 //$arResult['NAME'] = $_COOKIE["gotech_cur_lang"] == 'en' ? $hotel['PROPERTY_HOTEL_NAME_EN_VALUE'] : $hotel['NAME'];
			 $arResult['NAME'] = OnlineBookingSupport::getLanguage() == 'en' ? $hotel['PROPERTY_HOTEL_NAME_EN_VALUE'] : $hotel['NAME'];
			 $arResult['SITE'] = $hotel['PROPERTY_HOTEL_SITE_VALUE'];
			 $arResult['HOTEL_ID'] = $hotel['ID'];

		}

	}

	$this->IncludeComponentTemplate();
}
?>
