<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("MY_RESERVATION"),
	"DESCRIPTION" => GetMessage("MY_RESERVATION_DESCRIPTION"),
	"ICON" => "/images/logo_component.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 80,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "booking",
			"NAME" => GetMessage("ONLINE_BOOKING"),
			"SORT" => 3,
		)
	),
);
?>