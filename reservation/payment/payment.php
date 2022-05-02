<?
ini_set("session.use_only_cookies", false);
ini_set("session.use_trans_sid", 1);
ini_set("session.cache_limiter", "");

session_start();
?>
<?
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$APPLICATION->IncludeComponent("onlinebooking:reservation.payment", "");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>