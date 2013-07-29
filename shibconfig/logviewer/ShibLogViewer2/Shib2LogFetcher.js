/*

AJAX Log Feeder
Based on General AJAX technique samples by sbenfield@clearnova.com
If you find any bugs or have any ideas of enhancement, send them to aai@switch.ch
     
*/

/******************************************************************************/
// URL of the IDPLogFeeder.cgi script
CGI_URL = "/cgi-bin/Shib2LogFeeder.cgi"
/******************************************************************************/

    
var _ms_XMLHttpRequest_ActiveX = ""; // Holds type of ActiveX to instantiate
var _ajax;                           // Reference to a global XMLHTTPRequest object for some of the samples
var _logger = true;                  // write output to the Activity Log
var _log_area;                    // will point to the area to write status messages to
var _max_requests = 180;         // Will stop refreshing the log
var _refreshing = true;

if (!window.Node || !window.Node.ELEMENT_NODE) {
    var Node = { ELEMENT_NODE: 1, ATTRIBUTE_NODE: 2, TEXT_NODE: 3, CDATA_SECTION_NODE: 4, ENTITY_REFERENCE_NODE: 5,
                  ENTITY_NODE: 6, PROCESSING_INSTRUCTION_NODE: 7, COMMENT_NODE: 8, DOCUMENT_NODE: 9, DOCUMENT_TYPE_NODE: 10, 
    		  DOCUMENT_FRAGMENT_NODE: 11, NOTATION_NODE: 12 };
}

function startRefresh(){
	_refreshing = true;
	showLog();
}

function stopRefresh(){
	_refreshing = false;
}

function showLog(){
	
	if (_refreshing){
		if (_max_requests  && _max_requests > 0){
			getLog();
			setTimeout('showLog()', 1000);
			//window.location = '#end';
			_max_requests -= 1;
		} else {
			log_div = document.getElementById('log');
			log_div.innerHTML += "<div align=\"center\"><span  class=\"error\" style=\"border-style: solid; border-color: red; border-width: 1px; width: 300px; font-weight: bold; padding: 5px; margin: 5px; background: #fff0f2\">Stopped refreshing log. Reset log to continue</span></div>\n&nbsp;\n";
		}
	}
}

// log information to the status area textfield
function logger( text, clear ) {
	if (_logger) {
		if (!_log_area) {
			_log_area = document.getElementById("log_area");
		}
	
			_log_area.value += text; 
	}
}


/*
  Basic AJAX Functionality
  Done the basic way
  
  Most AJAX examples, are like the ping example below.
  A global variable that points to an instance of XMLHttpRequest is declared
  The XMLHTTPRequest object is instantiated differently for IE vs. Non-IE.
  (Can't give MS grief on this one, they invented the XMLHTTP object in '97 and
  did it with ActiveXObject. 
*/

// initialize the global AJAX object
// lots of samples that discuss AJAX use a global variable
// so I started these samples with one
// the ping example uses this one
var logAJAX;
function getLog() {
    //logger("PING: Initializing XMLHttpRequest");
    // Instantiate an object
    // Test to see if XMLHttpRequest is a defined object type for the user's browser
    // If not, assume we're running IE and attempty to instantiate the MS XMLHTtp object
    // Don't be confused by the ActiveXObject indicator. Use of this code will not trigger 
    // a security alert since the ActiveXObject is baked into IE and you aren't downloading it
    // into the IE runtime engine
    if ( window.XMLHttpRequest ) {
	   logAJAX = new XMLHttpRequest();
    } else {
	   logAJAX = new ActiveXObject("MSXML2.XMLHTTP");
    }
    //logger("PING: Setting Callback");
    // In Javascript, everything is an object. Functions are objects, everything inherits from Object
    // So assigning onreadystatechange to logCallback means that you can call logCallback by doing the following
    // logAJAX.onreadystatechange( "blahblah")
    logAJAX.onreadystatechange = logCallback;    
    //logger("PING: Opening POST Request (async)");
    // The open statement initializes the request. In this example, we'll just pass the value in the URL.
    logAJAX.open( "GET", CGI_URL, true );
    //logger("PING: Sending Request");
    // Send request to the server
    logAJAX.send(null);
}

