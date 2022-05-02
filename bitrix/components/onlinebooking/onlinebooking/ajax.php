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
switch ($_REQUEST['FormType'])
{
    case 'addOrder':


        $lang = $_REQUEST["language"];
        foreach($_REQUEST as $key => $req) {
            if($key == 'RoomTypeCode') $number["RoomTypeCode"] = $req;
            if($key == 'PeriodFrom') $number["PeriodFrom"] = $req;
            if($key == 'PeriodTo') $number["PeriodTo"] = $req;
            if($key == 'FirstDaySum') $number["FirstDaySum"] = $req;
            if($key == 'Amount') $number["Amount"] = $req;
            if($key == 'RoomCode') $number["RoomCode"] = $req;
            if($key == 'RoomInfoName') $number["RoomInfoName"] = $req;
            if($key == 'RoomInfoText') $number["RoomInfoText"] = $req;
            if($key == 'RoomName'){
                if($lang == 'ru'){
                    $number["RoomName"] = $req;
                }
            }
            if($key == 'RoomNameEn'){
                if($lang == 'en'){
                    $number["RoomName"] = $req;
                }
            }
            if($key == 'Currency'){
                if($req == 'RUB'){
                    $number["Currency"] = "<span class='gotech_ruble'>a</span>";
                }elseif($req == 'EUR' || $req == 'EURO'){
                    $number["Currency"] = '&euro;';
                }elseif($req == 'USD'){
                    $number["Currency"] = '$';
                }elseif($req == 'KGS'){
                  $number["Currency"] = 'KGS';
                }else{
                    $number["Currency"] = "<span class='gotech_ruble'>a</span>";
                }
            }
            if($key == 'visitors') $number["visitors"] = $req;
            if($key == 'cart_id') $number["cart_id"] = $req;
            if($key == 'key') $number["key"] = $req;
            if($key == 'AllotmentCode') $number["AllotmentCode"] = $req;
            if($key == 'RoomRateCodeDesc') $number["RoomRateCodeDesc"] = $req;
            if($key == 'RoomRateCode') $number["RoomRateCode"] = str_replace("thisisprocent", "%", $req);
            if($key == 'PaymentMethodCodesAllowedOnline') $number["PaymentMethodCodesAllowedOnline"] = $req;

            if(strstr($key, 'Accommodation_Code_')) {
                $ar = explode('_', $key);
                $number["Accommodation"][$ar[2]]["Code"] = $req;
            }
            if(strstr($key, 'Accommodation_Description_')) {
                $ar = explode('_', $key);
                $number["Accommodation"][$ar[2]]["Description"] = $req;
            }
            if(strstr($key, 'Accommodation_Age_')) {
                $ar = explode('_', $key);
                $number["Accommodation"][$ar[2]]["Age"] = $req;
            }
            if(strstr($key, 'Accommodation_Is_Child_')) {
                $ar = explode('_', $key);
                $number["Accommodation"][$ar[3]]["IsChild"] = $req;
            }
            if(strstr($key, 'Accommodation_Client_Age_From_')) {
                $ar = explode('_', $key);
                $number["Accommodation"][$ar[4]]["ClientAgeFrom"] = $req;
            }
            if(strstr($key, 'Accommodation_Client_Age_To_')) {
                $ar = explode('_', $key);
                $number["Accommodation"][$ar[4]]["ClientAgeTo"] = $req;
            }
            if(count($_SESSION["NUMBERS_BOOKING"][$_REQUEST["hotel_id"]]["NUMBERS"]) > 0)
                $number["Id"] = count($_SESSION["NUMBERS_BOOKING"][$_REQUEST["hotel_id"]]["NUMBERS"])+1;
            else $number["Id"] = 1;
            if($key == 'PromoCode')
                $number["PromoCode"] = $req;

            //$number['unique'] = rand(10000,10000000);
        }
        if(is_array($number))
            for($i=0;$i<$_REQUEST['count'];$i++)
            {
                $number['unique'] = rand(10000,10000000);
                $_SESSION["NUMBERS_BOOKING"][$_REQUEST["hotel_id"]]["NUMBERS"][] = $number;
            }


        $APPLICATION->IncludeComponent("onlinebooking:reservation.chose", "", array("ID_HOTEL" => $_REQUEST["hotel_id"], "CURR_PAGE" => $_REQUEST["curr_page"], "TYPE" => "NUMBER"));
        break;

    case 'deleteOrder':
        $RTcode = "";

        if(!empty($_SESSION["NUMBERS_BOOKING"][$_REQUEST["Hotel"]]))
            foreach($_SESSION["NUMBERS_BOOKING"][$_REQUEST["Hotel"]]["NUMBERS"] as $key => $NUMBERS_BOOKING) {
                if($NUMBERS_BOOKING["Id"] == $_REQUEST["Id"] && $NUMBERS_BOOKING['unique'] == $_REQUEST['unique']){
                    $RTkey = $NUMBERS_BOOKING["key"];
                    unset($_SESSION["NUMBERS_BOOKING"][$_REQUEST["Hotel"]]["NUMBERS"][$key]);
                }
                if(isset($_REQUEST["cart_id"]) && $NUMBERS_BOOKING['cart_id'] == $_REQUEST["cart_id"])
                {
                    $delete_id = $NUMBERS_BOOKING['Id'];
                    unset($_SESSION["NUMBERS_BOOKING"][$_REQUEST["Hotel"]]["NUMBERS"][$key]);
                }
            }
        //удление номеров одного типа
        if(isset($_REQUEST["cart_id"]))
        {
            foreach($_SESSION["NUMBERS_BOOKING"][$_REQUEST["Hotel"]]["NUMBERS"] as $key => $NUMBERS_BOOKING)
            {
                if($NUMBERS_BOOKING["Id"] == $delete_id)
                {
                    unset($_SESSION["NUMBERS_BOOKING"][$_REQUEST["Hotel"]]["NUMBERS"][$key]);
                }
            }
        }
        /*
        $temp = array_values($_SESSION["NUMBERS_BOOKING"][$_REQUEST["Hotel"]]["NUMBERS"]);
        $_SESSION["NUMBERS_BOOKING"][$_REQUEST["Hotel"]]["NUMBERS"] = $temp;
        */

        $APPLICATION->IncludeComponent("onlinebooking:reservation.chose", "", array("ID_HOTEL" => $_REQUEST["Hotel"], "CURR_PAGE" => $_REQUEST["curr_page"], "UNUSED_RT" => $RTkey, "TYPE" => "NUMBER"));
        break;
    case 'getBasket':

        $APPLICATION->IncludeComponent("onlinebooking:reservation.chose", "", array("ID_HOTEL" => $_REQUEST["hotel_id"], "CURR_PAGE" => $_REQUEST["curr_page"], "UNUSED_RT" => $RTkey, "TYPE" => "NUMBER"));
        break;

    case 'addOrderExSe':
        foreach($_REQUEST as $key => $req) {
            if($key == 'hotel') $service["Hotel"] = $req;
            if($key == 'hotel_id') $service["Hotel_id"] = $req;
            if($key == 'id') $service["Id"] = $req;
            if($key == 'code') $service["Code"] = $req;
            if($key == 'Currency') $service["Currency"] = $req;
            if($key == 'age_from') $service["age_from"] = $req;
            if($key == 'age_to') $service["age_to"] = $req;
            if($key == 'number_to_guest') $service["number_to_guest"] = $req;
            if($key == 'number_to_room') $service["number_to_room"] = $req;
            if($key == 'GuestID') $service["GuestID"] = $req;
            if($key == 'is_transfer') $service["IsTransfer"] = $req;
            if($key == 'transfer_date') $service["TransferDate"] = $req;
            if($key == 'transfer_time') $service["TransferTime"] = $req;
            if($key == 'transfer_place') $service["TransferPlace"] = $req;
            if($key == 'transfer_remarks') $service["TransferRemarks"] = $req;
            if($key == 'transfer_childseats') $service["TransferChildseats"] = $req;


            if(count($_SESSION["SERVICES_BOOKING"]) > 0)
                $service["cId"] = count($_SESSION["SERVICES_BOOKING"])+1;
            else $service["cId"] = 1;
        }

        $add = true;
        foreach($_SESSION["SERVICES_BOOKING"] as $k => $v)
        {
            if($v['GuestID'] == $service["GuestID"] && $v['Id'] == $service["Id"] && $v['Code'] == $service["Code"])
                $add = false;
        }

        if(is_array($service) && $add)
            $_SESSION["SERVICES_BOOKING"][] = $service;

        $APPLICATION->IncludeComponent("onlinebooking:reservation.chose", "", array("ID_HOTEL" => $_REQUEST["hotel_id"], "TYPE" => "SERVICE"));
        break;

    case 'deleteOrderExSe':
        if(!empty($_SESSION["SERVICES_BOOKING"]))
            foreach($_SESSION["SERVICES_BOOKING"] as $key => $SERVICES_BOOKING) {
                //if($SERVICES_BOOKING["cId"] == $_REQUEST["Id"]){
                if($SERVICES_BOOKING["Id"] == $_REQUEST["id"] && $SERVICES_BOOKING["GuestID"] == $_REQUEST["GuestID"]){
                    unset($_SESSION["SERVICES_BOOKING"][$key]);
                }
            }
        $APPLICATION->IncludeComponent("onlinebooking:reservation.chose", "", array("ID_HOTEL" => $_REQUEST["hotel_id"], "TYPE" => "SERVICE"));
        break;
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
