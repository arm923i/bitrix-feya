<?//if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	//$_REQUEST = $arParams["REQUEST"];

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
IncludeModuleLangFile(__DIR__.'/template.php');

if(!function_exists('plural_form'))
{
	function plural_form($number, $after) {
	  $cases = array (2, 0, 1, 1, 1, 2);
	  echo $number.' '.$after[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
	}
}

	$_REQUEST["hotel_id"] = $_REQUEST["hotel_id_"];

	if(!CModule::IncludeModule("iblock")) {
		echo GetMessage("IBLOCK_NOT_INCLUDE");
		return;
	}
	elseif(!CModule::IncludeModule("gotech.hotelonline")) {
		ShowError(GetMessage("ONLINEBOOKING_MODULE_NOT_INSTALLED"));
		return;
	}
	else {

	if(!function_exists("getMonthNameById")){
		function getMonthNameById($id){
			switch ($id) {
				case 1:
					return GetMessage('january');
					break;
				case 2:
					return GetMessage('february');
					break;
				case 3:
					return GetMessage('march');
					break;
				case 4:
					return GetMessage('april');
					break;
				case 5:
					return GetMessage('may');
					break;
				case 6:
					return GetMessage('june');
					break;
				case 7:
					return GetMessage('july');
					break;
				case 8:
					return GetMessage('august');
					break;
				case 9:
					return GetMessage('september');
					break;
				case 10:
					return GetMessage('october');
					break;
				case 11:
					return GetMessage('november');
					break;
				case 12:
					return GetMessage('december');
					break;
			}
			return "";
		}
	}
	if(!function_exists("getTrimMonthNameById")){
		function getTrimMonthNameById($id){
			switch ($id) {
				case 1:
					return GetMessage('jan');
					break;
				case 2:
					return GetMessage('feb');
					break;
				case 3:
					return GetMessage('mar');
					break;
				case 4:
					return GetMessage('apr');
					break;
				case 5:
					return GetMessage('may');
					break;
				case 6:
					return GetMessage('jun');
					break;
				case 7:
					return GetMessage('jul');
					break;
				case 8:
					return GetMessage('aug');
					break;
				case 9:
					return GetMessage('sep');
					break;
				case 10:
					return GetMessage('oct');
					break;
				case 11:
					return GetMessage('nov');
					break;
				case 12:
					return GetMessage('dec');
					break;
			}
      return "";
		}
	}

		$arResult["language"] = $_REQUEST["language"] ? htmlspecialchars($_REQUEST["language"]) : OnlineBookingSupport::getLanguage();


		if(!empty($_REQUEST)) {

			if(isset($_REQUEST["PeriodFrom"]))
			$explodePeriodFrom = explode('.', $_REQUEST["PeriodFrom"]);
			if(isset($_REQUEST["PeriodTo"]))
			$explodePeriodTo = explode('.', $_REQUEST["PeriodTo"]);

            if(!empty($_REQUEST["hotel_id"])) {
                $res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID'), "ID" => $_REQUEST["hotel_id"]), false, false,
				array('PREVIEW_TEXT',"PROPERTY_HOURS_ENABLE","PROPERTY_HOTEL_MAX_CHILDREN","PROPERTY_HOTEL_MAX_ADULT","PROPERTY_HOTEL_TIME","PROPERTY_HOTEL_TIME_FROM"));
                if ($hotel = $res->GetNext()) {
                    $arResult["HOURS_ENABLE"] = $hotel["PROPERTY_HOURS_ENABLE_VALUE"] ? true : false;
                    $arResult["HOTEL_MAX_CHILDREN"] = $hotel["PROPERTY_HOTEL_MAX_CHILDREN_VALUE"];
                    $arResult["HOTEL_MAX_ADULT"] = $hotel["PROPERTY_HOTEL_MAX_ADULT_VALUE"];
                    $arResult["HOTEL_TIME_FROM"] = $hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"];
                    $arResult["HOTEL_TIME"] = $hotel["PROPERTY_HOTEL_TIME_VALUE"];
                    $arResult["RCOND"] = $hotel["PREVIEW_TEXT"];
				}
			}

			$age_children = array();
			if(isset($_REQUEST['children']) && !empty($_REQUEST['children'])){
				foreach($_REQUEST as $key => $req) {
					$re = explode('_', $key);
					if($re[0] == 'childrenYear') {
						if(isset($_REQUEST['shChildrenYear_'.$re[1]]) && $_REQUEST['shChildrenYear_'.$re[1]] == 'true'){
							$age_children[] = intval($req);
						}
					}
				}
			}
			if(isset($_REQUEST["adults"]))
				$adults = intval(htmlspecialchars($_REQUEST["adults"]));
			else
				$adults = 0;
			if($adults == 0)
			$adults = 1;
			if(isset($_REQUEST["children"])) $children = intval($_REQUEST["children"]);
			else $children = 0;
			if($children != count($age_children))
			$arResult["ERROR"] = GetMessage("NO_ALL_AGE_FOR_CHILDREN");
			if(empty($arResult["ERROR"])){
				$guests = array(
				'Adults' => array('Quantity' => $adults),
				'Kids' => array('Quantity' => $children, 'Age' => $age_children),
				);

				$phone = htmlspecialchars($_REQUEST["phone"]);

				if(!empty($_REQUEST["email"]) && check_email($_REQUEST["email"]))
				$email = htmlspecialchars($_REQUEST["email"]);
				elseif(!empty($_REQUEST["email"]))
				$arResult["ERROR"] = GetMessage('ERROR_MAIL');
				if($USER->IsAuthorized())
				$login = CUser::GetLogin();
				else $login = "";
				$currency = 0;
				$currencyName = "RUB";
				$hotelCode = 0;
				$roomRate = "";
				$roomQuota = "";
				$outputCode = "";
				$WSDL = "";
				$showEconomy = true;
				if(!empty($_REQUEST["hotel_id"]))
				{
					$res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID'), "ID" => $_REQUEST["hotel_id"]), false, false,
					array("PROPERTY_HOTEL_TIME", "PROPERTY_HOTEL_TIME_FROM", "PROPERTY_CURRENCY", "PROPERTY_HOURS_ENABLE", "PROPERTY_HOTEL_CODE", "PROPERTY_HOTEL_OUTPUT_CODE", "PROPERTY_HOTEL_ROOM_RATE", "PROPERTY_HOTEL_ROOM_QUOTA", "PROPERTY_ADDRESS_WEB_SERVICE", "PROPERTY_HOTEL_SHOW_ECONOMY", "PROPERTY_HOTEL_ERROR_TEXT_RU", "PROPERTY_HOTEL_ERROR_TEXT_EN", "PROPERTY_SOAP_LOGIN", "PROPERTY_SOAP_PASSWORD"));
					if($hotel = $res->GetNext()) {
						$arResult["HOURS_ENABLE"] = $hotel["PROPERTY_HOURS_ENABLE_VALUE"] ? true : false;
						if($hotel["PROPERTY_HOTEL_SHOW_ECONOMY_VALUE"]){
							$showEconomy = true;
							}else{
							$showEconomy = false;
						}
						if(!empty($hotel["PROPERTY_CURRENCY_VALUE"]) && $hotel["PROPERTY_CURRENCY_VALUE"] != NULL){
							$currency = $hotel["PROPERTY_CURRENCY_ENUM_ID"];
							$currencyName = $hotel["PROPERTY_CURRENCY_VALUE"];
						}else $currency = 0;
						if(!empty($hotel["PROPERTY_HOTEL_TIME_VALUE"])){
							$pos = strpos($hotel["PROPERTY_HOTEL_TIME_VALUE"], ":");
							if($pos){
								$time = substr($hotel["PROPERTY_HOTEL_TIME_VALUE"],$pos-2, 2).":".substr($hotel["PROPERTY_HOTEL_TIME_VALUE"],$pos+1, 2).":00";
							}else
							$time = date("H:i:s", $hotel["PROPERTY_HOTEL_TIME_VALUE"]);
						}else $time = "00:00:00";
						if(!empty($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"])){
							$pos = strpos($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"], ":");
							if($pos){
								$timeFrom = substr($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"],$pos-2, 2).":".substr($hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"],$pos+1, 2).":00";
							}else
							$timeFrom = date("H:i:s", $hotel["PROPERTY_HOTEL_TIME_FROM_VALUE"]);
						}else $timeFrom = "00:00:00";
						if(!empty($hotel["PROPERTY_HOTEL_CODE_VALUE"])){
							$hotelCode = $hotel["PROPERTY_HOTEL_CODE_VALUE"];
						}else $hotelCode = "";
						if(!empty($hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"])){
							$outputCode = $hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"];
						}else $outputCode = "";
						if(!empty($hotel["PROPERTY_HOTEL_ROOM_RATE_VALUE"])){
							$roomRate = $hotel["PROPERTY_HOTEL_ROOM_RATE_VALUE"];
						}else $roomRate = "";
						if(!empty($hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"])){
							$roomQuota = $hotel["PROPERTY_HOTEL_ROOM_QUOTA_VALUE"];
						}else $roomQuota = "";
						if(!empty($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"])){
							$WSDL = $hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"];
						}else $WSDL = "";
						if ($arResult["language"] == 'en') {
							if(!empty($hotel["PROPERTY_HOTEL_ERROR_TEXT_EN_VALUE"])){
								$errorText = $hotel["PROPERTY_HOTEL_ERROR_TEXT_EN_VALUE"];
							}else $errorText = "";
							}else{
							if(!empty($hotel["PROPERTY_HOTEL_ERROR_TEXT_RU_VALUE"])){
								$errorText = $hotel["PROPERTY_HOTEL_ERROR_TEXT_RU_VALUE"];
							}else $errorText = "";
						}
                        if(!empty($hotel["PROPERTY_SOAP_LOGIN_VALUE"]))
                            $SOAP_LOGIN = trim($hotel["PROPERTY_SOAP_LOGIN_VALUE"]);
                        else
                            $SOAP_LOGIN = "";
                        if(!empty($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]))
                            $SOAP_PASSWORD = trim($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]);
                        else
                            $SOAP_PASSWORD = "";
					}


					$arResult["SHOW_ECONOMY"] = $showEconomy;
					$arResult["CURRENCY_ID"] = $currency;
					$arResult["CURRENCY_NAME"] = $currencyName;
					if(empty($arResult["ERROR"]))
					{
						$roomsUsed = array();
						if(isset($_SESSION["NUMBERS_BOOKING"])){
							foreach($_SESSION["NUMBERS_BOOKING"][$_REQUEST["hotel_id"]]["NUMBERS"] as $key => $value) {
								if(isset($roomsUsed[$value["RoomTypeCode"]])){
									$roomsUsed[$value["RoomTypeCode"]] += 1;
									}else{
									$roomsUsed[$value["RoomTypeCode"]] = 1;
								}
							}
						}
						$arResult["RoomsUsed"] = $roomsUsed;
						$x = array(
                            'Hotel' => $hotelCode,
                            'RoomRate' => '',//$roomRate,
                            'ClientType' => (isset($_REQUEST["client_type"]) && !empty($_REQUEST["client_type"])) ? $_REQUEST["client_type"] : "",
                            'RoomType' => (isset($_REQUEST["room_type"]) && !empty($_REQUEST["room_type"])) ? $_REQUEST["room_type"] : "",
                            'RoomQuota' => $roomQuota,
                            'PeriodFrom' => $explodePeriodFrom[2]."-".$explodePeriodFrom[1]."-".$explodePeriodFrom[0]."T".$timeFrom,
                            'PeriodTo' => $explodePeriodTo[2]."-".$explodePeriodTo[1]."-".$explodePeriodTo[0]."T".$time,
                            'ExternalSystemCode' => $outputCode,
                            'LanguageCode' => strtoupper($arResult["language"]),
                            'EMail' => $email,
                            'Phone' => $phone,
                            'Login' => $login,
                            'PromoCode' => $_REQUEST["promo_code"],
                            'GuestsQuantity' => $guests,
                            'ExtraParameters' => array(
                                'Employee' => '',
                                'Customer' => '',
                                'Contract' => '',
                                'IsCustomer' => false,
                                'ProfileCode' => '',
                            )
                        );

                        if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->ProfileCode)) {
                            $x['ExtraParameters']['IsCustomer'] = $_SESSION["AUTH_CLIENT_DATA"]->IsCustomer;
                            $x['ExtraParameters']['ProfileCode'] = $_SESSION["AUTH_CLIENT_DATA"]->ProfileCode;
                        }

						if($arResult["HOURS_ENABLE"]) {
							$x['PeriodFrom'] = $explodePeriodFrom[2]."-".$explodePeriodFrom[1]."-".$explodePeriodFrom[0]."T".$_REQUEST["TimeFrom"] . ":00";
							$x['PeriodTo'] = date("Y-m-d\TH:i:s", AddToTimeStamp(array("HH" => $_REQUEST["hour"]), MakeTimeStamp($_REQUEST["PeriodFrom"] . " " . $_REQUEST["TimeFrom"] . ":00", "DD.MM.YYYY HH:MI:SS")));
						}
						$_SESSION["email"] = $email;
						$_SESSION["phone"] = $phone;
						$_SESSION["promo_code"] = $_REQUEST["promo_code"];

                        $pfrom = $_REQUEST["PeriodFrom"];
                        $pto = $_REQUEST["PeriodTo"];

                        $new_pf = date('Y-m-d',(strtotime($pfrom) - 7 * 60*60*24)).'T'.$arResult["HOTEL_TIME_FROM"].':00';
                        $new_pt = date('Y-m-d',(strtotime($pto) + 7 * 60*60*24)).'T'.$arResult["HOTEL_TIME"].':00';

                        if($arResult["HOURS_ENABLE"]) {
                            $pto = date("Y-m-d", AddToTimeStamp(array("HH" => $_REQUEST["hour"]), MakeTimeStamp($_REQUEST["PeriodFrom"] . " " . $_REQUEST["TimeFrom"] . ":00", "DD.MM.YYYY HH:MI:SS")));
                            $new_pf = date('Y-m-d',(strtotime($pfrom) - 7 * 60*60*24)).'T00:00:00';
                            $new_pt = date('Y-m-d',(strtotime($pto) + 7 * 60*60*24)).'T23:59:59';
                        }

                        $today = date('d.m.Y',time());
                        $today = strtotime($today);

                        $new_pf_2 = strtotime($_REQUEST["PeriodFrom"]) - 7*60*60*24;

                        if($new_pf_2 < $today) {
                            $new_pf = date('Y-m-d') . 'T' . $arResult["HOTEL_TIME_FROM"] . ':00';
                        }


						define("start_time_object", microtime(true));
						$OnlineBookingSupport = new OnlineBookingSupport();
						$showError = !!$errorText;
						$res = $OnlineBookingSupport->GetAvailableRoomTypes($x, $_REQUEST["hotel_id"], trim($WSDL), 620, 420, $roomsUsed, $pfrom, $pto, $new_pf, $new_pt, false, $SOAP_LOGIN, $SOAP_PASSWORD);

						//echo var_dump($res);

						if($res === false && $showError) {
							$arResult["ERROR"] = $errorText;
						}
						$arResult["ReservationConditions"] = str_replace("\\n", "<br/>", $res["ReservationConditions"]);
						$arResult["AvailableRooms"] = $res["AvailableRooms"];
						$arResult["Periods"] = $res["Periods"];
						$arResult["AvailableRoomsByCheckInPeriods"] = $res["AvailableRoomsByCheckInPeriods"];
						$arResult["OtherPeriods"] = $res["OtherPeriods"];
						$arResult["HOTEL"]["ID"] = htmlspecialchars($_REQUEST["hotel_id"]);

						//Формирование описания периода
						$exPeriodFrom = explode(".", $_REQUEST["PeriodFrom"]);
						$exPeriodTo = explode(".", $_REQUEST["PeriodTo"]);
						if($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] == $exPeriodTo[1]){
							$arResult["intPeriodFrom"] = $exPeriodFrom[0];
							$arResult["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
							}elseif($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] != $exPeriodTo[1]){
							$arResult["intPeriodFrom"] = $exPeriodFrom[0]." ".getMonthNameById($exPeriodFrom[1]);
							$arResult["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
							}elseif($exPeriodFrom[2] != $exPeriodTo[2]){
							$arResult["intPeriodFrom"] = $exPeriodFrom[0]." ".getMonthNameById($exPeriodFrom[1])." ".$exPeriodFrom[2];
							$arResult["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
						}

						$arResult["RequestedPeriodFounded"] = false;
						foreach($arResult["Periods"] as &$periodItem){
							//Формирование описания периода
							$exPeriodFrom = explode(".", $periodItem["PeriodFrom"]);
							$exPeriodTo = explode(".", $periodItem["PeriodTo"]);
							if($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] == $exPeriodTo[1]){
								$periodItem["intPeriodFrom"] = $exPeriodFrom[0];
								$periodItem["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
								}elseif($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] != $exPeriodTo[1]){
								$periodItem["intPeriodFrom"] = $exPeriodFrom[0]." ".getMonthNameById($exPeriodFrom[1]);
								$periodItem["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
								}elseif($exPeriodFrom[2] != $exPeriodTo[2]){
								$periodItem["intPeriodFrom"] = $exPeriodFrom[0]." ".getMonthNameById($exPeriodFrom[1])." ".$exPeriodFrom[2];
								$periodItem["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
							}
							if($_REQUEST["PeriodFrom"]." - ".$_REQUEST["PeriodTo"] == $periodItem["PeriodFromTo"]){
								$arResult["RequestedPeriodFounded"] = true;
							}
						}
						//время чтобы правильно скрыть анимацию загрузки
						$endTime = microtime(true) - $start;

						//echo json_encode($arResult);
					}
				}
			}
		}

	}


if($arResult['HOURS_ENABLE'])
{
	$period_from = htmlspecialchars($_REQUEST["PeriodFrom"]);
	$time_from = htmlspecialchars($_REQUEST["TimeFrom"]);
	$hour = htmlspecialchars($_REQUEST["hour"]);
}
else
{
	$period_from = htmlspecialchars($_REQUEST["PeriodFrom"]);
	$period_to = htmlspecialchars($_REQUEST["PeriodTo"]);
}
$adults = intval(htmlspecialchars($_REQUEST['adults']));
$children = intval(htmlspecialchars($_REQUEST['children']));
$visitors = $adults + $children;

$booked = array();
$booked2 = array();
if(count($_SESSION['NUMBERS_BOOKING'][$_SESSION['HOTEL_ID']]["NUMBERS"])):
	$last = 0;
	foreach($_SESSION['NUMBERS_BOOKING'][$_SESSION['HOTEL_ID']]["NUMBERS"] as $k => $v):
		$booked[$k] = $v['RoomTypeCode'].'_'.$v['RoomRateCode'].'_'.$v['PeriodFrom'].'_'.$v['PeriodTo'].'_'.$v['visitors'];
		$booked2[$v['RoomTypeCode'].'_'.$v['RoomRateCode'].'_'.$v['PeriodFrom'].'_'.$v['PeriodTo'].'_'.$v['visitors']]++;
		$last = $k;
	endforeach;

endif;
?>

	<?$a='';?>
	<?ob_start();?>

		<?if($time_from && $hour):?>
				<b><?plural_form($hour,array(GetMessage('1HOUR'),GetMessage('2HOURS'),GetMessage('5HOURS')))?>,
				<?plural_form($adults,array(GetMessage('1GUEST'),GetMessage('2GUESTS'),GetMessage('5GUESTS')))?>,
				<?=$period_from?> <?=$time_from?></b>

		<?else:?>
				<b><?plural_form($_REQUEST['night'],array(GetMessage('1NIGHTS'),GetMessage('2NIGHTS'),GetMessage('5NIGHTS')))?>,
				<?plural_form($adults,array(GetMessage('1GUEST'),GetMessage('2GUESTS'),GetMessage('5GUESTS')))?>,
				<?=$period_from?> - <?=$period_to?></b>

		<?endif;?>


	<?
	$no_rates = true;
	$a = ob_get_contents();
	ob_clean();
	?>
	<?ob_start();?>
	<?if(!empty($arResult["AvailableRooms"])):?>

		<?foreach($arResult["AvailableRooms"] as $key => $room):?>
				<?if($room['RoomTypeCode'] != $_REQUEST['room_type_code']) continue;?>

				<?if(isset($room["RoomRates"])):?>
						<?$no_rates = false;?>
						<?$isFirst = true?>
						<?foreach($room["RoomRates"] as $RRKey => $RRValue):?>
							<?
							$this_book = $room['RoomTypeCode'].'_'.$RRValue.'_'.$period_from.'_'.$period_to.'_'.$visitors;
							//echo $this_book;
							?>
							<?$strRRValue = str_replace("%", "", $RRValue);?>
							<?$strRRValue = str_replace("+", "x", $strRRValue);?>
							<?$strRRValue = str_replace("/", "-", $strRRValue);?>

							<div class="order_item<?if($isFirst):?> active<?endif;?>"  data-rate_id="<?=$this_book?>">

								<?if(count($room["RoomRates"])>1):?>
										<input type="radio" name="<?="gotech_search_result_room_rates_".$strRoomType?>" id="<?="gotech_room_rate_".$strRRValue."_".$strRoomType?>" <?if($isFirst):?>checked<?endif;?>>
								<?else:?>
										<input type="hidden" name="<?="gotech_search_result_room_rates_".$strRoomType?>" id="<?="gotech_room_rate_".$strRRValue."_".$strRoomType?>">
								<?endif;?>

								<!-- Price -->
								<?
									$price = "";
									if($arResult["CURRENCY_NAME"] == "EURO" || $arResult["CURRENCY_NAME"] == "EUR"){
										$price = "&euro; ".$room[$RRValue]["Price"]["EURO"];
									}
									if($arResult["CURRENCY_NAME"] == "USD"){
										$price = "$ ".$room[$RRValue]["Price"]["USD"];
									}
                  if($arResult["CURRENCY_NAME"] == "KGS" || $arResult["CURRENCY_NAME"] == "KGZ"){
                    $price = $room[$RRValue]["Price"]["KGS"]." KGS";
                  }
									if($arResult["CURRENCY_NAME"] == "RUB"){
										$price = $room[$RRValue]["Price"]["RUB"]." <span class='gotech_ruble'>a</span>";
									}
									if(!$price)
										$price = $room[$RRValue]["Price"]["RUB"]." <span class='gotech_ruble'>a</span>";
									if($room["MaxPrice"]["RUB"] <= $room[$RRValue]["Price"]["RUB"] || !$arResult["SHOW_ECONOMY"]){
										$economy = 0;
									}else{
										$economy = 100 - round($room[$RRValue]["Price"]["RUB"]*100/$room["MaxPrice"]["RUB"]);
									}
								?>

								<?if(in_array($this_book,$booked)):?>
									<div class="order_actions when_book" id="book_id_<?=$this_book?>">
										<div class="bordered_info in_order">
											<?=GetMessage('IN_ORDER')?>:<span class="room_in_cart_cnt"><?=$booked2[$this_book]?></span>
										</div>
										<a href="#" data-cart_id="<?=$this_book?>" class="delete_order"><?=GetMessage('DELETE')?></a>
										<br/>
										<!-- Button -->
										<form class="accomodation_form" name="accomodation_form_<?=$strRRValue."_".($key+1)?>" action="<?=$APPLICATION->GetCurPage(false)?>" method="post" >
											<a href="#" class="gotech_button add_onemoreroom" id="<?="gotech_room_rate_".$strRRValue."_".$strRoomType?>-link" style="float:right;"><?=GetMessage('ADD_ROOM')?></a>


											<input type="hidden" name="count" value="1" />

											<input type="hidden" name="PeriodFrom0" value="<?=$period_from?>" />
											<input type="hidden" name="PeriodTo0" value="<?=$period_to?>" />
											<input type="hidden" name="adults0" value="<?=$adults?>" />
											<input type="hidden" name="children0" value="<?=$children?>" />

											<input type="hidden" name="language" value="<?=$arResult["language"]?>" />
											<input type="hidden" name="FormType" value="addOrder" />
											<input type="hidden" name="hotel_id" value="<?=$arResult["HOTEL"]["ID"]?>" />
											<input type="hidden" name="RoomTypeCode" value="<?=$room["RoomTypeCode"]?>" />
											<input type="hidden" name="LimitedRooms" value="<?=$room["LimitedRooms"]?>"/>
											<input type="hidden" name="RoomName" value="<?=$room["Name"]?>" />
											<input type="hidden" name="RoomNameEn" value="<?=$room["Name_en"]?>" />
											<input type="hidden" name="RoomRateCode" value="<?=str_replace("%", "thisisprocent", $RRValue)?>" />
											<input type="hidden" name="RoomRateCodeDesc" value="<?=$room[$RRValue]["ReservationConditionsShort"]?>" />
											<input type="hidden" name="PaymentMethodCodesAllowedOnline" value="<?=$room[$RRValue]["PaymentMethodCodesAllowedOnline"]?>" />
											<input type="hidden" name="FirstDaySum" value="<?=$room[$RRValue]["FirstDaySum"]?>" />
											<input type="hidden" name="Amount" value="<?=$room[$RRValue]["Amount"]?>" />
											<input type="hidden" name="Currency" value="<?=$room["Currency"]?>" />
											<input type="hidden" name="AmountPresentation" value="<?=$room["AmountPresentation"]?>" />
											<input type="hidden" name="curr_page" value="<?=htmlspecialcharsEx($_REQUEST["curr_page"])."?booking=yes"?>" />
											<input type="hidden" name="key" value="<?=($key+1)."_".$strRRValue?>" />
											<?if(!empty($room["RoomTypes"]["Accommodation"])):?>
												<?foreach($room["RoomTypes"]["Accommodation"] as $k => $Accommodation):?>
													<input type="hidden" name="Accommodation_Code_<?=$k?>" value="<?=$Accommodation["Code"]?>" />
													<input type="hidden" name="Accommodation_Description_<?=$k?>" value="<?=$Accommodation["Description"]?>" />
													<input type="hidden" name="Accommodation_Age_<?=$k?>" value="<?=$Accommodation["Age"]?>" />
													<input type="hidden" name="Accommodation_Is_Child_<?=$k?>" value="<?=$Accommodation["IsChild"]?>" />
													<input type="hidden" name="Accommodation_Client_Age_From_<?=$k?>" value="<?=$Accommodation["ClientAgeFrom"]?>" />
													<input type="hidden" name="Accommodation_Client_Age_To_<?=$k?>" value="<?=$Accommodation["ClientAgeTo"]?>" />
												<?endforeach;?>
											<?endif;?>
											<input type="hidden" name="<?=$RRValue."_RUB_currency"?>" value="<?=number_format($room[$RRValue]["Price"]["RUB"], 2, ',', ' ')?> <span class='gotech_ruble'>a</span>" />
											<input type="hidden" name="<?=$RRValue."_USD_currency"?>" value="$ <?=number_format($room[$RRValue]["Price"]["USD"], 2, ',', ' ')?>" />
											<input type="hidden" name="<?=$RRValue."_EUR_currency"?>" value="&euro; <?=number_format($room[$RRValue]["Price"]["EURO"], 2, ',', ' ')?>" />
                      <input type="hidden" name="<?=$RRValue."_KGS_currency"?>" value="<?=number_format($room[$RRValue]["Price"]["KGS"], 2, ',', ' ')?> KGS" />

										</form>



									</div>
								<?endif;?>
									<div class="order_actions when_no_book"<?if(in_array($this_book,$booked)):?> style="display:none;"<?endif;?>>
										<!-- Button -->
										<form class="accomodation_form" name="accomodation_form_<?=$strRRValue."_".($key+1)?>" action="<?=$APPLICATION->GetCurPage(false)?>" method="post" >

											<input type="hidden" name="count" value="1" />

											<input type="hidden" name="PeriodFrom0" value="<?=$period_from?>" />
											<input type="hidden" name="PeriodTo0" value="<?=$period_to?>" />
											<input type="hidden" name="adults0" value="<?=$adults?>" />
											<input type="hidden" name="children0" value="<?=$children?>" />

											<input type="hidden" name="language" value="<?=$arResult["language"]?>" />
											<input type="hidden" name="FormType" value="addOrder" />
											<input type="hidden" name="hotel_id" value="<?=$arResult["HOTEL"]["ID"]?>" />
											<input type="hidden" name="RoomTypeCode" value="<?=$room["RoomTypeCode"]?>" />
											<input type="hidden" name="LimitedRooms" value="<?=$room["LimitedRooms"]?>"/>
											<input type="hidden" name="RoomName" value="<?=$room["Name"]?>" />
											<input type="hidden" name="RoomNameEn" value="<?=$room["Name_en"]?>" />
											<input type="hidden" name="RoomRateCode" value="<?=str_replace("%", "thisisprocent", $RRValue)?>" />
											<input type="hidden" name="PaymentMethodCodesAllowedOnline" value="<?=$room[$RRValue]["PaymentMethodCodesAllowedOnline"]?>" />
											<input type="hidden" name="FirstDaySum" value="<?=$room[$RRValue]["FirstDaySum"]?>" />
											<input type="hidden" name="Amount" value="<?=$room[$RRValue]["Amount"]?>" />
											<input type="hidden" name="Currency" value="<?=$room["Currency"]?>" />
											<input type="hidden" name="AmountPresentation" value="<?=$room["AmountPresentation"]?>" />
											<input type="hidden" name="curr_page" value="<?=htmlspecialcharsEx($_REQUEST["curr_page"])."?booking=yes"?>" />
											<input type="hidden" name="key" value="<?=($key+1)."_".$strRRValue?>" />
											<?if(!empty($room["RoomTypes"]["Accommodation"])):?>
												<?foreach($room["RoomTypes"]["Accommodation"] as $k => $Accommodation):?>
													<input type="hidden" name="Accommodation_Code_<?=$k?>" value="<?=$Accommodation["Code"]?>" />
													<input type="hidden" name="Accommodation_Description_<?=$k?>" value="<?=$Accommodation["Description"]?>" />
													<input type="hidden" name="Accommodation_Age_<?=$k?>" value="<?=$Accommodation["Age"]?>" />
													<input type="hidden" name="Accommodation_Is_Child_<?=$k?>" value="<?=$Accommodation["IsChild"]?>" />
													<input type="hidden" name="Accommodation_Client_Age_From_<?=$k?>" value="<?=$Accommodation["ClientAgeFrom"]?>" />
													<input type="hidden" name="Accommodation_Client_Age_To_<?=$k?>" value="<?=$Accommodation["ClientAgeTo"]?>" />
												<?endforeach;?>
											<?endif;?>

												<span class="gotech_search_result_room_rates_item_button_">
														<span href="#" class="bron_button gotech_button"  id="<?="gotech_room_rate_".$strRRValue."_".$strRoomType?>-link" style="<?if($room["LimitedRooms"]<=0 && !empty($room["LimitedRoomsText"])):?>display: none;<?endif;?>">
															<?if(!$USER->IsAuthorized() || !in_array(COption::GetOptionint('gotech.hotelonline', 'USER_AGENT_GROUP'), $ar_group)):?>
																<?=GetMessage("BOOKING");?>
															<?else:?>
																<?=GetMessage("ADD");?><span class="add_to_order">&nbsp;<?=GetMessage("2_ORDER");?></span>
															<?endif;?>
														</span>
												</span>

											<input type="hidden" name="<?=$RRValue."_RUB_currency"?>" value="<?=number_format($room[$RRValue]["Price"]["RUB"], 2, ',', ' ')?> <span class='gotech_ruble'>a</span>" />
											<input type="hidden" name="<?=$RRValue."_USD_currency"?>" value="$ <?=number_format($room[$RRValue]["Price"]["USD"], 2, ',', ' ')?>" />
											<input type="hidden" name="<?=$RRValue."_EUR_currency"?>" value="&euro; <?=number_format($room[$RRValue]["Price"]["EURO"], 2, ',', ' ')?>" />
                      <input type="hidden" name="<?=$RRValue."_KGS_currency"?>" value="<?=number_format($room[$RRValue]["Price"]["KGS"], 2, ',', ' ')?> KGS" />

										</form>

									</div>
								<div class="price_info">
									<?if($economy):?>
										<div class="discount">
											<del><?=$room["MaxPrice"]["RUB"]?> <span class='gotech_ruble'>a</span></del> / <span>-<?=$economy?>%</span>
										</div>
									<?endif;?>
									<div class="price">
										<?=OnlineBookingSupport::format_price($price, $arResult["CURRENCY_NAME"])?>
									</div>
								</div>
								<div class="order_name">
									<?if(isset($room[$RRValue]["ReservationConditionsShort"]) && !empty($room[$RRValue]["ReservationConditionsShort"])):?>
										<?=$room[$RRValue]["ReservationConditionsShort"]?>
									<?else:?>
										<?=GetMessage("RATE").$room[$RRValue]["RoomRateDescription"]?>
									<?endif;?>
								</div>
								<div class="order_description">

									<p><?=$room[$RRValue]["ReservationConditionsOnline"]?></p>
								</div>
							</div>

							<?$isFirst = false;?>
						<?endforeach;?>
				<?endif;?>
		<?endforeach;?>
	<?endif?>

	<?if($no_rates):?>
		<?
		$c = 'not_found';
	/***************************************************/

	$new_pf = date('Y-m-d',(strtotime($_REQUEST["PeriodFrom"]) - 7 * 60*60*24)).'T'.$arResult["HOTEL_TIME_FROM"].':00';
	$new_pt = date('Y-m-d',(strtotime($_REQUEST["PeriodTo"]) + 7 * 60*60*24)).'T'.$arResult["HOTEL_TIME"].':00';

	$today = date('d.m.Y',time());
	$today = strtotime($today);

	$new_pf_2 = strtotime($_REQUEST["PeriodFrom"]) - 7*60*60*24;

	if($new_pf_2 < $today)
		$new_pf = date('Y-m-d').'T'.$arResult["HOTEL_TIME_FROM"].':00';
		//$new_pf = date('Y-m-d',strtotime('+1 day')).'T'.$arResult["HOTEL_TIME_FROM"].':00';

	$arResult['ROOM_SOLD'] = array();


		$GetRoomInventoryBalanceArr = array(
			"Hotel" => $_REQUEST["hotel_id"],
			"RoomType" => $_REQUEST['room_type_code'],//
			"Customer" => "",
			"Contract" => "",
			"Agent" => "",
			"RoomQuota" => "",
			"PeriodFrom" => $new_pf,
			"PeriodTo" => $new_pt,
			"RoomRate" => $roomRate,//iconv('windows-1251','UTF-8','Б'),
			"ClientType" => "",
			"OutputRoomsVacant" => "true",
			"OutputBedsVacant" => "false",
			"OutputRoomsRemains" => "true",
			"OutputBedsRemains" => "false",
			"OutputRoomsInQuota" => "false",
			"OutputBedsInQuota" => "false",
			"OutputRoomsChargedInQuota" => "false",
			"OutputBedsChargedInQuota" => "false",
			"OutputRoomsReserved" => "true",
			"OutputBedsReserved" => "true",
			"OutputInHouseRooms" => "false",
			"OutputInHouseBeds" => "false",
			"ExternalSystemCode" => "1CBITRIX",
			"LanguageCode" => "RU",
		);

        $soap_params = array('trace' => 1);
        if (isset($SOAP_LOGIN) && !empty($SOAP_LOGIN) && !empty($SOAP_PASSWORD)) {
            $soap_params['login'] = $SOAP_LOGIN;
            $soap_params['password'] = $SOAP_PASSWORD;
        }
        $soapclient = new SoapClient(trim($WSDL), $soap_params); // with auth
		$res2 = $soapclient->GetRoomInventoryBalance($GetRoomInventoryBalanceArr);

		$arResult['ROOM_SOLD'][$_REQUEST['room_type_code']] = $res2->return->RoomInventoryBalanceRow;


/****************************************************/



		?>

		<div class="ajax_noav">

			<div class="row">
				<div class="room_sold"><?=GetMessage('SOLD')?></div>
			</div>

			<div class="row">
				<a href="#" class="show_availability" onclick="$(this).hide();$(this).parent().next().slideDown();return false;">
					<?=GetMessage('WATCH_AVAILABILITY')?>
				</a>
			</div>

			<div class="availibility_block">
				<form>
					<input type="hidden" name="rtc" value="<?=htmlspecialchars($_REQUEST['room_type_code'])?>" />
					<input type="hidden" name="hid" value="<?=htmlspecialchars($_REQUEST["hotel_id"])?>" />
					<input type="hidden" name="htf" value="<?=$arResult["HOTEL_TIME_FROM"]?>" />
					<input type="hidden" name="ht" value="<?=$arResult["HOTEL_TIME"]?>" />

				</form>

				<div class="h"><?=GetMessage('ROOMS_AVAILABILITY')?>:</div>
				<table>
					<tr class="dates">
						<?foreach($arResult['ROOM_SOLD'][$_REQUEST['room_type_code']] as $k => $v):?>
							<td <?if((int)$v->RoomsVacant):?>class="av"<?endif;?>><?=date('d.m.Y',strtotime($v->Period))?></td>
							<?if(!$k)  $first_date = date('d.m.Y',strtotime($v->Period));?>
						<?endforeach;?>
					</tr>
					<tr class="prices">
						<?
						$av_room_price_arr = array(
							1 => 'SinglePrice',
							2 => 'DoublePrice',
							3 => 'TriplePrice',
							4 => 'QuadruppelPrice',
						);
						?>
						<?foreach($arResult['ROOM_SOLD'][$_REQUEST['room_type_code']] as $k => $v):?>
							<td class="<?if(!(int)$v->RoomsVacant):?>no_av<?else:?>av<?endif;?>">
								<?if((int)$v->RoomsVacant):?>
									<?if((int)$v->{$adults > 3 ? $av_room_price_arr[3] : $av_room_price_arr[$adults]}):?>
										<?=GetMessage('FROM1')?><br/><?=$v->{$adults > 3 ? $av_room_price_arr[3] : $av_room_price_arr[$adults]}?><br/>Р
									<?else:?>
										<div  style="min-height:56px;line-height:56px;"><?=GetMessage('IS_AV')?></div>
									<?endif;?>
								<?else:?>
									<div  style="min-height:56px;"></div>
								<?endif;?>

							</td>
						<?endforeach;?>
					</tr>
				</table>
				<a href="#" class="later"><?=GetMessage('LATER')?></a>
				<a href="#" class="earlier"<?if($first_date = date('d.m.Y')):?> style="display:none;"<?endif;?>><?=GetMessage('EARLIER')?></a>
			</div>
		</div>

		<script>
			$('.availibility_block .later').on('click',function() {

				var th = $(this);
				var av = th.parents('.availibility_block');

				var w1 = av.width();
				var w2 = av.find('table').width();
				var diff = w2-w1;


				var p = av;
				var t = p.find('table');
				var ml = parseInt(t.css('margin-left'));
				var ml1 = ml*(-1);

				if(ml1 < diff)
				{
					ml1+=200;
					if(ml1>diff)
						ml1=diff;
					t.animate({marginLeft:'-'+ml1+'px'},500);

				}
				else if(ml1 == diff)
				{
					th.after('<img src="/bitrix/js/onlinebooking/new/icons/snake-loader.gif" id="preloader1" style="float:right;" />');
					th.hide();

					var a = av.find('form').serialize();
					var d = av.find('tr.dates td:last-child').text();

					var adults = $('input[name="adults_f"]').val();
					if(!adults)
						adults = 1;

					a += '&date='+d+'&direction=later&adults='+adults;


					$.get('/bitrix/components/onlinebooking/reservation.find/get_room_av.php?'+a,
					function(data){
						av.find('.dates').append(data.dates);
						av.find('.prices').append(data.prices);
						th.click();

						th.show();
						$('#preloader1').remove();


					},'json');

				}

				av.find('.earlier').show();

				return false;

			});
			$('.availibility_block .earlier').on('click',function() {
				var th = $(this);
				var av = th.parents('.availibility_block');

				var w1 = av.width();
				var w2 = av.find('table').width();
				var diff = w2-w1;

				var p = av;
				var t = p.find('table');
				var ml = parseInt(t.css('margin-left'));
				var ml1 = ml*(-1);


				var d = av.find('tr.dates td:first-child').text();
				var today = '<?=date('d.m.Y')?>';

				if(ml1 <= diff && ml1)
				{

					ml1-=200;
					if(ml1<0)
						ml1=0;
					t.animate({marginLeft:'-'+ml1+'px'},500);

					if(d == today && !ml1)
					{
						th.hide();
					}
				}
				else
				{

					var a = av.find('form').serialize();
					d = av.find('tr.dates td:first-child').text();

					var adults = $('input[name="adults_f"]').val();
					if(!adults)
						adults = 1;

					if(d == today)
					{
						th.hide();
						return;
					}

					th.after('<img src="/bitrix/js/onlinebooking/new/icons/snake-loader.gif" id="preloader1" />');
					th.hide();

					a += '&date='+d+'&direction=earlier&adults='+adults;


					$.get('/bitrix/components/onlinebooking/reservation.find/get_room_av.php?'+a,
					function(data){

						$('#preloader1').remove();

						var was = av.find('.dates').length;

						av.find('.dates').prepend(data.dates);
						av.find('.prices').prepend(data.prices);

						var become = av.find('.dates').length


						t.css('margin-left','-'+((become-was)*67.2)+'px');

						d = av.find('tr.dates td:first-child').text();
						console.log(d+'/'+today);
						if(d != today)
							th.show();
						//th.click();

					},'json');

				}

				return false;

			});

		</script>



	<?endif?>	<?
	$b = ob_get_contents();
	ob_clean();


	echo json_encode(array('a'=>$a,'b'=>$b,'c'=>$c));

	?>
