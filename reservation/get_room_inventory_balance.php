<?
$WSDL = $_REQUEST["wsdl"];
$adults = $_REQUEST["adults"];
$rate = $_REQUEST["rate"];
$is_earlier = isset($_REQUEST["earlier"]);
$is_later = isset($_REQUEST["later"]);
$first_date = isset($_REQUEST["first_date"]) ? $_REQUEST["first_date"] : "";
$last_date = isset($_REQUEST["last_date"]) ? $_REQUEST["last_date"] : "";
$from_text = $_REQUEST["from_text"]; // GetMessage('FROM1')
$is_av_text = $_REQUEST["is_av_text"]; // GetMessage('IS_AV')
$title_text = $_REQUEST["title_text"]; // GetMessage('ROOMS_AVAILABILITY')
$later_text = $_REQUEST["later_text"]; // GetMessage('LATER')
$earlier_text = $_REQUEST["earlier_text"]; // GetMessage('EARLIER')
$GetRoomInventoryBalanceArr = json_decode($_REQUEST["data"]);
if ($is_earlier) {
    $h_from = explode("T", $GetRoomInventoryBalanceArr->PeriodFrom)[1];
    $h_to = explode("T", $GetRoomInventoryBalanceArr->PeriodTo)[1];

    $d1 = date('Y-m-d',(strtotime($first_date) - 8 * 60*60*24)).'T'.$h_from;
    $d2 = date('Y-m-d',(strtotime($first_date) - 1 * 60*60*24)).'T'.$h_to;

    $GetRoomInventoryBalanceArr->PeriodFrom = $d1;
    $GetRoomInventoryBalanceArr->PeriodTo = $d2;
}
if ($is_later) {
    $h_from = explode("T", $GetRoomInventoryBalanceArr->PeriodFrom)[1];
    $h_to = explode("T", $GetRoomInventoryBalanceArr->PeriodTo)[1];

    $d1 = date('Y-m-d',(strtotime($last_date) + 1 * 60*60*24)).'T'.$h_from;
    $d2 = date('Y-m-d',(strtotime($last_date) + 8 * 60*60*24)).'T'.$h_to;

    $GetRoomInventoryBalanceArr->PeriodFrom = $d1;
    $GetRoomInventoryBalanceArr->PeriodTo = $d2;
}
$GetRoomInventoryBalanceArr->RoomRates = array("RoomRate" => $rate);
$GetRoomInventoryBalanceArr->ExtraParameters = array("Duration" => intval((strtotime($GetRoomInventoryBalanceArr->RealPeriodTo) - strtotime($GetRoomInventoryBalanceArr->RealPeriodFrom)) / 86400));
$roomQuota = $GetRoomInventoryBalanceArr->RoomQuota;
ini_set("soap.wsdl_cache_enabled", 0);
ini_set("soap.wsdl_cache_ttl", 0);
$is_error = false;
try {
    $soap_params = array('trace' => 1);
    $soapclient = new SoapClient(trim($WSDL), $soap_params);
    // var_dump($GetRoomInventoryBalanceArr);
    $res2 = $soapclient->GetAvailableRoomsWithDailyPrices($GetRoomInventoryBalanceArr);
    // var_dump($res2->return);
}catch (SoapFault $fault) {
    writeError($fault->faultstring, $GetRoomInventoryBalanceArr);

    $is_error = true;
    $result = "<div style='display:none'>".$fault->faultstring."</div>";

    $p_from = explode("T", $GetRoomInventoryBalanceArr->PeriodFrom)[0];
    $p_to = explode("T", $GetRoomInventoryBalanceArr->PeriodTo)[0];

    $dates = '';
    $values = '';
    if (!$is_earlier && !$is_later) {
        $dates .= '<tr class="dates">';
        $values .= '<tr class="prices">';
    }
    while (strtotime($p_from) <= strtotime($p_to)) {
        $dates .= '<td data-date="'.strtotime($p_from).'">';
        $dates .= date('d.m.Y',strtotime($p_from));
        $dates .= '</td>';

        $values .= '<td data-date="'.strtotime($p_from).'" class="no_av ';
        if(strtotime($p_from) >= strtotime($GetRoomInventoryBalanceArr->RealPeriodFrom) && strtotime($p_from) < strtotime($GetRoomInventoryBalanceArr->RealPeriodTo)) {
            $values .= 'real_period';
        }
        $values .= '">';
        $values .= '<div style="min-height:15px;"></div>';
        $values .= '</td>';
        $p_from = date ("Y-m-d", strtotime("+1 day", strtotime($p_from)));
    }
    if (!$is_earlier && !$is_later) {
        $dates .= '</tr>';
        $values .= '</tr>';
    }

    if (!$is_earlier && !$is_later) {
        $d = $dates . $values;
        if (!$rate) {
            $result .= <<<EOT
        <div class="availibility_block" style="display: block;">
            <form>
                <input type="hidden" name="rtc" value="$GetRoomInventoryBalanceArr->RoomType">
                <input type="hidden" name="hid" value="$GetRoomInventoryBalanceArr->Hotel">
                <input type="hidden" name="htf" value="$h_from">
                <input type="hidden" name="ht" value="$h_to">
                <input type="hidden" name="cur_rate" value="$rr_key">
                <input type="hidden" name="data" value="$data">
            </form>
            <div class="h">$title_text</div>
            <table id="room_sold">$d</table>
            <a href="#" class="later" onclick="event.preventDefault();later_click($(this));return false;">$later_text</a>
            <a href="#" class="earlier" onclick="event.preventDefault();earlier_click($(this));return false;">$earlier_text</a>
        </div>
EOT;
        } else {
            $result .= $d;
        }
    }

}

