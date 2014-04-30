var File = {
	bindEvents: function() {
		$(".copy").each(function (i) {
			var clip = new ZeroClipboard(document.getElementById($(this).attr("id")), {
			  moviePath: System.config.httpHost + "/js/ZeroClipboard.swf"
			});
			clip.on('complete', function(client, args) {
				$("#"+$(this).attr("id")+" img").prop("src", System.config.httpHost + "/images/tick.png");
			});
		});
		
		$('video,audio').mediaelementplayer({
			// force iPad's native controls
			iPadUseNativeControls: true,
			// force iPhone's native controls
			iPhoneUseNativeControls: true, 
			// force Android's native controls
			AndroidUseNativeControls: true
		 
		});
		
		$('#modal-permissions select').change(function(e) {
			if($(this).val() == "2") {
				$('#modal-permissions #password').slideDown(500);
			} else {
				$('#modal-permissions #password').slideUp(500);
			}
		});
		
		$('#modal-permissions .form-group .col-sm-10').removeClass('col-sm-10');
		
		$('.button-filepermissions').unbind('click').click(function() {
			$('#modal-permissions').modal({
				keyboard: false
			}).modal('show');
			
			$('#modal-permissions .button-confirm').unbind('click').click(function(e) {
                var $button = $(this);
				$button.button('loading');
				
				var permission = $("#modal-permissions select option:selected").val();
				var password = $('#modal-permissions #password input').val();
				var file_alias = $('#modal-permissions #filealias').val();
				
				$.post(
					System.getHostname() + 'api/file/permission',
					{
						'file_alias': file_alias,
						'permission': permission,
						'password' : password
					},
					function(data) {
						$button.button('reset');
						$('#modal-permissions').modal('hide'),
						System.escStack.remove('modal');
						
						var response = $.parseJSON(data);
						
						if(response.success == true) {
							if(permission == "3") {
								$('.share-link').slideUp();
							} else {
								$('.share-link').slideDown();
							}
							
							System.showSuccess(response.message);
						} else {
							System.showError(response.message);
						}
					}
				);
            });
			
			System.escStack.add('modal', function() {
				$('.modal').modal('hide');
			});
			
			return false;
		});
		
		$(".more-info").unbind("click").click(function(){
			if($(".information").is(":visible")) {
				$(".information").slideUp();
				$(this).parent().removeClass("dropup");
			} else {
				$(".information").slideDown();
				$(this).parent().addClass("dropup");				
			}
		});
			
	},
	
	unbindEvents: function() {
		$('#modal-permissions select').unbind('change');
		$('.button-filepermissions').unbind('click');
	}
};

$(document).ready(function(e) {
    File.bindEvents();
});

System.unbindEventCallbacks.add('file', File.unbindEvents);
System.bindEventCallbacks.add('file', File.bindEvents);