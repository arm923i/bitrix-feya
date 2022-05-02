<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? __IncludeLang($_SERVER["DOCUMENT_ROOT"] . $this->__folder . "/lang/" . OnlineBookingSupport::getLanguage() . "/template.php"); ?>
<?
if (!function_exists('plural_form')) {
  function plural_form($number, $after)
  {
    $cases = array(2, 0, 1, 1, 1, 2);
    echo $number . ' ' . $after[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
  }
}

?>
<script>
  kidPlaceholders = [];
  for (var i = 0; i < 18; i++) {
    if (i == 1) {
      kidPlaceholders[i] = "<?=GetMessage("KID")?> (" + i + "<?=GetMessage("1AGES")?>)";
    } else if (i >= 2 && i < 5) {
      kidPlaceholders[i] = "<?=GetMessage("KID")?> (" + i + "<?=GetMessage("2AGES")?>)";
    } else if (i >= 5) {
      kidPlaceholders[i] = "<?=GetMessage("KID")?> (" + i + "<?=GetMessage("AGES")?>)";
    } else {
      kidPlaceholders[i] = "<?=GetMessage("KID")?> (<?=GetMessage("AGES_LESS_THAN_ONE")?>)";
    }
  }
</script>
<? $ar_group = $USER->GetUserGroup($USER->GetID()); ?>
<? if ((!$USER->IsAuthorized() || !in_array(COption::GetOptionint('gotech.hotelonline', 'USER_AGENT_GROUP'), $ar_group)) && !$arResult["IS_FROM_PAYMENT"]): ?>
  <div id="my_bron_form"
       class="page_header search_booking"<? if (!empty($arResult["BOOKING"])): ?> style="display:none;"<? endif ?>>
    <div class="h">
      <?= GetMessage("MY_RESERVATION") ?>
    </div>
    <div id="gotech_search_booking_content">
      <p><?= GetMessage("DESCRIPTION") ?></p>

      <? if (!empty($arResult["ERROR"])): ?>
        <div class="gotech_error_text gotech_big_text"><?= $arResult["ERROR"] ?></div>
      <? endif; ?>
      <form name="search_reservation" action="<?= $APPLICATION->GetCurPage() ?>" method="get">
        <input type="hidden" name="SessionID" value="<?= $_REQUEST['SessionID'] ?>">
        <input type="hidden" name="UserID" value="<?= $_REQUEST['UserID'] ?>">
        <input type="hidden" name="utm_source" value="<?= $_REQUEST['utm_source'] ?>">
        <input type="hidden" name="utm_medium" value="<?= $_REQUEST['utm_medium'] ?>">
        <input type="hidden" name="utm_campaign" value="<?= $_REQUEST['utm_campaign'] ?>">

        <input type="hidden" name="language" value="<?= OnlineBookingSupport::getLanguage() ?>">
        <? if (count($arResult["HOTELS"]) > 1): ?>
          <tr>
            <td><?= GetMessage("HOTEL") ?></td>
            <td>
              <select name="hotel_code">
                <? foreach ($arResult["HOTELS"] as $hotel): ?>
                  <option
                    value="<?= $hotel["ID"] ?>" <? if ($_REQUEST["hotel_code"] == $hotel["ID"]): ?> selected="selected" <? endif; ?>><?= $hotel["NAME"] ?></option>
                <? endforeach; ?>
              </select>
            </td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        <? else: ?>
          <input type="hidden" name="hotel_code" value="<?= $arResult["HOTELS"][0]["ID"] ?>"/>
        <? endif; ?>
        <input type="hidden" name="hotel" value="<?= htmlspecialchars($_REQUEST["hotel"]) ?>"/>

        <div class="param_block">
          <label class="pblock_label"><span><?= GetMessage("RESERVATION") ?></span></label>
          <input required name="reservation" placeholder="<?= GetMessage("RESERVATION") ?>"
                 id="gotech_search_booking_content_reservation_input" <? if (!empty($_REQUEST["reservation"])): ?> value="<?= htmlspecialcharsEx($_REQUEST["reservation"]) ?>" <? endif; ?>
                 onkeyup="submit_search();">
        </div>
        <div class="param_block">
          <label class="pblock_label"><span><?= GetMessage("EMAIL_OR_PHONE") ?></span></label>
          <input required name="data" placeholder="<?= GetMessage("EMAIL_OR_PHONE") ?>"
                 id="gotech_search_booking_content_email_or_phone_input" <? if (!empty($_REQUEST["phone"])): ?> value="<?= htmlspecialcharsEx($_REQUEST["phone"]) ?>" <? elseif (!empty($_REQUEST["email"])): ?> value="<?= htmlspecialcharsEx($_REQUEST["email"]) ?>" <? elseif (!empty($_REQUEST["data"])): ?> value="<?= htmlspecialcharsEx($_REQUEST["data"]) ?>" <? endif; ?>
                 onkeyup="submit_search();">
        </div>
        <input type="hidden" name="search" value="Y"/>
        <input type="hidden" name="cancel" value="N"/>
        <input type="hidden" name="is_change_order" value="N"/>
        <input type="hidden" name="is_change" value="N"/>
        <input type="hidden" name="is_change_room" value="0"/>
      </form>
      <br/><br/>
      <div id="gotech_search_booking_content_button">
        <a class="gotech_button" href="#" id="reservation">
          <?= GetMessage("FIND"); ?>
        </a>
      </div>
    </div>
  </div>
<? else: ?>
  <form name="search_reservation" action="<?= $APPLICATION->GetCurPage() ?>" method="get">
    <input type="hidden" name="SessionID" value="<?= $_REQUEST['SessionID'] ?>">
    <input type="hidden" name="UserID" value="<?= $_REQUEST['UserID'] ?>">
    <input type="hidden" name="utm_source" value="<?= $_REQUEST['utm_source'] ?>">
    <input type="hidden" name="utm_medium" value="<?= $_REQUEST['utm_medium'] ?>">
    <input type="hidden" name="utm_campaign" value="<?= $_REQUEST['utm_campaign'] ?>">

    <input type="hidden" name="language" value="<?= OnlineBookingSupport::getLanguage() ?>">
    <input type="hidden" name="hotel" value="<?= $_REQUEST["hotel"] ?: $arResult['HOTELS'][0]['ID'] ?>"/>
    <input type="hidden" name="reservation"
           value="<?= htmlspecialcharsEx($_REQUEST["reservation"] ?: $arResult['GuestGroup']) ?>">
    <input type="hidden"
           name="data" <? if (!empty($_REQUEST["phone"] || $arResult['CONTACT_PERSON_PHONE'])): ?> value="<?= htmlspecialcharsEx($_REQUEST["phone"] ?: $arResult['CONTACT_PERSON_PHONE']) ?>" <? elseif (!empty($_REQUEST["email"] || $arResult['CONTACT_PERSON_EMAIL'])): ?> value="<?= htmlspecialcharsEx($_REQUEST["email"] ?: $arResult['CONTACT_PERSON_EMAIL']) ?>" <? elseif (!empty($_REQUEST["data"])): ?> value="<?= htmlspecialcharsEx($_REQUEST["data"]) ?>" <? endif; ?>>
    <input type="hidden" name="search" value="Y"/>
    <input type="hidden" name="cancel" value="N"/>
    <input type="hidden" name="is_change_order" value="N"/>
    <input type="hidden" name="is_change" value="N"/>
    <input type="hidden" name="is_change_room" value="0"/>
  </form>
<? endif; ?>

<? if ((empty($arResult["ERROR"]) || $arResult["IS_FROM_PAYMENT"]) && !empty($arResult["BOOKING"]) && empty($arResult["SUCCESS"])): ?>
  <div id="gotech_booking_order">
    <div class="page_header">

      <div class="left">
        <div class="h"><?= GetMessage('APPROVE_BRON') ?></div>
        <div class="info">
          <div class="left">
            <img src="<?= $arResult['HOTELS'][0]['PICTURE']['src'] ?>"/>
          </div>
          <div class="right">
            <div>
              <?= GetMessage('HOTEL') ?><br/>
              <b><?= $arResult['HOTELS'][0]['NAME'] ?></b>
            </div>
            <div>
              <?= GetMessage('ADDRESS') ?><br/>
              <b><?= $arResult['HOTELS'][0]['HOTEL_ADDRESS'] ?></b>
              <p class="hotel-address-map-link"><a
                  href="https://www.google.ru/maps/?q=<?= urlencode($arResult['HOTELS'][0]['HOTEL_ADDRESS']) ?>"
                  target="_blank">Открыть карты</a></p>
            </div>
            <div>
              <?= GetMessage('PHONE') ?><b><?= $arResult['HOTELS'][0]['HOTEL_PHONE'] ?></b><br/>
              <?= GetMessage('FAX') ?><b><?= $arResult['HOTELS'][0]['HOTEL_FAX'] ?></b>
            </div>
            <div>
            </div>
            <div style="clear:both;"></div>
          </div>
        </div>
        <div style="clear:both;"></div>
      </div>

      <div style="clear:both;"></div>
    </div>
    <div id="gotech_booking_order_items">

      <?
      $services = array();
      $services_guest = array();
      $total_service_price = 0;
      foreach ($arResult["BOOKING"] as $rkey => $number):
        foreach ($number['Guests'] as $j => $guest):

          if (is_array($guest['ExtraServices'])) {
            foreach ($guest['ExtraServices'] as $l => $s):
              if (isset($s->TransferRemarks) || isset($s->TransferType)) {
                $total_service_price += $s->Sum;
                $r = explode(', ', $s->Remarks);
                $services[] = array(
                  'Id' => $r[1],
                  'Name' => $s->Service,
                  'GuestID' => $guest["Id"],
                  'Price' => $s->Sum,
                  'Code' => $s->ServiceCode
                );
                $services_guest[$guest["Id"]][] = array(
                  'Id' => $l,
                  'Name' => $s->Service." (".$s->TransferType.")",
                  'GuestID' => $guest["Id"],
                  'Price' => $s->Sum,
                  'Code' => ""
                );
              } else {
                $total_service_price += $s->Price;
                $r = explode(', ', $s->Remarks);
                $services[] = array(
                  'Id' => $r[1],
                  'Name' => $s->ServiceDescription,
                  'GuestID' => $guest["Id"],
                  'Price' => $s->Price,
                  'Code' => $s->ServiceCode
                );
                $services_guest[$guest["Id"]][] = array(
                  'Id' => $r[1],
                  'Name' => $s->ServiceDescription,
                  'GuestID' => $guest["Id"],
                  'Price' => $s->Price,
                  'Code' => $s->ServiceCode
                );
              }
            endforeach;
          } else if ($guest['ExtraServices']) {
            $total_service_price += $guest['ExtraServices']->Price;
            $r = explode(', ', $guest['ExtraServices']->Remarks);
            $services[] = array(
              'Id' => $r[2],
              'Name' => $guest['ExtraServices']->ServiceDescription,
              'GuestID' => $r[1],
              'Price' => $guest['ExtraServices']->Price,
              'Code' => $guest['ExtraServices']->ServiceCode
            );
            $services_guest[$r[1]][] = array(
              'Id' => $r[2],
              'Name' => $guest['ExtraServices']->ServiceDescription,
              'GuestID' => $r[1],
              'Price' => $guest['ExtraServices']->Price,
              'Code' => $guest['ExtraServices']->ServiceCode
            );
          }
        endforeach;
      endforeach;

      //echo var_dump($services_guest);
      ?>

      <div class="h"><?= GetMessage('RESERVATION') ?>: <?= $arResult['GuestGroup'] ?></div>
      <?
      $total = 0;
      //$total = $total_service_price;
      ?>
      <? $room_id = 0 ?>
      <? $guest_id = 0 ?>

      <?
      $arRooms = array();
      $icnt = 0;
      $num_cnt = count($arResult["BOOKING"]);
      ?>
      <? foreach ($arResult["BOOKING"] as $rkey => $number): ?>
        <?
        $icnt++;
        $key_number = $num_cnt - $icnt;


        $total += $number['Cost'];
        ?>
        <div class="booking_order_item">
          <input type="hidden" name="SessionID" value="<?= $_REQUEST['SessionID'] ?>">
          <input type="hidden" name="UserID" value="<?= $_REQUEST['UserID'] ?>">
          <input type="hidden" name="utm_source" value="<?= $_REQUEST['utm_source'] ?>">
          <input type="hidden" name="utm_medium" value="<?= $_REQUEST['utm_medium'] ?>">
          <input type="hidden" name="utm_campaign" value="<?= $_REQUEST['utm_campaign'] ?>">

          <input type="hidden" name="room_id" value="<?= $room_id ?>">
          <input type="hidden" name="reservation_code" value="<?= $rkey ?>">
          <input type="hidden" name="room_type_code" value="<?= $number["RoomTypeCode"] ?>">
          <input type="hidden" name="check_in_date" value="<?= $number["CheckInDateIn1CFormat"] ?>">
          <input type="hidden" name="check_out_date" value="<?= $number["CheckOutDateIn1CFormat"] ?>">
          <input type="hidden" name="currency_symbol" value="<?= $number["Currency"] ?>">
          <input type="hidden" name="customer" value="<?= $number["Customer"] ?>">
          <input type="hidden" name="contract" value="<?= $number["Contract"] ?>">
          <input type="hidden" name="room_rate_code" value="<?= $number["RoomRateCode"] ?>">
          <input type="hidden" name="room_quota_code" value="<?= $number["RoomQuotaCode"] ?>">
          <input type="hidden" name="price" value="<?= $number["Cost"] ?>">

          <input type="hidden" name="wsdl" value="<?= $arResult["WSDL"] ?>">
          <input type="hidden" name="hotel_code" value="<?= $arResult["HotelCode"] ?>">
          <input type="hidden" name="output_code" value="<?= $arResult["OutputCode"] ?>">
          <input type="hidden" name="contact_person_email" value="<?= $arResult["CONTACT_PERSON_EMAIL"] ?>">
          <input type="hidden" name="contact_person_phone" value="<?= $arResult["CONTACT_PERSON_PHONE"] ?>">
          <input type="hidden" name="login" value="<?= $arResult["login"] ?>">
          <input type="hidden" name="res_uuid" value="<?= $number["UUID"] ?>">
          <input type="hidden" name="uuid" value="<?= $arResult["UUID"] ?>">

          <div class="left">
            <img src="<?= $number['Picture']['src'] ?>"/>
          </div>
          <div class="right">
            <?
            $nights = (strtotime($number['CheckOutDate']) - strtotime($number['CheckInDate'])) / (60 * 60 * 24);

            $nights = (strtotime($number['CheckOutDate']) - strtotime($number['CheckInDate'])) / (60 * 60 * 24);
            if ($arResult['HOURS_ENABLE'])
              $nights = (strtotime($number['CheckOutDate']) - strtotime($number['CheckInDate'])) / (60 * 60);
            ?>
            <?if ($arResult['TotalSum']):?>
              <div class="price_block">
                <div
                  class="price"><?= number_format($number['Cost'], 0, '.', ' ') ?> <?= $number['Currency'] ?></div>
                <div class="info"><?= GetMessage('FOR') ?>
                  <? if ($arResult['HOURS_ENABLE']): ?>
                    <b><?plural_form($nights, array(GetMessage('1HOUR'), GetMessage('2HOURS'), GetMessage('5HOURS'))) ?></b>,
                    <b><?plural_form(count($number['Guests']), array(GetMessage('1GUEST'), GetMessage('2GUESTS'), GetMessage('5GUESTS'))) ?></b>
                  <? else: ?>
                    <b><?plural_form($nights, array(GetMessage('1NIGHT'), GetMessage('2NIGHTS'), GetMessage('5NIGHTS'))) ?></b>,
                    <b><?plural_form(count($number['Guests']), array(GetMessage('1GUEST'), GetMessage('2GUESTS'), GetMessage('5GUESTS'))) ?></b>
                  <? endif; ?>
                </div>
              </div>
            <? endif; ?>
            <div class="h">
              <?= $number['RoomTypeDescription'] ?>
            </div>


            <div class="info">
              <? if ($arResult['HOURS_ENABLE']): ?>
                <b><?= GetMessage('ARRIVAL') ?> <?= $number['CheckInDate'] ?></b>
                <span>|</span>
                <b><?= GetMessage('DEPARTURE') ?> <?= $number['CheckOutDate'] ?></b>
              <? else: ?>
                <b><?= GetMessage('ARRIVAL') ?> <?= $number['CheckInDate'] ?>
                  , <?= $arResult['HOTELS'][0]['HOTEL_TIME_FROM'] ?></b>
                <span>|</span>
                <b><?= GetMessage('DEPARTURE') ?> <?= $number['CheckOutDate'] ?>
                  , <?= $arResult['HOTELS'][0]['HOTEL_TIME'] ?> </b>
              <? endif; ?>
              <? if ($arResult['TotalSum'] && $arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] && (strtotime($number["CheckInDate"]) - $arResult["BAN_TO_CHANGE_RESERVATION_DAYS"] * 86400) > time()): ?>
                <a href="#"
                   class="change_order change_bron_dates"><?= GetMessage('DO_CHANGE_ORDER') ?></a>
              <? endif; ?>
              <br/>
              <?= GetMessage('RATE') ?>: <b><?= $number['RoomRateDescription'] ?></b>
            </div>

            <?if($number["UpgradeRoom"] && (!isset($_REQUEST["is_change_order"]) || $_REQUEST["is_change_order"] == "N") && (!isset($_REQUEST["is_change"]) || $_REQUEST["is_change"] == "N")):?>
              <input type="hidden" name="upgrade_room_type_code" value="<?= $number["UpgradeRoom"]['RoomTypeCode'] ?>">
              <input type="hidden" name="upgrade_room_rate_code" value="<?= $number["UpgradeRoom"]['RoomRateCode'] ?>">
              <?foreach($number["UpgradeRoom"]['AccommodationTypesList'] as $index => $accType):?>
                <input type="hidden" name="upgrade_acc_type_code_<?=$index?>" value="<?= $accType->Code ?>">
                <input type="hidden" name="upgrade_acc_type_age_<?=$index?>" value="<?= $accType->Age ?>">
                <input type="hidden" name="upgrade_acc_type_age_from_<?=$index?>" value="<?= $accType->ClientAgeFrom ?>">
                <input type="hidden" name="upgrade_acc_type_age_to_<?=$index?>" value="<?= $accType->ClientAgeTo ?>">
              <?endforeach;?>
              <div class="info gotech_upgrade_info">
                <div class="upgrade_title"><?= GetMessage('UPGRADE_SPEC_OFFER')?></div>
                <?if(isset($number["UpgradeRoom"]['Picture']['src'])):?>
                <div class="left">
                  <img src="<?= $number["UpgradeRoom"]['Picture']['src'] ?>">
                </div>
                <?endif;?>
                <div class="right">
                  <div><div class="color_scheme_text color_plus">+</div> <b><?= number_format($number["UpgradeRoom"]['Amount'], 0, '.', ' ') ?> <?= $number['Currency'] ?></b> <?= GetMessage('UPGRADE_CATEGORY_UP')?></div>
                  <div><?= GetMessage('UPGRADE_ROOM')?> <b><?=$number["UpgradeRoom"]['RoomTypeDescription']?></b></div>
                  <a href="#" class="gotech_button upgrade_room_button"><?= GetMessage('UPGRADE_UP_BUTTON')?></a>
                </div>

                <div class="gotech_search_result_room_info ui-helper-clearfix" style="display: none">
                  <div class="gotech_search_result_room_main_picture">
                    <div class="main_adaptive_img">
                      <? if (!empty($number["UpgradeRoom"]["MainPicture"]["src"])): ?>
                        <img src="<?= $number["UpgradeRoom"]["MainPicture"]["src"] ?>" width="100%">
                      <? endif; ?>
                    </div>
                  </div>

                  <div class="row">
                    <div class="room_detail_data">
                      <div class="main_img">
                        <? if (!empty($number["UpgradeRoom"]["MainPicture"]["src"])): ?>
                          <img src="<?= $number["UpgradeRoom"]["MainPicture"]["src"] ?>" width="100%">
                        <? endif; ?>
                      </div>
                      <div class="description">
                        <?= $number["UpgradeRoom"]["information_text"] ?>
                      </div>
                    </div>
                    <a href="#" class="gotech_button upgrade_room_button_execute"><?= GetMessage('UPGRADE_UP_BUTTON')?></a>
                    <a href="#" class="gotech_button upgrade_room_button_cancel delete_order"><?= GetMessage('UPGRADE_CANCEL_BUTTON')?></a>
                  </div>
                </div>
              </div>

              <script>
                $('.upgrade_room_button, .upgrade_room_button_cancel').click(function () {
                  var p = $(this).parents('.gotech_upgrade_info');
                  if ($(this).hasClass('upgrade_room_button')) {
                    $('.upgrade_room_button').hide();
                  } else {
                    setTimeout(function() {
                      $('.upgrade_room_button').show();
                    }, 500)
                  }
                  p.find('.gotech_search_result_room_info').slideToggle();
                  p.find('.room_detail_data').slideToggle();

                  if (p.find('.gotech_search_result_room_main_picture').is(':visible')) {
                    p.find(' > .gotech_search_result_room_main_picture').hide();
                  }
                  else {
                    p.find(' > .gotech_search_result_room_main_picture').show();
                  }

                  setTimeout(function () {
                    iframe_resize();
                  }, 500);
                  iframe_resize();

                  return false;
                });
              </script>
            <?endif;?>

            <? $adults = 0 ?>
            <? $kids = 0 ?>
            <? $age_array = array() ?>
            <? foreach ($number['Guests'] as $j => $guest): ?>
              <?
              $uid = $guest["Id"];
              ?>
              <? if ($guest["IsChild"]) {
                $kids++;
                $age_array[] = $guest["Age"];
              } else {
                $adults++;
              } ?>
            <? endforeach; ?>

            <? if ($arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] && (strtotime($number["CheckInDate"]) - $arResult["BAN_TO_CHANGE_RESERVATION_DAYS"] * 86400) > time() && ($_REQUEST["is_change_order"] == 'Y' && $_REQUEST["is_change_room"] == $room_id)): ?>
              <? $APPLICATION->IncludeComponent("onlinebooking:reservation", "", array("TYPE" => "EMBEDED", "PeriodFrom" => $number["CheckInDate"], "PeriodTo" => $number["CheckOutDate"], "ADULTS" => $adults, "KIDS" => $kids, "AGES" => $age_array)); ?>
              <script>
                function hideAgeInputs() {
                  var children = $('[name="children"]').val();
                  $('.gotech_search_window_guests_ages_block .param_block .pblock_label').removeClass('active');
                  $('.gotech_search_window_guests_ages_block .param_block .selectric-gotech_search_window_guests_ages_spinner').hide();
                  for(var i = 0; i < children; i++){
                    $('input[name="shChildrenYear_'+i+'"]').val('true');
                    $('#gotech_search_window_guests_ages_'+i).parent().parent().show();
                    $('#gotech_search_window_guests_ages_'+i).parent().parent().css('display', '-moz-inline-stack');
                    $('#gotech_search_window_guests_ages_'+i).parent().parent().css('display', 'inline-block');
                    $('#gotech_search_window_guests_ages_'+i).parent().parent().parent().find('.pblock_label').addClass('active');
                  }
                }
                setTimeout(hideAgeInputs, 100);
                setTimeout(hideAgeInputs, 200);
                setTimeout(hideAgeInputs, 500);
                setTimeout(hideAgeInputs, 1000);
              </script>
            <?endif;?>

            <div class="guests guests_block">
              <? foreach ($number['Guests'] as $j => $guest): ?>
                <?
                $uid = $guest["Id"];
                ?>
                <? $arRooms[$uid]["Room"] = $number['RoomTypeDescription']; ?>
                <?
                $arRooms[$uid]["fullname"] = $guest['FullName'];
                $arRooms[$uid]["fullname"] = $guest['FullName'];
                $arRooms[$uid]["GUID"] = $guest['GUID'];
                $arRooms[$uid]["GuestAge"] = $guest["Age"];
                $arRooms[$uid]["IsChild"] = $guest["IsChild"];
                $arRooms[$uid]["Nights"] = $nights;
                $arRooms[$uid]["RoomNumber"] = $rkey;
                $arRooms[$uid]["from"] = $number['CheckInDate'];
                $arRooms[$uid]["to"] = $number['CheckOutDate'];
                ?>
                <div class="guest" data-key="<?= $uid ?>" data-n="<?= $j ?>">

                  <? if ($arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] && (strtotime($number["CheckInDate"]) - $arResult["BAN_TO_CHANGE_RESERVATION_DAYS"] * 86400) > time() && ($_REQUEST["is_change"] == 'Y' && $_REQUEST["is_change_room"] == $room_id)): ?>
                    <? if ($guest["IsChild"]) {
                      if ($guest["Age"] == 1) {
                        $placeholder = GetMessage("KID") . " (" . $guest["Age"] . GetMessage("1AGES") . ")";
                      } elseif ($guest["Age"] >= 2 && $guest["Age"] < 5) {
                        $placeholder = GetMessage("KID") . " (" . $guest["Age"] . GetMessage("2AGES") . ")";
                      } elseif ($guest["Age"] >= 5) {
                        $placeholder = GetMessage("KID") . " (" . $guest["Age"] . GetMessage("AGES") . ")";
                      } else {
                        $placeholder = GetMessage("KID") . " (" . GetMessage("AGES_LESS_THAN_ONE") . ")";
                      }
                    } else {
                      $placeholder = GetMessage("ADULT");
                    } ?>
                    <input type="hidden" name="guid" value="<?= $guest["GUID"] ?>">
                    <input type="hidden" name="is_annulation" value="N">
                    <input type="hidden" name="room_quota" value="<?= $guest["RoomQuota"] ?>">
                    <input type="hidden" name="acc_type_code" value="<?= $guest["AccTypeCode"] ?>">
                    <input type="hidden" name="res_status_code"
                           value="<?= $guest["ResStatusCode"] ?>">
                    <input type="hidden" name="defaultChildPlaceholder"
                           value="<?= GetMessage("KID") . " (" . GetMessage("AGES_LESS_THAN_ONE") . ")" ?>">
                    <input type="hidden" name="isChild"
                           value="<? if ($guest["IsChild"]): ?>Y<? else: ?>N<? endif; ?>">
                    <input type="hidden" name="age" value="<?= $guest["Age"] ?>">
                    <input type="hidden" name="code" value="<?= $guest["Code"] ?>">

                    <div class="h"><?= GetMessage('1GUEST') ?> <span><?= ($j + 1) ?></span></div>
                    <div class="guest_data">
                      <div
                        class="name"><?= $guest['FullName'] ?: '' . GetMessage('1GUEST') . ' ' . ($j + 1) ?></div>


                      <div class="services" style="display:none;">
                        <? $ii = 1; ?>
                        <? foreach ($services_guest[$uid] as $jj => $gg): ?>
                          <div class="service" data-guest="<?= $uid ?>"
                               data-code="<?= $gg['Code'] ?>"
                               data-price="<?= $gg['Price'] ?>">
                            <?= $ii ?>
                            . <? //=$arResult['SERVICES_NAMES'][$gg['Id']]?> <?= $gg['Name'] ?>
                            |
                            <b><?= $gg['Price'] ?><? //=$arResult['SERVICES_PRICES'][$gg['Id']]?>
                              Р</b>
                            <?if($gg["Code"]):?>
                              <a href="#" class="delete delete_from_bron"></a><input
                                type="hidden" name="service_price_<?= $gg['Id'] ?>"
                                class="service_price" value="<?= $gg['Price'] ?>">
                            <?endif;?>
                          </div>
                          <? $ii++; ?>
                        <? endforeach; ?>
                      </div>

                      <? /*<input type="hidden" name="is_transfer" value="1" />*/ ?>


                      <div class="param_blocks">


                        <div
                          class="param_block gotech_guests_information_item_content_guest_lastname">
                                                    <span
                                                      class="gotech_guests_information_item_content_guest_lastname_input_label pblock_label active"><span><?= GetMessage('LAST_NAME') ?></span></span>
                          <input class="last_name" name="surname"
                                 placeholder="<?= GetMessage('LAST_NAME') ?>"
                                 value="<?= $guest['LastName'] ?>">
                        </div>
                        <div
                          class="param_block gotech_guests_information_item_content_guest_firstname">
                                                    <span
                                                      class="gotech_guests_information_item_content_guest_firstname_input_label pblock_label active"><span><?= GetMessage('NAME') ?></span></span>
                          <input class="first_name" name="name"
                                 placeholder="<?= GetMessage('NAME') ?>"
                                 value="<?= $guest['FirstName'] ?>">
                        </div>
                        <div
                          class="param_block gotech_guests_information_item_content_guest_secondname">
                                                    <span
                                                      class="gotech_guests_information_item_content_guest_secondname_input_label pblock_label active"><span><?= GetMessage('SECOND_NAME') ?></span></span>
                          <input class="second_name" name="secondName"
                                 placeholder="<?= GetMessage('SECON_NAME') ?>"
                                 value="<?= $guest['SecondName'] ?>">
                        </div>

                        <? if ($arResult["PROPERTY_BIRTHDAY"]): ?>
                          <div
                            class="param_block gotech_guests_information_item_content_guest_birthday">
                                                        <span
                                                          class="gotech_guests_information_item_content_guest_birthday_input_label pblock_label active"><span><?= GetMessage("DATE_OF_BIRTH") ?></span></span>
                            <input class="bday"
                                   placeholder="<?= GetMessage("DATE_OF_BIRTH") ?>"
                                   name="birthday"
                                   value="<? if ($guest["BirthDate"] != '01.01.0001'): ?><?= $guest["BirthDate"] ?><? endif; ?>"
                                   pattern="[0-9]*">
                          </div>
                        <? endif; ?>

                        <? if (true || $arResult["INPUT_GUEST_PASSPORT_DATA"]): ?>
                          <br>
                          <div
                            class="param_block gotech_guests_information_item_content_guest_document_type">
                                                <span
                                                  class="pblock_label active gotech_guests_information_item_content_guest_document_type_label"><span><?= GetMessage("DOCUMENT_TYPE") ?></span></span>
                            <select
                              name="ClientIdentityDocumentType"
                              id=""
                              class="gotech_guests_information_item_content_guest_document_type_spinner document_type_selector">
                              <? foreach ($arResult["DOC_TYPES"] as $k => $doc_type): ?>
                                <option
                                  value="<?= $k ?>" <? if ($guest['ClientIdentityDocumentType'] == $k): ?> selected="selected"<? endif; ?>><?= $doc_type ?></option>
                              <? endforeach; ?>
                            </select>
                          </div>
                          <div
                            class="param_block gotech_guests_information_item_content_guest_document_series">
                                                <span
                                                  class="pblock_label gotech_guests_information_item_content_guest_document_series_input_label"><span><?= GetMessage("PASSPORT_SERIES") ?></span></span>
                            <input type="text"
                                   class="gotech_guests_information_item_content_guest_document_series_input"
                                   name="ClientIdentityDocumentSeries"
                                   placeholder="<?= GetMessage("PASSPORT_SERIES") ?>"
                                   value="<?= htmlspecialchars($guest['ClientIdentityDocumentSeries']) ?>">
                          </div>
                          <div
                            class="param_block gotech_guests_information_item_content_guest_document_number">
                                                <span
                                                  class="pblock_label gotech_guests_information_item_content_guest_document_number_input_label"><span><?= GetMessage("PASSPORT_NUMBER") ?></span></span>
                            <input type="text"
                                   class="gotech_guests_information_item_content_guest_document_number_input"
                                   name="ClientIdentityDocumentNumber"
                                   placeholder="<?= GetMessage("PASSPORT_NUMBER") ?>"
                                   value="<?= htmlspecialchars($guest["ClientIdentityDocumentNumber"]) ?>">
                          </div>
                          <div
                            class="param_block gotech_guests_information_item_content_guest_document_date">
                                                <span
                                                  class="pblock_label gotech_guests_information_item_content_guest_document_date_input_label"><span><?= GetMessage("DOCUMENT_DATE") ?></span></span>
                            <input type="text" placeholder="<?= GetMessage("DOCUMENT_DATE") ?>"
                                   name="ClientIdentityDocumentIssueDate"
                                   class="guest_passport_valid_day gotech_guests_information_item_content_guest_document_date_input"
                                   value="<?= $guest["ClientIdentityDocumentIssueDate"]?>" pattern="[0-9]*">
                          </div>
                            <div
                                    class="param_block gotech_guests_information_item_content_guest_document_unit_code">
                                                <span
                                                        class="pblock_label gotech_guests_information_item_content_guest_document_unit_code_input_label"><span><?= GetMessage("PASSPORT_UNIT_CODE")?></span></span>
                                <input type="text"
                                       class="gotech_guests_information_item_content_guest_document_unit_code_input"
                                       name="ClientIdentityDocumentUnitCode"
                                       placeholder="<?= GetMessage("PASSPORT_UNIT_CODE")?>"
                                       value="<?= htmlspecialchars($guest["ClientIdentityDocumentUnitCode"]) ?>">
                            </div>
                            <div
                                    class="param_block gotech_guests_information_item_content_guest_document_issued_by">
                                                <span
                                                        class="pblock_label gotech_guests_information_item_content_guest_document_issued_by_input_label"><span><?= GetMessage("PASSPORT_ISSUED_BY")?></span></span>
                                <input type="text"
                                       class="gotech_guests_information_item_content_guest_document_issued_by_input"
                                       name="ClientIdentityDocumentIssuedBy"
                                       placeholder="<?= GetMessage("PASSPORT_ISSUED_BY")?>"
                                       value="<?= htmlspecialchars($guest["ClientIdentityDocumentIssuedBy"]) ?>">
                            </div>
                        <? endif; ?>
                        <? if ($arResult["INPUT_GUEST_ADDRESS"]): ?>
                          <br>
                          <div
                            class="param_block gotech_guests_information_item_content_guest_address"
                            style="width: 100%;">
                                                <span
                                                  class="pblock_label gotech_guests_information_item_content_guest_address_input_label"><span><?= GetMessage("CUSTOMER_ADDRESS") ?></span></span>
                            <input type="text"
                                   class="gotech_guests_information_item_content_guest_address_input"
                                   name="address"
                                   placeholder="<?= GetMessage("CUSTOMER_ADDRESS") ?>"
                                   value="<?= htmlspecialchars($guest["Address"]) ?>">
                          </div>
                        <? endif; ?>
                          <? if ($arResult["INPUT_GUEST_PASSPORT_DATA"]): ?>
                              <? foreach ($arResult["DOC_PHOTO_TYPES"] as $k => $doc_photo_type_arr): ?>
                                <div>
                                  <? foreach ($doc_photo_type_arr as $doc_photo_type): ?>
                                      <div
                                              class="param_block gotech_guests_information_item_content_guest_document_photo document_photo_block_<?=$k?>" style="<? if ($guest['ClientIdentityDocumentType'] == $k): ?> display: block <?else:?> display: none <?endif;?>">
                                          <button class="gotech_button photo_upload_button"><?= $doc_photo_type?></button>
                                          <input type="file"
                                                 class="gotech_guests_information_item_content_guest_document_photo_input"
                                                 name="ClientIdentityDocumentPhoto<?=$k?>">
                                          <input type="hidden" name="mimetype_<?=$k?>">
                                          <input type="hidden" name="base64_<?=$k?>">
                                          <span style="display: none"><?= $doc_photo_type?>: </span><span style="display: none" class="photo_name"></span>
                                          <span style="display: none" class="color_scheme_text remove_selected_photo">&#10006;</span>
                                      </div>
                                  <? endforeach; ?>
                                </div>
                              <? endforeach; ?>
                          <?endif;?>
                      </div>

                    </div>
                  <? else: ?>
                    <? if ($guest["IsChild"]) {
                      if ($guest["Age"] == 1) {
                        $placeholder = GetMessage("KID") . " (" . $guest["Age"] . GetMessage("1AGES") . ")";
                      } elseif ($guest["Age"] >= 2 && $guest["Age"] < 5) {
                        $placeholder = GetMessage("KID") . " (" . $guest["Age"] . GetMessage("2AGES") . ")";
                      } elseif ($guest["Age"] >= 5) {
                        $placeholder = GetMessage("KID") . " (" . $guest["Age"] . GetMessage("AGES") . ")";
                      } else {
                        $placeholder = GetMessage("KID") . " (" . GetMessage("AGES_LESS_THAN_ONE") . ")";
                      }
                    } else {
                      $placeholder = GetMessage("ADULT");
                    } ?>
                    <input type="hidden" name="guid" value="<?= $guest["GUID"] ?>">
                    <input type="hidden" name="is_annulation" value="N">
                    <input type="hidden" name="room_quota" value="<?= $guest["RoomQuota"] ?>">
                    <input type="hidden" name="acc_type_code" value="<?= $guest["AccTypeCode"] ?>">
                    <input type="hidden" name="res_status_code"
                           value="<?= $guest["ResStatusCode"] ?>">
                    <input type="hidden" name="defaultChildPlaceholder"
                           value="<?= GetMessage("KID") . " (" . GetMessage("AGES_LESS_THAN_ONE") . ")" ?>">
                    <input type="hidden" name="isChild"
                           value="<? if ($guest["IsChild"]): ?>Y<? else: ?>N<? endif; ?>">
                    <input type="hidden" name="age" value="<?= $guest["Age"] ?>">
                    <input type="hidden" name="code" value="<?= $guest["Code"] ?>">
                    <input type="hidden" class="last_name" name="surname"
                           value="<?= $guest['LastName'] ?>">
                    <input type="hidden" class="first_name" name="name"
                           value="<?= $guest['FirstName'] ?>">
                    <input type="hidden" class="second_name" name="secondName"
                           value="<?= $guest['SecondName'] ?>">
                    <input type="hidden" class="bday" name="birthday"
                           value="<? if ($guest["BirthDate"] != '01.01.0001'): ?><?= $guest["BirthDate"] ?><? endif; ?>"
                           pattern="[0-9]*">


                    <div class="h"><?= GetMessage('1GUEST') ?> <?= ($j + 1) ?></div>
                    <div class="guest_data">
                      <div
                        class="name"><?= $guest['FullName'] ?: '' . GetMessage('1GUEST') . ' ' . ($j + 1) ?></div>
                      <? if ($arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] && (strtotime($number["CheckInDate"]) - $arResult["BAN_TO_CHANGE_RESERVATION_DAYS"] * 86400) > time()): ?>
                        <a href="#"
                           class="change_order change_bron"><?= GetMessage('DO_CHANGE') ?></a>
                        <span class="gotech_my_info"><?= GetMessage('FILL_YOUR_DATA') ?></span>
                      <? endif; ?>

                      <? $ii = 1; ?>
                      <div class="services">
                        <? foreach ($services_guest[$uid] as $jj => $gg): ?>
                          <div class="service service<?= $gg['Id'] ?>"
                               data-id="<?= $gg['Id'] ?>" data-uid="<?= $gg['Id'] ?>"
                               data-guest="<?= $uid ?>" data-code="<?= $gg['Code'] ?>"
                               data-price="<?= $gg['Price'] ?>">
                            <?= $ii ?>
                            . <? //=$arResult['SERVICES_NAMES'][$gg['Id']]?> <?= $gg['Name'] ?>
                            |
                            <b><?= $gg['Price'] ?><? //=$arResult['SERVICES_PRICES'][$gg['Id']]?>
                              Р</b>
                            <?if($gg["Code"]):?>
                              <a href="#" class="delete delete_from_bron"></a><input
                                type="hidden" name="service_price_<?= $gg['Id'] ?>"
                                class="service_price" value="<?= $gg['Price'] ?>">
                            <?endif;?>
                          </div>
                          <? $ii++; ?>
                        <? endforeach; ?>
                      </div>
                    </div>
                  <? endif ?>
                </div>
              <? endforeach; ?>

              <? if ($arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] && (strtotime($number["CheckInDate"]) - $arResult["BAN_TO_CHANGE_RESERVATION_DAYS"] * 86400) > time() && ($_REQUEST["is_change"] == 'Y' && $_REQUEST["is_change_room"] == $room_id)): ?>
                <input type="hidden" name="date_of_birth_is_necessary"
                       value="<?= $arResult["PROPERTY_DATE_OF_BIRTH_NECESSARY"] ?>">
                <div id="gotech_search_window_guests" <?if($_REQUEST["is_change"] == 'Y'):?>style="display: none"<?endif;?>>
                  <? if ($arResult["HOTEL_MAX_ADULT"] > 0 || 1): ?>

                    <div class="param_block spinner_block adults_block"
                         data-max="<?= $arResult["HOTEL_MAX_ADULT"] ?>" style="float:left;">

                      <label
                              class="pblock_label active"><span><? if ($arResult["HOTEL"]["HOTEL_MAX_CHILDREN"] > 0): ?><?= GetMessage("ADULTS") ?><? else: ?><?= GetMessage("ADULTS") ?><? endif; ?></span></label>

                      <input type="hidden" name="adults" value="<?= $adults ?>">
                      <div class="spinner_container">
                        <div class="gotech_search_window_guests_spinner_prev spinner_prev"
                             onselectstart="return false" onmousedown="return false"></div>
                        <span class="number_field"><?= $adults ?></span>
                        <div
                                class="gotech_search_window_guests_spinner_next_active spinner_next"
                                onselectstart="return false" onmousedown="return false"></div>
                      </div>
                    </div>
                  <? endif; ?>
                  <? if ($arResult["HOTEL_MAX_CHILDREN"] > 0): ?>
                    <div class="param_block spinner_block children_block"
                         data-max="<?= $arResult["HOTEL_MAX_CHILDREN"] ?>" style="float:left;">

                      <label
                              class="pblock_label"><span><?= GetMessage("CHILDREN") ?></span></label>


                      <input type="hidden" name="children" value="<?= $kids ?>">
                      <div class="spinner_container">
                        <div class="gotech_search_window_guests_spinner_prev spinner_prev"
                             onselectstart="return false" onmousedown="return false"></div>
                        <span class="number_field"><?= $kids ?></span>
                        <div
                                class="gotech_search_window_guests_spinner_next_active spinner_next"
                                onselectstart="return false" onmousedown="return false"></div>
                      </div>
                    </div>
                    <div class="gotech_search_window_guests_ages_block" style="float:left;">
                      <? for ($i = 1; $i <= $arResult["HOTEL_MAX_CHILDREN"]; $i++): ?>
                        <div class="param_block" style="display:none;">
                          <label
                                  class="pblock_label active"><span><?= GetMessage("AGE") ?> <?= $i ?> <?= GetMessage("CHILD") ?></span></label>
                          <input type="hidden" name="shChildrenYear_<?= ($i - 1) ?>"
                                 value="false">
                          <select class="gotech_search_window_guests_ages_spinner"
                                  name="childrenYear_<?= ($i - 1) ?>">
                            <? for ($y = 0; $y <= 17; $y++): ?>
                              <option value="<?= $y ?>"
                                      <? if (!empty($age_array[$i - 1]) && $age_array[$i - 1] == $y): ?>selected="selected"<? endif; ?>><? if ($y === 0): ?><?= " < 1 " ?><? else: ?><?= $y ?><? endif; ?></option>
                            <? endfor; ?>
                          </select>
                        </div>
                      <? endfor; ?>
                    </div>
                    <div style="clear:both;"></div>
                    <br/><br/>

                  <? endif; ?>
                </div>
              <? endif; ?>

              <? if ($arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] && (strtotime($number["CheckInDate"]) - $arResult["BAN_TO_CHANGE_RESERVATION_DAYS"] * 86400) > time() && ($_REQUEST["is_change"] == 'Y' && $_REQUEST["is_change_room"] == $room_id)): ?>
                <div class="gotech_my_content_item_footer">
                                    <span class="gotech_my_content_item_footer_new_price"
                                          style="display:none;"><?= GetMessage("NEW_PRICE") ?><span></span></span>
                  <br/>
                  <span class="gotech_button" id="find_accommodation_link_wait" style="display:none;">
										<span class="inner_text">
											<span
                        id="find_accommodation_link_wait_text"><?= GetMessage("FIND_ACCOMMODATION_LINK_WAIT") ?></span>
											<span id="gotech_progress_icon"></span>
										</span>
									</span>
                  <a class="gotech_button" href="#" id="find_accommodation_link" target="_blank"
                     style="display:none;">
										<span class="inner_text">
											<?= GetMessage("FIND_ACCOMMODATION_LINK") ?>
										</span>
                  </a>
                  <span class="gotech_button" id="save_changes_link_wait" style="display:none;">
										<span class="inner_text">
											<span
                        id="save_changes_link_wait_text"><?= GetMessage("SAVE_CHANGES_LINK_WAIT") ?></span>
											<span id="gotech_progress_icon"></span>
										</span>
									</span>
                  <a class="gotech_button" href="#" id="save_changes_link" target="_blank">
										<span class="inner_text">
											<?= GetMessage("SAVE_CHANGES_LINK") ?>
										</span>
                  </a>
                </div>
              <? endif; ?>


            </div>


          </div>
          <div style="clear:both;"></div>
        </div>

        <? $room_id++ ?>
      <? endforeach; ?>

    </div>


    <div class="contact_info">

      <div class="h"><?= GetMessage('CONTACT_PERSON') ?></div>

      <div class="common_data">
        <b><?= $arResult['CONTACT_PERSON'] ?></b><br/>
        <? if ($arResult['CONTACT_PERSON_PHONE']): ?>
          <b><?= $arResult['CONTACT_PERSON_PHONE'] ?></b><br/>
        <? endif; ?>
        <? if ($arResult['CONTACT_PERSON_EMAIL']): ?>
          <a href="mailto:m.ivan@yandex.ru"><u><?= $arResult['CONTACT_PERSON_EMAIL'] ?></u></a>
        <? endif; ?>
        <? /*
				<br/><br/>
				<div class="auto">Номер автомобиля: <b>А877НР 92RUS</b></div>
				<div class="special_requests">Особые пожелания: <b>нет</b></div>
				*/ ?>
      </div>

      <?
      $canceldate = strtotime(str_replace('T', ' ', $arResult["CancelFeeDate"]));
      $time = time();
      ?>

      <? if ($arResult["CancelFeeDate"] && $canceldate > $time): ?>
        <div class="bordered_info">
          <? if (!$arResult['CancelFeeAmount'] || $arResult['CancelFeeAmount'] == 0): ?>
            <span>!</span>
            Беспланая отмена бронирования до
            <b><?= date('d.m.Y', strtotime($arResult["CancelFeeDate"])) ?></b>
            <a href="#" id="cancel"><?= GetMessage('DELETE') ?></a>
          <? else: ?>
            <span>!</span>
            Отмена брони со штрафом <b><?= number_format($arResult['CancelFeeAmount'], 0, '.', ' ') ?>
              </b> до
            <b><?= date('d.m.Y', strtotime($arResult["CancelFeeDate"])) ?></b>
            <a href="#" id="cancel"><?= GetMessage('DELETE') ?></a>
          <? endif; ?>


        </div>
      <? endif; ?>

      <div class="terms_text">
        <div class="terms_text_wrap">
          <div class="h">Условия бронирования и отмены</div>
          <? /*<b>В случае отсутствия полной или частичной оплаты, бронирование будет аннулировано по истечении 15­-ти минут</b>*/ ?>
          <p>
            <?= $arResult["ReservationConditions"] ?>
          </p>
        </div>
      </div>

    </div>

    <?if ($arResult['TotalSum']):?>
      <div class="common_price_info">
        <div class="price_info">
          <?
          if (!$arResult['TotalSum'])
            $arResult['TotalSum0'] = -1 * $arResult["BalanceAmount"];


          if ($arResult['TotalSum']) {
            $total = $arResult['TotalSum'];
          }
          $need_pay = $total - $arResult["AlreadyPaid"];
          if ($need_pay < 0) {
            $need_pay = 0;
          }
          ?>
          <?= GetMessage('ITOGO') ?> <span id="total"
                                           data-price="<?= $total ?>"><?= number_format($total, 0, '.', ' ') ?> <?= $arResult["Currency"] ?></span>
          <br/>
          <?= GetMessage('PAID') ?> <span id="payed"
                                          data-price="<?= $arResult["AlreadyPaid"] ?>"><?= number_format($arResult["AlreadyPaid"], 2, ',', ' ') ?> <?= $arResult["Currency"] ?></span>
          <br/>
          <?= GetMessage('ITOGO2') ?> <span id="left"
                                            data-price="<?= $need_pay ?>"><?= number_format($need_pay, 2, ',', ' ') ?> <?= $arResult["Currency"] ?></span>
        </div>

        <div class="change_service_button">

          <div class="added_services">

          </div>

          <a href="#" class="gotech_button change_service_b"><span id="gotech_progress_icon"></span><span class="to_hide"><?= GetMessage('SAVE_SERVICE_CHANGES') ?></span></a>
        </div>

      </div>
    <?endif;?>

    <? if (isset($_REQUEST["hotel_code"]) && count($arResult["Services"])): ?>
      <br/><br/>
      <div id="gotech_guests_information_services">
        <div class="gotech_services">
          <? $APPLICATION->IncludeComponent("onlinebooking:reservation.find", "", array("TYPE" => "SERVICE", 'MIN_TRANSFER' => $arResult["MIN_TRANSFER"], "SERVICES" => $arResult["Services"], "ROOMS" => $arRooms, "CURRENCY" => $number["Currency"], "HOTEL_ID" => $arResult["HOTEL"]["ID"], "SELECTED_SERVICES" => $services, "NIGHTS" => $nights)); ?>
        </div>
      </div>
    <? endif; ?>


    <div style="margin-top: 35px;margin-bottom: 30px;">
      <? if ($need_pay): ?>
        <? if (COption::GetOptionString('gotech.hotelonline', 'includePaySys') == 1 && !$arResult['IS_AGENT']): ?>
          <?
            $bonuses_payment_is_enabled = isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer) && isset($_SESSION["BONUS_AMOUNT"]) && intVal($_SESSION["BONUS_AMOUNT"]) > 0;
          ?>
          <? if ((isset($arResult["PAYMENT_METHODS"]) && !empty($arResult["PAYMENT_METHODS"])) || (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer) && isset($_SESSION["BONUS_AMOUNT"]))): ?>
            <div class="h">
              <?= ($arResult["PAYMENT_METHOD_TEXT"] ? $arResult["PAYMENT_METHOD_TEXT"] : GetMessage('MAY_PAY')) ?>:
            </div>
            <div class="payments_mobile" style="display:block;margin-top:40px;">
              <?
              $fullSum = $need_pay;
              $firstDaySum = $arResult["FirstDaySum"];
              $isFirst = true;
              $is_legal = false;
              ?>

              <? if (isset($arResult["PAYMENT_METHODS"]) && !empty($arResult["PAYMENT_METHODS"])): ?>
                <? foreach ($arResult["PAYMENT_METHODS"] as $key => $method): ?>

                  <label class="payment">
                    <?
                      $full_sum_to_pay = 0;
                    ?>
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
                      <? $discount_sum = (FloatVal($total) * IntVal($method["DISCOUNT"])) / 100 ?>
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

                      <?if($bonuses_payment_is_enabled):?>
                        <?
                          $max_sum_to_pay = $full_sum_to_pay > FloatVal($_SESSION["BONUS_AMOUNT"]) ? $_SESSION["BONUS_AMOUNT"] : $full_sum_to_pay;

                          if (!is_null($arResult["BonusesLimit"]) && FloatVal($arResult["BonusesLimit"]) < FloatVal($max_sum_to_pay)) {
                            $max_sum_to_pay = $arResult["BonusesLimit"];
                          }
                        ?>
                        <?if(FloatVal($max_sum_to_pay)):?>
                          <p>
                            <?= GetMessage('BONUSES_PAYMENT_DETAILS'); ?>
                          </p>
                          <div class="param_block bonuses_param_block bonuses_sum_block">
                            <label class="active pblock_label no-focus">
                              <span><?= GetMessage('BONUSES_PAYMENT_SUM_LABEL'); ?></span>
                            </label>
                            <input name="bonuses_payment_sum" placeholder="" data-max="<?= $max_sum_to_pay ?>"
                                   value="">
                            <div>(max: <?=number_format(Round($max_sum_to_pay, 2), 2, ',', ' ')?> <?=$number["Currency"]?>)</div>
                          </div>
                          <div class="param_block bonuses_param_block bonuses_sms_code_block" style="display: none">
                            <label class="active pblock_label no-focus">
                              <span><?= GetMessage('BONUSES_PAYMENT_SMS_CODE_LABEL'); ?></span>
                            </label>
                            <input name="bonuses_payment_sms_code" placeholder="">
                            <input type="hidden" name="bonuses_payment_trans_id">
                          </div>
                          <br>
                          <span><?=GetMessage("TO_PAY_TEXT")?> <span class="need_to_pay_price"><?=number_format(Round($full_sum_to_pay, 2), 2, ',', ' ')?></span><?=$number["Currency"]?></span>
                          <br><br>

                          <div class="gotech_error_text bonuses_message_text"
                               style="display: none"><?= GetMessage('BONUSES_PAYMENT_ERROR_TEXT'); ?></div>
                          <div class="gotech_success_text bonuses_message_text"
                               style="display: none"><?= GetMessage('BONUSES_PAYMENT_SUCCESS_TEXT'); ?></div>
                          <a href="#"
                             class="gotech_button sms_ok" style="display: none">
                            <?= GetMessage('OK') ?>
                          </a>
                        <?endif;?>

                      <?endif;?>
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
              <? endif; ?>
            </div>


            <div class="gotech_clear"></div>
          <? endif; ?>
        <? endif; ?>
      <? endif ?>
      <br/>
      <? $link = COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER') . "payment/payment.php?inv_id=" . $arResult["GuestGroup"] . "&amp;hotel_id=" . $arResult["HOTELS"][0]["ID"] . "&amp;client_code=" . $arResult["GuestCode"] . "&amp;Currency=" . $arResult["CurrencyPayCode"] . "&amp;CurrencyCode=" . $arResult["CurrencyCode"] . "&amp;inv_desc=" . $arResult["GuestFullName"] . "&amp;first_name=" . $arResult["GuestFirstName"] . "&amp;last_name=" . $arResult["GuestLastName"] . "&amp;lang=" . OnlineBookingSupport::getLanguage() . "&amp;email=" . $arResult["CONTACT_PERSON_EMAIL"] . "&amp;phone=" . $arResult["CONTACT_PERSON_PHONE"] . "&amp;hotel=" . $arResult["HotelCode"] . "&amp;hotel_name=" . $arResult["HotelName"] . "&amp;uuid=" . $arResult["UUID"]; ?>

      <div class="gotech_clear"><br/></div>

      <a id="pay_link" href="<?= $link ?>" style="display:none"></a>

      <a href="#"
         onclick="$('body').addClass('print');window.print();$('body').removeClass('print');return false;"><?= GetMessage('PRINT') ?></a>

    </div>


  </div>