if(!$is_error) {
    $result = "";
    if ($rate) {
        $result = "";
        if (!$is_earlier && !$is_later) {
            $result .= '<tr class="dates">';
        } else {
            $dates = "";
        }
        if (isset($res2->return->RoomTypeDailyAvailabilityAndPricesRow)) {
            $RoomTypeDailyAvailabilityAndPricesRows = array();
            if(!is_array($res2->return->RoomTypeDailyAvailabilityAndPricesRow))
                $RoomTypeDailyAvailabilityAndPricesRows[] = $res2->return->RoomTypeDailyAvailabilityAndPricesRow;
            else $RoomTypeDailyAvailabilityAndPricesRows = $res2->return->RoomTypeDailyAvailabilityAndPricesRow;
            // var_dump($RoomTypeDailyAvailabilityAndPricesRows);
            foreach($RoomTypeDailyAvailabilityAndPricesRows as $k => $v) {
                $result .= '<td data-date="'.strtotime($v->Period).'" ';
                if (!empty($roomQuota)) {
                    if((int)$v->RoomsRemains) {
                        $result .= 'class="av"';
                    }
                } else {
                    if((int)$v->RoomsVacant) {
                        $result .= 'class="av"';
                    }
                }
                $result .= '>';
                $result .= date('d.m.Y',strtotime($v->Period));
                $result .= '</td>';
                if(!$k) {
                    $first_date = date('d.m.Y',strtotime($v->Period));
                }
            }
            if (!$is_earlier && !$is_later) {
                $result .= '</tr>';
                $result .= '<tr class="prices">';
            } else {
                $dates = $result;
                $result = "";
                $values = "";
            }
            foreach($RoomTypeDailyAvailabilityAndPricesRows as $k => $v){
                $result .= '<td data-date="'.strtotime($v->Period).'" class="';
                $rooms_count = 0;
                if (!empty($roomQuota)) {
                    $rooms_count = (int)$v->RoomsRemains;
                } else {
                    $rooms_count = (int)$v->RoomsVacant;
                }

                if(!$rooms_count) {
                    $result .= 'no_av';
                } else {
                    $result .= 'av';
                }
                if(strtotime($v->Period) >= strtotime($GetRoomInventoryBalanceArr->RealPeriodFrom) && strtotime($v->Period) < strtotime($GetRoomInventoryBalanceArr->RealPeriodTo)) {
                    $result .= ' real_period';
                }
                $result .= '">';
                if(isset($v->RoomTypeDailyPrices)){
                    $RoomRateDailyPriceRow = $v->RoomTypeDailyPrices->RoomRateDailyPriceRow;
                    $RoomRateDailyPriceRows = array();
                    if(!is_array($RoomRateDailyPriceRow))
                        $RoomRateDailyPriceRows[] = $RoomRateDailyPriceRow;
                    else $RoomRateDailyPriceRows = $RoomRateDailyPriceRow;


                    $min_price = 0;
                    $rate_finded = false;
                    foreach($RoomRateDailyPriceRows as $key=>$arr){
                        $price = (int)$arr->Price;
                        if ($arr->RoomRateCode == $rate) {
                            $rate_finded = true;
                            $result .= $price.'<br/>';
                        }
                        // if ($min_price == 0 || $price < $min_price) {
                        //     $min_price = $price;
                        // }
                    }

                    if(!$rate_finded) {
                        $result .= '<div style="min-height:15px;line-height:15px;">'.$is_av_text.'</div>';
                    }
                } else {
                    $result .= '<div style="min-height:15px;"></div>';
                }
                $result .= '</td>';
            }
        } else {
            // $result .= '<td><div style="min-height:15px;"></div></td>';

            $p_from = explode("T", $GetRoomInventoryBalanceArr->PeriodFrom)[0];
            $p_to = explode("T", $GetRoomInventoryBalanceArr->PeriodTo)[0];

            $dates = '';
            $values = '';
            if (!$is_earlier && !$is_later) {
                $dates .= '<tr class="dates">';
                $values .= '<tr class="prices">';
            }
            while (strtotime($p_from) <= strtotime($p_to)) {
                $dates .= '<td data-date="'.strtotime($p_from).'">';
                $dates .= date('d.m.Y',strtotime($p_from));
                $dates .= '</td>';

                $values .= '<td data-date="'.strtotime($p_from).'" class="no_av ';
                if(strtotime($p_from) >= strtotime($GetRoomInventoryBalanceArr->RealPeriodFrom) && strtotime($p_from) < strtotime($GetRoomInventoryBalanceArr->RealPeriodTo)) {
                    $values .= 'real_period';
                }
                $values .= '">';
                $values .= '<div style="min-height:15px;"></div>';
                $values .= '</td>';
                $p_from = date ("Y-m-d", strtotime("+1 day", strtotime($p_from)));
            }

            if (!$is_earlier && !$is_later) {
                $dates .= '</tr>';
                $result = $dates . $values;
            }
        }
        if (!$is_earlier && !$is_later) {
            $result .= '</tr>';
        } else {
            if (isset($res2->return->RoomTypeDailyAvailabilityAndPricesRow)) {
                $values = $result;
            }
        }
    } else {
        $h_from = explode("T", $GetRoomInventoryBalanceArr->PeriodFrom)[1];
        $h_to = explode("T", $GetRoomInventoryBalanceArr->PeriodTo)[1];
        $data = htmlentities($_REQUEST["data"]);
        $result = "";
        $dates .= '<tr class="dates">';
        // var_dump($GetRoomInventoryBalanceArr);
        // var_dump($res2->return);
        if (isset($res2->return->RoomTypeDailyAvailabilityAndPricesRow)) {
            $RoomTypeDailyAvailabilityAndPricesRows = array();
            if(!is_array($res2->return->RoomTypeDailyAvailabilityAndPricesRow))
                $RoomTypeDailyAvailabilityAndPricesRows[] = $res2->return->RoomTypeDailyAvailabilityAndPricesRow;
            else $RoomTypeDailyAvailabilityAndPricesRows = $res2->return->RoomTypeDailyAvailabilityAndPricesRow;
            // var_dump($RoomTypeDailyAvailabilityAndPricesRows);
            $values_arr = array();
            foreach($RoomTypeDailyAvailabilityAndPricesRows as $k => $v) {
                $dates .= '<td data-date="'.strtotime($v->Period).'" ';
                if (!empty($roomQuota)) {
                    if((int)$v->RoomsRemains) {
                        $dates .= 'class="av"';
                    }
                } else {
                    if((int)$v->RoomsVacant) {
                        $dates .= 'class="av"';
                    }
                }
                $dates .= '>';
                $dates .= date('d.m.Y',strtotime($v->Period));
                $dates .= '</td>';
                if(!$k) {
                    $first_date = date('d.m.Y',strtotime($v->Period));
                }

                $rooms_count = 0;
                if (!empty($roomQuota)) {
                    $rooms_count = (int)$v->RoomsRemains;
                } else {
                    $rooms_count = (int)$v->RoomsVacant;
                }

                $classes = "";
                if(!$rooms_count) {
                    $classes .= 'no_av';
                } else {
                    $classes .= 'av';
                }
                if(strtotime($v->Period) >= strtotime($GetRoomInventoryBalanceArr->RealPeriodFrom) && strtotime($v->Period) < strtotime($GetRoomInventoryBalanceArr->RealPeriodTo)) {
                    $classes .= ' real_period';
                }
                if(isset($v->RoomTypeDailyPrices)){
                    $RoomRateDailyPriceRow = $v->RoomTypeDailyPrices->RoomRateDailyPriceRow;
                    $RoomRateDailyPriceRows = array();
                    if(!is_array($RoomRateDailyPriceRow))
                        $RoomRateDailyPriceRows[] = $RoomRateDailyPriceRow;
                    else $RoomRateDailyPriceRows = $RoomRateDailyPriceRow;

                    foreach($RoomRateDailyPriceRows as $key=>$arr){
                        $price = (int)$arr->Price;
                        if (!isset($values_arr[$arr->RoomRateCode])) {
                            $values_arr[$arr->RoomRateCode] = array();
                        }
                        $values_arr[$arr->RoomRateCode]["RoomRate"] = $arr->RoomRate;
                        $values_arr[$arr->RoomRateCode]["values"][] = array(
                            "Period" => date('d.m.Y',strtotime($v->Period)),
                            "Classes" => $classes,
                            "Price" => $price
                        );
                    }
                }
            }
            $dates .= '</tr>';


            $result = "";
            foreach ($values_arr as $rr_key => $rr) {
                $rate = $rr["RoomRate"];
                $prices = '<tr class="prices">';
                $index = 0;
                foreach($RoomTypeDailyAvailabilityAndPricesRows as $k => $v){
                    if (date('d.m.Y',strtotime($v->Period)) == $rr["values"][$index]["Period"]) {
                        $prices .= '<td data-date="'.strtotime($v->Period).'" class="'.$rr["values"][$index]["Classes"].'">';
                        if(isset($rr["values"][$index]["Price"]) && $rr["values"][$index]["Price"] > 0){
                            $prices .= $rr["values"][$index]["Price"].'<br/>';
                        } else {
                            $prices .= '<div style="min-height:15px;line-height:15px;">'.$is_av_text.'</div>';
                        }
                        $prices .= '</td>';
                        $index++;
                    } else {
                        $prices .= '<td class="no_av"><div style="min-height:15px;"></div></td>';
                    }
                }
                $prices .= '</tr>';

                $result .= <<<EOT
        <div class="availibility_block rate_info" style="display: block;">
            <h2>$rate</h2>
            <form>
                <input type="hidden" name="rtc" value="$GetRoomInventoryBalanceArr->RoomType">
                <input type="hidden" name="hid" value="$GetRoomInventoryBalanceArr->Hotel">
                <input type="hidden" name="htf" value="$h_from">
                <input type="hidden" name="ht" value="$h_to">
                <input type="hidden" name="cur_rate" value="$rr_key">
                <input type="hidden" name="data" value="$data">
            </form>

            <div class="h">$title_text</div>
            <table id="room_sold"> {$dates} {$prices}</table>
            <a href="#" class="later" onclick="event.preventDefault();later_click($(this));return false;">$later_text</a>
            <a href="#" class="earlier" onclick="event.preventDefault();earlier_click($(this));return false;">$earlier_text</a>
        </div>
EOT;
            }


        } else {
            $p_from = explode("T", $GetRoomInventoryBalanceArr->PeriodFrom)[0];
            $p_to = explode("T", $GetRoomInventoryBalanceArr->PeriodTo)[0];

            $dates = '';
            $values = '';
            if (!$is_earlier && !$is_later) {
                $dates .= '<tr class="dates">';
                $values .= '<tr class="prices">';
            }
            while (strtotime($p_from) <= strtotime($p_to)) {
                $dates .= '<td data-date="'.strtotime($p_from).'">';
                $dates .= date('d.m.Y',strtotime($p_from));
                $dates .= '</td>';

                $values .= '<td data-date="'.strtotime($p_from).'" class="no_av ';
                if(strtotime($p_from) >= strtotime($GetRoomInventoryBalanceArr->RealPeriodFrom) && strtotime($p_from) < strtotime($GetRoomInventoryBalanceArr->RealPeriodTo)) {
                    $values .= 'real_period';
                }
                $values .= '">';
                $values .= '<div style="min-height:15px;"></div>';
                $values .= '</td>';
                $p_from = date ("Y-m-d", strtotime("+1 day", strtotime($p_from)));
            }
            if (!$is_earlier && !$is_later) {
                $dates .= '</tr>';
                $values .= '</tr>';
            }

            if (!$is_earlier && !$is_later) {
                $d = $dates . $values;
                $result .= <<<EOT
                <div class="availibility_block" style="display: block;">
                    <form>
                        <input type="hidden" name="rtc" value="$GetRoomInventoryBalanceArr->RoomType">
                        <input type="hidden" name="hid" value="$GetRoomInventoryBalanceArr->Hotel">
                        <input type="hidden" name="htf" value="$h_from">
                        <input type="hidden" name="ht" value="$h_to">
                        <input type="hidden" name="cur_rate" value="$rr_key">
                        <input type="hidden" name="data" value="$data">
                    </form>
                    <div class="h">$title_text</div>
                    <table id="room_sold">$d</table>
                    <a href="#" class="later" onclick="event.preventDefault();later_click($(this));return false;">$later_text</a>
                    <a href="#" class="earlier" onclick="event.preventDefault();earlier_click($(this));return false;">$earlier_text</a>
                </div>
EOT;
            }
        }
    }
}
if (!$is_earlier && !$is_later) {
    echo $result;
} else {
    echo json_encode(array("dates" => $dates, "values" => $values));
}


