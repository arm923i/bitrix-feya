<?
ini_set("session.use_only_cookies", false);
ini_set("session.use_trans_sid", 1);
ini_set("session.cache_limiter", "");

session_start();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<script src="/bitrix/js/onlinebooking/new/handler.js"></script>

<div id="gotech_online_booking">
	<?if (!isset($_REQUEST["Result"]))
	die;	
	$APPLICATION->IncludeComponent(
		"onlinebooking:success.pay", 
		"", 
		array(
			"RESULT" => $_REQUEST["Result"]
		)
	);?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>