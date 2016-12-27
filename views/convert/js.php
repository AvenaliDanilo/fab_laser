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
	var active_profile = 0;
	var pwm_settings = 'none';
	var speed_settings = 'none';
	var skip_settings = 'none';
	var cut_settings = 'none';

	$(document).ready(function() {
		initSlider();
		
		$("#more-details").on('click', more_details)
		$(".modify-profile").on('click', modify_profile)
		$(".monitor-change").on('change', value_change);
		$("#laser-profile").on('change', profile_change);
		$("#pwm-mode").on('change', pwm_mode_change);
		$("#speed-mode").on('change', speed_mode_change);
		$("#skip-mode").on('change', skip_mode_change);
		$("#modalAddButton").on('click', add_profile);

		$("#newProfileForm").validate({
			rules:{
				profile_name:{
					required: true
				},
				profile_material:{
					required: true
				}
			},
			messages: {
				profile_name: {
					required: 'Please type a profile name'
				},
				profile_material: {
					required: 'Please type a profile material'
				}
			},
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
		
		// Because W or H are calculated only one is needed so make the
		// other zero.
		$("#target_width").on('change', function(e){
			$("#target_height").val("0");
			trigger_debug_update();
		});
		
		$("#target_height").on('change', function(e){
			$("#target_width").val("0");
			trigger_debug_update();
		});
		
		$("#invert").on('change', function(e){
			trigger_debug_update();
		});
		
		profiles = jQuery.parseJSON('<?php echo json_encode($presets);?>');
		
		load_profile(active_profile);
	});
	
	/**
	 * Load a specific profile to the UI
	 */
	function load_profile(idx)
	{
		var p = profiles[idx];
		
		active_profile = idx;
		
		do_not_apply = true;
		
		$("[name='info-name']").val(p.info.name);
		$("[name='info-material']").val(p.info.material);
		$("[name='info-description']").val(p.info.description);
		
		$("#speed-mode").val(p.speed.type).trigger('change');
		switch(p.speed.type)
		{
			case "const":
				$("[name='speed-burn']").val(p.speed.burn);
				$("[name='speed-travel']").val(p.speed.travel);
				break;
			case "linear":
				$("[name='speed-in_min']").val(p.speed["in_max"]);
				$("[name='speed-in_max']").val(p.speed["in_min"]);
				$("[name='speed-out_min']").val(p.speed["out_min"]);
				$("[name='speed-out_max']").val(p.speed["out_max"]);
				break;
		}
		
		$("#pwm-mode").val(p.pwm.type).trigger('change');
		switch(p.pwm.type)
		{
			case "const":
				$("[name='pwm-value']").val(p.pwm.value);
				break;
			case "linear":
				$("[name='pwm-in_min']").val(p.pwm["in_max"]);
				$("[name='pwm-in_max']").val(p.pwm["in_min"]);
				$("[name='pwm-out_min']").val(p.pwm["out_min"]);
				$("[name='pwm-out_max']").val(p.pwm["out_max"]);
				break;
		}
		
		switch(p.skip.type)
		{
			case "modulo":
				$("[name='skip-mod']").val(p.skip["mod"]);
				var on_list = p.skip["on"];
				var val = "";
				var is_first = true;
				for(i=0; i<on_list.length; i++)
				{
					if(is_first)
					{
						is_first = false;
					}
					else
						val += ", ";
					val += on_list[i]
				}
				
				$("[name='skip-on']").val(val);
				
				break;
		}
		$("#skip-mode").val(p.skip.type).trigger('change');
		
		modified = false;
		update_modified();
		
		do_not_apply = false;
	}

	function update_modified()
	{
		if(modified)
		{
			
			$("[data-attribute='save']").removeClass("btn-default").addClass("btn-primary");
		}
		else
		{
			$("[data-attribute='save']").removeClass("btn-primary").addClass("btn-default");
		}
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

	/**
	 * Profile modify action interpreter.
	 */
	function modify_profile()
	{
		var action = $(this).attr('data-attribute');
		console.log('profile:', action);
		
		switch(action)
		{
			case "save":
				save_profile(active_profile);
				modified = false;
				update_modified();
				break;
			case "add":
				$('#profileModal').modal('show');
				break;
			case "remove":
				$.SmartMessageBox({
						title: "<i class='fa fa-warning txt-color-orangeDark'></i> Warning!",
						content: "Do you really want to remove \""+profiles[active_profile].info.name+" ["+profiles[active_profile].info.material+"]\" profile",
						buttons: '[No][Yes]'
				}, function(ButtonPressed) {
					if (ButtonPressed === "Yes") 
					{
						remove_profile(active_profile);
						
					}
					if (ButtonPressed === "No")
					{
					}
				});
				break;
		}
	}
	
	/**
	 * Add a new profile.
	 */
	function add_profile()
	{
		if($("#newProfileForm").valid()){
			
			var name = $("#profile_name").val();
			var filename = name.replace(/ /g,'_').toLowerCase() + '.json';
			
			data = {
				info : {
					name: name,
					material: $("#profile_material").val(),
					description: $("#profile_desc").val(),
					},
				speed : {
					type : "const",
					burn : 1000,
					travel : 10000,
					off_during_travel : false
					},
				pwm : {
					type : "linear",
					in_min : 0,
					in_max : 255,
					out_min : 0,
					out_max : 255
					},
				skip : {
					type : "modulo",
					mod: 1,
					on: [0]
					},
				cut : {
					pwm : 255,
					num_pass : 1,
					z_step : 0.0
					}
			};
			
			$.ajax({
				type: "POST",
				url: "<?php echo site_url( plugin_url('modifyPreset').'/save' );?>/" + filename,
				data : data,
				dataType: 'json'
			}).done(function( data ) {
				reload_profiles();
			});
			
			$('#profileModal').modal('hide');
			more_details();
		}
	}
	
	/**
	 * Remove specific profile.
	 */
	function remove_profile(idx)
	{
		filename = profiles[idx].filename;
		
	 	$.ajax({
			type: "POST",
			url: "<?php echo site_url( plugin_url('modifyPreset').'/remove' );?>/" + filename,
			dataType: 'json'
		}).done(function( data ) {
			
			idx -= 1;
			if(idx < 0)
				idx = 0;
			active_profile = idx;
			
			reload_profiles();
		});
	}
	
	/**
	 * Save UI data to profile.
	 */
	function save_profile(idx, action = 'save')
	{
		var data = {};
		$(".slicing-profile :input").each(function (index, value) {
			var name = $(this).attr('name');
			if(name)
			{
				if(name == "skip-on")
				{
					data[$(this).attr('name')] = $(this).val().split(',');
				}
				else if(name != "laser-profile")
				{
					data[$(this).attr('name')] = $(this).val();
				}
			}
		});
		console.log( data );
		
		filename = profiles[idx].filename;
		
	 	$.ajax({
			type: "POST",
			url: "<?php echo site_url( plugin_url('modifyPreset') );?>/" + action + "/" + filename,
			dataType: 'json',
			data : data
		}).done(function( result ) {
			
		});
	}
	
	function reload_profiles(selected = '')
	{
		$.get("<?php echo site_url( plugin_url('modifyPreset').'/reload' );?>", 
			function(data, status){
				profiles = data.list;
				load_profile(active_profile);
				
				var html = "";
				
				for(i=0; i<profiles.length; i++)
				{
					p = profiles[i];
					html += '<option value="'+i+'">'+p.info.name+' ['+p.info.material+']</option>'
				}
				
				$("#laser-profile").html(html);
			});
	}
	
	function profile_change()
	{
		var profile_index = $(this).val();
		console.log('profile changed', profile_index);
		load_profile(profile_index);
		trigger_debug_update();
	}
	
	function speed_mode_change()
	{
		var mode = $(this).val();
		console.log('mode', mode);
		
		if(speed_settings != mode)
		{
			$('.speed-settings').slideUp();
			$('#speed-'+mode).slideDown();
		}
		
		speed_settings = mode;
	}
	
	function pwm_mode_change()
	{
		var mode = $(this).val();
		console.log('mode', mode);
		
		if(pwm_settings != mode)
		{
			$('.pwm-settings').slideUp();
			$('#pwm-'+mode).slideDown();
		}
		
		pwm_settings = mode;
	}
	
	function skip_mode_change()
	{
		var mode = $(this).val();
		console.log('mode', mode);
		
		if(skip_settings != mode)
		{
			$('.skip-settings').slideUp();
			$('#skip-'+mode).slideDown();
		}
		
		skip_settings = mode;
	}
	
	function value_change(e)
	{
		if(do_not_apply == false)
		{
			console.log("value changed", e);
			modified = true;
			update_modified();
			trigger_debug_update();
		}
	}
	
	function trigger_debug_update()
	{
		// postpone value change to reduce overloading the communication
		clearTimeout(update_timer);
		update_timer = setTimeout(apply_new_values, 1000);
	}

	function apply_new_values()
	{
		// TODO: disable inputs during update
		console.log('applying new_values...');
		
		var tw = $("#target_width").val();
		var th = $("#target_height").val();
		var invert = $("#invert").is(":checked")?"yes":"no";
		
		var preset_path = "<?php echo $profile_path; ?>";
		
		var filename = preset_path + "/" + profiles[active_profile].filename;
		if(modified)
		{
			save_profile(active_profile, 'temp');
			console.log('saving to temp file...');
			filename = "/tmp/fabui/laser_profile.json";
		}
		
		data = {
			profile : filename,
			target_width : tw,
			target_height : th,
			levels : levels,
			invert : invert
		}
		
		console.log(data);
		
		$("#loading").slideDown();
		
	 	$.ajax({
			type: "POST",
			url: "<?php echo site_url( plugin_url('generateGCode') );?>",
			data: data,
			dataType: 'json'
		}).done(function( data ) {
			console.log(data);
			$("#loading").slideUp();
			
			var new_src = "/temp/debug.png?" + new Date().getTime();
			
			$("#preview").attr('src', new_src);
		});
		
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
			trigger_debug_update();
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
