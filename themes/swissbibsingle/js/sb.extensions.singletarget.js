/**
 * Created by IntelliJ IDEA.
 * User: swissbib
 * Date: 2/7/12
 * Time: 4:30 PM
 * To change this template use File | Settings | File Templates.
 */



var swissbibextensionsSingleTarget = {

    initOnReady: function () {

        swissbibextensionsSingleTarget.initSortType_ListLength_ItemDisplay();
        swissbibextensionsSingleTarget.initAddRemoveMemorizeList();


    },

    initSortType_ListLength_ItemDisplay: function () {
        jQuery("[name=selectedSorting]").bind('change',function (event)
        {

            //it's not possible to call submit on form because TP is missing the name for the forward action??
            //TP needs the following parameter provided by a submit button.
            //<html:submit styleClass="hidden" styleId="changeSortingbutton" property="change_sorting_${currentResultSet.dbIdentifier}">
            //    <jsp:attribute name="value">${igf:getLabel(pageContext, '', 'result.singlehitlistaction.sorting.button')}</jsp:attribute>
            //</html:submit>
            //e.g. change_sorting_4 (4-> databaseID)
            //Therefor the acton has to be triggered by this button using the click event
            //$(".RefineHitListForm").submit();
            $("#changeSortingbutton").click();
        });

        jQuery("[name=selectedHitlistSize]").bind('change',function (event)
        {

            //explanation for this see above
            //$(".RefineHitListForm").submit();
            $("#changeHitListSizebutton").click();
        });

        jQuery("[name=selectedListType]").bind('change',function (event)
        {

            //explanation for this see above
            //$(".RefineHitListForm").submit();
            $("#changeHitListSizebutton").click();
        });

    },

    initAddRemoveMemorizeList: function() {
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


    }
};

jQuery(document).ready(function(){

    swissbibextensionsSingleTarget.initOnReady();

});