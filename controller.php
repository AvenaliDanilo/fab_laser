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
	
	public function make($fileID = '')
	{
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('plugin_helper');
		$this->load->model('Files', 'files');
		
		$data = array();
		$data['runningTask'] = $this->runningTask;
		$data['file_id'] = '';
		
		$file = $this->files->get($fileID, 1);
		$file_is_ok = False;
		if($file)
		{
			if($file['print_type'] == 'laser')
			{
				$data['file_id'] = $fileID;
				$file_is_ok = True;
				$data['fileid_jump_to'] = 2; // jump to step 2 if fileID is available
			}
		}
		
		$data['type']      = 'laser';
		// select_file
		$data['get_files_url'] = plugin_url('getFiles');
		$data['get_reacent_url'] = plugin_url('getRecentFiles');
		// task_wizard
		$data['start_task_url'] = plugin_url('startTask');
		// jog_setup
		$data['jog_message'] = 'Position the laser point to the origin (bottom-left corner) of the drawing. Jog to desired XY position, press <i class="fa fa-bullseye"></i> and then press "Start" ';
		$data['jog_image'] = plugin_assets_url('img/fabui_laser_02a.png');
		$data['fourth_axis'] = False;
		
		$data['steps'] = array(
				array('number'  => 1,
				 'title'   => 'Choose File',
				 'content' => $this->load->view( plugin_url('std/select_file'), $data, true ),
				 'active'  => !$file_is_ok
			    ),
				array('number'  => 2,
				 'title'   => 'Safety',
				 'content' => $this->load->view( plugin_url('make/wizard/safety'), $data, true ),
				 'active'  => $file_is_ok
				 
			    ),
				array('number'  => 3,
				 'title'   => 'Get Ready',
				 'content' => $this->load->view( plugin_url('make/wizard/jog_setup'), $data, true ),
			    ),
				array('number'  => 4,
				 'title'   => 'Laser Engraving',
				 'content' => ''
			    ),
				array('number'  => 5,
				 'title'   => 'Finish',
				 'content' => '',
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
		$widget->body   = array('content' => $this->load->view(plugin_url('std/task_wizard'), $data, true ), 'class'=>'fuelux', 'footer'=>$widgeFooterButtons);

		$this->addCssFile(plugin_assets_url('css/select_file.css'));

		if(!$this->runningTask){ //if task is running these filee are not needed
			$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
		}

		$this->addJsInLine($this->load->view( plugin_url('make/js'), $data, true));
		$this->addJSFile('/assets/js/plugin/fuelux/wizard/wizard.min.old.js'); //wizard
		
		$this->addJsInLine($this->load->view( plugin_url('std/task_wizard_js'), $data, true));
		$this->addJsInLine($this->load->view( plugin_url('std/select_file_js'), $data, true));
		
		$this->addJSFile('/assets/js/plugin/knob/jquery.knob.min.js');
		$this->addJsInLine($this->load->view( plugin_url('std/jog_setup_js'), $data, true));
		
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	public function convert($fileID = '')
	{
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('plugin_helper');
		$this->load->model('Files', 'files');
		
		$data = array();
		$data['runningTask'] = $this->runningTask;
		$data['file_id'] = '';
		
		$pwm_modes = array('const' => 'Constant', 'linear' => 'Linear mapping');
		$speed_modes = array('const' => 'Constant', 'linear' => 'Linear mapping');
		$skip_modes = array('modulo' => 'Modulo');
		
		$presets = $this->getPresets();
		
		$presets_combo = array();
		
		foreach($presets as $_key => $_value)
		{
			$presets_combo[$_key] = $_value['info']['name'] . ' [' . $_value['info']['material'] . ']';
		}
		
		$data['pwm_modes'] = $pwm_modes;
		$data['speed_modes'] = $speed_modes;
		$data['skip_modes'] = $skip_modes;
		$data['presets_combo'] = $presets_combo;
		$data['presets'] = $presets;
		$data['profile_path'] = plugin_path() . '/presets';
		
		$file = $this->files->get($fileID, 1);
		$file_is_ok = False;
		if($file)
		{
			if($file['file_ext'] == '.jpg' || $file['file_ext'] == '.png' || $file['file_ext'] == '.dxf')
			{
				$data['file_id'] = $fileID;
				$file_is_ok = True;
				$data['fileid_jump_to'] = 2; // jump to step 2 if fileID is available
			}
		}
		
		$data['type']      = 'laser';
		// select_file
		$data['get_files_url'] = plugin_url('getImageFiles');
		//~ $data['get_reacent_url'] = plugin_url('getRecentFiles');
		// task_wizard
		$data['start_task_url'] = plugin_url('startTask');
		
		$data['steps'] = array(
				array('number'  => 1,
				 'title'   => 'Choose File',
				 'content' => $this->load->view( plugin_url('std/select_file'), $data, true ),
				 'active'  => !$file_is_ok
			    ),
				array('number'  => 2,
				 'title'   => 'Configure',
				 'content' => $this->load->view( plugin_url('convert/ui'), $data, true ),
				 'active'  => $file_is_ok
			    ),
				array('number'  => 3,
				 'title'   => 'Finish',
				 'content' => '',
			    )
			);
		
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widgeFooterButtons = '';

		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-head-installation';
		$widget->header = array('icon' => 'fa-cube', "title" => "<h2>Convert to GCode</h2>");
		$widget->body   = array('content' => $this->load->view(plugin_url('std/task_wizard'), $data, true ), 'class'=>'fuelux', 'footer'=>$widgeFooterButtons);

		$this->addCssFile(plugin_assets_url('css/select_file.css'));

		if(!$this->runningTask){ //if task is running these filee are not needed
			$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
		}

		$this->addJsInLine($this->load->view( plugin_url('convert/js'), $data, true));
		$this->addJSFile('/assets/js/plugin/fuelux/wizard/wizard.min.old.js'); //wizard
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addCSSFile(plugin_assets_url('css/convert.css'));
		
		$this->addJsInLine($this->load->view( plugin_url('std/task_wizard_js'), $data, true));
		$this->addJsInLine($this->load->view( plugin_url('std/select_file_js'), $data, true));
		
		$this->content = $widget->print_html(true);
		$this->view();
	}
	
	private function getPresets()
	{
		$this->load->helper('plugin_helper');
		
		$presets = array();

		$this->load->helper('file');
		
		$presets_path = plugin_path() . '/presets';
		$preset_files = get_filenames( $presets_path );
		
		foreach($preset_files as $preset_file)
		{
			$data =  json_decode(file_get_contents($presets_path . '/' . $preset_file), true);
			$data['filename'] = $preset_file;
			$presets[] = $data;
		}
		
		return $presets;
	}
	
	public function test()
	{
		$this->output->set_content_type('application/json')->set_output(json_encode(array(true)));
	}
	
	public function modifyPreset($action, $preset = '')
	{
		$this->load->helper('plugin_helper');
		$this->load->helpers('utility_helper');
		$this->load->helper('file');
		$presets_path = plugin_path() . '/presets';
		$filename = $presets_path . '/' . $preset;
		
		$result = array();
		$result['success'] = false;
		
		switch($action)
		{
			case "temp":
				$data = arrayFromPost($this->input->post());
				$filename = "/tmp/fabui/laser_profile.json";
				$result['success'] = write_file($filename, json_encode($data));
				$result['filename'] = $filename;
				break;
			case "save":
			case "add":
				$data = arrayFromPost($this->input->post());
				$result['success'] = write_file($filename, json_encode($data));
				$result['filename'] = $filename;
				break;
			case "remove":
				$result['success'] = unlink($filename);
				break;
			case "reload":
				$result['list'] = $this->getPresets();
				$result['success'] = true;
				break;
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($result));
	}
	
	/**
	 * @param $data (list)
	 * return array data for dataTable pluguin
	 */
	private function dataTableFormat($data)
	{
		//load text helper
		$this->load->helper('text_helper');
		$aaData = array();
		foreach($data as $file){ 
			$td0 = '<label class="radio"><input type="radio" name="create-file" value="'.$file['id_file'].'"><i></i></label>';
			$td1 = '<i></i><span class="hidden-xs">'.$file['client_name'].'</span><span class="hidden-md hidden-sm hidden-lg">'.ellipsize($file['orig_name'], 35).'</span>';
			$td2 = '<i class="fa fa-folder-open"></i> <span class="hidden-xs">'.$file['name'].'</span><span class="hidden-md hidden-sm hidden-lg">'.ellipsize($file['name'], 35).'</span>';
			$td3 = $file['id_file'];
			$td4 = $file['id_object'];
			$aaData[] = array($td0, $td1, $td2, $td3, $td4);
		}
		return $aaData;
	}
	
	/**
	 * @param type (additive, subtractive)
	 * @return json object for dataTables plugin
	 * get all files
	 */
	public function getFiles()
	{
		//load libraries, models, helpers
		$this->load->model('Files', 'files');
		$files = $this->files->getForCreate( 'laser' );
		$aaData = $this->dataTableFormat($files);
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}
	
	/**
	 * @param type (additive, subtractive)
	 * @return json object for dataTables plugin
	 * get all files
	 */
	public function getImageFiles()
	{
		//load libraries, models, helpers
		$this->load->model('Files', 'files');
		$files = $this->files->getByExtension( array('.jpg', '.jpeg', '.png', '.dxf') );
		$aaData = $this->dataTableFormat($files);
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}
	
	public function getRecentFiles($task_type = '')
	{
		//load libraries, models, helpers
		$this->load->model('Tasks', 'tasks');
		$files  = $this->tasks->getLastCreations($task_type);
		$aaData = $this->dataTableFormat($files);
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}
	
	public function generateGCode($fileId)
	{
		$this->load->model('Files', 'files');
		$file = $this->files->get($fileId, 1);

		$response = false;
	
		if($file)
		{
			$postData = $this->input->post();
			
			$this->load->helper('plugin_helper');

			$params = array(
				$postData['profile'],
				$file['full_path'],
				'-W' => $postData['target_width'],
				'-H' => $postData['target_height'],
				'-l' => $postData['levels'],
				'-d',
				'-o' => "/tmp/fabui/output.gcode"
			);
			
			if($postData['invert'] == 'yes')
			{
				$params[] = '-i';
			}
			
			$log = startPluginPyScript('img2gcode.py', $params, false);
			
			if($log)
			{
				$response = true;
			}
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
	public function startTask()
	{
		$response = array(
			'start' => false, 
			'message' => 'Task Not Implemented yet.', 
			'trace' => '', 
			'error' => ''
			);
		
		$response['start'] = true;
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

 }
 
?>
