<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("T_IBLOCK_DESC_CATALOG"),
	"DESCRIPTION" => GetMessage("IBLOCK_MAIN_PAGE_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/logo_component.gif",
	"COMPLEX" => "Y",
	"SORT" => 10,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "booking",
			"NAME" => GetMessage("ONLINE_BOOKING"),
			"SORT" => 1,
		)
	),
);
?>