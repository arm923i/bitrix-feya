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
	$APPLICATION->SetPageProperty("description", GetMessage("SITE_REGISTRATION"));
	$APPLICATION->SetPageProperty("h1", GetMessage("REGISTRATION"));
	$APPLICATION->SetTitle(GetMessage("REGISTRATION"));

	$arParamsToDelete = array(
		"login",
		"logout",
		"register",
		"forgot_password",
		"change_password",
		"confirm_registration",
		"confirm_code",
		"confirm_user_id",
	);


	if(defined("AUTH_404"))
	{
		$arResult["AUTH_URL"] = SITE_DIR."register.php";
	}
	else
	{
		$arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("forgot_password=yes",$arParamsToDelete);
	}

	$arResult["BACKURL"] = $APPLICATION->GetCurPageParam("",$arParamsToDelete);


	$arResult["AUTH_AUTH_URL"] = $APPLICATION->GetCurPageParam("login=yes",$arParamsToDelete);

	foreach ($arResult as $key => $value)
	{
		if (!is_array($value)) $arResult[$key] = htmlspecialchars($value);
	}

	$arResult["LAST_LOGIN"] = htmlspecialchars($_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"]);

	if($_REQUEST["forgot_password"]=='yes'){
		$APPLICATION->SetPageProperty("h1", GetMessage("FORGOT_PASS"));
		$APPLICATION->SetTitle(GetMessage("FORGOT_PASS"));
		$this->IncludeComponentTemplate();
	}
	elseif($_REQUEST["change_password"] == "yes"){
		$APPLICATION->SetTitle(GetMessage("CHANGE_PASS"));
		$APPLICATION->SetPageProperty("h1", GetMessage("CHANGE_PASS"));
		$APPLICATION->IncludeComponent("bitrix:system.auth.changepasswd", "office.template", array(),false);
		if($_REQUEST["change"] == "Y")
			echo GetMessage("RESULT_OK");
	}
	else {
		$this->IncludeComponentTemplate();
	}
	
}
?>