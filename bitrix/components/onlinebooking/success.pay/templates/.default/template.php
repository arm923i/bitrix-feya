<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(isset($arResult["UUID"]) && !empty($arResult["UUID"])):?>
	<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
	<script src="/bitrix/js/onlinebooking/new/handler.js"></script>
	<div id="gotech_online_booking">
		<?$APPLICATION->IncludeComponent("onlinebooking:my.reservation", ".default", array("UUID" => $arResult["UUID"], "HOTEL_CODE" => $arResult["HOTEL"]));?>
	</div>
	<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?else:?>
	<?__IncludeLang($_SERVER["DOCUMENT_ROOT"].$this->__folder."/lang/".OnlineBookingSupport::getLanguage()."/template.php");?>
	<div class="clear"></div>
	<div id="wrapper">
		<p><?=$arResult["SUCCESS"]?></p>
		<div class="b-button">
			<a href="<?=COption::GetOptionString('gotech.hotelonline', 'PATH_TO_FOLDER')?>" class="button">
				<span>
					<i class="icons-search"></i>
					<?=GetMessage("SEARCH")?>
				</span>
			</a>
			<div class="clear"></div>
		</div>
	</div>
<?endif;?>