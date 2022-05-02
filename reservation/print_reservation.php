<?define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<html>
	<head>
		<title><?=$APPLICATION->ShowTitle()?></title>
		<link rel="stylesheet" type="text/css" href="/bitrix/js/onlinebooking/stylesheets/base.css" />
		<link rel="stylesheet" type="text/css" href="/bitrix/js/onlinebooking/stylesheets/print.css" />
	</head>
	<body>
		<div id="online_booking">
			<?$APPLICATION->IncludeComponent("onlinebooking:print.reservation", ".default", array(
				"NUM_RESERVATION" => $_GET["reservation_id"],
				"PHONE" => $_GET["phone"],
				"EMAIL" => $_GET["email"],
				"LOGIN" => $_GET["login"],
				"HOTEL_ID" => $_GET["hotel"]				
			));?>
		</div>
	</body>
</html>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>