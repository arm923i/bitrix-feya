<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? __IncludeLang($_SERVER["DOCUMENT_ROOT"] . $this->__folder . "/lang/" . OnlineBookingSupport::getLanguage() . "/template.php"); ?>
<?
$zakaz = array();
$total_price = 0;
foreach ($arResult['GUESTS'] as $k => &$v):
    if ($v['RoomInfoName']) {
        $zakaz[] = '«' . GetMessage('ROOM') . ' ' . $v['RoomInfoName'] . ' - ' . $v['RoomName'] . '»';
    } else {
        $zakaz[] = '«' . GetMessage('ROOM') . ' ' . ($k + 1) . ' - ' . $v['RoomName'] . '»';
    }
//    if(isset($arResult['LOGGED_USER_DISCOUNT'])) {
//        $v['Amount'] = $v['Amount']*(100 - $arResult['LOGGED_USER_DISCOUNT'])/100;
//    }
    $total_price += $v['Amount'];
endforeach;

$arUser['NAME'] = $_SESSION['sn_name'];
$arUser['LAST_NAME'] = $_SESSION['sn_last_name'];
$arUser['SECOND_NAME_NAME'] = $_SESSION['sn_second_name'];
$arUser['EMAIL'] = $_SESSION['sn_email'];

$rn = array();
$rn0 = array();
$prev = 0;
foreach ($arResult['GUESTS'] as $kg => &$vg):
    $rn[$kg] = count($vg['Accommodation']) + $prev;
    $prev = $rn[$kg];
    $rn0[$kg] = $vg['RoomName'];
endforeach;

$error_temp = $arResult["ERRORS"];
$pay_fio_key = array_search('pay_fio', $error_temp);
if ($pay_fio_key !== false) {
    unset($error_temp[$pay_fio_key]);
}
$email_key = array_search('email', $error_temp);
if ($email_key !== false) {
    unset($error_temp[$email_key]);
}
$phone_key = array_search('phone', $error_temp);
if ($phone_key !== false) {
    unset($error_temp[$phone_key]);
}
?>
<input type="hidden" name="final_bonuses_payment_trans_id">
<input type="hidden" name="final_bonuses_payment_sum">
<div id="gotech_basket_header" class="page_header">
    <div class="h"><?= GetMessage('ORDER_HEADER') ?></div>
    <div class="gotech_basket_header_info">
        <?= GetMessage('YOU_CHOSED') ?>
        <b><?= implode(', ', $zakaz) ?> <?= GetMessage('FOR_SUM') ?> <?= number_format($total_price, 0, '.', ' ') ?>
            </b>.
        <br/>
        <?= GetMessage('TO_BOOK_THEM') ?> <b><?= GetMessage('ENTER_GUEST_DATA') ?></b>.
    </div>


    <div class="gotech_error_text" style="padding:15px 0px 0px 0px;">
        <? if (!empty($arResult["WS_ERROR"])): ?>
            <?= $arResult['ERROR_TEXT'] ?>
        <? endif; ?>
        <? if (count($arResult["ERRORS"]) > 0): ?>

            <? if (in_array("agree", $arResult["ERRORS"]) && count($arResult["ERRORS"]) > 1 && 0): ?>
                <?= GetMessage("FILL_ERROR") ?>
            <? elseif (count($arResult["ERRORS"]) > 0): ?>
                <? if (in_array('pay_fio', $arResult["ERRORS"]) || in_array('email', $arResult["ERRORS"]) || in_array('phone', $arResult["ERRORS"])): ?>
                    <?= GetMessage('PAYER_DATA_ERROR') ?>:
                    <ul>
                        <? if (in_array('pay_fio', $arResult["ERRORS"])): ?>
                            <li><?= GetMessage('FIO_PAYER') ?></li>
                        <? endif ?>
                        <? if (in_array('email', $arResult["ERRORS"])): ?>
                            <li><?= GetMessage('PAYER_EMAIL') ?></li>
                        <? endif ?>
                        <? if (in_array('phone', $arResult["ERRORS"])): ?>
                            <li><?= GetMessage('PAYER_PHONE') ?></li>
                        <? endif ?>
                    </ul>
                <? endif; ?>

                <? if (count($error_temp)): ?>
                    <div><?= GetMessage('FIELDS_REQ') ?>:</div>
                    <ul>
                        <?

                        foreach ($arResult["ERRORS"] as $err_k => $err_v):
                            if (in_array($err_v, array('pay_fio', 'email', 'phone'))) continue;
                            $last = substr($err_v, -1);
                            $prev = 0;
                            foreach ($rn as $kk => $vv):
                                $n = $last - $prev;
                                $prev = $vv;
                                if ($last < $vv):
                                    if (strstr($err_v, 'surname')) $field = GetMessage('LAST_NAME');
                                    else if (strstr($err_v, 'secondname')) $field = GetMessage('SECOND_NAME');
                                    else if (strstr($err_v, 'name')) $field = GetMessage('FIRST_NAME');
                                    else if (strstr($err_v, 'birthday')) $field = GetMessage('BDAY');
                                    else if (strstr($err_v, 'phone')) $field = GetMessage('PHONE');
                                    else if (strstr($err_v, 'email')) $field = GetMessage('EMAIL');
                                    else if (strstr($err_v, 'transfer_date')) $field = GetMessage('TransferDate');
                                    if ($field) {
                                        echo '<li>' . $field . ' ' . GetMessage('FOR') . ' ' . $rn0[$kk] . ' - ' . GetMessage('GUEST') . ' ' . ($n + 1) . '</li>';
                                    }
                                    break;
                                endif;
                            endforeach;
                        endforeach;
                        ?>
                    </ul>
                    <? if (in_array("agree", $arResult["ERRORS"]) || in_array("subscribe", $arResult["ERRORS"])): ?>
                        <?= '<div>' . GetMessage("DO_AGREE") . '</div>' ?>
                    <? endif; ?>
                <? endif; ?>

            <? endif; ?>
            <?
            function age_search($haystack)
            {
                $needle = 'age';
                return (strpos($haystack, $needle) !== false);
            }

            $matches = array_filter($arResult["ERRORS"], 'age_search');
            ?>
            <? if (count($matches) > 0): ?>
                <br>
                <?= GetMessage("AGES_IS_FAILED") ?>
            <? endif; ?>
        <? endif; ?>
    </div>

