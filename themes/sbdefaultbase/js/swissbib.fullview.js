
/**
 * Swissbib javascript only for the full view of an item
 * @author project Swissbib, G�nter Hipler
 * @version 0.1 first prototype
 * */



//todo: GH: 20100826
//Ausgangslage: ist eine Institution Favorit, erscheint sie an zwei Orten (Favoriten und im eigentlichen Verbund)
//bis jetzt wurde die Verf�gbarkeit nur im Container der Favoriten angezeigt - nicht im Bibliotheksnetzwerk
//neu erscheint die Verf�gbarkeit auch im Biblioteksverbund (�nderung des selektors von id auf Klasse)
//aber die im Verbund angezeigten nformationen werden nochmals abgerufen, wenn der Verbund aufgeklappt wird
//ich muss das Attribut data-detched=true noch bei der Institution setzen





function fetchAvailabilty(urlString, barcodes) {
    var sBarcodes = new String(barcodes);
    var oBarcodes = null;
    //we can't know at this moment if it will be an array or a simple string in case of only one barcode
    //alert (urlString);
    //var errormesage = "${igf:getLabel(pageContext, '','temp.nose.xswissbib.availability.request.error' )}";

    if (sBarcodes.match("##")) {
        // now split the barcodes for detection after response
        oBarcodes = sBarcodes.split("##");
        $.each(oBarcodes,function() {
            $("." + this + "_BC").addClass("xSwissBib_loading");
        });
    }
    else {
        oBarcodes = sBarcodes;
        $("." + oBarcodes + "_BC").addClass("xSwissBib_loading");
    }

    $.ajax({
        //url:"/TouchPoint/AvailabilityRequest?sysnumber=003588223&barcode=BM1092012&barcode=BM0881025&barcode=BM0875068&barcode=BM0880114&barcode=BM0881390&barcode=BM0877183&barcode=BM0883549&idls=DSV01",
        //url:"/TouchPoint/AvailabilityRequest?sysnumber=004687046&barcode=A1001760405&idls=DSV01",
        url:urlString,
        error: function (httpRequest, textStatus, errorThrown) {

            //alert ("in error: " + textStatus);
            if (sBarcodes.match("##")) {
                $.each(sBarcodes.split("##"),function() {
                    //alert ("#" + this + "_BC");
                    //$("#" + this + "_BC").removeClass("xSwissBib_loading");
                    //$("#" + this + "_BC").text(errormesage);
                    $("." + this + "_BC").removeClass("xSwissBib_loading");
                    $("." + this + "_BC").text(errormesage);
                });
            }
            else {

                //$("#" + sBarcodes + "_BC").removeClass("xSwissBib_loading");
                //$("#" + sBarcodes + "_BC").text(errormesage);
                $("." + sBarcodes + "_BC").removeClass("xSwissBib_loading");
                $("." + sBarcodes + "_BC").text(errormesage);
            }
        },
        success:function (data,textstatus) {

            $.each(data,function(index,value){

                //$("#" + value.identifierBarcode + "_BC").removeClass("xSwissBib_loading");
                $("." + value.identifierBarcode + "_BC").removeClass("xSwissBib_loading");

                var properties = null;
                switch(value.availStatus) {
                case "0":
                        //more information necessary
                        //alert("more info necessary");
                        properties =  value.loanIcon ;
                    break;
                case "1":
                        //alert("available");
                        properties =  value.loanIcon ;
                        //availability ok
                    break;
                case "2":
                        //not available
                        //alert("not available");
                        properties =  value.loanIcon ;
                        properties +=  value.dueDate ;

                        if (value.numberRequests != "0") {
                            properties +=  "<br/>" + value.numberRequests ;
                        }
                    break;
                default:

                        properties = "return code not defined!";
                        //not defined
                    break;
                }
                properties += value.wholeMessage;

                //$("#" + value.identifierBarcode + "_BC").html(properties);
                //encodeURI

                //alert("#" + value.identifierBarcode + "_BC");

                //$("#" + value.identifierBarcode + "_BC").html(properties);
                $("." + value.identifierBarcode + "_BC").html(properties);
            });
        },
        dataType:'json'

    });

}

//with HTML5  custom attributes prefixed with data- are allowed and regarded as a valid structure!
//http://www.w3.org/TR/html5/dom.html#attr-data.)

