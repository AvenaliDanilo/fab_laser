<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">

	$(document).ready(function() {
		$(".axisxy").on('click', moveXYZ);
		$(".axisz").on('click', moveXYZ);
		$(".setzero").on('click', jogSetAsZero);
		
		$('.knob').knob({
			change: function (value) {
			},
			release: function (value) {
				rotation(value);
			},
			cancel: function () {
				console.log("cancel : ", this);
			}
		});
		
		$('.knob').keypress(function(e) {
			if(e.which == 13) {
				rotation($(this).val());
			}
		 });
	});
	
	function rotation(value)
	{
	}
	
	function moveXYZ()
	{
		var dir      = $(this).attr("data-attribue-direction");
		var xystep   = $("#xy-step").val();
		var zstep    = $("#z-step").val();
		var feedrate = $("#feedrate").val();
		var cmd      = '';
		
		switch(dir)
		{
			case "z-up":
				cmd = 'G91\nG0 Z+'+zstep+' F'+feedrate;
				break;
			case "z-down":
				cmd = 'G91\nG0 Z-'+zstep+' F'+feedrate;
				break;
			case "right":
				cmd = 'G91\nG0 X+'+xystep+' F'+feedrate;
				break;
			case "left":
				cmd = 'G91\nG0 X-'+xystep+' F'+feedrate;
				break;
			case "up":
				cmd = 'G91\nG0 Y+'+xystep+' F'+feedrate;
				break;
			case "down":
				cmd = 'G91\nG0 Y-'+xystep+' F'+feedrate;
				break;
			case "down-right":
				cmd = 'G91\nG0 X+'+xystep+' Y-'+xystep+' F'+feedrate;
				break;
			case "up-right":
				cmd = 'G91\nG0 X+'+xystep+' Y+'+xystep+' F'+feedrate;
				break;
			case "down-left":
				cmd = 'G91\nG0 X-'+xystep+' Y-'+xystep+' F'+feedrate;
				break;
			case "up-left":
				cmd = 'G91\nG0 X-'+xystep+' Y+'+xystep+' F'+feedrate;
				break;
		}
		
		if(cmd != '')
		{
			fabApp.jogMdi(cmd);
		}
		
		console.log('move_xyz', dir);
		console.log('JOG', cmd);
		return false;
	}

</script>