function logCallback() {
	// Called from ping
	//logger("PING: logCallback. readyState = " + logAJAX.readyState /* + " | Status: " + logAJAX.status */); 
	if ( logAJAX.readyState == 4 ) {
		//logger(logAJAX.responseText );
		// find the ping_status DIV and replace its HTML
		//document.getElementById("ping_status").innerHTML = "Ping Complete: " + logAJAX.responseText;
		
		
	log_div = document.getElementById('log');
	var div = "";
	
	// Stop refreshing when page is not found
	if (logAJAX.status != 200){
		stopRefresh();
	}
	
	div += '<pre>' +logAJAX.responseText + '</pre>';
	if (log_div && div != "null" && div != '') {
		log_div.innerHTML += div + "\n";
		//window.location = "#end";
	}
	}
}

function clearLog(){
	log_div = document.getElementById('log');
	log_div.innerHTML = "";
}

function AJAXRequest( method, url, data, process, async, dosend) {
    // self = this; creates a pointer to the current function
    // the pointer will be used to create a "closure". A closure
    // allows a subordinate function to contain an object reference to the
    // calling function. We can't just use "this" because in our anonymous
    // function later, "this" will refer to the object that calls the function 
    // during runtime, not the AJAXRequest function that is declaring the function
    // clear as mud, right?
    // Java this ain't
    
    var self = this;

    // check the dom to see if this is IE or not
    if (window.XMLHttpRequest) {
	// Not IE
        self.AJAX = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
	// Hello IE!
        // Instantiate the latest MS ActiveX Objects
        if (_ms_XMLHttpRequest_ActiveX) {
            self.AJAX = new ActiveXObject(_ms_XMLHttpRequest_ActiveX);
        } else {
	    // loops through the various versions of XMLHTTP to ensure we're using the latest
	    var versions = ["Msxml2.XMLHTTP.7.0", "Msxml2.XMLHTTP.6.0", "Msxml2.XMLHTTP.5.0", "Msxml2.XMLHTTP.4.0", "MSXML2.XMLHTTP.3.0", "MSXML2.XMLHTTP",
                        "Microsoft.XMLHTTP"];

            for (var i = 0; i < versions.length ; i++) {
                try {
		    // try to create the object
		    // if it doesn't work, we'll try again
		    // if it does work, we'll save a reference to the proper one to speed up future instantiations
                    self.AJAX = new ActiveXObject(versions[i]);

                    if (self.AJAX) {
                        _ms_XMLHttpRequest_ActiveX = versions[i];
                        break;
                    }
                }
                catch (objException) {
                // trap; try next one
                } ;
            }

            ;
        }
    }
    
    // if no callback process is specified, then assing a default which executes the code returned by the server
    if (typeof process == 'undefined' || process == null) {
        process = executeReturn;
    }

    self.process = process;

    // create an anonymous function to log state changes
    self.AJAX.onreadystatechange = function( ) {
        //logger("AJAXRequest Handler: State =  " + self.AJAX.readyState);
        self.process(self.AJAX);
    }

    // if no method specified, then default to POST
    if (!method) {
        method = "POST";
    }

    method = method.toUpperCase();

    if (typeof async == 'undefined' || async == null) {
        async = true;
    }

    //logger("----------------------------------------------------------------------");
    //logger("AJAX Request: " + ((async) ? "Async" : "Sync") + " " + method + ": URL: " + url + ", Data: " + data);

    self.AJAX.open(method, url, async);

    if (method == "POST") {
        self.AJAX.setRequestHeader("Connection", "close");
        self.AJAX.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        self.AJAX.setRequestHeader("Method", "POST " + url + "HTTP/1.1");
    }

    // if dosend is true or undefined, send the request
    // only fails is dosend is false
    // you'd do this to set special request headers
    if ( dosend || typeof dosend == 'undefined' ) {
	    if ( !data ) data=""; 
	    self.AJAX.send(data);
    }
    return self.AJAX;
}

