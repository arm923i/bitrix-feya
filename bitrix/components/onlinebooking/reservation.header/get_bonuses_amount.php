<?
ini_set("session.use_only_cookies", false);
ini_set("session.use_trans_sid", 1);
ini_set("session.cache_limiter", "");

session_start();
?>
<?
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);

$http_address = $_REQUEST["http_address"];
if ($http_address) {
  $http_address = rtrim($http_address, '/') . '/';
}
$hotel_token = $_REQUEST["hotel_token"];


if ($http_address && $hotel_token) {
  if (isset($_SESSION["AUTH_CLIENT_DATA"]->ProfileCode)) {
    $card = $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientPhone;
    if ($_SESSION["AUTH_CLIENT_DATA"]->CardUUID && !empty($_SESSION["AUTH_CLIENT_DATA"]->CardUUID)) {
      $card = $_SESSION["AUTH_CLIENT_DATA"]->CardUUID;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $http_address . "GetBonusesAmount/?Token=" . $hotel_token . "&Card=" . $card);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $bonuses_output = curl_exec($ch);
    curl_close($ch);

    $bonuses_output = json_decode($bonuses_output);
    if ($bonuses_output->Success) {
      $_SESSION["BONUS_AMOUNT"] = $bonuses_output->Amount;

      echo json_encode(['amount' => $_SESSION["BONUS_AMOUNT"]]);
    } else {
      echo json_encode(['amount' => '']);
    }
  } else {
    echo json_encode(['amount' => '']);
  }
} else {
  echo json_encode(['amount' => '']);
}
?>