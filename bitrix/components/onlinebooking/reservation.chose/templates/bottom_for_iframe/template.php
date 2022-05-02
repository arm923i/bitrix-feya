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
<?if(($arParams["TYPE"] != "SERVICE" /*&& !empty($arResult["NUMBERS_BOOKING"])*/) || ($arParams["TYPE"] == "SERVICE" /*&& !empty($arResult["SERVICES_BOOKING"])*/)):?>
	
	<div id="gotech_search_choose_iframe" style="display:block !important;">

		<script>
			$('#gotech_search_choose').show();
		</script>
		<input type="hidden" name="lang" value="<?=$arResult["LANG"]?>" />
		<input type="hidden" name="hotel_id" value="<?=$arParams["ID_HOTEL"]?>" />
		<input type="hidden" name="language" value="<?=$arResult["LANG"]?>" />

		<?if($arParams["TYPE"] != "SERVICE"):?>

			<?$total = 0;?>
			<?$guests = 0;?>
			<?$rooms = 0;?>
			<?foreach($arResult["NUMBERS_BOOKING"] as $key => $book):?>
				<?
				$nights = strtotime($book['PeriodTo']) - strtotime($book['PeriodFrom']);
				$nights = $nights/(60*60*24);
				
				$book_id = $book['RoomTypeCode'].'_'.$book['RoomRateCode'].'_'.$book['PeriodFrom'].'_'.$book['PeriodTo'].'_'.$book['visitors'];
				
				?>

				
				<?$total += $book["Amount"]?>
				<?$guests += $book["visitors"]?>
				<?$rooms += 1?>
			<?endforeach;?>
			
			
			<div id="gotech_search_choose_footer" style="display:block !important;">
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
			
				<script>
				
					$('#gotech_search_choose_footer_button').click(function() {
						
						parent.postMessage('toBasket___'+$('[name=gotech_search_choose_footer_button_link]').val(),'*');
						
					});
				
				</script>
			
			</div>
		
		<?else:?>
		
		<?endif;?>
	</div>	
<?else:?>
	
<?endif;?>
