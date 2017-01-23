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

	var xmlrpc_host = window.location.hostname;
	var xmlrpc_port = 8000;

	wsApp = (function(app) {
		
		app.mytemp = 0;
		app.socket = null;
		app.ws_callbacks = {}
		
		app.setMytest = function(value) {
			app.mytemp = value;
		};
		
		app.getMytest = function() {
			return app.mytemp;
		};
		
		app.getIncremental = function() {
			return app.mytemp++;
		};
		
		app.jogMdi = function(val, callback) {
			return app.serial('manualDataInput', val, callback);
		};
		
		app.serial = function(func, val, callback) {
			
			stamp = Date.now();
			
			var data = {
				'method'           : func,
				'value'            : val,
				'stamp'            : stamp,
				'useXmlrpc'        : xmlrpc,
			};
			
			var messageToSend = {
				'function' : 'serial',
				'params' : data
			};
			
			if($.isFunction(callback))
			{
				app.ws_callbacks[stamp] = callback;
				//~ console.log('=== adding callback for', stamp, app.ws_callbacks);
			}
			
			app.socket.send( JSON.stringify(messageToSend) );
			
			return stamp;
		};
		
		app.manageJogResponse = function(data) {
			var stamp = null;
			var response = [];
			
			for(i in data.commands)
			{
				if(stamp != null)
				{
					
					response.push(data.commands[i]);
				}
				else
				{
					stamp = i.split('_')[0];
					response.push(data.commands[i]);
				}
			}

			if(app.ws_callbacks.hasOwnProperty(stamp))
			{
				app.ws_callbacks[stamp](response);
				delete app.ws_callbacks[stamp];
			}
		};
		
		app.webSocket = function()
		{
			options = {
				http: "<?php echo site_url( plugin_url('ws_fallback') ); ?>",
				//~ arguments: {arg1:"test1", arg2:"test2"}
			};
			
			app.socket = ws = $.WebSocket ('ws://'+socket_host+':'+socket_port, null, options);

			// WebSocket onerror event triggered also in fallback
			ws.onerror = function (e) {
				console.log ('Error with WebSocket uid: ' + e.target.uid);
				
			};

			// if connection is opened => start opening a pipe (multiplexing)
			ws.onopen = function () {
				console.log("ws: opened as ", ws.fallback?"fallback":"websocket");
			};  
			
			ws.onmessage = function (e) {
				//console.log('>> ws.onmessage', e);
				try {
				
					var obj = jQuery.parseJSON(e.data);
					//console.log(obj.type);
					console.log("✔ WebSocket2 received message: %c [" + obj.type + "]", debugStyle);
					
					switch(obj.type){
						case 'temperatures':
							//app.updateTemperatures(obj.data);
							
							break;
						case 'macro':
							//app.manageMacro(obj.data);
							break;
						case 'emergency':
							//app.manageEmergency(obj.data);
							break;
						case 'alert':
							//app.manageAlert(obj.data);
							break;
						case 'task':
							//app.manageTask(obj.data);
							break;
						case 'system':
							//app.manageSystem(obj.data);
							break;
						case 'usb':
							//app.usb(obj.data.status, obj.data.alert);
							break;
						case 'jog':
							app.manageJogResponse(obj.data);
							break;
						case 'trace':
							//app.handleTrace(obj.data.content);
							break;
						default:
							break;
					}
				}catch(e){
					return;
				}
			}
			
			console.log("** wsApp init **");
		};
		
		app.ws_send = function(fun, data, callback) {
			
		};
		
		return app;
	})({});



	var idFile <?php echo $file_id != '' ? ' = '.$file_id : ''; ?>; //file to create
	var idTask <?php echo $runningTask ? ' = '.$runningTask['id'] : ''; ?>;
	
	$(document).ready(function() {
		$('#understandSafety').on('click', understandSafety);
		
		$('[data-toggle="tooltip"]').tooltip();
		
		wsApp.webSocket();
		
		console.log("wsApp:", wsApp.getIncremental());
		console.log("wsApp:", wsApp.getIncremental());
		console.log("wsApp:", wsApp.getIncremental());
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
				$('.btn-next').find('span').html('Engrave');
				
				//cmd = 'M60 S10\nM300\n';
				//fabApp.jogMdi(cmd);
				
				break;
			case 4: // Execution
				<?php if($runningTask): ?>;
				// do nothing
				<?php else: ?>
					startTask();
				<?php endif; ?>
				return false;
				break;
			case 5:
				
				$('.btn-next').find('span').html('');
		}
	}
	
	function setLaserPWM(action, value)
	{
		console.log(action, value);
		message="Laser PWM set to: " + value;
		showActionAlert(message);
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

				//setInterval(timer, 1000);
				//setInterval(jsonMonitor, 1000);
				idTask = response.id_task;
				
				<?php if($type == "print"): ?>
				fabApp.resetTemperaturesPlot(50);
				setTimeout(initGraph, 1000);
				//~ setTemperaturesSlidersValue(response.temperatures.extruder, response.temperatures.bed);
				<?php endif; ?>
				
				initRunningTaskPage();
				updateZOverride(0);
			}
			closeWait();
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
