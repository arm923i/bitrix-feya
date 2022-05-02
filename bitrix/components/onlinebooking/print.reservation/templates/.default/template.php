<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?__IncludeLang($_SERVER["DOCUMENT_ROOT"].$this->__folder."/lang/".OnlineBookingSupport::getLanguage()."/template.php");?>

<?if(empty($arResult["ERROR"])):?>
	<div class="date"><?=$arResult["today"]?></div>	
	<h2><?=GetMessage("GUEST_GROUP");?><?=$arResult["GUEST_GROUP"]?></h2>
	<p><?=GetMessage("HOTEL");?><?=$arResult["HOTEL_NAME"]?></p>
	<table>
		<tbody>
			<?foreach($arResult["GUESTS"] as $key => $guest):?>
				<tr>
					<td><h3><?=$key?></h3></td>              
				</tr>
				<tr>
					<td><b><?=GetMessage("GUESTS")?></b><?=count($guest)?></td>              
				</tr>			
					<?foreach($guest as $g):?>
						<tr>
							<td>
								<b><?=$g['GuestFullName']?></b><br />				
								<?=GetMessage("ROOM_TYPE");?> <?=$g["RoomType"]?><br />
								<?=GetMessage("ACCOMODATION_TYPE");?> <?=$g["AccommodationType"]?><br />
								<?=GetMessage("SUM");?> <?=$g["Sum"].' '.$g["Currency"]?>
							</td>
						</tr> 
					<?endforeach;?>				
			<?endforeach;?>	
		</tbody>
	</table>
	<div class="itogo"><b><?=GetMessage("ITOGO")?></b> <?=$arResult["TOTAL_SUM"]?></div>
	<h2><?=GetMessage("CONTACT_PERSON")?></h2>
	<div class="user-contacts">
		<b><?=$arResult["CONTACT_PERSON"]?></b><br/>
		<?if(!empty($arResult["CONTACT_PERSON_PHONE"])):?>
			<b><?=GetMessage("PHONE")?> </b><?=$arResult["CONTACT_PERSON_PHONE"]?><br/>
		<?endif;?>
		<?if(!empty($arResult["CONTACT_PERSON_FAX"])):?>
			<b><?=GetMessage("FAX")?> </b><?=$arResult["CONTACT_PERSON_FAX"]?><br/>
		<?endif;?>
		<?if(!empty($arResult["CONTACT_PERSON_EMAIL"])):?>
			<b><?=GetMessage("EMAIL")?> </b><?=$arResult["CONTACT_PERSON_EMAIL"]?><br/>
		<?endif;?>
	</div>
<?else:?>
	<p><b><?=GetMessage("RESULT")?> </b><?=$arResult["ERROR"]?></p>
<?endif;?>