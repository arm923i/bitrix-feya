<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"NUM_RESERVATION" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("NUM_RESERVATION"),
			"TYPE" => "STRING"
		),
		"EMAIL" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("EMAIL"),
			"TYPE" => "STRING"
		),
		"PHONE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("PHONE"),
			"TYPE" => "STRING"
		)
	)
);
?>
