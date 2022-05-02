<?
  $embeded = $_REQUEST["embeded"] == 'Y';
	$WSDL = $_REQUEST["wsdl"];
	$adults = $_REQUEST["adults"];
	$children = $_REQUEST["children"];
	$childrenAge = array();
	if(isset($_REQUEST["childrenAge"])){
		$childrenAge = $_REQUEST["childrenAge"];
	}
	$guests = array(
				'Adults' => array('Quantity' => $adults),
				'Kids' => array('Quantity' => $children, 'Age' => $childrenAge),
			);
	$params = array(
		'Hotel' => $_REQUEST["hotel"],
		'RoomRate' => $_REQUEST["room_rate"],
		'ClientType' => $_REQUEST["client_type"],
		'RoomType' => $_REQUEST["room_type"],
		'RoomQuota' => $_REQUEST["room_quota"],
		'PeriodFrom' => $_REQUEST["check_in_date"],
		'PeriodTo' => $_REQUEST["check_out_date"],
		'ExternalSystemCode' => $_REQUEST["output_code"],
		'LanguageCode' => strtoupper($_REQUEST["language"]),
		'EMail' => $_REQUEST["email"],
		'Phone' => $_REQUEST["phone"],
		'Login' => $_REQUEST["login"],
		'PromoCode' => $_REQUEST["promo_code"],
		'GuestsQuantity' => $guests,
    'ExtraParameters' => array(
        'ReservationCode' => '',
        'Employee' => '',
        'Customer' => '',
        'Contract' => '',
        'IsCustomer' => false,
        'ProfileCode' => '',
    )
  );

	if ($embeded) {
    $params['ExtraParameters']['ReservationCode'] = $_REQUEST["uuid"];
  }

    if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->ProfileCode)) {
        $params['ExtraParameters']['IsCustomer'] = $_SESSION["AUTH_CLIENT_DATA"]->IsCustomer;
        $params['ExtraParameters']['ProfileCode'] = $_SESSION["AUTH_CLIENT_DATA"]->ProfileCode;
    }
	ini_set("soap.wsdl_cache_enabled", intval($_REQUEST["cache_enabled"]));
	ini_set("soap.wsdl_cache_ttl", "86400");
	if(intval($_REQUEST["cache_enabled"])){
		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/cached_wsdl.txt")){
			if(filesize($_SERVER["DOCUMENT_ROOT"]."/cached_wsdl.txt") > 0) {
				$WSDL = $_SERVER["DOCUMENT_ROOT"]."/cached_wsdl.txt";
			}
		}
	}
	$soap_params = array('trace' => 1);
	$soapclient = new SoapClient(trim($WSDL), $soap_params);
	$res = $soapclient->GetAvailableRoomTypesExt($params);

	if(!isset($res->return->AccommodationTypes->RoomType)){
		echo '{"return_code": -1}'; //accommodation types not found
	}else{
		$accTypesArray = $res->return->AccommodationTypes->RoomType->AccommodationTypesList->AccommodationType;
		$amount = 0;
		$arrAccTypes = [];
		if(is_array($accTypesArray)){
			foreach ($accTypesArray as $key => $value) {
				$amount += intVal($value->Amount);
				$arrAccTypes[] = trim($value->Code);
			}
		}else{
			$amount += intVal($accTypesArray->Amount);
			$arrAccTypes[] = trim($accTypesArray->Code);
		}
		$arrJSON = array(
			"return_code" => 0,
			"amount" => number_format($amount, 2, ",", " ").' '.$_REQUEST["currency_symbol"],
			"acc_types" => $arrAccTypes
		);
		echo json_encode($arrJSON);
	}
?>
