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
  CModule::IncludeModule("gotech.hotelonline");

	/* Variables */
	$WSDL = $_REQUEST["wsdl"];
	$guest_group = $_REQUEST["guest_group"];
	$language = $_REQUEST["language"];
	$login = $_REQUEST["login"];
	$reservationRows = $_REQUEST["reservationRows"];
	$contact_phone = $_REQUEST["contact_phone"];
	$contact_email = $_REQUEST["contact_email"];
	$isUpgrade = $_REQUEST["isUpgrade"];
	$old_new_data = $_REQUEST["old_new_data"];

	/* Adding reservations data*/
	foreach($reservationRows as $key => $guest) {
		//if($guest["guid"] || $guest["is_annulation"] != "Y"){
		if($guest["guid"] || $guest["is_annulation"] != "Y"){
			$dop_fields = array(
				"ReservationCode" => $guest["guid"] ? $guest["guid"] : GUID(),
				"GroupCode" => $guest["guest_group"],
				"GroupDescription" => "",
				"GroupCustomer" => "",
				"ReservationStatus" => $guest["is_annulation"] == "Y"? $guest["annul_status_code"]:$guest["res_status_code"],
				"PeriodFrom" => $guest["check_in_date"],
				"PeriodTo" =>  $guest["check_out_date"],
				"Hotel" => $guest["hotel"],
				"RoomType" => $guest["room_type"],
				"AccommodationType" => $guest["acc_type_code"],
				"ClientType" => "",
				"RoomQuota" => $guest["room_quota"],
				"RoomRate" => $guest["room_rate"],
				"Customer" => $guest["customer"],
				"Contract" => $guest["contract"],
				"Agent" => "",
				"ContactPerson" => "",
				"NumberOfRooms" => 1,
				"NumberOfPersons" => 1,
				"Client" => array(
					"ClientCode" => $guest["code"] ? $guest["code"] : "",
					"ClientLastName" => $guest["surname"] ? $guest["surname"] : "",
					"ClientFirstName" => $guest["name"] ? $guest["name"] : "",
					"ClientSecondName" => $guest["secondName"] ? $guest["secondName"] : "",
					"ClientSex" => "",
					"ClientCitizenship" => "",
					"ClientBirthDate" => $guest["birthday"] ? getDateFormat($guest["birthday"]) : "0001-01-01T00:00:00",
					"ClientPhone" => ($key == 0) ? $contact_phone : "",
					"ClientFax" => "",
					"ClientEMail" => ($key == 0) ? $contact_email : "",
					"ClientRemarks" => ""
				),
				"ReservationRemarks" => "",
				"Car" => "",
				"PlannedPaymentMethod" => $guest["payment_method"],
				"ExternalSystemCode" => $guest["output_code"],
				"DoPosting" => true,
				"PromoCode" => "",
				"Room" => $guest["roomGUID"],
        "IsUpgrade" => $isUpgrade == 'Y'
				//"ChargeExtraServices" =>array("ChargeExtraServiceRow" => $guest["services"])
			);

      if ($guest["doChangeDoc"] == "Y") {
        $dop_fields["Client"]["ClientIdentityDocumentType"] = $guest["docType"];
        $dop_fields["Client"]["ClientIdentityDocumentSeries"] = $guest["docSeries"];
        $dop_fields["Client"]["ClientIdentityDocumentNumber"] = $guest["docNumber"];
		$dop_fields["Client"]["ClientIdentityDocumentUnitCode"] = $guest["docUnitCode"];
		$dop_fields["Client"]["ClientIdentityDocumentIssuedBy"] = $guest["docIssuedBy"];
        $dop_fields["Client"]["ClientIdentityDocumentIssueDate"] = $guest["docDate"] ? getDateFormat($guest["docDate"]) : "0001-01-01T00:00:00";
      }

      if ($guest["doChangeAddress"] == "Y") {
        $dop_fields["Client"]["Address"] = $guest["address"];
      }

			if(isset($guest["services"]) && count($guest["services"])) {
        foreach($guest["services"] as &$service) {
          if (isset($service["OrderDate"]) && isset($service["OrderTime"])) {
            $service["OrderDate"] = OnlineBookingSupport::getDateFormat($service["OrderDate"]." ".$service["OrderTime"]);
            unset($service["OrderTime"]);
          }
        }
        $dop_fields['ChargeExtraServices'] = array("ChargeExtraServiceRow" => $guest["services"]);
      }

			$WriteExternalGroupReservationRow[] = $dop_fields;
		}
	}

	$ab = array(
		"WriteExternalGroupReservationRows" => array(
			"WriteExternalGroupReservationRow" => $WriteExternalGroupReservationRow,
			"Login" => $login,
			"TransferBooked" => false,
			"TransferTime" => "0001-01-01T00:00:00",
			"TransferPlace" => "",
			"TransferRemarks" => "",
			//"ChargeExtraServices" =>array("ChargeExtraServiceRow" => $_REQUEST["extra_services"])

		),
		"Language" => strtoupper($language)
	);

	$soap_params = array('trace' => 1);
	$soapclient = new SoapClient(trim($WSDL), $soap_params);
