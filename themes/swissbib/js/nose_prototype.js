var np = {
	'themes' : {
		'green' : {
			'favicon' : 'style/img/favicon.ico'
		},
		'orange' : {
			'css' : 'style/css/themes/orange/orange.css',
			'favicon' : 'style/css/themes/orange/img/favicon.ico'
		}
	},
	init : function() {
		np.switchTheme();
		np.initThemeSwitch();
	},
	initThemeSwitch: function() {
		var haystack = [];
		for (var key in np.themes) {
			haystack.push(key);
		}
		jQuery('#meta li:last a').bind('click', function() {
			var active = JSON.parse(jQuery.cookie("np")).theme;
			var pos = jQuery.inArray( active, haystack );
			var ret = 'green';
			if (pos === haystack.length-1) {
				ret = haystack[0];
			} else {
				ret = haystack[pos+1];
			}
			np.switchTheme(ret);
			return false;
		})
	},
	switchTheme: function(th) {
		if (th === undefined) {
			if (jQuery.cookie("np") === null) {
				th = 'green';
			} else {
				th = JSON.parse(jQuery.cookie("np")).theme;
			}
		}
		
		jQuery('link[type="image/x-icon"]').remove();
		jQuery('#theme-css').remove();
		var tcss = jQuery('<link/>', {
			'id' : 'theme-css',
			'rel' : 'stylesheet',
			'type' : 'text/css'
		});
		var tico = jQuery('<link/>', {
			'id' : 'theme-icon',
			'rel' : 'shortcut icon',
			'type' : 'image/x-icon'
		});
		switch(th) {
			case 'orange':
				jQuery(tcss).attr('href',np.themes.orange.css);
				jQuery('head').append(tcss);
				jQuery(tico).attr('href',np.themes.orange.favicon);
			break;
			default:
				jQuery(tico).attr('href',np.themes.green.favicon);
			break;
		}
		jQuery('head').append(tico);
		jQuery.cookie("np", JSON.stringify({'theme':th}), { path: '/' });
	}
}

jQuery(function(){
	np.init();
})
