<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?__IncludeLang($_SERVER["DOCUMENT_ROOT"].$this->__folder."/lang/".OnlineBookingSupport::getLanguage()."/template.php");?>

<script>
	var cruises = [];
	<?foreach($arResult["cruises"] as $city_id => $depart_dates):?>
	<?foreach($depart_dates as $dep_date => $arrive_cities):?>
	<?foreach($arrive_cities as $arrive_city => $arrive_dates):?>
	<?foreach($arrive_dates as $cruises):?>
	<?foreach($cruises as $cruise):?>
	<?if($cruise["from"]):?>
	cruises.push({
		cruise_id: "<?=$cruise["cruise_id"]?>",
		cruise_name: "<?=$cruise["cruise_name"]?>",
		value: "<?=$city_id?>_<?=$cruise["from"]->format("d.m.Y")?>",
		sort: "<?=$city_id?><?=$cruise["arrive_city_id"]?><?=$cruise["from"]->format("Ymd")?><?=$cruise["to"]->format("Ymd")?>",
		id: "<?=$city_id?>",
		arrive_city_id: "<?=$cruise["arrive_city_id"]?>",
		arrive_city_name: "<?=$cruise["arrive_city_name"]?>",
		from: "<?=$cruise["from"]->format("d.m.Y")?>",
		to: "<?=$cruise["to"]->format("d.m.Y")?>",
		description: "<?=$cruise["description"]?>"
	});
	<?endif;?>
	<?endforeach;?>
	<?endforeach;?>
	<?endforeach;?>
	<?endforeach;?>
	<?endforeach;?>

	function compare(a,b) {
		if (a.sort < b.sort)
			return -1;
		if (a.sort > b.sort)
			return 1;
		return 0;
	}

	cruises.sort(compare);

	var request_arrive_city = "<?=htmlspecialcharsEx($_REQUEST["arriveto"])?>";
	var request_cruise = "<?=htmlspecialcharsEx($_REQUEST["cruise"])?>";
	var request_period_from = "<?if(!empty($_REQUEST["datefrom"])):?><?=htmlspecialcharsEx($_REQUEST["datefrom"])?><?elseif(!empty($_SESSION["PeriodFrom"])):?><?=$_SESSION["PeriodFrom"]?><?elseif(!empty($_REQUEST["PeriodFrom"])):?><?=htmlspecialchars($_REQUEST["PeriodFrom"])?><?endif;?>";
	var request_period_to = "<?if(!empty($_REQUEST["dateto"])):?><?=htmlspecialcharsEx($_REQUEST["dateto"])?><?elseif(!empty($_SESSION["PeriodTo"])):?><?=$_SESSION["PeriodTo"]?><?elseif(!empty($_REQUEST["PeriodTo"])):?><?=htmlspecialchars($_REQUEST["PeriodTo"])?><?endif;?>";
</script>

