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

$hotel = $_REQUEST["hotel"];
$groupNumber = $_REQUEST["group"];
$sum = $_REQUEST["sum"];
$full_sum = $_REQUEST["full_sum"];
$currency = $_REQUEST["currency"];
$uuid = $_REQUEST["uuid"];
$card = $_REQUEST["card"];

$trans_id = $_REQUEST["trans_id"];
$auth_code = $_REQUEST["auth_code"];
$http_address = $_REQUEST["http_address"];
if ($http_address) {
  $http_address = rtrim($http_address, '/') . '/';
}
$hotel_token = $_REQUEST["hotel_token"];



CModule::IncludeModule("gotech.hotelonline");
$arFields = array();
$arFields["hotel"] = $hotel;
$arFields["groupCode"] = $groupNumber;
$arFields["sum"] = $sum;
$arFields["currency"] = $currency;
$arFields["uuid"] = $uuid;
$arFields["trans_id"] = $trans_id;
$arFields["auth_code"] = $auth_code;
$arFields["http_address"] = $http_address;
$arFields["hotel_token"] = $hotel_token;
$arFields["card"] = $card;
$arFields["error_text"] = "";
$arFields["status"] = 'NEW';
$ID = OnlineBookingSupport::db_add('ob_gotech_bonuses_payments', $arFields);


if ($trans_id) {
  if ($auth_code || (float)$full_sum <= (float)$sum) {
    if ($http_address && $hotel_token) {
      $is_error = false;
      if ($auth_code) {
        $ch = curl_init($http_address . "FinishBonusesPayment/");

        $jsonData = array(
          'Token' => $hotel_token,
          'TransactionID' => $trans_id,
          'AuthorizationCode' => $auth_code,
        );

        $jsonDataEncoded = json_encode($jsonData);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $output = curl_exec($ch);
        curl_close($ch);

        $output = json_decode($output);
        if ($output->Success) {

          $_SESSION["BONUS_AMOUNT"] = $output->Amount;

//        $APPLICATION->IncludeComponent(
//          "onlinebooking:success.pay",
//          "",
//          array(
//            "HOTEL_CODE" => $hotel,
//            "ORDER_ID" => (int)$groupNumber,
//            "OUT_SUM" => (float)$sum,
//            "CURRENCY" => $currency,
//            "UUID" => $uuid,
//            "BONUSES_TRANS_ID" => $trans_id,
//            "PAYMENT_SYSTEM" => "Bonuses",
//            "PAYMENT_METHOD" => "bonuses",
//            "PAYMENT_DATA" => [],
//            "NO_REDIRECT" => true
//          )
//        );

          $result = json_encode(['error' => '', 'amount' => $output->Amount]);
        } else {
          $is_error = true;
          $result = json_encode(['error' => $output->Errors[0]]);
        }
      }
      if (!$is_error && (float)$full_sum <= (float)$sum) {
          $APPLICATION->IncludeComponent(
            "onlinebooking:success.pay",
            "",
            array(
              "HOTEL_CODE" => $hotel,
              "ORDER_ID" => (int)$groupNumber,
              "OUT_SUM" => (float)$sum,
              "CURRENCY" => $currency,
              "UUID" => $uuid,
              "BONUSES_TRANS_ID" => $trans_id,
              "PAYMENT_SYSTEM" => "Bonuses",
              "PAYMENT_METHOD" => "bonuses",
              "PAYMENT_DATA" => [],
              "NO_REDIRECT" => true
            )
          );
          $result = json_encode(['error' => '']);
      }
      echo $result;
    } else {
      echo json_encode(['error' => 'Не заполнены необходимые параметры. Обратитесь к администратору отеля']);
    }
  } else {
    echo json_encode(['error' => '']);
  }
} else {
  echo json_encode(['error' => 'ID транзакции не найден. Обратитесь к администратору отеля']);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>