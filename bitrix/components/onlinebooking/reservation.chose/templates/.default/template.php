<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?__IncludeLang($_SERVER["DOCUMENT_ROOT"].$this->__folder."/lang/".$arResult["LANG"]."/template.php");?>
<?

if(!function_exists('plural_form'))
{
	function plural_form($number, $after) {
	  $cases = array (2, 0, 1, 1, 1, 2);
	  echo $number.' '.$after[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
	}
}	
?>
<?if(($arParams["TYPE"] != "SERVICE" && !empty($arResult["NUMBERS_BOOKING"])) || ($arParams["TYPE"] == "SERVICE" && !empty($arResult["SERVICES_BOOKING"]))):?>
	<script>
		$('#gotech_search_choose').show();
	</script>
	<input type="hidden" name="lang" value="<?=$arResult["LANG"]?>" />
	<input type="hidden" name="hotel_id" value="<?=$arParams["ID_HOTEL"]?>" />
	<input type="hidden" name="language" value="<?=$arResult["LANG"]?>" />

	<?if($arParams["TYPE"] != "SERVICE"):?>
		<?/*
		<div id="gotech_search_choose_header">
			<div id="gotech_search_choose_header_text" class="gotech_middle_text"><?=GetMessage("YOU_CHOOSE")?></div>
			<span id="gotech_one_more_room" class="gotech_header_text"><span class="gotech_header_text_underline"><?=GetMessage("ADD_ONE_MORE_ROOM")?></span></span>
		</div>
		*/?>
		<?$total = 0;?>
		<?$guests = 0;?>
		<?$rooms = 0;?>
		<div id="gotech_right_basket">
			<?=GetMessage("CHOSED")?>:
		<?foreach($arResult["NUMBERS_BOOKING"] as $key => $book):?>
			<?
			$nights = strtotime($book['PeriodTo']) - strtotime($book['PeriodFrom']);
			if($arResult['HOURS_ENABLE']):
				$nights = $nights/(60*60);
			else:
				$nights = $nights/(60*60*24);
			endif;
			
			$book_id = $book['RoomTypeCode'].'_'.$book['RoomRateCode'].'_'.$book['PeriodFrom'].'_'.$book['PeriodTo'].'_'.$book['visitors'];
			
			?>
			<div class="cart_item" data-id="<?=$book['Id']?>" data-unique="<?=$book['unique']?>" data-book_id="<?=$book_id?>">
				<div class="name_price">
					<?=$book['RoomName']?> <span class="razd">|</span> <?=number_format($book['Amount'],0,'.',' ')?> <?=$book['Currency']?> 
					<a href="#" class="delete_order" onclick="delete_room_from_cart(<?=$book['Id']?>,<?=$book['unique']?>);parent.postMessage('refreshBasket','*');return false;" style="float:none;padding:0;position:relative;top:-1px;"></a>
				</div>
				<div class="nights">
					<?for($i=0;$i<$book['visitors'];$i++):?>
						<span class="visitor_small"></span>
					<?endfor;?>
					<?if($arResult['HOURS_ENABLE']):?>
						<b><?plural_form($nights,array(GetMessage("1HOUR"),GetMessage("2HOURS"),GetMessage("5HOURS")))?></b>
					<?else:?>
						<b><?plural_form($nights,array(GetMessage("1NIGHT"),GetMessage("2NIGHTS"),GetMessage("5NIGHTS")))?></b>
					<?endif;?>
					(<?=date('d.m',strtotime($book['PeriodFrom']))?> - <?=date('d.m',strtotime($book['PeriodTo']))?>) 
					
				</div>
				
			</div>
			
			<?$total += $book["Amount"]?>
			<?$guests += $book["visitors"]?>
			<?$rooms += 1?>
		<?endforeach;?>
		
		<span id="gotech_search_choose_footer_button" style="display:inline-block;">
			<a class="gotech_button" href="#">
				<?=GetMessage("BOOKING")?>
			</a>
		</span>
		</div>
		
		<div id="gotech_search_choose_footer">
			<div id="gotech_search_choose_footer_line">
				<?
					$roomText = "";
					if($rooms == 1){
						$roomText = GetMessage("1ROOM");
					}elseif($rooms >= 2 && $rooms <= 4){
						$roomText = GetMessage("2ROOMS");
					}elseif($rooms <= 20){
						$roomText = GetMessage("5ROOMS");
					}elseif($rooms%10 == 1){
						$roomText = GetMessage("1ROOM");
					}elseif($rooms%10 >= 2 && $rooms%10 <= 4){
						$roomText = GetMessage("2ROOMS");
					}else{
						$roomText = GetMessage("5ROOMS");
					}
					
					$guestText = "";
					if($guests == 1){
						$guestText = GetMessage("2GUEST");
					}elseif($guests >= 2 && $guests <= 4){
						$guestText = GetMessage("5GUESTS");
					}elseif($guests <= 20){
						$guestText = GetMessage("5GUESTS");
					}elseif($guests%10 == 1){
						$guestText = GetMessage("2GUEST");
					}elseif($guests%10 >= 2 && $guests%10 <= 4){
						$guestText = GetMessage("5GUESTS");
					}else{
						$guestText = GetMessage("5GUESTS");
					}
				?>
				<span class="gotech_search_choose_footer_line_sum_value">
					<?=GetMessage("CHOSED")?>:
					<b><?=$rooms?> <?=$roomText?></b>
					<span> | </span>
					<b><?=number_format($total, 2, ',', '&nbsp;')."&nbsp;".$book["Currency"]?></b>
					
					<span id="gotech_search_choose_footer_button" style="display:inline-block;">
						<a class="gotech_button" href="#">
							<?=GetMessage("BOOKING")?>
						</a>
					</span>
				</span>
			
			
			</div>	
			<input type="hidden" name="gotech_search_choose_footer_button_link" value="<?=COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')?>?booking=yes&hotel=<?=$arParams["ID_HOTEL"]?>&lang=<?=$arResult["LANG"]?>">
		</div>
	<?else:?>
		<div id="gotech_guests_information_services_header"><?=GetMessage("ADDITIONAL_SERVICES")?></div>
		<?		//var_dump($arParams);	
			$totalServiceSum = 0;
			$totalServiceSumDesc = "";
			$currency = "";
		?>
		<?foreach($arResult["SERVICES_BOOKING"] as $key => $book):?>
			<?$currency = $book["Currency"];?>
			<?if(empty($book["Price"]) || $book["Price"] == "0"){
				$servicePrice = GetMessage("FREE");
				$book["Price"] = 0;
			}else{
				$totalServiceSum += FloatVal($book["Price"]);
				$totalServiceSumDesc = strval(number_format($totalServiceSum, 2, ',', '&nbsp;'))."&nbsp;".$book["Currency"];
				$servicePrice = number_format($book["Price"], 2, ',', '&nbsp;')."&nbsp;".$book["Currency"];
			}?>
			<div class="gotech_guests_information_services_item">
				<span class="gotech_guests_information_services_item_service"><?=$book["Name"]?></span>
				<span id="service_guest_<?=$book["GuestID"]?>" class="gotech_guests_information_services_item_guest service_guest_<?=$book["GuestID"]?>"></span>
				<span class="gotech_guests_information_services_item_delete_icon">&times;</span>
				<span class="gotech_guests_information_services_item_price"><?=$servicePrice?></span>
				<input class="Amount" type="hidden" name="Amount" value="<?=$book["Price"]?>" />
				<input type="hidden" name="id" value="<?=$book["cId"]?>" />
				<input type="hidden" name="Currency" value="<?=$book["Currency"]?>" />
				<input type="hidden" name="Code" value="<?=$book["Code"]?>" />
			</div>
		<?endforeach;?>
		<script>
			$(function(){
				var $totalPriceField = $('.total_price');
				var $totalPriceInput = $('[name="total_sum"]');
				var $totalPaymentPriceFields = $('.payment_price');
				if($totalPriceField.length > 0 && $totalPriceInput.length > 0){
					$totalPriceField.html(number_format(parseFloat('<?=$totalServiceSum?>')+parseFloat($totalPriceInput.val()), 2, ',', '&nbsp;')+"&nbsp;<?=$currency?>");
				}
				if($totalPaymentPriceFields.length > 0){
					$totalPaymentPriceFields.each(function(){
						var $this = $(this);
						var $parent = $this.parent().parent();
						var $payment_discount = $parent.find('[name="payment_discount"]');
						var $payment_methods_radiobuttons = $parent.find('[name="payment_methods_radiobuttons"]');
						var $is_first_night = $parent.find('[name="is_first_night"]');
						if($is_first_night.val() != "Y"){
							var total_sum_desc = number_format(parseFloat('<?=$totalServiceSum?>')+parseFloat($totalPriceInput.val()), 2, ',', '&nbsp;')+"&nbsp;<?=$currency?>";
							var total_sum = parseFloat('<?=$totalServiceSum?>')+parseFloat($totalPriceInput.val());
							if($payment_discount.length > 0){
								if($payment_discount.val() && $payment_discount.val() != 100){
									total_sum_desc = number_format((parseFloat('<?=$totalServiceSum?>')+parseFloat($totalPriceInput.val()))*parseFloat($payment_discount.val())/100, 2, ',', '&nbsp;')+"&nbsp;<?=$currency?>";
									total_sum = (parseFloat('<?=$totalServiceSum?>')+parseFloat($totalPriceInput.val()))*parseFloat($payment_discount.val())/100;
								}
							}
							if($payment_methods_radiobuttons.length > 0){						
								var id = $payment_methods_radiobuttons.prop('id');
								var idArray = id.split('-');
								idArray[1] = total_sum*100;
								$payment_methods_radiobuttons.prop('id', idArray.join('-'));
								$payment_methods_radiobuttons.val(idArray.join('-'));
							}
							$this.html(total_sum_desc);
						}
					});
				}
				$('.gotech_guests_information_services_item').each(function() {
					$(this).find('.gotech_guests_information_services_item_guest').each(function(){
						var id = $(this).prop("id");
						var guest_id = id.substr(14, id.length+1);
						var splits = guest_id.split("_");
						var surname = $('input[name="surname_'+guest_id+'"]').val();
						var name = $('input[name="name_'+guest_id+'"]').val();
						var secondname = $('input[name="secondName_'+guest_id+'"]').val();
						var fullname = '';
						if(!!surname){
							fullname += surname;
						}
						if(!!name){
							fullname += " "+name;
						}
						if(!!secondname){
							fullname += " "+secondname;
						}
						fullname = fullname.trim();
						if(!!fullname && fullname != 'undefined'){
							$(this).html((parseInt(splits[1])+1)+". "+fullname);
						}else{
							$(this).html((parseInt(splits[1])+1)+". "+$('input[name="surname_'+guest_id+'"]').prop("placeholder"));
						}
					});
				});
				
				
				
				
			});
		</script>
	<?endif;?>
<?else:?>
	
<?endif;?>
<script>
	$('#gotech_right_basket #gotech_search_choose_footer_button a').click(function() {
		
		$(this).after('<img src="/bitrix/js/onlinebooking/new/icons/progress.gif" width="100" />');
		$(this).hide();
	});
	
	
	
	
	
	
</script>