function writeError($error, $data) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//    include_once "bitrix/modules/gotech.onlinebooking/functions/onlinebookingclass.php";
//    @include_once "bitrix/modules/gotech.hotelonline/functions/onlinebookingclass.php";
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/gotech.hotelonline/functions/onlinebookingclass.php");
    $strRes = "";
    foreach($data as $key=>$val)
    {
        if(is_array($val) || is_object($val)){
            $strRes .= $key." = [ ";
            foreach($val as $inkey=>$inval)
            {
                if(is_array($inval) || is_object($inval)){
                    $strRes .= $key." = [ ";
                    foreach($inval as $inkey2=>$inval2)
                    {
                        $strRes .= urldecode(strval($inval2))."; ";
                    }
                    $strRes .= " ] ";
                }else{
                    $strRes .= urldecode(strval($inval))."; ";
                }
            }
            $strRes .= " ] ";
        }else{
            $strRes .= $key."=".urldecode(strval($val))." | ";
        }
    }

    $arFields = array(
        "event" => "Getting available rooms with daily prices"
    ,"data" => $strRes
    ,"error_text" => $error
    );
    $ID = OnlineBookingSupport::db_add('ob_gotech_errors', $arFields);

    CEventLog::Add(array(
        "SEVERITY" => "SECURITY",
        "AUDIT_TYPE_ID" => "AVAILABLE_PRICES",
        "MODULE_ID" => "main",
        "ITEM_ID" => "find_component",
        "DESCRIPTION" => $strRes." ERROR TEXT: ".$error,
    ));
}
?>