<div id="wrapper">
	<div id="gotech_search_window" <?if($arParams["TYPE"] == 'EMBEDED'):?>class="gotech_search_window_embeded"<?endif;?>>

		<div class="gotech_sw_title"><?=GetMessage('SEARCH_TITLE')?></div>

		<form method="get" action="<?=$APPLICATION->GetCurPageParam();?>" name="gotech_booking_form">
			<input type="hidden" name="SessionID" value="<?=$_REQUEST['SessionID']?>">
			<input type="hidden" name="UserID" value="<?=$_REQUEST['UserID']?>">
			<input type="hidden" name="utm_source" value="<?=$_REQUEST['utm_source']?>">
			<input type="hidden" name="utm_medium" value="<?=$_REQUEST['utm_medium']?>">
			<input type="hidden" name="utm_campaign" value="<?=$_REQUEST['utm_campaign']?>">

      <input type="hidden" name="embeded" value="<?if($arParams["TYPE"] == 'EMBEDED'):?>Y<?else:?>N<?endif;?>"/>
      <input type="hidden" name="embeded_room_type_code" value=""/>
      <input type="hidden" name="embeded_room_rate_code" value=""/>
      <input type="hidden" name="embeded_room_quota_code" value=""/>
      <input type="hidden" name="embeded_hotel_code" value=""/>
      <input type="hidden" name="embeded_output_code" value=""/>
      <input type="hidden" name="embeded_contact_person_email" value=""/>
      <input type="hidden" name="embeded_contact_person_phone" value=""/>
      <input type="hidden" name="embeded_login" value=""/>
      <input type="hidden" name="embeded_guid" value=""/>
      <input type="hidden" name="embeded_uuid" value=""/>
      <input type="hidden" name="embeded_price" value=""/>

      <input type="hidden" name="get_available_rooms_link" value="<?=($_SERVER["HTTPS"] == "on" ? "https://" : "http://")?><?=$_SERVER["SERVER_NAME"] . COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')?>get_available_room_types.php">

			<input type="hidden" name="md1" <?if(!empty($_REQUEST["datefrom"])):?> value="<?=htmlspecialcharsEx($_REQUEST["datefrom"])?>" <?elseif(!empty($_SESSION["PeriodFrom"])):?> value="<?=$_SESSION["PeriodFrom"]?>" <?elseif(!empty($_REQUEST["PeriodFrom"])):?> value="<?=htmlspecialchars($_REQUEST["PeriodFrom"])?>"<?else:?> value="<?=date('d.m.Y')?>" <?endif;?> />
			<input type="hidden" name="md2" <?if(!empty($_REQUEST["dateto"])):?> value="<?=htmlspecialcharsEx($_REQUEST["dateto"])?>" <?elseif(!empty($_SESSION["PeriodTo"])):?> value="<?=$_SESSION["PeriodTo"]?>" <?elseif(!empty($_REQUEST["PeriodTo"])):?> value="<?=htmlspecialchars($_REQUEST["PeriodTo"])?>"<?else:?> value="<?=date('d.m.Y',strtotime('+1 day'))?>" <?endif;?> />
			<input type="hidden" name="language" value="<?=mb_strtolower(OnlineBookingSupport::getLanguage())?>" />
			<input type="hidden" name="hotel_id" value="<?=$arResult["HOTEL"]["ID"]?>" />
			<input type="hidden" name="SalesBegin" value="<?=$arResult["HOTEL"]["SalesBegin"]?>" />
			<input type="hidden" name="SalesEnd" value="<?=$arResult["HOTEL"]["SalesEnd"]?>" />
			<input type="hidden" name="online_booking" value="Y" />
			<input type="hidden" name="nights_min" value="<?=$arResult["HOTEL"]["NIGHTS_MIN"]?>" />
			<input type="hidden" name="hours_min" value="<?=$arResult["HOTEL"]["HOURS_MIN"]?>" />
			<input type="hidden" name="room_type" <?if(isset($_REQUEST["rt"]) && !empty($_REQUEST["rt"])):?>value="<?=htmlspecialchars($_REQUEST["rt"])?>"<?else:?>value="<?=$arResult["HOTEL"]["RoomType"]?>"<?endif;?> />
			<input type="hidden" name="client_type" <?if(isset($_REQUEST["ct"]) && !empty($_REQUEST["ct"])):?>value="<?=htmlspecialchars($_REQUEST["ct"])?>"<?else:?>value="<?=$arResult["HOTEL"]["ClientType"]?>"<?endif;?> />
			<input type="hidden" name="hotel_max_guests" value="<?=$arResult["HOTEL"]["HOTEL_MAX_GUESTS"]?>" />
			<div id="gotech_search_window_dates">
				<?if($arResult["CRUISE_MODE"]):?>
					<input type="hidden" name="PeriodFrom">
					<input type="hidden" name="PeriodTo">
					<input type="hidden" name="night">
					<input type="hidden" name="RoomInfoText">
					<div class="param_block">
						<label class="pblock_label"><span style="display: block"><?=GetMessage("DEPART_CITY")?>:</span></label>
						<select id="gotech_search_window_depart_city" name="depart_city" style="width: 150px">
							<?foreach($arResult["depart_cities"] as $k => $city):?>
								<option value="<?=$city["id"]?>" <?if(isset($_REQUEST["departfrom"]) && !empty($_REQUEST["departfrom"]) && ($_REQUEST["departfrom"] == $city["id"] || $_REQUEST["departfrom"] == $city["name"])):?>selected="selected"<?endif;?>><?=$city["name"]?></option>
							<?endforeach;?>
						</select>
					</div>
					<div class="param_block">
						<label class="pblock_label"><span style="display: block"><?=GetMessage("DEPART_DATE")?>:</span></label>
						<select id="gotech_search_window_depart_date" name="depart_date" style="width: 150px">

						</select>
					</div>
					<br>
					<div class="param_block" style="display: none">
						<label class="pblock_label"><span style="display: block"><?=GetMessage("ARRIVE_CITY")?>:</span></label>
						<select id="gotech_search_window_arrive_city" name="arrive_city" style="width: 150px">

						</select>
					</div>
					<div class="param_block date_to_inst">
						<label class="pblock_label" style="margin-bottom: 18px;"><span style="display: block"><?=GetMessage("ARRIVE_DATE")?>:</span></label>
						<div id="gotech_search_window_depart_date_to"></div>
					</div>
					<div class="param_block date_to_list" style="display: none">
						<label class="pblock_label" style="margin-bottom: 18px;"><span style="display: block"><?=GetMessage("ARRIVE_DATE")?>:</span></label>
						<select id="gotech_search_window_depart_date_to_list" name="arrive_date" style="width: 150px">

						</select>
					</div>
					<div id="gotech_search_window_depart_date_info" style="color: #bbb;margin-top: -6px;"></div>
				<?else:?>
					<?if(!$arResult["HOTEL"]["HOURS_ENABLE"]):?>
						<div id="gotech_search_window_dates_from" class="param_block">
							<label for="gotech_search_window_dates_from_input" class="pblock_label active"><span><?=GetMessage("PERIOD_FROM")?></span></label>
							<span class="gotech_periods_container" style="position:relative;">
								<input id="gotech_search_window_dates_from_input" class="datepicker_input" placeholder="<?=GetMessage("PERIOD_FROM")?>" readonly="readonly"/>
								<input name="PeriodFrom" id="gotech_search_period_from" <?if(!empty($_REQUEST["PeriodFrom"])):?> value="<?=htmlspecialcharsEx($_REQUEST["PeriodFrom"])?>" <?endif;?> readonly/>
							</span>
						</div>
						<div id="gotech_search_window_dates_to" class="param_block">
							<label for="gotech_search_window_dates_to_input" class="pblock_label active"><span><?=GetMessage("PERIOD_TO")?></span></label>
							<span class="gotech_periods_container" style="position:relative;">
								<input id="gotech_search_window_dates_to_input" class="datepicker_input" placeholder="<?=GetMessage("PERIOD_TO")?>" readonly/>
								<input name="PeriodTo" id="gotech_search_period_to" <?if(!empty($_REQUEST["PeriodTo"])):?> value="<?=htmlspecialcharsEx($_REQUEST["PeriodTo"])?>" <?endif;?> readonly/>
							</span>
						</div>
						<div class="param_block spinner_block"<?if(0):?> style="position:relative;top:5px;"<?endif;?>>
							<span id="gotech_search_window_dates_nights_container" class="spinner_container">
								<label for="gotech_search_window_dates_to_input" class="pblock_label" style="margin-bottom:12px;"><span><?=GetMessage("NIGHT_QUANTITY")?></span></label>
								<div class="gotech_search_window_dates_nights_spinner_prev spinner_prev" onselectstart="return false" onmousedown="return false" onclick="hide_finded_data(this);"></div>
								<span id="gotech_search_window_dates_nights" class="number_field"></span>
								<div class="gotech_search_window_dates_nights_spinner_next spinner_next" onselectstart="return false" onmousedown="return false" onclick="hide_finded_data(this);"></div>
							</span>
							<input name="night" type="hidden" <?if(!empty($_REQUEST["night"])):?> value="<?=htmlspecialcharsEx($_REQUEST["night"])?>" <?endif;?>/>
						</div>
					<?else:?>
						<div id="gotech_search_window_dates_from" class="time param_block" style="position:relative;top:2px;">
							<label for="gotech_search_window_dates_from_input" class="pblock_label active"><span><?=GetMessage("PERIOD_FROM")?></span></label>
							<span class="gotech_periods_container" style="position:relative;">
								<input id="gotech_search_window_dates_from_input" class="datepicker_input" readonly="readonly"/>
								<input name="PeriodFrom" id="gotech_search_period_from" <?if(!empty($_REQUEST["PeriodFrom"])):?> value="<?=htmlspecialcharsEx($_REQUEST["PeriodFrom"])?>" <?endif;?> readonly/>
							</span>
						</div>
						<div id="gotech_search_window_dates_to" class="time param_block">
							<label for="gotech_search_window_dates_to_input" class="pblock_label active"><span><?=GetMessage("PERIOD_TIME")?></span></label>
							<span class="gotech_periods_container" style="position:relative;">
								<select name="TimeFrom"></select>
								<div id="templateFolder" style="font-size: 16px;padding: 7px; border-radius: 3px; display: none;border " class="hidden"><?= $templateFolder ?></div>
							</span>
						</div>
						<div class="param_block spinner_block hours_block">
							<label class="pblock_label active"><span><?=GetMessage("HOURS")?></span></label>
							<span id="gotech_search_window_dates_nights_container">
								<div class="gotech_search_window_dates_nights_spinner_prev time" onselectstart="return false" onmousedown="return false" onclick="hide_finded_data(this);"></div>
								<span id="gotech_search_window_dates_hours"></span>
								<div class="gotech_search_window_dates_nights_spinner_next time" onselectstart="return false" onmousedown="return false" onclick="hide_finded_data(this);"></div>
							</span>
						</div>
						<input name="hour" type="hidden" value="<?if(!empty($_REQUEST["night"])):?><?=htmlspecialcharsEx($_REQUEST["night"])?><? else: ?><?=($arResult["HOTEL"]["HOURS_MIN"] ? $arResult["HOTEL"]["HOURS_MIN"] : 4)?><?endif;?>" />
						<input type="hidden" name="PeriodTo" id="gotech_search_period_to" <?if(!empty($_REQUEST["PeriodTo"])):?> value="<?=htmlspecialcharsEx($_REQUEST["PeriodTo"])?>" <?endif;?> readonly/>
					<?endif;?>
				<?endif;?>
			</div>
			<div id="gotech_search_window_guests">
				<div class="gotech_search_window_guests_text">
					<?=GetMessage('GUESTS_TEXT')?>
				</div>
				<?if($arResult["HOTEL"]["HOTEL_MAX_ADULT"] > 0):?>
					<div id="gotech_search_window_guests_adults" class="param_block spinner_block" data-max="<?=$arResult["HOTEL"]["HOTEL_MAX_ADULT"]?>">

						<label for="gotech_search_window_guests_adults_spinner" class="pblock_label"><span><?if($arResult["HOTEL"]["HOTEL_MAX_CHILDREN"] > 0):?><?=GetMessage("ADULTS")?><?else:?><?=GetMessage("GUESTS")?><?endif;?></span></label>
						<span id="gotech_search_window_guests_adults_count" style="display:none;"></span>

						<input type="hidden" name="adults">
						<div id="gotech_search_window_guests_adults_spinner" class="spinner_container">

							<div class="gotech_search_window_guests_spinner_prev spinner_prev" onselectstart="return false" onmousedown="return false" onclick="hide_finded_data(this);"></div>
							<!--

							<?for($i=1; $i <= $arResult["HOTEL"]["HOTEL_MAX_ADULT"]; $i++) {?>
								--><div style="position:absolute;width:0px;height:0px;left: -9999px" class="<?if((!empty($_REQUEST['adults']) && $_REQUEST['adults'] >= $i) || ($_REQUEST['adults'] == 0 && $i == 1)):?>gotech_search_window_guests_adults_spinner_icon_active<?elseif((!isset($_REQUEST['adults']) || empty($_REQUEST['adults'])) && $_SESSION["Guests"]["Adults"]["Quantity"] >= $i):?>gotech_search_window_guests_adults_spinner_icon_active<?elseif((!isset($_REQUEST['adults']) && !isset($_SESSION["Guests"]["Adults"]["Quantity"])) && $i <= $arResult["HOTEL"]["GUESTS_DEFAULT"]):?>gotech_search_window_guests_adults_spinner_icon_active<?else:?>gotech_search_window_guests_adults_spinner_icon<?endif;?>" onclick="hide_finded_data(this);"></div><!--
							<?}?>
							-->
							<span id="gotech_search_window_guests_adults_number" class="number_field"><?=(int)$_REQUEST['adults']?></span>
							<div class="gotech_search_window_guests_spinner_next_active spinner_next" onselectstart="return false" onmousedown="return false" onclick="hide_finded_data(this);"></div>
						</div>
					</div>
				<?endif;?>
				<?if($arResult["HOTEL"]["HOTEL_MAX_CHILDREN"] > 0):?>
					<div id="gotech_search_window_guests_children" class="param_block spinner_block">

						<label for="gotech_search_window_guests_children_spinner" class="pblock_label"><span><?=GetMessage("CHILDREN")?></span></label>
						<span id="gotech_search_window_guests_children_count" style="display:none;">0</span>

						<input type="hidden" name="children" value="<?=(int)$_REQUEST['kids']?>">
						<div id="gotech_search_window_guests_children_spinner" class="spinner_container">
							<div class="gotech_search_window_guests_spinner_prev spinner_prev" onselectstart="return false" onmousedown="return false" onclick="hide_finded_data(this);"></div>
							<!--
							<?for($i=0; $i < $arResult["HOTEL"]["HOTEL_MAX_CHILDREN"]; $i++) {?>


								--><div style="position:absolute;width:0px;height:0px;left: -9999px;" class="<?if(!empty($_REQUEST['kids']) && $_REQUEST['kids'] > $i):?>gotech_search_window_guests_children_spinner_icon_active<?elseif(!isset($_REQUEST['kids']) && !empty($_SESSION["Guests"]["Kids"]["Quantity"]) && $_SESSION["Guests"]["Kids"]["Quantity"] > $i):?>gotech_search_window_guests_children_spinner_icon_active<?else:?>gotech_search_window_guests_children_spinner_icon<?endif;?>" onclick="hide_finded_data(this);"></div><!--
							<?}?>
							-->
							<span id="gotech_search_window_guests_children_number" class="number_field"><?=(int)$_REQUEST['kids']?></span>
							<div class="gotech_search_window_guests_spinner_next_active spinner_next" onselectstart="return false" onmousedown="return false" onclick="hide_finded_data(this);"></div>
						</div>
					</div>
					<div id="gotech_search_window_guests_ages">
						<?/*<label for="gotech_search_window_guests_ages_spinner"><?=GetMessage("CHILDREN_AGE")?></label>*/?>
						<div class="gotech_search_window_guests_ages_block">
							<?for($i = 1; $i <= $arResult["HOTEL"]["HOTEL_MAX_CHILDREN"]; $i++):?>
								<div class="param_block">
									<label class="pblock_label"><span><?=GetMessage("AGE")?> <?=$i?> <?=GetMessage("CHILD")?></span></label>
									<input type="hidden" name="shChildrenYear_<?=($i-1)?>" value="false">
									<select id="gotech_search_window_guests_ages_<?=($i-1)?>" class="gotech_search_window_guests_ages_spinner" name="childrenYear_<?=($i-1)?>" onchange="hide_finded_data(this);">
										<?for($y = 0; $y <= 17; $y++):?>
											<option value="<?=$y?>" <?if(!empty($_REQUEST['kid'.$i]) && $_REQUEST['kid'.$i] == $y):?>selected="selected"<?elseif(empty($_REQUEST['kid'.$i]) && isset($_SESSION["Guests"]["Kids"]["Age"][$i-1]) && !empty($_SESSION["Guests"]["Kids"]["Age"][$i-1]) && $_SESSION["Guests"]["Kids"]["Age"][$i-1] == $y):?>selected="selected"<?endif;?>><?if($y === 0):?><?=" < 1 "?><?else:?><?=$y?><?endif;?></option>
										<?endfor;?>
									</select>
								</div>
							<?endfor;?>
						</div>
					</div>
				<?endif;?>
			</div>
			<?if($arResult["add_text"]):?>
				<div style="position: relative;margin-top: 15px; padding-bottom: 43px;"><?=$arResult["add_text"]?></div>
			<?endif;?>
			<div id="gotech_search_window_contacts" style="display: none;">
				<?if(!$arResult["IS_AGENT"]):?>
					<?if(!$USER->IsAuthorized() || !in_array(COption::GetOptionString('gotech.hotelonline', 'USER_AGENT_GROUP'), $USER->GetUserGroupArray())):?>
						<div id="gotech_search_window_contacts_phone">
							<label for="gotech_search_window_contacts_phone_input"><?=GetMessage("CONTACTS")?></label><br>
							<input type="text" id="gotech_search_window_contacts_phone_input" placeholder="Телефон" name="phone" value="" maxlength="20" onkeyup="hide_finded_data(this);" onchange="hide_finded_data(this);"/>
						</div>
						<div id="gotech_search_window_contacts_email">
							<input type="text" id="gotech_search_window_contacts_email_input" placeholder="Эл. почта" name="email" value="" onkeyup="hide_finded_data(this);" onchange="hide_finded_data(this);"/>
						</div>
					<?endif;?>
				<?endif;?>
			</div>
			<div id="gotech_search_window_footer">
				<?if(!$arResult["IS_AGENT"]):?>
					<?if($arResult["HOTEL"]["PROMO"] == "Y"):?>
						<div id="gotech_search_window_footer_promo">
							<span class="promo_label" onclick='$(this).parent().find(".promo_wrap").slideToggle();'>
								<?=GetMessage("PROMO")?><?=GetMessage("PROMO_IF_YOU_HAVE")?>
							</span>
							<?/*
							<label for="gotech_search_window_footer_promo_input"><?=GetMessage("PROMO")?><span><?=GetMessage("PROMO_IF_YOU_HAVE")?></span></label>
							*/?>
							<div class="promo_wrap">
								<input type="text" id="gotech_search_window_footer_promo_input" name="promo_code" value="<?=$_REQUEST['promo'] ?: htmlspecialcharsEx($_SESSION["promo_code"])?>" onkeyup="hide_finded_data(this);" onchange="hide_finded_data(this);"/>
							</div>
						</div>
					<?endif;?>
				<?endif;?>
				<div id="gotech_search_window_footer_find">
					<input type="hidden" name="send_data" value="Y" />
					<span class="gotech_blue_button" id="gotech_search_window_footer_find_link_wait">
						<span class="inner_text">
							<span id="gotech_search_window_footer_find_link_wait_text"><?=GetMessage("SEARCHdotdotdot")?></span>
							<span id="gotech_progress_icon"></span>
						</span>
					</span>
          <?if ($arParams['TYPE'] != 'EMBEDED'):?>
            <a class="gotech_button_big" href="#" id="gotech_search_window_footer_find_link">
              <span class="inner_text"><?=GetMessage("FIND_NUMBER")?></span>
            </a>
          <?else:?>
            <a class="gotech_button_big" href="#" id="gotech_search_window_footer_check_link">
              <span class="inner_text"><?=GetMessage("CHECK_NUMBER")?></span>
            </a>
          <?endif;?>
				</div>
			</div>
		</form>
	</div>
	<div id="gotech_search_result"></div>
