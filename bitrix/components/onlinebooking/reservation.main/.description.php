<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("MAIN_RESERVATION"),
	"DESCRIPTION" => GetMessage("MAIN_RESERVATION_DESCRIPTION"),
	"ICON" => "/images/logo_component.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 20,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "booking",
			"NAME" => GetMessage("ONLINE_BOOKING"),
			"SORT" => 8,
		)
	),
);
?>