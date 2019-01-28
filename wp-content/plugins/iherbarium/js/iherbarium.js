jQuery(function ($) {
	'use strict';
    // Change this to the location of your server-side upload handler:
    /*var url = window.location.hostname === 'blueimp.github.io' ?
                '//jquery-file-upload.appspot.com/' : 'server/php/',*/
	/*var url = '?ihaction=newobs';
	var uploadButton = $('<button/>')
            .addClass('btn btn-primary')
            .prop('disabled', true)
            .text('Processing...')
            .on('click', function () {
                var $this = $(this),
                    data = $this.data();
                $this
                    .off('click')
                    .text('Abort')
                    .on('click', function () {
                        $this.remove();
                        data.abort();
                    });
                data.submit().always(function () {
                    $this.remove();
                });
            });*/
	var cancelButton = $('<button/>')
    .addClass('btn btn-primary cancel')
    .prepend('<i class="glyphicon glyphicon-ban-circle"/>')
    .append($('<span/>').text('Processing...'))
    .on('click', function (e) {
    		e.preventDefault();
        var $this = $(this),
            data = $this.data();
        if (data.abort) {
            data.abort();console.log('t5t');
        } else {
            data.errorThrown = 'abort';
           $('#fileupload').trigger('fail', e, data);console.log('t6t');
        }}
        );
	
/*	var fileUploadButtonBar = this.element.find('.fileupload-buttonbar'),
    filesList = this.options.filesContainer;
this._on(fileUploadButtonBar.find('.start'), {
    click: function (e) {
        e.preventDefault();
        filesList.find('.start').click();
    }
});
this._on(fileUploadButtonBar.find('.cancel'), {
    click: function (e) {
        e.preventDefault();
        filesList.find('.cancel').click();
    }
});*/
        
       
	
        
  var myFileUpload =  $('#fileupload').fileupload({
        //url: url,
        dataType: 'json',
        autoUpload: false,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        maxFileSize: 999000,
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator.userAgent),
        previewMaxWidth: 100,
        previewMaxHeight: 100,
        previewCrop: true
    }).on('fileuploadadd', function (e, data) {
        data.context = $('<div/>').appendTo('#files');
        
        $.each(data.files, function (index, file) {
            var node = $('<p/>')
                    .append($('<span/>').text(file.name));
            if (!index) {
                node
                    .append('<br>')
                    //.append(uploadButton.clone(true).data(data));
                    .append(cancelButton.clone(true).data(data));
            }
            node.appendTo(data.context);
        }
        );
        
        
        var fileUploadButtonBar = $('body').find('.fileupload-buttonbar');
        fileUploadButtonBar.find('.start').on('click',function (e) {
                e.preventDefault();
               // filesList.find('.start').click();
                console.log('submit');
                data.submit();
                /* TODO: submit each files via ajax then submit form if ok and change page, verify form before submit*/
            });

        
        
    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index,
            file = data.files[index],
            node = $(data.context.children()[index]);
        if (file.preview) {
            node
                .prepend('<br>')
                .prepend(file.preview);
        }
        if (file.error) {
            node
                .append('<br>')
                .append($('<span class="text-danger"/>').text(file.error));
        }
        if (index + 1 === data.files.length) {
            data.context.find('button.cancel span')
                .text('Cancel')
                .prop('disabled', !!data.files.error);
        }
    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#progress .progress-bar').css(
            'width',
            progress + '%'
        );
    }).on('fileuploaddone', function (e, data) {
        $.each(data.result.files, function (index, file) {
            if (file.url) {
                var link = $('<a>')
                    .attr('target', '_blank')
                    .prop('href', file.url);
                $(data.context.children()[index])
                    .wrap(link);
            } else if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
        });
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {
        	
        	if (data.errorThrown !== 'abort')
        	{
        		var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
        	}
        	else
        	{
        		console.log('abort');
        		$(this).remove();
        	}
            
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
	


});