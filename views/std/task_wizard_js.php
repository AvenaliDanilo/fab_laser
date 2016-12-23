<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

?>
<script type="text/javascript">

	var wizard; //wizard object
	
	$(document).ready(function() {
		console.log('task_wizard_js: ready');
		initWizard();
	});
	
	//init wizard flow
	function initWizard()
	{
		wizard = $('.wizard').wizard();
		disableButton('.btn-prev');
		disableButton('.btn-next');
		
		$('.wizard').on('changed.fu.wizard', function (evt, data) {
			checkWizard();
		});
		$('.btn-prev').on('click', function() {
			console.log('prev');
			if(canWizardPrev()){
			}
		});
		$('.btn-next').on('click', function() {
			console.log('next');
			if(canWizardNext()){
			}
		});
		
		<?php if(isset($fileid_jump_to)): ?>
			$('.wizard').wizard('selectedItem', {
				step: <?php echo $fileid_jump_to?>
			});
			enableButton('.btn-prev');
		<?php endif; ?>
		
		checkWizard();
	}
	
	// check if i can move to previous step
	function canWizardPrev()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		return false;
	}
	
	//check if i can move to next step
	function canWizardNext()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		console.log('Can Wizard Next: ' + step);
		return false;
	}
	
	
</script>
