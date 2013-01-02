// --- TAB ACTIONS --- //
function showPages() {
  $('.doc_list').hide();
  $('.page_list').show();
  var pL = $('.page_list .page_link');
  if(pL.length == 0) {
    $('.page_list').html('<br/><br/><br/>No pages found.<br/><br/><br/>');
    $('.information, .original, .preview, .zoomFrame').hide();
  }
}
function showDocs() {
  $('.page_list').hide();
  $('.doc_list').show();
  var pL = $('.doc_list .page_link');
  if(pL.length == 0) $('.doc_list').html('<br/><br/><br/>No docs found.<br/><br/><br/>');
}

var currentPreview = "";
function showPreview(src,tab) {
  var tText = tab.innerHTML;
  $('.information, .original, .zoomFrame').hide();
  if(currentPreview !== src) {
    tab.innerHTML = "...";
    currentPreview = src;
    $('<img/>')
      .attr({
        'id':'preview',
        'src':src
      })
      .load(function() {
        tab.innerHTML = tText;
        $('#preview').replaceWith(this);
      })
  }
  $('.preview').show();
}

function showOriginal(src) {
  if(src.length > 0)
    $('.original').html('Please <a href="mailto:digitallibrary@villanova.edu?subject=Hi-Res%20Request%20for%20'+src+'">email</a> us for access to the Hi-Res image.');
  else
    $('.original').html('Original Image File Does Not Exist');
  $('.information, .preview, .zoomFrame').hide();
  $('.original').show();
}

/*
var iviewer = {};
function showZoom(src,tab) {
  iviewer.loadImage(src);
  var tText = tab.innerHTML;
  tab.innerHTML = "...";
  $('.information, .original, .preview').hide();
  $('.zoomFrame img').src = src;
  $('.zoomFrame').show();
  tab.innerHTML = tText;
}*/
function showZoom(src,tab) {
  var tText = tab.innerHTML;
  tab.innerHTML = "...";
  $('.zoomFrame').inspector(src);
  $('.information, .original, .preview').hide();
  $('.zoomFrame').show();
  tab.innerHTML = tText;
}

function showInfo() {
  $('.original, .preview, .zoomFrame').hide();
  $('.information').show();
}

// --- PAGE LINK ACTIONS --- //
var pages;
function loadPage(page) {
  if(!pages[page]) {
    $.get('page-tabs?page='+page+'&id='+documentID,function(response) {
      pages[page] = $.parseJSON(response);
      setTabs(pages[page]);
    });
  }
  else setTabs(pages[page]);
}
function setTabs(srcs) {
  var tabs = '<a onClick="showOriginal(\''+srcs['original']+'\')">Original</a>'+
             '<a onClick="showPreview(\''+srcs['large']+'\',this)">Large</a>'+
             '<a onClick="showPreview(\''+srcs['medium']+'\',this)">Medium</a>'+
             '<a onClick="showZoom(\''+srcs['large']+'\',this)">Zoom</a>'+
             '<a onClick="showInfo()">Information</a>';
  $(".view .navigation").html(tabs);
  // - Re-assign the click event handlers
  $('.view .navigation a').each(function (index) {
    $(this).click(function () {
      $('.view .navigation a.selected').removeClass('selected');
      $(this).addClass('selected');
      currTab = index;
    });
    if(index == currTab)    // SET THE MIDDLE TAB (medium) TO THE ACTIVE ONE
      $(this).click();
  });  
}

var loadingThumbs = true;
function createPageLinks() {
  loadingThumbs = true;
  var currEnd = $('.page_list .page_link').size();
  //console.log(currEnd);
  if(currEnd >= pages.length) {    
      $('.side-loading').html('All Pages Loaded');
  }
  else if(pages[currEnd]) {
    $('div.page_list').append('<div class="page_link new"><img src="'+pages[currEnd]['thumbnail']+'">'+pages[currEnd]['label']+'</div>');
    // Make sure we're clear so that this lock doesn't go balistic
    if($('.page_list .page_link:last-child').offset().top-$(this).scrollTop()+120 < $(window).height())
      createPageLinks();
    else
      loadingThumbs = false;
  }  
  setPageLinkClicks();
  /*
  $.get('img-src?file='+fileName+'&page='+pages[currEnd]['id']+'&use=thumbnail', function(src) {
  }); //*/
}

function setPageLinkClicks() {
  $('.page_link').each(function (index) {
    if($(this).is('.new')) {
      $(this).click(function () {
        $('.page_link.selected').removeClass('selected');
        $(this).addClass('selected');
        loadPage(index);
        //console.log('click '+index);
      });
      $(this).removeClass('new');
    }
  });
  if($('.page_list .page_link.selected').size() == 0) {
    loadPage(0);
    $('.page_list .page_link:first-child').addClass('selected');
  }
}

// fit preview to screen
function resizePreview() {
  var height = $(window).height()+$(this).scrollTop()-20-$('.preview').offset().top;
  $('.preview').css({'height':height}); 
}

// --- DOCUMENT READY --- //

var pages;
var currTab = 2;
$(document).ready(function() {  // -- document ready -- //
  
  $.get('page-data?file='+fileName,function(response) {
    pages = $.extend(pages,JSON.parse(response)); // LOTS OF PAGE DATA GO!
    loadingThumbs = false;
  });
  
  // PAGE LIST NAVIGATION
  $('.side_nav .top').each(function (index) {
    $(this).click(function () {
      $('a.top.selected').removeClass('selected');
      $(this).addClass('selected');
    });
    if(index == 0)
      $(this).click();
  });
  
  // fit preview to screen
  resizePreview();  
  
}); // - end ready -

$(window).resize(function() {
  resizePreview();  // fit preview to screen
}).scroll(function() {
  // - floating viewer -
  if (!$('.view').is('.magic_view') && $(this).scrollTop() > 160)
    $('.view').addClass('magic_view');
  else if ($(this).scrollTop() < 160)
    $('.view').removeClass('magic_view');
  
  // fit preview to screen
  resizePreview();
  
  // AJAX thumbnail loading
  if($('.page_list .page_link:last-child').offset().top-$(this).scrollTop()-100 < $(window).height()) { // if the last one is visible, load the next one
    //console.log(!loadingThumbs);
    if(!loadingThumbs)
      createPageLinks();
  }
});