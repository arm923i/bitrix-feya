<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?__IncludeLang($_SERVER["DOCUMENT_ROOT"].$this->__folder."/lang/".OnlineBookingSupport::getLanguage()."/template.php");?>
<?$SESSION["NUMBERS_BOOKING"] = $arResult["SESSION"]["NUMBERS_BOOKING"];?>
<div id="gotech_result">
	<?if(isset($arResult["ERROR"]) && !empty($arResult["ERROR"])):?>
		<div class="gotech_error_text gotech_big_text"><?=$arResult["ERROR"]?></div>
	<?elseif(isset($arResult["SUCCESS"]) && !empty($arResult["SUCCESS"])):?>
		<div class="gotech_success_text gotech_big_text"><?=$arResult["SUCCESS"]?></div>
	<?else:?>
		<?
		LocalRedirect('my.php?search=Y&hotel='.$arResult["hotel_info"]["ID"].'&reservation='.$arResult["info_order"]["GuestGroup"].'&data='.($arResult["info_order"]["GuestEMail"] ?: $arResult["info_order"]["GuestPhone"]))
		?>
		<form method="post" name="gotech_result_form">
			<input type="hidden" name="language" value="<?=OnlineBookingSupport::getLanguage()?>">
			<div id="print_content">
				<div id="gotech_result_header">
					<div id="gotech_result_header_text" class="gotech_middle_text"><?=GetMessage("BOOK_RESULT")?></div>
					<div id="gotech_result_header_about">
						<div id="gotech_result_header_about_hotel">
							<span id="gotech_result_header_about_hotel_label"><?=GetMessage("HOTEL")?></span>
							<span id="gotech_result_header_about_hotel_value"><?=$arResult["hotel_info"]["NAME"]?></span>
						</div>
						<?if(!empty($arResult["hotel_info"]["HOTEL_ADDRESS"])):?>
							<div id="gotech_result_header_about_address">
								<span id="gotech_result_header_about_address_label"><?=GetMessage("ADDRESS")?></span>
								<span id="gotech_result_header_about_address_value"><?=$arResult["hotel_info"]["HOTEL_ADDRESS"]?></span>
							</div>
						<?endif;?>
						<?if(!empty($arResult["hotel_info"]["HOTEL_PHONE"])):?>
							<div id="gotech_result_header_about_phone">
								<span id="gotech_result_header_about_phone_label"><?=GetMessage("PHONE")?></span>
								<span id="gotech_result_header_about_phone_value"><?=$arResult["hotel_info"]["HOTEL_PHONE"]?></span>
							</div>
						<?endif;?>
						<?if(!empty($arResult["hotel_info"]["HOTEL_FAX"])):?>
							<div id="gotech_result_header_about_fax">
								<span id="gotech_result_header_about_fax_label"><?=GetMessage("FAX")?></span>
								<span id="gotech_result_header_about_fax_value"><?=$arResult["hotel_info"]["HOTEL_FAX"]?></span>
							</div>
						<?endif;?>
						<?if(!empty($arResult["hotel_info"]["HOTEL_MAIL"])):?>
							<div id="gotech_result_header_about_email">
								<span id="gotech_result_header_about_email_label"><?=GetMessage("EMAIL")?></span>
								<span id="gotech_result_header_about_email_value"><a href="mailto:<?=$arResult["hotel_info"]["HOTEL_MAIL"]?>"><?=$arResult["hotel_info"]["HOTEL_MAIL"]?></a></span>
							</div>
						<?endif;?>
					</div>
				</div>
				<div id="gotech_result_content">
					<div id="gotech_result_content_header">
						<input type="hidden" name="reservation" value="<?=$arResult["info_order"]["GuestGroup"]?>">
						<span id="gotech_result_content_header_text" class="gotech_big_text">
							<?=GetMessage("NUMBER_BOOK")?> <?=$arResult["info_order"]["GuestGroup"]?>
						</span>
						<span id="gotech_result_content_header_print">
							<span id="gotech_result_content_header_print_icon" class="gotech_content_print"></span>
							<span id="gotech_result_content_header_print_text" class="gotech_content_print"><?=GetMessage("PRINT")?></span>
						</span>
					</div>
					<?$roomId = 0?>
					<?foreach($arResult["BOOKING"] as $number):?>
						<div class="gotech_result_content_item">
							<div class="gotech_result_content_item_header">
								<span class="gotech_result_content_item_header_room"><?=$number["RoomTypeDescription"]?></span>
								<?if($arResult["DO_CLIENT_CAN_CHANGE_RESERVATION"] && (strtotime($number["CheckInDate"])-86400)>time()):?>
									<a class="gotech_result_content_item_header_change_reservation" href="<?=OnlineBookingSupport::getProtocol().$_SERVER["SERVER_NAME"].COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')."my.php?lang=".OnlineBookingSupport::getLanguage()."&hotel_code=".$arResult["hotel_info"]["ID"]."&hotel=".$arResult["hotel_info"]["ID"]."&reservation=".$arResult["info_order"]["GuestGroup"]."&data=".($arResult["info_order"]["email"]?$arResult["info_order"]["email"]:$arResult["info_order"]["phone"])."&search=Y&cancel=N&is_change=Y&is_change_room=".$roomId?>"><?=GetMessage("DO_CHANGE")?></a>
								<?endif;?>
								<br>
								<span class="gotech_result_content_item_header_check_in"><?=GetMessage("ARRIVAL")?></span>
								<span class="gotech_result_content_item_header_check_in_value"><?=$number["CheckInDate"]?></span>
								<span class="gotech_result_content_item_header_date_arrows"></span>
								<span class="gotech_result_content_item_header_check_out"><?=GetMessage("DEPARTURE")?></span>
								<span class="gotech_result_content_item_header_check_out_value"><?=$number["CheckOutDate"]?></span>
								<span class="gotech_result_content_item_header_guests"><?=GetMessage("GUESTS")?></span>
								<span class="gotech_result_content_item_header_guests_icon"></span>
								<span class="gotech_result_content_item_header_guests_value"><?=count($number["Guests"])?></span>
								<span class="gotech_result_content_item_header_price gotech_big_text"><?=number_format($number["Cost"], 2, ',', ' ')?> <?=$number["Currency"]?></span>
							</div>
							<div class="gotech_result_content_item_guests">
								<?if(!empty($number["Guests"])):?>
									<?foreach($number["Guests"] as $guest):?>
										<div class="gotech_result_content_item_guests_item"><?=$guest?></div>
									<?endforeach;?>
								<?endif;?>
							</div>
						</div>
						<?$roomId++?>
					<?endforeach;?>
				</div>
				<div id="gotech_result_contacts_information">
					<div id="gotech_result_contacts_information_header" class="gotech_middle_text"><?=GetMessage("CONTACT_PERSON")?></div>
					<div id="gotech_result_contacts_information_content">
						<div id="gotech_result_contacts_information_content_name">
							<span id="gotech_result_contacts_information_content_name_label"><?=GetMessage("NAME")?></span>
							<span id="gotech_result_contacts_information_content_name_value"><?=$arResult["info_order"]["GuestFullName"]?></span>
						</div>
						<input type="hidden" name="phone" value="<?=$arResult["info_order"]["GuestPhone"]?>">
						<?if(!empty($arResult["info_order"]["GuestPhone"])):?>
							<div id="gotech_result_contacts_information_content_phone">
								<span id="gotech_result_contacts_information_content_phone_label"><?=GetMessage("PHONE")?></span>
								<span id="gotech_result_contacts_information_content_phone_value"><?=$arResult["info_order"]["GuestPhone"]?></span>
							</div>
						<?endif;?>
						<input type="hidden" name="email" value="<?=$arResult["info_order"]["GuestEMail"]?>">
						<?if(!empty($arResult["info_order"]["GuestEMail"])):?>
							<div id="gotech_result_contacts_information_content_email">
								<span id="gotech_result_contacts_information_content_email_label"><?=GetMessage("EMAIL")?></span>
								<span id="gotech_result_contacts_information_content_email_value"><a href="mailto:<?=$arResult["info_order"]["GuestEMail"]?>"><?=$arResult["info_order"]["GuestEMail"]?></a></span>
							</div>
						<?endif;?>
					</div>
				</div>
				<?if(!empty($arResult["hotel_info"]["RESERVATIONS_CONDITIONS"])):?>
					<div id="gotech_result_booking_conditions"><?=$arResult["hotel_info"]["RESERVATIONS_CONDITIONS"]?></div>
				<?endif;?>
				<?if(!empty($arResult["info_order"]["ReservationConditions"])):?>
					<div id="gotech_result_booking_conditions"><?=$arResult["info_order"]["ReservationConditions"]?></div>
				<?endif;?>
				<?if($arResult["SHOW_FREE_CANCEL"] && !empty($arResult["info_order"]["CheckDate"])):?>
					<div id="gotech_result_booking_conditions"><b><?=GetMessage("FREE_ANNULATION")?><?=$arResult["info_order"]["CheckDate"]?></b></div>
				<?endif;?>
				<div id="gotech_result_footer_total_line">
					<div id="gotech_result_footer_total_line_total">
						<span id="gotech_result_footer_total_line_total_label" class="gotech_big_text"><?=GetMessage("ITOGO")?></span>
						<span id="gotech_result_footer_total_line_total_value" class="gotech_big_text"><?=number_format($arResult["info_order"]["TotalSum"], 2, ',', ' ')?> <?=$arResult["info_order"]["Currency"]?></span>
					</div>
					<div class="gotech_clear"></div>
					<div id="gotech_result_footer_total_line_paid">
						<span id="gotech_result_footer_total_line_paid_label" class="gotech_big_text"><?=GetMessage("BOOK_STATUS_PAY")?></span>
						<span id="gotech_result_footer_total_line_paid_value" class="gotech_big_text">0,00 <?=$arResult["info_order"]["Currency"]?></span>
					</div>
					<div class="gotech_clear"></div>
					<div id="gotech_result_footer_total_line_left_to_pay">
						<span id="gotech_result_footer_total_line_left_to_pay_label" class="gotech_big_text"><?=GetMessage("BOOK_STATUS_DELAY_PAY")?></span>
						<span id="gotech_result_footer_total_line_left_to_pay_value" class="gotech_big_text"><?=number_format($arResult["info_order"]["TotalSum"], 2, ',', ' ')?> <?=$arResult["info_order"]["Currency"]?></span>
					</div>
				</div>
			</div>
			<div id="gotech_result_footer">
				<?if(COption::GetOptionString('gotech.hotelonline', 'includePaySys') == 1):?>
					<?if(isset($arResult["PAYMENT_METHODS"]) && !empty($arResult["PAYMENT_METHODS"])):?>
						<div id="gotech_result_footer_payment_methods">
							<div id="gotech_result_footer_payment_methods_label">
								<?=GetMessage("PAY_METHOD")?>
							</div>
							<div id="gotech_result_footer_payment_methods_radiobuttons">
								<?
									$fullSum = $arResult["info_order"]["TotalSum"];
									$firstDaySum = $arResult["info_order"]["FirstDaySum"];
									$isFirst = true;
								?>
								<?foreach($arResult["PAYMENT_METHODS"] as $method):?>
									<?if($method["FIRST_NIGHT"]=="Yes"):?>
										<?if(FloatVal($firstDaySum)<=FloatVal($fullSum)):?>
											<?$id=$method["PAYMENT_SYSTEM"]."-".(FloatVal($firstDaySum)*100)."-".$method["IS_CASH"]."-".$method["IS_RECEIPT"]."-".$method["IS_LEGAL"];?>
											<label class="gotech_result_footer_payment_methods_radiobuttons_label"><input type="radio" name="gotech_result_footer_payment_methods_radiobuttons" id="<?=$id?>" <?if($isFirst):?>checked<?endif;?>/><span class="label"><span></span></span><span><?=$method["NAME"]." (".number_format(FloatVal($arResult["info_order"]["FirstDaySum"]), 2, ',', ' ')." ".$arResult["CurrencySymbol"].")"?></span></label>
										<?endif;?>
									<?elseif(!empty($method["DISCOUNT"])):?>
										<?$id=$method["PAYMENT_SYSTEM"]."-".((FloatVal($fullSum)*IntVal($method["DISCOUNT"])/100)*100)."-".$method["IS_CASH"]."-".$method["IS_RECEIPT"]."-".$method["IS_LEGAL"];?>
										<label class="gotech_result_footer_payment_methods_radiobuttons_label"><input type="radio" name="gotech_result_footer_payment_methods_radiobuttons" id="<?=$id?>" <?if($isFirst):?>checked<?endif;?>/><span class="label"><span></span></span><span><?=$method["NAME"]." (".(number_format(Round(FloatVal($fullSum)*IntVal($method["DISCOUNT"])/100, 2), 2, ',', ' '))." ".$arResult["CurrencySymbol"].")"?></span></label>
									<?elseif(!$method["IS_CASH"]):?>
										<?$id=$method["PAYMENT_SYSTEM"]."-".(FloatVal($fullSum)*100)."-".$method["IS_CASH"]."-".$method["IS_RECEIPT"]."-".$method["IS_LEGAL"];?>
										<label class="gotech_result_footer_payment_methods_radiobuttons_label"><input type="radio" name="gotech_result_footer_payment_methods_radiobuttons" id="<?=$id?>" <?if($isFirst):?>checked<?endif;?>/><span class="label"><span></span></span><span><?=$method["NAME"]." (".number_format(FloatVal($arResult["info_order"]["TotalSum"]), 2, ',', ' ')." ".$arResult["CurrencySymbol"].")"?></span></label>
									<?endif;?>
									<?if($method["IS_LEGAL"]):?>
										<div class="gotech_result_footer_payment_methods_customer_data">
											<div <?if(!empty($arResult["info_order"]["CustomerName"]) && !empty($arResult["info_order"]["CustomerEMail"]) && !empty($arResult["info_order"]["CustomerTIN"])):?>style="display: none"<?endif;?>>
												<div class="gotech_result_footer_payment_methods_customer_data_text"><?=GetMessage("CUSTOMER_FIND_YOUR_ORGANIZATION")?></div>
												<input type="text" name="customer_search" placeholder="<?=GetMessage("CUSTOMER_SEARCH")?>">
												<div class="gotech_result_footer_payment_methods_customer_data_text"><?=GetMessage("CUSTOMER_OR_FILL_INPUT_FIELD")?></div>
												<label><span><?=GetMessage("CUSTOMER_DESCRIPTION")?><span style="color: red">*</span>: </span><input type="text" name="customer_description" placeholder="<?=GetMessage("CUSTOMER_DESCRIPTION")?>" value="<?=$arResult["info_order"]["CustomerName"]?>"></label>
												<label><span><?=GetMessage("CUSTOMER_ADDRESS")?><span style="color: red">*</span>: </span><input type="text" name="customer_address" placeholder="<?=GetMessage("CUSTOMER_ADDRESS")?>" value="<?=$arResult["info_order"]["CustomerLegacyAddress"]?>"></label>
												<label><span><?=GetMessage("CUSTOMER_PHONE")?>: </span><input type="text" name="customer_phone" placeholder="<?=GetMessage("CUSTOMER_PHONE")?>" value="<?=$arResult["info_order"]["CustomerPhone"]?>"></label>
												<label><span><?=GetMessage("CUSTOMER_EMAIL")?><span style="color: red">*</span>: </span><input type="text" name="customer_email" placeholder="<?=GetMessage("CUSTOMER_EMAIL")?>" value="<?=$arResult["info_order"]["CustomerEMail"]?>"></label>
												<label><span><?=GetMessage("CUSTOMER_TIN")?><span style="color: red">*</span>: </span><input type="text" name="customer_tin" placeholder="<?=GetMessage("CUSTOMER_TIN")?>" value="<?=$arResult["info_order"]["CustomerTIN"]?>"></label>
												<label><span><?=GetMessage("CUSTOMER_KPP")?><span style="color: red">*</span>: </span><input type="text" name="customer_kpp" placeholder="<?=GetMessage("CUSTOMER_KPP")?>" value="<?=$arResult["info_order"]["CustomerKPP"]?>"></label>
											</div>
										</div>
									<?endif;?>
									<?if(!$method["IS_CASH"]):?>
										<?$isFirst = false;?>
									<?endif;?>
								<?endforeach;?>
							</div>
						</div>
						<div class="gotech_clear"></div>
					<?endif;?>
				<?endif;?>
				<div id="gotech_result_footer_buttons">
					<?$link = COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')."payment/payment.php?inv_id=".$arResult["info_order"]["GuestGroup"]."&amp;hotel_id=".$arResult["hotel_info"]["ID"]."&amp;client_code=".$arResult["info_order"]["GuestCode"]."&amp;Currency=".$arResult["info_order"]["CurrencyDescription"]."&amp;CurrencyCode=".$arResult["info_order"]["CurrencyCode"]."&amp;inv_desc=".$arResult["info_order"]["GuestFullName"]."&amp;first_name=".$arResult["info_order"]["GuestFirstName"]."&amp;last_name=".$arResult["info_order"]["GuestLastName"]."&amp;lang=".OnlineBookingSupport::getLanguage()."&amp;email=".$arResult["info_order"]["email"]."&amp;phone=".$arResult["info_order"]["phone"]."&amp;hotel=".$arResult["info_order"]["hotel_code"]."&amp;hotel_name=".$arResult["info_order"]["hotel_name"]."&amp;uuid=".$arResult["info_order"]["UUID"];?>
					<div id="gotech_result_footer_buttons_annulation">
						<input type="hidden" name="cancel" value="N" />
						<a class="gotech_yellow_button" href="#" id="cancel">
							<?=GetMessage("DELETE")?>
						</a>
					</div>
					<?if(COption::GetOptionString('gotech.hotelonline', 'includePaySys') == 1):?>
						<?if(isset($arResult["PAYMENT_METHODS"]) && !empty($arResult["PAYMENT_METHODS"])):?>
							<?$ar_group = $USER->GetUserGroup($USER->GetID());?>
							<?if(!$USER->IsAuthorized() || !in_array(COption::GetOptionint('gotech.hotelonline', 'USER_AGENT_GROUP'), $ar_group)):?>
								<div id="gotech_result_footer_buttons_payment">
									<a class="gotech_blue_button" href="<?=$link?>" id="pay_link" target="_blank">
										<?=GetMessage("PAY")?>
									</a>
								</div>
							<?endif;?>
						<?endif;?>
					<?endif;?>
				</div>
			</div>			
		</form>
	<?endif;?>
