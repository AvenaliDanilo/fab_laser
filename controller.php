<?php
/**
 * 
 * @author FABteam
 * @version 1.0
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Plugin_fab_laser extends FAB_Controller {

	function __construct()
	{
		parent::__construct();
		if(!$this->input->is_cli_request()){ //avoid this form command line
			//check if there's a running task
			//load libraries, models, helpers
			$this->load->model('Tasks', 'tasks');
			//$this->tasks->truncate();
			$this->runningTask = $this->tasks->getRunning();
		}
	}

	public function index()
	{
		
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('plugin_helper');
		
		$data = array();
		
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widgeFooterButtons = '';

		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-head-installation';
		$widget->header = array('icon' => 'fa-cube', "title" => "<h2>Laser Utils</h2>");
		$widget->body   = array('content' => $this->load->view(plugin_url('main_widget'), $data, true ), 'class'=>'no-padding', 'footer'=>$widgeFooterButtons);

		$this->addJsInLine($this->load->view(plugin_url('js'), $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	public function make($fileID = '-1')
	{
		$this->load->library('smart');
		$this->load->helper(array('language_helper', 'form', 'fabtotum_helper', 'utility_helper', 'plugin_helper'));
		$this->load->model('Files', 'files');
		
		$data = array();
		
		$data['runningTask'] = $this->runningTask;
		//~ $data['runningTask'] = array('id' => 5);
		$data['file_id'] = '';
		
		// Skip file selection step if fileID is provided
		$file = $this->files->get($fileID, 1);
		$file_is_ok = False;
		if($file)
		{
			if($file['print_type'] == 'laser')
			{
				$data['file_id'] = $fileID;
				$file_is_ok = True;
				$data['wizard_jump_to'] = 2; // jump to step 2 if fileID is available
			}
		}
		
		// Skip to Job Execution step if task is already running
		$task_is_running = False;
		if($data['runningTask'])
		{
			$data['wizard_jump_to'] = 4;
			$task_is_running = True;
		}
		
		//$data['wizard_jump_to'] = 0;
		
		$data['type']      = 'laser';
		$data['type_label'] = 'Engraving';
		
		// Safety check
		if(!$task_is_running){
			
			$data['safety_check'] = safetyCheck("laser", "no");
			$data['safety_check']['url'] = 'std/safetyCheck/laser/no';
			$data['safety_check']['content'] = $this->load->view( 'std/task_safety_check', $data, true );
		}
		
		
		$data['is_laser']     = true;
		$data['is_laser_pro'] = isLaserProHead();
		
		$language = getCurrentLanguage() == 'it_IT' ? 'it' : ''; 
		$data['safety_guide_lines_link'] = $data['is_laser_pro'] ? 'https://www.fabtotum.com/'.$language.'/laser-head-pro-safety-health-guidelines/' : 'https://www.fabtotum.com/'.$language.'/laser-head-safety-health-guidelines';
		// select_file
		$data['get_files_url']   = 'std/getFiles/laser';
		$data['get_reacent_url'] = 'std/getRecentFiles/laser';
		
		// task_wizard
		$data['start_task_url']        = plugin_url('startTask');
		$data['restart_task_url_file'] = plugin_url('make', true);
		
		// jog_setup
		$data['jog_message'] = _('Position the laser point to the origin (bottom-left corner) of the drawing.<br>Jog to desired XY position, press <i class="fa fa-bullseye"></i> and then press "Start" ');
		$data['jog_image']   = !$data['is_laser_pro'] ? plugin_assets_url('img/fabui_laser_02a.png') : plugin_assets_url('img/fabui_laser_pro_02a.png');
		$data['fourth_axis'] = False;
		
		// job_execute
		$data['set_rpm_function'] = 'setLaserPWM';
		$data['rpm_label'] = 'PWM';
		//~ $data['rpm_message'] = 'PWM value set to:';
		$data['rpm_min'] = 0;
		$data['rpm_max'] = 255;
		
		// job finish
		$data['z_height_save_message'] = _("Z's height correction is <strong><span class=\"z-height\"></span></strong>, do you want to save it and override the value for the next engraving?");
		$data['task_jump_restart']     = 3;
		
		$data['steps'] = array(
				array('number'  => 1,
				 'title'   => _('Choose File'),
				 'content' => !$task_is_running ? $this->load->view( 'std/select_file', $data, true ) : '',
				 'active'  => !$file_is_ok && !$task_is_running
			    ),
				array('number'  => 2,
				 'title'   => _('Safety'),
				 'content' => !$task_is_running ? $this->load->view( plugin_url('make/wizard/safety'), $data, true ) : '',
				 'active'  => $file_is_ok && !$task_is_running
			    ),
				array('number'  => 3,
				 'title'   => _('Get Ready'),
				 'content' => !$task_is_running ? $this->load->view( 'std/jog_setup', $data, true ) : '',
			    ),
				array('number'  => 4,
				 'title'   => _('Laser Engraving'),
				 'content' => $this->load->view( 'std/task_execute', $data, true ),
				 'active' => $task_is_running
			    ),
				array('number'  => 5,
				 'title'   => _('Finish'),
				 'content' => $this->load->view( 'std/task_finished', $data, true )
			    )
			);
		
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widgeFooterButtons = '';

		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-make-laser';
		$widget->header = array('icon' => 'fa-cube', "title" => "<h2>Laser Engraving</h2>");
		$widget->body   = array('content' => $this->load->view('std/task_wizard', $data, true ), 'class'=>'fuelux');
		
		if(!$task_is_running){
			
			$this->addCssFile('/assets/css/std/select_file.css');
			$this->addCssFile('/assets/css/std/jog_setup.css');
			$this->addCssFile('/assets/css/std/jogtouch.css');
			$this->addCssFile('/assets/css/std/jogcontrols.css');
			
			$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
			
			$this->addJSFile('/assets/js/std/raphael.min.js' ); //vector library
			$this->addJSFile('/assets/js/std/modernizr-touch.js' ); //touch device detection
			$this->addJSFile('/assets/js/std/jogcontrols.js' ); //jog controls
			$this->addJSFile('/assets/js/std/jogtouch.js' ); //jog controls
			$this->addJSFile('/assets/js/plugin/knob/jquery.knob.min.js');
		}
		
		
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.cust.min.js'); 
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.resize.min.js');
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.fillbetween.min.js');
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.time.min.js');
		$this->addJSFile('/assets/js/plugin/flot/jquery.flot.tooltip.min.js');
		$this->addJSFile('/assets/js/plugin/fuelux/wizard/wizard.min.old.js'); //wizard
		
		
		$this->addJsInLine($this->load->view( plugin_url('make/js'), $data, true));

		if(!$task_is_running){
			$this->addJsInLine($this->load->view( 'std/task_safety_check_js', $data, true));
			$this->addJsInLine($this->load->view( 'std/select_file_js', $data, true));
			$this->addJsInLine($this->load->view( 'std/jog_setup_js', $data, true));
		}
		
		$this->addJsInLine($this->load->view( 'std/task_wizard_js', $data, true));
		$this->addJsInLine($this->load->view( 'std/task_execute_js', $data, true));
		$this->addJsInLine($this->load->view( 'std/task_finished_js', $data, true));
		
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	public function startTask()
	{
		//load helpers
		$this->load->helpers('fabtotum_helper');
		$this->load->helper('plugin_helper');
		$this->load->model('Files', 'files');
		
		$data = $this->input->post();
		
		$go_to_focus_point     = $data['go_to_focus'] == 'true' ? 1 : 0;
		$automatic_positioning = isset($data['automatic_positioning']) && $data['automatic_positioning'] == 'true' ? 1 : 0;
		$fan_on                = isset($data['fan']) && $data['fan'] == 'true' ? 1 : 0;
		$fileToCreate          = $this->files->get($data['idFile'], 1);
		
		//reset task monitor file
		resetTaskMonitor();
		
		$checkResult = doMacro('check_engraving');
		
		if($checkResult['response'] != 'success')
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => false, 'message' => $checkResult['message'], 'response' => $checkResult['response'] )));
			return;
		}
		
		$startSubtractive = doMacro('start_engraving', '', [$go_to_focus_point, $automatic_positioning, $fan_on]);
		
		
		
		if($startSubtractive['response'] != 'success'){
			$this->output->set_content_type('application/json')->set_output(json_encode(array('start' => false, 'message' => $startSubtractive['message'])));
			return;
		}
		
		resetTrace( _("Please wait...") );
		
		//get object record
		$object = $this->files->getObject($fileToCreate['id']);

		$response = array(
			'start' => false, 
			'message' => _("Task Not Implemented yet."), 
			'trace' => '', 
			'error' => ''
			);
		
		//add record to DB
		$this->load->model('Tasks', 'tasks');
		$taskData = array(
			'user'       => $this->session->user['id'],
			'controller' => 'plugin/fab_laser/make',
			'type'       => 'laser',
			'status'     => 'running',
			'id_file'    => $data['idFile'],
			'id_object'  => $object['id'],
			'start_date' => date('Y-m-d H:i:s')
		);
		$taskId   = $this->tasks->add($taskData);
		
		$response['start']        = true;
		$response['id_task']      = $taskId;
		$response['file']['name'] = $fileToCreate['client_name'];
		
		//start print
		$params = array(
			'-T' => $taskId, 
			'-F' => $fileToCreate['full_path']
		);
		
		startPluginPyScript('engrave.py', $params, true);
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

 }
 
?>
