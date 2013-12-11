// default start date for publication date slider
var PublishDateSliderMin = 1450;

// change default start date
function setPublishDateSliderMin(year) {
    PublishDateSliderMin = year;
}

function updatePublishDateSlider(prefix) {
    var from = parseInt($('#' + prefix + 'from').val(), 10);
    var to = parseInt($('#' + prefix + 'to').val(), 10);

    var min = PublishDateSliderMin;
    if (!from || from < min) {
        from = min;
    }
    // keep the max at 1 years from now
    var max = (new Date()).getFullYear() + 1;
    if (!to || to > max) {
        to = max;
    }
    if (from > max) {
        from = max;
    }
    // update the slider with the new min/max/values
    $('#' + prefix + 'Slider').slider('option', {
        min: min, max: max, values: [from, to]
    });
}

function makePublishDateSlider(prefix) {
    // create the slider widget
    $('#' + prefix + 'Slider').slider({
        range: true,
        min: 0, max: 9999, values: [0, 9999],
        slide: function(event, ui) {
            $('#' + prefix + 'from').val(ui.values[0]);
            $('#' + prefix + 'to').val(ui.values[1]);
        },
        change: function(event, ui) {
            $('#' + prefix + 'from').attr('name',prefix + 'from'); //activate Slider, when changed
            $('#' + prefix + 'to').attr('name',prefix + 'to');
        }
    });
    // initialize the slider with the original values
    // in the text boxes
    updatePublishDateSlider(prefix);

    // when user enters values into the boxes
    // the slider needs to be updated too
    $('#' + prefix + 'from, #' + prefix + 'to').change(function(){
        updatePublishDateSlider(prefix);
    });
}

$(document).ready(function(){
    // create the slider for the publish date facet
    $('.dateSlider').each(function(i) {
        var myId = $(this).attr('id');
        var prefix = myId.substr(0, myId.length - 6);
        makePublishDateSlider(prefix);
        $('#' + prefix + 'from').attr('name',''); //deactivate Slider in search
        $('#' + prefix + 'to').attr('name','');
    });
});
