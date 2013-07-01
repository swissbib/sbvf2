//dieses script ist zur Zeit nur offline und wird bisher nicht eingebunden
//Ziel asynchrone Aufrufe !


var gHrefRecommender="http://recommender.bibtip.de/service/bibtip4.php";

init_bibtip();

function init_bibtip()
{
  var isxn=getBibtipISXN();
  var shortTitle=getBibtipShortTitle();
  var nd=getBibtipNd();

  checkForRecs(isxn,shortTitle,nd);
}

function getBibtipShortTitle(){
	var element = document.getElementById('bibtip_shorttitle');
    if (element) {
        return getContent(element);
    }
	return "";
}

function getContent(element)
{
  var text_concat = '';

  if(element.nodeType==3) {
    // text node
    return element.data;
  }
  else {
    // element node
    var children=element.childNodes;
    var numChildren=children.length;
    for(var i=0;i<children.length;i++) {
        text_chunk = getContent(children[i]);
        if (text_chunk.length > 0) {
            text_concat = text_concat + text_chunk;
        }
    }
  }

  return text_concat;
}


function getBibtipISXN(){
	var element = document.getElementById('bibtip_isxn');
    if (element) {
        if (element.lastChild) {
            if (element.lastChild.nodeValue) {
                return element.lastChild.nodeValue;
            }
        }
    }
	return "";
}

function getBibtipNd(){
	var element = document.getElementById('bibtip_id');
    if (element) {
        if (element.lastChild) {
            if (element.lastChild.nodeValue) {
                return element.lastChild.nodeValue;
            }
        }
    }
	return "";
}




function getFbt(){
    var result = document.URL.match(/&fbt=[0-9]{7}/);
    if (result != null) {
        return result[0];
    }
    return "";
}



function checkForRecs(isxn,shortTitle,nd)
{

  var catalog='swissbib';

  var params="?isxns="+isxn+"&catalog="+catalog;
  params+="&title="+encodeURIComponent(shortTitle);
  params+="&nd="+nd;
  params +=getFbt();
  params+="&url="+encodeURIComponent(document.URL);
  params+="&referer="+encodeURIComponent(document.referrer);
  if (nd.length > 3) {
      if (navigator.cookieEnabled == true) {
        insertScriptSrc(gHrefRecommender+params);
      }
  }

}



function insertScriptSrc(src)
{
  var scriptElement=document.createElement("script");
  scriptElement.setAttribute("src",src);
  var bodyElement=document.getElementsByTagName("body")[0];
  bodyElement.appendChild(scriptElement);
}



function showCompleteList()
{

    document.getElementById('more_button').style.display = 'none';
    document.getElementById('bibtip_reclist').style.display = 'none';

    //alert("hallo");
    //document.getElementById('short_list').style.display = 'none';
    document.getElementById('long_list').style.display = 'block';
    document.getElementById('recListItem').style.display = 'block';
    document.getElementById('bibtip_contentlong_header').style.display = 'block';
    document.getElementById('bibtippowered').style.display = 'block';


    //document.getElementById('more_button').style.display = 'none';
    $(".tabs").tabs("select", 5);
}

function removeBibTipTab()
{

    $(".tabs").tabs("remove", 5);

}


function showRecs(recs)
{
  if(recs.length > 0) {
      var recDiv = document.getElementById("bibtip_reclist");
      if (recDiv) {
          var newHeader = createHeader();
          recDiv.appendChild(newHeader);
          var LIST_LENGTH = 2;

          var shortList = createList(recs,'short_list',LIST_LENGTH,0);
          recDiv.appendChild(shortList);

          var recDivlong = document.getElementById("bibtip_reclist_long");
          if (recDivlong && recs.length > LIST_LENGTH) {
              var longList = createList(recs,'long_list',100, 0);
              longList.style.display='none';
              recDivlong.appendChild(longList);

          }



          if (recs.length > LIST_LENGTH) {
            var newButton = document.createElement("div");
            newButton.setAttribute('id','more_button');
            var newAnchor = document.createElement("a");
            newAnchor.setAttribute("href","javascript:showCompleteList()");
            var moreLabel =  document.getElementById('user-recommendation_more').textContent;
            newAnchor.appendChild(document.createTextNode(moreLabel));
            newButton.appendChild(newAnchor);
            recDiv.appendChild(newButton);


          } else {
              removeBibTipTab();
          }
          recDiv.style.display='block';
          //by now we set the current tab always on the library tab
          //$(".tabs").tabs("select", 0);
      }

  }

}

function createHeader()
{
      var newHeader = document.createElement("div");
      newHeader.setAttribute('id','bibtip_header');
      var newImage=document.createElement("img");
      newImage.setAttribute("src",'http://recommender.bibtip.de/service/Bibtip_Logo_Final_ohne_Subt_52x16.gif');
      newImage.setAttribute("border",'0');
      var newAnchor = document.createElement("a");
      newAnchor.setAttribute("href","http://www.bibtip.com");
      newAnchor.setAttribute("target","blank");
      newAnchor.appendChild(newImage);
      newHeader.appendChild(newAnchor);
      var newTitle = document.createElement("span");
      newTitle.setAttribute('id','bibtip_title');
    
      var header = document.getElementById('user-recommendation_label').textContent;
      newTitle.appendChild(document.createTextNode(header));
      newHeader.appendChild(newTitle);
      return newHeader;
}

function createList(recs,id,lengthLimit, startIndex)
{
      var newDiv= document.createElement("div");
      newDiv.setAttribute("id",id);
      var newList = document.createElement("ul");
      newDiv.appendChild(newList);

      for (var i = startIndex; (i < recs.length) && (i < lengthLimit); i++) {
          var newLi = document.createElement("li");
          var newAnchor = document.createElement("a");
          var entry = recs[i];
          newAnchor.setAttribute("href",entry[1]);
          newAnchor.appendChild(document.createTextNode(entry[0]));
          newLi.appendChild(newAnchor);
          newList.appendChild(newLi);
      }

      return newDiv;
}
