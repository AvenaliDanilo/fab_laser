<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="row">
	<div class="col-sm-12 col-md-12">
		<div class="product-content product-wrap clearfix">
			<div class="row">
				<div class="col-sm-6 col-sx-6  hidden-xs">
					<div class="product-image medium text-center">
						<img class="img-responsive" style="width:50%; margin-top:10px; display:inline;" src="<?php echo plugin_assets_url('img/fabui_laser_01a-b.png');?>">
					</div>
				</div>
				<div class="col-sm-6">
					<div class="description">
						<div class="alert alert-warning font-md" role="alert"><strong><?php echo _("Warning");?>:</strong> <?php echo _("You are about to start a manufacturing task involving the laser head"); ?> </div>
						<p class="font-md text-left">
							<?php echo _("Make sure to follow the");?> <a target="_blank" href="<?php echo $safety_guide_lines_link; ?>" class="no-ajax"><?php echo _("safety guidelines");?></a>
						</p>
						<ol class="margin-top-10 font-md">
							<li><?php echo _("Verify that engraving or cutting the material poses no hazard");?></li>
							<li><strong><?php echo _("Put the provided safety goggles now before continuing");?></strong></li>
							<li><strong><?php echo _("Make sure no one else can approach the  unit without proper safety goggles and being informed of the hazard");?></strong></li>
							<li><strong><?php echo _("Do not remove the goggles unless it's safe to do so");?></strong></li>
							<li><?php echo _("Wait for the procedure to end");?></li>
							<li><?php echo _("Do not touch, place or remove the laser head while the unit is operating");?></li>
						</ol>
						<div class="smart-form">
							<fieldset>
								<section>
									<label class="checkbox font-md">
										<input type="checkbox" name="understand" id="understand">
										<i></i> <?php echo _("I understand and i agree with the conditions");?> <?php echo _("and");?> <a target="_blank" href="<?php echo $safety_guide_lines_link; ?>" class="no-ajax"><?php echo _("safety guidelines");?></a>
									</label>
								</section>
							</fieldset>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