try{
	$result = $soapclient->WriteExternalGroupReservation($ab);
}
catch (Exception $e) {
    echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
}
	if($result->return->ErrorDescription){
        $arrJSON = array(
            "return_code" => -1,
            "error" => $result->return->ErrorDescription,
        );
        echo json_encode($arrJSON);
	}else{
		$arrJSON = array(
			"return_code" => 0,
			"test" => count($WriteExternalGroupReservationRow),
			"test2" => $WriteExternalGroupReservationRow,
		);
		echo json_encode($arrJSON);
	}

  if ($old_new_data) {
    if ($isUpgrade == 'Y') {
      $arFields = array(
        "1c_group_code" => $old_new_data["guest_group"]
      , "1c_hotel_code" => $old_new_data["hotel"]
      , "check_in_date" => date("d.m.Y H:i:s", strtotime($old_new_data["new_check_in_date"]))
      , "check_out_date" => date("d.m.Y H:i:s", strtotime($old_new_data["new_check_out_date"]))
      , "adults" => $old_new_data["old_adults"]
      , "children" => $old_new_data["old_children"]
      , "old_1c_room_type_code" => $old_new_data["old_rt_code"]
      , "new_1c_room_type_code" => $old_new_data["new_rt_code"]
      , "old_1c_accommodation_type_codes" => $old_new_data["old_acc_codes"]
      , "new_1c_accommodation_type_codes" => $old_new_data["new_acc_codes"]
      , "1c_rate_code" => $old_new_data["rr_code"]
      , "1c_quota_code" => $old_new_data["rq_code"]
      , "old_total" => $old_new_data["old_total"]
      , "new_total" => $result->return->TotalSum
      , "error_text" => $arrJSON["error"] ? $arrJSON["error"] : ""
      );
      $ID = OnlineBookingSupport::db_add('ob_gotech_upgrade_reservations', $arFields);
    } else {
      $arFields = array(
        "1c_group_code" => $old_new_data["guest_group"]
      , "1c_hotel_code" => $old_new_data["hotel"]
      , "old_check_in_date" => date("d.m.Y H:i:s", strtotime($old_new_data["old_check_in_date"]))
      , "old_check_out_date" => date("d.m.Y H:i:s", strtotime($old_new_data["old_check_out_date"]))
      , "new_check_in_date" => date("d.m.Y H:i:s", strtotime($old_new_data["new_check_in_date"]))
      , "new_check_out_date" => date("d.m.Y H:i:s", strtotime($old_new_data["new_check_out_date"]))
      , "old_adults" => $old_new_data["old_adults"]
      , "old_children" => $old_new_data["old_children"]
      , "new_adults" => $old_new_data["new_adults"]
      , "new_children" => $old_new_data["new_children"]
      , "1c_room_type_code" => $old_new_data["old_rt_code"]
      , "1c_rate_code" => $old_new_data["rr_code"]
      , "1c_quota_code" => $old_new_data["rq_code"]
      , "old_1c_accommodation_type_codes" => $old_new_data["old_acc_codes"]
      , "new_1c_accommodation_type_codes" => $old_new_data["new_acc_codes"]
      , "old_total" => $old_new_data["old_total"]
      , "new_total" => $result->return->TotalSum
      , "error_text" => $arrJSON["error"] ? $arrJSON["error"] : ""
      );
      $ID = OnlineBookingSupport::db_add('ob_gotech_change_reservations', $arFields);
    }
  }

	function getDateFormat($date) {
		if(!$date) {
			return "0001-01-01T00:00:00";
		}
		else {
			if(strlen($date) > 10) {
				$date_with_time_array = explode(" ", $date);
				$date_array = explode('.', $date_with_time_array[0]);
				if(count($date_array) == 1){
					$date_array = explode('-', $date_with_time_array[0]);
				}
				$time_array = explode(':', $date_with_time_array[1]);
				if(count($time_array) == 2)
					$time_array[] = "00";
				if(strlen($date_array[2]) == 4){
					return $date_array[2]."-".$date_array[1]."-".$date_array[0]."T".$time_array[0].":".$time_array[1].":".$time_array[2];
				}else{
					return $date_array[0]."-".$date_array[1]."-".$date_array[2]."T".$time_array[0].":".$time_array[1].":".$time_array[2];
				}
			}
			elseif(strlen($date) == 10) {
				$date_array = explode('.', $date);
				return $date_array[2]."-".$date_array[1]."-".$date_array[0]."T".date('H:i:s');
			}
		}
    return "0001-01-01T00:00:00";
	}
	function GUID() {
		if (function_exists('com_create_guid') === true)
			return trim(com_create_guid(), '{}');
		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}
?>
