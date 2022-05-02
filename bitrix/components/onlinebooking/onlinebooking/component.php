<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("iblock")) {
	echo GetMessage("IBLOCK_NOT_INCLUDE");
	return;
}
elseif(!CModule::IncludeModule("gotech.hotelonline")) {
	ShowError(GetMessage("ONLINEBOOKING_MODULE_NOT_INSTALLED"));
	return;
}
else {
    if (isset($_GET["hotel"]) && !empty($_GET["hotel"]) && isset($_GET["reservation"]) && !empty($_GET["reservation"]) && isset($_GET["data"]) && !empty($_GET["data"])) {
        header("Location: my.php?search=Y&hotel=".$_GET["hotel"]."&reservation=".$_GET["reservation"]."&data=".$_GET["data"]);
        die();
    }
  if (isset($_GET["hotel"]) && !empty($_GET["hotel"]) && isset($_GET["uuid"]) && !empty($_GET["uuid"])) {
    header("Location: my.php?hotel=".$_GET["hotel"]."&uuid=".$_GET["uuid"]);
    die();
  }
	$MOD_RIGHT = $APPLICATION->GetGroupRight("gotech.hotelonline");
	if($MOD_RIGHT != "S") {
		$componentPage = "";

		$iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
		$hotels = CIBlockElement::GetList(
			array(),
			array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y"),
			false,
			false,
			array("ID")
		);
		while($res = $hotels->GetNext())
			$array[] = $res["ID"];
		if(count($array) == 1) {
			$arResult["HOTEL_ID"] = $array[0];
			if(isset($_GET["booking"]) && $_GET["booking"] == 'yes' && !empty($_SESSION["NUMBERS_BOOKING"]))
				$componentPage = "apply";
			elseif(isset($_GET["reservation"]) && $_GET["reservation"] == 'yes' && !empty($_SESSION["NUMBERS_BOOKING"]))
				$componentPage = "reservation";
			elseif(isset($_GET["number"]))
				$componentPage = "number";
			else
				$componentPage = "booking";
			setcookie("HOTEL_ID", $arResult["HOTEL_ID"]);
			$_SESSION["HOTEL_ID"] = $arResult["HOTEL_ID"];
			COption::SetOptionInt('gotech.hotelonline', 'CHOOSEN_HOTEL_ID', $arResult["HOTEL_ID"]);
		}
		elseif(count($array) > 1) {
			if(isset($_GET["hotel"]) && !empty($_GET["hotel"])){
				setcookie("HOTEL_ID", $_GET["hotel"]);
                $_SESSION["HOTEL_ID"] = $_GET["hotel"];
				COption::SetOptionInt('gotech.hotelonline', 'CHOOSEN_HOTEL_ID', $_GET["hotel"]);
			}
			if(isset($_GET["hotel"]) && !empty($_GET["hotel"]) && isset($_GET["booking"]) && $_GET["booking"] == 'yes' && !empty($_SESSION["NUMBERS_BOOKING"]))
				$componentPage = "apply";
			elseif(isset($_GET["hotel"]) && !empty($_GET["hotel"]) && isset($_GET["reservation"]) && $_GET["reservation"] == 'yes' && !empty($_SESSION["NUMBERS_BOOKING"]))
				$componentPage = "reservation";
			elseif(isset($_GET["hotel"]) && !empty($_GET["hotel"]) && isset($_GET["number"]))
				$componentPage = "number";
			elseif(isset($_GET["hotel"]) && !empty($_GET["hotel"]))
				$componentPage = "booking";
			else
				$componentPage = "main";
		}
        $APPLICATION->IncludeComponent("onlinebooking:reservation.header", "");
        if($this->includeComponentLang("", OnlineBookingSupport::getLanguage()) == NULL){
            __IncludeLang(dirname(__FILE__)."/lang/".OnlineBookingSupport::getLanguage()."/component.php");
        }
		if($_REQUEST['restore_password'] == 'Y')
		{
			$APPLICATION->IncludeComponent("onlinebooking:agent.passrestore", "");
		}
		else
		{
			$APPLICATION->SetTitle(GetMessage("TITLE_MODULE"));
			$this->IncludeComponentTemplate($componentPage);
		}
	}
	else {
		ShowError(GetMessage("NOT_ACCESS"));
		return;
	}
}
?>
