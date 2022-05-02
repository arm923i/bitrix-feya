<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?__IncludeLang($_SERVER["DOCUMENT_ROOT"].$this->__folder."/lang/".OnlineBookingSupport::getLanguage()."/template.php");?>
<? if($_REQUEST["fpssw"] == "N") 
{ 
	$APPLICATION->arAuthResult['TYPE'] = "ERROR";
	$APPLICATION->arAuthResult['MESSAGE'] = GetMessage("USER_NOT_FOUND");
	$new_url = explode("fpssw", $arResult["AUTH_URL"]);
	$arResult["AUTH_URL"] = $new_url[0];
}
?>
<div id="wrapper">	
	<?if(!empty($arResult)):?>
	
	<div id="my_bron_form" class="page_header search_booking">
		<div class="h">
			<?=GetMessage('PASSWORD_RECOVERY')?>
		</div>
		<div id="gotech_search_booking_content">
		
			<?if($APPLICATION->arAuthResult['TYPE'] == 'OK' || $APPLICATION->arAuthResult['TYPE'] == 'ERROR'):?>
				<p style="font-weight:bold;"><?=$APPLICATION->arAuthResult['MESSAGE']?></p>
			<?endif;?>
			
			<p><?=GetMessage('AUTH_FORGOT_PASSWORD');?>
			<br/>
			<?=GetMessage('AUTH_FORGOT_PASSWORD2');?></p>	
			
			<form class="respass_form" name="respass_form" action="<?=$arResult["AUTH_URL"]?>">
				<input type="hidden" name="restore_password" value="Y"/>
				<input type="hidden" name="AUTH_FORM" value="Y"/>
				<input type="hidden" name="TYPE" value="SEND_PWD"/>
				
				<div class="param_block">
					<label class="pblock_label"><span><?=GetMessage("USER_LOGIN")?></span></label>
					<input required="" name="USER_LOGIN" placeholder="<?=GetMessage("USER_LOGIN")?>">
				</div>
				<div class="or">
					или
				</div>
				<div class="param_block">
					<label class="pblock_label"><span><?=GetMessage("USER_EMAIL")?></span></label>
					<input required="" name="USER_EMAIL" placeholder="<?=GetMessage("USER_EMAIL")?>">
				</div>
				
			</form>
			<br><br>
			<div class="gotech_send_auth_button">
				<a class="gotech_button" href="#" id="gotech_restore_pass">
					<?=GetMessage("APPLY")?>			
				</a>
			</div>
		</div>
	</div>	
	
	<?endif;?>
</div>

<script type="text/javascript">
	function submit_auth(event){
		event = event || window.event;
		if ((event.keyCode == 0xA)||(event.keyCode == 0xD))
		{
			restore_pass(event);
		} 
	}

	$('a#gotech_restore_pass').on('click', restore_pass);
	
	function restore_pass(e){
		e.preventDefault();
		$('form[name="respass_form"]').submit();
	}
</script>