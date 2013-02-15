/**
 * swissbib Javascript Stuff.
 * @author project swissbib, Guenter Hipler
 * @version 0.1 first prototype
 * */

var currentSubmitButton;

var swissbibextensions = {

    initOnReady: function () {
        swissbibextensions.initSelectSortShortList();
        swissbibextensions.initLoginForm();
        swissbibextensions.initAsynchSearchRequest();
        swissbibextensions.initMultitargetRequest();
    },

    initMultitargetRequest : function () {

        var options = {
            beforeSubmit:  showRequest  // pre-submit callback
            //success:       showResponse  // post-submit callback

            // other available options:
            //url:       url         // override for form's 'action' attribute
            //type:      type        // 'get' or 'post', override for form's 'method' attribute
            //dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
            //clearForm: true        // clear all form fields after successful submit
            //resetForm: true        // reset the form after successful submit

            // $.ajax options can be used here too, for example:
            //timeout:   3000
        };



        jQuery(".rMT").bind("click",function(event) {
            // tabLeft and tabRight are JavaScript variables which are initialized in
            //within the jsp Layout file (sbLayoutHitlist.jsp) -> because target-numbers are dynamic


            var targetname = null;

            if ($("#tabbed_" + window.tabLeft).hasClass("selected")) {
                targetname = window.tabLeft;
            } else {
                targetname = window.tabRight;
            }

            var activeContainer = "ac=" + targetname;
            var link;

            var partsOfLink;
            if (event.target.href.match("worker") || event.target.href.match("do")) {
                partsOfLink = event.target.href.split("?");
            }


            if (partsOfLink.length == 2) {
                link = partsOfLink[0] + "?" + activeContainer + "&" + partsOfLink[1];
            } else {
                link = event.target.href + "&" + activeContainer;
            }

            //alert (link);
            window.location = link;
            return false;
        });


        jQuery(".sMT").bind("click",function(event) {
            currentSubmitButton = {
                name:$(event.target).attr("name"),
                value:$(event.target).attr("value")

                };
            $(event.target).closest("form").ajaxSubmit(options);

            return false;
        });
    },




    initAsynchSearchRequest : function ()  {
        jQuery(".executeFilterNavigation").bind("click",function(event){

            var urlToExecute = $(event.target).attr("toExecute");

            enhancedUrlToExecute = urlToExecute + "&methodToCall=execRefineAsynchNav";
            //alert (urlToExecute);

            $.ajax({
                url:enhancedUrlToExecute,
                error: function (httpRequest, textStatus, errorThrown) {
                    alert ("in error: " + textStatus);
                },
                success:function (data,textstatus) {
                    alert ("das Absetzen des requests refinement facets hat geklappt");
                },
                dataType:'xml'
            });

            setTimeout(function() {
                alert('ich bin mal eben fuer 3 Sekunden schlafen gegangen - jetzt will ich aber ein Ergebnis und setze einen request an das future Objekt ab');

                enhancedUrlToExecute = urlToExecute + "&methodToCall=getRefinedAsynchNav";

                //alert(enhancedUrlToExecute);

                $.ajax({
                    url:enhancedUrlToExecute,
                    error: function (httpRequest, textStatus, errorThrown) {
                        alert ("in error: " + textStatus);
                    },
                    success:function (data,textstatus) {
                        alert ("hat alles bestens geklappt, jetzt muss das Ergebnis des Future Objects noch in die HTLM Container gestellt werden");
                    },
                    dataType:'xml'
                });
            },3000);
        });

    },

    initSelectSortShortList: function () {
        jQuery(".icon_notepad_add").bind("click",function(event){
            var url = event.target.href;
            //alert (url);
            var currentSelectedList =  $("select.select[name=selectedMemorizeList] option:selected").val();
            var searchedExpression = /^(.*selectedMemorizeList=)(\w+)$/;

            event.target.href = url.replace(searchedExpression, "$1" + currentSelectedList);
        });



        jQuery("#icon_notepad_addadd").bind("click",function(event){
            /*
                        Out of [design /css] reasons we had to include the img - object within a span object
                        it might happen that users will reach the unserlying a object when the mouse pointer is a the edge of the span object
                         Therefore ask for the tagname of the object which has triggered the event.

                     */
            var urlObject = null;
            switch (event.target.tagName) {
                case "A":
                    urlObject = event.target;
                    break;
                case "SPAN":
                    urlObject = event.target.parentNode
                    break;
            }

            var url = urlObject.href;
            var currentSelectedList =  $("select.select[name=selectedMemorizeList] option:selected").val();
            var searchedExpression = /^(.*selectedMemorizeList=)(\w+)$/;
            var urlNew = url.replace(searchedExpression, "$1" + currentSelectedList);

            var itemsInList = new Array();

            var isMarkedExpression = /(isMarked=[\d_]+)/;

           $(".icon_notepad_add").each(function(){

               if(isMarkedExpression.exec(this.href)) {
                   itemsInList.push(RegExp.$1);
                   //alert (RegExp.$1);
               }
            });

            var joinedList =  itemsInList.join("&");
            urlNew = urlNew.replace(isMarkedExpression, joinedList);

            urlObject.href = urlNew;
        });



        jQuery("#icon_notepad_removeAll_memitems").bind("click",function(event){

    /*Example for the link to used to delete all the items of the list shown in one step
                /TouchPoint_tptest2/memorizelist.do?
                    methodToCall=deleteSelectedEntries&selectedMemListentries[0]=on&selectedMemorizeList=-1&listafteritemdeletion=-1

                    search and replace the parameter 'selectedMemListentries[0]=on'
                    selectedMemorizeList=-1  //used for the current listnumber

                    template of the "delete all link" => event.target.href
                    memorizelist.do?methodToCall=deleteSelectedEntries&SELECTEDITEMS&selectedMemorizeList=SELECTEDLIST&listafteritemdeletion=SELECTEDLIST
        */

            var url = event.target.href;
            var selectedListNr = null;

            var searchedItemExpression = /(selectedMemListentries\[\d+\]=on)/;
            var searchedListNumber = /selectedMemorizeList=([-0-9]+)&/;

            var listNumber = null;
            var memItems = new Array();

           $(".deleteAllItemsAtOnceSingleItem").each(function(){

               if(searchedItemExpression.exec(this.href)) {
                   memItems.push(RegExp.$1);
               }

               if(searchedListNumber.exec(this.href)) {
                   listNumber = RegExp.$1
               }
            });


            var joinedmemItems =  memItems.join("&");
            var urlNew = url.replace("SELECTEDITEMS", joinedmemItems);
            urlNew = urlNew.replace(/SELECTEDLIST/g, listNumber);
            //alert (urlNew);

            event.target.href = urlNew;

            //return false;
        });



        //change should only be possible because of event bubbling
        //event might be better bind on the select element itself 
        jQuery(".notepadJQueryMemorizeListGrip").bind('change',function(event){

            //the selected value of the visible element has to be set to the 'hidden' memorylist select box which is part of the
            //RefineHitlist Form
            var itemIdentifier = $("select.select[name=selectedMemorizeList] option:selected").val();
            $("#hiddenMemorizeSelection").val(itemIdentifier);





            if (itemIdentifier.length > 0)
            {
                var indexOfPos = itemIdentifier.indexOf("_");
                if(indexOfPos >=0) {
                    itemIdentifier = itemIdentifier.substring(indexOfPos+1);
                }
                var currentUrl = "memorizelist.do?methodToCall=showMemorizelist&selectMemorizeList="+itemIdentifier;

                $.ajax({
                    type: "GET",
                    url: currentUrl,
                    cache: false,
                    dataType: "text",
                    success: function(data) {
                        //alert ("in data");
                        //var ref = data;
                        return false;
                    }

                } );
            }

             //$("#hiddensaveToListbutton").click();

        });

        jQuery("[name^=dummylistToDelete]").bind('click',function(event){
            $("#dummyHiddenMemorizeList")[0].value = this.attributes["listnumber"].nodeValue;
            $("#dummyHiddenDeleteListSubmit").click();

        });

        /*
        currently not used -> Problem with MemorizeBean
        jQuery("#callCurrentMemorizeListFromHitlist").bind('click',function(event){

            var tempselect =  $("[id^=form_notepad_dummy-selectedMemorizeList]")[0].value;
            var tempHref  =  this.attributes["href"].nodeValue;
            var selectedList = tempselect.split("_");
            selectedList = selectedList[1];
            tempHref = tempHref + "&selectedMemorizeList=" + selectedList;

            window.location.href = tempHref;

            //$("#form_notepad_dummy")[0].submit();
            return false;

        });
        */

    },
    initLoginForm: function() {

    }

	
};

