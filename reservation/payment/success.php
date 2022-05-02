<?
ini_set("session.use_only_cookies", false);
ini_set("session.use_trans_sid", 1);
ini_set("session.cache_limiter", "");

session_start();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<div id="online_booking">
	<?if (!isset($_REQUEST["OutSum"]) ||
		!isset($_REQUEST["InvId"]))
	die;

  $pay_method_id = $_REQUEST["pay_method_id"];

  $payment_method_code = "";
  if (!empty($pay_method_id)) {
    $arSelect = Array("ID", "NAME", "CODE");
    $arFilter = Array(
      "IBLOCK_ID"=>COption::GetOptionInt('gotech.hotelonline', 'PAYMENT_METHODS_IBLOCK_ID'),
      "ID"=>$pay_method_id
    );
    $pmRes = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
    if($rz = $pmRes->GetNextElement())
    {
      $arFields = $rz->GetFields();
      $payment_method_code = $arFields["CODE"];
    }
  }

	$APPLICATION->IncludeComponent(
		"onlinebooking:success.pay",
		"",
		array(
      "PAYMENT_METHOD" => $payment_method_code,
			"HOTEL_CODE" => $_REQUEST["SHP_hotel"],
			"ORDER_ID" => $_REQUEST["InvId"],
			"OUT_SUM" => $_REQUEST["OutSum"],
			"PAYMENT_DATA" => $_REQUEST["PAYMENT_DATA"],
			"PAYMENT_SYSTEM" => $_REQUEST["PAYMENT_SYSTEM"],
			"CURRENCY" => $_REQUEST["Currency"],
			"UUID" => $_REQUEST["uuid"]
		)
	);?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
