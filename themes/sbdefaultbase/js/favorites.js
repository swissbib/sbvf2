/**
 * swissbib Javascript
 * @author OCLC
 * @version 1.1, 201106
 */

  $(document).ready(function() {
    var field = $("#libraryname");

	var lName = $.jStorage.get("libraryList");
	var lIdentifier = $.jStorage.get("libraryIdentifier");
	var lGroup = $.jStorage.get("libraryGroup");

	var fieldIdentifier = $("#libraryidentifier");
    var group = $("#librarygroup");
    var favorideId = $("#favoriteId");
    var favorideGroupId = $("#favoriteGroupId");

    var addFavorite = function(event,data,formatted,type) {
		if(data)
        {
		  var splittedTerms = data[0].split("@@");


          if(type == 'favorite')
          {
			favorideId.val(splittedTerms[0]);
			field.val(splittedTerms[2]);
            fieldIdentifier.val(splittedTerms[1]);
            var currentUrl ="nose/useraccount/favorites/favoritesServlet.jsp?type=addFavorite&favoriteId="+splittedTerms[0];
            sentFavorite(currentUrl);
          }
          if(type == 'group')
          {
            favorideGroupId.val(splittedTerms[0]);
            group.val(splittedTerms[2]);
            var currentUrl ="nose/useraccount/favorites/favoritesServlet.jsp?type=addFavoriteGroup&groupId="+splittedTerms[0];
            sentFavorite(currentUrl);
          }
        }
    };
    var sentFavorite = function(currentUrl) {
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
                 url: "nose/useraccount/favorites/favorites.jsp",
                 cache: false,
                 dataType: "html",
                 success: function(data1)
               {
                 var newData = data1;
                 var newReplaceMe = $(newData).find("#favoriteaccordion");
                 newReplaceMe.find("div#message").replaceWith(ref);
                 $("#favoriteaccordion").replaceWith(newReplaceMe);
                 return false;
               }
               });
            }
            return false;
          }
       });
    };



    if (field) {
      $(field).autocomplete(lName,
      {
    	autoFill: false,
        initialFocus: false,
        minChars: 0,
        max: 50,
        cacheLength: 1,
        matchContains: true,
        mustMatch: false,
        matchSubset: true,
        scrollHeight: 250,
        selectFirst: true,
        width: 1000,
        dataType: "text",
        highlight: function(value, term) {
            var result = value;
            if(value){
              var splittedTerms = term.split(" ");
              for(var x=0; x < splittedTerms.length; x++)
              {
                var splittedTerm = splittedTerms[x];
                result = result.replace(new RegExp(   "(?![^&;]+;)(?!<[^<>]*)(" + splittedTerm.replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
              }
            }
            return result;
          },
        formatItem: function(item) {
        	if(item)
            {
              if(item[0])
              {
                var splittedTerms = item[0].split("@@");
                if(splittedTerms.length > 3)
                  {
                    return splittedTerms[3];
                }
                return item[0];
              }
              else
              {
                return item;
              }
            }
        },
        formatResult: function(item) {
        	if(item)
            {
              if(item[0])
              {
                var splittedTerms = item[0].split("@@");
                return splittedTerms[2];
              }else{
                return item[0];
              }
            }else
            {
			  return item;
            }
        }
      }).result(function(event,data,formatted) {
		 addFavorite(event,data,formatted,"favorite");
      });
    }
    if (fieldIdentifier) {
        $(fieldIdentifier).autocomplete(lIdentifier,
        {
        	autofill: false,
          initialFocus: false,
          minChars: 0,
          max: 50,
          cacheLength: 1,
          matchContains: true,
          mustMatch: false,
          matchSubset: true,
          scrollHeight: 250,
          selectFirst: true,
          width: 500,
          dataType: "text",
          highlight: function(value, term) {
              var result = value;
              if(value){
                var splittedTerms = term.split(" ");
                for(var x=0; x < splittedTerms.length; x++)
                {
                  var splittedTerm = splittedTerms[x];
                  result = result.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + splittedTerm.replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
                }
              }
              return result;
            },
          formatItem: function(item) {
            if(item)
            {
              if(item[0])
              {
                var splittedTerms = item[0].split("@@");
                if(splittedTerms.length > 3)
                {
                	  return splittedTerms[3];
                }
                return item[0];
              }
              else
              {
                return item;
              }
            }
          },
          formatResult: function(item) {
            if(item)
            {
              if(item[0])
              {
                var splittedTerms = item[0].split("@@");
                if(splittedTerms.length > 2)
                {
              	  return splittedTerms[1];
                }else{
                  return item[0];
                }
              }else
              {
                return item;
              }
            }
          }
        }).result(function(event,data,formatted) {
            addFavorite(event,data,formatted,"favorite");
        });
      }
    if (group) {
        $(group).autocomplete(lGroup,
        {
          autofill: false,
          initialFocus: true,
          minChars: 0,
          max: 50,
          cacheLength: 1,
          matchContains: true,
          mustMatch: false,
          matchSubset: true,
          scrollHeight: 250,
          selectFirst: true,
          width: 500,
          dataType: "text",
          highlight: function(value, term) {
              var result = value;
              if(value){
                var splittedTerms = term.split(" ");
                for(var x=0; x < splittedTerms.length; x++)
                {
                  var splittedTerm = splittedTerms[x];
                  result = result.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + splittedTerm.replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
                }
              }
              return result;
            },
          formatItem: function(item) {
            if(item)
            {
              if(item[0])
              {
                var splittedTerms = item[0].split("@@");
                if(splittedTerms.length > 2)
                  {
                    return splittedTerms[2];
                }
                return item[0];
              }
            }
            else
            {
              return item;
            }
          },
          formatResult: function(item) {
            if(item)
              {
                if(item[0])
                {
                  var splittedTerms = item[0].split("@@");
                  return splittedTerms[2];
                }else{
                  return item[0];
                }
              }else
              {
                return item[0];
              }
          }
        }).result(function(event,data,formatted) {
           addFavorite(event,data,formatted,"group");
        });
      }
  });