function escStackItem(name, callback) {
	this.name = name;
	this.callback = callback;
	
	this.runCallback = function() {
		if(typeof(this.callback) == "function") {
			this.callback();	
		}
	}
}

var System = {
	/**
	 * Prevent all actions when showing modal window
	 */
	preventEvents: false,
	
	/**
	 * Language strings
	 */
	l10n: {
		strings: { },
		
		add: function(key, value) {
			System.l10n.strings[key] = value;
		},
		
		_: function(key) {
			return System.l10n.strings[key];
		}
	},
	
	/**
	 * Settings
	 */
	settings: {
		/**
		 * Time between a sync task is started to prevent logout
		 */
		syncIntervall: 30, // 30 s
		
		/**
		 * Duration, an error message from an AJAX request
		 * is shown in seconds
		 */
		errorMessageDuration: 5, // 5000ms = 5s
	},
	
	/**
	 * Configuration
	 */
	config: {
		httpHost: '',
		modRewrite: true,
		maxUploadSize: 0
	},
	
	/**
	 * Stack for Esc
	 */
	escStack: {
		canExecute: true,
		
		stack: new Array(),
		
		add: function(name, callback) {
			var last = System.escStack.stack.pop();
			
			// prevent multiple esc events
			if(last != null && last.name == name) {
				System.escStack.stack.push(last);
				return;	
			}
			
			if(last != null) {
				System.escStack.stack.push(last);
			}
			
			System.escStack.stack.push(new escStackItem(name, callback));
		},
		
		exec: function() {
			if(System.escStack.canExecute != true) {
				return;	
			}

			var last = System.escStack.stack.pop();
			
			if(last != null) {
				last.runCallback();
			}
		},
		
		remove: function(name) {
			var index = null;
			
			for(var i = 0; i < System.escStack.stack.length; i++) {
				var curElem = System.escStack.stack[i];
				if(curElem.name == name) {
					index = i;
					break;	
				}
			}
			
			if(index != null) {
				System.escStack.stack.splice(index, 1);	
			}
		}
	},
	
	/**
	 * Resize
	 */
	observeResize: {
		callbacks: { },
		
		add: function(key, callback) {
			if(typeof(callback) == "function") {
				System.observeResize.callbacks[key] = callback;
			}
		},
		
		remove: function(key) {
			System.observeResize.callbacks[key] = null;	
		},
		
		execute: function() {
			for(var key in System.observeResize.callbacks) {
				var callback = System.observeResize.callbacks[key];
				
				if(callback != null && typeof(callback) == "function") {
					callback();	
				}
			}
		}
	},

	/**
	 * Helper functions
	 */
	getHostname: function() {
		return System.config.httpHost + (System.config.modRewrite != 1 ? '/index.php/' : '/');
	},
	
	showError: function(message) {
		if(message == null) {
			return;
		}

		if(message.length > 0) {
			$alert = $('.cloneable.alert').clone().removeClass('cloneable').addClass('alert-danger').appendTo($('.alerts'));
			$alert.find('p').html(message);
		}
	},
	
	showSuccess: function(message) {
		if(message == null) {
			return;	
		}
		
		if(message.length > 0) {
			$alert = $('.cloneable.alert').clone().removeClass('cloneable').addClass('alert-success').appendTo($('.alerts'));
			$alert.find('p').html(message);
		}
	},
	
	syncSession: function() {
		if($('body').hasClass('preventautosync')) {
			return;	
		}
		
		$.get(System.getHostname() + 'api', { }, function() {
			setTimeout(System.syncSession, System.settings.syncIntervall * 1000);
		});
	},
	
	formatBytes: function(size, precision) {
		var units = new Array('Byte', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
		for (var i = 0; size >= 1024 && i < 4; i++) size /= 1024;
		
		if(precision == null || precision == 0) {
			return Math.round(size) + ' ' + units[i];
		} else {
			var size = "" + size;
			var pos = size.indexOf('.');
			
			return size.substr(0, pos + precision + 1) + ' ' + units[i];
		}
	},
	
	bindEventCallbacks: {
		callbacks: { },
		
		add: function(key, callback) {
			if(System.bindEventCallbacks.callbacks[key] == null) {
				System.bindEventCallbacks.callbacks[key] = callback;	
			}
		},
		
		execute: function() {
			for(var key in System.bindEventCallbacks.callbacks) {
				if(typeof(System.bindEventCallbacks.callbacks[key]) == "function") {
					System.bindEventCallbacks.callbacks[key]();
				}
			}
		}
	},
	
	unbindEventCallbacks: {
		callbacks: { },
		
		add: function(key, callback) {
			if(System.unbindEventCallbacks.callbacks[key] == null) {
				System.unbindEventCallbacks.callbacks[key] = callback;	
			}
		},
		
		execute: function() {
			for(var key in System.unbindEventCallbacks.callbacks) {
				if(typeof(System.unbindEventCallbacks.callbacks[key]) == "function") {
					System.unbindEventCallbacks.callbacks[key]();
				}
			}
		}
	},
	
	bindEvents: function() {
		jQuery.event.props.push('dataTransfer');
		System.observeResize.execute();
		
		$(document).bind('dragover', function(e) {
			e.stopPropagation();
			e.preventDefault();
			
			e.dataTransfer.dropEffect = 'copy';	
		});
		
		$(document).bind('drop', function(e) {
			e.stopPropagation();
			e.preventDefault();
			
			if(e.dataTransfer != null) {
				var files = e.dataTransfer.files;
				
				if(files != null) {			
					for(var i = 0; i < files.length; i++) {
						Uploads.uploadFile(files[i]);
					}

					// Visual feedback while uploading file
					if(!$('.button-status').parent().is('.open')) {
						$('.button-status').dropdown('toggle');
					}
				}
			}
			
			return false;
		});
		
		$(window).resize(function() {
			System.observeResize.execute();
		});
		
		$(document).keydown(function(e) {
			// Escape events
			if(e.keyCode == 27) {
				System.escStack.exec();
			}
        });
		
		$('a').click(function(e) {
			var href = $(this).attr('href');
			var noajax = $(this).attr('data-noajax'); // in some cases we must prevent ajax loading
			var target = $(this).attr('target'); // another case we have to prevent ajax loading
			
			if(typeof(href) != "undefined" && href != "#" && noajax != "true" && target != "_blank") {
				e.preventDefault();				
				History.pushState(null, null, href);
			}
        });
		
		$('form').submit(function(e) {
            var action = $(this).attr('action');
			var noajax = $(this).attr('data-noajax'); // in some cases we must prevent ajax loading
			
			if(typeof(action) != "undefined" && action != "#" && noajax != "true") {
				e.preventDefault();
				
				$(this).find('input[type=submit]').addClass('loading');	
				
				/**
				 * Problem with History.js: when new url and current url
				 * are the same, statechanged() is not triggered :/
				 * In case of someone finds a better solution: feel free to
				 * commit :-)
				 */
				if(window.location.href == action) {
					$.post(action, $(this).serialize() + '&submit=submit', System.ajaxCallback);
				} else {
					History.pushState({'postData': $(this).serialize() + '&submit=submit'}, null, action);
				}
			}
        });

		// Tooltips
		$('a[title]').tooltip();
		$('i[title]').tooltip();
		
		// Autofocus
		$('input[data-autofocus]').focus();
		
        $(".unselectable, .button").unselectable();
		
		$(".selectAll").click(function(){
		    $(this).select();
		});
		
		$('a.cancel-upload').unbind('click').click(function(e) {
			Uploads.cancelUpload($(this).data('upload-id'));
			return false;
		});
		
		System.observeResize.add('ajax-message', function() {
			$('.ajax-message').css({
				left: $(document).width() / 2 - $('.ajax-message').width() / 2
			});
		});
		
		System.bindEventCallbacks.execute();
	},
	
	unbindEvents: function() {
		$('a').unbind();
		$('form').unbind('submit');
		$(document).unbind('resize mousedown keydown dragover drop');
		
		System.observeResize.callbacks = { };
		System.escStack.stack = new Array();
		
		System.unbindEventCallbacks.execute();	
	},
	
	ajaxCallback: function(data) {
		System.unbindEvents();
		
		var html = $(data);

		// Load content
		$('.main').html(html.find('.main').html());

		$('title').html(html.filter('title').html());

		html.filter('script').each(function(index, element) {
			// Test if script file must be loaded
			var src = $(this).attr('src');
			
			if(typeof(src) != "undefined" && $('script[src="' + src + '"]').length == 0) {
				$('head').append($(this));
			}
		});
		
		html.filter('link[rel=stylesheet]').each(function(index, element) {
			// Test if stylesheet must be loaded
			var href = $(this).attr('href');
			
			if(typeof(href) != "undefined" && $('link[href="' + href + '"]').length == 0) {
				$('head').append($(this));	
			}
		});

		// bind File events on every preview and check if File is "our" Object
		if(typeof (File) !== 'undefinied' && File.unbindEvents === 'function' && File.bindEvents === 'function') {
			File.unbindEvents();
			File.bindEvents();
		}

		// Load navigation
		$('.nav.navbar-nav:first()').html(html.find('.nav.navbar-nav:first()').html());
		
		// Load message
		$('.alerts').html(html.find('.alerts').html());
		
		$('body').css('cursor', 'auto');
		
		System.bindEvents();
	},
	
	api: function(endpoint, data, success) {
		if(typeof(data) === 'undefined') {
			data = { };	
		}
		
		if(typeof(success) !== 'function') {
			success = function() { };
		}
		
		$.ajax({
			method: 'POST',
			dataType: 'json',
			contentType: 'application/json',
			url: System.getHostname() + endpoint,
			data: JSON.stringify(data),
			success: function(data) {
				success(data);
			}
		});
	}
};

$(document).ready(function(e) {
    System.bindEvents();
	System.syncSession();
});

History.Adapter.bind(window, 'statechange', function(e) {
	var state = History.getState();
	var postData = state.data.postData;
	
	$('body').css('cursor', 'progress');
	
	if(typeof(postData) != "undefined") {
		$.post(state.url, postData, System.ajaxCallback);
	} else {
		$.get(state.url, { }, System.ajaxCallback);	
	}
});