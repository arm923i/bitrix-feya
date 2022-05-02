<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?__IncludeLang($_SERVER["DOCUMENT_ROOT"].$this->__folder."/lang/".OnlineBookingSupport::getLanguage()."/template.php");?>
<?
if(!function_exists('plural_form'))
{
	function plural_form($number, $after) {
	  $cases = array (2, 0, 1, 1, 1, 2);
	  echo $number.' '.$after[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
	}
}
$path = COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER');
$gotech_lang = htmlspecialchars($_REQUEST['lang']);
$pathwl = $path."?lang=$gotech_lang";
if (SID !== null && SID){
    $pathwl = $pathwl."&".SID;
}
?>
<div id="gotech_booking_agent">

	<div class="header">
		
		<a href="<?=$pathwl?>" class="gotech_button add_new_order">
			<span>+</span>
			<?=GetMessage('ADD_NEW_ORDER')?>
		</a>
		<div class="gotech_booking_agent_header">
			<?=GetMessage('ALL_ORDERS')?>
		</div>
		
	</div>
	<div class="gotech_booking_agent_content">
		<div class="line"></div>
		<table border="0" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th class="bnumber">№ <span><?=GetMessage('BRONI')?></span></th>
					<th class="type"><?=GetMessage('TYPE')?></th>
					<th class="dates"><?=GetMessage('ALL_ORDERS')?></th>
					<th class="bguests"><?=GetMessage('GUEST')?></th>
					<th class="bprice"><?=GetMessage('COST')?></th>
					<th class="to_pay"><?=GetMessage('LEFT_TO_PAY')?></th>
					<th class="status"><?=GetMessage('PAY_STATUS')?></th>
				</tr>
			</thead>
			<tbody>
				<?if(count($arResult['BOOKING'])):?>
					<?foreach($arResult['BOOKING'] as $k => $book):?>
						<tr>
							<td class="bnumber" style="cursor:pointer" onclick="location.href='my.php?hotel_code=&hotel=<?=$arResult['HOTEL_ID']?>&reservation=<?=$book['Id']?>&data=<?=$book['Email']?>&search=Y&cancel=N&is_change=N&is_change_room=0';">
								<?=$book['Id']?>
							</td>
							<td class="type"><?=GetMessage($book['Status'])?></td>
							<td class="dates">
								<span class="in"><?=$book['PeriodFrom']?></span>
								<span class="out"><?=$book['PeriodTo']?></span>
								<span class="nights"><?plural_form($book['Nights'],array(GetMessage('1NIGHT'),GetMessage('2NIGHTS'),GetMessage('5NIGHTS')));?></span>
							</td>
							<td class="bguests">
								<span class="guests"><?=GetMessage('GUESTS')?>: <?=$book['Guests']?></span>
								<span class="guest"><?=$book['FullName']?></span>
							</td>
							<td class="bprice"><?=OnlineBookingSupport::format_price($book['Amount'], $arResult["CURRENCY_NAME"])?></td>
							<td class="to_pay"><?=OnlineBookingSupport::format_price($book['Balance'], $arResult["CURRENCY_NAME"])?></td>
							<td class="status">
								<?if($book['Amount'] - $book['Balance'] > 0 && $book['Balance']):?>
									<span class="part_payed"><span><?=GetMessage('PART_PAY')?></span></span>
								<?elseif(!$book['Balance'] && $book['Amount']>0):?>
									<span class="payed"><span><?=GetMessage('PAYED')?></span></span>
								<?elseif($book['Amount'] - $book['Balance'] <= 0):?>
									<span class="not_payed"><span><?=GetMessage('NOT_PAYED')?></span></span>
								<?endif;?>
							</td>
						</tr>
					<?endforeach;?>
				<?endif;?>
			</tbody>
		</table>
	</div>
</div>