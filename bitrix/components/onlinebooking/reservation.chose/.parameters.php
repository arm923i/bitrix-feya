<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("gotech.hotelonline"))
	return;
$arComponentParameters = array(
	"PARAMETERS" => array(
		"ID_HOTEL" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("ID_HOTEL"),
			"TYPE" => "STRING"
		),
		"LANGUAGE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("LANGUAGE"),
			"TYPE" => "STRING"
		),
		"TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("TYPE"),
			"TYPE" => "STRING",
			"DEFAULT" => "NUMBER"
		)
	)
);
?>