<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("SUCCESS_PAY"),
	"DESCRIPTION" => GetMessage("SUCCESS_PAY_DESCRIPTION"),
	"ICON" => "/images/logo_component.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 100,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "booking",
			"NAME" => GetMessage("ONLINE_BOOKING"),
			"SORT" => 10,
		)
	),
);
?>