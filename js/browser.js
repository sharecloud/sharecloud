// JavaScript Document
var Browser = {
	Settings: {
		File: {
			NameClass: 'filename',
			SizeClass: 'size',
			NumDownloadsClass: 'num-downloads',
			StatusClass: 'progress'
		},
		Folder: {
			NameClass: 'filename'	
		}
	},
	
	CreateFolderEvents: {
		opened: false,
		
		open: function() {
			if(System.preventEvents) {
				return;	
			}
			
			if(Browser.CreateFolderEvents.opened == true) {
				Browser.CreateFolderEvents.cancel();
				return;	
			}
			
			Browser.CreateFolderEvents.opened = true;
			
			$('.create-folder').slideDown();			
			$('.create-folder input').focus();
			
			System.escStack.add('create-folder', Browser.CreateFolderEvents.cancel);
		},
		
		cancel: function() {
			System.escStack.remove('create-folder');
			
			Browser.CreateFolderEvents.opened = false;
			
			$('.create-folder').slideUp();
			$('.create-folder input').val('');
		},
		
		submit: function() {
			System.api(
				'api/folders/add', 
				{
					'name': $('#create-folder-input').val(),
					'parent_id': $('#create-folder-input').data('parent')	
				},
				function(response) {
					try {
						if(response.success == true) {
							var copy = $('.cloneable .row.folder').clone(false);
								
							copy.find('.' + Browser.Settings.Folder.NameClass).html('<a href="' + response.data.url + '">' + response.data.name + '</a>');
							copy.data('id', response.data.id);
							
							var insert = null;
							
							$('.browser .row.folder').not('.header, .create-folder').each(function(index, element) {
								if($(this).find('.' + Browser.Settings.Folder.NameClass + ' a').html() <= response.data.name) {
									insert = $(this);	
								}
							});
							
							if(insert == null) {
								if($('.browser .row').not('.header, .create-folder').length > 0) {
									copy.hide().insertBefore($('.browser .row').not('.header, .create-folder').first()).show('highlight', 1000);
								} else {
									copy.hide().insertAfter($('.browser .row.create-folder')).show('highlight', 1000);
								}
							} else {
								copy.hide().insertAfter(insert).show('highlight', 1000);
							}
							
							Browser.CreateFolderEvents.cancel();
							
							// Rebind all events
							System.unbindEvents();
							System.bindEvents();
						} else {
							System.showError(response.message);
						}
					} catch(e) {
						console.debug("Exception: " + e);
						System.showError(System.l10n._('UnknownError'));
					}
				}
			);				
		}
	},
	
	RenameEvents: {
		open: function() {
			if(System.preventEvents || Browser.Selection.length() != 1) {
				return;	
			}
			
			var currentName = $('.browser .row.selected').find('a').text();
			
			// Clone form, show it up instead of foldername
			$('.browser .row.selected').find('a,span').hide();
			var form = $('.form-rename-template').clone().removeClass('form-rename-template').addClass('form-rename inline').show().insertAfter($('.browser .row.selected').find('a').first());
			
			// Set value of input
			var input = form.find('.input-filename');

			input.val(currentName);
			input.focus();
			
			// Ignore file extension (if file)
			if($('.browser .row.selected').hasClass('file')) {
				var index = currentName.lastIndexOf('.');
				
				if(index != -1) {
					input[0].setSelectionRange(0, index);
				} else {
					input.select();	
				}
			} else {			
				input.select();
			}
			
			input.keypress(function(e) {
                if(e.keyCode == 13) {
					e.preventDefault();
					input.attr('disabled', true);
					Browser.RenameEvents.submit();	
				}
            });
			
			System.escStack.add('rename', function() {
				Browser.RenameEvents.cancel();
			});
		},
		
		cancel: function() {
			// Delete form
			$('.browser .row').find('form').remove();
			
			// Show links
			$('.browser .row .filename').find('a,span').show();
		},
		
		submit: function() {
			var input = $('.browser .row .form-rename').find('.input-filename');
			var row = $('.browser .row .form-rename').parent().parent();	
			
			$('.browser .row .form-rename .indicator').show();
			
			System.preventEvents = true;
			
			System.api(
				'api/browser/rename',
				{
					'folder_id': (row.hasClass('folder') ? row.data('id') : null),
					'file_id': (row.hasClass('file') ? row.data('id') : null),
					'name': input.val()
				},
				function(response) {
					try {
						if(response.success == true) {
							var index = input.val().lastIndexOf('.');
							if(index != -1) {
								row.find('.filename a .filename').html(input.val().substring(0, index));
								row.find('.filename a .ext').html(input.val().substring(index));
							} else {
								row.find('.filename a').html(input.val());
							}
								
						} else {
							System.showError(response.message);	
						}
					} catch(e) {
						console.debug("Exception: " + e);
						System.showError(System.l10n._('UnknownError'));	
					} finally {
						System.preventEvents = false;					
						Browser.RenameEvents.cancel();	
					}
				}
			);
		}
	},
	
	Selection: {
		isEmpty: function() {
			return Browser.Selection.length() == 0;
		},
		
		length: function() {
			return $('.browser .row.selected').length;	
		},
		
		unselectAll: function() {
			if(System.preventEvents) {
				return;	
			}
			
			$('.browser .row').removeClass('selected');
			
			Browser.Selection.updateMenu();
		},
		
		selectAll: function() {
			if(System.preventEvents) {
				return;	
			}
			
			System.escStack.add('unselect', Browser.Selection.unselectAll);
			
			$('.browser .row:not(.upload)').each(function(index, element) {
                if(!$(this).hasClass('header') && !$(this).hasClass('create-folder')) {
					$(this).addClass('selected');
				}
            });
			
			Browser.Selection.updateMenu();
		},
		
		selectUp: function() {
			if(System.preventEvents) {
				return;	
			}
			
			var elem = $('.browser .row.last');
			
			if(elem.length == 1) {
				var next = elem.prev('.row');
			} else {
				var next = $('.browser .row').not('.header, .create-folder').last();
			}
			
			if(next.length == 1) {	
				Browser.Selection.select(next, false, false);
			}
		},
		
		selectDown: function() {
			if(System.preventEvents) {
				return;	
			}
			
			var elem = $('.browser .row.last');
			
			if(elem.length == 1) {
				var next = elem.next('.row');
			} else {
				var next = $('.browser .row').not('.header, .create-folder').first();
			}
			
			if(next.length == 1) {		
				Browser.Selection.select(next, false, false);
			}
		},
		
		select: function(element, crtlKey, shiftKey) {
			if(System.preventEvents) {
				return;	
			}
			
			System.escStack.add('unselect', Browser.Selection.unselectAll);
			
			// Do nothing if its the header-row
			if($(element).hasClass('header') || $(element).hasClass('create-folder')) {
				return;	
			}
			
			var addClass = !$(element).hasClass('selected');
			
			// Unselect all items if necessary
			if(crtlKey != true && shiftKey != true) {
				Browser.Selection.unselectAll();
			}
			
			if(shiftKey == true) {
				var current = $(element);
				var last = $('.browser .row.last');

				if(last.length != 0) {
					if(last.index() < current.index()) {
					    minorIndex = last.index() + 1;
					    majorIndex = current.index();
					} else {
                        majorIndex = last.index() - 1;
                        minorIndex = current.index();
					}
					for(var index = minorIndex; index <= majorIndex; index++  ) {
					    $($('.browser .row').get(index)).toggleClass('selected');
					}
					
					$('.browser .row').removeClass('last');
					current.addClass('last');
				}
			} else {
				$('.browser .row').removeClass('last');	
						
				if(addClass) {
					$(element).addClass('selected').addClass('last');	
				} else {
					$(element).removeClass('selected').addClass('last');	
				}	
			}
			
			Browser.Selection.updateMenu();
		},
		
		invert: function() {
			$('.browser .row').not('.header, .create-folder').each(function(index, element) {
                $(this).toggleClass('selected');
            });
			
			Browser.Selection.updateMenu();
		},
		
		updateMenu: function() {
			if(Browser.Selection.length() == 1) {
				$('.button-rename').removeClass('disabled');	
			} else {
				$('.button-rename').addClass('disabled');	
			}
			
			if(!Browser.Selection.isEmpty()) {
				$('.button-move, .button-delete').removeClass('disabled');	
			} else {
				$('.button-move, .button-delete').addClass('disabled');	
			}
			
			Browser.ToggleInvertSelectionButton();
		},
		
		deleteSelected: function() {
			var folders = [];
			var files = [];
			
			$('.browser .row').each(function(index, element) {
                if($(this).hasClass('selected')) {
					if($(this).hasClass('file')) {
						files.push($(this).data('id'));
					} else if($(this).hasClass('folder')) {
						folders.push($(this).data('id'));
					}
				}
            });
			
			$('#modal-delete .button-confirm').unbind('click').click(function() {
				var $button = $(this);
				$button.button('loading');
				
				System.api(
					'api/browser/delete',
					{
						'folders': folders,
						'files': files	
					}, 
					function(response) {
						$button.button('reset');
						$('#modal-delete').modal('hide');
						System.escStack.remove('modal');
						
						try {
							if(response.success == true) {
								$('.browser .row.selected').hide('blind', { }, 400, function() {
									$(this).remove();
									Browser.Selection.updateMenu();
									Browser.ShowNoFilesInfo();
								});
							} else {
								System.showError(response.message);	
							}
						} catch(e) {
							console.debug("Exception: " + e);
							System.showError(System.l10n._('UnknownError'));
						}
					}
				);
			});
			
			$('#modal-delete').modal({
				keyboard: false
			}).modal('show');
			
			System.escStack.add('modal', function() {
				$('.modal').modal('hide');
			});
		},
		
		moveSelected: function(target, callback) {
			var folders = [];
			var files = [];
			
			$('.browser .row').each(function(index, element) {
                if($(this).hasClass('selected')) {
					if($(this).hasClass('file')) {
						files.push($(this).data('id'));
					} else if($(this).hasClass('folder')) {
						folders.push($(this).data('id'));
					}
				}
            });
			
			System.api(
				'api/browser/move', 
				{
					'folders': folders,
					'files': files,
					'target': target
				},
				function(response) {
					try {
						if(typeof(callback) == "function") {
							callback(response);	
						}
						
						if(response.success == true) {
							$('.browser .row.selected').hide('blind', { }, 400, function() {
								$(this).remove();
								Browser.Selection.updateMenu();
								Browser.ShowNoFilesInfo();
							});
							
							System.api(
								'api/folders/folderSize', 
								{
									'folder_id': target
								}, 
								function(response){
									if(response.success == true) {
										$('.row.folder[data-id='+target+'] .column.size').text(response.message);
									}
								}
							);
							
						} else {
							System.showError(response.message);	
						}
					} catch(e) {
						console.debug("Exception: " + e);
						System.showError(System.l10n._('UnknownError'));	
					}
				}
			);
		}
	},
	
	CurrentFolderId: function() {
		return ($('.browser').data('id') != "" ? $('.browser').data('id') : null);
	},
	
	ShowNoFilesInfo: function() {
		if($('.browser .row.file').length == 0 && $('.browser p.no-files').length == 0) {
			$('<p class="no-files">' + System.l10n._('NoFiles') + '</p>').appendTo('.browser').hide().slideDown(500);
		}
	},
	
	ToggleInvertSelectionButton: function() {
		if($('.browser .row').not('.header, .create-folder').length == 0) {
			$('.button-invert-selection').addClass('disabled');
		} else {
			$('.button-invert-selection').removeClass('disabled');
		}
	},
	
	bindEvents: function() {
		$('body').unbind('keydown').keydown(function(e) {
			// Select all entries on Ctrl+A
			if((e.ctrlKey || e.metaKey) && e.keyCode == 65) {
				if($(e.target).is('input, textarea')) {
					return;	
				}
				
				e.preventDefault();
				
				Browser.Selection.selectAll();
			}
			
			// Ctrl+N
			if((e.ctrlKey || e.metaKey) && e.keyCode == 78) {
				e.preventDefault();
				
				Browser.CreateFolderEvents.open();	
			}
			
			// Arrow up
			if(e.keyCode == 38) {
				e.preventDefault();
				
				Browser.Selection.selectUp();	
			}
			
			// Arrow down
			if(e.keyCode == 40) {
				e.preventDefault();
				
				Browser.Selection.selectDown();	
			}
			
			// Del
			if(e.keyCode == 46) {
				e.preventDefault();
				
				Browser.Selection.deleteSelected();
			}
        });	
			
		$('body').mouseup(function(e) {
			if(System.preventEvents) {
				return;	
			}
		   
		    var container = $('.browser, .btn:not(.disabled), .modal');
			
			if(!container.is(e.target) && container.has(e.target).length === 0) {
				Browser.Selection.unselectAll();
			}
			
			var container = $('.browser .row form.inline');
			
			if(!container.is(e.target) && container.has(e.target).length === 0) {
				Browser.RenameEvents.cancel();
			}
        });
		
		$('.browser').unselectable();
		
		/**
		 * File selection
		 */
		$('.browser .row').unbind('click').click(function(e) {
			Browser.Selection.select(this, e.ctrlKey || e.metaKey, e.shiftKey);
		});
		
		/**
		 * Create folder
		 */
		$('.create-folder input').unbind('keypress').keypress(function(e) {
            if(e.keyCode == 13) {
				Browser.CreateFolderEvents.submit();
			}
        }); 
		
		$('.button-create-folder').unbind('click').click(function(e) {
            Browser.CreateFolderEvents.open();
						
			return false;
        });
		
		/**
		 * Drag'n'drop
		 */
		$('.browser .row').not('.create-folder, .header').draggable({
			distance: 10,			
			cursorAt: {
				top: -20,
				left: -20
			},
			helper: function() {
				return $('<div>', {
					html: 'Helper'
				}).addClass('dragHelper');
			},
			start: function(event, ui) {
				if(!$(this).hasClass('selected')) {
					Browser.Selection.select(this, false, false);	
				}
				
				var num = $('.browser .row.selected').length;
								
				$('.dragHelper').html(num + ' ' + (num == 1 ? System.l10n._('Element') : System.l10n._('Elements')));
				
				System.preventEvents = true;
			},
			stop: function(event, ui) {
				System.preventEvents = false;
			}
		});
		
		$('.browser .row.folder').droppable({
			hoverClass: 'drop-hover',
			tolerance: 'pointer',
			accept: function() {
				return !$(this).hasClass('selected');
			},
			drop: function(event, ui) {
				var target = $(this).data('id');
				Browser.Selection.moveSelected(target == "" ? null : target);
				
				$(this).find('.glyphicon').removeClass("glyphicon-folder-open");
				$(this).find('.glyphicon').addClass("glyphicon-folder-close");
				
				return false;
			},
			over: function(event, ui) {
				$(this).find('.glyphicon').removeClass("glyphicon-folder-close");
				$(this).find('.glyphicon').addClass("glyphicon-folder-open");
				
			}, 
			out: function(event, ui) {
				$(this).find('.glyphicon').removeClass("glyphicon-folder-open");
				$(this).find('.glyphicon').addClass("glyphicon-folder-close");
				
			}
		});
		
		$('.breadcrumb li').droppable({
			hoverClass: 'drop-hover',
			tolerance: 'pointer',
			accept: function() {
				return !$(this).is(":last-child");
			},
			drop: function(event, ui) {
				var target = $(this).data('folder-id');
				Browser.Selection.moveSelected(target == "" ? null : target);
				
				return false;
			}
		});
		
		
		
		/**
		 * Buttons
		 */
		$('.button-delete').unbind('click').click(function(e) {
			if($(this).parent().hasClass('disabled')) {
				return false;	
			}
			
			Browser.Selection.deleteSelected();
			
			return false;
		});
		
		$('.button-move').unbind('click').click(function(e) {
			if($(this).parent().hasClass('disabled')) {
				return false;	
			}
			
			$('#modal-move .button-confirm').unbind('click').click(function(e) {
                $button = $(this);
				$button.button('loading');
				
				var target = $('.select-move').val();
				
				Browser.Selection.moveSelected(target == "" ? null : target, function() {
					$button.button('reset');
					System.escStack.remove('modal');					
					$('#modal-move').modal('hide');
				});
            });
			
			$('#modal-move').modal({
				keyboard: false
			}).modal('show');
			
			System.escStack.add('modal', function() {
				$('.modal').modal('hide');
			});
			
			return false;
		});
		
		$('.button-invert-selection').unbind('click').click(function(e) {
			Browser.Selection.invert();
			
			return false;
		});
		
		$('.button-rename').unbind('click').click(function(e) {
			Browser.RenameEvents.open();			
			
			return false;
		});
		
		$('a.button-upload-file').unbind('click').click(function(e) {
			$('.input-file-upload').click();
			
			return false;
		});
		
		$('a.button-remote-download').unbind('click').click(function(e) {
			$('#modal-download .button-confirm').unbind('click').click(function(e) {
                var url = $('#input-url').val();
				var filename = $('#input-filename').val();
				
				Uploads.downloadRemote(url, filename);
				
				$('#modal-download').modal('hide');
				
				$('#input-url').val('');
				$('#input-filename').val('');
            });
			
			$('#modal-download').modal({
				keyboard: false
			}).modal('show');
			
			System.escStack.add('modal', function() {
				$('.modal').modal('hide');
			});
			
			return false;
		});
		
		// Listen for the file picker
		$('.input-file-upload').unbind('change').change(function(e) {
			var files = $(this)[0].files;
			
			if(typeof(files) != "undefined") {
				for(var i = 0; i < files.length; i++) {
					Uploads.uploadFile(files[i]);	
				}
			}
		});
		
		Browser.ToggleInvertSelectionButton();
	},
	
	unbindEvents: function() {
		$('.browser .row.ui-droppable').droppable('destroy');
		$('.browser .row.ui-draggable').draggable('destroy');
		$('.browser *').unbind();
		$('.buttons *').unbind();
		
		$('body').unbind('keydown mouseup');
	},
	
	rebindEvents: function() {
		Browser.unbindEvents();
		Browser.bindEvents();	
	}
};

System.unbindEventCallbacks.add('browser', Browser.unbindEvents);
System.bindEventCallbacks.add('browser', Browser.bindEvents);