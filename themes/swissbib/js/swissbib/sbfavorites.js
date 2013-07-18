/**
 * Created with IntelliJ IDEA.
 * User: swissbib
 * Date: 8/22/12
 * Time: 12:47 PM
 * To change this template use File | Settings | File Templates.
 */


var doFavorites = {

    initFavorites : function () {


        var field = $("#libraryname");
        var lName = $.jStorage.get("libraryList");

        var libraryName = $("#libraryname");

        var sentFavorite = function(favoriteID) {

            //currentUrl = favorites_servlet_url + "?type=addFavorite&favoriteId=" + favoriteID;
            var currentUrl = "http://sb-vf1.swissbib.unibas.ch/vufind/Favorites";
            var favorites_url = "/vufind/themes/swissbib/images/mockupFavorites.txt";


            $.ajax({
                type: "GET",
                url: currentUrl,
                cache: false,
                dataType: "text",
                success: function(data)
                {

                    var ref = data;
                    ref = ref.replace(/\r\n/g,'');
                    ref = ref.replace(/\n/g,'');
                    if (ref.length > 0)
                    {
                        $.ajax({
                            type: "GET",
                            url: favorites_url,
                            cache: false,
                            dataType: "html",
                            success: function(data1)
                            {
                                var newReplaceMe = $(data1).find("#favoriteaccordion");
                                //newReplaceMe.find("div#message").replaceWith(ref);
                                $("#favoriteaccordion").replaceWith(newReplaceMe);
                                return false;
                            }
                        });
                    }

                    return false;
                }
            });
        };

        var clearInputBoxes = function(){
            window.setTimeout(clearInputBoxesAll,2000);
        };

        var clearInputBoxesAll = function(){
            libraryName.val('');
        };

        if (field) {
            $(field).autocomplete(
                {
                    enable:true,
                    source : function (request,response){

                        //Achtung: RegExp ist erforderlich
                        //ein Ausdruck wie /request.term/i wird zwar als pattern erkannt - nicht jedoch mit dem Wert der Variablen!
                        //(das pattern wonach gesucht wird ist dann request.term... - nicht das was man will...)
                        //man faellt nur einmal hinein....

                        //wir moechten auch nach mehrfachen Termen suchen k�nnen die verstreut in der Beschreibung verteilt sind
                        var splittedTerms = request.term.split(" ");
                        var aRegexes = new Array();
                        for(var x=0; x < splittedTerms.length; x++) {
                            var splittedTerm = splittedTerms[x];
                            aRegexes.push(new RegExp(splittedTerm,"gi"));
                        }


                        var responseItems = new Array();
                        //lName.forEach(function (lNameItem){
                        for (var iItems = 0;iItems <  lName.length; iItems++){

                            var lNameItem = lName[iItems];
                            var name = lNameItem.split("@@");

                            //Format der Struktur (Aufbau siehe favorites.jsp)
                            //Index0: library.id
                            //Index1: name / identifier / strasse / Ort
                            var allTermsInLine = true;
                            var tName = name[1];
                            for (var xx= 0; xx < aRegexes.length; xx++) {
                                if (!aRegexes[xx].test(tName)) {
                                    allTermsInLine = false;
                                    break;
                                }
                            }

                            if  (allTermsInLine) {
                                for(var x=0; x < splittedTerms.length; x++)
                                {
                                    var splittedTerm = splittedTerms[x];
                                    tName = tName.replace(new RegExp(   "(?![^&;]+;)(?!<[^<>]*)(" + splittedTerm.replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
                                }
                                responseItems.push({value:tName, id:name[0]});
                            }
                        //});
                        };

            response(responseItems)
                    },
                    open : function (event, ui) {

                        var wdgt = jQuery(this).autocomplete('widget');
                        //var width = jQuery(this).width();
                        jQuery('a', wdgt).each(function(e) {
                            jQuery(this).css({'display':'block','white-space':'normal'});
                            //Das Setzen der HTML Eigenschaft ist erforderlich. Sonst erkennt der renderer die strong tags nicht als
                            //HTML Auszeichnung
                            jQuery(this).html(jQuery(this).text());
                            //der Aufruf von setwidt (ich nehme an das Veraendern der Breite macht das script ausgesprochen langsam)
                            //width = setwidth(this, width);
                        });
                    },
                    select: function( event, ui ) {
                        //alert (ui.item.value);

                        ui.item.value  = ui.item.value.replace(/<strong>/g,'');
                        ui.item.value  = ui.item.value.replace(/<\/strong>/g,'');

                        if( ui.item) {
                            sentFavorite(ui.item.id);
                            clearInputBoxes();
                        }
                    }


                });
        }

    }
};




$(document).ready(function() {

    doFavorites.initFavorites();

});



