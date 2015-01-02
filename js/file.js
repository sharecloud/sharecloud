var File = {
	bindEvents: function() {
		$(".copy").each(function (i) {
			var clip = new ZeroClipboard(document.getElementById($(this).attr("id")), {
			  moviePath: System.config.httpHost + "/js/ZeroClipboard.swf"
			});
			clip.on('complete', function(client, args) {
				$(".copy span").each(function(index, value) {
					$(value).removeClass("fa-check").addClass("fa-paperclip");
				});
				$("#"+$(this).attr("id")+" i").removeClass("fa-paperclip").addClass("fa-check");
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

		hljs.tabReplace='    ';
		// hljs.lineNodes=true; // has no effect with hljs.highlightBlock() :(
		$('pre code').each(function(i, block) {
			hljs.highlightBlock(block);
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
				
				System.api(
					'api/file/permission',
					{
						'file_alias': file_alias,
						'permission': permission,
						'password' : password
					},
					function(response) {
						$button.button('reset');
						$('#modal-permissions').modal('hide'),
						System.escStack.remove('modal');
						
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
			
			return false;
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