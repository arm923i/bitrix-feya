<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? __IncludeLang($_SERVER["DOCUMENT_ROOT"] . $this->__folder . "/lang/" . OnlineBookingSupport::getLanguage() . "/template.php"); ?>
<? $path = COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER');
$gotech_lang = $_REQUEST['lang'];
$pathwl = $path . "?lang=$gotech_lang";
if ($arResult['HOTEL_ID']) $pathwl .= '&hotel=' . $arResult['HOTEL_ID'];
if (SID !== null && SID) {
  $pathwl = $pathwl . "&" . SID;

}
?>
<!-- < Fix for autocomplete > -->
<input type="text" style="display:none">
<input type="password" style="display:none">
<!-- </ Fix for autocomplete > -->

<script>
  var pathwl = "<?=$pathwl?>";
  function getMonthNameByNumber(number) {
    switch (number) {
      case 0:
        return '<?=GetMessage('january')?>';
        break;
      case 1:
        return '<?=GetMessage('february')?>';
        break;
      case 2:
        return '<?=GetMessage('march')?>';
        break;
      case 3:
        return '<?=GetMessage('april')?>';
        break;
      case 4:
        return '<?=GetMessage('may')?>';
        break;
      case 5:
        return '<?=GetMessage('june')?>';
        break;
      case 6:
        return '<?=GetMessage('july')?>';
        break;
      case 7:
        return '<?=GetMessage('august')?>';
        break;
      case 8:
        return '<?=GetMessage('september')?>';
        break;
      case 9:
        return '<?=GetMessage('october')?>';
        break;
      case 10:
        return '<?=GetMessage('november')?>';
        break;
      case 11:
        return '<?=GetMessage('december')?>';
        break;
      default:
        return "";
        break;
    }
  }
  function getNightsDescription(nights) {
    if (nights == 1) {
      return nights;//+' <?=GetMessage('1NIGHT')?>';
    } else if (nights < 5) {
      return nights;//+' <?=GetMessage('2NIGHTS')?>';
    } else if (nights < 21) {
      return nights;//+' <?=GetMessage('5NIGHTS')?>';
    } else if (nights % 10 == 1) {
      return nights;//+' <?=GetMessage('1NIGHT')?>';
    } else if (nights % 10 < 5) {
      return nights;//+' <?=GetMessage('2NIGHTS')?>';
    } else {
      return nights;//+' <?=GetMessage('5NIGHTS')?>';
    }
  }
</script>

