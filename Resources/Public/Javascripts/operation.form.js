(function($) {
    "use strict";

    // style upload button
    $('#uploadFormButton').click(function() {
        $('#uploadForm [type="file"]').click();
    });

    // attach remove handler to images
    $('#uploaded .remove').click(removeClick);

    // split datetime
    var datetime = $('#datetime').val().match(/(\d\d\.\d\d\.\d\d\d\d) (\d\d\:\d\d)/);
    if (datetime) {
        $('#date').val(datetime[1]);
        $('#time').val(datetime[2]);
    }

    // list of all uploaded images
    var uploadImages;
    try {
        uploadImages = JSON.parse($('#imagesInput').val());
    } catch (err) {}
    uploadImages = uploadImages || [];

    $('#uploadForm [type="file"]').fileupload({
        dataType: 'json',
        url: $('#uploadForm').attr('action'),
        limitMultiFileUploads: 1
    });
    $('#uploadForm [type="file"]').on('fileuploadadd', function(ev, data) {
        var div = $('<div>');
        div.append('<div class="spinner"><div class="loader"/></div>');
        data.thumbnailElement = div;
        $('#uploaded').append(div);
    });
    $('#uploadForm [type="file"]').on('fileuploaddone', function(ev, data) {
        var result = data.result;

        var img = $('<img>');
        img.attr('src', result.files[0].thumbnailurl);

        var remove = $('<div class="remove"/>');
        remove.attr('data-id', result.files[0].identifier);
        remove.click(removeClick);

        data.thumbnailElement.append(img);
        data.thumbnailElement.append(remove);

        // update list of images
        uploadImages.push(result.files[0].identifier);
        $('#imagesInput').val(JSON.stringify(uploadImages));
    });

    $('body').click(function() {
        $('#uploaded .confirm').removeClass('confirm');
    });
    
    $('button.goback').click(function() {
        window.history.back();
    });
    
    $('#operationForm').submit(function() {
        $('#datetime').val($('#date').val() + ' ' + $('#time').val());
    });

    function removeClick() {
        var self = $(this);

        if (self.hasClass('confirm')) {
            self.parent().remove();

            uploadImages = $.grep(uploadImages, function(value) {
                return value != self.attr('data-id');
            });
            $('#imagesInput').val(JSON.stringify(uploadImages));
        } else {
            self.addClass('confirm');
        }

        return false;
    }
}(jQuery));
