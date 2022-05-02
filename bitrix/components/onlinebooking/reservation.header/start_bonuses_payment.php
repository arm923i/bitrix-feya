<?
ini_set("session.use_only_cookies", false);
ini_set("session.use_trans_sid", 1);
ini_set("session.cache_limiter", "");

session_start();
?>
<?
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);

$card = $_REQUEST["card"];
$http_address = $_REQUEST["http_address"];
$sum = $_REQUEST["sum"];
if ($http_address) {
  $http_address = rtrim($http_address, '/') . '/';
}
$hotel_token = $_REQUEST["hotel_token"];


if ($card) {
  if ($http_address && $hotel_token) {
    $ch = curl_init($http_address . "StartBonusesPayment/");
//    $phone = "7" . ltrim($client_phone, '7..8');

    $jsonData = array(
      'Token' => $hotel_token,
      'Card' => $card,
      'Author' => '1CBITRIX',
      'Amount' => $sum,
      'Source' => $_SERVER["SERVER_NAME"],
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
      echo json_encode(['error' => '', 'trans_id' => $output->TransactionID, 'success' => true, 'code_is_sent' => $output->CodeIsSent]);
    } else {
      echo json_encode(['error' => $output->Errors[0], 'success' => false, 'code_is_sent' => false]);
    }
  } else {
    echo json_encode(['error' => 'Не заполнены необходимые параметры. Обратитесь к администратору отеля', 'success' => false, 'code_is_sent' => false]);
  }
} else {
  echo json_encode(['error' => 'Номер телефона не задан', 'success' => false, 'code_is_sent' => false]);
}


?>