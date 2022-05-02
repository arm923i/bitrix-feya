<?
ini_set("session.use_only_cookies", false);
ini_set("session.use_trans_sid", 1);
ini_set("session.cache_limiter", "");

session_start();
?>
<?
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);

$WSDL = $_REQUEST["wsdl"];
$phone = $_REQUEST["phone"];
$hotel_code = $_REQUEST["hotel"];
$code = $_REQUEST["code"];
$language = $_REQUEST["language"] ? $_REQUEST["language"] : "RU";

$http_address = $_REQUEST["http_address"];
if ($http_address) {
    $http_address = rtrim($http_address, '/') . '/';
}
$hotel_token = $_REQUEST["hotel_token"];

$use_soap = $_REQUEST["use_soap"] === "true";

if ($use_soap) {
  $params = array(
    'Hotel' => $hotel_code,
    'Phone' => $phone,
    'Code' => $code,
    'ExternalSystemCode' => "1CBITRIX",
    'LanguageCode' => $language
  );
  ini_set("soap.wsdl_cache_enabled", 0);
  ini_set("soap.wsdl_cache_ttl", "86400");
  $soap_params = array('trace' => 1);
  $soapclient = new SoapClient(trim($WSDL), $soap_params);
  $res = $soapclient->VerifyClient($params);

  if (!isset($res->return->ErrorDescription)) {
    $_SESSION["AUTH_CLIENT_DATA"] = $res->return;

    if (isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer) && empty($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientPhone)) {
      $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientPhone = $phone;
    }

    if (isset($_SESSION["AUTH_CLIENT_DATA"]->ProfileCode) && $http_address && $hotel_token) {

      $card = $phone;
      if ($_SESSION["AUTH_CLIENT_DATA"]->CardUUID && !empty($_SESSION["AUTH_CLIENT_DATA"]->CardUUID)) {
        $card = $_SESSION["AUTH_CLIENT_DATA"]->CardUUID;
      }

      $ch = curl_init();
      //$phone = "7".ltrim($phone, '7..8');
      curl_setopt($ch, CURLOPT_URL, $http_address."GetBonusesAmount/?Token=".$hotel_token."&Card=".$card);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      curl_close($ch);

      $output = json_decode($output);
      if ($output->Success) {
        $_SESSION["BONUS_AMOUNT"] = $output->Amount;
      }
    }
  }

  echo json_encode($res->return);
} else {
  if ($http_address && $hotel_token) {
    //$phone = "7".ltrim($phone, '7..8');
    $data = array("Token" => $hotel_token, "Card" => $phone, "VerificationСode" => $code);

    $data_string = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $http_address."VerifyClient");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string))
    );
    $output = curl_exec($ch);
    curl_close($ch);

    $output = json_decode($output);

    if ($output->Success) {
      if (!$output->ErrorDescription) {
        $_SESSION["AUTH_CLIENT_DATA"] = clone($output);
        $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer = clone($output);

        if (isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer) && empty($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientPhone)) {
          $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientPhone = $phone;
        }

        if (isset($_SESSION["AUTH_CLIENT_DATA"]->ProfileCode)) {
          $card = $phone;
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
          }
        }
      }
    }

    echo json_encode($output);
  }
}
?>