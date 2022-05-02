<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="wrapper">	
	<?$APPLICATION->IncludeComponent(
		"onlinebooking:reservation.guests", 
		".default", 
		array(
			"ID_HOTEL" => $_GET["hotel"] ? $_GET["hotel"] : $arResult["HOTEL_ID"]
		), 
		$component
	);?>
</div>