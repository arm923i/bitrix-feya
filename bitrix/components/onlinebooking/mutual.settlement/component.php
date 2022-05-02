<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("iblock")) {
	echo GetMessage("IBLOCK_NOT_INCLUDE");
	return;
}
elseif(!CModule::IncludeModule("gotech.hotelonline")) {
	ShowError(GetMessage("ONLINEBOOKING_MODULE_NOT_INSTALLED"));	
	return;
}
else {
	CEventLog::Add(array(
	 "SEVERITY" => "SECURITY",
	 "AUDIT_TYPE_ID" => "Agent",
	 "MODULE_ID" => "main",
	 "ITEM_ID" => "GetAgentReport",
	 "DESCRIPTION" => "",
	));	
	$APPLICATION->IncludeComponent("onlinebooking:reservation.header", "");		
	$language = OnlineBookingSupport::getLanguage();
	if($this->includeComponentLang("", $language) == NULL){
		__IncludeLang(dirname(__FILE__)."/lang/".$language."/component.php");
	}
	$APPLICATION->SetTitle(GetMessage("TITLE_MODULE"));
	$ar_group = $USER->GetUserGroup($USER->GetID());	
	if($USER->IsAuthorized() && in_array(COption::GetOptionint('gotech.hotelonline', 'USER_AGENT_GROUP'), $ar_group)) {
		if(isset($_REQUEST["DateFrom"]) && isset($_REQUEST["DateTo"])){
			if(!empty($_REQUEST["DateFrom"]) && !empty($_REQUEST["DateTo"])){
				if($_REQUEST["DateFrom"]>$_REQUEST["DateTo"]){
					ShowError(GetMessage("CHECK_DATES"));
				}else{
					$date_array = explode('.', $_REQUEST["DateFrom"]);
					$periodFrom = $date_array[2]."-".$date_array[1]."-".$date_array[0]."T00:00:00";
					$date_array = explode('.', $_REQUEST["DateTo"]);
					$periodTo = $date_array[2]."-".$date_array[1]."-".$date_array[0]."T23:59:59";
					$iblock_id_hotel = COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID');
					$hotels = CIBlockElement::GetList(array("SORT" => "ASC"), array("IBLOCK_ID" => $iblock_id_hotel, "ACTIVE" => "Y"), false,
					false, array("NAME", "PROPERTY_HOTEL_CODE", "PROPERTY_ADDRESS_WEB_SERVICE", "PROPERTY_HOTEL_OUTPUT_CODE"));
					while($hotel = $hotels->GetNext()) {
						unset($query);
						if(!empty($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"])){
							$WSDL = $hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"];
						}else{
							$WSDL = COption::GetOptionString('gotech.hotelonline', 'AddressWebservice');
						}
                        $soap_params = array('trace' => 1);
                        $soapclient = new SoapClient(trim($WSDL), $soap_params);
						
						$query = array(
							"Login" => $USER->GetLogin(),
							"Hotel" => $hotel["PROPERTY_HOTEL_CODE_VALUE"],
							"ExternalSystemCode" => $hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]?$hotel["PROPERTY_HOTEL_OUTPUT_CODE_VALUE"]:COption::GetOptionString('gotech.hotelonline', 'OutputCode'),
							"LanguageCode" => $language,
							"DateFrom" => $periodFrom,
							"DateTo" => $periodTo
						);
						$arResult["SUCCESS"] = False;
						try {
							$result = $soapclient->GetAgentReport($query);
							
							if(substr($result->return, 0, 6) == "Base64" || strlen($result->return) > 1000){
								$base64Text = $result->return;
								$base64Text = str_replace("Base64", "", $base64Text);
								$base64Text = htmlspecialchars($base64Text);
								$base64Text = str_replace(" ", "", $base64Text);
								$base64Text = str_replace("\r\n", "", $base64Text);
								$base64Text = str_replace("\n", "", $base64Text);
								$base64Text = str_replace("\r", "", $base64Text);
								$base64Text = strtr($base64Text, '-_', '+/');
								$base64Text = preg_replace('/[\t-\x0d\s]/', '', $base64Text);
								$mod4 = strlen($base64Text) % 4;
								if ($mod4) {
									$base64Text .= substr('====', $mod4);
								}
								//Decode pdf content
								$pdf_decoded = base64_decode($base64Text, false);
								$arPDF = Array(
									"name" => "test1.pdf",
									"size" => "",
									"type" => "pdf",
									"del" => "Y",
									"MODULE_ID" => "forum",
									"content" => $pdf_decoded
								);
								$fid = CFile::SaveFile($arPDF, "settlements");
								$fPath = CFile::GetPath($fid);
								$fileName = "Settlement_".str_replace("-", "", substr($periodFrom, 0, 10))."_".str_replace("-", "", substr($periodTo, 0, 10)).".pdf";
								OnlineBookingSupport::file_force_download($_SERVER["DOCUMENT_ROOT"].$fPath, $fileName);
								CFile::Delete($fid);
								$arResult["RESULT"] = $result->return;
								$arResult["SUCCESS"] = True;
							}else{
								$arResult["RESULT"] = $result->return;
								$arResult["SUCCESS"] = False;
							}
						} catch (Exception $e) {
							$arResult["RESULT"] = GetMessage("REPORT_NOT_SEND");
							$arFields = array(
								"event" => "Get mutual settlement"
								,"data" => "WSDL: ".trim($WSDL).";\r\n DateFrom: ".$periodFrom.";\r\n DateTo: ".$periodTo.";\r\n Login: ".$USER->GetLogin()
								,"error_text" => $e
							);
							$ID = OnlineBookingSupport::db_add('ob_gotech_errors', $arFields);
						}
					}
				}
			}else{
				ShowError(GetMessage("CHECK_DATES"));
			}
		}
		$this->IncludeComponentTemplate();
	}
	else {		
		ShowError(GetMessage("NOT_ACCESS"));
		return;
	}
}
?>