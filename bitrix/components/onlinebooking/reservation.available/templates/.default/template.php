<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?__IncludeLang($_SERVER["DOCUMENT_ROOT"].$this->__folder."/lang/".$arResult["language"]."/template.php");?>
<?if ($arResult['ShowCalendar']):?>
<input type="hidden" name="CalendarPeriodFrom">
<input type="hidden" name="CalendarPeriodTo">
<div class="calendar_text"><?=GetMessage("CALENDAR_TEXT")?></div>
<div class="calendar" id="calendar">

</div>

<script>
$(function(){
	var periodFrom = new Date("<?=$arResult['PeriodFrom']?>");
	var periodTo = new Date("<?=$arResult['PeriodTo']?>");
	roomsArray = {};
	periodsArray = {};
	<?$lastRT = ""?>
	<?$minPeriodFrom = strtotime("+1 year")?>
	<?$index = 0?>
	<?foreach($arResult["AvailableRows"] as $key=>$arr):?>
		<?if($arr->RoomsVacant > 0 || $arr->RoomsRemains > 0):?>
			<?
				$ar_date = explode("T", $arr->Period);
				$date_new_ar = explode("-", $ar_date[0]);
				$period = $date_new_ar[2].".".$date_new_ar[1].".".$date_new_ar[0];
				if(strtotime($period) < $minPeriodFrom) {
					$minPeriodFrom = strtotime($period);
				}
				$filterPeriod = $date_new_ar[2].$date_new_ar[1].$date_new_ar[0];
			?>
			<?if($lastRT != $arr->RoomTypeCode):?>
				if(typeof roomsArray.<?=$arr->RoomTypeCode?> == 'undefined'){
					roomsArray.<?=$arr->RoomTypeCode?> = [];
				}
				<?$lastRT = $arr->RoomTypeCode?>
				<?$index = 0?>
			<?endif;?>
			<?
			$rooms = $arr->RoomsVacant;
			if(!$rooms) {
				$rooms = $arr->RoomsRemains;
			}
			?>
			roomsArray.<?=$arr->RoomTypeCode?>.push({period: "<?=$period?>", filterPeriod: "<?=$filterPeriod?>", rooms: "<?=$rooms?>", singlePrice: "<?=$arr->SinglePrice?>", doublePrice: "<?=$arr->DoublePrice?>", triplePrice: "<?=$arr->TriplePrice?>", additionalPrice: "<?=$arr->AdditionalBedPrice?>"});
			<?$index += 1?>
		<?endif;?>
	<?endforeach;?>

	init_calendar("<?=date('Y.m.d', $minPeriodFrom)?>", "<?=date('Y.m.d', $minPeriodFrom + 86400 * 365)?>");
	function init_calendar(pPeriodFrom, pPeriodTo) {
		var evObj = document.createEvent('MouseEvents');
		evObj.initMouseEvent('click', true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
		if(document.getElementById('calendar')) {	
			$.datepicker.regional['ru'] = {
				closeText: '�������',
				prevText: '&#8592;',
				nextText: '&#8594;',
				currentText: '�������',
				monthNames: ['������','�������','����','������','���','����',
				'����','������','��������','�������','������','�������'],
				monthNamesShort: ['���','���','���','���','���','���',
				'���','���','���','���','���','���'],
				dayNames: ['�����������','�����������','�������','�����','�������','�������','�������'],
				dayNamesShort: ['���','���','���','���','���','���','���'],
				dayNamesMin: ['��','��','��','��','��','��','��'],
				weekHeader: '���',
				dateFormat: 'dd.mm.yy',
				firstDay: 1,
				isRTL: false,
				showMonthAfterYear: false,
				yearSuffix: ''};
			$.datepicker.regional['en'] = {
				closeText: 'Done',
				prevText: '&#8592;',
				nextText: '&#8594;',
				currentText: 'Today',
				monthNames: ['January','February','March','April','May','June',
				'July','August','September','October','November','December'],
				monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
				'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
				dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
				dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
				dayNamesMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
				weekHeader: 'Wk',
				dateFormat: 'dd/mm/yy',
				firstDay: 1,
				isRTL: false,
				showMonthAfterYear: false,
				yearSuffix: ''};

			var dateFrom = new Date();
			var dateTo = new Date();
			dateTo.setTime(dateFrom.getTime());
			dateTo.setDate(dateFrom.getDate()+1);

			var minDateVal = 0;
			var maxDateVal = null;
			if(pPeriodFrom != undefined && pPeriodTo != undefined){
				minDateVal = new Date(pPeriodFrom);
				maxDateVal = new Date(pPeriodTo);
				dateFrom = new Date(minDateVal.getTime());
				dateTo = new Date(minDateVal.getTime());
				dateTo.setDate(dateFrom.getDate()+1);
			}else{
				$('input[name="CalendarPeriodFrom"]').val('');
				$('input[name="CalendarPeriodTo"]').val('');
			}
			var lang = $('input[name="language"]').val();
			var calendar = $('#calendar');	
			var lastFilledDate = new Date((new Date()).setMonth((new Date()).getMonth()+12));

			datepickerRange = {
				startDate:dateFrom, 
				endDate:dateTo, 
				currentDate: dateFrom, 
				selectCount:0,
				lastFilledDate: lastFilledDate,
				firstDisabledElement: null,
				checkDays: function(datepicker, step){
					step = step || 0;
					var self = this;
					if(this.startDate && this.endDate){
						if(step != 1 && this.startDate.getTime() == this.endDate.getTime())
							this.endDate.setDate(this.startDate.getDate()+1);
						if(step == 1){
							$('input[name="CalendarPeriodTo"]').val('');
						}
						var startDate = this.startDate;
						setTimeout(function() {
							fill_calendar();
							var next_elements_are_disabled = false;
							datepicker.dpDiv.find('.ui-datepicker-calendar').each(function(monthIndex){
								var calendar = $(this);
								var currMonth = datepicker.drawMonth+monthIndex;
								var currYear = datepicker.drawYear; 
								if (currMonth > 11) {
									currYear++;
									currMonth = datepicker.drawMonth - 12 + monthIndex;
								}	
								calendar.find('td>a.ui-state-default').each(function (dayIndex) {  
									var day = parseInt($(this).text() );
									var date = new Date(currYear, currMonth, day);

									if(self.firstDisabledElement != null && self.firstDisabledElement[0] == this && date.getTime() >= startDate.getTime()){
										return;
									}
									var $next_el = $(this).parent().next();
									if($next_el.length == 0){
										$next_el = $(this).parent().parent().next().find('td:first-child');
									}

									if(($next_el.length == 0 || !$next_el.find('*').length) && $(this).parents('.ui-datepicker-group-last').length == 0){
										$next_el = $($('.ui-datepicker-group-last tbody>tr:first-child>td>*')[0]).parent('td');
									}

									self.checkDay(this, day, currMonth, currYear, step, next_elements_are_disabled);
									if($next_el.find('>.ui-state-no-avail').length > 0 && !next_elements_are_disabled && date.getTime() >= startDate.getTime()){
										var $next_inner_el = $($next_el.find('>.ui-state-no-avail')[0]);
										$next_el.find('>.ui-state-no-avail')
										.removeClass('ui-state-no-avail')
										.addClass('ui-state-avail')
										.parent('td')
										.removeClass('ui-datepicker-unselectable')
										.removeClass('ui-state-disabled');
										if(step == 2){
											$next_inner_el.parent('td').addClass('ui-state-no-avail-before-container');
										}
										var classes = $next_inner_el.prop('class');

										$next_inner_el.replaceWith('<a class="'+classes+'" href="#">'+$next_inner_el.html()+'</a>');
										self.firstDisabledElement = $($next_el.find('>.ui-state-avail')[0]);
										next_elements_are_disabled = true;
									}
									if(step == 2 && self.firstDisabledElement != null && !self.firstDisabledElement.hasClass('ui-state-active')){
										self.firstDisabledElement.parent('td').addClass('ui-datepicker-unselectable');    
										self.firstDisabledElement.parent('td').addClass('ui-state-disabled');  
										self.firstDisabledElement.addClass('ui-state-no-avail').removeClass('ui-state-avail').parent('td').removeClass('ui-state-no-avail-before-container');
										self.firstDisabledElement = null;
									}
								});
							});
						}, 1);
					}
				},
				checkDay: function(elem, day, month, year, step, next_elements_are_disabled){
					var date = new Date(year, month, day);
          var adults, kids, guestsCount;
					if(date.getTime() >= this.startDate.getTime()&& date.getTime() <= this.endDate.getTime()){
						$(elem).addClass('ui-state-active').removeClass('ui-state-highlight');    
						if(step == 1){
							// TODO: 
						}else if(step == 2){
							if(date.getTime() != this.endDate.getTime()){
								adults = parseInt($('input[name="adults"]').val());
								kids = parseInt($('input[name="children"]').val());
								guestsCount = adults + kids;
							}else{
								var from = this.startDate.getTime();
								var to = this.endDate.getTime();
								var dayDiff = Math.round((to - from) / (1000 * 60 * 60 * 24));
							}
						}else{
							if(date.getTime() != this.endDate.getTime()){
								adults = parseInt($('select[name="adults"]').val());
								kids = parseInt($('select[name="children"]').val());
								guestsCount = adults + kids;
							}
						}
					}
					if(step == 2) {
						if(date.getTime() < this.startDate.getTime() && date.getTime() > this.endDate.getTime()){
							$(elem).parent('td').addClass('ui-datepicker-unselectable');    
							$(elem).parent('td').addClass('ui-state-disabled');  
							//$(elem).removeClass('ui-state-default'); 
							$(elem).parent('td').removeAttr('data-year');    				
							$(elem).parent('td').removeAttr('data-event');    				
							$(elem).parent('td').removeAttr('data-handler');    				
						}
					}
					else if(step == 1) {
						if(date.getTime() < this.startDate.getTime() || next_elements_are_disabled){
							$(elem).parent('td').addClass('ui-datepicker-unselectable');    
							$(elem).parent('td').addClass('ui-state-disabled');  
							//$(elem).removeClass('ui-state-default'); 
							$(elem).parent('td').removeAttr('data-year');    				
							$(elem).parent('td').removeAttr('data-event');    				
							$(elem).parent('td').removeAttr('data-handler');    				
						}
					}
				},
				getSelectedDate: function(inst){
					return new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay);
				}
			};

			calendar.datepicker({
				minDate: minDateVal,
				maxDate: maxDateVal,
				mode: 'range',
				regional:"ru",
				numberOfMonths: 2,
				showButtonPanel: false,
				hideIfNoPrevNext: true,
				firstDay : "1",
				dateFormat: 'dd.mm.yy',
				beforeShow: function (input, inst) {
					datepickerRange.checkDays(inst);
				},
				"onSelect": function (dateText, inst) {
					datepickerRange.selectCount++;
					datepickerRange.currentDate = datepickerRange.getSelectedDate(inst);
					if(datepickerRange.selectCount<2){
						datepickerRange.startDate = datepickerRange.getSelectedDate(inst);
						datepickerRange.endDate = null;
						$('input[name="CalendarPeriodFrom"]').val(dateText);
						datepickerRange.endDate = datepickerRange.getSelectedDate(inst);
						datepickerRange.checkDays(inst, 1);
					}else{
						datepickerRange.selectCount = 0;
						datepickerRange.endDate = datepickerRange.getSelectedDate(inst);
						if(datepickerRange.startDate.getTime()>datepickerRange.endDate.getTime()){
							datepickerRange.endDate = datepickerRange.startDate;
							datepickerRange.startDate = datepickerRange.currentDate;    
							//date correcting in inputs
							$('input[name="CalendarPeriodFrom"]').val($.datepicker.formatDate('dd.mm.yy', datepickerRange.startDate));
							$('input[name="CalendarPeriodTo"]').val($.datepicker.formatDate('dd.mm.yy', datepickerRange.endDate));
						}
						else if(datepickerRange.startDate.getTime()==datepickerRange.endDate.getTime()){
							$('input[name="CalendarPeriodTo"]').val($.datepicker.formatDate('dd.mm.yy', new Date(datepickerRange.endDate.getFullYear(), datepickerRange.endDate.getMonth(), datepickerRange.endDate.getDate()+1)));
						}else{
							$('input[name="CalendarPeriodTo"]').val(dateText);
						}
						setPeriods(datepickerRange);
						datepickerRange.checkDays(inst, 2);
					}
					return false;
				},
				onChangeMonthYear: function(year, month, inst) {
					datepickerRange.currentDate = datepickerRange.getSelectedDate(inst);
					var cDate = new Date(datepickerRange.currentDate.getTime());
					cDate.setDate(1);
					cDate.setMonth(cDate.getMonth()+3);
					var lastDate = new Date(datepickerRange.lastFilledDate.getTime());
					lastDate.setDate(1);
					if(cDate >= lastDate){
						var nextDate = new Date(lastDate.getTime());
						nextDate.setMonth(nextDate.getMonth()+3);
					}
					datepickerRange.checkDays(inst);
					setTimeout(function(){
						fill_calendar();
					}, 0.1);
				}			
			});
					
			$.datepicker.setDefaults($.datepicker.regional[lang]);
			calendar.datepicker( "refresh" );
			calendar.datepicker("setDate", dateFrom);
			$('td.ui-datepicker-today>*').removeClass('ui-state-active');

			setPeriods(datepickerRange);
		}
	}

	fill_calendar();
	function fill_calendar(){
		var $td = $('td[data-month]');
		var rt_types = Object.keys(roomsArray);
		for (var i = 0; i < rt_types.length; i++) {
			var rtCode = rt_types[i];	
			if(typeof roomsArray[rtCode] != 'undefined'){
				var rtArray = roomsArray[rtCode];
				var indexOf = function (arr, period) {
				  for(var idx = 0, i = arr.length;arr[idx] && arr[idx].period !== period;idx++);
				  return idx === i ? -1 : idx;
				}

				$.each($td, function(){
					var $el = $(this).find('>*');
					var year = $(this).data('year');
					var month = $(this).data('month');
					var day = $el.html();

					if(parseInt(day)<10 && day.length < 2){
						day = "0"+day;
					}
					month = parseInt(month)+1;
					if(month<10){
						month = "0"+month;
					}
					if(typeof roomsArray != 'undefined'){
						var elInd = indexOf(rtArray, day+"."+month+"."+year);
            var classes;
						if(elInd >= 0){
							$el.addClass("ui-state-avail").removeClass("ui-state-no-avail");
							if($el[0].tagName == 'SPAN'){
								classes = $el.prop('class');
								$el.replaceWith('<a class="'+classes+'" href="#">'+$el.html()+'</a>');
							}
							$(this).removeClass("ui-datepicker-unselectable").removeClass("ui-state-disabled");
							if(typeof periodsArray[rtArray[elInd]["filterPeriod"]] != 'undefined'){
								periodsArray[rtArray[elInd]["filterPeriod"]].push({roomType: rtCode, rooms: rtArray[elInd]["rooms"]});
							}else{
								periodsArray[rtArray[elInd]["filterPeriod"]] = [];
							}
						}else{
							$el.removeClass("ui-state-avail").removeClass("ui-state-hover").addClass("ui-state-no-avail");
							classes = $el.prop('class');
							$el.replaceWith('<span class="'+classes+'">'+$el.html()+'</span>');
							$(this).addClass("ui-datepicker-unselectable").addClass("ui-state-disabled");
						}
					}
				});
			}else{
				console.log(rtCode+' is not found in roomsArray by '+rate+' rate');
			}
		}
	}
})
</script>

<?endif;?>