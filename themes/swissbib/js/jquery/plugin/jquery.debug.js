 /*
 * Simple jQuery logger / debugger.
 * Based on: http://jquery.com/plugins/Authoring/
 * See var DEBUG below for turning debugging/logging on and off.
 *
 * @version   20070111
 * @since     2006-07-10
 * @copyright Copyright (c) 2006 Glyphix Studio, Inc. http://www.glyphix.com
 * @author    Brad Brizendine <brizbane@gmail.com>
 * @license   MIT http://www.opensource.org/licenses/mit-license.php
 * @requires  >= jQuery 1.0.3
 */
// global debug switch ... add DEBUG = true; somewhere after jquery.debug.js is loaded to turn debugging on
var DEBUG = false;
// shamelessly ripped off from http://getfirebug.com/
/*
if (!("console" in window) || !("firebug" in console)){
	var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml", "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];
	// create the logging div
	jQuery(document).ready(
		function(){
			$(document.body).append('<div id="DEBUG"><ol></ol></div>');
		}
	);
	// attach a function to each of the firebug methods
	window.console = {};
	for (var i = 0; i < names.length; ++i){
		window.console[names[i]] = function(msg){ $('#DEBUG ol').append( '<li>' + msg + '</li>' ); }
	}
}
*/
/*
 * debug
 * Simply loops thru each jquery item and logs it
 */
jQuery.fn.debug = function() {
	return this.each(function(){
		$.log(this);
	});
};

/*
 * log
 * Send it anything, and it will add a line to the logging console.
 * If firebug is installed, it simple send the item to firebug.
 * If not, it creates a string representation of the html element (if message is an object), or just uses the supplied value (if not an object).
 */
jQuery.log = function(message){
	// only if debugging is on
	if( window.DEBUG ){
		// if no firebug, build a debug line from the actual html element if it's an object, or just send the string
		var str = message;
		if( !('firebug' in console) ){
			if( typeof(message) == 'object' ){
				str = '&lt;';
				str += message.nodeName.toLowerCase();
				for( var i = 0; i < message.attributes.length; i++ ){
					str += ' ' + message.attributes[i].nodeName.toLowerCase() + '="' + message.attributes[i].nodeValue + '"';
				}
				str += '&gt;';
			}
		}
		console.debug(str);
	}
};
/*
 * dump
 * Arguments: The data - array,hash(associative array),object
 *    The level - OPTIONAL
 * Returns  : The textual representation of the array.
 * This function was inspired by the print_r function of PHP.
 * This will accept some data as the argument and return a
 * text that will be a more readable version of the
 * array/hash/object that is given.
 */
jQuery.fn.dump = function() {
	return this.each(function(){
	    $.log(dumpIt(this));
	});
};
function dumpIt(arr,level) {
    var dumped_text = "";
    if(!level) level = 0;
    var level_padding = "";
    for(var j=0;j<level+1;j++) level_padding += "    ";
        if(typeof(arr) == 'object') {
            for(var item in arr) {
                var value = arr[item];
                if(typeof(value) == 'object') {
                    dumped_text += level_padding + "'" + item + "' ...\n";
                    dumped_text += dumpIt(value,level+1);
                } else {
                    dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
                }
             }
        } else {
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}


// usage: log('inside coolFunc', this, arguments);
// paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function() {
	log.history = log.history || [];
	// store logs to an array for reference
	log.history.push(arguments);
	if (this.console) {
		arguments.callee = arguments.callee.caller;
		var newarr = [].slice.call(arguments); ( typeof console.log === 'object' ? log.apply.call(console.log, console, newarr) : console.log.apply(console, newarr));
	}
};

// make it safe to use console.log always
(function(b) {
	function c() {
	}
	for (var d = "assert,clear,count,debug,dir,dirxml,error,exception,firebug,group,groupCollapsed,groupEnd,info,log,memoryProfile,memoryProfileEnd,profile,profileEnd,table,time,timeEnd,timeStamp,trace,warn".split(","), a; a = d.pop(); ) {
		b[a] = b[a] || c
	}
})((function() {
	try {
		console.log();
		return window.console;
	} catch (err) {
		return window.console = {};
	}
})());
