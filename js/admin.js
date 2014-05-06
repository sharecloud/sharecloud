var Admin = {
	bindEvents: function() {
		$('.check-for-updates').click(function(e) {
            $('.check-for-updates').button('loading');
			$('.check-for-update-error').addClass('hidden');	
			
			$.get(System.getHostname() + 'admin/updateCheck', function(data) {
				$('.check-for-updates').button('reset');
				
				try {
					var response = $.parseJSON(data);
					
					if(response.success == true) {
						if(response.data.isUpdateAvailable) {
							$('.check-for-updates').addClass('hidden');
							$('.update-system').removeClass('hidden');
						} else {
							$('.check-for-updates').parent().remove();
							$('.no-update-available').removeClass('hidden');
						}
					} else {
						// Display error
						$('.check-for-update-error').removeClass('hidden');	
					}
				} catch (e) {
					
					
				}
			});
			
			return false;
        });
	},
	
	unbindEvents: function() {
			
	}
};

$(document).ready(function(e) {
    Admin.bindEvents();
});

System.unbindEventCallbacks.add('admin', Admin.unbindEvents);
System.bindEventCallbacks.add('admin', Admin.bindEvents);