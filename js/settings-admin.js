$(document).ready(function() {
	function saveSettings() {
		OC.msg.startSaving('#activitydefaults_msg');
		var post = $('#activitydefaults').serialize();
		$.post(OC.generateUrl('/apps/activitydefaults/settings-admin'), post, function(response) {
			OC.msg.finishedSuccess('#activitydefaults_msg', response.data.message);
		});
	}

	$('#activitydefaults input[type=checkbox]').change(saveSettings);

	$('#activitydefaults select').change(saveSettings);

	$('#activitydefaults .activity_select_group').click(function() {
		var $selectGroup = '#activitydefaults .' + $(this).attr('data-select-group');
		var $filteredBoxes = $($selectGroup).not(':disabled');
		var $checkedBoxes = $filteredBoxes.filter(':checked').length;
		
		$filteredBoxes.attr('checked', true);
		if ($checkedBoxes === $filteredBoxes.filter(':checked').length) {
			// All values were already selected, so invert it
			$filteredBoxes.attr('checked', false);
		}

		saveSettings();
	});
});
