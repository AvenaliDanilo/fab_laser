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

	$(document).ready(function() {
		$('#understandSafety').on('click', understandSafety);
	});

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
			case 1: // Select file
				disableButton('.btn-prev');
				if(idFile)
					enableButton('.btn-next');
				else
					disableButton('.btn-next');
				$('.btn-next').find('span').html('Next');
				
				//cmd = 'M60 S0\n';
				//fabApp.jogMdi(cmd);
				
				break;
			case 2: // Safety
				enableButton('.btn-prev');
				disableButton('.btn-next');
				$('.btn-next').find('span').html('Next');
				
				//cmd = 'M60 S0\n';
				//fabApp.jogMdi(cmd);
				
				break;
			case 3: // Calibration
				enableButton('.btn-prev');
				disableButton('.btn-next');
				$('.btn-next').find('span').html('Start');
				
				//cmd = 'M60 S10\nM300\n';
				//fabApp.jogMdi(cmd);
				break;
				
				break;
			case 4: // Execution
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
	
	function understandSafety()
	{
		enableButton('.btn-next');
		return false;
	}
	
	function jogSetAsZero()
	{
		console.log('set as zero');
		enableButton('.btn-next');
		return false;
	}
	
</script>