jQuery(document).ready(function(){
    swissbibextensions.initOnReady();
});

function showRequest(formData, jqForm, options) {

    var targetname;
    if ($("#tabbed_" + window.tabLeft).hasClass("selected")) {
        targetname = window.tabLeft;
    } else {
        targetname = window.tabRight;
    }

    //two query - Parameters has to be added
    //a) current submit button (seems to be that Touchpoint evaluates it
    //b) current targetname so the server is able to decide which target is going to be shown

    formData.push(
        {
            name:currentSubmitButton.name,
            value:currentSubmitButton.value,
            type:"submit"
        }
    );


    formData.push(
        {
            name:"ac",
            value:targetname
        }
    );

    var evaluatedHref = $("a#GripJSessionId");
    //I'm looking for: TouchPoint/memorizelist.do;jsessionid=511FC5D507BB0DC61E35BD45627DB769.worker1?methodToCall=show
    var compoundURL = null;
    var queryString = $.param(formData);

    if (evaluatedHref.attr("href").match("jsessionid")) {
        var myRegexp = /;(.*?)\?/;
        var match = myRegexp.exec(evaluatedHref.attr("href"));

        compoundURL = options.url + ";" + match[1] + "?" + queryString;

    } else {
        compoundURL =  options.url +  "?" + queryString;
    }

    window.location = compoundURL;
    return false;
}