</div>
<div id="gotech_basket">


    <form action="<?= $APPLICATION->GetCurPageParam() ?>" method="post" name="book_apply">
        <input type="hidden" name="SessionID" value="<?=$_REQUEST['SessionID']?>">
        <input type="hidden" name="UserID" value="<?=$_REQUEST['UserID']?>">
        <input type="hidden" name="utm_source" value="<?=$_REQUEST['utm_source']?>">
        <input type="hidden" name="utm_medium" value="<?=$_REQUEST['utm_medium']?>">
        <input type="hidden" name="utm_campaign" value="<?=$_REQUEST['utm_campaign']?>">

        <input type="hidden" name="hotel_id" value="<?= $arResult["HOTEL"]["ID"] ?>"/>
        <input type="hidden" name="language" value="<?= OnlineBookingSupport::getLanguage() ?>"/>
        <?
        $totalSum = 0;
        $firstDaySum = 0;
        $totalSumDesc = "";

        foreach ($_SESSION['SERVICES_BOOKING'] as $jj => $ll):
            $totalSum += $arResult['SERVICES_PRICES'][$ll['Id']];
        endforeach;


        ?>
        <? $rooms = 0; ?>
        <? $guests = 0; ?>
        <? $arRooms = array(); ?>
        <div class="gotech_basket_items">
            <? $firstGuest = true; ?>
            <? foreach ($arResult['GUESTS'] as $key_number => $room): ?>
                <? $firstGuest2 = true; ?>
                <?
                if ($_REQUEST['send_booking'] == 'Y')
                    $firstGuest2 = false;
                ?>
                <? $guid = OnlineBookingSupport::GUID(); ?>
                <?
                $nights = (strtotime($room['PeriodTo']) - strtotime($room['PeriodFrom'])) / (60 * 60 * 24);
                if ($arResult['HOURS_ENABLE'])
                    $nights = (strtotime($room['PeriodTo']) - strtotime($room['PeriodFrom'])) / (60 * 60);
                ?>
                <div class="gotech_basket_item" data-nights="<?= $nights ?>">
                    <div class="photo">
                        <img src="<?= $room['Picture']['src'] ?>"/>
                    </div>
                    <div class="info">

                        <div class="room_right_block">
                            <a href="#" class="delete_order gotech_guests_information_item_header_delete"
                               data-unique="<?= $room['unique'] ?>"><?= GetMessage('DELETE_ROOM') ?></a>
                            <input type="hidden" name="id" value="<?= $room["Id"] ?>">
                            <div class="room_price">
                                <?= OnlineBookingSupport::format_price($room["Amount"], $arResult["CURRENCY_NAME"]) ?>
                            </div>
                            <div class="room_params">
                                <? if ($arResult['HOURS_ENABLE']): ?>
                                    <span><?= GetMessage('FOR2') ?>
                                        <b><?plural_form($nights, array(GetMessage('1HOUR'), GetMessage('2HOURS'), GetMessage('5HOURS'))) ?></b>,</span>
                                    <span><b><?plural_form($room["visitors"], array(GetMessage('1GUEST'), GetMessage('2GUESTS'), GetMessage('5GUESTS'))) ?></b></span>
                                <? else: ?>
                                    <span><?= GetMessage('FOR2') ?>
                                        <b><?plural_form($nights, array(GetMessage('1NIGHT'), GetMessage('2NIGHTS'), GetMessage('5NIGHTS'))) ?></b>,</span>
                                    <span><b><?plural_form($room["visitors"], array(GetMessage('1GUEST'), GetMessage('2GUESTS'), GetMessage('5GUESTS'))) ?></b></span>
                                <? endif; ?>

                            </div>
                        </div>

                        <div
                            class="room_name"><? if ($room["RoomCode"]): ?><?= GetMessage('ROOM') . " " . $room["RoomInfoName"] . " - " ?><? endif; ?><?= $room['RoomName'] ?></div>
                        <div class="in_out">
                            <? if ($arResult['HOURS_ENABLE']): ?>
                                <b><?= GetMessage("ARRIVAL") ?> <?= $room["PeriodFrom"] ?></b>
                                <span>|</span>
                                <b><?= GetMessage("DEPARTURE") ?> <?= $room["PeriodTo"] ?></b>
                            <? else: ?>
                                <b><?= GetMessage("ARRIVAL") ?> <?= $room["PeriodFrom"] ?>
                                    , <?= $arResult['HOTEL_TIME_IN'] ?></b>
                                <span>|</span>
                                <b><?= GetMessage("DEPARTURE") ?> <?= $room["PeriodTo"] ?>
                                    , <?= $arResult['HOTEL_TIME_OUT'] ?></b>
                            <? endif; ?>
                            <br/>
                            <? if ($room['RoomInfoText']): ?>
                                <div><?= $room['RoomInfoText'] ?></div>
                            <? endif; ?>
                            <? if ($room['RoomRateCodeDesc']): ?>
                                <?= GetMessage('RATE') ?>: <b><?= $room['RoomRateCodeDesc'] ?></b>
                            <? endif; ?>
                        </div>
                        <?
                        $rate = $arResult['ROOM_RATES'][$room['RoomRateCode']];
                        ?>
                        <? if (count($rate['PROPS']['SERVICES_NAMES']['VALUE'])): ?>

                            <div class="things">

                                <? foreach ($rate['PROPS']['SERVICES_NAMES']['VALUE'] as $k => $vs): ?>
                                    <div class="thing"
                                         style="background:url(<?= $rate['IMAGES'][$k]['src'] ?>) no-repeat 0px 4px;">
                                        <?= $vs ?>
                                    </div>
                                <? endforeach; ?>

                            </div>
                        <? endif; ?>

                        <div class="guests_block">

                            <div class="h"><?= GetMessage('ENTER_GUEST_DATA2') ?>:</div>

                            <? $accTypeCounter = 0; ?>
                            <? foreach ($room["Accommodation"] as $key_ac => $accomodation): ?>
                                <? $num = $key_ac + 1; ?>
                                <?
                                $uid = $key_number . "_" . $key_ac;
                                $surname = "";
                                $firstname = "";
                                $secondname = "";
                                $arRooms[$key_number . "_" . $key_ac]["Room"] = $room["RoomName"];
                                $arRooms[$key_number . "_" . $key_ac]["from"] = $room['PeriodFrom'];
                                $arRooms[$key_number . "_" . $key_ac]["to"] = $room['PeriodTo'];
                                ?>
                                <div data-key="<?= $key_number . "_" . $key_ac ?>"
                                     class="guest_block_data gotech_guests_information_item_content_guest"<? if ((!$arResult["FIO"] && $accTypeCounter > 0) || ($key_ac && 0)): ?> style="display:none;"<? endif ?>>

                                    <? if ($arUser['NAME']): ?>
                                        <input type="hidden" class="sn_user_name" value="<?= $arUser['NAME'] ?>"/>
                                    <? endif; ?>
                                    <? if ($arUser['LAST_NAME']): ?>
                                        <input type="hidden" class="sn_user_last_name"
                                               value="<?= $arUser['LAST_NAME'] ?>"/>
                                    <? endif; ?>
                                    <? if ($arUser['SECOND_NAME']): ?>
                                        <input type="hidden" class="sn_user_second_name"
                                               value="<?= $arUser['SECOND_NAME'] ?>"/>
                                    <? endif; ?>
                                    <? if ($arUser['EMAIL']): ?>
                                        <input type="hidden" class="sn_user_email" value="<?= $arUser['EMAIL'] ?>"/>
                                    <? endif; ?>

                                    <? //=var_dump($accomodation)?>

                                    <div class="h<? if ($firstGuest2): ?> gray<? endif; ?>">
                                        <?= GetMessage('GUEST') ?> <?= $key_ac + 1 ?>
                                        <? if ($accomodation['Age']): ?>
                                            (<?= $accomodation['Age'] ?> <?= GetMessage('AGES') ?>)
                                        <? endif ?>
                                    </div>

                                    <div class="guest_data">
                                        <div
                                            class="name"></div> <? /*<a href="#" class="change_order"><?=GetMessage('CHANGE')?></a>*/ ?>

                                        <div class="services">
                                            <? $i = 0; ?>
                                            <? foreach ($_SESSION['SERVICES_BOOKING'] as $jj => $ll): ?>
                                                <? if ($uid == $ll['GuestID'] || ($uid == '0_0' && $ll['IsTransfer'])): ?>
                                                    <? $i++; ?>
                                                    <div
                                                        class="service service<?= $ll['Id'] ?><? if ($ll['IsTransfer']): ?> service_transfer<? endif; ?>"
                                                        data-code="<?= $ll['Code'] ?>" data-uid="<?= $ll['Id'] ?>"
                                                        data-guest="<?= $ll['GuestID'] ?>" data-id="<?= $ll['Id'] ?>">
                                                        <span
                                                            class="pn"><?= $i ?></span>. <?= $arResult['SERVICES_NAMES'][$ll['Id']] ?>
                                                        | <b><?= $arResult['SERVICES_PRICES'][$ll['Id']] ?> <span
                                                                class="gotech_ruble">a</span></b>
                                                        <a href="#" class="delete"></a><input type="hidden"
                                                                                              name="service_price_<?= $ll['Id'] ?>"
                                                                                              class="service_price"
                                                                                              value="<?= $arResult['SERVICES_PRICES'][$ll['Id']] ?>">
                                                    </div>
                                                    <? if ($ll['IsTransfer']): ?>
                                                        <input type="hidden" name="is_transfer" value="1"/>
                                                    <? endif; ?>
                                                <? endif ?>
                                            <? endforeach; ?>
                                        </div>

                                    </div>

                                    <div class="param_blocks <? if ($firstGuest): ?> first_person<? endif; ?>">

                                        <? if ((!!$accomodation["IsChild"]) || (!!$accomodation["Age"]) || ($accomodation["ClientAgeTo"] > 0 && $accomodation["ClientAgeTo"] < 18)) {
                                            if ($accomodation["Age"] == 1) {
                                                $placeholder = GetMessage("KID") . " (" . $accomodation["Age"] . GetMessage("1AGES") . ")";
                                            } elseif ($accomodation["Age"] >= 2 && $accomodation["Age"] < 5) {
                                                $placeholder = GetMessage("KID") . " (" . $accomodation["Age"] . GetMessage("2AGES") . ")";
                                            } elseif ($accomodation["Age"] >= 5) {
                                                $placeholder = GetMessage("KID") . " (" . $accomodation["Age"] . GetMessage("AGES") . ")";
                                            } else {
                                                $placeholder = GetMessage("KID") . " (" . GetMessage("AGES_LESS_THAN_ONE") . ")";
                                            }
                                        } else {
                                            $placeholder = GetMessage("ADULT");
                                        } ?>
                                        <? foreach ($arResult["FIELD"] as $key => $FIELD): ?>

                                            <? $value = "" ?>
                                            <? if (isset($_REQUEST[$key . "_" . $key_number . "_" . $key_ac]) && !empty($_REQUEST[$key . "_" . $key_number . "_" . $key_ac])) {
                                                $value = htmlspecialcharsEx($_REQUEST[$key . "_" . $key_number . "_" . $key_ac]);

                                                if ($key == 'surname') {
                                                    $surname = $value;
                                                } elseif ($key == 'name') {
                                                    $firstname = $value;
                                                } elseif ($key == 'secondName') {
                                                    $secondname = $value;
                                                }
                                            } elseif ($firstRow) {
                                                if ($key == 'surname') {
                                                    $value = isset($arResult["CLIENT"]->ClientLastName) ? $arResult["CLIENT"]->ClientLastName : "";
                                                    if (!$value && isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)) {
                                                        $value = isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientLastName) ? $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientLastName : "";
                                                    }
                                                    $surname = $value;
                                                } elseif ($key == 'name') {
                                                    $value = isset($arResult["CLIENT"]->ClientFirstName) ? $arResult["CLIENT"]->ClientFirstName : "";
                                                    if (!$value && isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)) {
                                                        $value = isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientFirstName) ? $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientFirstName : "";
                                                    }
                                                    $firstname = $value;
                                                } elseif ($key == 'secondName') {
                                                    $value = isset($arResult["CLIENT"]->ClientSecondName) ? $arResult["CLIENT"]->ClientSecondName : "";
                                                    if (!$value && isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)) {
                                                        $value = isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientSecondName) ? $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientSecondName : "";
                                                    }
                                                    $secondname = $value;
                                                } elseif ($key == 'birthday') {
                                                    $value = isset($arResult["CLIENT"]->ClientBirthDate) && $arResult["CLIENT"]->ClientBirthDate != '0001-01-01T00:00:00' ? OnlineBookingSupport::getDateFromFormat($arResult["CLIENT"]->ClientBirthDate) : "";
                                                    if (!$value && isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)) {
                                                        $value = (isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientBirthDate) && $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientBirthDate != '0001-01-01T00:00:00') ? OnlineBookingSupport::getDateFromFormat($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientBirthDate) : "";
                                                    }
                                                }
                                            } elseif ($firstGuest) {
                                                if ($key == 'surname') {
                                                    $value = isset($arResult["CLIENT"]->ClientLastName) ? $arResult["CLIENT"]->ClientLastName : "";
                                                    if (!$value && isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)) {
                                                        $value = isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientLastName) ? $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientLastName : "";
                                                    }
                                                    $surname = $value;
                                                } elseif ($key == 'name') {
                                                    $value = isset($arResult["CLIENT"]->ClientFirstName) ? $arResult["CLIENT"]->ClientFirstName : "";
                                                    if (!$value && isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)) {
                                                        $value = isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientFirstName) ? $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientFirstName : "";
                                                    }
                                                    $firstname = $value;
                                                } elseif ($key == 'secondName') {
                                                    $value = isset($arResult["CLIENT"]->ClientSecondName) ? $arResult["CLIENT"]->ClientSecondName : "";
                                                    if (!$value && isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)) {
                                                        $value = isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientSecondName) ? $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientSecondName : "";
                                                    }
                                                    $secondname = $value;
                                                } elseif ($key == 'birthday') {
                                                    $value = isset($arResult["CLIENT"]->ClientBirthDate) && $arResult["CLIENT"]->ClientBirthDate != '0001-01-01T00:00:00' ? OnlineBookingSupport::getDateFromFormat($arResult["CLIENT"]->ClientBirthDate) : "";
                                                    if (!$value && isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)) {
                                                        $value = (isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientBirthDate) && $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientBirthDate && $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientBirthDate != '01.01.0001') ? OnlineBookingSupport::getDateFromFormat($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientBirthDate) : "";
                                                    }
                                                }
                                            }
                                            ?>
                                            <? if ($key == 'citizenship' && !empty($arResult["CITIZENSHIP"])): ?>
                                                <div
                                                    class="param_block gotech_guests_information_item_content_guest_citizenship"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                    <span
                                                        class="gotech_guests_information_item_content_guest_citizenship_spinner_label pblock_label active"><span><?= $FIELD ?></span></span>
                                                    <select name="citizenship_<?= $key_number ?>_<?= $key_ac ?>"
                                                            id="<?= $key_number ?>_<?= $key_ac ?>"
                                                            class="gotech_guests_information_item_content_guest_citizenship_spinner">
                                                        <? foreach ($arResult["CITIZENSHIP"] as $k => $citizenship): ?>
                                                            <option
                                                                value="<?= $k ?>" <? if (isset($_REQUEST["citizenship_" . $key_number . "_" . $key_ac]) && $_REQUEST["citizenship_" . $key_number . "_" . $key_ac] == $k): ?> selected="selected" <? elseif ($k == $arResult["CITIZENSHIP_SELECTED"]): ?> selected="selected" <? endif; ?>><?= $citizenship ?></option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                            <? endif; ?>
                                            <? if ($key == 'surname'): ?>
                                                <div
                                                    class="param_block gotech_guests_information_item_content_guest_lastname">
                                                    <span
                                                        class="gotech_guests_information_item_content_guest_lastname_input_label pblock_label"><span><?= GetMessage('LAST_NAME') ?></span></span>
                                                    <input type="text" <? if ($arResult['FIO']): ?>required<? endif; ?>
                                                           name="<?= $key ?>_<?= $key_number ?>_<?= $key_ac ?>"
                                                           placeholder="<?= GetMessage('LAST_NAME') ?>"
                                                           class="last_name <? if ($firstGuest2): ?> fln<? endif; ?> <? if (in_array("surname" . $key_ac, $arResult["ERRORS"])): ?>gotech_guests_information_item_content_guest_lastname_input_error<? else: ?>gotech_guests_information_item_content_guest_lastname_input<? endif; ?>"
                                                           value="<?= $value ?>">
                                                </div>
                                            <? endif; ?>
                                            <? if ($key == 'name'): ?>
                                                <div
                                                    class="param_block gotech_guests_information_item_content_guest_firstname"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                    <span
                                                        class="gotech_guests_information_item_content_guest_firstname_input_label pblock_label"><span><?= GetMessage('FIRST_NAME') ?></span></span>
                                                    <input type="text" <? if ($arResult['FIO']): ?>required<? endif; ?>
                                                           name="first<?= $key ?>_<?= $key_number ?>_<?= $key_ac ?>"
                                                           placeholder="<?= GetMessage('FIRST_NAME') ?>"
                                                           class="name <? if (in_array("name" . $key_ac, $arResult["ERRORS"])): ?>gotech_guests_information_item_content_guest_firstname_input_error<? else: ?>gotech_guests_information_item_content_guest_firstname_input<? endif; ?>"
                                                           value="<?= $value ?>">
                                                </div>
                                            <? endif; ?>
                                            <? if ($key == 'secondName'): ?>
                                                <div
                                                    class="param_block gotech_guests_information_item_content_guest_secondname"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                    <span
                                                        class="gotech_guests_information_item_content_guest_secondname_input_label pblock_label"><span><?= GetMessage('SECOND_NAME') ?></span></span>
                                                    <input type="text"
                                                           <? if ($arResult['FIO'] && 0): ?>required<? endif; ?>
                                                           name="patronymic<? //=$key?>_<?= $key_number ?>_<?= $key_ac ?>"
                                                           placeholder="<?= GetMessage('SECOND_NAME') ?>"
                                                           class="second_name gotech_guests_information_item_content_guest_secondname_input"
                                                           value="<?= htmlspecialchars($_REQUEST["patronymic_" . $key_number . "_" . $key_ac]) ? htmlspecialchars($_REQUEST["patronymic_" . $key_number . "_" . $key_ac]) : $value ?>">
                                                </div>
                                            <? endif; ?>

                                            <? if ($key == 'birthday'): ?>
                                                <div
                                                    class="param_block gotech_guests_information_item_content_guest_birthday"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                    <span
                                                        class="gotech_guests_information_item_content_guest_birthday_input_label pblock_label"><span><?= GetMessage('BDAY') ?></span></span>
                                                    <input type="text"
                                                           <? if ($arResult['DATE_OF_BIRTH_NECESSARY']): ?>required<? endif; ?>
                                                           placeholder="<?= GetMessage('BDAY') ?>"
                                                           name="<?= $key ?>_<?= $key_number ?>_<?= $key_ac ?>"
                                                           class="bday <? if (in_array("birthday" . $guests, $arResult["ERRORS"])): ?>gotech_guests_information_item_content_guest_birthday_input_error<? else: ?>gotech_guests_information_item_content_guest_birthday_input<? endif; ?>"
                                                           value="<?= $value ?>" pattern="[0-9]*">
                                                </div>
                                            <? endif; ?>
                                        <? endforeach; ?>
                                        <? if ($arResult["INPUT_GUEST_PASSPORT_DATA"]): ?>
                                            <br>
                                            <div
                                                class="param_block gotech_guests_information_item_content_guest_document_type"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                <span
                                                    class="pblock_label active gotech_guests_information_item_content_guest_document_type_label"><span><?= GetMessage("DOCUMENT_TYPE")?></span></span>
                                                <select
                                                    name="ClientIdentityDocumentType_<?= $key_number ?>_<?= $key_ac ?>"
                                                    id="<?= $key_number ?>_<?= $key_ac ?>"
                                                    class="gotech_guests_information_item_content_guest_document_type_spinner document_type_selector">
                                                    <? foreach ($arResult["DOC_TYPES"] as $k => $doc_type): ?>
                                                        <option
                                                            value="<?= $k ?>" <? if (isset($_REQUEST["ClientIdentityDocumentType_" . $key_number . "_" . $key_ac]) && $_REQUEST["ClientIdentityDocumentType_" . $key_number . "_" . $key_ac] == $k): ?> selected="selected"<? endif; ?>><?= $doc_type ?></option>
                                                    <? endforeach; ?>
                                                </select>
                                            </div>
                                            <div
                                                class="param_block gotech_guests_information_item_content_guest_document_series"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                <span
                                                    class="pblock_label gotech_guests_information_item_content_guest_document_series_input_label"><span><?= GetMessage("PASSPORT_SERIES")?></span></span>
                                                <input type="text"
                                                       class="gotech_guests_information_item_content_guest_document_series_input"
                                                       name="ClientIdentityDocumentSeries_<?= $key_number ?>_<?= $key_ac ?>"
                                                       placeholder="<?= GetMessage("PASSPORT_SERIES")?>"
                                                       value="<?= htmlspecialchars($_REQUEST["ClientIdentityDocumentSeries_" . $key_number . "_" . $key_ac]) ?>">
                                            </div>
                                            <div
                                                class="param_block gotech_guests_information_item_content_guest_document_number"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                <span
                                                    class="pblock_label gotech_guests_information_item_content_guest_document_number_input_label"><span><?= GetMessage("PASSPORT_NUMBER")?></span></span>
                                                <input type="text"
                                                       class="gotech_guests_information_item_content_guest_document_number_input"
                                                       name="ClientIdentityDocumentNumber_<?= $key_number ?>_<?= $key_ac ?>"
                                                       placeholder="<?= GetMessage("PASSPORT_NUMBER")?>"
                                                       value="<?= htmlspecialchars($_REQUEST["ClientIdentityDocumentNumber_" . $key_number . "_" . $key_ac]) ?>">
                                            </div>
                                            <div
                                                class="param_block gotech_guests_information_item_content_guest_document_date"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                <span
                                                    class="pblock_label gotech_guests_information_item_content_guest_document_date_input_label"><span><?= GetMessage("DOCUMENT_DATE")?></span></span>
                                                <input type="text" placeholder="<?= GetMessage("DOCUMENT_DATE")?>"
                                                       name="ClientIdentityDocumentIssueDate_<?= $key_number ?>_<?= $key_ac ?>"
                                                       class="guest_passport_valid_day gotech_guests_information_item_content_guest_document_date_input"
                                                       value="" pattern="[0-9]*">
                                            </div>
                                            <div
                                                    class="param_block gotech_guests_information_item_content_guest_document_unit_code"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                <span
                                                        class="pblock_label gotech_guests_information_item_content_guest_document_unit_code_input_label"><span><?= GetMessage("PASSPORT_UNIT_CODE")?></span></span>
                                                <input type="text"
                                                       class="gotech_guests_information_item_content_guest_document_unit_code_input"
                                                       name="ClientIdentityDocumentUnitCode_<?= $key_number ?>_<?= $key_ac ?>"
                                                       placeholder="<?= GetMessage("PASSPORT_UNIT_CODE")?>"
                                                       value="<?= htmlspecialchars($_REQUEST["ClientIdentityDocumentUnitCode_" . $key_number . "_" . $key_ac]) ?>">
                                            </div>
                                            <div
                                                    class="param_block gotech_guests_information_item_content_guest_document_issued_by"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                <span
                                                        class="pblock_label gotech_guests_information_item_content_guest_document_issued_by_input_label"><span><?= GetMessage("PASSPORT_ISSUED_BY")?></span></span>
                                                <input type="text"
                                                       class="gotech_guests_information_item_content_guest_document_issued_by_input"
                                                       name="ClientIdentityDocumentIssuedBy_<?= $key_number ?>_<?= $key_ac ?>"
                                                       placeholder="<?= GetMessage("PASSPORT_ISSUED_BY")?>"
                                                       value="<?= htmlspecialchars($_REQUEST["ClientIdentityDocumentIssuedBy_" . $key_number . "_" . $key_ac]) ?>">
                                            </div>

                                        <? endif; ?>
                                        <? if ($arResult["INPUT_GUEST_ADDRESS"]): ?>
                                            <br>
                                            <div
                                                class="param_block gotech_guests_information_item_content_guest_address"
                                                style="width: 100%; <? if ($firstGuest2): ?>display:none;<? endif; ?>">
                                                <span
                                                    class="pblock_label gotech_guests_information_item_content_guest_address_input_label"><span><?= GetMessage("CUSTOMER_ADDRESS")?></span></span>
                                                <input type="text"
                                                       class="gotech_guests_information_item_content_guest_address_input"
                                                       name="address_<?= $key_number ?>_<?= $key_ac ?>"
                                                       placeholder="<?= GetMessage("CUSTOMER_ADDRESS")?>"
                                                       value="<?= htmlspecialchars($_REQUEST["address" . $key_number . "_" . $key_ac]) ?>">
                                            </div>
                                        <? endif; ?>
                                        <? if ($firstGuest && 0): ?>

                                            <div id="gotech_guests_information_contacts_content_phone"
                                                 class="param_block"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                <span id="gotech_guests_information_contacts_content_phone_input_label"
                                                      class="pblock_label"><span><?= GetMessage("PHONE") ?><? if ($arResult["PHONE_NECESSARY"] == '1'): ?><? endif; ?></span></span>
                                                <input
                                                    class="phone <? if (in_array("phone_" . $key_number . "_" . $key_ac, $arResult["ERRORS"])): ?>gotech_guests_information_contacts_content_phone_input_error<? endif; ?>"
                                                    id="gotech_guests_information_contacts_content_phone_input"
                                                    placeholder="<?= GetMessage("PHONE") ?>"
                                                    name="phone_<?= $key_number ?>_<?= $key_ac ?>"
                                                    <? if (isset($_REQUEST["phone_" . $key_number . "_" . $key_ac])): ?>value="<?= htmlspecialchars($_REQUEST["phone_" . $key_number . "_" . $key_ac]) ?>"<? endif; ?>>
                                            </div>
                                            <div id="gotech_guests_information_contacts_content_email"
                                                 class="param_block"<? if ($firstGuest2): ?> style="display:none;"<? endif; ?>>
                                                <span id="gotech_guests_information_contacts_content_email_input_label"
                                                      class="pblock_label"><span><?= GetMessage("EMAIL") ?><? if ($arResult["EMAIL_NECESSARY"] == '1'): ?><? endif; ?></span></span>
                                                <input
                                                    class="email <? if (in_array("email_" . $key_number . "_" . $key_ac, $arResult["ERRORS"])): ?>gotech_guests_information_contacts_content_email_input_error<? endif; ?>"
                                                    id="gotech_guests_information_contacts_content_email_input"
                                                    placeholder="<?= GetMessage("EMAIL") ?>"
                                                    name="email_<?= $key_number ?>_<?= $key_ac ?>"
                                                    <? if (isset($_REQUEST["email_" . $key_number . "_" . $key_ac])): ?>value="<?= htmlspecialchars($_REQUEST["email_" . $key_number . "_" . $key_ac]) ?>"<? endif; ?>>
                                            </div>
                                            <? if (isset($arResult["SMS_IS_ENABLED"]) && $arResult["SMS_IS_ENABLED"] == 1 && 0): ?>

                                                <input type="checkbox" name="sms"
                                                       id="gotech_guests_information_contacts_content_sms_checkbox"
                                                       class="css-checkbox" <? if (!empty($_REQUEST["sms"])): ?> checked="checked" <? endif; ?>/>
                                                <label for="gotech_guests_information_contacts_content_sms_checkbox"
                                                       class="css-label"><?= GetMessage("SMS") ?></label>
                                            <? endif; ?>
                                        <? endif; ?>

                                        <input type="hidden" class="guest_info_saved"
                                               name="guest_info_saved_<?= $key_number ?>_<?= $key_ac ?>" value="N"/>


                                        <input type="hidden"
                                               name="typeOfAccommodation_<?= $key_number ?>_<?= $key_ac ?>"
                                               class="field-text" value="<?= $accomodation["Code"] ?>"/>
                                        <input type="hidden" name="Age_<?= $key_number ?>_<?= $key_ac ?>"
                                               class="field-text" value="<?= $accomodation["Age"] ?>"/>
                                        <input type="hidden" name="RoomCode_<?= $key_number ?>_<?= $key_ac ?>"
                                               class="field-text" value="<?= $room["RoomCode"] ?>"/>
                                        <input type="hidden" name="RoomType_<?= $key_number ?>_<?= $key_ac ?>"
                                               class="field-text" value="<?= $room["RoomTypeCode"] ?>"/>
                                        <input type="hidden" name="RoomRateCode_<?= $key_number ?>_<?= $key_ac ?>"
                                               class="field-text" value="<?= $room["RoomRateCode"] ?>"/>
                                        <input type="hidden"
                                               name="PaymentMethodCodesAllowedOnline_<?= $key_number ?>_<?= $key_ac ?>"
                                               class="field-text"
                                               value="<?= $room["PaymentMethodCodesAllowedOnline"] ?>"/>
                                        <input type="hidden" name="periodFrom_<?= $key_number ?>_<?= $key_ac ?>"
                                               class="field-text" value="<?= $room["PeriodFrom"] ?>"/>
                                        <input type="hidden" name="periodTo_<?= $key_number ?>_<?= $key_ac ?>"
                                               class="field-text" value="<?= $room["PeriodTo"] ?>"/>
                                        <input type="hidden" name="allotmentCode_<?= $key_number ?>_<?= $key_ac ?>"
                                               class="field-text" value="<?= $room["AllotmentCode"] ?>"/>
                                        <input type="hidden" name="guid_<?= $key_number ?>_<?= $key_ac ?>"
                                               class="field-text" value="<?= $guid ?>"/>
                                        <? $firstRow = false; ?>
                                        <? $firstGuest = false; ?>
                                        <? $guests += 1 ?>
                                    </div>
                                </div>
                                <?
                                $fullname = trim($surname . " " . $firstname . " " . $secondname);
                                if (!empty($fullname)) {
                                    $arRooms[$key_number . "_" . $key_ac]["Guest"] = $fullname;
                                } else {
                                    $arRooms[$key_number . "_" . $key_ac]["Guest"] = $placeholder;
                                }
                                $arRooms[$key_number . "_" . $key_ac]["GUID"] = $guid;
                                $arRooms[$key_number . "_" . $key_ac]["GuestAge"] = $accomodation["Age"];
                                $arRooms[$key_number . "_" . $key_ac]["IsChild"] = (!!$accomodation["IsChild"]) || (!!$accomodation["Age"]) || ($accomodation["ClientAgeTo"] > 0 && $accomodation["ClientAgeTo"] < 18);
                                $arRooms[$key_number . "_" . $key_ac]["Nights"] = $nights;
                                ?>


                                <? $accTypeCounter++; ?>
                            <? endforeach; ?>
                            <? if (!$arResult["FIO"] && count($room["Accommodation"]) > 1): ?>
                                <div class="add_guest_info_block">
                                    <a href="#" class="add_guest_info">
                                        <span class="color_scheme_text">+</span>
                                        <span><?= GetMessage('ADD_GUEST_INFO') ?></span>
                                    </a>
                                </div>
                            <? endif; ?>
                            <div class="add_guest_block" style="display:none;">

                                <? if (count($room['Accommodation']) > 1 && 0): ?>
                                    <a href="#" class="add_guest">
                                        <span class="color_scheme_text">+</span>
                                        <span><?= GetMessage('ADD_GUEST') ?></span>
                                    </a>
                                <? endif; ?>
                                <? /*<a href="#" class="save_guest_data"><?=GetMessage('SAVE')?></a>*/ ?>
                            </div>
                        </div>

                    </div>


                    <div style="clear:both;"></div>
                </div>

                <?
                $totalSum += FloatVal($room["Amount"]);
                $firstDaySum += FloatVal($room["FirstDaySum"]);
                $totalSumDesc = strval(number_format($totalSum, 0, ',', ' ')) . " " . $room["Currency"];
                $rooms += 1;
                $firstGuest2 = false;
                ?>

            <? endforeach; ?>
            <?
            $currencyCode = $room["Currency"];
            if (strpos($room["Currency"], "gotech_ruble") !== false) {
                $currencyCode = "643";
            }
            ?>
            <input type="hidden" name="total_sum" value="<?= $totalSum ?>"/>
            <input type="hidden" name="total_sum_desc" value="<?= $totalSumDesc ?>"/>
            <input type="hidden" name="first_day_sum" value="<?= $firstDaySum ?>"/>
            <input type="hidden" name="total_sum_currency" value="<?= $currencyCode ?>"/>
            <input type="hidden" name="total_sum_currency_desc" value="<?= $room['Currency'] ?>"/>

        </div>
        <div style="clear:both;"></div>

        <?
        $path = COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER');
        ?>
        <div class="gotech_basket_bottom">
            <a href="" class="gotech_button add_another_room" onclick="add_room(event, '<?=$path?>?hotel=<?=$arResult['HOTEL']['ID']?>')">
                <span>+</span>
                <?= GetMessage('ADD_ROOM') ?>
            </a>


        </div>

        <div style="clear:both;"></div>

        <? if (count($arResult["Services"])): ?>
            <div id="gotech_guests_information_services">
                <div class="gotech_services">
                    <? $APPLICATION->IncludeComponent("onlinebooking:reservation.find", "", array("TYPE" => "SERVICE", 'MIN_TRANSFER' => $arResult["MIN_TRANSFER"], "SERVICES" => $arResult["Services"], "ROOMS" => $arRooms, "CURRENCY" => $number["Currency"], "HOTEL_ID" => $arResult["HOTEL"]["ID"], "SELECTED_SERVICES" => $_SESSION["SERVICES_BOOKING"], "NIGHTS" => $nights)); ?>
                </div>
            </div>
        <? endif; ?>



        <? if ((isset($arResult["NUMBER_OF_CAR"]) && $arResult["NUMBER_OF_CAR"] == 1) || (isset($arResult["SHOW_REMARKS"]) && $arResult["SHOW_REMARKS"] == 1)): ?>
            <div id="gotech_basket_additional_info">
                <div class="h"><?= GetMessage("ADDITIONAL_INFO") ?></div>

                <? if (isset($arResult["NUMBER_OF_CAR"]) && $arResult["NUMBER_OF_CAR"] == 1): ?>
                    <div class="param_block auto">
                        <label class="pblock_label"><span><?= GetMessage("NUMBER_OF_CAR") ?></span></label>
                        <input type="text" name="avto_number"
                               placeholder="<?= GetMessage("NUMBER_OF_CAR") ?>" <? if (!empty($_REQUEST["avto_number"])): ?> value="<?= htmlspecialcharsEx($_REQUEST["avto_number"]) ?>" <? endif; ?> />
                    </div>

                <? endif; ?>
                <? if (isset($arResult["SHOW_REMARKS"]) && $arResult["SHOW_REMARKS"] == 1): ?>
                    <div class="param_block special_requests">
                        <label class="pblock_label"><span><?= GetMessage("ADDITIONAL_WISHES") ?></span></label>
                        <input type="text" name="additional_wishes" placeholder="<?= GetMessage("ADDITIONAL_WISHES") ?>"
                               <? if (!empty($_REQUEST["additional_wishes"])): ?>value="<?= htmlspecialcharsEx($_REQUEST["additional_wishes"]) ?>"<? endif; ?> />
                    </div>
                <? endif; ?>

            </div>
        <? endif; ?>


        <div id="gotech_basket_bottom2">
            <? if ($arResult["IS_AGENT"]): ?>
                <? /*
			<input type="hidden" name="email" value="<?=$USER->GetEmail();?>" />
			<input type="hidden" name="another_pay_fio" value="<?=$USER->GetLogin();?>" />
			*/ ?>
            <? else: ?>
                <div id="gotech_payer">

                    <div class="h"><?= GetMessage('PAYER') ?></div>
                    <div class="param_block fio">
                        <label class="pblock_label active" style="margin-bottom:15px;"><span><?= GetMessage('FIO_PAYER') ?></span></label>
                        <select name="pay_fio" <?if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)):?>style="pointer-events: none"<?endif;?>>
                            <? if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer) && $_SESSION["AUTH_CLIENT_DATA"]->CustomerName):?>
                                <option selected="selected" value="<?= $_SESSION["AUTH_CLIENT_DATA"]->CustomerName ?>"><?= $_SESSION["AUTH_CLIENT_DATA"]->CustomerName ?></option>
                            <?else:?>
                                <? if ($arUser['NAME'] && $arUser['LAST_NAME']): ?>
                                    <option value="<?= $arUser['LAST_NAME'] ?> <?= $arUser['NAME'] ?>"><?= $arUser['LAST_NAME'] ?> <?= $arUser['NAME'] ?></option>
                                <? else: ?>
                                    <option value="Гость 1" class="first_user_option">Гость 1</option>
                                <? endif; ?>
                                <option class="last_option" value="" <? if (isset($_REQUEST["another_pay_fio"]) && !empty($_REQUEST["another_pay_fio"])): ?>selected="selected"<? endif; ?>>
                                    <?= GetMessage("ANOTHER_PAYER")?>
                                </option>
                            <? endif; ?>
                        </select>

                    </div>
                    <div class="param_block another_lastname" style="display:none;">
                        <label class="pblock_label"><span><?= GetMessage("PAYER_LASTNAME")?></span></label>
                        <input required type="text" name="another_pay_lastname" placeholder="<?= GetMessage("PAYER_LASTNAME")?>"
                               <? if (in_array("pay_fio", $arResult["ERRORS"])): ?>class="err"
                               <? endif; ?><? if (isset($_REQUEST["pay_fio"])): ?>value="<?= htmlspecialchars($_REQUEST["another_pay_lastname"]) ?>"<? endif; ?> />
                    </div>
                    <div class="param_block another_name" style="display:none;">
                        <label class="pblock_label"><span><?= GetMessage("FIRST_NAME")?></span></label>
                        <input required type="text" name="another_pay_name" placeholder="<?= GetMessage("FIRST_NAME")?>"
                               <? if (in_array("pay_fio", $arResult["ERRORS"])): ?>class="err"
                               <? endif; ?><? if (isset($_REQUEST["pay_fio"])): ?>value="<?= htmlspecialchars($_REQUEST["another_pay_name"]) ?>"<? endif; ?> />
                    </div>
                    <div class="param_block another_secondname" style="display:none;">
                        <label class="pblock_label"><span><?= GetMessage("SECOND_NAME")?></span></label>
                        <input required type="text" name="another_pay_secondname" placeholder="<?= GetMessage("SECOND_NAME")?>"
                               <? if (in_array("pay_fio", $arResult["ERRORS"])): ?>class="err"
                               <? endif; ?><? if (isset($_REQUEST["pay_fio"])): ?>value="<?= htmlspecialchars($_REQUEST["another_pay_secondname"]) ?>"<? endif; ?> />
                    </div>

                    <div class="param_block phone">
                        <label class="pblock_label"><span><?= GetMessage('PHONE_NUMBER') ?></span></label>
                        <input required type="text" name="phone" placeholder="<?= GetMessage('PHONE_NUMBER') ?>" <? if (in_array("phone", $arResult["ERRORS"])): ?>class="err"<? endif; ?><?if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)):?>value="<?= htmlspecialchars($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientPhone) ?>"<?else:?><? if (isset($_REQUEST["phone"])): ?>value="<?= htmlspecialchars($_REQUEST["phone"]) ?>"<? endif; ?><? endif; ?> />
                        <div class="tip">
                            <?= $arResult['LABEL_PHONE'] ?>
                        </div>
                    </div>

                    <div class="param_block email">
                        <label class="pblock_label"><span>E-mail</span></label>
                        <input required type="text" name="email" placeholder="E-mail" <? if (in_array("email", $arResult["ERRORS"])): ?>class="err"<? endif; ?><?if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IsCustomer) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)):?>value="<?= htmlspecialchars($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientEMail) ?>"<?else:?><? if (isset($_REQUEST["email"])): ?>value="<?= htmlspecialchars($_REQUEST["email"]) ?>"<? endif; ?><? endif; ?> />
                        <div class="tip">
                            <?= $arResult['LABEL_EMAIL'] ?>
                        </div>
                    </div>

                    <? if ($arResult["INPUT_PAYER_PASSPORT_DATA"]): ?>
                        <div class="another_passport_data_block" style="display:none;">
                            <div class="param_block gotech_guests_information_item_content_guest_birthday">
                    <span
                        class="gotech_guests_information_item_content_guest_birthday_input_label pblock_label"><span><?= GetMessage('BDAY') ?></span></span>
                                <input type="text" placeholder="<?= GetMessage('BDAY') ?>" name="payer_birthday"
                                       <? if (isset($_REQUEST["payer_birthday"])): ?>value="<?= htmlspecialchars($_REQUEST["payer_birthday"]) ?>"<? endif; ?>
                                       class="bday <? if (in_array("payer_birthday", $arResult["ERRORS"])): ?>gotech_guests_information_item_content_guest_birthday_input_error<? else: ?>gotech_guests_information_item_content_guest_birthday_input<? endif; ?>"
                                       pattern="[0-9]*">
                            </div>

                            <div class="param_block gotech_guests_information_item_content_guest_citizenship">
                    <span
                        class="gotech_guests_information_item_content_guest_citizenship_spinner_label pblock_label active"><span><?= $FIELD ?></span></span>
                                <select name="payer_citizenship" id="payer_citizenship"
                                        class="gotech_guests_information_item_content_guest_citizenship_spinner">
                                    <? foreach ($arResult["CITIZENSHIP"] as $k => $citizenship): ?>
                                        <option
                                            value="<?= $k ?>" <? if (isset($_REQUEST["payer_citizenship"]) && $_REQUEST["payer_citizenship"] == $k): ?> selected="selected" <? elseif ($k == $arResult["CITIZENSHIP_SELECTED"]): ?> selected="selected" <? endif; ?>><?= $citizenship ?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <br>
                            <div class="param_block gotech_guests_information_item_content_guest_document_type">
                    <span
                        class="pblock_label active gotech_guests_information_item_content_guest_document_type_label"><span><?= GetMessage("DOCUMENT_TYPE")?></span></span>
                                <select name="payer_document_type" id="payer_document_type"
                                        class="gotech_guests_information_item_content_guest_document_type_spinner document_type_selector">
                                    <? foreach ($arResult["DOC_TYPES"] as $k => $doc_type): ?>
                                        <option
                                            value="<?= $k ?>" <? if (isset($_REQUEST["payer_document_type"]) && $_REQUEST["payer_document_type"] == $k): ?> selected="selected"<? endif; ?>><?= $doc_type ?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="param_block gotech_guests_information_item_content_guest_document_series">
                                <span
                                    class="pblock_label gotech_guests_information_item_content_guest_document_series_input_label"><span><?= GetMessage("PASSPORT_SERIES")?></span></span>
                                <input type="text"
                                       class="gotech_guests_information_item_content_guest_document_series_input"
                                       name="payer_passport_series" placeholder="<?= GetMessage("PASSPORT_SERIES")?>"
                                       value="<?= htmlspecialchars($_REQUEST["payer_passport_series"]) ?>">
                            </div>
                            <div class="param_block gotech_guests_information_item_content_guest_document_number">
                                <span
                                    class="pblock_label gotech_guests_information_item_content_guest_document_number_input_label"><span><?= GetMessage("PASSPORT_NUMBER")?></span></span>
                                <input type="text"
                                       class="gotech_guests_information_item_content_guest_document_number_input"
                                       name="payer_passport_number" placeholder="<?= GetMessage("PASSPORT_NUMBER")?>"
                                       value="<?= htmlspecialchars($_REQUEST["payer_passport_number"]) ?>">
                            </div>
                        </div>
                        <div class="param_block gotech_guests_information_item_content_guest_document_date">
                            <span
                                class="pblock_label gotech_guests_information_item_content_guest_document_date_input_label"><span><?= GetMessage("DOCUMENT_DATE")?></span></span>
                            <input type="text" placeholder="<?= GetMessage("DOCUMENT_DATE")?>" name="payer_document_date"
                                   class="guest_passport_valid_day gotech_guests_information_item_content_guest_document_date_input"
                                   <? if (isset($_REQUEST["payer_document_date"])): ?>value="<?= htmlspecialchars($_REQUEST["payer_document_date"]) ?>"<? endif; ?>
                                   pattern="[0-9]*">
                        </div>
                        <div class="param_block gotech_guests_information_item_content_guest_document_issued_by">
                <span
                    class="pblock_label gotech_guests_information_item_content_guest_document_issued_by_input_label"><span><?= GetMessage("PASSPORT_ISSUED_BY")?></span></span>
                            <input type="text"
                                   class="gotech_guests_information_item_content_guest_document_issued_by_input"
                                   name="payer_passport_issued_by" placeholder="<?= GetMessage("PASSPORT_ISSUED_BY")?>"
                                   value="<?= htmlspecialchars($_REQUEST["payer_passport_issued_by"]) ?>">
                        </div>
                    <? endif; ?>
                    <? if ($arResult["INPUT_PAYER_ADDRESS"]): ?>
                        <div class="another_address_block" style="display:none;">
                            <div class="param_block gotech_guests_information_item_content_guest_address"
                                 style="width: 100%">
                        <span
                            class="pblock_label gotech_guests_information_item_content_guest_address_input_label"><span><?= GetMessage("CUSTOMER_ADDRESS")?></span></span>
                                <input type="text" style="width: 400px; max-width: 100%;"
                                       class="gotech_guests_information_item_content_guest_address_input"
                                       name="payer_address"
                                       placeholder="<?= GetMessage("CUSTOMER_ADDRESS")?>" value="<?= htmlspecialchars($_REQUEST["payer_address"]) ?>">
                            </div>
                        </div>
                    <? endif; ?>
                </div>
            <? endif; ?>


            <div class="total_price">
                <?= GetMessage('TOTAL') ?>: <span><?= $totalSumDesc ?></span>
            </div>
            <div class="order_short_list">
                <?= GetMessage('Rooms') ?> <b><?= count($arResult['GUESTS']) ?></b> / <?= GetMessage('Guests') ?>
                <b><?= $guests ?></b> / <?= GetMessage('Services') ?>: <b class="services_num"><?= count($_SESSION['SERVICES_BOOKING']) ?></b>
                <?if(isset($_SESSION["BONUS_AMOUNT"])):?>
                <br>
                <b><?= GetMessage("BONUS_AMOUNT")?><?=$_SESSION["BONUS_AMOUNT"]?></b>
                <?endif;?>
            </div>

            <div class="terms">
                <div class="term">
                    <label
                        <? if (in_array("agree", $arResult["ERRORS"])): ?>style="color: red;font-weight: bold;"<? endif; ?>>
                        <input type="checkbox" name="agree" value="1"/><span></span>
                        <? if ($arResult['AGREE_BRON_TEXT']['TEXT']): ?>
                            <?= $arResult['AGREE_BRON_TEXT']['TEXT'] ?>
                        <? else: ?>
                            <?= GetMessage('I_AGREE_WITH') ?> <a href="<?= $arResult["Terms"] ?>"
                                                                 target="_blank"><b><u><?= GetMessage('TERMS_OF_RES') ?></u></b></a>
                        <? endif; ?>
                    </label>
                </div>
                <?if ($arResult["AGREEMENT_ID"]):?>
                <div class="term <? if (in_array("subscribe", $arResult["ERRORS"])): ?>error<? endif; ?>">
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:main.userconsent.request",
                        "",
                        array(
                            "ID" => $arResult["AGREEMENT_ID"],
                            "IS_CHECKED" => "N",
                            "AUTO_SAVE" => "N",
                            "IS_LOADED" => "Y",
                            "INPUT_NAME" => "subscribe",
                            "REPLACE" => array(
                                'button_caption' => (!$arResult["SHOW_PAYMENT_METHODS"] || $arResult['IS_AGENT']) ? GetMessage('BOOKING') : GetMessage('PAY'),
                                'fields' => $arResult["FIELDS_FOR_AGREEMENT"]
                            )
                        )
                    );?>
                </div>
                <?else:?>
                    <input type="hidden" name="subscribe" value="1">
                <?endif;?>
                <div class="term">
                    <? if (isset($arResult["SMS_IS_ENABLED"]) && $arResult["SMS_IS_ENABLED"] == 1): ?>
                        <label>
                            <input type="checkbox" name="sms" checked
                                   id="gotech_guests_information_contacts_content_sms_checkbox"
                                   class="css-checkbox" <? if (!empty($_REQUEST["sms"])): ?> checked="checked" <? endif; ?>/><span></span> <?= GetMessage("SMS") ?>
                        </label>
                    <? endif; ?>
                </div>
            </div>


            <? if (COption::GetOptionString('gotech.hotelonline', 'includePaySys') == 1 && $arResult["SHOW_PAYMENT_METHODS"] && !$arResult['IS_AGENT']): ?>
                <? if (isset($arResult["PAYMENT_METHODS"]) && !empty($arResult["PAYMENT_METHODS"])): ?>
                    <?
                        $bonuses_payment_is_enabled = isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer) && isset($_SESSION["BONUS_AMOUNT"]) && intVal($_SESSION["BONUS_AMOUNT"]) > 0;
                    ?>
                    <div class="payments_mobile" style="display:block;margin-top:40px;">
                        <?
                        $fullSum = $totalSum;
                        $isFirst = true;
                        $is_legal = false;
                        ?>

                        <? foreach ($arResult["PAYMENT_METHODS"] as $key => $method): ?>
                            <?
                                $full_sum_to_pay = 0;
                            ?>
                            <label class="payment" data-citizenship='<?=json_encode($method["CITIZENSHIP"])?>'>
                                <? if ($method["FIRST_NIGHT"] == "Yes"): ?>
                                    <? if (FloatVal($firstDaySum) <= FloatVal($fullSum)): ?>
                                        <? $id = $method["PAYMENT_SYSTEM"] . "-" . (FloatVal($firstDaySum) * 100) . "-" . $method["IS_CASH"] . "-" . $method["IS_RECEIPT"] . "-" . $method["IS_LEGAL"] . "-" . $key; ?>
                                        <input type="radio" <? if ($method["IS_CASH"]): ?>data-cash="Y"<? endif; ?>
                                               name="payment_methods_radiobuttons"
                                               <? if ($method["IS_LEGAL"]): ?>class="is_legal"<? endif; ?>
                                               id="<?= $id ?>"
                                               <? if ($isFirst || (isset($_REQUEST["payment_methods_radiobuttons"]) && $_REQUEST["payment_methods_radiobuttons"] == $id)): ?>checked<? endif; ?>
                                               value="<?= $id ?>"/>
                                        <span><?= $method["NAME"] . " (<span class='payment_price'>" . (number_format(Round(FloatVal($firstDaySum), 2), 2, ',', ' ')) . " " . $number["Currency"] . "</span>)" ?></span>
                                        <input type="hidden" name="payment_discount" value=""><input type="hidden"
                                                                                                     name="is_first_night"
                                                                                                     value="Y">
                                        <? $full_sum_to_pay = FloatVal($firstDaySum)?>
                                    <? endif; ?>
                                <? elseif (!empty($method["DISCOUNT"])): ?>
                                    <? $discount_sum = (FloatVal($fullSum) * IntVal($method["DISCOUNT"])) / 100 ?>
                                    <? if ($discount_sum > FloatVal($fullSum)): ?>
                                        <? $discount_sum = FloatVal($fullSum) ?>
                                    <? endif; ?>
                                    <? $id = $method["PAYMENT_SYSTEM"] . "-" . ($discount_sum * 100) . "-" . $method["IS_CASH"] . "-" . $method["IS_RECEIPT"] . "-" . $method["IS_LEGAL"] . "-" . $key; ?>
                                    <input type="radio" <? if ($method["IS_CASH"]): ?>data-cash="Y"<? endif; ?>
                                           name="payment_methods_radiobuttons"
                                           <? if ($method["IS_LEGAL"]): ?>class="is_legal"<? endif; ?> id="<?= $id ?>"
                                           <? if ($isFirst || (isset($_REQUEST["payment_methods_radiobuttons"]) && $_REQUEST["payment_methods_radiobuttons"] == $id)): ?>checked<? endif; ?>
                                           value="<?= $id ?>"/>
                                    <span><?= $method["NAME"] . " (<span class='payment_price'>" . (number_format(Round($discount_sum, 2), 2, ',', ' ')) . " " . $number["Currency"] . "</span>)" ?></span>
                                    <input type="hidden" name="payment_discount" value="<?= $method["DISCOUNT"] ?>">
                                    <input type="hidden" name="is_first_night" value="">
                                    <? $full_sum_to_pay = $discount_sum?>
                                <? else: ?>

                                    <? $id = $method["PAYMENT_SYSTEM"] . "-" . (FloatVal($fullSum) * 100) . "-" . $method["IS_CASH"] . "-" . $method["IS_RECEIPT"] . "-" . $method["IS_LEGAL"] . "-" . $key; ?>

                                    <input type="radio" <? if ($method["IS_CASH"]): ?>data-cash="Y"<? endif; ?>
                                           name="payment_methods_radiobuttons"
                                           <? if ($method["IS_LEGAL"]): ?>class="is_legal"<? endif; ?> id="<?= $id ?>"
                                           <? if ($isFirst || (isset($_REQUEST["payment_methods_radiobuttons"]) && $_REQUEST["payment_methods_radiobuttons"] == $id)): ?>checked<? endif; ?>
                                           value="<?= $id ?>"/>
                                    <span><?= $method["NAME"] . " (<span class='payment_price'>" . (number_format(Round(FloatVal($fullSum), 2), 2, ',', ' ')) . " " . $number["Currency"] . "</span>)" ?></span>
                                    <input type="hidden" name="payment_discount" value=""><input type="hidden"
                                                                                                 name="is_first_night"
                                                                                                 value="">
                                    <? $full_sum_to_pay = FloatVal($fullSum)?>
                                <? endif; ?>
                                <? $isFirst = false; ?>
                                <? if ($method["IS_LEGAL"]): ?>
                                    <? $is_legal = true ?>
                                <? endif; ?>
                                <div>
                                    <p>
                                        <?= $method["DETAILS"]; ?>
                                    </p>
                                    <a href="#"
                                       class="gotech_button prebookapply<? if ($is_legal_): ?> is_legal_button<? endif; ?>" <? if ($is_legal): ?> style="display:none;"<? endif; ?>>
                                        <? if ($is_legal): ?>
                                            <?= GetMessage('SEND'); ?>
                                        <? elseif ($method["IS_CASH"]): ?>
                                            <?= GetMessage('PAY_AFTER') ?>
                                        <? else: ?>
                                            <?= GetMessage('PAY') ?>
                                        <? endif; ?>
                                    </a>
                                </div>
                            </label>
                        <? endforeach; ?>

                        <script>
                            $(function() {
                                if ($('[name="citizenship_0_0"]').length) {
                                    $('[name="citizenship_0_0"]').change(function() {
                                        var iso = $(this).val();
                                        var is_clicked = false;
                                        $('.payments_mobile label').each(function() {
                                            var citizenships = $(this).data('citizenship');
                                            if (citizenships && citizenships.length) {
                                                var is_finded = false;
                                                citizenships.forEach(function(el) {
                                                    if (el == iso) {
                                                        is_finded = true;
                                                    }
                                                });
                                                if (is_finded) {
                                                    $(this).show();
                                                    if (!is_clicked) {
                                                        $(this).click();
                                                        is_clicked = true;
                                                    }
                                                } else {
                                                    $(this).hide();
                                                }
                                            }
                                        })
                                    })
                                    $('[name="citizenship_0_0"]').change();
                                }
                            })
                        </script>

                        <? if ($is_legal): ?>
                            <div class="gotech_guests_information_footer_payment_methods_customer_data">
                                <div class="h">
                                    <?= GetMessage("CUSTOMER_FIND_YOUR_ORGANIZATION") ?>
                                </div>
                                <div class="param_block">
                                    <label class="pblock_label"><span><?= GetMessage("CUSTOMER_SEARCH") ?>
                                            *</span></label>
                                    <input type="text" name="customer_search"
                                           placeholder="<?= GetMessage("CUSTOMER_SEARCH") ?>*">
                                    <? /*<a href="#" class="show_legal_fields" style="display:block;float:right;font-size:12px;">ввести вручную</a>*/ ?>
                                </div>
                                <div class="legal_fields" style="display:none;">
                                    <div class="h">
                                        <?= GetMessage("CUSTOMER_OR_FILL_INPUT_FIELD") ?>
                                    </div>
                                    <div class="param_block">
                                        <label class="pblock_label"><span><?= GetMessage("CUSTOMER_DESCRIPTION") ?>
                                                *</span></label>
                                        <input type="text" name="customer_description"
                                               placeholder="<?= GetMessage("CUSTOMER_DESCRIPTION") ?>*">
                                    </div>
                                    <div class="param_block">
                                        <label class="pblock_label"><span><?= GetMessage("CUSTOMER_ADDRESS") ?>
                                                *</span></label>
                                        <input type="text" name="customer_address"
                                               placeholder="<?= GetMessage("CUSTOMER_ADDRESS") ?>*">
                                    </div>
                                    <div class="param_block">
                                        <label class="pblock_label"><span><?= GetMessage("CUSTOMER_TIN") ?>
                                                *</span></label>
                                        <input type="text" name="customer_tin"
                                               placeholder="<?= GetMessage("CUSTOMER_TIN") ?>*">
                                    </div>
                                    <div class="param_block">
                                        <label class="pblock_label"><span><?= GetMessage("CUSTOMER_KPP") ?>
                                                *</span></label>
                                        <input type="text" name="customer_kpp"
                                               placeholder="<?= GetMessage("CUSTOMER_KPP") ?>*">
                                    </div>
                                    <div class="param_block">
                                        <label
                                            class="pblock_label"><span><?= GetMessage("CUSTOMER_EMAIL") ?></span></label>
                                        <input type="text" name="customer_email"
                                               placeholder="<?= GetMessage("CUSTOMER_EMAIL") ?>*">
                                    </div>
                                    <div class="param_block">
                                        <label
                                            class="pblock_label"><span><?= GetMessage("CUSTOMER_PHONE") ?></span></label>
                                        <input type="text" name="customer_phone"
                                               placeholder="<?= GetMessage("CUSTOMER_PHONE") ?>">
                                    </div>
                                </div>
                                <a href="#" class="gotech_button is_legal_button">
                                    <?= GetMessage('SEND'); ?>
                                </a>
                            </div>
                        <? endif; ?>

                    </div>


                    <div class="gotech_clear"></div>
                <? endif; ?>
            <? endif; ?>



            <? if (!$arResult["SHOW_PAYMENT_METHODS"] || $arResult['IS_AGENT']): ?>
                <div class="submit_button_block">
                    <a href="#" id="book_apply" class="gotech_button"><?= GetMessage('BOOKING') ?></a>
                </div>
            <? else: ?>
                <div class="submit_button_block hide_330" style="display:none;">
                    <a href="#" id="book_apply" class="gotech_button"><?= GetMessage('PAY') ?></a>
                </div>
            <? endif; ?>

        </div>

        <input type="hidden" name="send_booking" value="Y"/>

    </form>


