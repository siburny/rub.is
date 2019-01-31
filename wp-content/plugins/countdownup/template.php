<script>
jQuery(function() {
	var start = new Date('<?php echo $date->format('c'); ?>');
	var increment = 1 * '<?php echo $increment; ?>';
	var loop_time = 1 * '<?php echo $loop_time; ?>';
	var loop_range = '<?php echo $loop_range; ?>';
	var expire = !!<?php echo $expire; ?>;
	var large = !!<?php echo $large; ?>;
	
	var display = ['<?php echo implode("', '", $display); ?>'];
	var seconds = {
		year: 365.25*1000*60*60*24, month: 30.41666667*1000*60*60*24, week: 7*1000*60*60*24, day: 1000*60*60*24, hour: 1000*60*60, minute: 1000*60, second: 1000,
		years: 365.25*1000*60*60*24, months: 30.41666667*1000*60*60*24, weeks: 7*1000*60*60*24, days: 1000*60*60*24, hours: 1000*60*60, minutes: 1000*60, seconds: 1000
	};

	var delimiter = '<?php echo $delimiter; ?>';
	
	function updateCountdown() {
		var today = new Date();
		var start2 = new Date(start);
		var diff =  increment * (today - start2);
		var abs_diff = Math.abs(diff);
		var text = '';
		
		if(!expire) {
			if(!!loop_time && !!loop_range) {
				if(loop_range == 'h') {
					loop_range = seconds['hour'];
				} else {
					loop_range = seconds['day'];
				}

				if(start2 > today) {
					while(start2 > new Date(today.getTime() + loop_time * loop_range)) {
						today = new Date(today.getTime() + loop_time * loop_range);
					}
				} else {
					while(start2 < today) {
						today = new Date(today.getTime() - loop_time * loop_range);
					}
				}
			} else {
				start2.setFullYear(today.getFullYear());
				diff =  start2 - today;
				
				if(increment < 0) {
					if(diff < 0) {
						start2.setFullYear(today.getFullYear() + 1);
					} else {
						start2.setFullYear(today.getFullYear());
					}
				} else if(increment > 0) {
					if(diff > 0) {
						start2.setFullYear(today.getFullYear() - 1);
					} else {
						start2.setFullYear(today.getFullYear());
					}
				}
			}

			diff = (start2 - today);
			abs_diff = Math.abs(diff);
		} else {
			if(diff <= 999) {
				text = '<?php echo str_replace("'", "''", $expireText); ?>';
			}
		}
		
		if(!text) {
			var show_zero = !!<?php echo $showzero ?>;
			for(i=0;i<display.length;i++) {
				if(!!seconds[display[i]]) {
					if(abs_diff > seconds[display[i]] || show_zero) {
						var count = Math.floor(abs_diff / seconds[display[i]]);
						
						if(!large) {
							text += (text.length ? delimiter : '') + count.toLocaleString() + ' ' + display[i] + (count != 1 ? 's' : '');
						} else {
							text += '<div style="text-align:center;display:inline-block;margin:10px;text-transform:capitalize;"><span style="font-weight:bold;font-size:24px;color:#000;">' + count + '</span><br /><span style="font-size:11px;">' + display[i] + (count != 1 ? 's' : '') + '</span></div>'
						}

						if(count > 0 && !show_zero) {
							//show_zero = true;
						}
						abs_diff -= count * seconds[display[i]];
					}
				}
			}
		}
		
		jQuery('#countdown_<?php echo $uniqueId; ?>').html(text);
	}

	setInterval(updateCountdown, 1000);
	updateCountdown();
});

</script>
<?php if($large): ?><div id="countdown_<?php echo $uniqueId; ?>" style="text-align:center;"></div><?php else: ?><span id="countdown_<?php echo $uniqueId; ?>" style=""></span><?php endif; ?>