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

	if(isset($_REQUEST["inv_id"]) && isset($_REQUEST["out_summ"]) && isset($_REQUEST["pay_sys"]) && COption::GetOptionString('gotech.hotelonline', 'includePaySys') == 1) {
		$payment_systems_iblock_id = COption::GetOptionInt('gotech.hotelonline', 'PAYMENT_SYSTEMS_IBLOCK_ID');
		$arSelect = Array("ID", "NAME", "CODE");
		$arFilter = Array("IBLOCK_ID"=>$payment_systems_iblock_id, "ID"=>$_REQUEST["pay_sys"]);
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);

		if($el = $res->GetNextElement())
		{
			$arFields = $el->GetFields();
			$arResult["SYSTEM_NAME"] = $arFields["NAME"];

			$this->IncludeComponentTemplate();
		}
	}
}
?>
