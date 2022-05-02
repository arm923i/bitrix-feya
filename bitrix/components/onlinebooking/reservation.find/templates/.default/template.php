<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? __IncludeLang($_SERVER["DOCUMENT_ROOT"] . $this->__folder . "/lang/" . $arResult["language"] . "/template.php"); ?>
<? $currensy = array("RUB", "USD", "EURO", "KGS"); ?>
<? define("start_time_template", microtime(true)); ?>
<? $ar_group = $USER->GetUserGroup($USER->GetID()); ?>
<?
if (!function_exists('plural_form')) {
    function plural_form($number, $after)
    {
        $cases = array(2, 0, 1, 1, 1, 2);
        echo $number . ' ' . $after[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }
}
function plural_form_c($number, $children, $after)
{
    $cases = array(2, 0, 1, 1, 1, 2);
    if ($children) {
        echo $number . '+' . $children . ' ' . $after[($children % 100 > 4 && $children % 100 < 20) ? 2 : $cases[min($children % 10, 5)]];
    } else {
        echo $number . ' ' . $after[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }
}

if ($arResult['HOURS_ENABLE']) {
    $period_from = $_REQUEST["PeriodFrom"] ? htmlspecialchars($_REQUEST["PeriodFrom"]) : $_SESSION['PeriodFrom'];
    $time_from = $_REQUEST["TimeFrom"] ? htmlspecialchars($_REQUEST["TimeFrom"]) : $_SESSION['TimeFrom'];
    $hour = $_REQUEST["hour"] ? htmlspecialchars($_REQUEST["hour"]) : $_SESSION['hour'];

    $period_to_time = date('d.m.Y, H:i', strtotime($period_from . ' ' . $time_from) + $hour * 60 * 60);

    $period_to = $period_from;
} else {
    $period_from = $_REQUEST["PeriodFrom"] ? htmlspecialchars($_REQUEST["PeriodFrom"]) : $_SESSION['PeriodFrom'];
    $period_to = $_REQUEST["PeriodTo"] ? htmlspecialchars($_REQUEST["PeriodTo"]) : $_SESSION['PeriodTo'];
}
$adults = $_REQUEST['adults'] ? htmlspecialchars($_REQUEST['adults']) : $_SESSION['adults'];
$children = $_REQUEST['children'] ? htmlspecialchars($_REQUEST['children']) : $_SESSION['children'];
$visitors = $adults + $children;

$booked = array();
$booked2 = array();
if (count($_SESSION['NUMBERS_BOOKING'][$_SESSION['HOTEL_ID']]["NUMBERS"])):
    $last = 0;
    foreach ($_SESSION['NUMBERS_BOOKING'][$_SESSION['HOTEL_ID']]["NUMBERS"] as $k => $v):
        if ($v["RoomCode"]) {
            $booked[$k] = $v['RoomTypeCode'] . '_' . $v["RoomCode"] . "_" . $v['RoomRateCode'] . '_' . $v['PeriodFrom'] . '_' . $v['PeriodTo'] . '_' . $v['visitors'];
        } else {
            $booked[$k] = $v['RoomTypeCode'] . '_' . $v['RoomRateCode'] . '_' . $v['PeriodFrom'] . '_' . $v['PeriodTo'] . '_' . $v['visitors'];
        }
        $booked2[$v['RoomTypeCode'] . '_' . $v['RoomRateCode'] . '_' . $v['PeriodFrom'] . '_' . $v['PeriodTo'] . '_' . $v['visitors']]++;
        $last = $k;
    endforeach;

endif;

$in_cart_num = $last ?: 0;

?>

<input type="hidden" name="SessionID" value="<?=$_REQUEST['SessionID']?>">
<input type="hidden" name="UserID" value="<?=$_REQUEST['UserID']?>">
<input type="hidden" name="utm_source" value="<?=$_REQUEST['utm_source']?>">
<input type="hidden" name="utm_medium" value="<?=$_REQUEST['utm_medium']?>">
<input type="hidden" name="utm_campaign" value="<?=$_REQUEST['utm_campaign']?>">

<input type="hidden" name="PeriodFrom_f">
<input type="hidden" name="PeriodTo_f">
<input type="hidden" name="email_f">
<input type="hidden" name="phone_f">
<input type="hidden" name="promo_code_f">
<input type="hidden" name="children_f">
<input type="hidden" name="adults_f">
<input type="hidden" name="wsdl" value="<?= $arResult['WSDL'] ?>">


<? if (!empty($arResult["AvailableRooms"]) && !empty($_REQUEST) || !empty($arResult["AvailableRoomsByCheckInPeriods"]) || !empty($arResult["OtherPeriods"])): ?>

    <div id="gotech_search_data">
        <div class="h">
            <?= GetMessage('ROOM_SEARCH') ?>
        </div>
        <div class="search_params_blocks">
            <? if ($arResult['HOURS_ENABLE']): ?>
                <div class="search_params_block">
                    <span><?= GetMessage('IN') ?>:</span> <b><?= $period_from ?>, <?= $time_from ?></b><span
                        class="razd">|</span>
                </div>
                <div class="search_params_block">
                    <span><?= GetMessage('OUT') ?>:</span> <b><?= $period_to_time ?></b><span class="razd">|</span>
                </div>
            <? else: ?>
                <? if (!empty($arResult['depart_city_label'])): ?>
                    <div class="search_params_block">
                        <span><?= GetMessage('DEPART_CITY') ?>:</span> <b><?= $arResult['depart_city_label'] ?></b>
                    </div>
                    <br>
                <? endif; ?>
                <div class="search_params_block">
                    <span><?= GetMessage('IN') ?>:</span>
                    <b><?= $_REQUEST["PeriodFrom"] ? $_REQUEST["PeriodFrom"] : $_SESSION['PeriodFrom'] ?>
                        , <?= $arResult['HOTEL_TIME_FROM'] ?></b><span class="razd">|</span>
                </div>
                <div class="search_params_block">
                    <span><?= GetMessage('OUT') ?>:</span>
                    <b><?= $_REQUEST["PeriodTo"] ? $_REQUEST["PeriodTo"] : $_SESSION['PeriodTo'] ?>
                        , <?= $arResult['HOTEL_TIME'] ?></b><span class="razd">|</span>
                </div>
            <? endif; ?>
            <div class="search_params_block">
                <? for ($i = 0; $i < $_REQUEST['adults']; $i++) { ?>
                    <span class="search_adult_icon"></span>
                <? } ?>
                <? for ($i = 0; $i < $_REQUEST['children']; $i++) { ?>
                    <span class="search_child_icon"></span>
                <? } ?>
                &nbsp;
                <? if ($arResult['HOURS_ENABLE']): ?>
                    <?plural_form($hour, array(GetMessage('1HOUR'), GetMessage('2HOURS'), GetMessage('5HOURS'))) ?>
                <? else: ?>
                    <?plural_form($_REQUEST['night'], array(GetMessage('1NIGHTS'), GetMessage('2NIGHTS'), GetMessage('5NIGHTS'))) ?>
                <? endif; ?>
            </div>
        </div>
        <a href="#" class="chage_search_data"><?= GetMessage('CHANGE_ORDER') ?></a>
    </div>


    <? if (!empty($arResult["AvailableRooms"]) || !empty($arResult["AvailableRoomsByCheckInPeriods"])): ?>
        <?if (!empty($arResult["AvailableRoomsByCheckInPeriods"])) $arResult["AvailableRooms"] = $arResult["AvailableRoomsByCheckInPeriods"];?>
        <div id="gotech_search_result_header"></div>
        <? if (!empty($arResult["AvailableRoomsByCheckInPeriods"])): ?>
            <?
            $byperiods = true;
            ?>
            <? if (!$arResult["RequestedPeriodFounded"]): ?>
                <div class="period_not_founded_error_message"><?= GetMessage("PERIOD_NOT_FOUNDED") ?></div>
            <? endif; ?>
            <? if (count($arResult["Periods"]) > 1): ?>
                <div id="gotech_search_result_header_periods">
                    <?
                    $isFirst = true;
                    ?>
                    <? foreach ($arResult["Periods"] as $periodKey => $periodItem): ?>
                        <span class="<? if (!$arResult["RequestedPeriodFounded"] && $isFirst): ?>gotech_search_result_header_period_item_selected<? elseif ($_REQUEST["PeriodFrom"] . " - " . $_REQUEST["PeriodTo"] == $periodItem["PeriodFromTo"]): ?>gotech_search_result_header_period_item_selected<? else: ?>gotech_search_result_header_period_item<? endif; ?>">
                            <?
                            $checkInPeriodValue = str_replace(array(" ", "-", "."), "", $periodItem["PeriodFromTo"]);
                            if (!$arResult["RequestedPeriodFounded"] && $isFirst || ($_REQUEST["PeriodFrom"] . " - " . $_REQUEST["PeriodTo"] == $periodItem["PeriodFromTo"]))
                                $first_period = $checkInPeriodValue;
                            ?>
                            <input type="hidden" name="period" value="<?= $checkInPeriodValue ?>">
                            <input type="hidden" name="intPeriod" value="&nbsp;<?= GetMessage("from") ?> <?= $periodItem["intPeriodFrom"] ?> <?= GetMessage("to") ?> <?= $periodItem["intPeriodTo"] ?>">
                            <input type="hidden" name="costFor" value="<?= GetMessage("COST_FOR") ?>">
                            <input type="hidden" name="cost" value="<?= GetMessage("COST") ?>">
                            <? if (!$arResult["RequestedPeriodFounded"] && $isFirst || $_REQUEST["PeriodFrom"] . " - " . $_REQUEST["PeriodTo"] == $periodItem["PeriodFromTo"]): ?>
                                <? $intPeriodText = "&nbsp; " . GetMessage('from') . " " . $periodItem['intPeriodFrom'] . " " . GetMessage('to') . " " . $periodItem['intPeriodTo'] ?>
                            <? endif; ?>
                            <span class="gotech_search_result_header_period_item_dates">
                                <?
                                $exPeriodFrom = explode(".", $periodItem["PeriodFrom"]);
                                $exPeriodTo = explode(".", $periodItem["PeriodTo"]);
                                $resultText = $periodItem["PeriodFromTo"];
                                if ($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] == $exPeriodTo[1]) {
                                    $resultText = $exPeriodFrom[0] . " - " . $exPeriodTo[0] . " " . getMonthNameById($exPeriodTo[1]);
                                } elseif ($exPeriodFrom[2] == $exPeriodTo[2] && $exPeriodFrom[1] != $exPeriodTo[1]) {
                                    $resultText = $exPeriodFrom[0] . " " . getMonthNameById($exPeriodFrom[1]) . " - " . $exPeriodTo[0] . " " . getMonthNameById($exPeriodTo[1]);
                                } elseif ($exPeriodFrom[2] != $exPeriodTo[2]) {
                                    $resultText = $exPeriodFrom[0] . " " . getMonthNameById($exPeriodFrom[1]) . " - " . $exPeriodTo[0] . " " . getMonthNameById($exPeriodTo[1]);
                                }
                                ?>
                                <?= $resultText ?>
                            </span>
                            <span class="gotech_search_result_header_period_item_night">
                                <?
                                $nights = (int)$periodItem["nights"];
                                if ($nights == 1) {
                                    $text = GetMessage("1NIGHTS");
                                } elseif ($nights > 1 && $nights < 5) {
                                    $text = GetMessage("2NIGHTS");
                                } elseif ($nights >= 5 && $nights < 21) {
                                    $text = GetMessage("5NIGHTS");
                                } else {
                                    $text = GetMessage("1NIGHTS");
                                }
                                ?>
                                <?= "" . $periodItem["nights"] . " " . $text ?>
                                <? if (!$arResult["RequestedPeriodFounded"] && $isFirst || $_REQUEST["PeriodFrom"] . " - " . $_REQUEST["PeriodTo"] == $periodItem["PeriodFromTo"]): ?>
                                    <? $periodNights = "" . $periodItem["nights"] . " " . $text ?>
                                <? endif; ?>
                                <input type="hidden" name="nights" value="<?= "" . $periodItem["nights"] . " " . $text ?>">
                            </span>
                            <span class="gotech_search_result_header_period_item_price">
                                <?= GetMessage("fromPrice") . " " . OnlineBookingSupport::format_price($periodItem["minPrice"][$arResult["CURRENCY_NAME"]], $arResult["CURRENCY_NAME"])?>
                                <input type="hidden" name="period_RUB_currency"
                                       value="<?= GetMessage("fromPrice") . " " . number_format($periodItem["minPrice"]["RUB"], 0, ',', ' ') . " <span class='gotech_ruble'>a</span>" ?>"/>
                                <input type="hidden" name="period_USD_currency"
                                       value="<?= GetMessage("fromPrice") . " $ " . number_format($periodItem["minPrice"]["USD"], 0, ',', ' ') ?>"/>
                                <input type="hidden" name="period_EUR_currency"
                                       value="<?= GetMessage("fromPrice") . " &euro; " . number_format($periodItem["minPrice"]["EURO"], 0, ',', ' ') ?>"/>
                                <input type="hidden" name="period_KGS_currency"
                                       value="<?= GetMessage("fromPrice") . " " . number_format($periodItem["minPrice"]["KGS"], 0, ',', ' ') . " KGS" ?>"/>
                            </span>
					    </span>
                        <? $isFirst = false ?>
                    <? endforeach; ?>
                </div>
            <? elseif (count($arResult["Periods"]) == 1): ?>
                <? $intPeriodText = "<br/>&nbsp; " . GetMessage('from') . " " . $arResult["Periods"][0]['intPeriodFrom'] . " " . GetMessage('to') . " " . $arResult["Periods"][0]['intPeriodTo'] ?>
                <?
                    $nights = (int)$arResult["Periods"][0]["nights"];
                    if ($nights == 1) {
                        $text = GetMessage("1NIGHTS");
                    } elseif ($nights > 1 && $nights < 5) {
                        $text = GetMessage("2NIGHTS");
                    } elseif ($nights >= 5 && $nights < 21) {
                        $text = GetMessage("5NIGHTS");
                    } else {
                        $text = GetMessage("1NIGHTS");
                    }
                    $checkInPeriodValue = str_replace(array(" ", "-", "."), "", $arResult["Periods"][0]["PeriodFromTo"]);
                    $first_period = $checkInPeriodValue;
                ?>
                <? $periodNights = "" . $arResult["Periods"][0]["nights"] . " " . $text ?>
            <? endif; ?>
            <? if ($intPeriodText): ?>
                <div id="gotech_search_result_header_text" class="gotech_middle_text">
                    <?= GetMessage("AVAILABLE") ?>
                    <span class="gotech_additional_rooms"><?= $intPeriodText ?></span>
                </div>
            <? else: ?>
                <div id="gotech_search_result_header_text" class="gotech_middle_text"><?= GetMessage("AVAILABLE") ?></div>
            <? endif; ?>
        <? endif; ?>

        <? $htmlDetailInformation = ""; ?>
        <? $roomTypeCodeArray = array(); ?>
        <div id="search_result_items">
            <? foreach ($arResult["AvailableRooms"] as $key => $room): ?>
                <? $strRoomType = str_replace("+", "x", $room["RoomTypeCode"]) ?>
                <? $strRoomType = str_replace(" ", "-", $strRoomType) ?>
                <? $strRoomType = str_replace(".", "-", $strRoomType) ?>
                <?
                $RTId = $room["Id"];
                $searchResult = array_filter($arResult["Photos"], function ($array) use ($RTId) {
                    return $array["RoomId"] == $RTId;
                });

                $num_photos = count($searchResult);
                if ($room["Picture"]["src"])
                    $num_photos++;

                if ($byperiods)
                    $checkInPeriodValue = str_replace(array(" ", "-", "."), "", $room["PeriodFromTo"]);
                ?>

                <div
                    class="gotech_search_result_room<? if ($byperiods): ?> <?= $checkInPeriodValue ?><? endif; ?>"<? if ($byperiods && $first_period != $checkInPeriodValue && $room['RoomsAvailable']): ?> style="display:none;"<? endif; ?>>

                    <div class="gotech_search_result_room_main_picture">
                        <? $roomTypeCodeArray = array() ?>
                        <div class="main_adaptive_img">
                            <? if (!empty($room["Picture"]["src"])): ?>
                                <img src="<?= $room["Picture"]["src"] ?>" width="100%" <? if ($arResult["language"] == "ru") echo 'alt="' . $room["Name"] . '" title="' . $room["Name"] . '"'; else echo 'alt="' . $room["Name_en"] . '" title="' . $room["Name_en"] . '"'; ?>>
                            <? endif; ?>
                            <? if (array_search($room["RoomTypeCode"], $roomTypeCodeArray) === false): ?>
                                <?
                                $roomTypeCodeArray[] = $room["RoomTypeCode"];
                                ?>

                                <? if (count($searchResult) > 0): ?>
                                    <? $ind = 0 ?>
                                    <? foreach ($searchResult as $in_key => $photo): ?>
                                        <? if ($ind > 3 && 0): ?>
                                            <? break; ?>
                                        <? endif; ?>
                                        <img data-src="<?= $photo["preview_src"] ?>" src="">
                                        <? $ind++ ?>
                                    <? endforeach; ?>
                                <? endif; ?>
                            <? endif; ?>
                        </div>
                        <div class="foto_count_info"><?plural_form($num_photos, array(GetMessage('1PHOTO'), GetMessage('2PHOTO'), GetMessage('5PHOTO'))); ?></div>
                    </div>

                    <? $roomTypeCodeArray = array(); ?>

                    <div class="gotech_search_result_room_additional_pictures"<? if ($room['RoomsAvailable']) { ?> style="margin-top:90px;"<? } ?>>
                        <? if (!empty($room["Picture"]["src"])): ?>
                            <a href="#" class="active">
                                <img src="<?= $room["Picture"]["src"] ?>" <? if ($arResult["language"] == "ru") echo 'alt="' . $room["Name"] . '" title="' . $room["Name"] . '"'; else echo 'alt="' . $room["Name_en"] . '" title="' . $room["Name_en"] . '"'; ?>>
                            </a>
                        <? endif; ?>
                        <? if (array_search($room["RoomTypeCode"], $roomTypeCodeArray) === false): ?>
                            <?
                            $roomTypeCodeArray[] = $room["RoomTypeCode"];
                            ?>

                            <? if (count($searchResult) > 0): ?>
                                <? $ind = 0 ?>
                                <? foreach ($searchResult as $in_key => $photo): ?>
                                    <? if ($ind > 3 && 0): ?>
                                        <? break; ?>
                                    <? endif; ?>
                                    <a href="#">
                                        <img src="<?= $photo["preview_src"] ?>" data-big="<?= $photo["detail_src"] ?>">
                                    </a>
                                    <? $ind++ ?>
                                <? endforeach; ?>
                            <? endif; ?>
                        <? endif; ?>
                    </div>

                    <div class="gotech_search_result_room_info">
                        <div class="room_name"><?= $arResult["language"] == "ru" ? $room['Name'] : $room['Name_en'] ?></div>
                        <input type="hidden" name="gotech_search_result_room_link" value="<?= $room["Link"] ?>">

                        <div class="gotech_search_result_room_main_picture">
                            <? $roomTypeCodeArray = array() ?>
                            <div class="main_adaptive_img">
                                <? if (!empty($room["Picture"]["src"])): ?>
                                    <img src="<?= $room["Picture"]["src"] ?>" width="100%" <? if ($arResult["language"] == "ru") echo 'alt="' . $room["Name"] . '" title="' . $room["Name"] . '"'; else echo 'alt="' . $room["Name_en"] . '" title="' . $room["Name_en"] . '"'; ?>>
                                <? endif; ?>
                                <? if (array_search($room["RoomTypeCode"], $roomTypeCodeArray) === false): ?>
                                    <?
                                    $roomTypeCodeArray[] = $room["RoomTypeCode"];
                                    ?>

                                    <? if (count($searchResult) > 0): ?>
                                        <? $ind = 0 ?>
                                        <? foreach ($searchResult as $in_key => $photo): ?>
                                            <? if ($ind > 3 && 0): ?>
                                                <? break; ?>
                                            <? endif; ?>
                                            <img data-src="<?= $photo["detail_src"] ?>" src="">
                                            <? $ind++ ?>
                                        <? endforeach; ?>
                                    <? endif; ?>
                                <? endif; ?>
                            </div>
                            <div class="foto_count_info"><?plural_form($num_photos, array(GetMessage('1PHOTO'), GetMessage('2PHOTO'), GetMessage('5PHOTO'))); ?></div>
                        </div>

                        <div class="row">
                            <? if ($room['LimitedRoomsText']): ?>
                                <div class="bordered_info available_rooms_count" style="margin-right:5px;">
                                    <?= $room['LimitedRoomsText'] ?>
                                </div>
                            <? endif; ?>
                            <? if ($room['LastReservationDate']): ?>
                                <?
                                    $hours_back = false;
                                    $skip = false;
                                    $t1 = strtotime(str_replace('T', ' ', $room['LastReservationDate']));
                                    $now = time();

                                    $diff = $now - $t1;
                                    $diff = round($diff / 60);

                                    if ($diff >= 60) {
                                        $hours_back = true;
                                        $diff = round($diff / 60);

                                        if ($diff >= 24) $skip = true;
                                    }

                                    if (!$diff || $diff < 0) $skip = true;
                                ?>
                                <? if (!$skip): ?>
                                    <div class="bordered_info was_ordered_info">
                                        <span>!</span> <?= GetMessage('ROOM_WAS_BOOKED') ?>
                                        <b>
                                            <? if ($hours_back): ?>
                                                <?plural_form($diff, array(GetMessage('1HOUR'), GetMessage('2HOURS'), GetMessage('5HOURS'))) ?>
                                            <? else: ?>
                                                <?plural_form($diff, array(GetMessage('1MINUTES'), GetMessage('2MINUTES'), GetMessage('5MINUTES'))) ?>
                                            <? endif; ?>
                                            <?= GetMessage('AGO') ?>
                                        </b>
                                    </div>
                                <? endif; ?>
                            <? endif; ?>

                            <div class="room_detail_data">
                                <? $roomTypeCodeArray = array() ?>
                                <div class="main_img">
                                    <? if (!empty($room["Picture"]["src"])): ?>
                                        <img src="<?= $room["Picture"]["src"] ?>" width="100%" <? if ($arResult["language"] == "ru") echo 'alt="' . $room["Name"] . '" title="' . $room["Name"] . '"'; else echo 'alt="' . $room["Name_en"] . '" title="' . $room["Name_en"] . '"'; ?>>
                                    <? endif; ?>
                                    <? if (array_search($room["RoomTypeCode"], $roomTypeCodeArray) === false): ?>
                                        <?
                                        $roomTypeCodeArray[] = $room["RoomTypeCode"];
                                        ?>

                                        <? if (count($searchResult) > 0): ?>
                                            <? $ind = 0 ?>
                                            <? foreach ($searchResult as $in_key => $photo): ?>
                                                <? if ($ind > 3 && 0): ?>
                                                    <? break; ?>
                                                <? endif; ?>
                                                <img data-src="<?= $photo["detail_src"] ?>" src="">
                                                <? $ind++ ?>
                                            <? endforeach; ?>
                                        <? endif; ?>
                                    <? endif; ?>
                                </div>
                                <div class="description">
                                    <?= $room["information_text"] ?>
                                </div>

                            </div>

                            <a href="#" class="all_info" data-opened_text="<?= GetMessage('ROOM_DETAIL_INFO_HIDE') ?>"><?= GetMessage('ROOM_DETAIL_INFO') ?></a>

                        </div>

                        <? if (isset($room["RoomRates"])): ?>
                            <div class="order_block" style="width:100%;">
                                <? //if (count($room['Rooms']) > 0): ?>

                                <? //else: ?>
                                    <? if ($arResult['HOURS_ENABLE']): ?>
                                        <p class="common_order_info">
                                            <?if($_REQUEST['embeded'] == 'Y'):?>
                                              <?= GetMessage('NEW_COST_FOR') ?>
                                            <?else:?>
                                              <?= GetMessage('COST_FOR') ?>
                                            <?endif;?>
                                            <span>
                                                <b>
                                                    <span class="text_hour"><?plural_form($hour, array(GetMessage('1HOUR'), GetMessage('2HOURS'), GetMessage('5HOURS'))) ?></span>,
									                <span class="text_adults"><?plural_form_c($adults, $children, array(GetMessage('1GUEST'), GetMessage('2GUESTS'), GetMessage('5GUESTS'))) ?></span>,
									                <span class="text_from_to"><?= $period_from ?> <?= $time_from ?></span>
                                                </b>
                                            </span>
                                            <a class="divider">|</a>
                                            <? if (count($room['Rooms']) == 0 && !$byperiods): ?>
                                                <a href="#" class="change_order"><?= GetMessage('CHANGE_ORDER') ?></a>
                                            <? endif; ?>
                                        </p>
                                    <? else: ?>
                                        <p class="common_order_info">
                                            <? if ($byperiods): ?>
                                                <?if($_REQUEST['embeded'] == 'Y'):?>
                                                  <?= GetMessage('NEW_COST_FOR') ?>
                                                <?else:?>
                                                  <?= GetMessage('COST_FOR') ?>
                                                <?endif;?>
                                                <span>
                                                    <b>
                                                        <span class="text_night"><?plural_form($room['Duration'], array(GetMessage('1NIGHTS'), GetMessage('2NIGHTS'), GetMessage('5NIGHTS'))) ?></span>,
										                <span class="text_adults"><?plural_form_c($adults, $children, array(GetMessage('1GUEST'), GetMessage('2GUESTS'), GetMessage('5GUESTS'))) ?></span>,
										                <span class="text_from_to"><?= $room['CheckInDate'] ?> - <?= $room['CheckOutDate'] ?></span>
                                                    </b>
                                                </span>
                                                <a class="vline">|</a>
                                            <? else: ?>
                                                <?if($_REQUEST['embeded'] == 'Y'):?>
                                                  <?= GetMessage('NEW_COST_FOR') ?>
                                                <?else:?>
                                                  <?= GetMessage('COST_FOR') ?>
                                                <?endif;?>
                                                <span>
                                                    <b>
                                                        <span class="text_night"><?plural_form($_REQUEST['night'], array(GetMessage('1NIGHTS'), GetMessage('2NIGHTS'), GetMessage('5NIGHTS'))) ?></span>,
										                <span class="text_adults"><?plural_form_c($adults, $children, array(GetMessage('1GUEST'), GetMessage('2GUESTS'), GetMessage('5GUESTS'))) ?></span>,
										                <span class="text_from_to"><?= $period_from ?> - <?= $period_to ?></span>
                                                    </b>
                                                </span>
                                                <a class="vline">|</a>
                                            <? endif; ?>

                                            <? if (count($room['Rooms']) == 0 && !$byperiods): ?>
                                                <a href="#" class="change_order"><?= GetMessage('CHANGE_ORDER') ?></a>
                                            <? endif; ?>
                                        </p>
                                    <? endif; ?>
                                <? //endif; ?>

                                <div class="change_search_params_block" style="display:none;margin-bottom:30px;">
                                    <form>
                                        <? if ($arResult['HOURS_ENABLE']): ?>
                                            <input type="hidden" name="hotel_id_" value="<?= $_REQUEST['hotel_id'] ? htmlspecialchars($_REQUEST['hotel_id']) : $_SESSION['hotel_id'] ?>"/>
                                            <input type="hidden" name="room_type_code" value="<?= $room["RoomTypeCode"] ?>"/>

                                            <div class="time param_block" style="position:relative;top:2px;">
                                                <label for="gotech_search_window_dates_from_input" class="pblock_label active">
                                                    <span><?= GetMessage("PERIOD_FROM") ?></span>
                                                </label>
                                                <span class="gotech_periods_container" style="position:relative;">
                                                    <input id="gotech_search_window_dates_from_input" class="datepicker_input" readonly="readonly"/>
                                                    <input name="PeriodFrom" class="period_from" <? if (!empty($_REQUEST["PeriodFrom"])): ?> value="<?= htmlspecialcharsEx($_REQUEST["PeriodFrom"]) ?>" <? endif; ?> readonly/>
									            </span>
                                            </div>
                                            <div class="time param_block">
                                                <label for="gotech_search_window_dates_to_input" class="pblock_label active">
                                                    <span><?= GetMessage("PERIOD_TIME") ?></span>
                                                </label>
                                                <span class="gotech_periods_container" style="position:relative;">
                                                    <select data-time="<?= htmlspecialchars($_REQUEST['TimeFrom']) ?>" name="TimeFrom"></select>
                                                    <div id="templateFolder" style="font-size: 16px;padding: 7px; border-radius: 3px; display: none;border " class="hidden"><?= $templateFolder ?></div>
									            </span>
                                            </div>
                                            <div class="param_block spinner_block num_param_block hour_block">
                                                <label class="pblock_label active">
                                                    <span><?= GetMessage("HOURS") ?></span>
                                                </label>
                                                <span class="spinner_container" style="width:93px;">
                                                    <div class="spinner_prev time" onselectstart="return false" onmousedown="return false"></div>
                                                    <span class="number_field" style="width:60px;"><?= htmlspecialcharsEx($_REQUEST["hour"]) ?></span>
                                                    <div class="spinner_next time" onselectstart="return false" onmousedown="return false"></div>
                                                </span>
                                            </div>
                                            <input name="hour" type="hidden" value="<? if (!empty($_REQUEST["hour"])): ?><?= htmlspecialcharsEx($_REQUEST["hour"]) ?><? else: ?><?= ($arResult["HOTEL"]["HOURS_MIN"] ? $arResult["HOTEL"]["HOURS_MIN"] : 4) ?><? endif; ?>"/>
                                            <input type="hidden" name="PeriodTo" id="gotech_search_period_to" <? if (!empty($_REQUEST["PeriodTo"])): ?> value="<?= htmlspecialcharsEx($_REQUEST["PeriodTo"]) ?>" <? endif; ?> readonly/>
                                        <? else: ?>
                                            <input type="hidden" name="hotel_id_" value="<?= $_REQUEST['hotel_id'] ? htmlspecialchars($_REQUEST['hotel_id']) : $_SESSION['hotel_id'] ?>"/>
                                            <input type="hidden" name="room_type_code" value="<?= $room["RoomTypeCode"] ?>"/>
                                            <div class="param_block">
                                                <label class="pblock_label active">
                                                    <span><?= GetMessage("PERIOD_FROM") ?></span>
                                                </label>
                                                <span class="gotech_periods_container" style="position:relative;">
                                                    <input class="datepicker_input" placeholder="<?= GetMessage("PERIOD_FROM") ?>" readonly="readonly"/>
                                                    <input name="PeriodFrom" class="period_from" <? if (!empty($period_from)): ?> value="<?= htmlspecialcharsEx($period_from) ?>" <? endif; ?> readonly/>
                                                </span>
                                            </div>
                                            <div class="param_block">
                                                <label class="pblock_label active">
                                                    <span><?= GetMessage("PERIOD_TO") ?></span>
                                                </label>
                                                <span class="gotech_periods_container" style="position:relative;">
                                                    <input class="datepicker_input" placeholder="<?= GetMessage("PERIOD_TO") ?>" readonly/>
                                                    <input name="PeriodTo" class="period_to" <? if (!empty($period_to)): ?> value="<?= htmlspecialcharsEx($period_to) ?>" <? endif; ?> readonly/>
                                                </span>
                                            </div>
                                            <div class="param_block spinner_block nights_block" style="position:relative;top:-2px;">
                                                <span class="spinner_container" style="width:110px;">
                                                    <label class="pblock_label active"><span><?= GetMessage("NIGHT_QUANTITY") ?></span></label>
                                                    <div class="gotech_search_window_dates_nights_spinner_prev spinner_prev" onselectstart="return false" onmousedown="return false"></div>
                                                    <span class="number_field" style="width:79px;"><?= htmlspecialcharsEx($_REQUEST["night"]) ?></span>
                                                    <div class="gotech_search_window_dates_nights_spinner_next spinner_next" onselectstart="return false" onmousedown="return false"></div>
                                                </span>
                                                <input name="night" type="hidden" <? if (!empty($_REQUEST["night"])): ?> value="<?= htmlspecialcharsEx($_REQUEST["night"]) ?>" <? endif; ?>/>
                                            </div>
                                        <? endif; ?>

                                        <div>
                                            <div class="gotech_search_window_guests_text">
                                                <?= GetMessage('GUESTS_TEXT') ?>
                                            </div>
                                            <? if ($arResult["HOTEL_MAX_ADULT"] > 0 || 1): ?>
                                                <div class="param_block spinner_block adults_block" data-max="<?= $room['MaxAdults'] ?: $arResult["HOTEL_MAX_ADULT"] ?>" style="float:left;">
                                                    <label class="pblock_label active"><span><? if ($arResult["HOTEL"]["HOTEL_MAX_CHILDREN"] > 0): ?><?= GetMessage("ADULTS") ?><? else: ?><?= GetMessage("ADULTS") ?><? endif; ?></span></label>

                                                    <input type="hidden" name="adults" value="<?= (int)$_REQUEST['adults'] ?>">
                                                    <div class="spinner_container">
                                                        <div class="gotech_search_window_guests_spinner_prev spinner_prev" onselectstart="return false" onmousedown="return false"></div>
                                                        <span class="number_field"><?= (int)$_REQUEST['adults'] ?></span>
                                                        <div class="gotech_search_window_guests_spinner_next_active spinner_next" onselectstart="return false" onmousedown="return false"></div>
                                                    </div>
                                                </div>
                                            <? endif; ?>

                                            <? if ($arResult["HOTEL_MAX_CHILDREN"] > 0): ?>
                                                <div class="param_block spinner_block children_block" data-max="<?= $room['MaxChildren'] ?: $arResult["HOTEL_MAX_CHILDREN"] ?>" style="float:left;">

                                                    <label class="pblock_label"><span><?= GetMessage("CHILDREN") ?></span></label>
                                                    <input type="hidden" name="children" value="<?= (int)$_REQUEST['kids'] ?>">
                                                    <div class="spinner_container">
                                                        <div class="gotech_search_window_guests_spinner_prev spinner_prev" onselectstart="return false" onmousedown="return false"></div>
                                                        <span class="number_field"><?= (int)$_REQUEST['children'] ?></span>
                                                        <div class="gotech_search_window_guests_spinner_next_active spinner_next" onselectstart="return false" onmousedown="return false"></div>
                                                    </div>
                                                </div>
                                                <div class="gotech_search_window_guests_ages_block" style="float:left;">
                                                    <? for ($i = 1; $i <= $arResult["HOTEL_MAX_CHILDREN"]; $i++): ?>
                                                        <div class="param_block"<? if ($_REQUEST['shChildrenYear_' . ($i - 1)] == 'true') : ?><? else: ?> style="display:none;"<? endif; ?>>
                                                            <label class="pblock_label active"><span><?= GetMessage("AGE") ?> <?= $i ?> <?= GetMessage("CHILD") ?></span></label>
                                                            <input type="hidden" name="shChildrenYear_<?= ($i - 1) ?>" value="false">
                                                            <select class="gotech_search_window_guests_ages_spinner" name="childrenYear_<?= ($i - 1) ?>">
                                                                <? for ($y = 0; $y <= 17; $y++): ?>
                                                                    <option value="<?= $y ?>"<? if ($_REQUEST['childrenYear_' . ($i - 1)] == $y): ?> selected<? endif ?>><? if ($y === 0): ?><?= " < 1 " ?><? else: ?><?= $y ?><? endif; ?></option>
                                                                <? endfor; ?>
                                                            </select>
                                                        </div>
                                                    <? endfor; ?>
                                                </div>

                                            <? endif; ?>
                                            <div style="clear:both;"></div>
                                            <br/><br/>
                                            <a href="#" class="gotech_button submit_change_order"><?= GetMessage("CHANGE") ?></a>
                                            <div style="clear:both;"></div>
                                        </div>
                                    </form>
                                </div>

                                <? $isFirst = true ?>
                                <div class="room_rates_block">
                                    <? $b_price = []; ?>
                                    <? foreach ($room["RoomRates"] as $RRKey => $RRValue): ?>
                                        <? if (isset($room["MaxPriceRate"]) && $room["MaxPriceRate"] != trim($RRValue) && $room[$RRValue]["RateWrap"]): ?>
                                            <!-- Price -->
                                            <?
                                                $b_price = $room[$RRValue]["Price"];
                                            ?>
                                        <? endif; ?>
                                    <? endforeach; ?>
                                    <? foreach ($room["RoomRates"] as $RRKey => $RRValue): ?>
                                        <? if (count($room["RoomRates"]) > 1 && $room[$RRValue]["RateWrap"]): ?>

                                        <? else: ?>
                                            <? if (isset($room['Rooms']) && count($room['Rooms']) > 0): ?>
                                                <?
                                                    $this_book = $room['RoomTypeCode'] . '_' . $RRValue . '_' . $period_from . '_' . $period_to . '_' . $visitors;
                                                ?>
                                                <? $strRRValue = str_replace("%", "", $RRValue); ?>
                                                <? $strRRValue = str_replace("+", "x", $strRRValue); ?>
                                                <? $strRRValue = str_replace("/", "-", $strRRValue); ?>

                                                <div class="order_item active not_change_class" data-code="<?= $RRValue ?>" data-rate_id="<?= $this_book ?>">
                                                    <!-- Details -->
                                                    <div style="display:none;" class="gotech_search_result_room_rates_item_details_hidden" id="<?= "gotech_room_rate_" . $strRRValue . "_" . $strRoomType ?>-block">
                                                        <? if (strlen($room[$RRValue]["ReservationConditionsOnline"]) > 512): ?>
                                                            <?= substr($room[$RRValue]["ReservationConditionsOnline"], 0, 512) . "..." ?>
                                                        <? elseif (!empty($room[$RRValue]["ReservationConditionsOnline"])): ?>
                                                            <?= $room[$RRValue]["ReservationConditionsOnline"] ?>
                                                        <? endif; ?>
                                                    </div>
                                                    <!-- Price -->
                                                    <?
                                                    $price = "";
                                                    if ($arResult["CURRENCY_NAME"] == "EURO" || $arResult["CURRENCY_NAME"] == "EUR") {
                                                        $price = "&euro; " . $room[$RRValue]["Price"]["EURO"];
                                                    }
                                                    if ($arResult["CURRENCY_NAME"] == "USD") {
                                                        $price = "$ " . $room[$RRValue]["Price"]["USD"];
                                                    }
                                                    if ($arResult["CURRENCY_NAME"] == "RUB") {
                                                        $price = $room[$RRValue]["Price"]["RUB"] . " <span class='gotech_ruble'>a</span>";
                                                    }
                                                    if ($arResult["CURRENCY_NAME"] == "KGS" || $arResult["CURRENCY_NAME"] == "KGS") {
                                                      $price = $room[$RRValue]["Price"]["KGS"] . " KGS";
                                                    }
                                                    if (!$price)
                                                        $price = $room[$RRValue]["Price"]["RUB"] . " <span class='gotech_ruble'>a</span>";
                                                    if ($room["MaxPrice"]["RUB"] <= $room[$RRValue]["Price"]["RUB"] || !$arResult["SHOW_ECONOMY"]) {
                                                        $economy = 0;
                                                    } else {
                                                        $economy = 100 - round($room[$RRValue]["Price"]["RUB"] * 100 / $room["MaxPrice"]["RUB"]);
                                                    }
                                                    $embeded_price_diff = 0;
                                                    if ($_REQUEST['embeded'] == 'Y') {
                                                      $embeded_price_diff = round($room[$RRValue]["Price"]["RUB"] * 100 / $_REQUEST['embeded_price']) - 100;
                                                    }
                                                    ?>
                                                    <div
                                                      class="order_actions when_no_book">
                                                        <!-- Button -->
                                                        <span class="gotech_search_result_room_rates_item_button">
                                                            <span class="show_rooms_button gotech_button" data-id="<?= "order_rooms_" . $strRRValue . "_" . $strRoomType ?>">
                                                                <? if($arResult['CRUISE_MODE']): ?>
                                                                    <?= GetMessage("CHOOSE_CROOM"); ?>
                                                                <? else:?>
                                                                    <?= GetMessage("CHOOSE_ROOM"); ?>
                                                                <? endif;?>
                                                            </span>
                                                        </span>
                                                    </div>

                                                    <div class="price_info">
                                                        <?if($embeded_price_diff):?>
                                                          <div class="discount">
                                                            <del>
                                                              <?= $_REQUEST['embeded_price'] ?> <span class='gotech_ruble'>a</span>
                                                            </del><?if($embeded_price_diff < 0): ?> / <span><?=$embeded_price_diff?>%</span><? endif; ?>
                                                          </div>
                                                        <? elseif ($economy): ?>
                                                            <div class="discount">
                                                                <del>
                                                                    <?= $room["MaxPrice"]["RUB"] ?> <span class='gotech_ruble'>a</span>
                                                                </del> / <span>-<?= $economy ?>%</span>
                                                            </div>
                                                        <? endif; ?>
                                                        <div class="price">
                                                            <?= OnlineBookingSupport::format_price($price, $arResult["CURRENCY_NAME"]) ?>
                                                        </div>
                                                    </div>

                                                    <div class="order_name">
                                                        <? if (isset($room[$RRValue]["ReservationConditionsShort"]) && !empty($room[$RRValue]["ReservationConditionsShort"])): ?>
                                                            <?= $room[$RRValue]["ReservationConditionsShort"] ?>
                                                        <? else: ?>
                                                            <?= GetMessage("RATE") . $room[$RRValue]["RoomRateDescription"] ?>
                                                        <? endif; ?>
                                                    </div>
                                                    <div class="order_description">
                                                        <? $b_price_text = "" ?>
                                                        <? $b_price_remains = "" ?>
                                                        <?
                                                        if (isset($b_price) && isset($b_price["RUB"]) && $b_price && count($b_price) > 0) {
                                                            if ($room[$RRValue]["Price"]["RUB"] > $b_price["RUB"]) {
                                                                if ($arResult["CURRENCY_NAME"] == "EURO" || $arResult["CURRENCY_NAME"] == "EUR") {
                                                                    $b_price_text = "&euro; " . $b_price["EURO"];
                                                                    $b_price_remains = "&euro; " . ($room[$RRValue]["Price"]["EURO"] - $b_price["EURO"]);
                                                                }
                                                                if ($arResult["CURRENCY_NAME"] == "USD") {
                                                                    $b_price_text = "$ " . $b_price["USD"];
                                                                    $b_price_remains = "$ " . ($room[$RRValue]["Price"]["USD"] - $b_price["USD"]);
                                                                }
                                                                if ($arResult["CURRENCY_NAME"] == "RUB") {
                                                                    $b_price_text = $b_price["RUB"] . " <span class='gotech_ruble'>a</span>";
                                                                    $b_price_remains = ($room[$RRValue]["Price"]["RUB"] - $b_price["RUB"]) . " <span class='gotech_ruble'>a</span>";
                                                                }
                                                                if ($arResult["CURRENCY_NAME"] == "KGS" || $arResult["CURRENCY_NAME"] == "KGZ") {
                                                                    $b_price_text = $b_price["KGS"] . " KGS";
                                                                    $b_price_remains = ($room[$RRValue]["Price"]["KGS"] - $b_price["KGS"]) . " KGS";
                                                                }
                                                                if (!$b_price_text) {
                                                                    $b_price_text = $b_price["RUB"] . " <span class='gotech_ruble'>a</span>";
                                                                    $b_price_remains = ($room[$RRValue]["Price"]["RUB"] - $b_price["RUB"]) . " <span class='gotech_ruble'>a</span>";
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <? if ($b_price_text): ?>
                                                            <p>
                                                                <?= GetMessage("RATE_WRAP_DESCRIPTION_1") ?> <?= $b_price_text ?> <?= GetMessage("RATE_WRAP_DESCRIPTION_2") ?>
                                                                «<?= $room[$RRValue]["RoomRateName"] ?>
                                                                » <?= GetMessage("RATE_WRAP_DESCRIPTION_3") ?> <?= $b_price_remains ?>
                                                                <br>
                                                            </p>
                                                        <? else: ?>
                                                            <p><?= $room[$RRValue]["ReservationConditionsOnline"] ?></p>
                                                        <? endif; ?>
                                                    </div>
                                                </div>


                                                <? $is_first_room = true ?>
                                                <? $ind = 0; ?>
                                                <div class="order_rooms" id="<?= "order_rooms_" . $strRRValue . "_" . $strRoomType ?>" style="display: none">
                                                <? foreach ($room['Rooms'] as $room_key => $room_info): ?>
                                                    <?
                                                    $this_book = $room['RoomTypeCode'] . '_' . $room_key . '_' . $RRValue . '_' . $period_from . '_' . $period_to . '_' . $visitors;
                                                    ?>
                                                    <? if ($ind == 2): ?>
                                                        <div class="hidden_rooms">
                                                    <? endif; ?>

                                                    <div class="order_item <? if ($is_first_room): ?>active<? endif; ?>" data-room="<?= $room_key ?>" data-code="<?= $RRValue ?>" data-rate_id="<?= $this_book ?>"
                                                         <? if (in_array($this_book, $booked)): ?>style="display: none"<? endif; ?>>

                                                        <? if (count($room["Rooms"]) > 1): ?>
                                                            <input type="radio" name="<?= "gotech_search_result_room_rates_" . $strRoomType ?>" id="<?= "gotech_room_rate_" . $strRRValue . "_" . $strRoomType ?>" <? if ($isFirst): ?>checked<? endif; ?>>
                                                        <? else: ?>
                                                            <input type="hidden" name="<?= "gotech_search_result_room_rates_" . $strRoomType ?>" id="<?= "gotech_room_rate_" . $strRRValue . "_" . $strRoomType ?>">
                                                        <? endif; ?>

                                                        <div class="order_actions when_no_book when_by_room">
                                                            <!-- Button -->
                                                            <form class="accomodation_form" name="accomodation_form_<?= $strRRValue . "_" . ($key + 1) . "_" . $room_key ?>" action="<?= $APPLICATION->GetCurPage(false) ?>" method="post">
                                                                <? if ($byperiods): ?>
                                                                    <input type="hidden" name="CheckInDate" value="<?= $room["CheckInDate"] ?>"/>
                                                                    <input type="hidden" name="CheckOutDate" value="<?= $room["CheckOutDate"] ?>"/>
                                                                    <input type="hidden" name="PeriodFrom0" value="<?= $room["CheckInDate"] ?>"/>
                                                                    <input type="hidden" name="PeriodTo0" value="<?= $room["CheckOutDate"] ?>"/>
                                                                <? else: ?>
                                                                    <input type="hidden" name="PeriodFrom0" value="<?= $period_from ?>"/>
                                                                    <input type="hidden" name="PeriodTo0" value="<?= $period_to ?>"/>
                                                                <? endif; ?>

                                                                <input type="hidden" name="adults0" value="<?= $adults ?>"/>
                                                                <input type="hidden" name="children0" value="<?= $children ?>"/>
                                                                <input type="hidden" name="count" value="1"/>

                                                                <input type="hidden" name="language" value="<?= $arResult["language"] ?>"/>
                                                                <input type="hidden" name="FormType" value="addOrder"/>
                                                                <input type="hidden" name="hotel_id" value="<?= $arResult["HOTEL"]["ID"] ?>"/>
                                                                <input type="hidden" name="RoomTypeCode" value="<?= $room["RoomTypeCode"] ?>"/>
                                                                <input type="hidden" name="LimitedRooms" value="<?= $room["LimitedRooms"] ?>"/>
                                                                <input type="hidden" name="RoomCode" value="<?= $room_key ?>"/>
                                                                <input type="hidden" name="RoomInfoName" value="<?= $room_info["Name"] ?>"/>
                                                                <input type="hidden" name="RoomInfoText" value="<?= $_REQUEST["RoomInfoText"] ?>"/>
                                                                <input type="hidden" name="RoomName" value="<?= $room["Name"] ?>"/>
                                                                <input type="hidden" name="RoomNameEn" value="<?= $room["Name_en"] ?>"/>
                                                                <input type="hidden" name="RoomRateCode" value="<?= str_replace("%", "thisisprocent", $RRValue) ?>"/>
                                                                <input type="hidden" name="RoomRateCodeDesc" value="<?= $room[$RRValue]["ReservationConditionsShort"] ?>"/>
                                                                <input type="hidden" name="PaymentMethodCodesAllowedOnline" value="<?= $room[$RRValue]["PaymentMethodCodesAllowedOnline"] ?>"/>
                                                                <input type="hidden" name="FirstDaySum" value="<?= $room[$RRValue]["FirstDaySum"] ?>"/>
                                                                <input type="hidden" name="Amount" value="<?= $room[$RRValue]["Amount"] ?>"/>
                                                                <input type="hidden" name="Currency" value="<?= $room["Currency"] ?>"/>
                                                                <input type="hidden" name="AmountPresentation" value="<?= $room["AmountPresentation"] ?>"/>
                                                                <input type="hidden" name="curr_page" value="<?= htmlspecialcharsEx($_REQUEST["curr_page"]) . "?booking=yes" ?>"/>
                                                                <input type="hidden" name="request_text" value="<?= htmlspecialcharsEx($room[$RRValue]["ByRequestText"]) ?>"/>

                                                                <input type="hidden" name="key" value="<?= ($key + 1) . "_" . $strRRValue . "_" . $room_key ?>"/>

                                                                <? if (!empty($room["RoomTypes"]["Accommodation"])): ?>
                                                                    <? foreach ($room["RoomTypes"]["Accommodation"] as $k => $Accommodation): ?>
                                                                        <input type="hidden" name="Accommodation_Code_<?= $k ?>" value="<?= $Accommodation["Code"] ?>"/>
                                                                        <input type="hidden" name="Accommodation_Description_<?= $k ?>" value="<?= $Accommodation["Description"] ?>"/>
                                                                        <input type="hidden" name="Accommodation_Age_<?= $k ?>" value="<?= $Accommodation["Age"] ?>"/>
                                                                        <input type="hidden" name="Accommodation_Is_Child_<?= $k ?>" value="<?= $Accommodation["IsChild"] ?>"/>
                                                                        <input type="hidden" name="Accommodation_Client_Age_From_<?= $k ?>" value="<?= $Accommodation["ClientAgeFrom"] ?>"/>
                                                                        <input type="hidden" name="Accommodation_Client_Age_To_<?= $k ?>" value="<?= $Accommodation["ClientAgeTo"] ?>"/>
                                                                    <? endforeach; ?>
                                                                <? endif; ?>

                                                                <span class="gotech_search_result_room_rates_item_button">
                                                                  <?if($_REQUEST['embeded'] == 'Y'):?>
                                                                    <span href="#" class="change_bron_button gotech_button" id="<?= "gotech_room_rate_" . $strRRValue . "_" . $strRoomType . "_" . $room_key ?>-link" style="<? if ($room["LimitedRooms"] <= 0 && !empty($room["LimitedRoomsText"])): ?>display: none;<? endif; ?>">
                                                                        <?= GetMessage("CHOOSE"); ?>
                                                                    </span>
                                                                  <?else:?>
                                                                    <span href="#" class="bron_button gotech_button" id="<?= "gotech_room_rate_" . $strRRValue . "_" . $strRoomType . "_" . $room_key ?>-link" style="<? if ($room["LimitedRooms"] <= 0 && !empty($room["LimitedRoomsText"])): ?>display: none;<? endif; ?>">
                                                                        <?= GetMessage("BOOKING"); ?>
                                                                    </span>
                                                                  <?endif;?>
                                                                </span>

                                                                <input type="hidden" name="<?= $RRValue . "_RUB_currency" ?>" value="<?= number_format($room[$RRValue]["Price"]["RUB"], 0, ',', ' ') ?> <span class='gotech_ruble'>a</span>"/>
                                                                <input type="hidden" name="<?= $RRValue . "_USD_currency" ?>" value="$ <?= number_format($room[$RRValue]["Price"]["USD"], 0, ',', ' ') ?>"/>
                                                                <input type="hidden" name="<?= $RRValue . "_EUR_currency" ?>" value="&euro; <?= number_format($room[$RRValue]["Price"]["EURO"], 0, ',', ' ') ?>"/>
                                                                <input type="hidden" name="<?= $RRValue . "_KGS_currency" ?>" value="<?= number_format($room[$RRValue]["Price"]["KGS"], 0, ',', ' ') ?> KGS"/>
                                                            </form>
                                                        </div>

                                                        <div class="price_info">
                                                            <?if($embeded_price_diff):?>
                                                                <div class="discount">
                                                                  <del>
                                                                    <?= $_REQUEST['embeded_price'] ?> <span class='gotech_ruble'>a</span>
                                                                  </del><?if($embeded_price_diff < 0): ?> / <span><?=$embeded_price_diff?>%</span><? endif; ?>
                                                                </div>
                                                            <? elseif ($economy): ?>
                                                                <div class="discount">
                                                                    <del>
                                                                        <?= $room["MaxPrice"]["RUB"] ?> <span class='gotech_ruble'>a</span>
                                                                    </del> / <span>-<?= $economy ?>%</span>
                                                                </div>
                                                            <? endif; ?>
                                                            <div class="price">
                                                                <?= OnlineBookingSupport::format_price($price, $arResult["CURRENCY_NAME"]) ?>
                                                            </div>
                                                        </div>

                                                        <div class="order_name">
                                                            <?= $room_info["Name"] ?>
                                                        </div>
                                                        <div class="order_description">
                                                            <p><?= $room_info["information_text"] ?></p>
                                                            <a target="_blank" class="room_info_image_wrapper" href="<?= CFile::GetPath($room_info['Picture']) ?>">
                                                                <img width="100%" class="room_info_image" src="<?= CFile::GetPath($room_info['Picture']) ?>"/>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <? if ($ind >= 2 && $ind + 1 == count($room['Rooms'])): ?>
                                                        </div>
                                                        <div class="show_hidden_rooms" data-open="<?= GetMessage("HIDE_CROOM_LIST")?>" data-close="<?= GetMessage("SHOW_CROOM_LIST")?> <?plural_form(count($room['Rooms']) - 2, array(GetMessage("1CROOM"), GetMessage("2CROOMS"), GetMessage("5CROOMS"))) ?>">
                                                            <?= GetMessage("SHOW_CROOM_LIST")?> <?plural_form(count($room['Rooms']) - 2, array(GetMessage("1CROOM"), GetMessage("2CROOMS"), GetMessage("5CROOMS"))) ?>
                                                        </div>
                                                    <? endif; ?>
                                                    <? $ind += 1; ?>
                                                    <? $is_first_room = false ?>
                                                <? endforeach; ?>
                                                </div>
                                            <? else: ?>
                                                <?
                                                    $this_book = $room['RoomTypeCode'] . '_' . $RRValue . '_' . $period_from . '_' . $period_to . '_' . $visitors;
                                                ?>
                                                <? $strRRValue = str_replace("%", "", $RRValue); ?>
                                                <? $strRRValue = str_replace("+", "x", $strRRValue); ?>
                                                <? $strRRValue = str_replace("/", "-", $strRRValue); ?>

                                                <div class="order_item active" data-code="<?= $RRValue ?>" data-rate_id="<?= $this_book ?>">
                                                    <? if (count($room["RoomRates"]) > 1): ?>
                                                        <input type="radio" name="<?= "gotech_search_result_room_rates_" . $strRoomType ?>" id="<?= "gotech_room_rate_" . $strRRValue . "_" . $strRoomType ?>" <? if ($isFirst): ?>checked<? endif; ?>>
                                                    <? else: ?>
                                                        <input type="hidden" name="<?= "gotech_search_result_room_rates_" . $strRoomType ?>" id="<?= "gotech_room_rate_" . $strRRValue . "_" . $strRoomType ?>">
                                                    <? endif; ?>

                                                    <!-- Details -->
                                                    <div style="display:none;" class="gotech_search_result_room_rates_item_details_hidden" id="<?= "gotech_room_rate_" . $strRRValue . "_" . $strRoomType ?>-block">
                                                        <? if (strlen($room[$RRValue]["ReservationConditionsOnline"]) > 512): ?>
                                                            <?= substr($room[$RRValue]["ReservationConditionsOnline"], 0, 512) . "..." ?>
                                                        <? elseif (!empty($room[$RRValue]["ReservationConditionsOnline"])): ?>
                                                            <?= $room[$RRValue]["ReservationConditionsOnline"] ?>
                                                        <? endif; ?>
                                                    </div>
                                                    <!-- Price -->
                                                    <?
                                                        $price = "";
                                                        if ($arResult["CURRENCY_NAME"] == "EURO" || $arResult["CURRENCY_NAME"] == "EUR") {
                                                            $price = "&euro; " . $room[$RRValue]["Price"]["EURO"];
                                                        }
                                                        if ($arResult["CURRENCY_NAME"] == "USD") {
                                                            $price = "$ " . $room[$RRValue]["Price"]["USD"];
                                                        }
                                                        if ($arResult["CURRENCY_NAME"] == "RUB") {
                                                            $price = $room[$RRValue]["Price"]["RUB"] . " <span class='gotech_ruble'>a</span>";
                                                        }
                                                        if ($arResult["CURRENCY_NAME"] == "KGS" || $arResult["CURRENCY_NAME"] == "KGZ") {
                                                            $price = $room[$RRValue]["Price"]["KGS"] . " KGS";
                                                        }
                                                        if (!$price)
                                                            $price = $room[$RRValue]["Price"]["RUB"] . " <span class='gotech_ruble'>a</span>";
                                                        if ($room["MaxPrice"]["RUB"] <= $room[$RRValue]["Price"]["RUB"] || !$arResult["SHOW_ECONOMY"]) {
                                                            $economy = 0;
                                                        } else {
                                                            $economy = 100 - round($room[$RRValue]["Price"]["RUB"] * 100 / $room["MaxPrice"]["RUB"]);
                                                        }
                                                        $embeded_price_diff = 0;
                                                        if ($_REQUEST['embeded'] == 'Y') {
                                                          $embeded_price_diff = round($room[$RRValue]["Price"]["RUB"] * 100 / $_REQUEST['embeded_price']) - 100;
                                                        }
                                                    ?>
                                                    <? if (in_array($this_book, $booked)): ?>
                                                        <div class="order_actions when_book" id="book_id_<?= $this_book ?>">
                                                            <div class="bordered_info in_order">
                                                                <?= GetMessage('IN_ORDER') ?>:<span class="room_in_cart_cnt"><?= $booked2[$this_book] ?></span>
                                                            </div>
                                                            <a href="#" data-cart_id="<?= $this_book ?>" class="delete_order"><?= GetMessage('DELETE') ?></a>
                                                            <br/>
                                                            <!-- Button -->
                                                            <form class="accomodation_form" name="accomodation_form_<?= $strRRValue . "_" . ($key + 1) ?>" action="<?= $APPLICATION->GetCurPage(false) ?>" method="post">
                                                                <a href="#" class="gotech_button add_onemoreroom" id="<?= "gotech_room_rate_" . $strRRValue . "_" . $strRoomType ?>-link" style="float:right;"><?= GetMessage('ADD_ROOM') ?></a>
                                                                <input type="hidden" name="count" value="1"/>
                                                                <input type="hidden" name="PeriodFrom0" value="<?= $period_from ?>"/>
                                                                <input type="hidden" name="PeriodTo0" value="<?= $period_to ?>"/>
                                                                <input type="hidden" name="adults0" value="<?= $adults ?>"/>
                                                                <input type="hidden" name="children0" value="<?= $children ?>"/>
                                                                <input type="hidden" name="language" value="<?= $arResult["language"] ?>"/>
                                                                <input type="hidden" name="FormType" value="addOrder"/>
                                                                <input type="hidden" name="hotel_id" value="<?= $arResult["HOTEL"]["ID"] ?>"/>
                                                                <input type="hidden" name="RoomTypeCode" value="<?= $room["RoomTypeCode"] ?>"/>
                                                                <input type="hidden" name="LimitedRooms" value="<?= $room["LimitedRooms"] ?>"/>
                                                                <input type="hidden" name="RoomName" value="<?= $room["Name"] ?>"/>
                                                                <input type="hidden" name="RoomNameEn" value="<?= $room["Name_en"] ?>"/>
                                                                <input type="hidden" name="RoomRateCode" value="<?= str_replace("%", "thisisprocent", $RRValue) ?>"/>
                                                                <input type="hidden" name="RoomRateCodeDesc" value="<?= $room[$RRValue]["ReservationConditionsShort"] ?>"/>
                                                                <input type="hidden" name="PaymentMethodCodesAllowedOnline" value="<?= $room[$RRValue]["PaymentMethodCodesAllowedOnline"] ?>"/>
                                                                <input type="hidden" name="FirstDaySum" value="<?= $room[$RRValue]["FirstDaySum"] ?>"/>
                                                                <input type="hidden" name="Amount" value="<?= $room[$RRValue]["Amount"] ?>"/>
                                                                <input type="hidden" name="Currency" value="<?= $room["Currency"] ?>"/>
                                                                <input type="hidden" name="AmountPresentation" value="<?= $room["AmountPresentation"] ?>"/>
                                                                <input type="hidden" name="curr_page" value="<?= htmlspecialcharsEx($_REQUEST["curr_page"]) . "?booking=yes" ?>"/>
                                                                <input type="hidden" name="request_text" value="<?= htmlspecialcharsEx($room[$RRValue]["ByRequestText"]) ?>"/>

                                                                <? if ($byperiods): ?>
                                                                    <input type="hidden" name="key" value="<?= ($key + 1) . "_" . $strRRValue . "_" . str_replace('/', '-', $room["AllotmentCode"]) ?>"/>
                                                                    <input type="hidden" name="AllotmentCode" value="<?= $room["AllotmentCode"] ?>"/>
                                                                <? else: ?>
                                                                    <input type="hidden" name="key" value="<?= ($key + 1) . "_" . $strRRValue ?>"/>
                                                                <? endif; ?>

                                                                <? if (!empty($room["RoomTypes"]["Accommodation"])): ?>
                                                                    <? foreach ($room["RoomTypes"]["Accommodation"] as $k => $Accommodation): ?>
                                                                        <input type="hidden" name="Accommodation_Code_<?= $k ?>" value="<?= $Accommodation["Code"] ?>"/>
                                                                        <input type="hidden" name="Accommodation_Description_<?= $k ?>" value="<?= $Accommodation["Description"] ?>"/>
                                                                        <input type="hidden" name="Accommodation_Age_<?= $k ?>" value="<?= $Accommodation["Age"] ?>"/>
                                                                        <input type="hidden" name="Accommodation_Is_Child_<?= $k ?>" value="<?= $Accommodation["IsChild"] ?>"/>
                                                                        <input type="hidden" name="Accommodation_Client_Age_From_<?= $k ?>" value="<?= $Accommodation["ClientAgeFrom"] ?>"/>
                                                                        <input type="hidden" name="Accommodation_Client_Age_To_<?= $k ?>" value="<?= $Accommodation["ClientAgeTo"] ?>"/>
                                                                    <? endforeach; ?>
                                                                <? endif; ?>
                                                                <input type="hidden" name="<?= $RRValue . "_RUB_currency" ?>" value="<?= number_format($room[$RRValue]["Price"]["RUB"], 0, ',', ' ') ?> <span class='gotech_ruble'>a</span>"/>
                                                                <input type="hidden" name="<?= $RRValue . "_USD_currency" ?>" value="$ <?= number_format($room[$RRValue]["Price"]["USD"], 0, ',', ' ') ?>"/>
                                                                <input type="hidden" name="<?= $RRValue . "_EUR_currency" ?>" value="&euro; <?= number_format($room[$RRValue]["Price"]["EURO"], 0, ',', ' ') ?>"/>
                                                                <input type="hidden" name="<?= $RRValue . "_KGS_currency" ?>" value="<?= number_format($room[$RRValue]["Price"]["KGS"], 0, ',', ' ') ?> KGS"/>
                                                            </form>
                                                        </div>
                                                    <? endif; ?>
                                                    <div
                                                        class="order_actions when_no_book"<? if (in_array($this_book, $booked)): ?> style="display:none;"<? endif; ?>>
                                                        <!-- Button -->
                                                        <form class="accomodation_form" name="accomodation_form_<?= $strRRValue . "_" . ($key + 1) ?>" action="<?= $APPLICATION->GetCurPage(false) ?>" method="post">
                                                            <? if ($byperiods): ?>
                                                                <input type="hidden" name="CheckInDate" value="<?= $room["CheckInDate"] ?>"/>
                                                                <input type="hidden" name="CheckOutDate" value="<?= $room["CheckOutDate"] ?>"/>
                                                                <input type="hidden" name="PeriodFrom0" value="<?= $room["CheckInDate"] ?>"/>
                                                                <input type="hidden" name="PeriodTo0" value="<?= $room["CheckOutDate"] ?>"/>
                                                            <? else: ?>
                                                                <input type="hidden" name="PeriodFrom0" value="<?= $period_from ?>"/>
                                                                <input type="hidden" name="PeriodTo0" value="<?= $period_to ?>"/>
                                                            <? endif; ?>
                                                            <input type="hidden" name="adults0" value="<?= $adults ?>"/>
                                                            <input type="hidden" name="children0" value="<?= $children ?>"/>
                                                            <input type="hidden" name="count" value="1"/>
                                                            <input type="hidden" name="language" value="<?= $arResult["language"] ?>"/>
                                                            <input type="hidden" name="FormType" value="addOrder"/>
                                                            <input type="hidden" name="hotel_id" value="<?= $arResult["HOTEL"]["ID"] ?>"/>
                                                            <input type="hidden" name="RoomTypeCode" value="<?= $room["RoomTypeCode"] ?>"/>
                                                            <input type="hidden" name="LimitedRooms" value="<?= $room["LimitedRooms"] ?>"/>
                                                            <input type="hidden" name="RoomName" value="<?= $room["Name"] ?>"/>
                                                            <input type="hidden" name="RoomNameEn" value="<?= $room["Name_en"] ?>"/>
                                                            <input type="hidden" name="RoomRateCode" value="<?= str_replace("%", "thisisprocent", $RRValue) ?>"/>
                                                            <input type="hidden" name="RoomRateCodeDesc" value="<?= $room[$RRValue]["ReservationConditionsShort"] ?>"/>
                                                            <input type="hidden" name="PaymentMethodCodesAllowedOnline" value="<?= $room[$RRValue]["PaymentMethodCodesAllowedOnline"] ?>"/>
                                                            <input type="hidden" name="FirstDaySum" value="<?= $room[$RRValue]["FirstDaySum"] ?>"/>
                                                            <input type="hidden" name="Amount" value="<?= $room[$RRValue]["Amount"] ?>"/>
                                                            <input type="hidden" name="Currency" value="<?= $room["Currency"] ?>"/>
                                                            <input type="hidden" name="AmountPresentation" value="<?= $room["AmountPresentation"] ?>"/>
                                                            <input type="hidden" name="curr_page" value="<?= htmlspecialcharsEx($_REQUEST["curr_page"]) . "?booking=yes" ?>"/>
                                                            <input type="hidden" name="request_text" value="<?= htmlspecialcharsEx($room[$RRValue]["ByRequestText"]) ?>"/>

                                                            <? if ($byperiods): ?>
                                                                <input type="hidden" name="key" value="<?= ($key + 1) . "_" . $strRRValue . "_" . str_replace('/', '-', $room["AllotmentCode"]) ?>"/>
                                                                <input type="hidden" name="AllotmentCode" value="<?= $room["AllotmentCode"] ?>"/>
                                                            <? else: ?>
                                                                <input type="hidden" name="key" value="<?= ($key + 1) . "_" . $strRRValue ?>"/>
                                                            <? endif; ?>

                                                            <? if (!empty($room["RoomTypes"]["Accommodation"])): ?>
                                                                <? foreach ($room["RoomTypes"]["Accommodation"] as $k => $Accommodation): ?>
                                                                    <input type="hidden" name="Accommodation_Code_<?= $k ?>" value="<?= $Accommodation["Code"] ?>"/>
                                                                    <input type="hidden" name="Accommodation_Description_<?= $k ?>" value="<?= $Accommodation["Description"] ?>"/>
                                                                    <input type="hidden" name="Accommodation_Age_<?= $k ?>" value="<?= $Accommodation["Age"] ?>"/>
                                                                    <input type="hidden" name="Accommodation_Is_Child_<?= $k ?>" value="<?= $Accommodation["IsChild"] ?>"/>
                                                                    <input type="hidden" name="Accommodation_Client_Age_From_<?= $k ?>" value="<?= $Accommodation["ClientAgeFrom"] ?>"/>
                                                                    <input type="hidden" name="Accommodation_Client_Age_To_<?= $k ?>" value="<?= $Accommodation["ClientAgeTo"] ?>"/>
                                                                <? endforeach; ?>
                                                            <? endif; ?>
                                                            <span class="gotech_search_result_room_rates_item_button">
                                                                <?if($_REQUEST['embeded'] == 'Y'):?>
                                                                  <span class="change_bron_button gotech_button" id="<?= "gotech_room_rate_" . $strRRValue . "_" . $strRoomType ?>-link" style="<? if ($room["LimitedRooms"] <= 0 && !empty($room["LimitedRoomsText"])): ?>display: none;<? endif; ?>">
                                                                      <?= GetMessage("CHOOSE"); ?>
                                                                  </span>
                                                                <?else:?>
                                                                  <span class="bron_button gotech_button" id="<?= "gotech_room_rate_" . $strRRValue . "_" . $strRoomType ?>-link" style="<? if ($room["LimitedRooms"] <= 0 && !empty($room["LimitedRoomsText"])): ?>display: none;<? endif; ?>">
                                                                      <? if (!$USER->IsAuthorized() || !in_array(COption::GetOptionint('gotech.hotelonline', 'USER_AGENT_GROUP'), $ar_group)): ?>
                                                                          <?= GetMessage("BOOKING"); ?>
                                                                      <? else: ?>
                                                                          <?= GetMessage("ADD"); ?>
                                                                          <span class="add_to_order">&nbsp;<?= GetMessage("2_ORDER"); ?></span>
                                                                      <? endif; ?>
                                                                  </span>
                                                                <?endif;?>
                                                            </span>

                                                            <input type="hidden"name="<?= $RRValue . "_RUB_currency" ?>"value="<?= number_format($room[$RRValue]["Price"]["RUB"], 0, ',', ' ') ?> <span class='gotech_ruble'>a</span>"/>
                                                            <input type="hidden"name="<?= $RRValue . "_USD_currency" ?>"value="$ <?= number_format($room[$RRValue]["Price"]["USD"], 0, ',', ' ') ?>"/>
                                                            <input type="hidden"name="<?= $RRValue . "_EUR_currency" ?>"value="&euro; <?= number_format($room[$RRValue]["Price"]["EURO"], 0, ',', ' ') ?>"/>
                                                            <input type="hidden"name="<?= $RRValue . "_KGS_currency" ?>"value="<?= number_format($room[$RRValue]["Price"]["KGS"], 0, ',', ' ') ?> KGS"/>
                                                        </form>
                                                    </div>

                                                    <div class="price_info">
                                                        <?if(isset($arResult['LOGGED_USER_DISCOUNT'])):?>
                                                            <div class="discount">
                                                                <del>
                                                                    <?= OnlineBookingSupport::format_price($price*100/(100 - $arResult['LOGGED_USER_DISCOUNT']), $arResult["CURRENCY_NAME"]) ?>
                                                                </del> / <span>-<?= $arResult['LOGGED_USER_DISCOUNT'] ?>%</span>
                                                            </div>
                                                            <div class="price">
                                                              <?= OnlineBookingSupport::format_price($price, $arResult["CURRENCY_NAME"]) ?>
                                                            </div>
                                                        <?else:?>
                                                            <?if($embeded_price_diff):?>
                                                                <div class="discount">
                                                                  <del>
                                                                    <?= $_REQUEST['embeded_price'] ?> <span class='gotech_ruble'>a</span>
                                                                  </del><?if($embeded_price_diff < 0): ?> / <span><?=$embeded_price_diff?>%</span><? endif; ?>
                                                                </div>
                                                            <? elseif ($economy): ?>
                                                                <div class="discount">
                                                                    <del>
                                                                        <?= $room["MaxPrice"]["RUB"] ?> <span class='gotech_ruble'>a</span>
                                                                    </del> / <span>-<?= $economy ?>%</span>
                                                                </div>
                                                            <? endif; ?>
                                                            <div class="price">
                                                              <?= OnlineBookingSupport::format_price($price, $arResult["CURRENCY_NAME"]) ?>
                                                            </div>
                                                        <? endif; ?>
                                                    </div>

                                                    <div class="order_name">
                                                        <? if (isset($room[$RRValue]["ReservationConditionsShort"]) && !empty($room[$RRValue]["ReservationConditionsShort"])): ?>
                                                            <?= $room[$RRValue]["ReservationConditionsShort"] ?>
                                                        <? else: ?>
                                                            <?= GetMessage("RATE") . $room[$RRValue]["RoomRateDescription"] ?>
                                                        <? endif; ?>
                                                    </div>
                                                    <div class="order_description">
                                                        <? $b_price_text = "" ?>
                                                        <? $b_price_remains = "" ?>
                                                        <?
                                                        if (isset($b_price) && isset($b_price["RUB"]) && $b_price && count($b_price) > 0) {
                                                            if ($room[$RRValue]["Price"]["RUB"] > $b_price["RUB"]) {
                                                                if ($arResult["CURRENCY_NAME"] == "EURO" || $arResult["CURRENCY_NAME"] == "EUR") {
                                                                    $b_price_text = "&euro; " . $b_price["EURO"];
                                                                    $b_price_remains = "&euro; " . ($room[$RRValue]["Price"]["EURO"] - $b_price["EURO"]);
                                                                }
                                                                if ($arResult["CURRENCY_NAME"] == "USD") {
                                                                    $b_price_text = "$ " . $b_price["USD"];
                                                                    $b_price_remains = "$ " . ($room[$RRValue]["Price"]["USD"] - $b_price["USD"]);
                                                                }
                                                                if ($arResult["CURRENCY_NAME"] == "RUB") {
                                                                    $b_price_text = $b_price["RUB"] . " <span class='gotech_ruble'>a</span>";
                                                                    $b_price_remains = ($room[$RRValue]["Price"]["RUB"] - $b_price["RUB"]) . " <span class='gotech_ruble'>a</span>";
                                                                }
                                                                if ($arResult["CURRENCY_NAME"] == "KGS" || $arResult["CURRENCY_NAME"] == "KGZ") {
                                                                    $b_price_text = $b_price["KGS"] . " KGS";
                                                                    $b_price_remains = ($room[$RRValue]["Price"]["KGS"] - $b_price["KGS"]) . " KGS";
                                                                }
                                                                if (!$b_price_text) {
                                                                    $b_price_text = $b_price["RUB"] . " <span class='gotech_ruble'>a</span>";
                                                                    $b_price_remains = ($room[$RRValue]["Price"]["RUB"] - $b_price["RUB"]) . " <span class='gotech_ruble'>a</span>";
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <? if ($b_price_text): ?>
                                                            <p>
                                                                <?= GetMessage("RATE_WRAP_DESCRIPTION_1") ?> <?= $b_price_text ?> <?= GetMessage("RATE_WRAP_DESCRIPTION_2") ?>
                                                                «<?= $room[$RRValue]["RoomRateName"] ?>
                                                                » <?= GetMessage("RATE_WRAP_DESCRIPTION_3") ?> <?= $b_price_remains ?>
                                                                <br>
                                                            </p>
                                                        <? else: ?>
                                                            <p><?= $room[$RRValue]["ReservationConditionsOnline"] ?></p>
                                                        <? endif; ?>
                                                    </div>
                                                    <? if ($arResult["SHOW_AVAILABILITY_ALL_ROOMS"]): ?>
                                                        <div class="row in-rate">
                                                            <a href="#" class="show_availability" onclick="showSold($(this), event, '<?= $RRValue ?>')">
                                                                <?= GetMessage('WATCH_AVAILABILITY') ?>
                                                            </a>
                                                        </div>
                                                        <div class="availibility_block">
                                                            <form>
                                                                <input type="hidden" name="rtc" value="<?= $room['RoomTypeCode'] ?>"/>
                                                                <input type="hidden" name="hid" value="<?= htmlspecialchars($_REQUEST["hotel_id"]) ?>"/>
                                                                <input type="hidden" name="htf" value="<?= $arResult["HOTEL_TIME_FROM"] ?>"/>
                                                                <input type="hidden" name="ht" value="<?= $arResult["HOTEL_TIME"] ?>"/>
                                                                <input type="hidden" name="data" value="<?= $room["data"] ?>"/>
                                                            </form>

                                                            <div class="h"><?= GetMessage('ROOMS_AVAILABILITY') ?>:</div>
                                                            <table id="room_sold">
                                                                <img src="/bitrix/js/onlinebooking/new/icons/progress.gif" style="width: 50px;">
                                                            </table>
                                                            <a href="#" class="later"><?= GetMessage('LATER') ?></a>
                                                            <a href="#" class="earlier"><?= GetMessage('EARLIER') ?></a>
                                                        </div>
                                                    <? endif; ?>
                                                </div>
                                            <? endif; ?>
                                            <? $isFirst = false; ?>
                                        <? endif; ?>
                                    <? endforeach; ?>
                                </div>
                            </div>
                        <? endif; ?>

                        <? if (!$room['RoomsAvailable']): ?>
                            <div class="row">
                                <div class="room_sold"><?= GetMessage('SOLD') ?></div>
                            </div>

                            <div class="row">
                                <a href="#" class="show_availability" onclick="showSold($(this), event)">
                                    <?= GetMessage('WATCH_AVAILABILITY') ?>
                                </a>
                            </div>

                            <div class="availibility_block">
                                <form>
                                    <input type="hidden" name="rtc" value="<?= $room['RoomTypeCode'] ?>"/>
                                    <input type="hidden" name="hid" value="<?= htmlspecialchars($_REQUEST["hotel_id"]) ?>"/>
                                    <input type="hidden" name="htf" value="<?= $arResult["HOTEL_TIME_FROM"] ?>"/>
                                    <input type="hidden" name="ht" value="<?= $arResult["HOTEL_TIME"] ?>"/>
                                    <input type="hidden" name="data" value="<?= $room["data"] ?>"/>
                                </form>

                                <div class="h"><?= GetMessage('ROOMS_AVAILABILITY') ?>:</div>
                                <table id="room_sold">
                                    <img src="/bitrix/js/onlinebooking/new/icons/progress.gif" style="width: 50px;">
                                </table>
                                <a href="#" class="later"><?= GetMessage('LATER') ?></a>
                                <a href="#" class="earlier"><?= GetMessage('EARLIER') ?></a>
                            </div>
                        <? endif; ?>
                    </div>
                    <div style="clear:both;"></div>
                    <div style="clear:both;"></div>
                </div>
            <? endforeach; ?>
        </div>
    <? endif; ?>

    <div id="rate-by-request-popup" class="mfp-hide mfp-align-top zoom-anim-dialog jewelery-popup">
        <div class="header">
            <a href="#" class="close mfp-close" onclick="$.magnificPopup.close();return false;"></a>
            <div class="title"></div>
        </div>
        <div class="body">
            <p></p>
        </div>
    </div>

    <link rel="stylesheet" href="/bitrix/js/onlinebooking/new/libs/image-slider/ideal-image-slider.css">
    <link rel="stylesheet" href="/bitrix/js/onlinebooking/new/libs/image-slider/default.css">
    <script src="/bitrix/js/onlinebooking/new/libs/image-slider/ideal-image-slider.js"></script>
    <script>
        $('body').on('click', '.show_hidden_rooms', function (e) {
            e.preventDefault();
            var hidden_rooms = $(this).prev();
            if (hidden_rooms.hasClass('open')) {
                hidden_rooms.removeClass('open');
                $(this).text($(this).data('close'));
            } else {
                hidden_rooms.addClass('open');
                $(this).text($(this).data('open'));
            }
        });
    </script>
    <script>
        function getValue(array, search) {
            var i = array.length;
            while (i--) {
                if (array[i].key === search) {
                    return array[i].value;
                }
            }
        }
        var sliderElems = document.querySelectorAll('.main_img, .main_adaptive_img');
        var sliderEl = null;
        var slider = null;
        var slider_map = [];
        if (sliderElems) {
            for (var i = 0; i < sliderElems.length; i++) {
                sliderEl = sliderElems[i];
                slider = new IdealImageSlider.Slider({
                    selector: sliderEl,
                    height: '220px',
                    interval: 5000000,
                    disableNav: false,
                    afterChange: function () {
                        var ind = $(this._attributes.currentSlide).index();
                        var $images = $(this._attributes.container).parents('.gotech_search_result_room').find('.gotech_search_result_room_additional_pictures');
                        var $img = $images.find('>a:nth-of-type(' + (ind + 1) + ')');
                        $images.find('>a').each(function () {
                            $(this).removeClass('active');
                        });
                        $img.addClass('active');
                    }
                });
                slider.start();
                slider_map.push({key: sliderEl, value: slider});
            }
        }

        var in_cart_num = <?=$in_cart_num?>;
        in_cart_num++;

        $('[name=md1]').val('<?=$period_from?>');
        $('[name=md2]').val('<?=$period_to?>');


        $('.chage_search_data').click(function () {
            $("#gotech_search_window").slideDown(1000);
            $("#gotech_search_window").removeAttr("style");

            $('body').off('click', '.show_hidden_rooms');
            $('body').off('click', '#room_sold td');
            $('body').off('click', '.order_actions .delete_order');
            $('body').off('click', '.bron_button, .add_onemoreroom');
            $('body').off('click', '.order_block .order_item');

            $('#gotech_search_result').html('');

            $('#gotech_search_choose').removeClass('find_page');
            $('div#gotech_online_booking').removeClass('find_page');

            iframe_resize();
            return false;
        });

        function showSold(el, e, rate) {
            e.preventDefault();
            // el.hide();
            var av_block = el.parent().find('~.availibility_block');
            if (av_block.is(':visible')) {
                av_block.slideUp();
                av_block.find('img').hide();
            } else {
                av_block.slideDown();
                if (av_block.find('#room_sold .prices td').length <= 1) {
                    av_block.find('img').show();
                    var ajax_data = {
                        wsdl: $('input[name="wsdl"]').val(),
                        data: av_block.find('input[name="data"]').val(),
                        from_text: "<?=GetMessage('FROM1')?>",
                        is_av_text: "<?=GetMessage('IS_AV')?>",
                        title_text: "<?=GetMessage('ROOMS_AVAILABILITY')?>",
                        earlier_text: "<?=GetMessage('EARLIER')?>",
                        later_text: "<?=GetMessage('LATER')?>",
                        adults: $('input[name="adults"]').val(),
                        rate: rate
                    };
                    ajax_in_progress = true;
                    $.ajax({
                        type: "POST",
                        url: "<?=OnlineBookingSupport::getProtocol()?><?=$_SERVER["SERVER_NAME"] . COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')?>get_room_inventory_balance.php",
                        dataType: "html",
                        data: ajax_data,
                        async: true,
                        cache: false,
                        success: function (html) {
                            ajax_in_progress = false;
                            console.log("Success!");
                            console.log(html);
                            if (!rate) {
                                av_block.replaceWith(html);
                            } else {
                                av_block.find('img').hide();
                                av_block.find('#room_sold').html(html);
                            }
                        },
                        error: function (html) {
                            ajax_in_progress = false;
                            console.log("Error!");
                            av_block.find('img').hide();
                        }
                    });
                }
            }
            return false;
        }

        $('body').on('click', '#room_sold td', function() {
            if ($(this).data('date')) {
                var $o_block = $(this).parents('.order_block');
                if ($o_block.length) {
                    if(!$o_block.find('.change_search_params_block').is(':visible')) {
                        $o_block.find('.change_order').click();
                    }
                    var min_nights = parseInt($('input[name="nights_min"]').val());
                    if (!min_nights) {
                        min_nights = 1;
                    }

                    var is_start_date_selected = $o_block.find('#room_sold .is_start_date');
                    var is_end_date_selected = $o_block.find('#room_sold .is_end_date');

                    var is_start_event = false;
                    if (is_start_date_selected.length && is_end_date_selected.length) {
                        is_start_date_selected.removeClass('is_start_date');
                        is_end_date_selected.removeClass('is_end_date');

                        $o_block.find('#room_sold [data-date="' + $(this).data('date') + '"]').addClass('is_start_date');
                        is_start_event = true;
                    } else if (is_start_date_selected.length) {
                        $o_block.find('#room_sold [data-date="' + $(this).data('date') + '"]').addClass('is_end_date');
                    } else {
                        $o_block.find('#room_sold [data-date="' + $(this).data('date') + '"]').addClass('is_start_date');
                        is_start_event = true;
                    }
                    var dateFrom, dateTo;
                    if (is_start_event) {
                        dateFrom = new Date($(this).data('date') * 1000);
                        dateTo = $o_block.find('[name="PeriodTo"]').datepicker('getDate');
                    } else {
                        dateFrom = $o_block.find('[name="PeriodFrom"]').datepicker('getDate');
                        dateTo = new Date($(this).data('date') * 1000);
                    }

                    var dayDiff = Math.round((dateTo - dateFrom) / (1000 * 60 * 60 * 24));
                    if (dayDiff < min_nights) {
                        dateTo = new Date(dateFrom.getTime() + min_nights * 86400000);
                    }

                    var shortDatepickerRange = {endDate: dateTo, startDate: dateFrom};

                    setPeriods2(shortDatepickerRange, $o_block);
                    setNumberOfNights2(shortDatepickerRange, $o_block);
                }
            }
        });

        function book_room_ajax(el) {
            var th = el;
            var f = el.parents('form');
            var t = el;

          var limited_rooms = f.find('input[name="LimitedRooms"]').val();

          if (!limited_rooms || parseInt(limited_rooms) > 0 || !f.parents('.gotech_search_result_room_info').find('.available_rooms_count').length) {

            if (1) {
              var id = el.prop('id');
              var splits = id.split("_");
              var PromoCode = $('input[name="promo_code_f"]').val();

              var PeriodFrom = f.find('input[name="PeriodFrom0"]').val();
              var PeriodTo = f.find('input[name="PeriodTo0"]').val();
              var adults = f.find('input[name="adults0"]').val();
              var childs = f.find('input[name="children0"]').val();

              $.session.set("PeriodFrom", PeriodFrom);
              $.session.set("PeriodTo", PeriodTo);
              $.session.set("adults", adults);
              $.session.set("children", childs);

              if (typeof yaCounter != 'undefined') {
                yaCounter.reachGoal('ADD_ROOM');
                yaCounter.reachGoal('ADD_' + splits[1].toUpperCase());
              }
              if (typeof dataLayer != 'undefined') {
                dataLayer.push({
                  event: "VirtualPageview",
                  virtualPageURL: "/virtual/clickBook_3",
                  virtualPageTitle: "Шаг 3 – Забронировать номер",
                  virtualPageHotel: $('input[name="hotel_id"]').val()
                });
              }
              if (typeof ga != 'undefined') {
                ga('send', 'event', 'BUTTON', 'ADD_ROOM');
                ga('send', 'event', 'BUTTON', 'ADD_' + splits[1].toUpperCase());
              }

              if (adults == undefined) {
                adults = 1;
              }
              var visitors;
              if (childs == undefined || !childs) {
                visitors = parseInt(adults);
              } else {
                visitors = parseInt(adults) + parseInt(childs);
              }

              var oldValue = 0;
              var newValue = 0;

              var $form = el.parents('form');
              oldValue = $form.children('input[name="LimitedRooms"]').val();
              if (oldValue != 0 || $('#LimitedRooms_' + splits[0]).text() == "") {
                if ($form.children('input[name="CheckInDate"]').val() == undefined) {
                  $form.append('<input type="hidden" name="PeriodFrom" value="' + PeriodFrom + '" />');
                } else {
                  $form.append('<input type="hidden" name="PeriodFrom" value="' + $form.children('input[name="CheckInDate"]').val() + '" />');
                }
                if ($form.children('input[name="CheckOutDate"]').val() == undefined) {
                  $form.append('<input type="hidden" name="PeriodTo" value="' + PeriodTo + '" />');
                } else {
                  $form.append('<input type="hidden" name="PeriodTo" value="' + $form.children('input[name="CheckOutDate"]').val() + '" />');
                }
                $form.append('<input type="hidden" name="visitors" value="' + visitors + '" />');
                if (PromoCode)
                  $form.append('<input type="hidden" name="PromoCode" value="' + PromoCode + '" />');
              }
              var sid = '';
              if ($('[name="SID"]').length && $('[name="SID"]').val()) {
                sid = '?' + $('[name="SID"]').val();
              }
              var ajax_data = $form.serialize() + '&cart_id=' + th.parents('.order_item').data('rate_id');

              $.post('/bitrix/components/onlinebooking/onlinebooking/ajax.php' + sid, ajax_data, function (data) {
                var is_agent = $('input[name="is_agent"]').val();
                if (data) {

                  var empty_basket = false;
                  if (!$('#gotech_right_basket .cart_item').length == 1) {
                    in_cart_num = 0;
                    empty_basket = true;
                  }

                  $('#gotech_search_choose').html(data);
                  $('#gotech_search_choose').css('opacity', 1);
                  $('#gotech_search_choose').show();

                  if (is_agent == "Y") {

                    var marg = $('#gotech_search_choose').outerHeight();
                    $('#gotech_search_result').css('margin-bottom', parseInt(marg) + 20 + 'px');
                    iframe_resize();
                  } else {

                    var path_to_folder = $('input[name="path_to_folder"]').val();
                    if (!path_to_folder) {
                      path_to_folder = "/";
                    }
                    var link = path_to_folder + "?booking=yes&hotel=" + $('input[name="hotel_id"]').val();
                    if ($('input[name="SessionID"]').val()) {
                      link += "&SessionID=" + $('input[name="SessionID"]').val();
                    }
                    if ($('input[name="UserID"]').val()) {
                      link += "&UserID=" + $('input[name="UserID"]').val();
                    }
                    if ($('input[name="utm_source"]').val()) {
                      link += "&utm_source=" + $('input[name="utm_source"]').val();
                    }
                    if ($('input[name="utm_medium"]').val()) {
                      link += "&utm_medium=" + $('input[name="utm_medium"]').val();
                    }
                    if ($('input[name="utm_campaign"]').val()) {
                      link += "&utm_campaign=" + $('input[name="utm_campaign"]').val();
                    }
                    var email = "";
                    var phone = "";
                    var langu = "";
                    if ($('input[name="email"]').length > 0) {
                      email = $('input[name="email"]').val();
                    }
                    if ($('input[name="phone"]').length > 0) {
                      phone = $('input[name="phone"]').val();
                    }
                    if ($('input[name="language"]').length > 0) {
                      langu = $('input[name="language"]').val();
                    }
                    if (typeof yaCounter != 'undefined') {
                      yaCounter.reachGoal('TO_INPUT_GUEST_DATA');
                    }
                    if (typeof ga != 'undefined') {
                      ga('send', 'event', 'BUTTON', 'TO_INPUT_GUEST_DATA');
                    }

                    var pp = th.parents('.order_actions');
                    var ppp = th.parents('.order_item');
                    if (pp.hasClass('when_no_book')) {
                      pp.hide();
                      pp.before('' +
                        '<div class="order_actions when_book">' +
                        '<div class="bordered_info in_order">' +
                        '<?=GetMessage('IN_ORDER')?>:<span class="room_in_cart_cnt">1</span>' +
                        '</div>' +
                        '<a href="#" data-cart_id="' + ppp.data('rate_id') + '" class="delete_order"><?=GetMessage('DELETE')?></a>' +
                        '<br/>' +
                        '</div>'
                      );

                      var formclone = pp.find('form').clone();
                      ppp.find('.order_actions.when_book').append(formclone);

                      var fff = pp.prev().find('form'); //$('.order_actions.when_book form')
                      fff.find('div').remove();
                      fff.find('span').remove();
                      if (!pp.hasClass('when_by_room')) {
                        fff.prepend('' +
                          '<a href="#" class="gotech_button add_onemoreroom" style="float:right;"><?=GetMessage('ADD_ROOM')?></a>');
                      }
                      fff.prepend('' +
                        '<input type="hidden" name="count" value="1"/>');
                    }
                    else {
                      var newcnt = Number(pp.find('.room_in_cart_cnt').text());
                      newcnt++;
                      pp.find('.room_in_cart_cnt').text(newcnt);
                    }

                    in_cart_num++;
                    $('.order_actions .delete_order').on('click', function () {
                      var cart_id = $(this).data('cart_id');
                      var t = $(this);

                      $.get('/bitrix/components/onlinebooking/onlinebooking/ajax.php?FormType=deleteOrder&cart_id=' + cart_id + '&Hotel=<?=htmlspecialchars($_REQUEST['hotel_id'])?>',
                        function (data) {
                          $('#gotech_search_choose').html(data);
                          $('#gotech_search_choose').css('opacity', 1);
                          $('#gotech_search_choose').show();
                          t.parent().next().show();
                          t.parent().next().find('.bron_button').show();
                          t.parent().remove();
                        });
                      return false;
                    });

                    parent.postMessage('refreshBasket', '*');
                    if (empty_basket)
                      location.href = link;
                  }

                  f.find('input[name="LimitedRooms"]').val(parseInt(f.find('input[name="LimitedRooms"]').val()) - 1)
                  change_available_rooms_count_text(f, parseInt(f.find('input[name="LimitedRooms"]').val()));
                } else {
                  alert("Возникла ошибка при бронировании");
                }
                if (is_agent != "N") {
                  $('div#gotech_online_booking').fadeTo(1, 1);
                  $('div#gotech_online_booking').css('pointer-events', 'auto');
                  $('.gotech_search_result_progress_icon').hide();

                }
              }, 'html');

            }
          } else if(limited_rooms && parseInt(limited_rooms) <= 0) {
            f.find('input[name="LimitedRooms"]').val("0")
            change_available_rooms_count_text(f, 0);
          }
        }

        $(document).scroll(function () {
            var basket = $('#gotech_right_basket');
            var wt = $(window).scrollTop();
            if (wt >= 440) {
                basket.css('width', '279px');
                basket.css('position', 'fixed');
                basket.css('top', '10px');
            }
            else {
                basket.css('width', 'auto');
                basket.css('position', 'relative');
                basket.css('top', '0px');
            }
        });

        var ajax_in_progress = false;

        function later_click(th) {
            var av = th.parents('.availibility_block');
            var w1 = av.width();
            var w2 = av.find('table').width();
            var diff = w2 - w1;
            var p = av;
            var t = p.find('table');
            var ml = parseInt(t.css('margin-left'));
            var ml1 = ml * (-1);

            if (ml1 < diff) {
                ml1 += 200;
                if (ml1 > diff)
                    ml1 = diff;
                t.animate({marginLeft: '-' + ml1 + 'px'}, 500);

            } else if (ml1 >= diff) {
                var last_date = av.find('tr.dates td:last-child').text();
                var rate = av.parent().find('[name="RoomRateCode"]').val();
                if (av.find('[name="cur_rate"]').length) {
                    rate = av.find('[name="cur_rate"]').val();
                }
                var ajax_data = {
                    wsdl: $('input[name="wsdl"]').val(),
                    data: av.find('input[name="data"]').val(),
                    from_text: "<?=GetMessage('FROM1')?>",
                    is_av_text: "<?=GetMessage('IS_AV')?>",
                    adults: $('input[name="adults"]').val(),
                    rate: rate,
                    later: true,
                    last_date: last_date
                };
                if (!ajax_in_progress) {
                    av.find('#room_sold .prices').append('<td class="" style="border: 0px;padding: 0;position: relative;width: 50px;display: block;"><img src="/bitrix/js/onlinebooking/new/icons/progress.gif" style="width: 50px;position: absolute;top: -24px;left:0;"></td>');
                    diff = av.find('table').width() - av.width();
                    t.animate({marginLeft: '-' + diff + 'px'}, 0);
                    ajax_in_progress = true;
                    $.ajax({
                        type: "POST",
                        url: "<?=OnlineBookingSupport::getProtocol()?><?=$_SERVER["SERVER_NAME"] . COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')?>get_room_inventory_balance.php",
                        dataType: "json",
                        data: ajax_data,
                        async: true,
                        cache: false,
                        success: function (res) {
                            ajax_in_progress = false;
                            av.find('#room_sold .prices img').parent().remove();
                            av.find('#room_sold .dates').append(res['dates']);
                            av.find('#room_sold .prices').append(res['values']);
                            th.click();
                            th.show();
                        },
                        error: function (html) {
                            ajax_in_progress = false;
                            console.log("Error!");
                        }
                    });
                }
            }
            av.find('.earlier').show();
        }

        function earlier_click(th) {
            var av = th.parents('.availibility_block');
            var w1 = av.width();
            var w2 = av.find('table').width();
            var diff = w2 - w1;
            var p = av;
            var t = p.find('table');
            var ml = parseInt(t.css('margin-left'));
            var ml1 = ml * (-1);
            var d = av.find('tr.dates td:first-child').text();
            var today = '<?=date('d.m.Y')?>';

            if (ml1 <= diff && ml1) {
                ml1 -= 200;
                if (ml1 < 0)
                    ml1 = 0;
                t.animate({marginLeft: '-' + ml1 + 'px'}, 500);

                if (d == today && !ml1) {
                    th.hide();
                }
            }
            else {
                var first_date = av.find('tr.dates td:first-child').text();
                var rate = av.parent().find('[name="RoomRateCode"]').val();
                if (av.find('[name="cur_rate"]').length) {
                    rate = av.find('[name="cur_rate"]').val();
                }
                var ajax_data = {
                    wsdl: $('input[name="wsdl"]').val(),
                    data: av.find('input[name="data"]').val(),
                    from_text: "<?=GetMessage('FROM1')?>",
                    is_av_text: "<?=GetMessage('IS_AV')?>",
                    adults: $('input[name="adults"]').val(),
                    rate: rate,
                    earlier: true,
                    first_date: first_date
                };
                if (!ajax_in_progress) {
                    av.find('#room_sold .dates').prepend('<td class="fix_td" style="width: 50px;display: block;border: 0;"></td>');
                    av.find('#room_sold .prices').prepend('<td class="" style="border: 0px;padding: 0;position: relative;width: 50px;display: block;"><img src="/bitrix/js/onlinebooking/new/icons/progress.gif" style="width: 50px;position: absolute;top: -24px;left:0;"></td>');
                    t.animate({marginLeft: '0px'}, 0);
                    ajax_in_progress = true;
                    $.ajax({
                        type: "POST",
                        url: "<?=OnlineBookingSupport::getProtocol()?><?=$_SERVER["SERVER_NAME"] . COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')?>get_room_inventory_balance.php",
                        dataType: "json",
                        data: ajax_data,
                        async: true,
                        cache: false,
                        success: function (res) {
                            ajax_in_progress = false;
                            var was = av.find('.dates').length;
                            av.find('#room_sold .dates .fix_td').remove();
                            av.find('#room_sold .prices img').parent().remove();
                            av.find('#room_sold .dates').prepend(res['dates']);
                            av.find('#room_sold .prices').prepend(res['values']);
                            var become = av.find('.dates').length;
                            t.css('margin-left', '-' + ((become - was) * 67.2) + 'px');
                            d = av.find('tr.dates td:first-child').text();
                            if (d != today)
                                th.show();
                        },
                        error: function (html) {
                            ajax_in_progress = false;
                            console.log("Error!");
                        }
                    });
                }
            }
        }

        $(document).ready(function () {
            $('body').on('click', '.order_actions .delete_order', function () {
                var cart_id = $(this).data('cart_id');
                var t = $(this);

                $.get('/bitrix/components/onlinebooking/onlinebooking/ajax.php?FormType=deleteOrder&cart_id=' + cart_id + '&Hotel=<?=htmlspecialchars($_REQUEST['hotel_id'])?>',
                    function (data) {
                        $('#gotech_search_choose').html(data);
                        $('#gotech_search_choose').css('opacity', 1);
                        $('#gotech_search_choose').show();
                        t.parent().next().show();
                        t.parent().next().find('.bron_button').show();
                        t.parent().remove();
                        parent.postMessage('refreshBasket', '*');
                    });
                return false;
            });
            $('.order_actions .delete_order').on('click', function () {
                var cart_id = $(this).data('cart_id');
                var t = $(this);

                $.get('/bitrix/components/onlinebooking/onlinebooking/ajax.php?FormType=deleteOrder&cart_id=' + cart_id + '&Hotel=<?=htmlspecialchars($_REQUEST['hotel_id'])?>',
                    function (data) {
                        $('#gotech_search_choose').html(data);
                        $('#gotech_search_choose').css('opacity', 1);
                        $('#gotech_search_choose').show();
                        t.parent().next().show();
                        t.parent().next().find('.bron_button').show();
                        t.parent().remove();
                        parent.postMessage('refreshBasket', '*');
                    });
                return false;
            });

            $('.availibility_block .later').on('click', function () {
                var th = $(this);
                later_click(th);
                return false;
            });

            $('.availibility_block .earlier').on('click', function () {
                var th = $(this);
                earlier_click(th);
                return false;
            });

            /**********************swipe************************************/

            $('.availibility_block').on('swipeleft', function () {
                var av = $(this);
                av.find('.later').click();
                return false;
            });
            $('.availibility_block').on('swiperight', function () {
                var av = $(this);
                av.find('.earlier').click();
                return false;
            });
            $('.main_adaptive_img').on('swipeleft', function () {
                $(this).find('.slider_arrow_left').click();
                return false;
            });
            $('.main_adaptive_img').on('swiperight', function () {
                $(this).find('.slider_arrow_right').click();
                return false;
            });

            /****************************************************************/

            $('.change_search_params_block .gotech_search_window_guests_ages_spinner').selectric({
                maxHeight: 160,
                disableOnMobile: true,
            });

            if ($('.change_search_params_block [name=TimeFrom]').length) {
                $('.change_search_params_block [name=TimeFrom]').each(function () {
                    var t = $(this);
                    initHours2(new Date(), t);
                });
            }

            var datepickerRange = getDatepickerRange();
            setCalendar2('.period_from', datepickerRange);
            setCalendar2('.period_to', datepickerRange);

            setPeriods2(datepickerRange);

            $('.change_search_params_block').each(function () {
                setNumberOfNights2(datepickerRange, $(this));
            });

            /**/

            $('.change_search_params_block .submit_change_order').click(function () {
                var th = $(this);
                var p = $(this).parents('.change_search_params_block');
                var pp = $(this).parents('.gotech_search_result_room');

                pp.find('.ajax_noav').remove();
                p.slideUp();

                var period_from = p.find('.period_from').val();
                var period_to = p.find('.period_to').val();
                var nights = p.find('[name=night]').val();
                var adults = Number(p.find('[name=adults]').val());
                var children = p.find('[name=children]').val() ? Number(p.find('[name=children]').val()) : 0;

                p.prev().find('.text_night').text(nights + ' ' + declOfNum(nights, ['<?=GetMessage('1NIGHTS')?>', '<?=GetMessage('2NIGHTS')?>', '<?=GetMessage('5NIGHTS')?>']));
                p.prev().find('.text_adults').text((adults + children) + ' ' + declOfNum(adults + children, ['<?=GetMessage('1GUEST')?>', '<?=GetMessage('2GUESTS')?>', '<?=GetMessage('5GUESTS')?>']));
                p.prev().find('.text_from_to').text(period_from + ' - ' + period_to);

                pp.find('.order_item .price').after('<img src="/bitrix/js/onlinebooking/new/icons/progress.gif" style="width: 60px;top: -17px;position: relative;" />');
                pp.find('.order_item .discount').hide();
                pp.find('.order_item .price').hide();
                pp.find('.order_item.active .order_actions').hide();

                th.after('<img src="/bitrix/js/onlinebooking/new/icons/snake-loader.gif" id="preloader1" />');
                th.hide();

                var f = $(this).parents('form').serialize();
                var room = $(this).parents('.gotech_search_result_room');

                $.post('/bitrix/components/onlinebooking/reservation.find/templates/.default/change_order.php', f,
                    function (data) {
                        if (!data.c) {
                            room.find('.room_rates_block').show();
                            room.find('.room_rates_block').html(data.b);
                        }
                        else if (data.c == 'not_found') {
                            room.find('.room_rates_block').hide();
                            room.find('.room_rates_block').after(data.b);
                        }

                        $('#preloader1').remove();
                        th.show();
                    }, 'json');
                return false;
            });

            $('.order_block .change_order').click(function () {
                $(this).parents('.order_block').find('.change_search_params_block').slideToggle();
                setTimeout(function () {
                    iframe_resize();
                }, 800);
                return false;
            });

            $('.all_info, .gotech_search_result_room_main_picture img').click(function () {
                var p = $(this).parents('.gotech_search_result_room');
                $(this).parent().parent().find('.room_detail_data').slideToggle();

                if (p.find('.gotech_search_result_room_main_picture').is(':visible')) {
                    p.find(' > .gotech_search_result_room_main_picture').hide();
                    p.find('.gotech_search_result_room_additional_pictures').show();
                }
                else {
                    p.find(' > .gotech_search_result_room_main_picture').show();
                    p.find('.gotech_search_result_room_additional_pictures').hide();
                }

                setTimeout(function () {
                    iframe_resize();
                }, 500);
                iframe_resize();

                return false;
            });

            $('.gotech_search_result_room_additional_pictures > a').click(function () {
                var $parent = $(this).parent();
                var $this = $(this);
                $parent.find('a').removeClass('active');
                $this.addClass('active');

                $parent.parent().find('.main_img, .main_adaptive_img').each(function (ind, el) {
                    var slider = getValue(slider_map, el);
                    if (slider) {
                        slider.gotoSlide($this.index() + 1);
                    }
                });

                return false;
            });

            $('.gotech_search_result_room').each(function (i) {
                $(this).attr('id', 'room' + i);
            });
        });

        function check_rooms_list(list_el) {
            var open_hidden_list = true;
            list_el.find('>.order_item').each(function (ind, item) {
                if ($(item).is(':visible')) {
                    open_hidden_list = false;
                }
            });
            if (list_el.find('.hidden_rooms .order_item').length < 2) {
                open_hidden_list = true;
            }
            if (open_hidden_list) {
                list_el.find('.show_hidden_rooms').click();
                list_el.find('.show_hidden_rooms').hide();
            }
        }

        $('.room_rates_block').each(function (ind, list_el) {
            check_rooms_list($(list_el));
        });

        $('body').on('click', '.bron_button, .add_onemoreroom', function (e) {
            e.preventDefault();
            var th = $(this);
            var f = $(this).parents('form');
            var t = e.target || e.srcElement;
            var r_text = f.find('input[name="request_text"]').val();
            var limited_rooms = f.find('input[name="LimitedRooms"]').val();

            if (!limited_rooms || parseInt(limited_rooms) > 0 || !f.parents('.gotech_search_result_room_info').find('.available_rooms_count').length) {
              if (r_text) {
                var rate_title = f.find('input[name="RoomRateCodeDesc"]').val();
                $('#rate-by-request-popup .header>.title').text(rate_title);
                $('#rate-by-request-popup .body>p').html($('<textarea />').html(r_text).text());
                $.magnificPopup.open({
                  items: {
                    src: '#rate-by-request-popup',
                    type: 'inline'
                  },
                  fixedContentPos: true,
                  fixedBgPos: true,
                  overflowY: 'auto',
                  closeBtnInside: true,
                  preloader: false,
                  midClick: true,
                  removalDelay: 300,
                  mainClass: 'my-mfp-zoom-in'
                });
              } else {
                if ($(t.parentElement).is(":visible")) {
                  var wait_button = $(this);
                  if (!$('#gotech_right_basket .cart_item').length) {
                    th.after('<img src="/bitrix/js/onlinebooking/new/icons/progress.gif" width="100" />');
                    th.hide();
                  }
                  var idd = $(this).prop('id');
                  var id = $(this).prop('id').slice(17, -5);
                  var splits = id.split("_");
                  var PromoCode = $('input[name="promo_code_f"]').val();

                  var PeriodFrom = f.find('input[name="PeriodFrom0"]').val();
                  var PeriodTo = f.find('input[name="PeriodTo0"]').val();
                  var adults = f.find('input[name="adults0"]').val();
                  var childs = f.find('input[name="children0"]').val();

                  $.session.set("PeriodFrom", PeriodFrom);
                  $.session.set("PeriodTo", PeriodTo);
                  $.session.set("adults", adults);
                  $.session.set("children", childs);

                  if (typeof yaCounter != 'undefined') {
                    yaCounter.reachGoal('ADD_ROOM');
                    yaCounter.reachGoal('ADD_' + splits[1].toUpperCase());
                  }
                  if (typeof dataLayer != 'undefined') {
                    dataLayer.push({
                      event: "VirtualPageview",
                      virtualPageURL: "/virtual/clickBook_3",
                      virtualPageTitle: "Шаг 3 – Забронировать номер",
                      virtualPageHotel: $('input[name="hotel_id"]').val()
                    });
                  }
                  if (typeof ga != 'undefined') {
                    ga('send', 'event', 'BUTTON', 'ADD_ROOM');
                    ga('send', 'event', 'BUTTON', 'ADD_' + splits[1].toUpperCase());
                  }

                  if (adults == undefined) {
                    adults = 1;
                  }
                  var visitors;
                  if (childs == undefined || !childs) {
                    visitors = parseInt(adults);
                  } else {
                    visitors = parseInt(adults) + parseInt(childs);
                  }

                  var oldValue = 0;
                  var newValue = 0;

                  var $form = $(this).parents('form');
                  oldValue = $form.children('input[name="LimitedRooms"]').val();
                  if (oldValue != 0 || $('#LimitedRooms_' + splits[0]).text() == "") {
                    if ($form.children('input[name="CheckInDate"]').val() == undefined) {
                      $form.append('<input type="hidden" name="PeriodFrom" value="' + PeriodFrom + '" />');
                    } else {
                      $form.append('<input type="hidden" name="PeriodFrom" value="' + $form.children('input[name="CheckInDate"]').val() + '" />');
                    }
                    if ($form.children('input[name="CheckOutDate"]').val() == undefined) {
                      $form.append('<input type="hidden" name="PeriodTo" value="' + PeriodTo + '" />');
                    } else {
                      $form.append('<input type="hidden" name="PeriodTo" value="' + $form.children('input[name="CheckOutDate"]').val() + '" />');
                    }
                    $form.append('<input type="hidden" name="visitors" value="' + visitors + '" />');
                    if (PromoCode)
                      $form.append('<input type="hidden" name="PromoCode" value="' + PromoCode + '" />');
                  }
                  var sid = '';
                  if ($('[name="SID"]').length && $('[name="SID"]').val()) {
                    sid = '?' + $('[name="SID"]').val();
                  }
                  var ajax_data = $form.serialize() + '&cart_id=' + th.parents('.order_item').data('rate_id');
                  $.post('/bitrix/components/onlinebooking/onlinebooking/ajax.php' + sid, ajax_data, function (data) {
                    var is_agent = $('input[name="is_agent"]').val();
                    if (data) {
                      var path_to_folder = $('input[name="path_to_folder"]').val();
                      if (!path_to_folder) {
                        path_to_folder = "/";
                      }

                      var empty_basket = false;
                      if (!$('#gotech_right_basket .cart_item').length) {
                        in_cart_num = 0;
                        empty_basket = true;

                        var link = path_to_folder + "?booking=yes&hotel=" + $('input[name="hotel_id"]').val()
                        if ($('input[name="SessionID"]').val()) {
                          link += "&SessionID=" + $('input[name="SessionID"]').val();
                        }
                        if ($('input[name="UserID"]').val()) {
                          link += "&UserID=" + $('input[name="UserID"]').val();
                        }
                        if ($('input[name="utm_source"]').val()) {
                          link += "&utm_source=" + $('input[name="utm_source"]').val();
                        }
                        if ($('input[name="utm_medium"]').val()) {
                          link += "&utm_medium=" + $('input[name="utm_medium"]').val();
                        }
                        if ($('input[name="utm_campaign"]').val()) {
                          link += "&utm_campaign=" + $('input[name="utm_campaign"]').val();
                        }
                        location.href = link;
                        return;
                      }

                      $('#gotech_search_choose').html(data);
                      $('#gotech_search_choose').css('opacity', 1);
                      $('#gotech_search_choose').show();

                      if (is_agent == "Y") {
                        var marg = $('#gotech_search_choose').outerHeight();
                        $('#gotech_search_result').css('margin-bottom', parseInt(marg) + 20 + 'px');
                        iframe_resize();
                      } else {
                        var email = "";
                        var phone = "";
                        var langu = "";
                        if ($('input[name="email"]').length > 0) {
                          email = $('input[name="email"]').val();
                        }
                        if ($('input[name="phone"]').length > 0) {
                          phone = $('input[name="phone"]').val();
                        }
                        if ($('input[name="language"]').length > 0) {
                          langu = $('input[name="language"]').val();
                        }
                        if (typeof yaCounter != 'undefined') {
                          yaCounter.reachGoal('TO_INPUT_GUEST_DATA');
                        }
                        if (typeof ga != 'undefined') {
                          ga('send', 'event', 'BUTTON', 'TO_INPUT_GUEST_DATA');
                        }

                        var pp = th.parents('.order_actions');
                        var ppp = th.parents('.order_item');

                        if (pp.hasClass('when_no_book')) {
                          pp.hide();
                          pp.before('' +
                            '<div class="order_actions when_book" id="book_id_' + ppp.data('rate_id') + '">' +
                            '<div class="bordered_info in_order">' +
                            '<?=GetMessage('IN_ORDER')?>:<span class="room_in_cart_cnt">1</span>' +
                            '</div>' +
                            '<a href="#" data-cart_id="' + ppp.data('rate_id') + '" class="delete_order"><?=GetMessage('DELETE')?></a>' +
                            '<br/>' +
                            '</div>'
                          );

                          var formclone = pp.find('form').clone();
                          ppp.find('.order_actions.when_book').append(formclone);
                          var fff = pp.prev().find('form');
                          fff.find('div').remove();
                          fff.find('span').remove();
                          if (!pp.hasClass('when_by_room')) {
                            fff.prepend('' +
                              '<a href="#" class="gotech_button add_onemoreroom" id="' + idd + '" onclick="book_room_ajax($(this))" style="float:right;"><?=GetMessage('ADD_ROOM')?></a>');
                          }

                          $('.when_book form').each(function () {
                            $(this).find('div').remove();
                            $(this).find('span').remove();
                          });
                        }
                        else {
                          var newcnt = Number(pp.find('.room_in_cart_cnt').text());
                          newcnt++;
                          pp.find('.room_in_cart_cnt').text(newcnt);
                        }

                        in_cart_num++;

                        $('.order_actions .delete_order').on('click', function () {
                          var cart_id = $(this).data('cart_id');
                          var t = $(this);

                          $.get('/bitrix/components/onlinebooking/onlinebooking/ajax.php?FormType=deleteOrder&cart_id=' + cart_id + '&Hotel=<?=htmlspecialchars($_REQUEST['hotel_id'])?>',
                            function (data) {
                              $('#gotech_search_choose').html(data);
                              $('#gotech_search_choose').css('opacity', 1);
                              $('#gotech_search_choose').show();
                              t.parent().next().show();
                              t.parent().next().find('.bron_button').show();
                              t.parent().remove();
                            });
                          return false;
                        });

                        parent.postMessage('refreshBasket', '*');
                        if (empty_basket)
                          location.href = link;
                      }

                      f.find('input[name="LimitedRooms"]').val(parseInt(f.find('input[name="LimitedRooms"]').val()) - 1)
                      change_available_rooms_count_text(f, parseInt(f.find('input[name="LimitedRooms"]').val()));
                    } else {
                      alert("Возникла ошибка при бронировании");
                    }
                    if (is_agent != "N") {
                      $('div#gotech_online_booking').fadeTo(1, 1);
                      $('div#gotech_online_booking').css('pointer-events', 'auto');
                      $('.gotech_search_result_progress_icon').hide();
                      wait_button.show();
                    }
                  }, 'html');

                }
              }
            } else if(limited_rooms && parseInt(limited_rooms) <= 0) {
              f.find('input[name="LimitedRooms"]').val("0")
              change_available_rooms_count_text(f, 0);
            }
        });

      function change_available_rooms_count_text(el, count) {
        var msg_only = "<?=GetMessage("MSG_ONLY")?>";
        var msg_only_1 = "<?=GetMessage("MSG_ONLY_1")?>";
        var msg_only_room = "<?=GetMessage("MSG_ONLY_ROOM")?>";
        var msg_only_1_room = "<?=GetMessage("MSG_ONLY_1_ROOM")?>";
        var msg_only_rooms = "<?=GetMessage("MSG_ONLY_ROOMS")?>";
        var no_rooms = "<?=GetMessage("CHOOSE_A_LAST_ROOM")?>";
        var language = "<?=$arResult["language"]?>";

        var text = "";
        if (language == 'en') {
          text = msg_only + count + msg_only_room;
        } else {
          if (count == 1) {
            text = msg_only_1 + count + msg_only_1_room;
          } else if (count > 1 && count < 5) {
            text = msg_only + count + msg_only_room;
          } else if (count == 0) {
            text = no_rooms;
          } else {
            text = msg_only + count + msg_only_rooms;
          }
        }
        if (el.parents('.gotech_search_result_room_info').find('.available_rooms_count').length) {
          el.parents('.gotech_search_result_room_info').find('.available_rooms_count').html(text);
        }
      }
    </script>
<? elseif (isset($arResult["SERVICES"]) && !empty($arResult["SERVICES"])): ?>
    <div id="gotech_booking_services">
        <div class="gotech_booking_services_h"><?= GetMessage('ADD_TO_ORDER') ?>:</div>
        <div id="gotech_booking_services_tabs" class="<?if($arResult['SHOW_SERVICES_AS_LIST']):?>view_list<?else:?>view_tile<?endif;?>">
            <div class="views">
                <a href="#" class="tile_view <?if(!$arResult['SHOW_SERVICES_AS_LIST']):?>active<?endif;?>"><?= GetMessage('TILE') ?></a>
                <a href="#" class="list_view <?if($arResult['SHOW_SERVICES_AS_LIST']):?>active<?endif;?>"><?= GetMessage('LIST') ?></a>
            </div>
            <?
            $services = array();
            $have_popular = false;
            foreach ($arResult["SERVICES"] as $key => $service):
                $services[$key] = $service;
                unset($services[$key]["Services"]);
                foreach ($service["Services"] as $sect_key => $sect_service):

                    if ($sect_service['Popular'] == 'Y')
                        $have_popular = true;

                    if (count($sect_service['prices'])):
                        foreach ($sect_service["prices"] as $j => $s):
                            $sect_service['prices'][$j]['available'] = array();
                            foreach ($arResult["ROOMS"] as $k => $room):
                                if (!$s["AgeFrom"] || $room["GuestAge"] >= $s["AgeFrom"] || !$room["GuestAge"]):
                                    if (!$s["AgeTo"] || ($room["IsChild"] && $room["GuestAge"] <= $s["AgeTo"] && $room["GuestAge"] >= $s["AgeFrom"]) || (!$room["IsChild"] && $s["AgeTo"] >= 18)):
                                        $sect_service['prices'][$j]['available'][] = $k;
                                    endif;
                                endif;
                            endforeach;
                        endforeach;
                    else:
                        $s = $sect_service;
                        foreach ($arResult["ROOMS"] as $k => $room):
                            if (!$s["AgeFrom"] || $room["GuestAge"] >= $s["AgeFrom"] || !$room["GuestAge"]):
                                if (!$s["AgeTo"] || ($room["IsChild"] && $room["GuestAge"] <= $s["AgeTo"] && $room["GuestAge"] >= $s["AgeFrom"]) || (!$room["IsChild"] && $s["AgeTo"] >= 18)):
                                    $sect_service['available'][] = $k;
                                endif;
                            endif;
                        endforeach;
                    endif;
                    if (!$sect_service["Code"]) {
                        if (count($sect_service['prices'])) {
                            $sect_service["Code"] = $sect_service['prices'][0]["Code"];
                        }
                    }
                    $services[$key]['Services'][$sect_key] = $sect_service;
                endforeach;
            endforeach;
            $arResult["SERVICES"] = $services;

            $transfer_selected = false;
            foreach ($arParams['SELECTED_SERVICES'] as $k => $v) {
                if ($v['IsTransfer']) {
                    $transfer_selected = true;
                    $tk = $k;
                }
            }
            ?>
            <div class="tab_links">
                <? if ($have_popular): ?>
                    <a href="#popular_services" class="tab_link active"><?= GetMessage('POPULAR_SERVICES') ?></a>
                <? endif; ?>
                <a href="#all_services" class="tab_link<? if (!$have_popular): ?> active<? endif; ?>"><?= GetMessage('ALL_SERVICES') ?></a>
            </div>
            <div style="clear:both;"></div>
            <div class="tabs">
                <div id="all_services" class="tab<? if (!$have_popular): ?> active<? endif; ?>">
                    <div class="services_block">
                        <? $htmlDetailInformation = ""; ?>
                        <? $arResult['SERVICES_POPULAR'] = array(); ?>
                        <? $popup = array(); ?>
                        <? foreach ($arResult["SERVICES"] as $key => $service): ?>
                            <? if (substr($key, 0, 7) == "section"): ?>
                                <? $is_expand = false; ?>
                                <? if ($arResult['SECTION_EXPAND'][$service['Id']] && $arResult['SECTION_EXPAND'][$service['Id']] == 1): ?>
                                    <?
                                        $is_expand = true;
                                    ?>
                                <? endif; ?>
                                <div class="service_category <?if($is_expand):?>opened<?endif;?>">
                                    <div class="service_category_header <? if ($arResult['SECTION_ICONS'][$service['Id']]): ?> svg<? endif ?>">
                                        <? if ($arResult['SECTION_ICONS'][$service['Id']]): ?>
                                            <?
                                                $f = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $arResult['SECTION_ICONS'][$service['Id']]);
                                            ?>
                                            <?= $f ?>
                                        <? endif; ?>
                                        <a href="#" class="slide_toggle <?if($is_expand):?>opened<?endif;?>"><?= ($is_expand ? GetMessage('COLLAPSE') : GetMessage('EXPAND')) ?></a>
                                        <div class="name" onclick="$(this).parent().find('.slide_toggle').click();"><?= $service["Name"] ?></div>
                                        <div class="desc"><?= $service["Description"] ?: '&nbsp;' ?></div>
                                    </div>
                                    <div class="service_category_content <?if($is_expand):?>default_expanded<?endif;?>">
                                        <? $sn_cnt = 0; ?>
                                        <? foreach ($service["Services"] as $sect_key => $sect_service): ?>
                                            <? $isValidService = false ?>
                                            <? $service = $sect_service; ?>
                                            <? foreach ($arResult["ROOMS"] as $k => $room): ?>
                                                <? if (count($service['prices'])): ?>
                                                    <?
                                                    $service["AgeFrom"] = $service['prices'][0]['AgeFrom'];
                                                    $service["AgeTo"] = $service['prices'][0]['AgeTo'];
                                                    ?>
                                                <? endif; ?>
                                                <? if (!$service["AgeFrom"] || $room["GuestAge"] >= $service["AgeFrom"] || !$room["GuestAge"]): ?>
                                                    <? if (!$service["AgeTo"] || ($room["IsChild"] && $room["GuestAge"] <= $service["AgeTo"] && $room["GuestAge"] >= $service["AgeFrom"]) || (!$room["IsChild"] && $service["AgeTo"] >= 18)): ?>
                                                        <? $isValidService = true ?>
                                                    <? endif; ?>
                                                <? endif; ?>
                                            <? endforeach; ?>
                                            <?
                                            if (count($sect_service["prices"])):
                                                foreach ($sect_service["prices"] as $j => $s):
                                                    $n = 0;
                                                    foreach ($arResult["ROOMS"] as $k => $room):
                                                        if ($s['Days'] <= $room['Nights'])
                                                            $n++;
                                                    endforeach;
                                                    if (!$n)
                                                        unset($sect_service["prices"][$j]);
                                                endforeach;
                                                if (!count($sect_service["prices"])) $isValidService = false;
                                            endif;
                                            ?>
                                            <? if ($isValidService): ?>
                                                <?
                                                $sn_cnt++;
                                                if ($sect_service['Popular'] == 'Y')
                                                    $arResult['SERVICES_POPULAR'][] = $sect_service;
                                                ?>
                                                <? $strServiceType = str_replace("+", "x", $sect_service["Code"]) . $sect_key ?>
                                                <? $strServiceType = str_replace(" ", "-", $strServiceType) ?>

                                                <div data-sid="<?= $sect_service["Code"] ?>" <? if ($sect_service['IsTransfer']): ?>data-transfer="Y"<? endif; ?> class="service_item service_num_<?= $sn_cnt ?>">
                                                    <div class="info">
                                                        <div class="img_name">
                                                            <div class="img">
                                                                <? if ($sect_service['Discount']): ?>
                                                                    <div class="discount">-<?= $sect_service['Discount'] ?>% </div>
                                                                <? endif; ?>
                                                                <img src="<?= $sect_service['Picture']['src'] ?>"/>
                                                            </div>
                                                            <div class="name"><?= $sect_service["Name"] ?></div>
                                                        </div>
                                                    </div>
                                                    <? if (count($sect_service["prices"])): ?>
                                                        <? if ($sect_service['IsTransfer']): ?>
                                                            <select style="max-width:95%;">
                                                                <? foreach ($sect_service["prices"] as $k => $v): ?>
                                                                    <option value="<?= $v['Id'] ?>" data-oldprice="<?= $v["OldPrice"]?>" data-price="<?= $v['Price'] ?>">
                                                                        <?= $v['Name'] ?>
                                                                    </option>
                                                                <? endforeach; ?>
                                                            </select>
                                                        <? else: ?>
                                                            <select data-serviceid="<?= $sect_service["Id"] ?>" style="max-width:95%;">
                                                                <? foreach ($sect_service["prices"] as $k => $v): ?>
                                                                    <option value="<?= $v['Id'] ?>" data-oldprice="<?= $v["OldPrice"] ?>" data-price="<?= $v['Price'] ?>">
                                                                        <?plural_form($v['Days'], array(GetMessage('1DAY'), GetMessage('2DAYS'), GetMessage('5DAYS'))) ?>
                                                                    </option>
                                                                <? endforeach; ?>
                                                            </select>
                                                        <? endif ?>
                                                    <? endif; ?>

                                                    <?
                                                    $selected_service = false;
                                                    $people = 0;
                                                    foreach ($arParams['SELECTED_SERVICES'] as $jj => $ll):
                                                        foreach ($sect_service["prices"] as $k => $v):
                                                            if ($v["Code"] == $ll['Code']) {
                                                                $selected_service = true;
                                                                $people++;
                                                            }
                                                        endforeach;
                                                    endforeach;
                                                    ?>
                                                    <div class="price">
                                                        <!-- Price -->
                                                        <?
                                                        $price_nights = 1;
                                                        if ($sect_service["IsPricePerNight"]) {
                                                            $price_nights = $arResult['NIGHTS'];
                                                        }
                                                        $old_price = 0;
                                                        $discount = 0;
                                                        if (empty($sect_service["Price"]) && !count($sect_service["prices"])) {
                                                            $servicePrice = GetMessage("FREE");
                                                            $sprice = 0;
                                                        } else if (!empty($sect_service["prices"])) {
                                                            $keys = array_keys($sect_service["prices"]);
                                                            $firstKey = $keys[0];
                                                            $price_el = $sect_service["prices"][$keys[0]];

                                                            $servicePrice = OnlineBookingSupport::format_price($price_el["Price"], $arResult["CURRENCY_NAME"]);
                                                            $sprice = $price_el["Price"] * $price_nights;

                                                            if ($price_el["OldPrice"]) {
                                                                $old_price = $price_el["OldPrice"] * $price_nights;
                                                            }
                                                            if ($price_el["Discount"]) {
                                                                $discount = $price_el["Discount"];
                                                            }
                                                        } else {
                                                            $servicePrice = OnlineBookingSupport::format_price($sect_service["Price"], $arResult["CURRENCY_NAME"]);
                                                            $sprice = $sect_service["Price"] * $price_nights;
                                                            if ($sect_service["OldPrice"]) {
                                                                $old_price = $sect_service["OldPrice"] * $price_nights;
                                                            }
                                                            if ($sect_service["Discount"]) {
                                                                $discount = $sect_service["Discount"];
                                                            }
                                                        }
                                                        ?>
                                                        <?if($old_price):?>
                                                            <div class="discount">
                                                                <del><?=OnlineBookingSupport::format_price($old_price, $arResult["CURRENCY_NAME"])?></del>
                                                                <span>-<?=$discount?>%</span>
                                                            </div>
                                                        <?endif;?>
                                                        <span class="current_price" data-oldprice="<?=$old_price?>" data-price="<?= $sprice ?>"><?= $servicePrice ?></span>
                                                    </div>

                                                    <span class="gotech_search_result_room_rates_item_button__"<? if ($selected_service): ?> style="display:none;"<? endif; ?>>
														<a href="#" id="<?= "gotech_room_rate_" . $strServiceType ?>-link" class="gotech_button gotech_add_service_popup <?= "gotech_room_rate_" . $strServiceType ?>-link"><?= GetMessage('ADD_TO_ORDER2') ?></a>
													</span>

                                                    <div class="added_info"<? if ($selected_service): ?> style="display:block;"<? else: ?> style="display:none;"<? endif; ?>>
                                                        <?= GetMessage('SERVICE_IN') ?>
                                                        <a href="#" class="cart_a"><?= GetMessage('CART1') ?></a>
                                                        <a href="#" class="del"></a>
                                                        <? if (!$sect_service["IsTransfer"]): ?>
                                                            <br>
                                                            <b>
                                                                <span class="people_num"><?= $people ?></span> <?= GetMessage('PEOPLE') ?>.
                                                            </b>, &nbsp;
                                                            <a href="#" class="change_service <?= "gotech_room_rate_" . $strServiceType ?>-link"><?= GetMessage('CHANGE') ?></a>
                                                        <? else: ?>
                                                            <a href="#" class="change_service <?= "gotech_room_rate_" . $strServiceType ?>-link"><?= GetMessage('CHANGE') ?></a>
                                                        <? endif; ?>
                                                    </div>
                                                </div>
                                                <? if (count($sect_service["prices"])): ?>
                                                    <? foreach ($sect_service["prices"] as $j => $l): ?>
                                                        <div class="service_form_ExSe" id="service_form_ExSe_<?= $l["Id"] ?>">
                                                            <input type="hidden" name="FormType" value="addOrderExSe"/>
                                                            <input type="hidden" name="hotel" value="<?= $sect_service["Hotel"] ?>"/>
                                                            <input type="hidden" name="hotel_id" value="<?= $sect_service["Hotel_id"] ?>"/>
                                                            <input type="hidden" name="id" value="<?= $l["Id"] ?>"/>
                                                            <input type="hidden" name="code" value="<?= $l["Code"] ?>"/>
                                                            <input type="hidden" name="price" value="<?= $l["Price"] ?>"/>
                                                            <input type="hidden" name="Currency" value="<?= $arResult["CURRENCY"] ?>"/>
                                                            <input type="hidden" name="age_from" value="<?= $l["AgeFrom"] ?>"/>
                                                            <input type="hidden" name="age_to" value="<?= $l["AgeTo"] ?>"/>
                                                            <input type="hidden" name="number_to_guest" value="<?= $l["NumberToGuest"] ?>"/>
                                                            <input type="hidden" name="number_to_room" value="<?= $l["NumberToRoom"] ?>"/>
                                                        </div>
                                                    <? endforeach; ?>
                                                <? else: ?>
                                                    <div class="service_form_ExSe" id="service_form_ExSe_<?= $sect_service["Id"] ?>">
                                                        <input type="hidden" name="FormType" value="addOrderExSe"/>
                                                        <input type="hidden" name="hotel" value="<?= $sect_service["Hotel"] ?>"/>
                                                        <input type="hidden" name="hotel_id" value="<?= $sect_service["Hotel_id"] ?>"/>
                                                        <input type="hidden" name="id" value="<?= $sect_service["Id"] ?>"/>
                                                        <input type="hidden" name="code" value="<?= $sect_service["Code"] ?>"/>
                                                        <input type="hidden" name="price" value="<?= $sect_service["Price"] ?>"/>
                                                        <input type="hidden" name="Currency" value="<?= $arResult["CURRENCY"] ?>"/>
                                                        <input type="hidden" name="age_from" value="<?= $sect_service["AgeFrom"] ?>"/>
                                                        <input type="hidden" name="age_to" value="<?= $sect_service["AgeTo"] ?>"/>
                                                        <input type="hidden" name="number_to_guest" value="<?= $sect_service["NumberToGuest"] ?>"/>
                                                        <input type="hidden" name="number_to_room" value="<?= $sect_service["NumberToRoom"] ?>"/>
                                                    </div>
                                                <? endif; ?>
                                                <div <? if ($sect_service['IsTransfer']): ?>data-transfer="Y" <? endif; ?><? if (count($sect_service["prices"])): ?> data-days="Y"<? endif; ?> data-service_id="<?= $sect_service["Id"] ?>" data-code="<?= $strServiceType ?>" id="<?= "gotech_room_rate_" . $strServiceType ?>-popup" class="mfp-hide mfp-align-top zoom-anim-dialog jewelery-popup">
                                                    <div class="header">
                                                        <a href="#" class="close mfp-close" onclick="$.magnificPopup.close();return false;"></a>
                                                        <div class="title">
                                                            <?= GetMessage('ADD_SERVICE2') ?>:
                                                        </div>
                                                    </div>
                                                    <div class="body">
                                                        <div class="service_top">
                                                            <div class="image">
                                                                <img src="<?= $sect_service['Picture']['src'] ?>"/>
                                                            </div>
                                                            <div class="text">
                                                                <div class="service_h"><?= $sect_service["Name"] ?></div>
                                                                <div class="price" data-pernight="<?if($sect_service["IsPricePerNight"]):?>1<?else:?>0<?endif;?>" data-price="<?= $sprice ?>" data-fprice="<?= number_format($sprice, 0, ',', ' ') ?>">
                                                                    <?= OnlineBookingSupport::format_price($sprice, $arResult["CURRENCY_NAME"])?><?if (!$sect_service['IsTransfer'] && $sect_service["IsPricePerNight"]):?><?='<span style="font-size: 15px;font-weight: normal;"> ' . GetMessage("COST_FOR_SHORT") ?><?plural_form($arResult["NIGHTS"], array(GetMessage("1NIGHTS"), GetMessage("2NIGHTS"), GetMessage("5NIGHTS")))?><?= '</span>'; ?><?endif;?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div style="clear:both;"></div>
                                                        <div class="guests_service_block">
                                                            <? if (!$sect_service['IsTransfer']): ?>
                                                                <p>
                                                                    <b><?= GetMessage('SIGN_GUESTS') ?>:</b>
                                                                </p>
                                                                <div class="guests">
                                                                    <?
                                                                        $g = array();
                                                                    ?>
                                                                    <? foreach ($arResult['ROOMS'] as $k => $v): ?>
                                                                        <?
                                                                        $g[$v['RoomNumber']]++;
                                                                        $npos = 0;
                                                                        if (count($sect_service['prices'])):
                                                                            foreach ($sect_service["prices"] as $kk => $vv):
                                                                                if ($vv['Days'] <= $v['Nights'] && in_array($k, $vv['available'])):
                                                                                    $npos++;
                                                                                endif;
                                                                            endforeach;
                                                                        else:
                                                                            $npos = 1;
                                                                        endif;
                                                                        if (!count($sect_service['prices']) && !in_array($k, $sect_service['available'])|| !$npos)
                                                                            continue;
                                                                        ?>
                                                                        <div data-num="<?= $k ?>" data-guid="<?= $v['GUID'] ?>" class="guest guest_<?= $k ?>">
                                                                            <?
                                                                            $checked = false;
                                                                            $who_checked = array();
                                                                            foreach ($arParams['SELECTED_SERVICES'] as $jj => $ll):
                                                                                if ($sect_service["Code"] == $ll['Code'] && $ll['GuestID'] == $k) {
                                                                                    $checked = true;
                                                                                    $who_checked[$ll['GuestID']] = $ll['Id'];
                                                                                }
                                                                            endforeach;
                                                                            ?>

                                                                            <? if (count($sect_service["prices"])): ?>
                                                                                <div style="float:right;<? if (!$checked): ?>display:none;<? endif ?>">
                                                                                    <select>
                                                                                        <? foreach ($sect_service["prices"] as $kk => $vv): ?>
                                                                                            <? if ($vv['Days'] <= $v['Nights'] && in_array($k, $vv['available'])): ?>
                                                                                                <option value="<?= $vv['Id'] ?>" data-price="<?= $vv['Price'] ?>"<? if ($checked && $who_checked[$k] == $vv['Id']): ?> selected<? endif; ?>>
                                                                                                    <?plural_form($vv['Days'], array(GetMessage('1DAY'), GetMessage('2DAYS'), GetMessage('5DAYS'))) ?>
                                                                                                </option>
                                                                                            <? endif; ?>
                                                                                        <? endforeach; ?>
                                                                                    </select>
                                                                                </div>
                                                                            <? endif; ?>

                                                                            <div class="checkbox">
                                                                                <label>
                                                                                    <input type="checkbox" name="" value="<?= $k ?>"<? if ($checked): ?> checked<? endif; ?> />
                                                                                    <span></span>
                                                                                    <span class="fio">
                                                                                        <? if ($v['fullname']): ?>
                                                                                            <?= $v['fullname'] ?>
                                                                                            <span>(<?= $v['Room'] ?>)</span>
                                                                                        <? else: ?>
                                                                                            <?= GetMessage('GUEST') ?> <?= $g[$v['RoomNumber']] ?>
                                                                                            <span>(<?= $v['Room'] ?>, <?= $v['from'] ?>-<?= $v['to'] ?><? if ($v['GuestAge']): ?>, <?plural_form($v['GuestAge'], array(GetMessage('1YEAR'), GetMessage('2YEARS'), GetMessage('5YEARS'))) ?><? endif; ?>)</span>
                                                                                        <? endif; ?>
    																				</span>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    <? endforeach; ?>
                                                                </div>
                                                            <? else: ?>
                                                                <div id="gotech_guests_information_transfer_container" style="display:block;">
                                                                    <?
                                                                    $tdate = htmlspecialcharsEx($_REQUEST["TransferDate"]) ? $_REQUEST["TransferDate"] : $arParams["MIN_TRANSFER"];
                                                                    $tdate = explode(' ', $tdate)[0];
                                                                    ?>
                                                                    <br/>
                                                                    <div class="param_block isdp" style="display:inline-block;width:40%">
                                                                        <label class="pblock_label active"><span><?= GetMessage("TransferDate1") ?></span></label>
                                                                        <input disabled name="TransferDate" readonly autofocus style="cursor: pointer;" placeholder="<?= GetMessage("TransferDate1") ?>" type="text" class="datepicker_input hasDatepicker1" value="<?= $tdate ?>" pattern="[0-9]*"/>
                                                                    </div>
                                                                    <div class="param_block" style="display:inline-block;width:40%">
                                                                        <label class="pblock_label<? if (!empty($_REQUEST["TransferTime"])): ?> active<? endif; ?>"><span><?= GetMessage("TransferTime") ?></span></label>
                                                                        <input name="TransferTime" placeholder="<?= GetMessage("TransferTime") ?>" type="text" <? if (in_array("transfer_time", $arResult["ERRORS"])): ?>class="error_input"<? endif; ?> <? if (!empty($_SESSION['SERVICES_BOOKING'][$tk]["TransferTime"])): ?> value="<?= htmlspecialcharsEx($_SESSION['SERVICES_BOOKING'][$tk]["TransferTime"]) ?>" <? endif; ?> size="5"/>
                                                                    </div>

                                                                    <div class="param_block transfer_select" style="display:block;">
                                                                        <label class="pblock_label active"><?= GetMessage("TransferPlace") ?></label>
                                                                        <select name="transfer_type" <? if (in_array("transfer_type", $arResult["ERRORS"])): ?>class="error_input"<? endif; ?>>

                                                                            <? foreach ($sect_service['prices'] as $jj => $ll): ?>
                                                                                <option data-code="<?= $ll['Code'] ?>" data-price="<?= $ll['Price'] ?>" <? if ((!$jj && !$transfer_selected) || ($transfer_selected && $_SESSION['SERVICES_BOOKING'][$tk]['Id'] == $ll['Id'])): ?>checked<? endif; ?> value="<?= $ll['Id'] ?>">
                                                                                    <?= $ll['Name'] ?>
                                                                                </option>
                                                                            <? endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="param_block" style="display:block;position:relative;top:-5px;">
                                                                        <label style="margin-bottom:-5px;" class="pblock_label<? if (!empty($_REQUEST["TransferRemarks"])): ?> active<? endif; ?>"><span><?= GetMessage("TransferRemarks") ?></span></label>
                                                                        <input name="TransferRemarks" placeholder="<?= GetMessage("TransferRemarks") ?>" type="text" <? if (!empty($_SESSION['SERVICES_BOOKING'][$tk]["TransferRemarks"])): ?> value="<?= htmlspecialcharsEx($_SESSION['SERVICES_BOOKING'][$tk]["TransferRemarks"]) ?>" <? endif; ?> />
                                                                    </div>

                                                                    <div class="param_block num_param_block spinner_block" data-min="0" data-max="4" style="display:block;">
                                                                        <label class="pblock_label active"><span><?= GetMessage("CHILD_SEATS_NUM") ?></span></label>
                                                                        <input type="hidden" name="TransferChildSeats" value="0">
                                                                        <div class="spinner_container">
                                                                            <div class="spinner_prev" onselectstart="return false" onmousedown="return false"></div>
                                                                            <span class="number_field">0</span>
                                                                            <div class="spinner_next" onselectstart="return false" onmousedown="return false"></div>
                                                                        </div>
                                                                    </div>
                                                                    <br/>
                                                                </div>
                                                            <? endif; ?>
                                                            <a href="#" class="gotech_button gotech_add_service_button"><?= GetMessage('ADD_TO_ORDER2') ?></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <? if ($sect_service['Popular'] != 'Y'): ?>
                                                    <script>
                                                        $('.<?="gotech_room_rate_" . $strServiceType?>-link').magnificPopup({
                                                            items: {
                                                                src: '#<?="gotech_room_rate_" . $strServiceType?>-popup',
                                                                type: 'inline'
                                                            },
                                                            fixedContentPos: false,
                                                            fixedBgPos: true,
                                                            overflowY: 'auto',
                                                            closeBtnInside: true,
                                                            preloader: false,
                                                            midClick: true,
                                                            removalDelay: 300,
                                                            mainClass: 'my-mfp-zoom-in',
                                                            callbacks: {
                                                                open: function () {

                                                                    if (typeof dataLayer != 'undefined') {
                                                                        dataLayer.push({
                                                                            event:"UA gtm events",
                                                                            eventCategory:"requestBooking",
                                                                            eventAction: "clickButtonAddToOrder"
                                                                        });
                                                                    }

                                                                    var t1 = Number($('#gotech_booking_services').offset().top);
                                                                    var t2 = Number($('#gotech_booking_services').parent().parent().parent().css('top'));
                                                                    $('[name=TransferDate]').prop('disabled', true);
                                                                    setTimeout(function () {
                                                                        $('[name=TransferDate]').prop('disabled', false);
                                                                    }, 100);
                                                                    $('.jewelery-popup').css('margin-top', t1 - t2);
                                                                }
                                                            }
                                                        });
                                                    </script>
                                                <? endif; ?>
                                            <? endif; ?>
                                        <? endforeach; ?>
                                    </div>
                                </div>
                            <? endif; ?>
                        <? endforeach; ?>
                    </div>
                </div>

                <? if (count($arResult['SERVICES_POPULAR'])): ?>
                    <div id="popular_services" class="tab active">
                        <div class="services_block">
                            <?
                            $sn_cnt = 0;
                            ?>
                            <? foreach ($arResult['SERVICES_POPULAR'] as $key => $sect_service): ?>
                                <?
                                $sn_cnt++;
                                ?>
                                <? $strServiceType = str_replace("+", "x", $sect_service["Code"]) . $key ?>
                                <? $strServiceType = str_replace(" ", "-", $strServiceType) ?>
                                <div data-sid="<?= $sect_service["Code"] ?>" <? if ($sect_service['IsTransfer']): ?>data-transfer="Y"<? endif; ?> class="service_item service_num_<?= $sn_cnt ?>">
                                    <div class="info">
                                        <div class="img_name">
                                            <div class="img">
                                                <? if ($sect_service['Discount']): ?>
                                                    <div class="discount">-<?= $sect_service['Discount'] ?>%</div>
                                                <? endif; ?>
                                                <img src="<?= $sect_service['Picture']['src'] ?>"/>
                                            </div>
                                            <div class="name"><?= $sect_service["Name"] ?></div>
                                        </div>
                                    </div>
                                    <? if (count($sect_service["prices"])): ?>
                                        <? if ($sect_service['IsTransfer']): ?>
                                            <select style="max-width:95%;">
                                                <? foreach ($sect_service["prices"] as $k => $v): ?>
                                                    <option value="<?= $v['Id'] ?>" data-price="<?= $v['Price'] ?>">
                                                        <?= $v['Name'] ?>
                                                    </option>
                                                <? endforeach; ?>
                                            </select>
                                        <? else: ?>
                                            <select style="max-width:95%;">
                                                <? foreach ($sect_service["prices"] as $k => $v): ?>
                                                    <option value="<?= $v['Id'] ?>" data-price="<?= $v['Price'] ?>">
                                                        <?plural_form($v['Days'], array(GetMessage('1DAY'), GetMessage('2DAYS'), GetMessage('5DAYS'))) ?>
                                                    </option>
                                                <? endforeach; ?>
                                            </select>
                                        <? endif ?>
                                    <? endif; ?>
                                    <div class="price">
                                        <!-- Price -->
                                        <?
                                        $price_nights = 1;
                                        if ($sect_service["IsPricePerNight"]) {
                                            $price_nights = $arResult['NIGHTS'];
                                        }
                                        if (empty($sect_service["Price"]) && !count($sect_service["prices"])) {
                                            $servicePrice = GetMessage("FREE");
                                        } else if (!empty($sect_service["prices"])) {
                                            $keys = array_keys($sect_service["prices"]);
                                            $firstKey = $keys[0];
                                            $price_el = $sect_service["prices"][$keys[0]];

                                            $servicePrice = OnlineBookingSupport::format_price($price_el["Price"] * $price_nights, $arResult["CURRENCY_NAME"]);

                                        } else {
                                            $servicePrice = OnlineBookingSupport::format_price($sect_service["Price"] * $price_nights, $arResult["CURRENCY_NAME"]);
                                        }
                                        ?>
                                        <span class="current_price"><?= $servicePrice ?></span>
                                    </div>
                                    <?
                                    $selected_service = false;
                                    $people = 0;
                                    foreach ($arParams['SELECTED_SERVICES'] as $jj => $ll):
                                        foreach ($sect_service["prices"] as $k => $v):
                                            if ($v["Code"] == $ll['Code']) {
                                                $selected_service = true;
                                                $people++;
                                            }
                                        endforeach;
                                    endforeach;
                                    ?>
                                    <span class="gotech_search_result_room_rates_item_button__"<? if ($selected_service): ?> style="display:none;"<? endif; ?>>
										<a href="#" id="<?= "gotech_room_rate_" . $strServiceType ?>-link" class="gotech_button gotech_add_service_popup <?= "gotech_room_rate_" . $strServiceType ?>-link"><?= GetMessage('ADD_TO_ORDER2') ?></a>
									</span>
                                    <div class="added_info"<? if ($selected_service): ?> style="display:block;"<? else: ?> style="display:none;"<? endif; ?>>
                                        <?= GetMessage('SERVICE_IN') ?>
                                        <a href="#" class="cart_a"><?= GetMessage('CART1') ?></a>
                                        <a href="#" class="del"></a>
                                        <? if (!$sect_service['IsTransfer']): ?>
                                            <br>
                                            <b>
                                                <span class="people_num"><?= $people ?></span> <?= GetMessage('PEOPLE') ?> .
                                            </b>, &nbsp;
                                            <a href="#" class="change_service <?= "gotech_room_rate_" . $strServiceType ?>-link"><?= GetMessage('CHANGE') ?></a>
                                        <? else: ?>
                                            <a href="#" class="change_service <?= "gotech_room_rate_" . $strServiceType ?>-link"><?= GetMessage('CHANGE') ?></a>
                                        <? endif; ?>
                                    </div>
                                </div>

                                <script>
                                    $('.<?="gotech_room_rate_" . $strServiceType?>-link').magnificPopup({
                                        items: {
                                            src: '#<?="gotech_room_rate_" . $strServiceType?>-popup',
                                            type: 'inline'
                                        },
                                        fixedContentPos: false,
                                        fixedBgPos: false,
                                        overflowY: 'auto',
                                        closeBtnInside: true,
                                        preloader: false,
                                        midClick: true,
                                        removalDelay: 300,
                                        mainClass: 'my-mfp-zoom-in',
                                        focus: 'input',
                                        callbacks: {
                                            open: function () {

                                                if (typeof dataLayer != 'undefined') {
                                                    dataLayer.push({
                                                        event:"UA gtm events",
                                                        eventCategory:"requestBooking",
                                                        eventAction: "clickButtonAddToOrder"
                                                    });
                                                }

                                                var t1 = Number($('#gotech_booking_services').offset().top);
                                                var t2 = Number($('#gotech_booking_services').parent().parent().parent().css('top'));
                                                $('.jewelery-popup').css('margin-top', t1 - t2);
                                                setTimeout(function () {
                                                    $(document).tap();
                                                }, 15);
                                            }
                                        }
                                    });
                                </script>
                            <? endforeach; ?>
                        </div>
                    </div>
                <? endif; ?>
            </div>
        </div>
        <?
        $tfrom = array();
        $tto = array();
        foreach ($arResult['ROOMS'] as $k => $v):
            $tfrom[] = strtotime(date('d.m.Y', strtotime($v['from'])));
            $tto[] = strtotime(date('d.m.Y', strtotime($v['to'])));
        endforeach;

        $tfrom = min($tfrom);
        $tto = max($tto);
        $d = date('d.m.Y');
        $tfrom1 = date('d.m.Y', $tfrom);
        $d1 = strtotime($d);
        if ($d != $tfrom1) {
            $d1 = $tfrom - 24 * 60 * 60;
            $tfrom = $tfrom - 24 * 60 * 60;
        }
        $d2 = $tto + 24 * 60 * 60;
        $diff = ($d2 - $d1) / (24 * 60 * 60);
        $dates = array();
        $date[] = date('d-m-Y', ($tfrom));
        for ($i = 1; $i <= $diff; $i++) {
            $dd = $tfrom + $i * 24 * 60 * 60;
            $date[] = date('d-m-Y', ($dd));
        }

        $ddd = '';
        foreach ($date as $k => $v) {
            if ($k) $ddd .= ',';
            $ddd .= '"' . $v . '"';
        }
        ?>
        <script>
            var availableDates = [<?=$ddd?>];
            $('[name=TransferDate]').val('<?=date('d.m.Y', ($tfrom + 24 * 60 * 60))?>');

            function available(date) {
                var day = date.getDate();
                if (day < 10) {
                    day = '0' + day;
                }
                var month = date.getMonth() + 1;
                if (month < 10) {
                    month = '0' + month;
                }
                dmy = day + "-" + month + "-" + date.getFullYear();
                if ($.inArray(dmy, availableDates) != -1) {
                    return [true, "", "Available"];
                } else {
                    return [false, "", "unAvailable"];
                }
            }

            setTimeout(function() {
              $('.hasDatepicker1').datepicker({
                  beforeShowDay: available
              });

              $('.service_category_content').each(function () {
                  if (!$(this).html().trim())
                      $(this).parents('.service_category').remove();
              });

              var max = 0;
              $('#popular_services .service_item').each(function () {
                  var h = $(this).find('.info').height() < 166 ? 166 : $(this).find('.info').height();
                  if (h > max)
                      max = h;
              });
              $('#popular_services .service_item .info').height(max);

              $('#all_services').show();
              $('#all_services').css('position', 'absolute');
              $('#all_services').css('left', '-9999px');

              $('#all_services .service_category_content').show();
              $('#all_services .service_category_content:not(.default_expanded)').css('position', 'absolute');
              $('#all_services .service_category_content:not(.default_expanded)').css('left', '-9999px');

              max = 0;
              $('#all_services').find('.service_category_content').each(function () {
                  $(this).find('.service_item').each(function () {
                      var h = $(this).find('.info').height() < 166 ? 166 : $(this).find('.info').height();
                      if (h > max)
                          max = h;
                  });
                  $(this).find('.info').height(max);
              });
              $('#all_services').removeAttr('style');
              $('#all_services .service_category_content:not(.default_expanded)').removeAttr('style');

            }, 1000);
            $('.service_item select').selectric({
                maxHeight: 160,
                disableOnMobile: true,
            });
            $('.jewelery-popup select').selectric({
                maxHeight: 160,
                disableOnMobile: true
            });

            $('[name=transfer_type]').change(function () {
                var price = $(this).find('option:selected').data('price');
                var fprice = number_format(price, 0, ',', ' ');

                var p = $(this).parents('.jewelery-popup');
                var sid = p.data('code');
                p.find('.price').html(format_price(price));
                p.find('.price').attr('data-price', price);
                p.find('.price').attr('data-fprice', fprice);

                $('[data-sid="' + sid + '"] .service_item[data-transfer=Y]').find('select').val($(this).val());
                $('[data-sid="' + sid + '"] .service_item[data-transfer=Y]').find('select').selectric('destroy');
                $('[data-sid="' + sid + '"] .service_item[data-transfer=Y]').find('select').selectric({
                    maxHeight: 160,
                    disableOnMobile: true,
                });
                $('[data-sid="' + sid + '"] .service_item[data-transfer=Y]').find('select').trigger('change');
            });

            $('.tab_link').click(function () {
                $(this).parent().find('.tab_link').removeClass('active');
                $(this).addClass('active');

                $('.tabs .tab').hide();
                var id = $(this).attr('href');
                $('.tab' + id).show();

                return false;
            });

            $('.service_item select').change(function () {
                var sid = $(this).data('serviceid');
                if (!sid) {
                  sid = $(this).parents('.service_item').data('sid');
                }

                var price = $(this).find('option:selected').data('price');
                // $('[name=transfer_type]').val($(this).val());
                // $('[name="transfer_type"]').selectric('change');
                var fprice = number_format(price, 0, ',', ' ');
                if ($(this).parents('.service_item').find('.discount').length) {
                    var oldprice = $(this).find('option:selected').data('oldprice');
                    $(this).parents('.service_item').find('.discount del').html(format_price(oldprice));
                }
                $(this).parents('.service_item').find('.current_price').html(format_price(price));

                var popup = $('.jewelery-popup[data-service_id=' + sid + ']').length ? $('.jewelery-popup[data-service_id=' + sid + ']') : $('.jewelery-popup[data-code=' + sid + ']');
                if (!popup.find('[type=checkbox]:checked').length && sid) {
                    popup.find('select').val($(this).val());
                    popup.find('select').selectric('destroy');
                    popup.find('select').selectric({
                        maxHeight: 160,
                        disableOnMobile: true,
                    });

                    popup.find('.price').html(fprice + ' <span class="gotech_ruble">a</span>');
                    popup.find('.price').attr('data-price', price);
                    popup.find('.price').attr('data-fprice', fprice);
                }
                else if ($(this).parents('.service_item ').data('transfer') == 'Y') {
                    popup.find('select').val($(this).val());
                    popup.find('select').selectric('destroy');
                    popup.find('select').selectric({
                      maxHeight: 160,
                      disableOnMobile: true,
                    });

                    popup.find('.price').html(fprice + ' <span class="gotech_ruble">a</span>');
                    popup.find('.price').attr('data-price', price);
                    popup.find('.price').attr('data-fprice', fprice);
                }
            });

            $('.views > a').click(function () {
                $(this).parent().find('a').removeClass('active');
                $(this).addClass('active');

                if ($(this).hasClass('tile_view')) {
                    $('#gotech_booking_services_tabs').removeClass('view_list');
                    $('#gotech_booking_services_tabs').addClass('view_tile');
                }
                else {
                    $('#gotech_booking_services_tabs').addClass('view_list');
                    $('#gotech_booking_services_tabs').removeClass('view_tile');
                }

                return false;
            });

            $('.jewelery-popup [type=checkbox]').change(function () {
                var p = $(this).parents('.guest');

                p.find('.selectricWrapper').parent().hide();
                if ($(this).is(':checked'))
                    p.find('.selectricWrapper').parent().show();

                if (!p.find('.selectricWrapper').length) {
                    p.find('select').parent().hide();
                    if ($(this).is(':checked'))
                        p.find('select').parent().show();
                }
            });

            $('.jewelery-popup select').change(function () {
                $(this).parents('.jewelery-popup').find('[type=checkbox]').trigger('change');
            });

            $('.jewelery-popup [type=checkbox]').change(function () {
                var p = $(this).parents('.jewelery-popup');
                var price = 0;

                p.find('[type=checkbox]').each(function () {

                    if ($(this).is(':checked')) {
                        var pr = $(this).parents('.guest').find('select option:selected').data('price');
                        pr = Number(pr);
                        price += pr;
                    }

                });

                var nights_text = '';
                if (!price) {
                    price = p.find('.price').data('price');
                    var is_per_night = p.find('.price').data('pernight') == '1' ? true : false;
                    var nights = "<?= $arResult['NIGHTS']?>";
                    if (is_per_night && nights && nights > 1) {
                        nights_text = '<span style="font-size: 15px;font-weight: normal;"> <?= GetMessage("COST_FOR_SHORT")?><?plural_form($arResult['NIGHTS'], array(GetMessage('1NIGHTS'), GetMessage('2NIGHTS'), GetMessage('5NIGHTS'))) ?></span>'
                    }
                }

                p.find('.price').html(format_price(price) + nights_text);
            });
        </script>
        <script>
            $(function () {
                setSelectricForGuestsSpinner();
            });
        </script>
    </div>
<? elseif (!empty($_REQUEST) && $_REQUEST["send_data"] == "Y"): ?>
    <? if (empty($arResult["ERROR"])): ?>
        <div style="padding-left: 27%; left:0" class="error_text"><?= GetMessage("NO_NUMBER"); ?></div>
        <? $APPLICATION->IncludeComponent("onlinebooking:reservation.available", "", array("hotel_id" => $_REQUEST["hotel_id"], "PeriodFrom" => $_REQUEST["PeriodFrom"], "PeriodTo" => $_REQUEST["PeriodTo"])); ?>
    <? else: ?>
        <div style="text-align: center;" class="error_text"><?= $arResult["ERROR"]; ?></div>
    <? endif; ?>
<? endif; ?>
<script>
    setRoomRatesRadioButtonsHandler();
    setSelectricForCurrency();
    $(document).ready(function () {
        $("#gotech_search_result_header").attr("data-previous-value", $(".gotech_search_result_header_period_item_selected").find("input[name='period']").val());
        if ($('.gotech_search_result_header_period_item').length > 0 && $('.gotech_search_result_header_period_item_selected').length > 0) {
            $($('.gotech_search_result_header_period_item')[0]).before($('.gotech_search_result_header_period_item_selected')[0]);
        }
        $('body').on('click', '.order_block .order_item', function (e) {
            $(this).parents('.room_rates_block').find('.order_item:not(.not_change_class)').removeClass('active');
            $(this).addClass('active');

            if (e.target && $(e.target).hasClass('room_info_image')) {

            } else {
                return false;
            }
        });
        $('.room_info_image_wrapper').magnificPopup({
            type: 'image',
            closeOnContentClick: true,
            closeBtnInside: false,
            fixedContentPos: true,
            mainClass: 'mfp-no-margins mfp-with-zoom',
            image: {
                verticalFit: true
            },
            zoom: {
                enabled: true,
                duration: 300
            }
        });
        $('.show_rooms_button').click(function(e) {
            e.preventDefault();
            $('#' + $(this).data('id')).toggle();
        })
    });
</script>
