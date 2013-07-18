/**
 * Created with IntelliJ IDEA.
 * User: swissbib
 * Date: 11/9/12
 * Time: 2:14 PM
 * To change this template use File | Settings | File Templates.
 */



var baseAutocomplete = {


    initAutocomplete: function(suggestionHandler) {

        var options = {
            setWidth : 5 /* below this number of results the width is calculated */
        };
        //only for testing
        var availableTags = [
            "ActionScript",
            "AppleScript",
            "Asp",
            "BASIC",
            "C",
            "C++",
            "Clojure",
            "COBOL",
            "ColdFusion",
            "Erlang",
            "Fortran",
            "Groovy",
            "Haskell",
            "Java",
            "JavaScript",
            "Lisp, und da hat es dann noch ganz viel Text um zu schauen was passiert wenn der so lang wird.",
            "Perl",
            "PHP",
            "Python",
            "Ruby",
            "Scala",
            "Scheme"
        ];
        function highlight(value, term) {
            term = term.split(' ');
            for (var i = 0; i < term.length; i++) {

                //if (term[i].substr(term[i].length -1 ) == "*") {
                //    var modifiedTermNoStar =  term[i].substr(0, term[i].length -2 );
                //    value = value.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + modifiedTermNoStar.replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
                //} else {
                value = value.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + term[i].replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
                //}
            }
            return value;
        }
        function setwidth(element, width, maxWidth) {
            /* don't use with large resultset */
            jQuery(element).css({'display':'inline','white-space':'nowrap'});
            var elwidth = jQuery(element).width();
            if (elwidth > width) {
                width = elwidth;
            }
            if (elwidth > maxWidth) {
                width = maxWidth;
            }
            jQuery(element).css({'display':'block','white-space':'normal'});
            console.log(width);
            return width;
        }
        function uiserach_open(event, ui) {
            var term = event.target.value;
            var wdgt = jQuery(this).autocomplete('widget');
            var width = jQuery(wdgt).width();
            if (options.setWidth > 0 && jQuery('a', wdgt).length < options.setWidth) {
                var setWidth = true;
                var maxWidth = width;
                width = jQuery(this).outerWidth();
            }
            console.log('count', jQuery('a', wdgt).length);
            jQuery('a', wdgt).each(function(e) {
                jQuery(this).html(highlight(jQuery(this).html(), term));
                if (setWidth) {
                    width = setwidth(this, width, maxWidth);
                }
            });
            jQuery(wdgt).css('width',width);
        }
        jQuery('#search_term').autocomplete({
            //source: availableTags,
            //source:"/TouchPoint/search/swissbibsuggest",
            source:suggestionHandler,
            open: uiserach_open
        });
    }
};