</div>
</div>

<script>
    function add_room(e, link) {
        e.preventDefault();

        if (typeof dataLayer != 'undefined') {
            dataLayer.push({
                event:"UA gtm events",
                eventCategory:"requestBooking",
                eventAction: "clickButtonAddRoom"
            });
        }
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
        window.location = location.origin + link;
    }

    $('.gotech_guests_information_item_content_guest_citizenship_spinner').selectric({
        maxHeight: 160,
        disableOnMobile: false,
    });
    $('.document_type_selector').selectric({
        maxHeight: 160,
        disableOnMobile: false,
    });
    $('[name=pay_fio]').selectric({
        maxHeight: 160,
        disableOnMobile: false,
    });
    $('.bday, .guest_passport_valid_day').mask("AB.CD.0000", {
        translation: {
            A: {pattern: /[0-3]/},
            B: {pattern: /[0-9]/},
            C: {pattern: /[0-1]/},
            D: {pattern: /[0-9]/},
        },
        placeholder: "__.__._____",
        onKeyPress: function (a, b, c, d) {

            if (!a) return;

            var m = a.match(/(\d{1})/g)

            if (!m)return;

            if (parseInt(m[0]) === 3) {
                if (parseInt(m[1]) > 1) {
                    c.val(c.val().slice(0, 1));
                }
            }
            if (parseInt(m[2]) === 1) {
                if (parseInt(m[3]) > 2) {
                    c.val(c.val().slice(0, 4));
                }
            }
        }
    }).keyup();
    $('[name=TransferTime]').mask('00:00', {placeholder: "__:__"});

    $(document).ready(function () {

        $('[name="TransferDate"]').prop('disabled', false);

        $('[type=text]').each(function () {

            $('.last_name').each(function () {

                if ($(this).val()) {
                    $(this).trigger('change');
                    $(this).trigger('keyup');
                }

            });

            if ($(this).val()) {
                $(this).parents('.param_block').find('.pblock_label').addClass('active');
            }
        });

    });

    $('.show_legal_fields').click(function () {
        $('.legal_fields').show();
        return false;
    });


    $('.payments.hide_330').find('label').eq(0).click();


    $('.gotech_add_service_popup').click(function () {

    });


    $('.prebookapply').click(function () {

        var t = $(this);

        scrollParentTop();

        t.after('<img src="/bitrix/js/onlinebooking/new/icons/snake-loader.gif" id="preloader1" />');
        t.hide();
        $('#book_apply').click();

        return false;
    });

    $('[name=customer_description],[name=customer_address],[name=customer_tin],[name=customer_kpp],[name=customer_email]').change(function () {

        var customer_description = $('[name=customer_description]').val();
        var customer_address = $('[name=customer_address]').val();
        var customer_tin = $('[name=customer_tin]').val();
        var customer_kpp = $('[name=customer_kpp]').val();
        var customer_email = $('[name=customer_email]').val();

        /*
         if(customer_description && customer_address && customer_tin && customer_kpp && customer_email)
         $('.is_legal_button').show();
         else
         $('.is_legal_button').hide();
         */
    });

    var dadata_chosed = false;

    $('.is_legal_button').click(function () {

        var cs = $('[name=customer_search]').val();

        if (!dadata_chosed || !cs) {
            $('[name=customer_search]').focus();
            $('.legal_fields').show();
        }
        else {

            var t = $(this);

            scrollParentTop();

            t.after('<img src="/bitrix/js/onlinebooking/new/icons/snake-loader.gif" id="preloader1" />');
            t.hide();
            $('#book_apply').click();
        }

        return false;
    });


    $('.fln').focusin(function () {

        var p = $(this).parents('.gotech_guests_information_item_content_guest');
        var pp = $(this).parents('.gotech_basket_item');


        var name = p.find('.sn_user_name').val();
        var last_name = p.find('.sn_user_last_name').val();
        var second_name = p.find('.sn_user_second_name').val();
        var email = p.find('.sn_user_email').val();

        if (last_name && !p.find('[name="surname_0_0"]').val())
            p.find('[name="surname_0_0"]').val(last_name);
        if (name && !p.find('[name="firstname_0_0"]').val())
            p.find('[name="firstname_0_0"]').val(name);
        if (second_name && !p.find('[name="secondName_0_0"]').val())
            p.find('[name="secondName_0_0"]').val(second_name);
        if (email && !p.find('[name="email_0_0"]').val())
            p.find('[name="email_0_0"]').val(email);

        if (email) {
            $('[name="email"]').val(email);
        }

        p.find('.param_block').fadeIn();
        p.find('.h.gray').removeClass('gray');
        pp.find('.add_guest_block').fadeIn();

        iframe_resize();

    });

    var phone_need = <?=(int)$arResult["PHONE_NECESSARY"]?>;
    var email_need = <?=(int)$arResult["EMAIL_NECESSARY"]?>;

    $('input.name,input.last_name,input.second_name').keyup(function () {

        var p = $(this).parents('.guest_block_data');
        var key = p.data('key');

        var name = p.find('input.name').val();
        var last_name = p.find('input.last_name').val();
        var second_name = p.find('input.second_name').val();
        var email = p.find('input.email').val();
        var bday = p.find('input.bday').val();
        var phone = p.find('input.phone').val();
        var err = false;

        var is_fname = true;
        var fname = last_name + ' ' + name + ' ' + second_name;
        fname = fname.trim();


        if (!err) {
            p.find('div.name').text(fname);

            $('.guests_service_block .guest_' + key).each(function () {
                $(this).find('.fio').text(fname);
            });
        }


    });
    $('input.name,input.last_name').change(function () {

        var p = $(this).parents('.guest_block_data');
        var name = p.find('input.name').val();
        var last_name = p.find('input.last_name').val();
        var second_name = p.find('input.second_name').val();
        var key = p.data('key');

        if (key == '0_0') return;

        var is_fname = true;
        var fname = last_name + ' ' + name + ' ' + second_name;
        fname = fname.trim();


        if (fname && is_fname && name && last_name) {
            var exist = false;
            $('[name=pay_fio] option').each(function () {
                if ($(this).val() == fname)
                    exist = true;
            });

            if (!exist) {
                $('[name=pay_fio] .last_option').before('<option value="' + fname + '">' + fname + '</option>');
                $('[name=pay_fio]').selectric('destroy');
                $('[name=pay_fio]').selectric({
                    maxHeight: 160,
                    disableOnMobile: false,
                });
            }
        }

    });

    $('[name=pay_fio]').change(function() {
        if(!$(this).val())
        {
            $('.another_lastname').show();
            $('.another_name').show();
            $('.another_secondname').show();
            $('.another_passport_data_block').show();
            $('.another_passport_data_block+.gotech_guests_information_item_content_guest_document_date').show();
            $('.another_address_block').show();
        }
        else
        {
            $('.another_lastname').hide();
            $('.another_name').hide();
            $('.another_secondname').hide();
            $('.another_passport_data_block').hide();
            $('.another_passport_data_block+.gotech_guests_information_item_content_guest_document_date').hide();
            $('.another_address_block').hide();
        }
    });

    $('[name=pay_fio]').change();


    $('.save_guest_data').click(function () {

        var p = $(this).parents('.gotech_basket_item');
        p.find('input').removeClass('err');

        var gnum = p.find('.guest_block_data').length;
        var gok = 0;
        var gvis = 0;

        p.find('.guest_block_data:visible').each(function (i) {

            //if($(this).is(':visible'))
            //	gvis++;

            var p = $(this);
            var n = i + 1;

            var first = $(this).find('.param_blocks').hasClass('first_person');

            var key = p.data('key');

            var name = p.find('input.name').val();
            var last_name = p.find('input.last_name').val();
            var second_name = p.find('input.second_name').val();
            var email = p.find('input.email').val();
            var bday = p.find('input.bday').val();
            var phone = p.find('input.phone').val();
            var err = false;

            var is_fname = true;
            var fname = last_name + ' ' + name + ' ' + second_name;
            fname = fname.trim();
            if (!fname) {
                is_fname = false;
                fname = 'Гость ' + n;
            }

            if ((p.find('input.name').attr('required') && !name) || (!name && first)) {
                err = true;
                p.find('input.name').addClass('err');
            }
            if ((p.find('input.last_name').attr('required') && !last_name) || (!last_name && first)) {
                err = true;
                p.find('input.last_name').addClass('err');
            }
            if ((p.find('input.second_name').attr('required') && !second_name) /*|| (!second_name && first)*/) {
                err = true;
                p.find('input.second_name').addClass('err');
            }
            if ((p.find('input.bday').attr('required') && !bday)) {
                err = true;
                p.find('input.bday').addClass('err');
            }

            if (!email && first && email_need) {
                err = true;
                p.find('input.email').addClass('err');
            }
            if (!phone && first && phone_need) {
                err = true;
                p.find('input.phone').addClass('err');
            }
            /*
             if(!name || !last_name || !second_name)
             err = true;

             if(p.find('.first_person').length && !email)
             {
             err = true;
             }
             */
            if (!err) {
                p.find('.param_blocks').hide();
                p.find('.guest_data .name').text(fname);
                p.find('.guest_data').show();

                p.find('.guest_info_saved').val('Y');
                gok++;

                $('.guests_service_block .guest_' + key).each(function () {
                    $(this).find('.fio').text(fname);
                });
            }

            if (fname && is_fname) {
                var exist = false;
                $('[name=pay_fio] option').each(function () {
                    if ($(this).val() == fname)
                        exist = true;
                });

                if (!exist) {
                    $('[name=pay_fio]').append('<option value="' + fname + '">' + fname + '</option>');
                    $('[name=pay_fio]').selectric('destroy');
                    $('[name=pay_fio]').selectric({
                        maxHeight: 160,
                        disableOnMobile: false,
                    });
                }
            }


        });


        if (gok == gnum)
            $(this).hide();


        iframe_resize();


        return false;
    });

    $('.payments_mobile [type=radio]').change(function () {
        $('.payments_mobile .payment').removeClass('active');
        $('.payments_mobile [type=radio]:checked').parents('label').addClass('active');
    });

    $('.add_guest').click(function () {

        $(this).parents('.guests_block').find('.save_guest_data').fadeIn();
        $(this).parents('.guests_block').find('.gotech_guests_information_item_content_guest').not(':visible').eq(0).slideDown();
        if (!$(this).parents('.guests_block').find('.gotech_guests_information_item_content_guest').not(':visible').length) {
            $(this).remove();
        }


        return false;
    });


    $('.guest_data .change_order').click(function () {

        $(this).parent().slideUp();
        $(this).parent().next().slideDown();

        $(this).parents('.gotech_basket_item').find('.save_guest_data').show();

        return false;
    });

    $('#gotech_payments input[type="radio"]').on('change', function (e) {
        var id = $('#gotech_payments input[type="radio"]:checked').prop("id");
        if (id != 'undefined') {
            var parameters = id.split("-");
            var is_cash = parameters[2];
            var is_receipt = parameters[3];
            var is_legal = parameters[4];
            if (is_receipt == '1') {
                $('a#book_apply').data('title', "<?=GetMessage("RECEIPT")?>");
                if (!$('.gotech_footer_fixed').length) {
                    $('a#book_apply').html("<?=GetMessage("RECEIPT")?>");
                }
            } else if (is_legal == '1') {
                $('a#book_apply').data('title', "<?=GetMessage("SEND")?>");
                if (!$('.gotech_footer_fixed').length) {
                    $('a#book_apply').html("<?=GetMessage("SEND")?>");
                }
            } else if (is_cash == '1') {
                $('a#book_apply').data('title', "<?=GetMessage("PAY_AFTER")?>");
                if (!$('.gotech_footer_fixed').length) {
                    $('a#book_apply').html("<?=GetMessage("PAY_AFTER")?>");
                }
            } else {
                $('a#book_apply').data('title', "<?=GetMessage("PAY")?>");
                if (!$('.gotech_footer_fixed').length) {
                    $('a#book_apply').html("<?=GetMessage("PAY")?>");
                }
            }
            if (is_legal == '1') {
                $('a#book_apply').addClass('hide_button');
                showCustomerData(this, false);
            } else {
                $('a#book_apply').removeClass('hide_button');
                showCustomerData(this, true);
            }
        }

        iframe_resize();
    });

    $('[name=phone_0_0]').change(function () {
        var v = $(this).val();
        if (!$('[name=phone]').val())
            $('[name=phone]').val(v);
    });
    $('[name=email_0_0]').change(function () {
        var v = $(this).val();
        if (!$('[name=email]').val())
            $('[name=email]').val(v);
    });
    $('[name=surname_0_0], [name=firstname_0_0],[name=patronymic_0_0]').change(function () {
        /*
         var v = $('[name=surname_0_0]').val() + ' ' + $('[name=name_0_0]').val() + ' ' + $('[name=secondName_0_0]').val();
         v = v.trim();
         $('[name=pay_fio]').val(v);
         */
        var fullname = $('[name=surname_0_0]').val() + ' ' + $('[name=firstname_0_0]').val() + ' ' + $('[name=patronymic_0_0]').val();

        $('option.first_user_option').val(fullname);
        $('option.first_user_option').text(fullname);

        $('[name=pay_fio]').selectric('destroy');
        $('[name=pay_fio]').selectric({
            maxHeight: 160,
            disableOnMobile: false,
        });

    });

    <?if(count($arResult['ERRORS'])):?>
    $('.last_name').each(function () {
        $(this).focusin();
    });
    $('.add_guest').click();
    $('.save_guest_data').click();
    <?endif;?>



    $('.transfer_type').change(function () {
        var p = $(this).parents('.jewelery-popup');

        var price = p.find('.transfer_type:checked').data('price');

        var t = p.find('.price').html().split('<span');

        p.find('.price').html(price + ' <span' + t[1]);

    });


    $('.gotech_add_service_button').click(function () {

        var p = $(this).parents('.jewelery-popup');
        var pp = p.prev().prev();

        var transfer = false;
        if (p.data('transfer') == 'Y') {
            transfer = true;

            var transfer_date = p.find('[name="TransferDate"]').val();
            var transfer_time = p.find('[name="TransferTime"]').val();
            var transfer_remarks = p.find('[name="TransferRemarks"]').val();
            var transfer_childseats = p.find('[name="TransferChildSeats"]').val();

            if (!transfer_date)
                p.find('[name="TransferDate"]').addClass('err');
            if (!transfer_time || transfer_time.length < 2)
                p.find('[name="TransferTime"]').addClass('err');

            if (!transfer_remarks)
                p.find('[name="TransferRemarks"]').addClass('err');

            if (!transfer_childseats)
                p.find('[name="TransferChildSeats"]').addClass('err');

            if (!transfer_date || !transfer_time || transfer_time.length < 2 || !transfer_remarks || !transfer_childseats)
                return false;

            p.find('.err').removeClass('err');
        }


        var sid = p.data('service_id');
        if (p.data('days') == 'Y')
            sid = p.data('code');


        var name = p.find('.service_h').text();
        var price;
        var code = p.data('code');
        var hotel_id = $('[name=hotel_id]').val();

        var gd;

        var checked_arr = new Array();
        var price_arr = new Array();
        var id_arr = new Array();
        var n;

        if (transfer) {
            var tkey = $('.guest_block_data').eq(0).data('key');

            $('[data-key="' + tkey + '"]').find('.services .service_transfer').remove();

            code = p.find('[name=transfer_type] option:selected').data('code');
            sid = p.find('[name=transfer_type]').val();

            var time = p.find('[name=TransferTime]').val();
            if (time.length < 5) {
              if (time.length == 4) {
                time += '0';
              }
              if (time.length == 3) {
                time += '00';
              }
              if (time.length == 2) {
                time += ':00';
              }
            }
            var timeSplit = time.split(':');
            if (parseInt(timeSplit[1]) > 59) {
              time = timeSplit[0] + ':59';
            }
            $('[name=TransferTime]').val(time);

            n = $('[data-key="0_0"]').find('.services .service').length + 1;
            price = p.find('[name=transfer_type] option:selected').data('price');

            if (!$('[data-key="' + tkey + '"]').find('.services .service' + sid).length)
                $('[data-key="' + tkey + '"]')
                    .find('.services').append('' +
                    '<div class="service service' + sid + ' service_transfer" data-code="' + code + '" data-uid="' + sid + '" data-guest="' + tkey + '" data-id="' + sid + '">' +
                    '<span class="pn">' + (n) + '</span>. ' + name + '    |   <b>' + price + ' <span class="gotech_ruble">a</span></b> <a href="#" class="delete"></a>' +
                    '<input type="hidden" name="service_price_' + sid + '" class="service_price" value="' + price + '"/>' +
                    '<input type="hidden" name="is_transfer" value="1" />' +
                    '</div>');

            $('#gotech_booking_services .service_item[data-transfer="Y"]').each(function () {
                $(this).find('.gotech_button').show();
                $(this).find('.added_info').hide();
                if ($(this).data('sid') == p.data('code')) {
                    $(this).find('.gotech_button').hide();
                    $(this).find('.added_info').show();
                }
            });


        }
        else {
            p.find('.guests_service_block .guest').each(function () {
                var c = $(this).find('[type=checkbox]');
                var cv = c.val();

                if ($('[data-key="' + cv + '"]').find('.services .service' + sid).length && !c.is(':checked')) {
                    $('[data-key="' + cv + '"]').find('.services .service' + sid + ' .delete').click();
                }

                if (c.is(':checked')) {
                    checked_arr.push(cv);

                    var s = $(this).find('select');
                    if (s.length) {
                        price_arr.push(s.find('option:selected').data('price'));
                        id_arr.push(s.val());

                    }
                }
            });
            if (!price_arr.length)
                price = p.find('.price').data('price');

            for (var j = 0; j < checked_arr.length; j++) {
                var uid;
                if (!price_arr.length) {
                    price0 = price;
                    price1 = price + ' <span class="gotech_ruble">a</span>';
                    uid = sid;
                }
                else {
                    price0 = price_arr[j];
                    price1 = price_arr[j] + ' <span class="gotech_ruble">a</span>';
                    uid = id_arr[j];
                }
                n = $('[data-key="' + checked_arr[j] + '"]').find('.services .service').length + 1;
                if (!$('[data-key="' + checked_arr[j] + '"]').find('.services .service' + sid).length)
                    $('[data-key="' + checked_arr[j] + '"]')
                        .find('.services').append('' +
                        '<div class="service service' + sid + '" data-code="' + code + '" data-uid="' + uid + '" data-guest="' + checked_arr[j] + '" data-id="' + uid + '">' +
                        '<span class="pn">' + (n) + '</span>. ' + name + '    |   <b>' + price1 + '</b> <a href="#" class="delete"></a>' +
                        '<input type="hidden" name="service_price_' + uid + '" class="service_price" value="' + price0 + '" />' +
                        '</div>');
            }

            if (checked_arr.length) {
                $('#gotech_booking_services .service_item[data-sid="' + code + '"]').each(function () {
                    $(this).find('.gotech_button').hide();
                    $(this).find('.added_info').find('.people_num').text(checked_arr.length);
                    $(this).find('.added_info').show();
                });
            }
        }

        var data = $('div#service_form_ExSe_' + sid);
        var post_data;

        for (var i = 0; i < checked_arr.length; i++) {
            if (id_arr.length)
                data = $('div#service_form_ExSe_' + id_arr[i]);

            post_data =
            {
                FormType: data.children('input[name="FormType"]').val(),
                id: data.children('input[name="id"]').val(),
                hotel: data.children('input[name="hotel"]').val(),
                hotel_id: data.children('input[name="hotel_id"]').val(),
                code: data.children('input[name="code"]').val(),
                age_from: data.children('input[name="age_from"]').val(),
                age_to: data.children('input[name="age_to"]').val(),
                number_to_guest: data.children('input[name="number_to_guest"]').val(),
                number_to_room: data.children('input[name="number_to_room"]').val(),
                Currency: data.children('input[name="Currency"]').val(),
                GuestID: checked_arr[i]
            };
            $.post('/bitrix/components/onlinebooking/onlinebooking/ajax.php', post_data,
                function (data) {

                });
        }

        if (transfer) {
            post_data =
            {
                FormType: data.children('input[name="FormType"]').val(),
                id: data.children('input[name="id"]').val(),
                hotel: data.children('input[name="hotel"]').val(),
                hotel_id: data.children('input[name="hotel_id"]').val(),
                code: data.children('input[name="code"]').val(),
                age_from: data.children('input[name="age_from"]').val(),
                age_to: data.children('input[name="age_to"]').val(),
                number_to_guest: data.children('input[name="number_to_guest"]').val(),
                number_to_room: data.children('input[name="number_to_room"]').val(),
                Currency: data.children('input[name="Currency"]').val(),
                GuestID: tkey
            };
            post_data['is_transfer'] = true;
            post_data['transfer_date'] = transfer_date;
            post_data['transfer_time'] = transfer_time;
            post_data['transfer_remarks'] = transfer_remarks;
            post_data['transfer_childseats'] = transfer_childseats;

            $.post('/bitrix/components/onlinebooking/onlinebooking/ajax.php', post_data,
                function (data) {

                });

        }


        $('.guest_data').each(function () {
            $(this).find('.service').each(function (i) {
                $(this).find('.pn').text(i + 1);
            });

        });

        var scnt = $('.service').length;
        $('b.services_num').text(scnt);

        $.magnificPopup.close();
        recount();

        return false;
    });

    $('.added_info .del').click(function () {

        var pp = $(this).parents('.service_item');
        var code = pp.data('sid');

        var transfer = (pp.data('transfer') == 'Y');

        if (transfer) {
            var p = $('.service_transfer').eq(0);
            var id = p.data('id');
            var g = p.data('guest');

            $.post('/bitrix/components/onlinebooking/onlinebooking/ajax.php',
                {
                    FormType: 'deleteOrderExSe',
                    id: id,
                    GuestID: g
                },
                function (data) {

                    p.remove();
                    ;
                    pp.find('.added_info').hide();
                    pp.find('.added_info').prev().find('a').show();

                    $('.guest_data').each(function () {
                        $(this).find('.service').each(function (i) {
                            $(this).find('.pn').text(i + 1);

                        });

                    });
                    var scnt = $('.service').length;
                    $('b.services_num').text(scnt);
                }
            );
        }
        else
            $('.service[data-code="' + code + '"]').each(function () {
                var p = $(this);
                var id = p.data('id');
                var g = p.data('guest');

                $.post('/bitrix/components/onlinebooking/onlinebooking/ajax.php',
                    {
                        FormType: 'deleteOrderExSe',
                        id: id,
                        GuestID: g
                    },
                    function (data) {

                        $('.service[data-code="' + code + '"]').remove();
                        $('.jewelery-popup[data-service_id="' + id + '"]').find('[type=checkbox]').prop('checked', false);
                        $('.jewelery-popup[data-service_id="' + id + '"]').find('[type=checkbox]').trigger('change');
                        pp.find('.added_info').hide();
                        pp.find('.added_info').prev().find('a').show();

                        $('.guest_data').each(function () {
                            $(this).find('.service').each(function (i) {
                                $(this).find('.pn').text(i + 1);

                            });

                        });
                        var scnt = $('.service').length;
                        $('b.services_num').text(scnt);
                        recount();
                    }
                );
            });
        recount();

        return false;
    });

    $('body').on('click', '.guest_data .service .delete', function (data) {

        if ($(this).hasClass('delete_from_bron')) return false;

        var p = $(this).parents('.service');
        var id = p.data('id');
        var g = p.data('guest');
        var code = p.data('code');

        $('.jewelery-popup[data-service_id="' + id + '"]').find('.guest.guest_' + g + ' [type=checkbox]').prop('checked', false);
        $('.jewelery-popup[data-service_id="' + id + '"]').find('.guest.guest_' + g + ' [type=checkbox]').trigger('change');

        p.remove();
        var n = $('.service.service' + id).length;

        var el = $('#gotech_booking_services .service_item[data-sid="' + id + '"]');
        if (!el.length)
            el = $('#gotech_booking_services .service_item[data-sid="' + code + '"]');

        el.find('.added_info .people_num').text(n);
        if (!n) {
            el.find('.added_info').hide();
            el.find('.added_info').prev().find('a').show();

        }
        $.post('/bitrix/components/onlinebooking/onlinebooking/ajax.php',
            {
                FormType: 'deleteOrderExSe',
                id: id,
                GuestID: g
            },
            function (data) {
                if (p.hasClass('service_transfer')) {
                    $('.service_item[data-transfer="Y"] .added_info').hide();
                    $('.service_item[data-transfer="Y"] .added_info').prev().show();
                    $('.service_item[data-transfer="Y"] .added_info').prev().find('a').show();
                }
                var scnt = $('.service').length;
                $('b.services_num').text(scnt);
            }
        );
        recount();
        $('.guest_data').each(function () {
            $(this).find('.service').each(function (i) {
                $(this).find('.pn').text(i + 1);

            });

        });

        return false;
    });


