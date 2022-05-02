<?
$WSDL = $_REQUEST["wsdl"];
$phone = $_REQUEST["phone"];
$hotel_code = $_REQUEST["hotel"];
$language = $_REQUEST["language"] ? $_REQUEST["language"] : "RU";

$http_address = $_REQUEST["http_address"];
if ($http_address) {
  $http_address = rtrim($http_address, '/') . '/';
}
$hotel_token = $_REQUEST["hotel_token"];
$guest_birth_date = $_REQUEST["guest_birth_date"];

$use_soap = $_REQUEST["use_soap"] === "true";

if ($use_soap) {
  $params = array(
    'Hotel' => $hotel_code,
    'Phone' => $phone,
    'ExternalSystemCode' => "1CBITRIX",
    'LanguageCode' => $language
  );
  ini_set("soap.wsdl_cache_enabled", 0);
  ini_set("soap.wsdl_cache_ttl", "86400");
  $soap_params = array('trace' => 1);
  $soapclient = new SoapClient(trim($WSDL), $soap_params);
  $res = $soapclient->RequestCode($params);

  echo '{"is_sent": ' . (isset($res->return->CodeIsSent) && $res->return->CodeIsSent == 'true' ? 1 : 0) . ', "error": "' . $res->return->ErrorDescription . '"}';
} else {
  if ($http_address && $hotel_token) {
    //$phone = "7" . ltrim($phone, '7..8');
    $data = array("Token" => $hotel_token, "Card" => $phone, "DateOfBirth" => $guest_birth_date);

    $data_string = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $http_address . "RequestCode");
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
      echo '{"is_sent": ' . ($output->CodeIsSent ? 1 : 0) . ', "cards_count": ' . $output->CardsCount . ', "error": "' . $output->ErrorDescription . '"}';
    } else {
      echo '{"is_sent": 0, "cards_count": 0, "error": "Ошибка во время запроса"}';
    }
  }
}
?>