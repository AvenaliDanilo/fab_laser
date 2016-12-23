<?php
/**
 * 
 * @author Daniel Kesler
 * @version 1.0
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>

<script type="text/javascript">

	var idFile <?php echo $file_id != '' ? ' = '.$file_id : ''; ?>; //file to create
	var idTask <?php echo $runningTask ? ' = '.$runningTask['id'] : ''; ?>;

	var details = false;
	var levels = 6;
	var update_timer = null;
	var modified = false;
	var do_not_apply = false;

	var profiles = {};

	$(document).ready(function() {
		initSlider();
		
		$("#more-details").on('click', more_details)
		$(".modify-profile").on('click', modify_profile)
		$("#laser-profile").on('change', profile_change);
		$(".form-control").on('change', value_change);
		$("#pwm-mode").on('change', pwm_mode_change);
		$("#speed-mode").on('change', speed_mode_change);
		$("#skip-mode").on('change', skip_mode_change);
		
		
		profiles = jQuery.parseJSON('<?php echo json_encode($presets);?>');
		
		console.log('profiles', profiles);
	});
	
	function load_profile(idx)
	{
		var p = profiles[idx];
		console.log( p.info.name )
		console.log( p.speed.type )
		console.log( p.pwm.type )
		
		$("#speed-mode").val(p.speed.type).trigger('change');
		switch(p.speed.type)
		{
			case "const":
				$("[name='speed-burn']").val(p.speed.burn);
				$("[name='speed-travel']").val(p.speed.travel);
				break;
			case "linear":
				$("[name='speed-input-min']").val(p.speed["in-max"]);
				$("[name='speed-input-max']").val(p.speed["in-min"]);
				$("[name='speed-output-min']").val(p.speed["out-min"]);
				$("[name='speed-output-max']").val(p.speed["out-max"]);
				break;
		}
		
		$("#pwm-mode").val(p.pwm.type).trigger('change');
		switch(p.pwm.type)
		{
			case "const":
				$("[name='pwm-value']").val(p.pwm.value);
				break;
			case "linear":
				console.log('pwm-in-max', p.pwm["in-max"]);
				$("[name='pwm-input-min']").val(p.pwm["in-max"]);
				$("[name='pwm-input-max']").val(p.pwm["in-min"]);
				$("[name='pwm-output-min']").val(p.pwm["out-min"]);
				$("[name='pwm-output-max']").val(p.pwm["out-max"]);
				break;
		}
		
		$("#skip-mode").val(p.skip.type).trigger('change');
	}

	function more_details()
	{
		if(!details)
		{
			$("#all-settings").slideDown();
			$("#more-details").html('Less Details');
		}
		else
		{
			$("#all-settings").slideUp();
			$("#more-details").html('More Details');
		}
		
		details = !details;
		
		return false;
	}

	function modify_profile()
	{
		var action = $(this).attr('data-attribute');
		console.log('profile:', action);
		
		switch(action)
		{
			case "save":
				modified = false;
				break;
		}
	}
	
	function profile_change()
	{
		var profile_index = $(this).val();
		console.log('profile changed', profile_index);
		load_profile(profile_index);
	}
	
	function speed_mode_change()
	{
		var mode = $(this).val();
		console.log('mode', mode);
		
		$('.speed-settings').slideUp();
		$('#speed-'+mode).slideDown();
	}
	
	function pwm_mode_change()
	{
		var mode = $(this).val();
		console.log('mode', mode);
		$('.pwm-settings').slideUp();
		$('#pwm-'+mode).slideDown();
	}
	
	function skip_mode_change()
	{
		var mode = $(this).val();
		console.log('mode', mode);
		$('.skip-settings').slideUp();
		$('#skip-'+mode).slideDown();
	}
	
	function value_change(e)
	{
		//~ console.log("value changed", e);
	}

	function apply_new_values()
	{
		
	}

	/**
	* handle rotating slider scan
	*/
	function initSlider()
	{
		noUiSlider.create(document.getElementById('gray-slider'), {
			start: levels,
			step: 1,
			connect: "lower",
			range: {'min': 1, 'max' : 10},
		});
		sweepSlider = document.getElementById('gray-slider');
		
		sweepSlider.noUiSlider.on('slide',  function(e){
			levels = parseInt(e);
		});
	}

	/**
	* freeze ui
	*/
	function freezeUI()
	{
		disableButton('.btn-prev');
		disableButton('.btn-next');
		disableButton('.top-directions');
		disableButton('.top-axisz');
	}
	/**
	*
	*/
	function unFreezeUI()
	{
		enableButton('.top-directions');
		enableButton('.top-axisz');
	}
	
	function checkWizard()
	{
		console.log('check Wizard');
		var step = $('.wizard').wizard('selectedItem').step;
		console.log(step);
		switch(step){
			case 1:
				disableButton('.btn-prev');
				if(idFile)
					enableButton('.btn-next');
				else
					disableButton('.btn-next');
				$('.btn-next').find('span').html('Next');
				break;
			case 2:
				enableButton('.btn-prev');
				disableButton('.btn-next');
				$('.btn-next').find('span').html('Save');
				break;
			case 3:
				startTask();
				return false;
				break; 
		}
	}
	
	function startTask()
	{
		console.log('Starting task');
		is_task_on = true;
		openWait('Initializing');
		
		var data = {
			idFile:idFile
			};
			
		$.ajax({
			type: 'post',
			data: data,
			url: '<?php echo site_url($start_task_url); ?>',
			dataType: 'json'
		}).done(function(response) {	
			if(response.start == false){
				$('.wizard').wizard('selectedItem', { step: 2 });
				showErrorAlert('Error', response.message);
			}else{
				//~ fabApp.resetTemperaturesPlot(50);
				freezeUI();
				//~ setInterval(timer, 1000);
				//~ setInterval(jsonMonitor, 1000);
				idTask = response.id_task;
				//~ initSliders();
				//~ setTimeout(initGraph, 1000);
				//~ setTemperaturesSlidersValue(response.temperatures.extruder, response.temperatures.bed);
				//~ getTaskMonitor(true);
				//~ updateZOverride(0);
			}
			closeWait();
			//TODO freeze menu fabApp.freezeMenu();
		})
	}
	
	function jogSetAsZero()
	{
		console.log('set as zero');
		enableButton('.btn-next');
		return false;
	}
	
</script>