<? elseif (isset($arResult["SUCCESS"]) && !empty($arResult["SUCCESS"])): ?>
  <div id="gotech_booking_order" style="padding:20px;">
    <div class="gotech_success_text gotech_big_text h"><?= $arResult["SUCCESS"] ?></div>
  </div>
<? else: ?>
  <div id="gotech_booking_order" style="padding:20px;">
    <div class="err" style="color:red;"><?= $arResult["ERROR"] ?></div>
    <?if (!empty($arResult["ERROR"])):?>
    <a class="gotech_button" href="#" id="reservation_again" onclick="new_search_click(event, pathwl)" style="position: relative;top: 15px;"><?= GetMessage("RESERVATION_AGAIN") ?></a>
    <?endif;?>
  </div>
<? endif; ?>

<div id="gotech_annulation_dialog" class="jewelery-popup mfp-hide">
  <a href="#" class="mfp-close"></a>
  <? if ($arResult["DO_ANNULATION"]): ?>
    <div class="h"><?= GetMessage("You_shure") ?></div>
    <div class="gotech_cancel_button" style="margin-top:30px;text-align:center;">
      <a href="#" id="cancel_yes" class="gotech_button"
         title="<?= GetMessage("YES") ?>"><span><?= GetMessage("YES") ?></span></a>
      <a href="#" id="cancel_no" class="gotech_button"
         title="<?= GetMessage("NO") ?>"><span><?= GetMessage("NO") ?></span></a>
    </div>
  <? else: ?>
    <? if (empty($arResult["BOOKING_HOTEL"]["HOTEL_PHONE"])): ?>
      <div id="gotech_annulation_text" class="h"><?= GetMessage("CANNOT_ANNULATION") ?></div>
    <? else: ?>
      <div id="gotech_annulation_text"
           class="h"><?= GetMessage("CANNOT_ANNULATION_WITH_PHONE") . " " . $arResult["BOOKING_HOTEL"]["HOTEL_PHONE"] ?></div>
    <? endif; ?>
    <div class="gotech_cancel_button" style="margin-top:20px;">
      <a href="#" id="cancel_no" class="gotech_blue_button"
         title="<?= GetMessage("OK") ?>"><span><?= GetMessage("OK") ?></span></a>
    </div>
  <? endif; ?>
