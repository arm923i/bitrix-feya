<?
ini_set("session.use_only_cookies", false);
ini_set("session.use_trans_sid", 1);
ini_set("session.cache_limiter", "");

session_start();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<script src="/bitrix/js/onlinebooking/new/handler.js"></script>
<div id="gotech_online_booking">
	<?
		$hotelCode = $_REQUEST["hotel_code"] ? $_REQUEST["hotel_code"] : ($_REQUEST["hotel"] ? $_REQUEST["hotel"] : "");
	?>
	<?$APPLICATION->IncludeComponent("onlinebooking:my.reservation", ".default", array("UUID" => $_REQUEST["uuid"], "HOTEL_CODE" => $hotelCode));?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>