</div>
<div id="gotech_annulation_dialog">
	<?if($arResult["DO_ANNULATION"]):?>
		<span id="gotech_annulation_text_header"><?=GetMessage("You_shure")?></span>
		<div class="gotech_cancel_button">
			<a href="#" id="cancel_yes" class="gotech_yellow_button" title="<?=GetMessage("YES")?>"><span><?=GetMessage("YES")?></span></a>
			<a href="#" id="cancel_no" class="gotech_blue_button" title="<?=GetMessage("NO")?>"><span><?=GetMessage("NO")?></span></a>
		</div>
	<?else:?>
		<?if(empty($arResult["BOOKING_HOTEL"]["HOTEL_PHONE"])):?>
			<span id="gotech_annulation_text"><?=GetMessage("CANNOT_ANNULATION")?></span>
		<?else:?>
			<span id="gotech_annulation_text"><?=GetMessage("CANNOT_ANNULATION_WITH_PHONE")." ".$arResult["BOOKING_HOTEL"]["HOTEL_PHONE"]?></span>
		<?endif;?>
		<div class="gotech_cancel_button">
			<a href="#" id="cancel_no" class="gotech_blue_button" title="<?=GetMessage("OK")?>"><span><?=GetMessage("OK")?></span></a>
		</div>
	<?endif;?>