</div>
<div id="hour1" class="hidden" style="display:none"><?//=GetMessage("1HOUR")?></div>
<div id="hour2" class="hidden" style="display:none"><?//=GetMessage("2HOURS")?></div>
<div id="hour5" class="hidden" style="display:none"><?//=GetMessage("5HOURS")?></div>
<script>
	$(function(){

		<?if($_REQUEST['promo']):?>
		$('.promo_label').click();
		<?endif;?>

		setTimeout(function() {
			$.get('/bitrix/components/onlinebooking/onlinebooking/ajax.php?FormType=getBasket&hotel_id='+$('[name=hotel_id]').val(),
				function(data){
					if(data)
					{
						$('#gotech_search_choose').html(data);
						console.log('cart_item='+$('#gotech_search_choose .cart_item').length);
						if($('#gotech_search_choose .cart_item').length)
						{
							$('#gotech_search_choose').css('opacity', 1);
							$('#gotech_search_choose').show();
						}
					}
				});
		},1000);

		if($('[name=TimeFrom]').length)
		{
			$('[name=TimeFrom]').selectric({
				maxHeight: 160,
				disableOnMobile: true
			});
		}


		setSelectricForAges();


		var datepickerRange = getDatepickerRange();
		setCalendar('#gotech_search_period_from', datepickerRange);
		setCalendar('#gotech_search_period_to', datepickerRange);

		setPeriods(datepickerRange);
		setNumberOfNights(datepickerRange);

		<?if($_REQUEST['timefrom']):?>
		<?
		$t1 = $_REQUEST['timefrom'][0].$_REQUEST['timefrom'][1];
		$t2 = $_REQUEST['timefrom'][2].$_REQUEST['timefrom'][3];

		if($t1[0] != 0 && $t1 > 23)
			$t1 = '00';
		if($t2[0] != 0 && $t2 > 59)
			$t2 = '00';

		//$time = $_REQUEST['timefrom'][0].$_REQUEST['timefrom'][1].':'.$_REQUEST['timefrom'][2].$_REQUEST['timefrom'][3];
		$time = $t1.':'.$t2;
		?>

		setTimeout(function() {

			if(!$('#gotech_search_window_dates [name=TimeFrom] option[value="<?=$time?>"]').length)
			{
				$('#gotech_search_window_dates [name=TimeFrom] option').each(function() {

					var tt1 = <?=$t1?>;
					var tt2 = <?=$t2?>;

					var v = $(this).val();
					var t = v.split(':');
					if(t[0] == tt1 && t[1] == '00' && tt2 < 30)
					{
						$(this).after('<option value="<?=$time?>"><?=$time?></option>');
					}
					if(t[0] == tt1 && t[1] == '30' && tt2 > 30)
					{
						$(this).after('<option value="<?=$time?>"><?=$time?></option>');
					}

				});
			}

			$('#gotech_search_window_dates [name=TimeFrom]').val('<?=$time?>');
			$('#gotech_search_window_dates [name=TimeFrom]').selectric('destroy');
			$('#gotech_search_window_dates [name=TimeFrom]').selectric({
				maxHeight: 160,
				disableOnMobile: true
			});
		},500);
		<?endif;?>
		<?if($_REQUEST['hours']):?>
		$('[name=hour]').val(<?=htmlspecialchars($_REQUEST['hours'])?>);
		$('#gotech_search_window_dates_hours').text('<?=htmlspecialchars($_REQUEST['hours'])?>');
		<?endif;?>


	});
	$(document).ready(function(){
		/* = = = = ADULTS = = = = */
		var adIcCount = $('#gotech_search_window_guests_adults_spinner').children().length - 2;
		var adCount = 0;
    var child;
		if(adIcCount == 1){
			hideButton('prev', 'adults');
			hideButton('next', 'adults');
		}else{
			for(var i = 0; i < adIcCount; i++){
				child = $($('#gotech_search_window_guests_adults_spinner').children()[i+1]);
				if(child.hasClass('gotech_search_window_guests_adults_spinner_icon_active')){
					if(i == 1){
						showButton('prev', 'adults');
					}
					if(i == adIcCount-1){
						hideButton('next', 'adults');
					}
					adCount++;
				}
			}
		}
		$('#gotech_search_window_guests_adults_count').html(adCount);
		$('input[name="adults"]').val(adCount);
		$('#gotech_search_window_guests_adults_number').text(adCount);
		/* = = = = CHILDREN = = = = */
		var childIcCount = $('#gotech_search_window_guests_children_spinner').children().length - 2;
		var childCount = 0;
		if(childIcCount == 0){
			hideButton('prev', 'children');
			hideButton('next', 'children');
			$('label[for="gotech_search_window_guests_ages_spinner"]').hide();
			for(var j = 0; j < 4; j++){
				/* hide ages */
				$('input[name="shChildrenYear_'+j+'"]').val('false');
				$('#gotech_search_window_guests_ages_'+j).parent().parent().hide();
			}
		}else{
			$('label[for="gotech_search_window_guests_ages_spinner"]').hide();
			for(var i = 0; i < childIcCount; i++){
				child = $($('#gotech_search_window_guests_children_spinner').children()[i+1]);
				if(child.hasClass('gotech_search_window_guests_children_spinner_icon_active')){
					if(i == 0){
						showButton('prev', 'children');
					}
					if(i == childIcCount-1){
						hideButton('next', 'children');
					}
					/* show ages */
					$('input[name="shChildrenYear_'+i+'"]').val('true');
					$('label[for="gotech_search_window_guests_ages_spinner"]').show();
					$('#gotech_search_window_guests_ages_'+i).parent().parent().show();
					$('#gotech_search_window_guests_ages_'+i).parent().parent().css('display', '-moz-inline-stack');
					$('#gotech_search_window_guests_ages_'+i).parent().parent().css('display', 'inline-block');
					$('#gotech_search_window_guests_ages_'+i).parent().parent().parent().find('.pblock_label').addClass('active');
					childCount++;
				}
			}
		}
		$('#gotech_search_window_guests_children_count').html(childCount);
		$('input[name="children"]').val(childCount);
		$('#gotech_search_window_guests_children_number').text(childCount);


		<?if(isset($_REQUEST["send_data"]) && $_REQUEST["send_data"] == 'Y' && $arResult['START_FROM_GET']):?>
		setTimeout(function() {
			$('#gotech_search_window_footer_find_link').click();
		},800);
		<?endif;?>


		parent.postMessage('showBasket','*');

	});
</script>