<div id="gotech_header">
  <? if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)): ?>
    <?
    $client_name = ($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientFirstName || $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientSecondName) ? $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientFirstName . " " . $_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer->ClientSecondName : $_SESSION["AUTH_CLIENT_DATA"]->CustomerName;
    ?>
    <b
      class="user_info_label"><?= $client_name ?><? if (isset($_SESSION["BONUS_AMOUNT"])): ?> (<?= GetMessage("BONUSES") ?><span class="header_bonuses_amount"><?= $_SESSION["BONUS_AMOUNT"] ?></span>)<? endif; ?></b>
  <? endif; ?>
  <input type="hidden" name="is_agent" <? if ($arResult["USER_OFFICE"] == 1): ?>value="Y"
         <? else: ?>value="N"<? endif; ?>>
  <input type="hidden" name="language" value="<?= OnlineBookingSupport::getLanguage() ?>">
  <input type="hidden" name="path_to_folder"
         value="<?= COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER') ?>">
  <input type="hidden" name="bonuses_http_address" value="<?= $arResult["BONUS_SYSTEM_WEB_ADDRESS"] ?>">
  <input type="hidden" name="bonuses_hotel_token" value="<?= $arResult["BONUS_SYSTEM_HOTEL_TOKEN"] ?>">
  <input type="hidden" name="currency_name" value="<?= $arResult["CURRENCY_NAME"] ?>">
  <input type="hidden" name="hotel_time_from" value="<?= $arResult["TIME_FROM"] ?>">
  <input type="hidden" name="hotel_time_to" value="<?= $arResult["TIME_TO"] ?>">

  <div class="buttons_wrap">
    <? if (isset($_REQUEST["uuid"]) && !empty($_REQUEST["uuid"])): ?>

    <? else: ?>
      <? if (($_REQUEST['reservation'] && $_REQUEST['data']) || $_REQUEST['booking'] == 'yes' || $_REQUEST['uuid']): ?>
        <svg width="10" height="12" viewBox="0 0 400 666">
          <g id="svgg">
            <path id="path0"
                  d="M283.000 8.428 L 283.000 16.856 268.047 17.178 C 249.431 17.579,250.710 16.312,250.182 34.877 L 249.759 49.759 234.877 50.182 C 216.312 50.710,217.579 49.431,217.178 68.047 L 216.856 83.000 209.678 83.080 C 199.198 83.196,186.272 84.395,185.323 85.340 C 184.870 85.791,184.275 92.986,184.000 101.330 L 183.500 116.500 168.297 117.000 C 149.402 117.621,150.709 116.329,150.181 134.908 L 149.758 149.821 134.927 150.161 C 116.323 150.586,117.581 149.318,117.178 168.047 L 116.856 183.000 106.178 183.060 C 83.758 183.185,84.621 182.473,84.000 201.330 L 83.500 216.500 68.296 217.000 C 49.401 217.621,50.709 216.326,50.181 234.927 L 49.756 249.860 34.925 250.180 C 16.322 250.581,17.581 249.313,17.178 268.047 L 16.856 283.000 8.428 283.000 L 0.000 283.000 0.000 333.000 L 0.000 383.000 8.428 383.000 L 16.856 383.000 17.178 397.953 C 17.581 416.687,16.322 415.419,34.925 415.820 L 49.756 416.140 50.181 431.073 C 50.709 449.674,49.401 448.379,68.296 449.000 L 83.500 449.500 84.000 464.670 C 84.621 483.527,83.758 482.815,106.178 482.940 L 116.856 483.000 117.178 497.953 C 117.581 516.682,116.323 515.414,134.927 515.839 L 149.758 516.179 150.181 531.092 C 150.709 549.671,149.402 548.379,168.297 549.000 L 183.500 549.500 184.000 564.670 C 184.275 573.014,184.870 580.209,185.323 580.660 C 186.272 581.605,199.198 582.804,209.678 582.920 L 216.856 583.000 217.178 597.953 C 217.579 616.569,216.312 615.290,234.877 615.818 L 249.759 616.241 250.182 631.123 C 250.710 649.688,249.431 648.421,268.047 648.822 L 283.000 649.144 283.000 657.572 L 283.000 666.000 333.000 666.000 L 383.000 666.000 383.000 657.500 L 383.000 649.000 391.500 649.000 L 400.000 649.000 400.000 599.500 L 400.000 550.000 391.500 550.000 L 383.000 550.000 383.000 537.125 C 383.000 515.648,384.195 516.985,364.358 516.253 L 350.215 515.732 349.767 501.616 C 349.143 481.934,350.304 483.000,329.482 483.000 C 314.858 483.000,316.005 484.389,315.985 466.668 C 315.965 449.366,317.228 450.565,298.609 450.181 L 283.142 449.861 282.821 434.964 C 282.414 416.071,283.580 417.276,264.857 416.395 L 250.215 415.707 249.844 401.376 C 249.361 382.702,250.312 383.659,231.735 383.156 C 214.747 382.696,216.035 384.034,216.015 366.807 C 215.996 349.238,216.845 350.000,197.277 350.000 L 183.000 350.000 183.000 333.000 L 183.000 316.000 197.277 316.000 C 216.845 316.000,215.996 316.762,216.015 299.193 C 216.035 281.966,214.747 283.304,231.735 282.844 C 250.312 282.341,249.361 283.298,249.844 264.624 L 250.215 250.293 264.857 249.605 C 283.580 248.724,282.414 249.929,282.821 231.036 L 283.142 216.139 298.609 215.819 C 317.228 215.435,315.965 216.634,315.985 199.332 C 316.005 181.611,314.858 183.000,329.482 183.000 C 350.304 183.000,349.143 184.066,349.767 164.384 L 350.215 150.268 364.358 149.747 C 384.195 149.015,383.000 150.352,383.000 128.875 L 383.000 116.000 391.500 116.000 L 400.000 116.000 400.000 66.500 L 400.000 17.000 391.500 17.000 L 383.000 17.000 383.000 8.500 L 383.000 0.000 333.000 0.000 L 283.000 0.000 283.000 8.428 "
                  stroke="none" fill="#000000" fill-rule="evenodd"/>
          </g>
        </svg>
        <a href="" class="new_search" onclick="new_search_click(event, '<?= $pathwl ?>')">
          <?= GetMessage('NEW_SEARCH'); ?>
        </a>
      <? endif; ?>
      <!-- Выбрать другой номер Новый заказ-->
      <? if ($arResult["USER_OFFICE"] != 1): ?>
        <? /*if($arResult["USER_OFFICE"] == 0):?>
            <span id="gotech_header_auth_login" class="gotech_header_text_disabled"><span id="gotech_header_auth_icon"></span><span><?=$USER->GetLogin();?></span></span>
          <?endif;*/ ?>
        <? if ($_SESSION['sn'] && $_SESSION['sn_id']): ?>
          <span id="gotech_header_auth_login" class="gotech_header_text_disabled">
              <span
                id="gotech_header_auth_icon"></span><span><? //=GetMessage("AUTHED")?><?= $_SESSION['sn_last_name']; ?> <?= $_SESSION['sn_name']; ?></span>
              (<a href="<?= $APPLICATION->GetCurPageParam('sn_logout=Y') ?>"
                  onclick="location.href='<?= $APPLICATION->GetCurPageParam('sn_logout=Y') ?>';"
                  style="color:#010308"><?= GetMessage("LOGOUT") ?></a>)
            </span>
        <? endif; ?>
        <? if ($APPLICATION->GetCurPage(false) != $path . "my.php" || 1): ?>
          <input type="hidden" name="gotech_header_my_link"
                 value="<?= $_REQUEST["hotel"] ? $path . "my.php?lang=$gotech_lang&hotel=" . $_REQUEST["hotel"] . "&" . SID : $path . "my.php?lang=$gotech_lang&" . SID ?>">
          <span id="gotech_header_my" class="gotech_header_text_ gotech_header_auth_button">
              <span class="gotech_header_text_underline"><?= GetMessage("MY_RESERVATION") ?></span>
            </span>
        <? endif; ?>
        <? if ($arResult["USER_OFFICE"] == 2 && !$USER->IsAuthorized()): ?>
          <? if (isset($_SESSION["AUTH_CLIENT_DATA"]) && isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)): ?>
            <input type="hidden" name="gotech_header_auth_out_link"
                   value="<?= $APPLICATION->GetCurPageParam("logout=yes") ?>">
            <span id="gotech_header_auth_out" class="gotech_button_gray gotech_header_auth_button">
                    <?= GetMessage("LOGOUT") ?>
                  </span>
          <? else: ?>
            <? if ($arResult["USE_OFFICE_MODULE"]): ?>
              <span id="gotech_header_auth_for_agent" class="gotech_header_text_ gotech_header_auth_button"
                    style="margin-right: 12px;">
              <span class="gotech_header_text_underline"><?= GetMessage("AGENT_LOG_IN") ?></span>
            </span>
            <? endif; ?>
          <? endif; ?>
        <? elseif ($USER->IsAuthorized()): ?>
          <input type="hidden" name="gotech_header_auth_out_link"
                 value="<?= $APPLICATION->GetCurPageParam("logout=yes") ?>">
          <span id="gotech_header_auth_out" class="gotech_button_gray gotech_header_auth_button">
              <?= GetMessage("LOGOUT") ?>
            </span>
        <? endif; ?>

      <? elseif ($arResult["USER_OFFICE"] == 1): ?>
        <? $officepath = COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER'); ?>
        <? if ($APPLICATION->GetCurPage(false) != $officepath . "history.php"): ?>
          <input type="hidden" name="gotech_header_history_link" value="<?= $officepath . "history.php" ?>">
          <span id="gotech_header_history" class="gotech_header_text"><span id="gotech_header_my_icon"></span><span
              class="gotech_header_text_underline"><?= GetMessage("MY_RESERVATIONS") ?></span></span>
        <? endif; ?>
        <? if ($APPLICATION->GetCurPage(false) != $officepath . "mutual_settlement.php" && 0): ?>
          <input type="hidden" name="gotech_header_settlements_link" value="<?= $officepath . "mutual_settlement.php" ?>">
          <span id="gotech_header_settlements" class="gotech_header_text"><span
              id="gotech_header_settlements_icon"></span><span
              class="gotech_header_text_underline"><?= GetMessage("SETTLEMENTS") ?></span></span>
        <? endif; ?>
        <span id="gotech_header_auth_login" class="gotech_header_text_disabled"><span id="gotech_header_auth_icon"></span><span><?= GetMessage("AGENT") ?><?= $USER->GetLogin(); ?></span></span>
        <input type="hidden" name="gotech_header_auth_out_link"
               value="<?= $APPLICATION->GetCurPageParam("logout=yes") ?>">
        <span id="gotech_header_auth_out" class="gotech_header_text"><span id="gotech_header_auth_icon"></span><span
            class="gotech_header_text_underline"><?= GetMessage("LOGOUT") ?></span></span>
        <? if ($APPLICATION->GetCurPage(false) == $officepath . "mutual_settlement.php" || $APPLICATION->GetCurPage(false) == $officepath . "history.php"): ?>
          <input type="hidden" name="gotech_header_new_link" value="<?= $pathwl ?>">
          <span id="gotech_header_new" class="gotech_header_text"><span id="gotech_header_another_room_icon"></span><span
              class="gotech_header_text_underline"></span></span>
        <? endif; ?>
      <? endif; ?>
      <? if (($APPLICATION->GetCurPage(false) == $path && isset($_REQUEST["booking"]) && $_REQUEST["booking"] == "yes" && !empty($_SESSION["NUMBERS_BOOKING"])) || ($APPLICATION->GetCurPage(false) == $path . "my.php")): ?>
        <input type="hidden" name="gotech_header_new_link" value="<?= $pathwl ?>">
  <!--      <span id="gotech_header_new" class="gotech_header_text"><span id="gotech_header_another_room_icon"></span><span-->
  <!--          class="gotech_header_text_underline"></span></span>-->
      <? elseif ($APPLICATION->GetCurPage(false) != $path || isset($_REQUEST["reservation"]) && $_REQUEST["reservation"] == "yes"): ?>
        <? /*
        <input type="hidden" name="gotech_header_new_link" value="<?=$pathwl?>">
        <span id="gotech_header_new" class="gotech_header_text" style="float: right;"><span id="gotech_header_new_icon"></span><span class="gotech_header_text_underline"><?=GetMessage("RESERVATION")?></span></span>
      */ ?>
      <? endif; ?>
    <? endif; ?>

    <? if (!COption::GetOptionString('gotech.hotelonline', 'LANG') || 1): ?>
      <? if (substr($APPLICATION->GetCurPageParam("", array("language", 'lang')), -3) == 'php' || substr($APPLICATION->GetCurPageParam("", array("language", 'lang')), -1) == '/') {
        $link = $APPLICATION->GetCurPageParam("", array("language", 'lang')) . "?lang=";
      } else {
        $link = $APPLICATION->GetCurPageParam("", array("language", 'lang')) . "&lang=";
      } ?>
      <?
      if (SID !== null && SID) {
        $link = $link . "&" . SID;
      }
      ?>
      <input type="hidden" name="gotech_header_lang_link" value="<?= $link ?>">
      <select id="gotech_header_lang" class="gotech_header_lang_spinner">
        <option value="ru" <? if (OnlineBookingSupport::getLanguage() == 'ru'): ?>selected="selected"<? endif; ?>>Rus
        </option>
        <option value="en" <? if (OnlineBookingSupport::getLanguage() == 'en'): ?>selected="selected"<? endif; ?>>Eng
        </option>
      </select>
    <? endif; ?>


  </div>

  <div class="logo">
    <? if ($arResult['LOGO']): ?>
      <img src="<?= $arResult['LOGO']['src'] ?>" onclick="location.href='<?= $arResult['SITE'] ?: $pathwl ?>';"/>
    <? else: ?>
      <img style="min-height: 71px"/>
    <? endif; ?>
    <a href="<?= $arResult['SITE'] ?: $pathwl ?>" onclick="location.href='<?= $arResult['SITE'] ?: $pathwl ?>';">
      <span><?= $arResult['NAME'] ?></span>
    </a>
  </div>

  <div id="mmenu_button" onclick="$('#gotech_header .buttons_wrap').slideToggle();"></div>

