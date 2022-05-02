<?
ini_set("session.use_only_cookies", false);
ini_set("session.use_trans_sid", 1);
ini_set("session.cache_limiter", "");

session_start();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
	// if(CModule::IncludeModule("gotech.hotelonline")){
	// 	$OnlineBookingSupport = new OnlineBookingSupport();
	// 	$res = $OnlineBookingSupport->detect_mobile_device();
	// 	if($res){
	// 		if(CModule::IncludeModule("gotech.hotelonlinemobile")){
	// 			header('Location: '.COption::GetOptionString("gotech.hotelonlinemobile", 'PATH_TO_MOBILE').'index.php');
	// 			Exit;
	// 		}
	// 	}
	// }
?>
<!--<div id="blind" style="display: none;"><img style="top: 45%;position: relative;left: 45%; height: auto;" src="/bitrix/js/onlinebooking/29.gif" alt="Waiting..." /></div>-->
<script src="/bitrix/js/onlinebooking/new/handler.js"></script>

<div id="gotech_online_booking">
	<?$APPLICATION->IncludeComponent("onlinebooking:onlinebooking", ".default");?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>