jQuery(document).ready(function(){

$(".fetch_availability").click(function(event){
    var searchedid;
    var searchedBarcodes;
    switch (event.target.tagName) {
        case "A":
    //the click is done only on the a-tag
    if ($(event.target).is("[data-firstfetched=true]")) {
    //alert("los gehts first fetched");

                //searchedid = "#" + event.target.id + "_url  > p:nth-child(1)";
                //searchedBarcode = "#" + event.target.id + "_url  > p:nth-child(2)";
                searchedid = "#" + event.target.id + "_url";
                searchedBarcodes = "#" + event.target.id + "_bc";
                $(event.target).attr("data-fetched",true);
                $(event.target).removeAttr("data-firstfetched");

                //alert (searchedid);

                fetchAvailabilty($(searchedid).text(),$(searchedBarcodes).text());

    }else if ($(event.target).is(".expanded") && ! $(event.target).is("[data-fetched=true]"))  {
    //alert("los gehts normal fetch");
                //searchedid = "#" + event.target.id + "_url  > p:nth-child(1)";
                //searchedBarcode = "#" + event.target.id + "_url  > p:nth-child(2)";
                searchedid = "#" + event.target.id + "_url";
                searchedBarcodes = "#" + event.target.id + "_bc";
                $(event.target).attr("data-fetched",true);
                fetchAvailabilty($(searchedid).text(),$(searchedBarcodes).text());
            }

            break;
        case "SPAN":
            if ($(event.target.parentNode).is(".expanded") && ! $(event.target.parentNode).is("[data-fetched=true]")) {
                //searchedid = "#" + event.target.id + "_url  > p:nth-child(1)";
                //searchedBarcodes = "#" + event.target.id + "_url  > p:nth-child(2)";

                searchedid = "#" + event.target.parentNode.id + "_url";
                searchedBarcodes = "#" + event.target.parentNode.id + "_bc";
                $(event.target.parentNode).attr("fetched",true);

                fetchAvailabilty($(searchedid).text(),$(searchedBarcodes).text());
            }
            break;
    }
});

});






//following was part of sbLayoutHitDetail.jsp


jQuery(document).ready(function(){
    //alert ("test auf parameter");
    var requestedURL = document.URL;
    var searchedExpression = /library=(\w+)$/;
    if(searchedExpression.exec(requestedURL)) {
        var requestedInstititution =  RegExp.$1;

        //at first close all library network sections
        var networkDivsSelector = "div[class='innerbox grey'] h3";
        $(networkDivsSelector).removeClass("expanded").removeClass("persist").addClass("collapsed");
        networkDivsSelector = "div[class='innerbox grey'] > div";
        $(networkDivsSelector).css("display","none");

        /*
                                is the  requested library part of the users favorites?
                            */
        // as a convention institutions part of the favorites group are prefixed with 'f'
        var tconstructedFavId = "f" + requestedInstititution;
        var attributeFavSelector =  "a[name='"+ tconstructedFavId + "']";

        if ($(attributeFavSelector).size() > 0) {
            //this case happens when the requested institution is part of the favorites

            $("h3#library_toggler_group_of_favorites").removeClass("collapsed").addClass("expanded");
            $("div.library_toggler_group_of_favorites").css("display","block");
            $("a#stock_toggler_singlefavorite_" + tconstructedFavId).css("display","block").removeClass("collapsed").addClass("expanded").attr("data-firstfetched",true).click();
            //now jump to the top of the favorites
            window.location.hash = "#topfavorites";

        } else {

            //this happens when the requested institution is not part of the favorites

            var attributeSelector = "[name='" + requestedInstititution + "']";
            var networkDivSelector = "'div[class='innerbox grey']:has(" + attributeSelector +")' h3";
            $(networkDivSelector).removeClass("collapsed").addClass("expanded");

            var divSelector = "'div[class='innerbox grey']:has(" + attributeSelector +")' > div";
            $(divSelector).css("display","block");

            $(attributeSelector).removeClass("collapsed").addClass("expanded");
            $(attributeSelector).attr("data-firstfetched",true);

            $(attributeSelector).click();

            //it might happen that the requested institution isn't part of the favorites although the item the user is looking for
            //is held by an favorite institution of the user
            //then the favorite network container should be in state collapsed although fetching the availybility
            // automatically how it is done for the requested institution isn't advisable.
            //Might be a lot of institutions if the user has created a pile of favorites 


            if ($("h3#library_toggler_group_of_favorites").size() > 0) {
                $("h3#library_toggler_group_of_favorites").removeClass("collapsed").addClass("expanded");
                $("div.library_toggler_group_of_favorites").css("display","block");
                //$("a#stock_toggler_singlefavorite_" + tconstructedFavId).css("display","block").removeClass("collapsed").addClass("expanded").attr("data-firstfetched",true).click();
            }


            //now jump  to the selected element
            $("a[href=#tab-library]").click();
            window.location.hash = "#" + requestedInstititution;
        }

    }



});




