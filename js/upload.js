// JavaScript Document

function FileUpload() {
	this.id = 0;
	this.filename = '';
	
	this.deltaUploaded = 0;
	this.uploaded = 0;
	this.size = 0;
	
	this.isIndeterminate = false;
	
	this.inDOM = false;
	
	this.getProgress = function() {
		if(this.size == 0) {
			return 0;	
		}
		
		return this.uploaded / this.size;
	}
	
	this.xhr = null;
}

var Uploads = {
	Settings: {
		File: {
			NameClass: 'filename',
			StatusClass: 'progress-status'
		}
	},
	
	currentUploads: { },
	
	createHash: function() {
		var text;
		var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		
		do {
			text = '';
			
			for(var i = 0; i < 5; i++) {
				text += possible.charAt(Math.floor(Math.random() * possible.length));
			}
		} while(typeof(Uploads.currentUploads[text]) != "undefined");
		
		// Reserve hash
		Uploads.currentUploads[text] = { };
		
		return text;
	},
	
	downloadRemote: function(url, filename) {
		var id = Uploads.createHash();
		
		var fileObj = new FileUpload();
		fileObj.id = id;
		fileObj.filename = filename;
		fileObj.isIndeterminate = true;
		
		Uploads.currentUploads[id] = fileObj;
		
		fileObj.xhr = $.post(System.getHostname() + 'api/remote', {
			'url': url,
			'filename': filename,
			'folder': (typeof(Browser) != "undefined" ? Browser.CurrentFolderId() : null)
		}, function(response) {
			Uploads.uploadCompleted(id, response);
		});
	},
	
	uploadFile: function(file) {
		var id = Uploads.createHash();
		
		var fileObj = new FileUpload();
		fileObj.id = id;
		fileObj.filename = file.name;
		fileObj.size = file.size;
		
		// Check quota and then upload
		$.getJSON(System.getHostname() + 'api/quota', { }, function(response) {
			if(response.data.quota > 0 && response.data.available < fileObj.size) {
				System.showError(System.l10n._('ErrorQuotaExceeded'));
				Uploads.currentUploads[id] = null;
			} else {
				var folder  =(typeof(Browser) != "undefined" ? Browser.CurrentFolderId() : null);
				
				fileObj.xhr = new XMLHttpRequest();
				
				fileObj.xhr.onload = function() {
					Uploads.uploadCompleted(id, this.responseText);
				};
				
				fileObj.xhr.upload.onprogress = function(e) {
					if(e.lengthComputable) {
						fileObj.deltaUploaded = e.loaded - fileObj.uploaded;
						fileObj.uploaded = e.loaded;
					}
				};
				
				var endpoint = 'api/upload' + (folder != null ? '/' + folder  : '') + '/' + file.name;
				
				fileObj.xhr.open('PUT', System.getHostname() + endpoint, true);
				fileObj.xhr.setRequestHeader('Content-Type', file.type);
				fileObj.xhr.send(file);				
				
				Uploads.currentUploads[id] = fileObj;
				
				Uploads.updateUI();
			}
		});
	},
	
	uploadCompleted: function(id, data) {
		try {
			var data = $.parseJSON(data);
			
			if(data.success == true) {
				if(typeof(Browser) != null) {
					data = data.data;
					
					var file = Uploads.currentUploads[id];
					
					var html = $('.cloneable .row.file').clone().removeClass('cloneable').attr('data-alias', data.alias).attr('data-id', data.id);
					if(data.ext != '.') {
						html.find('.' + Browser.Settings.File.NameClass).html('<span class="glyphicon glyphicon-file"> </span> <a class="file" href="' + data.url + '"><span class="filename">' + data.filename.substring(0, data.filename.lastIndexOf('.')) + '</span><span class="ext">.' + data.ext + '</span></a>');
					} else {
						html.find('.' + Browser.Settings.File.NameClass).html('<span class="glyphicon glyphicon-file"> </span> <a class="file" href="' + data.url + '"><span class="filename">' + data.filename + '</span></a>');
					}
					html.find('.' + Browser.Settings.File.SizeClass).html(System.formatBytes(data.size));
					html.find('.' + Browser.Settings.File.NumDownloadsClass).html(data.downloads);
					
					var folderid = (data.folderid == null ? '' : data.folderid);
					
					// Insert new entry
					var insert = null;
					
					if($('.browser[data-id=' + folderid + '] .row.folder').length == 0) {
						insert = $('.browser[data-id=' + folderid + '] .create-folder');
					} else {
						insert = $('.browser[data-id=' + folderid + '] .row.folder').last();	
					}
								
					$('.browser[data-id=' + folderid + '] .row.file:not(.upload)').each(function(index, element) {
						if($(this).find('.' + Browser.Settings.File.NameClass + ' a').html() <= data.filename) {
							insert = $(this);
						}
					});
					
					$('.browser[data-id=' + folderid + '] .no-files').remove();
					html.insertAfter(insert).hide().show('highlight', 1000);
					
					System.unbindEvents();
					System.bindEvents();
				}
			} else {
				System.showError(data.message);	
			}
		} catch(e) {
			console.debug("Exception: " + e);
			System.showError(System.l10n._('UnknownError'));	
		} finally {	
			// Remove old entry
			$('#upload-progress .entry[data-upload-id=' + id + ']').remove();
			
			Uploads.currentUploads[id] = null;
		}
	},
	
	cancelUpload: function(id) {
		if(typeof(Uploads.currentUploads[id]) != "undefined") {
			var file = Uploads.currentUploads[id];
			
			file.xhr.abort();
			Uploads.currentUploads[id] = null;
			
			$('#upload-progress .entry[data-upload-id=' + id + ']').remove();
		}
	},
	
	updateUI: function() {		
		var numUploads = 0;
		var numPercentage = 0;
		var numRemoteDownloads = 0;		
		
		for(var id in Uploads.currentUploads) {
			var file = Uploads.currentUploads[id];
			
			if(file == null || typeof(file.filename) == "undefined") {
				continue;	
			}
			
			if(!file.inDOM) {
				// Insert Upload
				var html = $('#upload-progress .cloneable.entry').clone().removeClass('cloneable').show();
				html.attr('data-upload-id', id);			
				html.appendTo($('#upload-progress')).show();
				
				html.find('a.cancel-upload').attr('data-upload-id', id).unbind('click');				
				file.inDOM = true;
				
				$('#upload-progress .no-uploads').hide();
				
				System.unbindEvents();
				System.bindEvents();
			}
			
			if(file.isIndeterminate) {
				// Remote Downloads
				
				var elem = $('#upload-progress .entry[data-upload-id=' + id + ']');
				elem.find('.' + Uploads.Settings.File.NameClass).html(file.filename);
				
				var percent = Math.round(file.getProgress() * 100);
				
				elem.find('.progress').addClass('progress-striped').addClass('active');
				elem.find('.progress-bar').css('width', '100%');
				elem.find('.sr-only').html('0 %');
				
				numRemoteDownloads++;
			} else {
				// Actual uploads
				
				var elem = $('#upload-progress .entry[data-upload-id=' + id + ']');
				elem.find('.' + Uploads.Settings.File.NameClass).html(file.filename);
				
				var percent = Math.round(file.getProgress() * 100);
				
				elem.find('.progress-bar').attr('aria-valuenow', percent);
				elem.find('.progress-bar').css('width', percent + '%');
				elem.find('.sr-only').html(percent + ' %');
						
				elem.find('.' + Uploads.Settings.File.StatusClass).html(System.formatBytes(file.uploaded, 0) + " / " + System.formatBytes(file.size, 0) + " - " + Math.round(file.getProgress() * 100) + ' % [' + Uploads.calcSpeed(file.deltaUploaded, 500) + ']');
				numPercentage += percent;
			}
			
			numUploads++;
		}
		
		if(numUploads > 0) {
			var strUpload = System.l10n._('UploadPlural');
			
			if(numUploads == 1) {
				strUpload = System.l10n._('UploadSingular');
			}
			
			if(numUploads != numRemoteDownloads) {			
				$('.button-status').html(numUploads + ' ' + strUpload + ' (' + (numPercentage / (numUploads - numRemoteDownloads)) + ' %)');
			} else {
				$('.button-status').html(numUploads + ' ' + strUpload);
			}
		} else {
			$('.button-status').html(System.l10n._('NoUploads'));
			$('#upload-progress p.no-uploads').show();
		}
	},
	
	calcSpeed: function(deltaSize, deltaTime) {
		if(deltaTime == 0) {
			return '0 B/s';	
		}
		
		var deltaSize = Math.abs(deltaSize);
		var factor = 1000 / deltaTime;
		
		return System.formatBytes(factor * deltaSize, 2) + '/s';
	}
};

// Update UI every 500ms
setInterval(Uploads.updateUI, 500);