</script>

<script>
    $(function () {
        $('input[name="payment_methods_radiobuttons"]:checked').change();
        $('input[name="payment_methods_radiobuttons"]:checked').click();
        $("input[name='customer_search']").suggestions({
            token: "39d56491dac45396522f98c4958f0c16ab61152b",
            type: "PARTY",
            count: 5,
            onSearchComplete: function (query, suggestions) {
                console.log(suggestions);
            },
            /* Вызывается, когда пользователь выбирает одну из подсказок */
            onSelect: function (suggestion) {
                console.log(suggestion);
                if (suggestion) {
                    var $this = $(this);

                    $this.parents('.gotech_guests_information_footer_payment_methods_customer_data').find('input[name="customer_description"]').val(suggestion.value)
                    $this.parents('.gotech_guests_information_footer_payment_methods_customer_data').find('input[name="customer_address"]').val(suggestion.data.address.value)
                    $this.parents('.gotech_guests_information_footer_payment_methods_customer_data').find('input[name="customer_tin"]').val(suggestion.data.inn)
                    $this.parents('.gotech_guests_information_footer_payment_methods_customer_data').find('input[name="customer_kpp"]').val(suggestion.data.kpp)
                    checkDadataInputs($('.gotech_guests_information_footer_payment_methods_customer_data label>input'), $('a#book_apply'));

                    $('[name=customer_description]').trigger('change');

                    dadata_chosed = true;
                    $('.legal_fields').show();
                    $('[name=customer_email]').val($('[name=email]').val());
                    $('[name=customer_phone]').val($('[name=phone]').val());
                }
            }
        });
    });
    $("input[name^='ClientIdentityDocumentUnitCode']").suggestions({
        token: "39d56491dac45396522f98c4958f0c16ab61152b",
        type: "fms_unit",
        count: 5,
        // запретить автоисправление по пробелу
        triggerSelectOnSpace: false,
        // запрещаем автоподстановку по Enter
        triggerSelectOnEnter: false,
        // запретить автоисправление при выходе из текстового поля
        triggerSelectOnBlur: false,
        formatResult: function (value, currentValue, suggestion) {
            suggestion.value = suggestion.data.code;
            return suggestion.data.code + " — " + suggestion.data.name;
        },
        onSearchComplete: function (query, suggestions) {
            console.log(suggestions);
        },
        /* Вызывается, когда пользователь выбирает одну из подсказок */
        onSelect: function (suggestion) {
            console.log(suggestion);
            if (suggestion) {
                var $this = $(this);

                $this.val(suggestion.data.code);
                $this.parent().parent().find("[name^='ClientIdentityDocumentIssuedBy']").val(suggestion.data.name)
            }
        }
    });
    $('.gotech_guests_information_footer_payment_methods_customer_data label>input').on('input', function (e) {
        $('a#book_apply').removeClass('hide_button');
        checkDadataInputs($('.gotech_guests_information_footer_payment_methods_customer_data label>input'), $('a#book_apply'));
    });
    setSelectricForCitizenships();
    $('#age_error_text').hide();
    $('#age_error_text').click(function (e) {
        $(this).hide();
        e.preventDefault();
    });
    <?if(isset($arResult["Services"]) && !empty($arResult["Services"])):?>
    $(document).ready(function () {
        $("#gotech_guests_information_services_slider").owlCarousel({
            autoPlay: 6000,
            items: 3,
            itemsDesktop: [1199, 2],
            itemsDesktopSmall: [480, 1],
            stopOnHover: true,
            navigation: true,
            navigationText: ["", ""]
        });
    });
    <?endif;?>
    $('.gotech_guests_information_item_content_guest_birthday_input').on('change', function () {
        if ($(this).val()) {
            var arDate = $(this).val().split('.');
            var date = new Date(arDate[2], arDate[1], arDate[0]);
            if (date.getFullYear() <= 1900 || parseInt(arDate[0]) < 1 || parseInt(arDate[1]) < 1 || parseInt(arDate[2]) <= 1900) {
                $(this).val('');
            }
        }
    });
    $('input[name="payment_methods_radiobuttons"]').on('change', function (e) {
        var id = $('input[name="payment_methods_radiobuttons"]:checked').prop("id");
        if (id != 'undefined') {
            var parameters = id.split("-");
            var is_cash = parameters[2];
            var is_receipt = parameters[3];
            var is_legal = parameters[4];
            if (is_receipt == '1') {
                $('a#book_apply').data('title', "<?=GetMessage("RECEIPT")?>");
                if (!$('.gotech_footer_fixed').length) {
                    $('a#book_apply').html("<?=GetMessage("RECEIPT")?>");
                }
            } else if (is_legal == '1') {
                $('a#book_apply').data('title', "<?=GetMessage("SEND")?>");
                if (!$('.gotech_footer_fixed').length) {
                    $('a#book_apply').html("<?=GetMessage("SEND")?>");
                }
            } else if (is_cash == '1') {
                $('a#book_apply').data('title', "<?=GetMessage("PAY_AFTER")?>");
                if (!$('.gotech_footer_fixed').length) {
                    $('a#book_apply').html("<?=GetMessage("PAY_AFTER")?>");
                }
            } else {
                $('a#book_apply').data('title', "<?=GetMessage("PAY")?>");
                if (!$('.gotech_footer_fixed').length) {
                    $('a#book_apply').html("<?=GetMessage("PAY")?>");
                }
            }
            if (is_legal == '1') {
                $('a#book_apply').addClass('hide_button');
                showCustomerData(this, false);
            } else {
                $('a#book_apply').removeClass('hide_button');
                showCustomerData(this, true);
            }
        }

        iframe_resize();
    });
    $('input[id="gotech_guests_information_contacts_content_email_input"]').on('input', function (e) {
        $customer_email = $('input[name="customer_email"]');
        if ($customer_email.length) {
            $customer_email.val($(this).val());
            $('a#book_apply').removeClass('hide_button');
            checkDadataInputs($('.gotech_guests_information_footer_payment_methods_customer_data label>input'), $('a#book_apply'));
        }
    });
    $('input[id="gotech_guests_information_contacts_content_phone_input"]').on('input', function (e) {
        $customer_phone = $('input[name="customer_phone"]');
        if ($customer_phone.length) {
            $customer_phone.val($(this).val());
        }
    });


    function showCustomerData(element, hide) {
        if (hide) {
            $('.gotech_guests_information_footer_payment_methods_customer_data').slideUp();
        } else {
            $('.gotech_guests_information_footer_payment_methods_customer_data').show();
            $('.gotech_guests_information_footer_payment_methods_customer_data').slideDown();
        }
        iframe_resize();
    }


    scrollParentTop();
    parent.postMessage('hideBasket', '*');

    $('.add_guest_info').on('click', function (e) {
        e.preventDefault();
        var need_to_hide = true;
        $('.guest_block_data').each(function (ind, el) {
            if (need_to_hide && !$(el).is(':visible')) {
                $(el).show();
                need_to_hide = false;
            }
        });
        var invisible_elements = $('.guest_block_data').length - $('.guest_block_data:visible').length;
        if (need_to_hide || !invisible_elements) {
            $(this).hide();
        }
    })

    $(".gotech_guests_information_item_content_guest_address_input").suggestions({
        token: "39d56491dac45396522f98c4958f0c16ab61152b",
        type: "ADDRESS",
        count: 5,
        /* Вызывается, когда пользователь выбирает одну из подсказок */
        onSelect: function(suggestion) {
            console.log(suggestion);
        }
    });
</script>

