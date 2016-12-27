<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<hr class="simple">
<div class="row">
	
	<div class="col-sm-7">
		<div class="debug-preview text-center">
			<img id="preview" src="/temp/debug.png" class="margin-bottom-10">
			<div class="debug-loading" style="display:none" id="loading"><i class="fa fa-cog fa-spin fa-3x fa-fw"></i></div>
		</div>
	</div>
	
	<div class="col-sm-5">
		
		<!-- Target size, levels -->
		<fieldset>
			<div class="row">
				<section class="col-sm-6 col-xs-6">
					<label>Target dimensions in mm</label>
					<div class="input-group margin-top-5">
						<span class="input-group-addon">Width</span>
						<input class="form-control" id="target_width", type="number" value="30">
					</div>
				</section>
				
				<section class="col-sm-6 col-xs-6">
					<label>&nbsp;</label>
					<div class="input-group margin-top-5">
						<span class="input-group-addon">Height</span>
						<input class="form-control" id="target_height" type="number" value="0">
					</div>
				</section>
			</div>
			<section>
				<p class="font-sm margin-top-10">Number of gray levels: </p>
				<div id="gray-slider" class="noUiSlider noUi-target"></div>
			</section>
			<section>
				<div class="checkbox">
					<label>
					  <input id="invert" type="checkbox" class="checkbox style-0" checked="checked">
					  <span>Invert colors</span>
					</label>
				</div>
			</section>
		</fieldset>
		<hr class="simple">
		
		<!-- Profile -->
		<fieldset>
			<div class="row">
				<section class="col-sm-8 col-xs-8">
					<?php echo form_dropdown('laser-profile', $presets_combo, 'const', array('id' => 'laser-profile', 'class' => 'form-control' ) ); ?> <i></i>
				</section>
				<section class="col-sm-4 col-xs-4">
					<div class="btn-group btn-group-justified">
						<a href="javascript:void(0);" class="btn btn-default btn-sm modify-profile" data-attribute="add"><i class="fa fa-plus"></i></a>
						<a href="javascript:void(0);" class="btn btn-default btn-sm modify-profile" data-attribute="remove"><i class="fa fa-minus"></i></a>
						<a href="javascript:void(0);" class="btn btn-default btn-sm modify-profile" data-attribute="save"><i class="fa fa-floppy-o"></i></a>
					</div>
				</section>
			</div>
		</fieldset>
		
		<hr class="simple">
		
		<div id="all-settings" style="display:none" class="slicing-profile">

		<fieldset style="display:none">
			<input type="text" id="info-name" name="info-name" value=""/>
			<input type="text" id="info-material" name="info-material" value=""/>
			<input type="text" id="info-description" name="info-description" value=""/>
		</fieldset>

		<!-- Feedrate -->
		<fieldset>
			<section>
				<label>Feedrate mode</label>
				<select class="form-control monitor-change" id="speed-mode" name="speed-type">
					<option value="const" selected="selected">Constant</option>
					<option value="linear">Linear mapping</option>
				</select>
			</section>
			
			<div id="speed-linear" class="speed-settings" style="display: none">
			
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Input-min</span>
							<input name="speed-in_min" class="form-control monitor-change" type="number" value="0">
						</div>
					</section>
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Input-max</span>
							<input name="speed-in_max" class="form-control monitor-change" type="number" value="255">
						</div>
					</section>
				</div>
				
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Output-min</span>
							<input name="speed-out_min" class="form-control monitor-change" type="number" value="0">
						</div>
					</section>
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Output-max</span>
							<input name="speed-out_max" class="form-control monitor-change" type="number" value="255">
						</div>
					</section>
				</div>
			
			</div>
			
			<div id="speed-const" class="speed-settings" style="display: none">
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Burn</span>
							<input name="speed-burn" class="form-control monitor-change" type="number" value="1000">
						</div>
					</section>
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Travel</span>
							<input name="speed-travel" class="form-control monitor-change" type="number" value="10000">
						</div>
					</section>
				</div>
			</div>
			
		</fieldset>
		
		<hr class="simple">
		<!-- PWM -->
		<fieldset>
			<section>
				<label>PWM mode</label>
				<select class="form-control monitor-change" id="pwm-mode" name="pwm-type">
					<option value="const" selected="selected">Constant</option>
					<option value="linear">Linear mapping</option>
				</select>
			</section>
			
			
			<div id="pwm-linear" class="pwm-settings" style="display: none">
			
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Input-min</span>
							<input name="pwm-in_min" class="form-control monitor-change" type="number" value="0">
						</div>
					</section>
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Input-max</span>
							<input name="pwm-in_max" class="form-control monitor-change" type="number" value="0">
						</div>
					</section>
				</div>
				
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Output-min</span>
							<input name="pwm-out_min" class="form-control monitor-change" type="number" value="0">
						</div>
					</section>
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Outpu-max</span>
							<input name="pwm-out_max" class="form-control monitor-change" type="number" value="0">
						</div>
					</section>
				</div>
			
			</div>
			
			<div id="pwm-const" class="pwm-settings" style="display: none">
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Value</span>
							<input name="pwm-value" class="form-control monitor-change" type="number" value="0">
						</div>
					</section>
				</div>
			</div>
			
		</fieldset>
		
		<hr class="simple">
		<!-- Skip -->
		<fieldset>
			<section>
				<label>Skip line mode</label>
				<select class="form-control monitor-change" id="skip-mode" name="skip-type">
					<option value="modulo" selected="selected">Modulo</option>
				</select>
			</section>
			
			<div class="row">
				<section class="col-sm-6 col-xs-6">
					<div class="input-group margin-top-10">
						<span class="input-group-addon">Mod</span>
						<input name="skip-mod" class="form-control monitor-change" type="number" value="0">
					</div>
				</section>
				<section class="col-sm-6 col-xs-6">
					<div class="input-group margin-top-10">
						<span class="input-group-addon">List [on]</span>
						<input name="skip-on" class="form-control monitor-change" type="text" value="0">
					</div>
				</section>
			</div>
			
			
		</fieldset>
		
		<hr class="simple">
				<!-- TODO: cut settings -->
				
		</div>
		
		<div class="row">
			<div class="col-md-12 text-center"> 
				<button id="more-details" class="btn btn-secondary">More Details</button> 
			</div>
		</div>
		
		<hr class="simple">
	</div>

	
</div>


<!-- ADD PROFILE MODAL -->
<div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Add new profile</i></h4>
			</div>

			<div class="modal-body custom-scroll " id="progressModalBody">
				<form id="newProfileForm">
					<fieldset>
						<section>
							<div class="input-group margin-top-10">
								<span class="input-group-addon">Name</span>
								<input id="profile_name" name="profile_name" class="form-control" type="text" value="New Profile">
							</div>
						</section>
						
						<section>
							<div class="input-group margin-top-10">
								<span class="input-group-addon">Material</span>
								<input id="profile_material" name="profile_material" class="form-control" type="text" value="Generic">
							</div>
						</section>
						
						<section>
							<div class="input-group margin-top-10">
								<span class="input-group-addon">Description</span>
								<input id="profile_desc" name="profile_desc" class="form-control" type="text" value="">
							</div>
						</section>
					</fieldset>
				</form>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="modalAddButton"><i class="fa fa-check"></i> Add </button>
			</div>
		</div>
	</div>
</div>
