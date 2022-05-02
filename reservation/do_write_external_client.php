<?
ini_set("session.use_only_cookies", false);
ini_set("session.use_trans_sid", 1);
ini_set("session.cache_limiter", "");

session_start();
?>
<?
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("gotech.hotelonline");

/* Variables */
$WSDL = $_REQUEST["wsdl"];
$guest = $_REQUEST["client"];

$pictures = array(
//		array(
//			"PictureType" => "",
//			"PictureData" => ""
//		)
);

if ($guest["pictures"]) {
    foreach($guest["pictures"] as $picture) {
        $pictures[] = array(
            "PictureType" => $picture["type"],
			"PictureData" => $picture["content"]
        );
    }
}

$clientData = array(
    "ReservationCode" => $guest["guid"],
    "ClientCode" => $guest["code"] ? $guest["code"] : "",
    "ClientLastName" => $guest["surname"] ? $guest["surname"] : "",
    "ClientFirstName" => $guest["name"] ? $guest["name"] : "",
    "ClientSecondName" => $guest["secondName"] ? $guest["secondName"] : "",
    "ClientSex" => "",
    "ClientCitizenship" => "",
    "ClientBirthDate" => $guest["birthday"] ? getDateFormat($guest["birthday"], false) : "0001-01-01T00:00:00",
    "ClientPhone" => $guest["phone"] ? $guest["phone"] : "",
    "ClientFax" => "",
    "ClientEMail" => $guest["email"] ? $guest["email"] : "",
    "ClientRemarks" => "",
    "ClientIdentityDocumentType" => $guest["docType"],
    "ClientIdentityDocumentSeries" => $guest["docSeries"],
    "ClientIdentityDocumentNumber" => $guest["docNumber"],
    "ClientIdentityDocumentUnitCode" => $guest["docUnitCode"],
    "ClientIdentityDocumentIssuedBy" => $guest["docIssuedBy"],
    "ClientIdentityDocumentIssueDate" => $guest["docDate"] ? getDateFormat($guest["docDate"], true) : "0001-01-01",
    "Address" => $guest["address"],
    "Hotel" => $guest["hotel"],
    "ExternalSystemCode" => $guest["output_code"],
    "ClientExtraData" => array(
        "GuestGroup" => $guest["guest_group"],
        "IdentityDocumentPages" => array("Picture" => $pictures)
    )
);
$soap_params = array('trace' => 1);
$soapclient = new SoapClient(trim($WSDL), $soap_params);
try {
    $result = $soapclient->WriteExternalClientExt($clientData);
} catch (Exception $e) {
    echo 'Выброшено исключение: ', $e->getMessage(), "\n";
}
if ($result->return->ErrorDescription) {
    $arrJSON = array(
        "return_code" => -1,
        "error" => $result->return->ErrorDescription,
    );
    echo json_encode($arrJSON);
} else {
    $arrJSON = array(
        "return_code" => 0,
    );
    echo json_encode($arrJSON);
}

function getDateFormat($date, $withoutTime)
{
    if (!$date) {
        return $withoutTime ? "0001-01-01" : "0001-01-01T00:00:00";
    } else {
        if (strlen($date) > 10) {
            $date_with_time_array = explode(" ", $date);
            $date_array = explode('.', $date_with_time_array[0]);
            if (count($date_array) == 1) {
                $date_array = explode('-', $date_with_time_array[0]);
            }
            $time_array = explode(':', $date_with_time_array[1]);
            if (count($time_array) == 2)
                $time_array[] = "00";
            if (strlen($date_array[2]) == 4) {
                return $withoutTime ? $date_array[2] . "-" . $date_array[1] . "-" . $date_array[0] : $date_array[2] . "-" . $date_array[1] . "-" . $date_array[0] . "T" . $time_array[0] . ":" . $time_array[1] . ":" . $time_array[2];
            } else {
                return $withoutTime ? $date_array[0] . "-" . $date_array[1] . "-" . $date_array[2] : $date_array[0] . "-" . $date_array[1] . "-" . $date_array[2] . "T" . $time_array[0] . ":" . $time_array[1] . ":" . $time_array[2];
            }
        } elseif (strlen($date) == 10) {
            $date_array = explode('.', $date);
            return $withoutTime ? $date_array[2] . "-" . $date_array[1] . "-" . $date_array[0] : $date_array[2] . "-" . $date_array[1] . "-" . $date_array[0] . "T" . date('H:i:s');
        }
    }
    return $withoutTime ? "0001-01-01" : "0001-01-01T00:00:00";
}

?>
