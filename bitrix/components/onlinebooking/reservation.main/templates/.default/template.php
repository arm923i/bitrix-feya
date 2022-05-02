<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?__IncludeLang($_SERVER["DOCUMENT_ROOT"].$this->__folder."/lang/".OnlineBookingSupport::getLanguage()."/template.php");?>
<?if(!empty($arResult)):?>
	<p><?=GetMessage("CHOOSE_HOTELS")?></p>
	<?foreach($arResult["HOTELS"] as $hotel):?>
		<p>
			<a href="<?=$hotel["HREF"]?>" title="<?=$hotel["NAME"]?>"><?=$hotel["NAME"]?></a>
		</p>
	<?endforeach;?>
<?endif;?>