</div>
<script>

  if ($('.document_type_selector').length) {
    $('.document_type_selector').selectric({
      maxHeight: 160,
      disableOnMobile: false,
    });
  }

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

  function recount() {
    var total = 0;

    $('.booking_order_item').each(function () {

      total += Number($(this).find('[name=price]').val());

    });
    console.log(total);

    $('.guest_data').each(function () {
      if ($(this).find('.for_delete').length)
        total -= Number($(this).find('.for_delete').data('price'));
    });

    console.log(total);
    $('.guest_data').each(function () {

      if ($(this).find('.new_service').length)
        total += Number($(this).find('.new_service').data('price'));

    });

    console.log(total);

    $('#total').html(total + ' <span class="gotech_ruble">a</span>');

    var left = Number($('#left').data('price'));
    var payed = Number($('#payed').data('price'));
    left_new = total - payed;

    $('#left').html(left_new + ' <span class="gotech_ruble">a</span>');

  }


  $(document).ready(function () {

    $('[name=TransferTime]').mask('00:00', {placeholder: "__:__"});

    $('body').on('click', '.guest_data .service .delete, .added_services .service .delete', function () {

      if ($(this).parent().hasClass('new_service')) {
        var g = $(this).parents('.service').data('guest');
        var id = $(this).parents('.service').data('id');
        var price = $(this).parents('.service').data('price');

        $('.new_service[data-guest="' + g + '"][data-id="' + id + '"][data-price="' + price + '"]').remove();

        //$(this).parent().remove();
      }
      else {
        $(this).parent().addClass('for_delete').css('text-decoration', 'line-through');
        $(this).hide();

        $('.change_service_button').show();
        $('.change_service_button').css('display', 'inline-block');
      }

      recount();
      return false;
    });
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
      if (!transfer_time)
        p.find('[name="TransferTime"]').addClass('err');
      if (!transfer_remarks)
        p.find('[name="TransferRemarks"]').addClass('err');
      if (!transfer_childseats)
        p.find('[name="TransferChildSeats"]').addClass('err');

      if (!transfer_date || !transfer_time || !transfer_remarks || !transfer_childseats)
        return false;
    }
    //pp.css('border','1px solid red');

    var sid = p.data('service_id');
    //if(p.data('days') == 'Y')
    sid = p.data('code');


    var name = p.find('.service_h').text();
    //var price = p.find('.price').html();
    var price;
    var code = p.data('code');
    var hotel_id = $('[name=hotel_id]').val();

    var gd;

    var checked_arr = new Array();
    var price_arr = new Array();
    var id_arr = new Array();
    var n;

    if (transfer) {
      $('[data-key="0_0"]').find('.services .service_transfer').remove();

      code = p.find('[name="transfer_type"] option:selected').data('code');
      sid = p.find('[name="transfer_type"] option:selected').val();
      var destination = $.trim(p.find('[name="transfer_type"] option:selected').text());

      n = $('[data-key="0_0"]').find('.services .service').length + 1;
      price = p.find('[name="transfer_type"] option:selected').data('price');

      if (!$('[data-key="0_0"]').find('.services .service' + sid).length)
        $('[data-key="0_0"]')
          .find('.services').append('' +
          '<div class="service service' + sid + ' service_transfer new_service" data-price="' + price + '" data-code="' + code +
          '" data-uid="' + sid + '" data-guest="0_0" data-id="' + sid +
          '" data-tdestination="' + destination + '" data-tdate="' + transfer_date + '" data-ttime="' + transfer_time + '" data-tremarks="' + transfer_remarks + '" data-tchildseats="' + transfer_childseats + '">' +
          '<span class="pn">' + (n) + '</span>. ' + name + '    |   <b>' + price + ' <span class="gotech_ruble">a</span></b> <a href="#" class="delete"></a>' +
          '<input type="hidden" name="service_price_' + sid + '" class="service_price" value="' + price + '"/>' +
          '<input type="hidden" name="is_transfer" value="1" />' +
          '</div>');

      $('.added_services').append('' +
        '<div class="service service' + sid + ' service_transfer new_service" data-price="' + price + '" data-code="' + code + '" data-uid="' + sid + '" data-guest="0_0" data-id="' + sid +
        '" data-tdestination="' + destination + '" data-tdate="' + transfer_date + '" data-ttime="' + transfer_time + '" data-tremarks="' + transfer_remarks + '" data-tchildseats="' + transfer_childseats + '">' +
        '<span class="pn">' + (n) + '</span>. ' + name + '    |   <b>' + price + ' <span class="gotech_ruble">a</span></b> <a href="#" class="delete"></a>' +
        '<input type="hidden" name="service_price_' + sid + '" class="service_price" value="' + price + '"/>' +
        '<input type="hidden" name="is_transfer" value="1" />' +
        '</div>');


      $('#gotech_booking_services .service_item[data-transfer="Y"]').each(function () {
        $(this).find('.gotech_button').hide();
        $(this).find('.added_info').show();
      });


    }
    else {
      p.find('.guests_service_block .guest').each(function () {

        var c = $(this).find('[type=checkbox]');
        var cv = c.val();

        //if($('[data-key="'+cv+'"]').find('.services .service'+sid).length && !c.is(':checked'))
        if ($('[data-key="' + cv + '"]').find('.services .service[data-code="' + sid + '"]').length && !c.is(':checked')) {
          //$('[data-key="'+cv+'"]').find('.services .service'+sid+' .delete').click();
          $('[data-key="' + cv + '"]').find('.services .service[data-code="' + sid + '"] .delete').click();
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

      for (var i = 0; i < checked_arr.length; i++) {
        var uid;
        if (!price_arr.length) {
          price0 = price;
          price1 = price + ' <span class="gotech_ruble">a</span>';
          uid = sid;
        }
        else {
          price0 = price_arr[i];
          price1 = price_arr[i] + ' <span class="gotech_ruble">a</span>';
          uid = id_arr[i];
        }
        n = $('[data-key="' + checked_arr[i] + '"]').find('.services .service').length + 1;
        if (!$('[data-key="' + checked_arr[i] + '"]').find('.services .service' + sid).length)
          $('[data-key="' + checked_arr[i] + '"]')
            .find('.services').append('' +
            '<div class="service service' + sid + ' new_service" data-price="' + price0 + '" data-code="' + code + '" data-uid="' + uid + '" data-guest="' + checked_arr[i] + '" data-id="' + uid + '">' +
            '<span class="pn">' + (n) + '</span>. ' + name + '    |   <b>' + price1 + '</b> <a href="#" class="new_delete delete"></a>' +
            '<input type="hidden" name="service_price_' + uid + '" class="service_price" value="' + price0 + '" />' +
            '</div>');


        $('.added_services').append('' +
          '<div class="service service' + sid + ' new_service" data-price="' + price0 + '" data-code="' + code + '" data-uid="' + uid + '" data-guest="' + checked_arr[i] + '" data-id="' + uid + '">' +
          '<span class="pn">' + (n) + '</span>. ' + name + '    |   <b>' + price1 + '</b> <a href="#" class="new_delete delete"></a>' +
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

    $('.change_service_button').show();
    $('.change_service_button').css('display', 'inline-block');

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
        }
      );
    }
    else
      $('.service[data-code="' + code + '"]').each(function () {
        var p = $(this);
        p.addClass('for_delete').css('text-decoration', 'line-through');

        pp.find('.added_info').hide();
        pp.find('.added_info').prev().find('a').show();

        var id = p.data('id');
        $('.jewelery-popup[data-service_id="' + id + '"]').find('[type=checkbox]').prop('checked', false);
        $('.jewelery-popup[data-service_id="' + id + '"]').find('[type=checkbox]').trigger('change');

        $('.change_service_button').show();
        $('.change_service_button').css('display', 'inline-block');

        if (p.hasClass('new_service'))
          p.remove();

      });
    recount();

    return false;
  });


  $('.change_service_button a.change_service_b').click(function () {

    var $this = $(this);
    $this.addClass('add_loader');
    var reservationRows = [];
    var guest_group = "<?=$arResult["ExtGuestGroupCode"]?>";

    $('.booking_order_item').each(function () {
      var $parent = $(this);//$(elem).parents('.booking_order_item');
      var $elements = $parent.find('.guest');
      var rt_code = $parent.find('[name="room_type_code"]').val();
      var check_in_date = $parent.find('[name="check_in_date"]').val();
      var check_out_date = $parent.find('[name="check_out_date"]').val();
      var customer = $parent.find('[name="customer"]').val();
      var contract = $parent.find('[name="contract"]').val();

      var adults = $parent.find('[name="adults"]').val();
      if (!adults || isNaN(adults)) {
        adults = 1;
      } else {
        adults = parseInt(adults)
      }
      var children = $parent.find('[name="children"]').val();
      if (!children || isNaN(children)) {
        children = 0;
      } else {
        children = parseInt(children)
      }
      var visitors = adults + children;


      $elements.each(function () {

        var services = [];
        $(this).find('.new_service').each(function () {
          if (!$(this).hasClass('for_delete')) {
            var o = {};
            o.Service = $(this).data('code');
            o.Price = $(this).data('price');
            //o.ChargeDate = null;
            o.Quantity = 1;
            o.Remarks = $(this).data('guest') + ', ' + $(this).data('id');
            o.Currency = 643;
            o.Sum = $(this).data('price');

            if ($(this).find('[name="is_transfer"]').val() === '1') {
              o.OrderType = 'Transfer';
              o.Department = 'Transfer';
              o.OrderDate = $(this).data('tdate'); //Дата заказа (подачи) трансфера
              o.OrderTime = $(this).data('ttime'); //Дата заказа (подачи) трансфера
              o.Destination = $(this).data('tdestination'); //Направление
              o.TransferRemarks = $(this).data('tremarks'); //Номер рейса или поезда
              o.TransferType = $(this).data('code'); //Направление
              o.TransferChildseats = $(this).data('tchildseats'); //Число детских вресел
              o.PassengersNumber = '1'; //Число пассажиров
            }

            services.push(o);

          }
        });

        var data = {
          roomGUID: "<?=OnlineBookingSupport::GUID()?>",
          guest_group: guest_group,
          hotel: "<?=$arResult["HotelCode"]?>",
          room_rate: "<?=$arResult["RoomRateCode"]?>",
          room_type: rt_code,
          room_quota: $(this).find('input[name="room_quota"]').val(),
          check_in_date: check_in_date,
          check_out_date: check_out_date,
          output_code: "<?=$arResult["OutputCode"]?>",
          language: $('[name="language"]').val(),
          guid: $(this).find('input[name="guid"]').val(),
          acc_type_code: $(this).find('input[name="acc_type_code"]').val(),
          res_status_code: $(this).find('input[name="res_status_code"]').val(),
          annul_status_code: "<?=$arResult["ANNULATION_STATUS_CODE"]?>",
          is_annulation: $(this).find('input[name="is_annulation"]').val(),
          customer: customer,
          contract: contract,
          code: $(this).find('input[name="code"]').val(),
          surname: $(this).find('input[name="surname"]').val(),
          name: $(this).find('input[name="name"]').val(),
          secondName: $(this).find('input[name="secondName"]').val(),
          birthday: $(this).find('input[name="birthday"]').val(),
          payment_method: "<?=$arResult["PaymentMethod"]?>",
          vis: visitors,
          services: services
        }
        reservationRows.push(data);
      });

    });

    var ajax_data =
    {
      guest_group: guest_group,
      reservationRows: reservationRows,
      wsdl: "<?=$arResult["WSDL"]?>",
      hotel: "<?=$arResult["HotelCode"]?>",
      output_code: "<?=$arResult["OutputCode"]?>",
      language: $('[name="language"]').val(),
      login: "<?=$arResult["login"]?>",
      contact_email: "<?=$arResult["CONTACT_PERSON_EMAIL"]?>",
      contact_phone: "<?=$arResult["CONTACT_PERSON_PHONE"]?>",
      cache_enabled: "<?=COption::GetOptionString('gotech.hotelonline', 'SOAPCache', 0)?>",
    }
    console.log(ajax_data);
    $.ajax({
      type: "POST",
      url: "<?=($_SERVER["HTTPS"] == "on" ? "https://" : "http://")?><?=$_SERVER["SERVER_NAME"] . COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')?>do_change_group_reservations.php",
      dataType: "html",
      data: ajax_data,
      async: true,
      cache: false,
      success: function (html) {
        console.log("Success!");
        console.log(html);
        var objReturn = JSON.parse(html);
        var return_code = objReturn.return_code;
        switch (return_code) {
          case 0:
            $('input[name="is_change_order"]').val("N");
            $('input[name="is_change"]').val("N");
            $('form[name="search_reservation"]').submit();
            break;
          case -1:
            $this.removeClass('add_loader');
            $('#find_accommodation_link_wait').hide();
            $('#find_accommodation_link_wait #gotech_progress_icon').hide();
            $('#find_accommodation_link').hide();
            $('#save_changes_link_wait').hide();
            $('#save_changes_link_wait #gotech_progress_icon').hide();
            $('#save_changes_link').show();
            $('#gotech_my_content').fadeTo(1, 1);
            $('#gotech_my_content').css('pointer-events', 'auto');
            alert("<?=GetMessage("SAVE_CHANGES_ERROR")?>");
            break;
          default:
            $this.removeClass('add_loader');
            console.log("Ошибка при запросе на сервер!");
            break;
        }
        $('#gotech_my_content').fadeTo(1, 1);
        $('#gotech_my_content').css('pointer-events', 'auto');
      },
      error: function (html) {
        console.log("Error!");
        $this.removeClass('add_loader');
        $('#find_accommodation_link_wait').hide();
        $('#find_accommodation_link_wait #gotech_progress_icon').hide();
        $('#find_accommodation_link').hide();
        $('#save_changes_link_wait').hide();
        $('#save_changes_link_wait #gotech_progress_icon').hide();
        $('#save_changes_link').show();
        $('#gotech_my_content').fadeTo(1, 1);
        $('#gotech_my_content').css('pointer-events', 'auto');
      }
    });

    return false;
  });


  $('#cancel').magnificPopup({
    items: {
      src: '#gotech_annulation_dialog',
      type: 'inline'
    },
    fixedContentPos: true,
    fixedBgPos: true,
    overflowY: 'auto',
    closeBtnInside: true,
    preloader: false,
    midClick: true,
    removalDelay: 300,
    mainClass: 'my-mfp-zoom-in',
  });

  $('a#cancel').on('click', function (e) {
    $("a#cancel_yes").on("click", function (e) {
      e.preventDefault();
      $('input[name="cancel"]').val("Y");
      $('input[name="search"]').val("N");
      $('form[name="search_reservation"]').submit();
    });
    $("a#cancel_no").on("click", function (e) {
      e.preventDefault();
      //$('#gotech_annulation_dialog').dialog("close");
      $('.mfp-close').click();
    });
  });
  $('body').on('click', 'a#reservation', function () {
    $('form[name="search_reservation"]').submit();
    $('form').html('<div style="font-size:12px;"><?=GetMessage('LOAD')?> <img src="/bitrix/js/onlinebooking/new/icons/progress.gif" width="100" /></div>');
    $(this).hide();
  });

</script>


<script type="text/javascript">
  $(function () {
    setSelectricForAges();
    $('#gotech_search_window_guests').parents('.gotech_my_content_item_guests').prepend($('#gotech_search_window_guests'));

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

    setTimeout(function () {
      iframe_resize();
    }, 500);


  });
  $(document).ready(function () {

    setTimeout(function () {
      iframe_resize();
    }, 500);


    $('.gotech_my_content_item_guests_item_guest_birthday_input').mask('99.99.9999'); // Date mask
    /* = = = = ADULTS = = = = */
    var adIcCount = $('#gotech_search_window_guests_adults_spinner').children().length - 2;
    var adCount = 0;
    var child;
    if (adIcCount == 1) {
      hideButton('prev', 'adults');
      hideButton('next', 'adults');
    } else {
      for (var i = 0; i < adIcCount; i++) {
        child = $($('#gotech_search_window_guests_adults_spinner').children()[i + 1]);
        if (child.hasClass('gotech_search_window_guests_adults_spinner_icon_active')) {
          if (i == 1) {
            showButton('prev', 'adults');
          }
          if (i == adIcCount - 1) {
            hideButton('next', 'adults');
          }
          adCount++;
        }
      }
    }
    $('#gotech_search_window_guests_adults_count').html(adCount);
    $('input[name="adults"]').val(adCount);
    /* = = = = CHILDREN = = = = */
    var childIcCount = $('#gotech_search_window_guests_children_spinner').children().length - 2;
    var childCount = 0;
    if (childIcCount == 0) {
      hideButton('prev', 'children');
      hideButton('next', 'children');
      $('label[for="gotech_search_window_guests_ages_spinner"]').hide();
      for (var j = 0; j < 4; j++) {
        /* hide ages */
        $('input[name="shChildrenYear_' + j + '"]').val('false');
        $('#gotech_search_window_guests_ages_' + j).parent().parent().hide();
      }
    } else {
      $('label[for="gotech_search_window_guests_ages_spinner"]').hide();
      for (var i = 0; i < childIcCount; i++) {
        child = $($('#gotech_search_window_guests_children_spinner').children()[i + 1]);
        if (child.hasClass('gotech_search_window_guests_children_spinner_icon_active')) {
          if (i == 0) {
            showButton('prev', 'children');
          }
          if (i == childIcCount - 1) {
            hideButton('next', 'children');
          }
          /* show ages */
          $('input[name="shChildrenYear_' + i + '"]').val('true');
          $('label[for="gotech_search_window_guests_ages_spinner"]').show();
          $('#gotech_search_window_guests_ages_' + i).parent().parent().show();
          $('#gotech_search_window_guests_ages_' + i).parent().parent().css('display', '-moz-inline-stack');
          $('#gotech_search_window_guests_ages_' + i).parent().parent().css('display', 'inline-block');
          childCount++;
        }
      }
    }
    $('#gotech_search_window_guests_children_count').html(childCount);
    $('input[name="children"]').val(childCount);

  });

  //    $('.gotech_my_footer_payment_methods_customer_data label>input').on('input', function (e) {
  //        $('a#pay_link').removeClass('hide_button');
  //        $('.gotech_my_footer_payment_methods_customer_data label>input').each(function () {
  //            if ($(this).val().length == 0 && $(this).prop('name') != 'customer_phone') {
  //                $('a#pay_link').addClass('hide_button');
  //                return false;
  //            } else if ($(this).prop('name') == 'customer_email') {
  //                var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  //                if (!regex.test($(this).val())) {
  //                    $('a#pay_link').addClass('hide_button');
  //                    return false;
  //                }
  //            }
  //        });
  //    });

  $('input[name="payment_methods_radiobuttons"]').on('click', function (e) {
    var id = $('input[name="payment_methods_radiobuttons"]:checked').attr("id");
    if (id != 'undefined') {
      var parameters = id.split("-");
      var is_legal = parameters[4];
      if (is_legal == '1') {
        showCustomerData(this, false);
      } else {
        showCustomerData(this, true);
      }
    }

    setTimeout(function () {
      iframe_resize();
    }, 500);
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

  $('body').on('click', 'span.gotech_my_content_item_header_change_reservation', function () {
    var room_id = $(this).parent().find('[name="room_id"]').val();
    $('input[name="is_change_order"]').val("N");
    $('input[name="is_change"]').val("Y");
    $('input[name="is_change_room"]').val(room_id);
    $('form[name="search_reservation"]').submit();
  });
  $('body').on('click', '.change_bron', function () {
    var room_id = $(this).parents('.booking_order_item').find('[name="room_id"]').val();
    $('input[name="is_change_order"]').val("N");
    $('input[name="is_change"]').val("Y");
    $('input[name="is_change_room"]').val(room_id);
    $('form[name="search_reservation"]').submit();
  });

  $('body').on('click', '.change_bron_dates', function () {
    var room_id = $(this).parents('.booking_order_item').find('[name="room_id"]').val();
    $('input[name="is_change_order"]').val("Y");
    $('input[name="is_change"]').val("N");
    $('input[name="is_change_room"]').val(room_id);
    $('form[name="search_reservation"]').submit();
  });

  function doGetAvailableRoomTypes(elem) {
    //var $parent = $(elem).parents('.gotech_my_content_item');
    var $parent = $(elem).parents('.booking_order_item');
    var adults = $parent.find('[name="adults"]').val();
    var children = $parent.find('[name="children"]').val();
    var childrenAge = [];
    $('[name^="shChildrenYear"]').each(function (index) {
      if ($(this).val() == 'true') {
        childrenAge.push($('[name="childrenYear_' + index + '"]').val());
      }
    });
    var rt_code = $parent.find('[name="room_type_code"]').val();
    var currency_symbol = $parent.find('[name="currency_symbol"]').val();
    var check_in_date = $parent.find('[name="check_in_date"]').val();
    var check_out_date = $parent.find('[name="check_out_date"]').val();
    var ajax_data =
    {
      adults: adults,
      children: children,
      childrenAge: childrenAge,
      wsdl: "<?=$arResult["WSDL"]?>",
      hotel: "<?=$arResult["HotelCode"]?>",
      room_rate: "<?=$arResult["RoomRateCode"]?>",
      client_type: "",
      room_type: rt_code,
      room_quota: "<?=$arResult["ROOM_QUOTA"]?>",
      check_in_date: check_in_date,
      check_out_date: check_out_date,
      output_code: "<?=$arResult["OutputCode"]?>",
      language: $('[name="language"]').val(),
      email: "<?=$arResult["CONTACT_PERSON_EMAIL"]?>",
      phone: "<?=$arResult["CONTACT_PERSON_PHONE"]?>",
      login: "<?=$arResult["login"]?>",
      promo_code: "",
      cache_enabled: "<?=COption::GetOptionString('gotech.hotelonline', 'SOAPCache', 0)?>",
      currency_symbol: currency_symbol
    }
    console.log(ajax_data);
    $.ajax({
      type: "POST",
      url: "http://<?=$_SERVER["SERVER_NAME"] . COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')?>get_available_room_types.php",
      dataType: "html",
      data: ajax_data,
      async: true,
      cache: false,
      success: function (html) {
        console.log("Success!");
        console.log(html);
        var objReturn = JSON.parse(html);
        var return_code = objReturn.return_code;
        switch (return_code) {
          case 0:
            $('.gotech_my_content_item_footer_new_price>span').html(objReturn.amount);
            $('.gotech_my_content_item_footer_new_price').show();
            $('.gotech_my_content_item_footer').css('background-color', 'rgba(255, 255, 0, 0.27)');
            setTimeout(function () {
              $('.gotech_my_content_item_footer').css('background-color', 'rgb(242, 242, 242)')
            }, 2000);
            $('#find_accommodation_link_wait').hide();
            $('#find_accommodation_link_wait #gotech_progress_icon').hide();
            $('#find_accommodation_link').hide();
            $('#save_changes_link_wait').hide();
            $('#save_changes_link_wait #gotech_progress_icon').hide();
            $('#save_changes_link').show();
            //var $elements = $parent.find('.gotech_my_content_item_guests_item');
            var $elements = $parent.find('.guest');
            $elements.each(function (index) {
              $(this).find('[name="acc_type_code"]').val(objReturn.acc_types[index]);
            });
            console.log("Бронь успешно изменена!");
            break;
          case -1:
            $('#find_accommodation_link_wait').hide();
            $('#find_accommodation_link_wait #gotech_progress_icon').hide();
            $('#find_accommodation_link').show();
            $('#save_changes_link_wait').hide();
            $('#save_changes_link_wait #gotech_progress_icon').hide();
            $('#save_changes_link').hide();
            alert("<?=GetMessage("FIND_ACCOMMODATION_ERROR")?>");
            break;
          default:
            console.log("Ошибка при запросе на сервер!");
            break;
        }
        $('#gotech_my_content').fadeTo(1, 1);
        $('#gotech_my_content').css('pointer-events', 'auto');
      },
      error: function (html) {
        console.log("Error!");
        $('#find_accommodation_link_wait').hide();
        $('#find_accommodation_link_wait #gotech_progress_icon').hide();
        $('#find_accommodation_link').show();
        $('#save_changes_link_wait').hide();
        $('#save_changes_link_wait #gotech_progress_icon').hide();
        $('#save_changes_link').hide();
        $('#gotech_my_content').fadeTo(1, 1);
        $('#gotech_my_content').css('pointer-events', 'auto');
      }
    });
  }

  $('body').on('click', '.change_bron_button', function (e) {
    e.preventDefault();
    doChangeGroupReservations(this);
  });

  $('body').on('click', '.upgrade_room_button_execute', function (e) {
    e.preventDefault();
    doChangeGroupReservations(this);
  });

  function doChangeGroupReservations(elem) {
    //var $parent = $(elem).parents('.gotech_my_content_item');
    var $parent = $(elem).parents('.booking_order_item');

    var old_new_data = {};

    if ($(elem).hasClass('upgrade_room_button_execute')) {
      $('.upgrade_room_button_cancel').hide();
    }
    $(elem).after('<img src="/bitrix/js/onlinebooking/new/icons/progress.gif" width="100" />');
    $(elem).hide();

    //var $elements = $parent.find('.gotech_my_content_item_guests_item');
    var $elements = $parent.find('.guest');
    var reservationRows = [];
    var guest_group = "<?=$arResult["ExtGuestGroupCode"]?>";

    if ($(elem).hasClass('upgrade_room_button_execute')) {
      var rr_code = $parent.find('[name="upgrade_room_rate_code"]').val();
      var rq_code = $parent.find('[name="room_quota_code"]').val();
      var rt_code = $parent.find('[name="upgrade_room_type_code"]').val();
      var output_code = $parent.find('[name="output_code"]').val();
      var email = $parent.find('[name="contact_person_email"]').val();
      var phone = $parent.find('[name="contact_person_phone"]').val();
    } else {
      var rr_code = $parent.find('[name="embeded_room_rate_code"]').val();
      var rq_code = $parent.find('[name="embeded_room_quota_code"]').val();
      var rt_code = $parent.find('[name="room_type_code"]').val();
      var output_code = $parent.find('[name="embeded_output_code"]').val();
      var email = $parent.find('[name="embeded_contact_person_email"]').val();
      var phone = $parent.find('[name="embeded_contact_person_phone"]').val();
    }

    if ($parent.find('input[name="PeriodFrom_f"]').length && $parent.find('input[name="PeriodTo_f"]').length) {
      var PeriodFrom = $parent.find('input[name="PeriodFrom_f"]').val();
      var pfrom_split = PeriodFrom.split('.');
      var check_in_date = pfrom_split[2] + '-' + pfrom_split[1] + '-' + pfrom_split[0] + 'T' + $('input[name="hotel_time_from"]').val();

      var PeriodTo = $parent.find('input[name="PeriodTo_f"]').val();
      var pto_split = PeriodTo.split('.');
      var check_out_date = pto_split[2] + '-' + pto_split[1] + '-' + pto_split[0] + 'T' + $('input[name="hotel_time_to"]').val();
    } else {
      var check_in_date = $parent.find('input[name="check_in_date"]').val();
      var check_out_date = $parent.find('input[name="check_out_date"]').val();
    }

    var customer = $parent.find('[name="customer"]').val();
    var contract = $parent.find('[name="contract"]').val();

    // Смотрим сколько взрослых и детей мы выбрали для изменения
    var adults = 1;
    var children = 0;
    var visitors = 1;
    if ($parent.find('#gotech_search_window_guests [name="adults"]').length) {
      adults = $parent.find('#gotech_search_window_guests [name="adults"]').val();
      if (!adults || isNaN(adults)) {
        adults = 1;
      } else {
        adults = parseInt(adults)
      }
      children = $parent.find('#gotech_search_window_guests [name="children"]').val();
      if (!children || isNaN(children)) {
        children = 0;
      } else {
        children = parseInt(children)
      }
      visitors = adults + children;
    }

    // Считаем сколько сейчас взрослых + детей есть
    var have_adults = 0;
    var have_children = 0;

    var $haveAdultEls = [];
    var $haveChildEls = [];

    var $lastAdultEl = null;
    var $lastChildEl = null;

    var old_acc_codes = [];
    $elements.each(function () {
      if ($(this).find('input[name="isChild"]').val() == "Y") {
        have_children++;
        $lastChildEl = $(this);
        $haveChildEls.push($(this));
      } else {
        have_adults++;
        $lastAdultEl = $(this);
        $haveAdultEls.push($(this));
      }
      old_acc_codes.push($(this).find('input[name="acc_type_code"]').val());
    })
    var have_visitors = have_adults + have_children;

    if ($(elem).hasClass('upgrade_room_button_execute')) {
      adults = have_adults;
      children = have_children;
    }

    var services = [];

    $parent.find('.service').each(function () {
      if (!$(this).hasClass('for_delete')) {
        var o = {};
        o.Service = $(this).data('code');
        o.Price = $(this).data('price');
        o.Quantity = 1;
        o.Remarks = $(this).data('uid');
        o.Currency = 643;
        o.Sum = $(this).data('price');

        if ($(this).find('[name="is_transfer"]').val() === '1') {
          o.OrderType = 'Transfer';
          o.Department = 'Transfer';
          o.OrderDate = $(this).data('tdate'); //Дата заказа (подачи) трансфера
          o.OrderTime = $(this).data('ttime'); //Дата заказа (подачи) трансфера
          o.Destination = $(this).data('tdestination'); //Направление
          o.TransferRemarks = $(this).data('tremarks'); //Номер рейса или поезда
          o.TransferType = $(this).data('code'); //Направление
          o.TransferChildseats = $(this).data('tchildseats'); //Число детских вресел
          o.PassengersNumber = '1'; //Число пассажиров
        }
        services.push(o);
      }
    });


    function getData($el, is_anul, is_new, acc_type) {
      return {
        roomGUID: "<?=OnlineBookingSupport::GUID()?>",
        guest_group: guest_group,
        hotel: "<?=$arResult["HotelCode"]?>",
        room_rate: rr_code,
        room_type: rt_code,
        room_quota: rq_code,
        check_in_date: check_in_date,
        check_out_date: check_out_date,
        output_code: output_code,
        language: $('[name="language"]').val(),
        guid: !is_new ? $el.find('input[name="guid"]').val() : "",
        acc_type_code: acc_type,
        res_status_code: $el.find('input[name="res_status_code"]').val(),
        annul_status_code: "<?=$arResult["ANNULATION_STATUS_CODE"]?>",
        is_annulation: is_anul, //$(this).find('input[name="is_annulation"]').val(),
        customer: customer,
        contract: contract,
        code: !is_new ? $el.find('input[name="code"]').val() : "",
        surname: !is_new ? $el.find('input[name="surname"]').val() : "",
        name: !is_new ? $el.find('input[name="name"]').val() : "",
        secondName: !is_new ? $el.find('input[name="secondName"]').val() : "",
        birthday: !is_new ? $el.find('input[name="birthday"]').val() : "",

        doChangeDoc: (!is_new && $el.find('select[name="ClientIdentityDocumentType"]').length) ? "Y" : "N",
        docType: (!is_new && $el.find('select[name="ClientIdentityDocumentType"]').length) ? $el.find('select[name="ClientIdentityDocumentType"]').val() : "",
        docSeries: (!is_new && $el.find('input[name="ClientIdentityDocumentSeries"]').length) ? $el.find('input[name="ClientIdentityDocumentSeries"]').val() : "",
        docNumber: (!is_new && $el.find('input[name="ClientIdentityDocumentNumber"]').length) ? $el.find('input[name="ClientIdentityDocumentNumber"]').val() : "",
        docDate: (!is_new && $el.find('input[name="ClientIdentityDocumentIssueDate"]').length) ? $el.find('input[name="ClientIdentityDocumentIssueDate"]').val() : "",
        docUnitCode: (!is_new && $el.find('input[name="ClientIdentityDocumentUnitCode"]').length) ? $el.find('input[name="ClientIdentityDocumentUnitCode"]').val() : "",
        docIssuedBy: (!is_new && $el.find('input[name="ClientIdentityDocumentIssuedBy"]').length) ? $el.find('input[name="ClientIdentityDocumentIssuedBy"]').val() : "",

        doChangeAddress: (!is_new && $el.find('input[name="address"]').length) ? "Y" : "N",
        address: (!is_new && $el.find('input[name="address"]').length) ? $el.find('input[name="address"]').val() : "",

        payment_method: "<?=$arResult["PaymentMethod"]?>",
        vis: visitors
      }
    }

    var new_adult_accommodations = [];
    var new_child_accommodations = [];
    // If it change reservation get new accommodations
    var $acc_form = $(elem).parents('.accomodation_form');
    if (!$(elem).hasClass('upgrade_room_button_execute') && $acc_form.length) {
      var acc_types_count = $acc_form.find('[name^="Accommodation_Code_"]').length;
      for (var acc_i = 0; acc_i < acc_types_count; acc_i++) {
        if ($acc_form.find('[name^="Accommodation_Is_Child_' + acc_i + '"]').val() == '1') {
          new_child_accommodations.push({
            code: $acc_form.find('[name^="Accommodation_Code_' + acc_i + '"]').val(),
            age: $acc_form.find('[name^="Accommodation_Age_' + acc_i + '"]').val()
          })
        } else {
          new_adult_accommodations.push({
            code: $acc_form.find('[name^="Accommodation_Code_' + acc_i + '"]').val()
          })
        }
      }
    } else {
      var upgrade_acc_types_count = $parent.find('[name^="upgrade_acc_type_code_"]').length;
      for (var upg_acc_i = 0; upg_acc_i < upgrade_acc_types_count; upg_acc_i++) {
        var age_to = parseInt($parent.find('[name^="upgrade_acc_type_age_to_' + upg_acc_i + '"]').val());
        if (age_to > 0 && age_to < 18) {
          new_child_accommodations.push({
            code: $parent.find('[name="upgrade_acc_type_code_' + upg_acc_i + '"]').val(),
            age: $parent.find('[name^="upgrade_acc_type_age_' + upg_acc_i + '"]').val()
          })
        } else {
          new_adult_accommodations.push({
            code: $parent.find('[name="upgrade_acc_type_code_' + upg_acc_i + '"]').val()
          })
        }
      }
    }

    // Сначала разбираемся со взрослыми
    var ad_i = 0;
    var new_acc_codes = [];
    if (have_adults <= adults) {
      $haveAdultEls.forEach(function (el) {
        var acc_code = new_adult_accommodations.length > ad_i ? new_adult_accommodations[ad_i].code : $(el).find('input[name="acc_type_code"]').val();
        var data = getData($(el), 'N', false, acc_code)
        new_acc_codes.push(acc_code);
        reservationRows.push(data);
        ad_i++;
      });
      for (var i = 0; i < (adults - have_adults); i++) {
        var acc_code = new_adult_accommodations.length > ad_i ? new_adult_accommodations[ad_i].code : "";
        var data = getData($lastAdultEl, 'N', true, acc_code)
        if (acc_code) {
          new_acc_codes.push(acc_code);
        }
        reservationRows.push(data);
        ad_i++;
      }
    } else {
      $haveAdultEls.forEach(function (el) {
        var is_anul = ad_i >= adults ? 'Y' : 'N';
        var acc_code = new_adult_accommodations.length > ad_i ? new_adult_accommodations[ad_i].code : $(el).find('input[name="acc_type_code"]').val();
        var data = getData($(el), is_anul, false, acc_code)
        if (is_anul == 'N') {
          new_acc_codes.push(acc_code);
        }
        reservationRows.push(data);
        ad_i++;
      });
    }

    // Теперь переходим к детям
    var ch_i = 0;
    if (have_children <= children) {
      $haveChildEls.forEach(function (el) {
        var acc_code = new_child_accommodations.length > ch_i ? new_child_accommodations[ch_i].code : $(el).find('input[name="acc_type_code"]').val();
        var data = getData($(el), 'N', false, acc_code)
        new_acc_codes.push(acc_code);
        reservationRows.push(data);
        ch_i++;
      });
      for (var i = 0; i < (children - have_children); i++) {
        var acc_code = new_child_accommodations.length > ch_i ? new_child_accommodations[ch_i].code : "";
        var data = getData($lastChildEl, 'N', true, acc_code)
        if (acc_code) {
          new_acc_codes.push(acc_code);
        }
        reservationRows.push(data);
        ch_i++;
      }
    } else {
      $haveChildEls.forEach(function (el) {
        var is_anul = ch_i >= children ? 'Y' : 'N';
        var acc_code = new_child_accommodations.length > ch_i ? new_child_accommodations[ch_i].code : $(el).find('input[name="acc_type_code"]').val();
        var data = getData($(el), is_anul, false, acc_code)
        if (is_anul == 'N') {
          new_acc_codes.push(acc_code);
        }
        reservationRows.push(data);
        ch_i++;
      });
    }

    old_new_data['guest_group'] = guest_group;
    old_new_data['hotel'] = "<?=$arResult["HotelCode"]?>";
    old_new_data['rr_code'] = rr_code;
    old_new_data['rq_code'] = rq_code;
    old_new_data['old_rt_code'] = $parent.find('[name="room_type_code"]').val();
    old_new_data['new_rt_code'] = rt_code;
    old_new_data['old_check_in_date'] = $parent.find('input[name="check_in_date"]').val();
    old_new_data['old_check_out_date'] = $parent.find('input[name="check_out_date"]').val();
    old_new_data['new_check_in_date'] = check_in_date;
    old_new_data['new_check_out_date'] = check_out_date;
    old_new_data['old_adults'] = have_adults;
    old_new_data['old_children'] = have_children;
    old_new_data['new_adults'] = adults;
    old_new_data['new_children'] = children;
    old_new_data['old_total'] = $('#total').data('price');
    old_new_data['old_acc_codes'] = old_acc_codes.join(', ');
    old_new_data['new_acc_codes'] = new_acc_codes.join(', ');

    console.log('old_new_data: ', old_new_data);
    console.log(reservationRows);
    var ajax_data =
    {
      old_new_data: old_new_data,
      isUpgrade: $(elem).hasClass('upgrade_room_button_execute') ? "Y" : "N",
      guest_group: guest_group,
      reservationRows: reservationRows,
      wsdl: "<?=$arResult["WSDL"]?>",
      hotel: "<?=$arResult["HotelCode"]?>",
      output_code: output_code,
      language: $('[name="language"]').val(),
      login: "<?=$arResult["login"]?>",
      contact_email: email,
      contact_phone: phone,
      cache_enabled: "<?=COption::GetOptionString('gotech.hotelonline', 'SOAPCache', 0)?>",
      change_service: "Y",
      extra_services: services,
    }
    console.log(ajax_data);
    $.ajax({
      type: "POST",
      url: "<?=($_SERVER["HTTPS"] == "on" ? "https://" : "http://")?><?=$_SERVER["SERVER_NAME"] . COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')?>do_change_group_reservations.php",
      dataType: "html",
      data: ajax_data,
      async: true,
      cache: false,
      success: function (html) {
        console.log("Success!");
        console.log(html);
        var objReturn = JSON.parse(html);
        var return_code = objReturn.return_code;
        switch (return_code) {
          case 0:
            $('input[name="is_change"]').val("N");
            $('input[name="is_change_order"]').val("N");
            $('form[name="search_reservation"]').submit();
            break;
          case -1:
            $('#find_accommodation_link_wait').hide();
            $('#find_accommodation_link_wait #gotech_progress_icon').hide();
            $('#find_accommodation_link').hide();
            $('#save_changes_link_wait').hide();
            $('#save_changes_link_wait #gotech_progress_icon').hide();
            $('#save_changes_link').show();
            $('#gotech_my_content').fadeTo(1, 1);
            $('#gotech_my_content').css('pointer-events', 'auto');
            alert("<?=GetMessage("SAVE_CHANGES_ERROR")?>");
            break;
          default:
            console.log("Ошибка при запросе на сервер!");
            break;
        }
        $('#gotech_my_content').fadeTo(1, 1);
        $('#gotech_my_content').css('pointer-events', 'auto');
      },
      error: function (html) {
        console.log("Error!");
        $('#find_accommodation_link_wait').hide();
        $('#find_accommodation_link_wait #gotech_progress_icon').hide();
        $('#find_accommodation_link').hide();
        $('#save_changes_link_wait').hide();
        $('#save_changes_link_wait #gotech_progress_icon').hide();
        $('#save_changes_link').show();
        $('#gotech_my_content').fadeTo(1, 1);
        $('#gotech_my_content').css('pointer-events', 'auto');
      }
    });
  }

  $('body').on('change', '[name="ClientIdentityDocumentType"]', function() {
      $(this).parents('.guest').find('.gotech_guests_information_item_content_guest_document_photo').hide();
      var doc_type = $(this).val();
      $(this).parents('.guest').find('.gotech_guests_information_item_content_guest_document_photo.document_photo_block_' + doc_type).show();
  })

  function doWriteExternalClient(elem) {
      var $parent = $(elem).parents('.booking_order_item');
      var $elements = $parent.find('.guest');
      var guest_group = "<?=$arResult["GuestGroup"]?>";

      var output_code = $parent.find('[name="output_code"]').val();
      var email = $parent.find('[name="contact_person_email"]').val();
      var phone = $parent.find('[name="contact_person_phone"]').val();

      function getData($el, ind) {
          var pictures = [];
          var doc_type = $el.find('[name="ClientIdentityDocumentType"]').val();
          var $photo_blocks_els = $el.find('.document_photo_block_' + doc_type);
          $photo_blocks_els.each(function() {
              if ($(this).find('input[name^="mimetype"]').length && $(this).find('input[name^="mimetype"]').val()) {
                  pictures.push({
                      type: $(this).find('input[name^="mimetype"]').val(),
                      content: $(this).find('input[name^="base64"]').val()
                  })
              }
          })
          return {
              wsdl: "<?=$arResult["WSDL"]?>",
              cache_enabled: "<?=COption::GetOptionString('gotech.hotelonline', 'SOAPCache', 0)?>",
              client: {
                  guest_group: guest_group,
                  hotel: "<?=$arResult["HotelCode"]?>",
                  output_code: output_code,
                  guid: $el.find('input[name="guid"]').val(),
                  code: $el.find('input[name="code"]').val(),
                  surname: $el.find('input[name="surname"]').val(),
                  name: $el.find('input[name="name"]').val(),
                  secondName: $el.find('input[name="secondName"]').val(),
                  birthday: $el.find('input[name="birthday"]').val(),
                  phone: !ind ? phone : "",
                  email: !ind ? email : "",

                  docType: ($el.find('select[name="ClientIdentityDocumentType"]').length) ? $el.find('select[name="ClientIdentityDocumentType"]').val() : "",
                  docSeries: ($el.find('input[name="ClientIdentityDocumentSeries"]').length) ? $el.find('input[name="ClientIdentityDocumentSeries"]').val() : "",
                  docNumber: ($el.find('input[name="ClientIdentityDocumentNumber"]').length) ? $el.find('input[name="ClientIdentityDocumentNumber"]').val() : "",
                  docDate: ($el.find('input[name="ClientIdentityDocumentIssueDate"]').length) ? $el.find('input[name="ClientIdentityDocumentIssueDate"]').val() : "",
                  docUnitCode: ($el.find('input[name="ClientIdentityDocumentUnitCode"]').length) ? $el.find('input[name="ClientIdentityDocumentUnitCode"]').val() : "",
                  docIssuedBy: ($el.find('input[name="ClientIdentityDocumentIssuedBy"]').length) ? $el.find('input[name="ClientIdentityDocumentIssuedBy"]').val() : "",

                  pictures: pictures,

                  address: ($el.find('input[name="address"]').length) ? $el.find('input[name="address"]').val() : "",
              }
          }
      }

      var clients = [];
      var ind = 0;
      $elements.each(function() {
          clients.push(getData($(this), ind));
          ind++;
      })
      console.log(clients);
      clients.forEach(function(client) {
          $.ajax({
              type: "POST",
              url: "<?=($_SERVER["HTTPS"] == "on" ? "https://" : "http://")?><?=$_SERVER["SERVER_NAME"] . COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')?>do_write_external_client.php",
              dataType: "html",
              data: client,
              async: true,
              cache: false,
              success: function (html) {
                  console.log("Success!");
                  console.log(html);
                  var objReturn = JSON.parse(html);
                  var return_code = objReturn.return_code;
                  switch (return_code) {
                      case 0:
                          $('input[name="is_change"]').val("N");
                          $('input[name="is_change_order"]').val("N");
                          $('form[name="search_reservation"]').submit();
                          break;
                      case -1:
                          $('#find_accommodation_link_wait').hide();
                          $('#find_accommodation_link_wait #gotech_progress_icon').hide();
                          $('#find_accommodation_link').hide();
                          $('#save_changes_link_wait').hide();
                          $('#save_changes_link_wait #gotech_progress_icon').hide();
                          $('#save_changes_link').show();
                          $('#gotech_my_content').fadeTo(1, 1);
                          $('#gotech_my_content').css('pointer-events', 'auto');
                          alert("<?=GetMessage("SAVE_CHANGES_ERROR")?>");
                          break;
                      default:
                          console.log("Ошибка при запросе на сервер!");
                          break;
                  }
                  $('#gotech_my_content').fadeTo(1, 1);
                  $('#gotech_my_content').css('pointer-events', 'auto');
              },
              error: function (html) {
                  console.log("Error!");
                  $('#find_accommodation_link_wait').hide();
                  $('#find_accommodation_link_wait #gotech_progress_icon').hide();
                  $('#find_accommodation_link').hide();
                  $('#save_changes_link_wait').hide();
                  $('#save_changes_link_wait #gotech_progress_icon').hide();
                  $('#save_changes_link').show();
                  $('#gotech_my_content').fadeTo(1, 1);
                  $('#gotech_my_content').css('pointer-events', 'auto');
              }
          });
      })
  }

  scrollParentTop();
  parent.postMessage('hideBasket', '*');

  $('.payments_mobile [type=radio]').change(function () {
    $('.payments_mobile .payment').removeClass('active');
    $('.payments_mobile [type=radio]:checked').parents('label').addClass('active');
  });

  $('[name="bonuses_payment_sms_code"]').mask("#");
  $("[name='bonuses_payment_sum']").keyup(function () {
    var $this = $(this);
    $this.val($this.val().replace(/[^\d.]/g, ''));
    var sum = parseFloat($this.val());
    var max_sum = parseFloat($this.data('max'));
    var id = $('input[name="payment_methods_radiobuttons"]:checked').attr("id");
    if (id != 'undefined') {
      var parameters = id.split("-");
      var out_sum = parseInt(parameters[1]) / 100;
      if (sum > max_sum) {
        $this.val(max_sum);
        sum = max_sum;
      }
      $this.parent().parent().find('.need_to_pay_price').html(number_format(out_sum - sum, 2, ',', ' '));
    }
  });

  function common_booking(t) {
    t.after('<img src="/bitrix/js/onlinebooking/new/icons/snake-loader.gif" id="preloader1" />');
    t.hide();
    setTimeout(function () {
      $('body').prepend('<div id="darkbox"><img src="/bitrix/js/onlinebooking/new/icons/progress.gif" /></div>');
      if (parent.postMessage) {
        parent.postMessage('830', "*");
      }
    }, 200);

    var bonuses_sum = t.parent().find('[name="bonuses_payment_sum"]').val();

    var card = "";
    if (bonuses_sum && $.isNumeric(bonuses_sum)) {
      var phone = "<?=$_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientPhone?>";
      card = "<?if ($_SESSION["AUTH_CLIENT_DATA"]->CardUUID && !empty($_SESSION["AUTH_CLIENT_DATA"]->CardUUID)): ?><?= $_SESSION["AUTH_CLIENT_DATA"]->CardUUID ?><? endif;?>"
    }

    var old_link = $('a#pay_link').attr('href');
    var id = $('input[name="payment_methods_radiobuttons"]:checked').attr("id");
    if (id != 'undefined') {
      var parameters = id.split("-");
      var out_sum = parseInt(parameters[1]) / 100;
      // var bonuses_trans_id = "";
      if (bonuses_sum && $.isNumeric(bonuses_sum)) {
        out_sum = out_sum - parseFloat(bonuses_sum);
        // bonuses_trans_id = t.parent().find('[name="bonuses_payment_trans_id"]').val();
      }
      if (out_sum > 0) {
        var new_link = old_link + "&pay_sys=" + parameters[0] + "&pay_method_id=" + parameters[5] + "&out_summ=" + out_sum + "&bonuses_sum=" + bonuses_sum + "&bonuses_card=" + card;
        <?if($isLegal):?>
        var wrapper = $('.gotech_guests_information_footer_payment_methods_customer_data');
        new_link += "&customer_description=" + wrapper.find('input[name="customer_description"]').val() + "&customer_address=" + wrapper.find('input[name="customer_address"]').val() + "&customer_email=" + wrapper.find('input[name="customer_email"]').val() + "&customer_phone=" + wrapper.find('input[name="customer_phone"]').val() + "&customer_kpp=" + wrapper.find('input[name="customer_kpp"]').val() + "&customer_tin=" + wrapper.find('input[name="customer_tin"]').val();
        wrapper.hide();
        <?endif;?>
        <?if (SID !== null && SID):?>
        new_link = new_link + "&<?=SID?>";
        <?endif;?>
        $('a#pay_link').attr('href', new_link);
        window.location.replace(new_link);
      } else {
        if (bonuses_sum > 0) {
          var ajax_data = {
            card: card ? card : phone,
            sum: bonuses_sum,
            http_address: $("[name='bonuses_http_address']").val(),
            hotel_token: $("[name='bonuses_hotel_token']").val()
          };
          var sid = '';
          if ($('[name="SID"]').length && $('[name="SID"]').val()) {
            sid = '?' + $('[name="SID"]').val();
          }
          $.post('/bitrix/components/onlinebooking/reservation.header/start_bonuses_payment.php' + sid, ajax_data, function (data) {
            data = JSON.parse(data);
            if (data["error"]) {
              t.parent().find('.gotech_error_text.bonuses_message_text').show();
            } else {
              t.parent().find('[name="bonuses_payment_trans_id"]').val(data["trans_id"]);
              if (data['success']) {
                if (data['code_is_sent']) {
                  // Hide current stage of payment
                  t.hide();
                  t.parent().find('.bonuses_sum_block').hide();

                  // Show next stage
                  t.parent().find('.bonuses_sms_code_block').show();
                  t.parent().find('.sms_ok').show();
                } else {
                  finish_bonuses_payment(t, bonuses_sum);
                }
              }
            }
          });
        } else {
          window.location.reload();
        }
      }
    }
  }

  $('.prebookapply').click(function () {

    var t = $(this);

    //var bonuses_sum = t.parent().find('[name="bonuses_payment_sum"]').val();
    //
    //var card = "";
    //if (bonuses_sum && $.isNumeric(bonuses_sum)) {
    //  card = "<?//if ($_SESSION["AUTH_CLIENT_DATA"]->CardUUID && !empty($_SESSION["AUTH_CLIENT_DATA"]->CardUUID)): ?><!----><?//= $_SESSION["AUTH_CLIENT_DATA"]->CardUUID ?><!----><?// endif;?>//"
    //}

    common_booking(t);
    //if (bonuses_sum && $.isNumeric(bonuses_sum)) {
    //  var phone = "<?//=$_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientPhone?>//";
    //  var card = "<?//if ($_SESSION["AUTH_CLIENT_DATA"]->CardUUID && !empty($_SESSION["AUTH_CLIENT_DATA"]->CardUUID)):?>//<!----><?//=$_SESSION["AUTH_CLIENT_DATA"]->CardUUID?>//<!----><?//endif;?>//"
    //
    //  var ajax_data = {
    //    card: card ? card : phone,
    //    sum: bonuses_sum,
    //    http_address: $("[name='bonuses_http_address']").val(),
    //    hotel_token: $("[name='bonuses_hotel_token']").val()
    //  };
    //  var sid = '';
    //  if ($('[name="SID"]').length && $('[name="SID"]').val()) {
    //    sid = '?' + $('[name="SID"]').val();
    //  }
    //  $.post('/bitrix/components/onlinebooking/reservation.header/start_bonuses_payment.php' + sid, ajax_data, function (data) {
    //    data = JSON.parse(data);
    //    console.log(data);
    //    if (data["error"]) {
    //      t.parent().find('.gotech_error_text.bonuses_message_text').show();
    //    } else {
    //      t.parent().find('[name="bonuses_payment_trans_id"]').val(data["trans_id"]);
    //      if (data['success']) {
    //        if (data['code_is_sent']) {
    //          // Hide current stage of payment
    //          t.hide();
    //          t.parent().find('.bonuses_sum_block').hide();
    //
    //          // Show next stage
    //          t.parent().find('.bonuses_sms_code_block').show();
    //          t.parent().find('.sms_ok').show();
    //        } else {
    //          finish_bonuses_payment(t, bonuses_sum);
    //        }
    //      }
    //    }
    //  });
    //} else {
    //  common_booking(t, 0);
    //}


    return false;
  });

  function finish_bonuses_payment(t, bonuses_sum) {
    var phone = "<?=$_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientPhone?>";
    var card = "<?if ($_SESSION["AUTH_CLIENT_DATA"]->CardUUID && !empty($_SESSION["AUTH_CLIENT_DATA"]->CardUUID)):?><?=$_SESSION["AUTH_CLIENT_DATA"]->CardUUID?><?endif;?>"

    var id = $('input[name="payment_methods_radiobuttons"]:checked').attr("id");
    var out_sum = 0;
    if (id != 'undefined') {
      var parameters = id.split("-");
      out_sum = parseInt(parameters[1]) / 100;
    }

    var ajax_data = {
      trans_id: t.parent().find('[name="bonuses_payment_trans_id"]').val(),
      auth_code: t.parent().find('[name="bonuses_payment_sms_code"]').val(),
      http_address: $("[name='bonuses_http_address']").val(),
      hotel_token: $("[name='bonuses_hotel_token']").val(),
      currency: "<?=$arResult["CurrencyCode"]?>",
      sum: bonuses_sum,
      full_sum: out_sum,
      card: card ? card : phone,
      uuid: "<?=$arResult["UUID"]?>",
      group: "<?=$arResult["GuestGroup"]?>",
      hotel: "<?=$arResult["HotelCode"]?>"
    };
    var sid = '';
    if ($('[name="SID"]').length && $('[name="SID"]').val()) {
      sid = '?' + $('[name="SID"]').val();
    }
    $.post('/bitrix/components/onlinebooking/reservation.header/finish_bonuses_payment.php' + sid, ajax_data, function (data) {
      data = JSON.parse(data);
      console.log(data);

      if (t.parent().find('[name="bonuses_payment_sms_code"]').val()) {
        // Hide current stage of payment
        t.hide();
        t.parent().find('.bonuses_sms_code_block').hide();
      }

      // Show next stage
      if (data["error"]) {
        t.parent().find('.gotech_error_text.bonuses_message_text').show();
      } else {
        // common_booking(t, bonuses_sum);
        t.parent().find('.gotech_success_text.bonuses_message_text').show();
        $('#darkbox').hide();
        window.location.reload();
      }
    });
  }

  $('.sms_ok').click(function (e) {
    e.preventDefault();

    var t = $(this);

    if (t.parent().find('[name="bonuses_payment_sms_code"]').val()) {

      var bonuses_sum = t.parent().find('[name="bonuses_payment_sum"]').val();

      if (bonuses_sum && $.isNumeric(bonuses_sum)) {
        finish_bonuses_payment(t, bonuses_sum);
      } else {
        t.parent().find('.gotech_error_text.bonuses_message_text').show();
      }
    }
  });

</script>
