<? //if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
//$_REQUEST = $arParams["REQUEST"];

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
IncludeModuleLangFile(__DIR__ . '/templates/.default/template.php');

if (!function_exists('plural_form')) {
    function plural_form($number, $after)
    {
        $cases = array(2, 0, 1, 1, 1, 2);
        echo $number . ' ' . $after[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }
}

$_REQUEST["hotel_id"] = $_REQUEST["hotel_id_"];

if (!CModule::IncludeModule("iblock")) {
    echo GetMessage("IBLOCK_NOT_INCLUDE");
    return;
} elseif (!CModule::IncludeModule("gotech.hotelonline")) {
    ShowError(GetMessage("ONLINEBOOKING_MODULE_NOT_INSTALLED"));
    return;
} else {

    $res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => COption::GetOptionInt('gotech.hotelonline', 'HOTEL_IBLOCK_ID'), "ID" => $_REQUEST["hid"]), false, false,
        array("PROPERTY_HOTEL_TIME", "PROPERTY_HOTEL_ROOM_RATE", "PROPERTY_HOTEL_TIME_FROM", "PROPERTY_CURRENCY", "PROPERTY_HOURS_ENABLE", "PROPERTY_HOTEL_CODE", "PROPERTY_HOTEL_OUTPUT_CODE", "PROPERTY_HOTEL_ROOM_RATE", "PROPERTY_HOTEL_ROOM_QUOTA", "PROPERTY_ADDRESS_WEB_SERVICE", "PROPERTY_HOTEL_SHOW_ECONOMY", "PROPERTY_HOTEL_ERROR_TEXT_RU", "PROPERTY_HOTEL_ERROR_TEXT_EN", "PROPERTY_SOAP_LOGIN", "PROPERTY_SOAP_PASSWORD"));
    if ($hotel = $res->GetNext()) {

        $roomRate = $hotel["PROPERTY_HOTEL_ROOM_RATE_VALUE"];

        if (!empty($hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"])) {
            $WSDL = $hotel["PROPERTY_ADDRESS_WEB_SERVICE_VALUE"];
        } else $WSDL = "";
        if(!empty($hotel["PROPERTY_SOAP_LOGIN_VALUE"]))
            $SOAP_LOGIN = trim($hotel["PROPERTY_SOAP_LOGIN_VALUE"]);
        else
            $SOAP_LOGIN = "";
        if(!empty($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]))
            $SOAP_PASSWORD = trim($hotel["PROPERTY_SOAP_PASSWORD_VALUE"]);
        else
            $SOAP_PASSWORD = "";
    }

    $adults = $_REQUEST['adults'];

    if ($_REQUEST['direction'] == 'later'):
        $d1 = date('Y-m-d', (strtotime($_REQUEST["date"]) + 1 * 60 * 60 * 24)) . 'T' . $_REQUEST["htf"] . ':00';
        $d2 = date('Y-m-d', (strtotime($_REQUEST["date"]) + 8 * 60 * 60 * 24)) . 'T' . $_REQUEST["ht"] . ':00';
    elseif ($_REQUEST['direction'] == 'earlier'):
        $d2 = date('Y-m-d', (strtotime($_REQUEST["date"]) - 1 * 60 * 60 * 24)) . 'T' . $_REQUEST["htf"] . ':00';
        $d1 = date('Y-m-d', (strtotime($_REQUEST["date"]) - 8 * 60 * 60 * 24)) . 'T' . $_REQUEST["ht"] . ':00';


        $today = date('d.m.Y', time());
        $today = strtotime($today);

        $new_pf_2 = strtotime($_REQUEST["date"]) - 8 * 60 * 60 * 24;

        if ($new_pf_2 <= $today)
            $d1 = date('Y-m-d', time()) . 'T' . $_REQUEST["htf"] . ':00';


    endif;

    $new_pf = $d1;
    $new_pt = $d2;


    $GetRoomInventoryBalanceArr = array(
        "Hotel" => $_REQUEST["hid"],
        "RoomType" => $_REQUEST['rtc'],//
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

    $av_room_price_arr = array(
        1 => 'SinglePrice',
        2 => 'DoublePrice',
        3 => 'TriplePrice',
        4 => 'QuadruppelPrice',
    );

    $soap_params = array('trace' => 1);
    if (!empty($SOAP_LOGIN) && !empty($SOAP_PASSWORD)) {
        $soap_params['login'] = $SOAP_LOGIN;
        $soap_params['password'] = $SOAP_PASSWORD;
    }
    $soapclient = new SoapClient(trim($WSDL), $soap_params); // with auth
    $res2 = $soapclient->GetRoomInventoryBalance($GetRoomInventoryBalanceArr);

    $arr = $res2->return->RoomInventoryBalanceRow;

    ob_start();
    ?>

    <?
    foreach ($arr as $k => $v): ?>
        <?
        if (!$k) continue; ?>
        <td <?
            if ((int)$v->RoomsVacant): ?>class="av"<?
        endif; ?>><?= date('d.m.Y', strtotime($v->Period)) ?></td>
    <?endforeach; ?>

    <?
    $dates = ob_get_contents();
    ob_clean();


    ob_start();
    ?>
    <?
    foreach ($arr as $k => $v): ?>
        <?
        if (!$k) continue; ?>
        <td class="<?
        if (!(int)$v->RoomsVacant): ?>no_av<? else: ?>av<?endif; ?>">
            <?
            if ((int)$v->RoomsVacant): ?>
                <?
                if ((int)$v->{$av_room_price_arr[$adults]}): ?>
                    <?= GetMessage('FROM1') ?><br/><?= $v->{$av_room_price_arr[$adults]} ?><br/>Р
                <? else: ?>
                    <div style="min-height:56px;line-height:56px;">доступно</div>
                <?endif; ?>
            <? else: ?>
                <div style="min-height:56px;"></div>
            <?endif; ?>

        </td>
    <?endforeach; ?>
    <?
    $prices = ob_get_contents();
    ob_clean();

    echo json_encode(array('dates' => $dates, 'prices' => $prices));

}
?>