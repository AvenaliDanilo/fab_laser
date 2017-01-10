<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<hr class="simple">
<div id="row_3" class="row interstitial" >
	<div class="col-sm-12">
		<div class="well">
			<div class="row">
				<div class="col-sm-6">
					<div class="text-center">
						<div class="row">
							<div class="col-sm-7">
								<img style=" display: inline;" class="img-responsive" src="<?php echo $jog_image?>" />
							</div>
							<div class="col-sm-5">
								
								<h1></h1>
								<h2 class="text-center"><?php echo $jog_message; ?></h2>
								
							</div>
						</div>
					</div>
					<div class="smart-form">
						<div class="row">
							<section>
								<label class="checkbox">
									<input type="checkbox" name="checkbox" checked="checked">
									<i></i>Calibrate Z focusing point (Suggested)</label>
							</section>
						</div>
					</div>
					
					</div>
					
			    <div class="col-sm-6">
			        <div class="text-center">
			            <div class="row">
							<div class="col-sm-12">
								<div class="smart-form">
									<fieldset style="background: none !important;">
										<div class="row">
											<section class="col col-4">
												<label class="label-mill text-center">XY Step (mm)</label>
												<label class="input">
													<input  type="number" id="xy-step" value="10" step="1" min="0" max="100">
												</label>
											</section>
											<section class="col col-4">
												<label class="label-mill text-center">Feedrate</label>
												<label class="input">
													<input  type="number" id="feedrate" value="1000" step="50" min="0" max="5000">
												</label>
											</section>
											<section class="col col-4">
												<label class="label-mill text-center">Z Step (mm)</label>
												<label class="input"> 
													<input type="number" id="z-step" value="5" step="1" min="0" max="100">
												</label>
											</section>
										</div>
									</fieldset>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="btn-group-vertical">
									<button data-attribue-direction="up-left" data-attribute-keyboard="103" class="btn btn-default btn-lg axisxy btn-circle btn-xl rotondo">
										<i class="fa fa-arrow-left fa-1x fa-rotate-45">
										</i>
									</button>
									<button data-attribue-direction="left" data-attribute-keyboard="100" class="btn btn-default btn-lg axisxy btn-circle btn-xl rotondo">
										<span class="glyphicon glyphicon-arrow-left ">
										</span>
									</button>
									<a href="javascript:void(0)" data-attribue-direction="down-left" data-attribute-keyboard="97" class="btn btn-default btn-lg axisxy btn-circle btn-xl rotondo">
										<i class="fa fa-arrow-down fa-rotate-45 ">
										</i>
									</a>
								</div>
								<div class="btn-group-vertical">
									<button data-attribue-direction="up" data-attribute-keyboard="104" class="btn btn-default btn-lg axisxy btn-circle btn-xl rotondo">
										<i class="fa fa-arrow-up fa-1x">
										</i>
									</button>
									<button id="zero-all"  class="btn btn-default btn-lg btn-circle btn-xl rotondo setzero">
										<i class="fa fa-bullseye">
										</i>
									</button>
									<button data-attribue-direction="down" data-attribute-keyboard="98" class="btn btn-default btn-lg axisxy btn-circle btn-xl rotondo">
										<i class="glyphicon glyphicon-arrow-down ">
										</i>
									</button>
								</div>
								<div class="btn-group-vertical">
									<button data-attribue-direction="up-right" data-attribute-keyboard="105" class="btn btn-default btn-lg axisxy btn-circle btn-xl rotondo">
										<i class="fa fa-arrow-up fa-1x fa-rotate-45">
										</i>
									</button>
									<button data-attribue-direction="right" data-attribute-keyboard="102" class="btn btn-default btn-lg axisxy btn-circle btn-xl rotondo">
										<span class="glyphicon glyphicon-arrow-right">
										</span>
									</button>
									<button data-attribue-direction="down-right" data-attribute-keyboard="99" class="btn btn-default btn-lg axisxy btn-circle btn-xl rotondo">
										<i class="fa fa-arrow-right fa-rotate-45">
										</i>
									</button>
								</div>
									
									
								<div class="btn-group-vertical margin-top-10" style="margin-left: 10px;">
									<button class="btn btn-default axisz" data-attribue-direction="z-down">
										<i class="fa fa-angle-double-up">
										</i>&nbsp;Z
									</button>
									<hr />
									<button class="btn btn-default axisz" data-attribue-direction="z-up">
										<i class="fa fa-angle-double-down">
										</i>&nbsp; Z
									</button>
									
								</div>
							</div>
							
							<?php if($fourth_axis == True): ?>
							<div class="col-sm-4">
								<span>Mode:</span><span class="mode"> 4th Axis</span>
								<div class="knobs-demo  text-center margin-top-10" id="mode-a">
									<input class="knob" data-width="150" value="0" data-cursor="true" data-step="0.5" data-min="1" data-max="360" data-thickness=".3" data-fgColor="#A0CFEC" data-displayInput="true">
								</div>
							</div>
                            <?php endif; ?>
							
							
						</div>
			        </div>
        		</div>
    		</div>
		</div>
    </div>

</div>

