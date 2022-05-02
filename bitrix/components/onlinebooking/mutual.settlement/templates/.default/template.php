<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?__IncludeLang($_SERVER["DOCUMENT_ROOT"].$this->__folder."/lang/".OnlineBookingSupport::getLanguage()."/template.php");?>
<div id="wrapper">
	<?if(!empty($arResult["RESULT"])):?>
		<script>
			$("#gotech_header_history")[0].style.display="none";
		</script>
		<div class="page-title clear">
			<a class="button grey" href="history.php">
				<span>&lt;&nbsp;&nbsp;<?=GetMessage("MY_RESERVATIONS")?></span>
			</a>
			<?if($arResult["SUCCESS"]):?>
				<p><?=GetMessage("RESULT");?></p>
			<?else:?>
				<p><?=$arResult["RESULT"]?></p>
			<?endif;?>
		</div>
	<?else:?>
		<div id="mutual_header" class="gotech_middle_text">
			<?=GetMessage("FILL_PERIOD");?>
		</div>
		<div id="mutual_dates">
			<form method="get" action="<?=$APPLICATION->GetCurPageParam();?>" name="send_report">
				<div id="mutual_dates_header">
					<?=GetMessage("PERIOD")?>
				</div>
				<div id="mutual_dates_content">
					<span id="mutual_dates_content_date_from_container">
						<input id="mutual_dates_content_date_from_input" readonly/>
						<input id="mutual_dates_content_date_from" name="DateFrom" readonly/>
					</span>
					<span>&nbsp;-&nbsp;</span>
					<span id="mutual_dates_content_date_to_container">
						<input id="mutual_dates_content_date_to_input" readonly/>
						<input id="mutual_dates_content_date_to" name="DateTo" readonly/>
					</span>
					<div id="mutual_dates_content_button_get_order">
						<a class="gotech_blue_button" href="#" id="mutual_get_order">
							<?=GetMessage("SEND")?>
						</a>
					</div>
				</div>
			</form>
		</div>
	<?endif;?>
</div>
<script type="text/javascript">
	$('body').on('click', "a#mutual_get_order", function(e) {
		e.preventDefault();
		var noFill = false;
		$('input[name*="DateFrom"]').each(function(index, element){
			console.log($(element));
			console.log($(element).val());
			if($(element).val().trim() == "")
				noFill = true;
		});
		$('input[name*="DateTo"]').each(function(index, element){
			console.log($(element));
			console.log($(element).val());
			if($(element).val().trim() == "")
				noFill = true;
		});
		if(!noFill){
			$('form[name="send_report"]').submit();
		}else{
			alert("<?=GetMessage("FIELD_IS_NOT_FILLED")?>");
		}
		return false;
	});
</script>