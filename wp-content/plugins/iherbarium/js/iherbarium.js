jQuery(function ($) {
	// Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: 'server/php/'
    });

    // Enable iframe cross-domain access via redirect option:
    $('#fileupload').fileupload(
        'option',
        'redirect',
        window.location.href.replace(
            /\/[^\/]*$/,
            '/cors/result.html?%s'
        )
    );
    
    $('#fileupload').fileupload('option', {
        url: '//jquery-file-upload.appspot.com/',
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator.userAgent),
        maxFileSize: 999000,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
    });
    // Upload server status check for browsers with CORS support:
    if ($.support.cors) {
        $.ajax({
            url: '//jquery-file-upload.appspot.com/',
            type: 'HEAD'
        }).fail(function () {
            $('<div class="alert alert-danger"/>')
                .text('Upload server currently unavailable - ' +
                        new Date())
                .appendTo('#fileupload');
        });
    }


});