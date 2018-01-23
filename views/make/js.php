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

	<?php if($runningTask): ?>
	var idFile = <?php echo $runningTask['id_file']; ?>;
	<?php else: ?>
	var idFile <?php echo $file_id != '' ? ' = '.$file_id : ''; ?>; //file to create
	<?php endif; ?>
	var idTask <?php echo $runningTask ? ' = '.$runningTask['id'] : ''; ?>;
	var isLaserPro = <?php echo $is_laser_pro ? 'true' : 'false'; ?>;
	
	
	$(document).ready(function() {
		$('#understandSafety').on('click', understandSafety);
		$('[data-toggle="tooltip"]').tooltip();


		$("#understand").click(function(){
			if($(this).is(":checked")){
				enableButton('.button-next');
			}else{
				disableButton('.button-next');
			}
		});

		$("#focus-point").click(function() {

			if($(this).is(":checked")){
				$("#laser-calibrate-z-focus-row").show();
				$("#automatic-positioning").prop('checked', false);
				$(".jog-controls").show();
				$(".jog-controls-holder").show();
				$(".touch-container").show();
				$("#head-position-row").show();
			}else{
				$("#laser-calibrate-z-focus-row").hide();
			}
			
		});
		
		$("#automatic-positioning").on('click', function(){
			if($(this).is(":checked")){
				$("#focus-point").prop('checked', false);
				$(".jog-controls").hide();
				$(".jog-controls-holder").hide();
				$(".touch-container").hide();
				enableButton('.button-next');
				$("#laser-calibrate-z-focus-row").show();
				$("#head-position-row").hide();
			}else{
				$(".jog-controls").show();
				$(".jog-controls-holder").show();
				$(".touch-container").show();
				disableButton('.button-next');
				$("#head-position-row").show();
			}
		});

		
	});
	
	function handleStep()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		
		if(step == 3)
		{
			<?php if($runningTask): ?>;
			// do nothing
			<?php else: ?>
				cmd = 'M62\nM723';
				fabApp.jogMdi(cmd);
				startTask();
				return false;
			<?php endif; ?>
			return false;
		}
		
		return true;
	}
	
	function checkWizard()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		switch(step){
			case 1: // Select file
				disableButton('.button-prev');
				if(idFile)
					enableButton('.button-next');
				else
					disableButton('.button-next');
				$('.button-next').find('span').html('Next');
				
				cmd = 'M62\n\M723'; //shutdown laser
				fabApp.jogMdi(cmd);
				break;
				
			case 2: // Safety
				enableButton('.button-prev');
				disableButton('.button-next');
				$('.button-next').find('span').html('Next');
				
				cmd = 'M62\n\M723'; //shutdown laser (also laser cross)
				fabApp.jogMdi(cmd);
				$("#understand").prop('checked', false);
				break;
				
			case 3: // Calibration
				enableButton('.button-prev');
				disableButton('.button-next');
				$('.button-next').find('span').html('Engrave');

				if(isLaserPro){ //if is pro laser use laser cross for calibration
					cmd = 'M722\nM300\n';
				}else{
					cmd = 'M60 S10\nM300\n';
				}
				
				fabApp.jogMdi(cmd);
				//fabApp.jogMdi('M701 S0\nM702 S0\nM703 S0\n');
				break;
				
			case 4: // Execution
				break;
				
			case 5:
				$('.button-next').find('span').html('');
		}
	}
	
	function jogSetAsZero()
	{
		enableButton('.button-next');
		return false;
	}
	
	function understandSafety()
	{
		enableButton('.button-next');
		return false;
	}
	
	function startTask()
	{
		console.log('Starting task');
		openWait('<i class="fa fa-spinner fa-spin "></i> ' + "<?php echo dgettext('fab_laser', 'Preparing {0}');?>".format("<?php echo _(ucfirst($type)); ?>"), "<?php echo dgettext("fab_laser", "Checking safety measures...") ?>");
		
		var data = {
			idFile:idFile,
			go_to_focus: $("#focus-point").is(":checked")
		};

		if(isLaserPro){
			data['fan'] = $("#fan-on").is(":checked");
			data['automatic_positioning'] = $("#automatic-positioning").is(":checked");
		}
			
		$.ajax({
			type: 'post',
			data: data,
			url: '<?php echo site_url($start_task_url); ?>',
			dataType: 'json'
		}).done(function(response) {
			if(response.start == false){
				gotoWizardStep(2);
				fabApp.showErrorAlert(response.message);
			}else{
				gotoWizardStep(4);
				idTask = response.id_task;
				updateFileInfo(response.file);
				disableCompleteSteps();
				initRunningTaskPage();
				updateZOverride(0);
                if (typeof ga !== 'undefined') {
				    ga('send', 'event', 'laser', 'start', 'laser started');
				}
			}
			closeWait();
		})
	}
	/**
	*
	**/
	function setLaserPWM(action, value)
	{
		message="Laser PWM set to: " + value;
		showActionAlert(message);
	}
	
</script>
