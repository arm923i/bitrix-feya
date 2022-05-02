<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?__IncludeLang($_SERVER["DOCUMENT_ROOT"].$this->__folder."/lang/".OnlineBookingSupport::getLanguage()."/template.php");?>
<div id="wrapper">	
	<?if(!empty($arResult["ERROR"])):?>
		<?=ShowError($arResult["ERROR"]);?>
	<?endif;?>
	<?if(empty($arResult["ERROR"]) && !empty($arResult) && !empty($arResult["RESULT"])):?>
	
	<div id="my_bron_form" class="page_header search_booking">
		<div class="h">
			<?=GetMessage('REGISTRATION')?>
		</div>
		<div id="gotech_search_booking_content">
					
			<p><?=GetMessage('TEXT1');?></p>

			<div class="error_auth" style="display: none;margin-bottom: 15px;color: red;"></div>
			
			<form class="respass_form" name="respass_form" action="<?=$arResult["AUTH_URL"]?>">
				<input type="hidden" name="restore_password" value="Y"/>
				<input type="hidden" name="AUTH_FORM" value="Y"/>
				<input type="hidden" name="TYPE" value="SEND_PWD"/>
				
				<div class="param_block">
					<label class="pblock_label"><span><?=GetMessage("NEW_PASS")?></span></label>
					<input type="password" id="gotech_agent_dialog_password_input_1" required="" name="password1" placeholder="<?=GetMessage("NEW_PASS")?>"  onInput="checkPass()" onkeyup="submit_auth();">
				</div>
				
				<div class="param_block">
					<label class="pblock_label"><span><?=GetMessage("NEW_PASS2")?></span></label>
					<input type="password" id="gotech_agent_dialog_password_input_2"  required="" name="password2" placeholder="<?=GetMessage("NEW_PASS2")?>"  onInput="checkPass()" onkeyup="submit_auth();">
				</div>
				
			</form>
			<br><br>
			<div class="gotech_send_auth_button">
				<a class="gotech_button" href="#" id="gotech_send_auth">
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
			send_auth(event);
		} 
	}
	
	function checkPass(event){
		event = event || window.event;
		var pass1 = $("input[name='password1']");
		var pass2 = $("input[name='password2']");
		if(pass1.val().length>=6)
			$('#icon_pass1').css('opacity', '1');
		else
			$('#icon_pass1').css('opacity', '0');
		if(pass2.val().length>0 && pass2.val() == pass1.val())
			$('#icon_pass2').css('opacity', '1');
		else
			$('#icon_pass2').css('opacity', '0');
	}

	$('a#gotech_send_auth').on('click', send_auth);
	
	function send_auth(e){
		e.preventDefault();
		var pass1val = $('input[name="password1"]').val();
		var pass2val = $('input[name="password2"]').val();
		if(pass1val.length>=6)
			if(pass1val == pass2val){
				var ajax_data = {USER_NAME:"<?=htmlspecialchars($arResult["RESULT"]->CustomerDescription)?>", USER_LOGIN:"<?=htmlspecialchars($_REQUEST["email"])?>", USER_PASSWORD:pass1val};
				$.post('/bitrix/components/onlinebooking/agent.registration/ajax.php', ajax_data,  function(data){
					if(data)
						$('.error_auth').each(function() {
							$(this).show();
							$(this).html(data);
						});
					else
						location.replace("index.php");
				});
			}else
				$('.error_auth').each(function() {
					$(this).show();
					$(this).html("<?=GetMessage("ERROR_LOGIN")?>");
				});
		else
			$('.error_auth').each(function() {
				$(this).show();
				$(this).html("<?=GetMessage("ERROR_PASS")?>");
			});
	}
</script>