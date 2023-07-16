jQuery(document).ready(function($) {	
	// Listen for change event on the active_leagues_type select field
	$('#active_leagues_type').change(function() {
		var selectedValue = $(this).val();
		$('#active_l_t_h').val(selectedValue);		
		if(selectedValue==2){
			$('#manual_active_leagues').show();
			$('#list_active_leagues').hide();
		}else{
			$('#manual_active_leagues').hide();
			$('#list_active_leagues').show();
		}
	});
});