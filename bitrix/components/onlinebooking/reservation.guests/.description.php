<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("GUESTS_RESERVATION"),
	"DESCRIPTION" => GetMessage("GUESTS_RESERVATION_DESCRIPTION"),
	"ICON" => "/images/logo_component.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 40,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "booking",
			"NAME" => GetMessage("ONLINE_BOOKING"),
			"SORT" => 2,
		)
	),
);
?>