</div>
<?// if (!$arResult["USE_OFFICE_MODULE"] && !$USER->IsAuthorized()) { ?>
<?
 $show_auth_wrap = (!isset($_SESSION["AUTH_CLIENT_DATA"]) || !isset($_SESSION["AUTH_CLIENT_DATA"]->IndividualCustomer)) && $arResult["BONUS_SYSTEM_WEB_ADDRESS"] && $arResult["BONUS_SYSTEM_HOTEL_TOKEN"];
?>
<? if (!$USER->IsAuthorized()) { ?>
  <div
    id="gotech_auth_wrap"<? if ((!$show_auth_wrap || $_GET['booking'] == 'yes') && (!$arResult['SN_AUTH'] || $_SESSION['sn'] || (!$arResult['VK'] && !$arResult['FB']))): ?> style="display:none;"<? endif; ?>>
    <input type="hidden" name="auth_dialog_title" value="<?= GetMessage("AUTH") ?>">
    <input type="hidden" name="auth_dialog_error_login" value="<?= GetMessage("ERROR_LOGIN") ?>">
    <? if ($APPLICATION->GetCurPage(false) == $path . "my.php"): ?>
      <div class="auth_text" style="display:inline-block;margin-right:15px;"><?= GetMessage('MY_AUTH_TEXT') ?></div>
    <?else:?>
      <div class="auth_text" style="display:inline-block;margin-right:15px;"><?= GetMessage('AUTH_TEXT') ?></div>
    <?endif;?>
    <div class="auth_error_text" style="display:none;color: red;margin-bottom: 5px;"></div>
    <? if ($arResult['SN_AUTH'] && !$_SESSION['sn'] && ($arResult['VK'] || $arResult['FB']) && $_GET['booking'] != 'yes' && $APPLICATION->GetCurPage() != '/my.php'): ?>
      <div class="social_form" style="postion:relative;top:-2px;">
        <? /*
				<span class="social_text">
					<?=GetMessage('ENTER_VIA')?>:
				</span>
				*/ ?>
        <? if ($arResult['VK']): ?>
          <a href_="<?= $arResult['VK_LOGIN_URL'] ?>" class="sn_login vk_login" target="_parent"
             onclick="window.open('<?= $arResult['VK_LOGIN_URL'] ?>', '_blank', 'height=500,width=500')">
            <span class="vk_login_icon"></span>
            <span class="sn_login_text"><?= GetMessage('vk') ?></span>
          </a>
        <? endif; ?>
        <? if ($arResult['FB']): ?>
          <a href_="<?= $arResult['FACEBOOK_LOGIN_URL'] ?>" class="sn_login fb_login" target="_parent"
             onclick="window.open('<?= $arResult['FACEBOOK_LOGIN_URL'] ?>', '_blank', 'height=500,width=500')">
            <span class="fb_login_icon"></span>
            <span class="sn_login_text"><?= GetMessage('fb') ?></span>
          </a>
        <? endif; ?>
      </div>
    <? endif; ?>
    <div class="auth_form" <?if(!$show_auth_wrap):?>style="display:none;"<?endif;?>>
      <div class="standart_form">
        <p class="birth-date-text" style="display: none"><?= GetMessage('PLS_FILL_BIRTH_DATE') ?></p>
        <input type="text" id="gotech_auth_dialog_phone_input" name="phone" autocomplete="off" placeholder="<?= GetMessage('PHONE') ?>"
               value="" style="min-height:31px"/>
        <input type="text" id="gotech_auth_dialog_date_input" name="birth_date" autocomplete="off" placeholder="<?= GetMessage('BIRTH_DATE') ?>"
               value="" style="display: none;min-height:31px"/>
        <input type="text" id="gotech_auth_dialog_code_input" name="code" autocomplete="off" placeholder="<?= GetMessage('CODE') ?>"
               value="" style="display: none;min-height:31px"/>

        <a class="gotech_button" href="#" id="gotech_get_code" style="min-width: 115px;margin-top: 0px;">
          <?= GetMessage("GET_CODE") ?>
        </a>
        <img class="loader" src="/bitrix/js/onlinebooking/new/icons/progress.gif" width="50"
             style="display:none;position: absolute;top: -7px;left: 150px;">
        <a class="gotech_button" href="#" id="gotech_apply_code" style="display: none">
          <?= GetMessage("ENTER") ?>
        </a>
        <img class="loader-again" src="/bitrix/js/onlinebooking/new/icons/progress.gif" width="50"
             style="display:none;position: absolute;top: -7px;left: 300px;">

        <a class="get_code_again_link" href="#" style="display: none"><?= GetMessage("GET_CODE_AGAIN") ?><span
            id="gotech_code_timer" style="display: none"> через <span>60</span> сек</span></a>
      </div>
    </div>
  </div>
<? } ?>
<style>
  @media (max-width: 684px) {
    .loader-again {
      top: 48px !important;
      left: calc(50% - 25px) !important;
    }
  }
