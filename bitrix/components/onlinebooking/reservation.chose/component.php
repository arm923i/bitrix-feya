<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();	
if(!CModule::IncludeModule("iblock")) {
	echo GetMessage("IBLOCK_NOT_INCLUDE");
	return;
}
elseif(!CModule::IncludeModule("gotech.hotelonline")) {
	ShowError(GetMessage("ONLINEBOOKING_MODULE_NOT_INSTALLED"));	
	return;
}
else {
	if(isset($_REQUEST["language"]))
		$arResult["LANG"] = mb_strtolower($_REQUEST["language"]);
	else 
		$arResult["LANG"] = OnlineBookingSupport::getLanguage();
	if($this->includeComponentLang("", $arResult["LANG"]) == NULL){
		__IncludeLang(dirname(__FILE__)."/lang/".$arResult["LANG"]."/component.php");
	}
	
	
	foreach($_SESSION["NUMBERS_BOOKING"][$arParams["ID_HOTEL"]]["NUMBERS"] as $k => $v)
	{
		$_SESSION["NUMBERS_BOOKING"][$arParams["ID_HOTEL"]]["NUMBERS"][$k]['Id'] = $k+1;
	}
	
	if(!function_exists('getMonthNameById'))
	{
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
	//var_dump($arParams);
	if($arParams["TYPE"] != "SERVICE"){
		$splits = explode("_", $arParams["UNUSED_RT"]);
		$arResult["UnUsed_Room"] = $splits[0];
		if(!empty($_SESSION["NUMBERS_BOOKING"])) {
		
			
			$iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
			$hotels = CIBlockElement::GetList(
				array(),
				array("IBLOCK_ID" => $iblock_id_hotel, "ID" => $arParams["ID_HOTEL"]),
				false,
				false,
				array(
					"PROPERTY_HOURS_ENABLE","PROPERTY_FIO","PROPERTY_PHONE_NECESSARY","PROPERTY_HOTEL_ERROR_TEXT_RU","PROPERTY_HOTEL_ERROR_TEXT_EN",
					"PROPERTY_LABEL_PHONE","PROPERTY_LABEL_EMAIL","PROPERTY_LABEL_PHONE_EN","PROPERTY_LABEL_EMAIL_EN",
				)
			);
			if($hotel = $hotels->GetNext())
			{
				$arResult['HOURS_ENABLE'] = $hotel['PROPERTY_HOURS_ENABLE_VALUE'];
			}
		
		
		
			if(!empty($_SESSION["NUMBERS_BOOKING"][$arParams["ID_HOTEL"]])) {
				$arRes["NUMBERS_BOOKING"] = $_SESSION["NUMBERS_BOOKING"][$arParams["ID_HOTEL"]]["NUMBERS"];
				$id_iblock_numbers = COption::GetOptionString('gotech.hotelonline', 'NUMBER_IBLOCK_ID');
				$id_hotel_property = COption::GetOptionString('gotech.hotelonline', 'NUMBERHOTEL');
				$id_number_code_property = COption::GetOptionString('gotech.hotelonline', 'NUMBERCODE');
				$id_number_en_name = COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMEEN');
				COption::SetOptionString('gotech.hotelonline', 'NUMBERNAMERU', "NAME");
				if(COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU') == "NAME")
					$id_number_ru_name  = "NAME";
				else 
					$id_number_ru_name = "PROPERTY_".COption::GetOptionString('gotech.hotelonline', 'NUMBERNAMERU');
				foreach($arRes["NUMBERS_BOOKING"] as $per => $NUMBERS_BOOKING) {
					unset($filter);
					unset($arSelect);
					unset($dbEl);
					unset($res);					
					$arResult["NUMBERS_BOOKING"][$per] = $NUMBERS_BOOKING;
					$filter = array(
						"IBLOCK_ID" => $id_iblock_numbers,
						"ACTIVE" => "Y",
						$id_number_code_property => $NUMBERS_BOOKING["RoomTypeCode"],
						$id_hotel_property  => $arParams["ID_HOTEL"]						
					);
					$arSelect = array(						
						$id_hotel_property,
						$id_number_code_property,
						$id_number_en_name,
						$id_number_ru_name
					);
					
					$dbEl = CIBlockElement::GetList(array(), $filter, false, false, $arSelect);
					if($res = $dbEl->GetNext()) {
						if($arResult["LANG"] == "en"){							
							$arResult["NUMBERS_BOOKING"][$per]["RoomName"] = $res[$id_number_en_name."_VALUE"];
						}
						else 
							if($id_number_ru_name == "NAME")
								$arResult["NUMBERS_BOOKING"][$per]["RoomName"] = $res[$id_number_ru_name];
							else
								$arResult["NUMBERS_BOOKING"][$per]["RoomName"] = $res[$id_number_ru_name.'_VALUE'];
					}
					else{
						$arResult["NUMBERS_BOOKING"][$per]["RoomName"] = $NUMBERS_BOOKING["RoomName"];
					}

					//������������ �������� �������
					$exPeriodFrom = explode(".", $arResult["NUMBERS_BOOKING"][$per]["PeriodFrom"]);
					$exPeriodTo = explode(".", $arResult["NUMBERS_BOOKING"][$per]["PeriodTo"]);
					if($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] == $exPeriodTo[1]){
						$arResult["NUMBERS_BOOKING"][$per]["intPeriodFrom"] = $exPeriodFrom[0];
						$arResult["NUMBERS_BOOKING"][$per]["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
					}elseif($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] != $exPeriodTo[1]){
						$arResult["NUMBERS_BOOKING"][$per]["intPeriodFrom"] = $exPeriodFrom[0]." ".getMonthNameById($exPeriodFrom[1]);
						$arResult["NUMBERS_BOOKING"][$per]["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
					}elseif($exPeriodFrom[2] != $exPeriodTo[2]){
						$arResult["NUMBERS_BOOKING"][$per]["intPeriodFrom"] = $exPeriodFrom[0]." ".getMonthNameById($exPeriodFrom[1])." ".$exPeriodFrom[2];
						$arResult["NUMBERS_BOOKING"][$per]["intPeriodTo"] = $exPeriodTo[0]." ".getMonthNameById($exPeriodTo[1])." ".$exPeriodTo[2];
					}
				}
			}
		}
	}else{
		if(!empty($_SESSION["SERVICES_BOOKING"])) {
			$arRes["SERVICES_BOOKING"] = $_SESSION["SERVICES_BOOKING"];	
			foreach($arRes["SERVICES_BOOKING"] as $per => $SERVICES_BOOKING) {
				unset($filter);
				unset($arSelect);
				unset($dbEl);
				unset($res);					
				$arResult["SERVICES_BOOKING"][$per] = $SERVICES_BOOKING;
				$filter = array(
					"IBLOCK_CODE" => "service",
					"ACTIVE" => "Y",
					"PROPERTY_SERVICEHOTEL" => $SERVICES_BOOKING["Hotel"],
					"ID" => $SERVICES_BOOKING["Id"]
				);
				
				$dbEl = CIBlockElement::GetList(array(), $filter);
				if($res = $dbEl->GetNextElement()) {
					unset($fields);
					unset($props);
					$fields = $res->GetFields();
					$props = $res->GetProperties();
                    $arResult["SERVICES_BOOKING"][$per]["Id"] = $fields["ID"];
					$arResult["SERVICES_BOOKING"][$per]["Price"] = $props["SERVICEPRICE"]["VALUE"];
					$arResult["SERVICES_BOOKING"][$per]["Code"] = $props["SERVICECODE"]["VALUE"];
					if($arResult["LANG"] == "en")							
						$arResult["SERVICES_BOOKING"][$per]["Name"] = $props["SERVICENAMEEN"]["VALUE"];
					else
						$arResult["SERVICES_BOOKING"][$per]["Name"] = $fields["NAME"];
				}
			}
		}else{?>
			<script>
				$(function(){
					var $totalPriceField = $('.total_price');
					var $totalPriceInput = $('[name="total_sum"]');
					var $totalPaymentPriceFields = $('.payment_price');
					if($totalPriceField.length > 0 && $totalPriceInput.length > 0){
						$totalPriceField.html(number_format(parseFloat($totalPriceInput.val()), 2, ',', ' ')+' '+$('[name="total_sum_currency_desc"]').val());
					}
					if($totalPaymentPriceFields.length > 0){
						$totalPaymentPriceFields.each(function(){
							var $this = $(this);
							var $parent = $this.parent().parent();
							var $payment_discount = $parent.find('[name="payment_discount"]');
							var $payment_methods_radiobuttons = $parent.find('[name="payment_methods_radiobuttons"]');
							var $is_first_night = $parent.find('[name="is_first_night"]');
							if($is_first_night.val() != "Y"){
								var total_sum_desc = number_format(parseFloat($totalPriceInput.val()), 2, ',', ' ')+' '+$('[name="total_sum_currency_desc"]').val();
								var total_sum = parseFloat($totalPriceInput.val());
								if($payment_discount.length > 0){
									if($payment_discount.val() && $payment_discount.val() != 100){
										total_sum_desc = number_format(parseFloat($totalPriceInput.val())*parseFloat($payment_discount.val())/100, 2, ',', ' ')+' '+$('[name="total_sum_currency_desc"]').val();
										total_sum = parseFloat($totalPriceInput.val())*parseFloat($payment_discount.val())/100;
									}
								}
								if($payment_methods_radiobuttons.length > 0){						
									var id = $payment_methods_radiobuttons.prop('id');
									var idArray = id.split('-');
									idArray[1] = total_sum*100;
									$payment_methods_radiobuttons.prop('id', idArray.join('-'));
									$payment_methods_radiobuttons.val(idArray.join('-'));
								}
								$this.html(total_sum_desc);
							}
						});
					}
				});
			</script>
		<?}
	}
	
	$this->IncludeComponentTemplate();
}
?>