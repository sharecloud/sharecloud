/******************************************************************************\
*                                                                              *
* The MIT License (MIT)                                                        *
*                                                                              *
* Copyright (c) 2013 Felix Wehnert                                             *
*                                                                              *
* Permission is hereby granted, free of charge, to any person obtaining a copy *
* of this software and associated documentation files (the "Software"), to deal*
* in the Software without restriction, including without limitation the rights *
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell    *
* copies of the Software, and to permit persons to whom the Software is        *
* furnished to do so, subject to the following conditions:                     *
*                                                                              *
* The above copyright notice and this permission notice shall be included in   *
* all copies or substantial portions of the Software.                          *
*                                                                              *
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR   *
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,     *
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE  *
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER       *
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,*
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN    *
* THE SOFTWARE.                                                                *
*                                                                              *
\******************************************************************************/
/**
 * jQuery Plugin unselectable
 * 
 * With this plugin you are able to make HTML Elements unselectable.
 * 
 * Compatible with all popular Browsers
 * 
 * Example:
 * $(".button, label").unselectable();
 * 
 * That's it!
 * 
 */


(function($){
  $.fn.unselectable = function() {
  	
    this.css({
    	"-webkit-user-select": "none", /* Chrome & Safari */
		"-khtml-user-select": "none", /* Konqueror */
		"-moz-user-select": "none", /* Firefox */
		"-ms-user-select": "none", /* IE10+ */
		"-o-user-select": "none", /* Opera (not implemented yet) */
		
		"user-select": "none" /* W3C */
    });
    
    this.attr("unselectable", "on"); /* Opera + IE 7-9 */
	
	this.on("onSelectStart", function(){ /* All */
			return false;
	});
	
  };
})( jQuery );