</style>
<script type="text/javascript">
  function new_search_click(e, link) {
    e.preventDefault();

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

  $('#gotech_header_auth').click(function () {
    $('#gotech_auth_dialog_phone_input').val('');
    $('#gotech_auth_dialog_code_input').val('');
    $('#gotech_auth_dialog_code_input').hide();
    $('#gotech_get_code').show();
    $('#gotech_apply_code').hide();
    $('.auth_form .get_code_again_link').hide();
    $('.auth_form .loader').hide();
    $('.auth_form .loader-again').hide();

    $('#gotech_auth_wrap').show();
    $('#gotech_auth_wrap .auth_form').slideDown();

    return false;
  });
  $('body').on('click', '#gotech_send_auth', function (e) {
    send_auth(e);
  });

  $('body').on('click', '#gotech_get_code, .get_code_again_link', function (e) {
    $('.birth-date-text').hide();
    if (!$('#gotech_code_timer').is(':visible')) {
      var phone = $(this).parent().find('[name="phone"]').val();
      if ($(this).parent().find('[name="birth_date"]').is(':visible')) {
        var birth_date = $(this).parent().find('[name="birth_date"]').val();
        if (birth_date && birth_date.length === 10) {
          $(this).parent().find('[name="birth_date"]').css('border-color', '');
          var birth_day_arr = birth_date.split('.');
          var format_birth_day = birth_day_arr[2] + birth_day_arr[1] + birth_day_arr[0];
          get_code(phone, $(this), format_birth_day);
        } else {
          $(this).parent().find('[name="birth_date"]').css('border-color', 'red');
        }
      } else {
        if (phone) {
          $(this).parent().find('[name="phone"]').css('border-color', '');
          get_code(phone, $(this), "");
        } else {
          $(this).parent().find('[name="phone"]').css('border-color', 'red');
        }
      }
    }
  });
  $('body').on('click', '#gotech_apply_code', function (e) {
    var code = $(this).parent().find('[name="code"]').val();
    if (code) {
      verify_client($(this).parent().find('[name="phone"]').val(), code);
    } else {
      $(this).parent().find('[name="code"]').css('border-color', 'red');
    }
  });

  $('#gotech_auth_dialog_date_input').mask("AB.CD.0000", {
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

  var timer;
  function startTimer() {
    var sec = $('#gotech_code_timer');
    var timer_elem = sec.find('>span');
    timer_elem.text('60');
    sec.show();
    var secVal = parseInt(timer_elem.text());
    if (timer) {
      clearTimeout(timer);
    }

    timer = setTimeout(function tick() {
      if (secVal > 1) {
        timer_elem.text(--secVal);
        timer = setTimeout(tick, 1000);
      } else {
        sec.hide();
        $('.get_code_again_link').show();
      }
    }, 1000);
  }

  function get_code(phone, $elem, birth_date) {
    if (!$elem.hasClass('get_code_again_link')) {
      $elem.hide();
      $('.standart_form .loader').show();
    } else {
      $('#gotech_apply_code').hide();
      $('.standart_form .loader-again').show();
    }
    var ajax_data = {
      wsdl: '<?=$arResult["WSDL"]?>',
      http_address: '<?=$arResult["BONUS_SYSTEM_WEB_ADDRESS"]?>',
      use_soap: !('<?=$arResult["BONUS_SYSTEM_WEB_ADDRESS"]?>' && '<?=$arResult["BONUS_SYSTEM_HOTEL_TOKEN"]?>'),
      phone: phone,
      guest_birth_date: birth_date,
      hotel: '<?=$arResult["HOTEL_CODE"]?>',
      hotel_token: '<?=$arResult["BONUS_SYSTEM_HOTEL_TOKEN"]?>',
      language: '<?=OnlineBookingSupport::getLanguage()?>'
    };
    $.post('/bitrix/components/onlinebooking/reservation.header/request_code.php', ajax_data, function (data) {
      data = JSON.parse(data);
      console.log(data);
      $('.standart_form .loader').hide();
      $('.standart_form .loader-again').hide();
      if (!data["is_sent"]) {
        if (parseInt(data["cards_count"]) > 1) {
          $('.auth_error_text').hide();
          $elem.parent().find('[name="phone"]').hide();
          $elem.parent().find('[name="birth_date"]').show();
          $('.birth-date-text').show();
          $elem.show();
        } else {
          //var error = !parseInt(data["cards_count"]) ? '<?//=$arResult["WSDL"]?>//' : data['error'];
          var error = data['error'];
          $('.auth_error_text').text(error);
          $('.auth_error_text').show();
          if (!$elem.hasClass('get_code_again_link')) {
            $elem.show();
          }
        }
      } else {
        $('.birth-date-text').hide();
        $('.auth_error_text').hide();
        $elem.parent().find('[name="phone"]').hide();
        $elem.parent().find('[name="birth_date"]').hide();
        $('#gotech_apply_code').show();
        if (!$elem.hasClass('get_code_again_link')) {
          $elem.parent().find('[name="code"]').show();
          $elem.parent().find('.get_code_again_link').show();
        }
        startTimer();
      }
    });
  }

  function verify_client(phone, code) {
    $('.standart_form .loader-again').show();
    $('#gotech_apply_code').css('opacity', 0);
    var ajax_data = {
      wsdl: '<?=$arResult["WSDL"]?>',
      use_soap: !('<?=$arResult["BONUS_SYSTEM_WEB_ADDRESS"]?>' && '<?=$arResult["BONUS_SYSTEM_HOTEL_TOKEN"]?>'),
      phone: phone,
      code: code,
      hotel: '<?=$arResult["HOTEL_CODE"]?>',
      http_address: '<?=$arResult["BONUS_SYSTEM_WEB_ADDRESS"]?>',
      hotel_token: '<?=$arResult["BONUS_SYSTEM_HOTEL_TOKEN"]?>',
      language: '<?=OnlineBookingSupport::getLanguage()?>'
    };
    var sid = '';
    if ($('[name="SID"]').length && $('[name="SID"]').val()) {
      sid = '?' + $('[name="SID"]').val();
    }
    $.post('/bitrix/components/onlinebooking/reservation.header/verify_client.php' + sid, ajax_data, function (data) {
      data = JSON.parse(data);
      console.log(data);
      if (data["ErrorDescription"]) {
        $('.auth_error_text').text(data['ErrorDescription']);
        $('.auth_error_text').show();
        $('.standart_form .loader-again').hide();
        $('#gotech_apply_code').css('opacity', 1);
      } else {
        $('.standart_form .loader-again').hide();
        $('#gotech_auth_wrap').slideUp();
        setTimeout(function () {
          $('#gotech_auth_wrap').hide();
          $('#gotech_auth_wrap .auth_form').slideUp();
          location.reload();
        }, 500);
      }
    });
  }
  // fix problem with continuous opnening language selector
  setTimeout(function() {
    $('[tabindex]').removeAttr('tabindex');
  }, 1000);

  function get_bonuses_amount() {
    var ajax_data = {
      http_address: '<?=$arResult["BONUS_SYSTEM_WEB_ADDRESS"]?>',
      hotel_token: '<?=$arResult["BONUS_SYSTEM_HOTEL_TOKEN"]?>'
    };
    var sid = '';
    if ($('[name="SID"]').length && $('[name="SID"]').val()) {
      sid = '?' + $('[name="SID"]').val();
    }
    $.post('/bitrix/components/onlinebooking/reservation.header/get_bonuses_amount.php' + sid, ajax_data, function (data) {
      data = JSON.parse(data);
      console.log(data);
      if (data["amount"]) {
        $('.header_bonuses_amount').text(data['amount']);
      }
    });
  }

  get_bonuses_amount();
</script>

<? if ($arResult["USE_OFFICE_MODULE"]): ?>
  <div id="gotech_auth_dialog">
    <input type="hidden" name="auth_dialog_title" value="<?= GetMessage("AGENT_LOG_IN_HEADER") ?>">
    <input type="hidden" name="auth_dialog_error_login" value="<?= GetMessage("ERROR_LOGIN") ?>">
    <div id="goteh_error_auth" class="error_auth" style="display:none;margin: 5px 0px;color: red;"></div>
    <div class="auth_form">
      <div class="standart_form">
        <input type="text" id="gotech_auth_dialog_login_input" name="login" placeholder="<?= GetMessage('LOGIN') ?>"
               value="" onkeyup="submit_auth();"/>
        <input type="password" id="gotech_auth_dialog_password_input" name="password"
               placeholder="<?= GetMessage('PASSWORD') ?>" value="" onkeyup="submit_auth();"/>

        <a class="gotech_button" href="#" id="gotech_send_auth" style="top: 20px;">
          <?= GetMessage("ENTER") ?>
        </a>
        <a class="password_restore_link"
           href="<?= $pathwl ?>&restore_password=Y"><?= GetMessage("FORGOT_PASSWORD") ?></a>
      </div>
    </div>
  </div>
<? endif; ?>

<script>
  $(function () {
    setSelectricForHeaderLang();
    $('#gotech_header_lang').change(function () {

      document.location.href = $('input[name="gotech_header_lang_link"]').val() + $(this).val();
    });
  });
</script>
