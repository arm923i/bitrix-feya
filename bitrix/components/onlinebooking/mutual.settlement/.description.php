<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("MUTUAL_SETTLEMENT"),
	"DESCRIPTION" => GetMessage("MUTUAL_SETTLEMENT_DESCRIPTION"),
	"ICON" => "/images/logo_component.gif",
	"COMPLEX" => "Y",
	"SORT" => 12,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "agent_office",
			"NAME" => GetMessage("ONLINE_BOOKING_OFFICE_AGENT"),
			"SORT" => 1,
		)
	),
);
?>