</div>
<script>
	$(function(){
		$('input[name="gotech_result_footer_payment_methods_radiobuttons"]:checked').change();
		$('input[name="gotech_result_footer_payment_methods_radiobuttons"]:checked').click();
		$("input[name='customer_search']").suggestions({
	        token: "39d56491dac45396522f98c4958f0c16ab61152b",
	        type: "PARTY",
        	count: 5,
	        /* Вызывается, когда пользователь выбирает одну из подсказок */
	        onSelect: function(suggestion) {
	            console.log(suggestion);
	            if(suggestion){
	            	var $this = $(this);
	            	$this.parent().find('input[name="customer_description"]').val(suggestion.value)
	            	$this.parent().find('input[name="customer_address"]').val(suggestion.data.address.value)
	            	$this.parent().find('input[name="customer_tin"]').val(suggestion.data.inn)
	            	$this.parent().find('input[name="customer_kpp"]').val(suggestion.data.kpp)
	            }
	        }
	    });
	});
	$('.gotech_result_footer_payment_methods_customer_data label>input').on('input', function(e) {
		$('a#pay_link').removeClass('hide_button');
		$('.gotech_result_footer_payment_methods_customer_data label>input').each(function(){
			if($(this).val().length == 0 && $(this).prop('name') != 'customer_phone'){
				$('a#pay_link').addClass('hide_button');
				return false;
			}else if($(this).prop('name') == 'customer_email'){
				var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
				if(!regex.test($(this).val())){
					$('a#pay_link').addClass('hide_button');
					return false;
				}
			}
		});
	});
	$("a#pay_link").on('click', function(e) {
		e.preventDefault();
		var old_link = $('a#pay_link').prop('href');
		var id = $('input[name="gotech_result_footer_payment_methods_radiobuttons"]:checked').prop("id");
		if(id != 'undefined'){
			var parameters = id.split("-");
			var new_link = old_link+"&pay_sys="+parameters[0]+"&out_summ="+(parseInt(parameters[1])/100);
			<?if($method["IS_LEGAL"]):?>
				if($('.gotech_result_footer_payment_methods_customer_data').is(':visible')){
					var wrapper = $('.gotech_result_footer_payment_methods_customer_data:visible');
					new_link += "&customer_description="+wrapper.find('input[name="customer_description"]').val()+"&customer_address="+wrapper.find('input[name="customer_address"]').val()+"&customer_email="+wrapper.find('input[name="customer_email"]').val()+"&customer_phone="+wrapper.find('input[name="customer_phone"]').val()+"&customer_kpp="+wrapper.find('input[name="customer_kpp"]').val()+"&customer_tin="+wrapper.find('input[name="customer_tin"]').val();
					wrapper.hide();
				}
			<?endif;?>
            <?if (SID !== null && SID):?>
                new_link = new_link + "&<?=SID?>";
            <?endif;?>
			$('a#pay_link').prop('href', new_link);
			window.location.replace(new_link);
		}
	});
	$('input[name="gotech_result_footer_payment_methods_radiobuttons"]').on('change', function(e) {
		var id = $('input[name="gotech_result_footer_payment_methods_radiobuttons"]:checked').prop("id");
		if(id != 'undefined'){
			var parameters = id.split("-");
			var is_cash = parameters[2];
			var is_receipt = parameters[3];
			var is_legal = parameters[4];
			if(is_cash=='1'){
				$('a#pay_link').hide();
			}else{
				$('a#pay_link').show();
			}
			if(is_receipt=='1'){
				$('a#pay_link').html("<?=GetMessage("RECEIPT")?>");
			}else if(is_legal=='1'){
				$('a#pay_link').html("<?=GetMessage("SEND")?>");
			}else{
				$('a#pay_link').html("<?=GetMessage("PAY")?>");
			}
			if(is_legal=='1'){
				<?if(empty($arResult["info_order"]["CustomerName"]) || empty($arResult["info_order"]["CustomerEMail"]) || empty($arResult["info_order"]["CustomerTIN"])):?>
				$('a#pay_link').addClass('hide_button');
				<?endif;?>
				showCustomerData(this, false);
			}else{
				$('a#pay_link').removeClass('hide_button');
				showCustomerData(this, true);
			}
		}
	});
	function showCustomerData(element, hide) {
		$('.gotech_result_footer_payment_methods_customer_data').hide()
		if(hide){

		}else{
			$(element).parent().next().show();
		}
	}
	$('#gotech_annulation_dialog').dialog({
		title: "<?=GetMessage("Cancel_reservation")?>",
		dialogClass: 'gotech_hotelonline',
		autoOpen: false,
		width: 295,
		height: 120,
		resizable: false,
		draggable: false,
		modal: true,
		position: "center",
		closeText: ''
	});
	$('body').on('click', 'div.ui-widget-overlay.ui-front', function(e){
		$('#gotech_annulation_dialog').dialog("close");
	});
	$('a#cancel').on('click', function(e) {
		e.preventDefault();
		$('#gotech_annulation_dialog').dialog("open");
		$("a#cancel_yes").on("click", function(e) {
			e.preventDefault();
			$('input[name="cancel"]').val("Y");
			$('form[name="gotech_result_form"]').submit();			
		});
		$("a#cancel_no").on("click", function(e) {
			e.preventDefault();
			$('#gotech_annulation_dialog').dialog("close");		
		});
	});
</script>