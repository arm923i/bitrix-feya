<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"onlinebooking:reservation", 
	".default",
	array(
		"ID_HOTEL" => $_GET["hotel"]
	), 
	$component
);?>
<div id="gotech_search_choose">
	<?$APPLICATION->IncludeComponent(
		"onlinebooking:reservation.chose", 
		".default", 
		array(
			"ID_HOTEL" => $_GET["hotel"] ? $_GET["hotel"] : $arResult["HOTEL_ID"], 
			"CURR_PAGE" => $APPLICATION->GetCurPageParam("booking=yes")
		)
	);?>
</div>