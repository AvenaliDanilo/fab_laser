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
		
	</div>
	
	<div class="col-sm-5">
		<!-- Target size, levels -->
		<fieldset>
			<div class="row">
				<section class="col-sm-6 col-xs-6">
					<label>Target dimensions in mm</label>
					<div class="input-group margin-top-5">
						<span class="input-group-addon">Width</span>
						<input class="form-control" type="number" value="0">
					</div>
				</section>
				
				<section class="col-sm-6 col-xs-6">
					<label>&nbsp;</label>
					<div class="input-group margin-top-5">
						<span class="input-group-addon">Height</span>
						<input class="form-control" type="number" value="0">
					</div>
				</section>
			</div>
			<section>
				<p class="font-sm margin-top-10">Number of gray levels: </p>
				<div id="gray-slider" class="noUiSlider noUi-target"></div>
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
		
		<div id="all-settings" style="display:none">
		
		<!-- Feedrate -->
		<fieldset>
			<section>
				<label>Feedrate mode</label>
				<select class="form-control" id="speed-mode">
					<option value="const" selected="selected">Constant</option>
					<option value="linear">Linear mapping</option>
				</select>
			</section>
			
			<div id="speed-linear" class="speed-settings" style="display: none">
			
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Input-min</span>
							<input name="speed-input-min" class="form-control" type="number" value="0">
						</div>
					</section>
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Input-max</span>
							<input name="speed-input-max" class="form-control" type="number" value="0">
						</div>
					</section>
				</div>
				
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Output-min</span>
							<input name="speed-output-min" class="form-control" type="number" value="0">
						</div>
					</section>
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Outpu-max</span>
							<input name="speed-output-max" class="form-control" type="number" value="0">
						</div>
					</section>
				</div>
			
			</div>
			
			<div id="speed-const" class="speed-settings" style="display: none">
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Burn</span>
							<input name="speed-burn" class="form-control" type="number" value="0">
						</div>
					</section>
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Travel</span>
							<input name="speed-travel" class="form-control" type="number" value="0">
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
				<select class="form-control" id="pwm-mode">
					<option value="const" selected="selected">Constant</option>
					<option value="linear">Linear mapping</option>
				</select>
			</section>
			
			
			<div id="pwm-linear" class="pwm-settings" style="display: none">
			
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Input-min</span>
							<input name="pwm-input-min" class="form-control" type="number" value="0">
						</div>
					</section>
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Input-max</span>
							<input name="pwm-input-max" class="form-control" type="number" value="0">
						</div>
					</section>
				</div>
				
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Output-min</span>
							<input name="pwm-output-min" class="form-control" type="number" value="0">
						</div>
					</section>
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Outpu-max</span>
							<input name="pwm-output-max" class="form-control" type="number" value="0">
						</div>
					</section>
				</div>
			
			</div>
			
			<div id="pwm-const" class="pwm-settings" style="display: none">
				<div class="row">
					<section class="col-sm-6 col-xs-6">
						<div class="input-group margin-top-10">
							<span class="input-group-addon">Value</span>
							<input name="pwm-value" class="form-control" type="number" value="0">
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
				<select class="form-control" id="skip-mode">
					<option value="modulo" selected="selected">Modulo</option>
				</select>
			</section>
			
			<div class="row">
				<section class="col-sm-6 col-xs-6">
					<div class="input-group margin-top-10">
						<span class="input-group-addon">Mod</span>
						<input name="skip-mod" class="form-control" type="number" value="0">
					</div>
				</section>
				<section class="col-sm-6 col-xs-6">
					<div class="input-group margin-top-10">
						<span class="input-group-addon">List [on]</span>
						<input name="skip-on-list" class="form-control" type="number" value="0">
					</div>
				</section>
			</div>
			
			
		</fieldset>
		
		<hr class="simple">
		
		</div>
		
		<div class="row">
			<div class="col-md-12 text-center"> 
				<button id="more-details" class="btn btn-secondary">More Details</button> 
			</div>
		</div>
		
		<hr class="simple">
		
	</div>

	
</div>
