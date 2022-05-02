<?
ini_set("session.use_only_cookies", false);
ini_set("session.use_trans_sid", 1);
ini_set("session.cache_limiter", "");

session_start();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Îøèáêà!");?>
<div id="online_booking">
	<?$APPLICATION->IncludeComponent(
		"onlinebooking:fail.pay", 
		"", 
		array(
			"HOTEL_CODE" => $_REQUEST["SHP_hotel"],
			"ORDER_ID" => $_REQUEST["InvId"], 
			"OUT_SUM" => $_REQUEST["OutSum"],
			"CURRENCY" => $_REQUEST["Currency"],
			"UUID" => $_REQUEST["uuid"]
		)
	);?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>