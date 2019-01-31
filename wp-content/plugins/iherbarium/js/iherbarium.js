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
    .addClass('btn btn-danger cancel')
    .prepend('<i class="glyphicon glyphicon-ban-circle"/>').append(' ')
    .append($('<span/>').text('Processing...'))
    .on('click', function (e) {
    		e.preventDefault();
    		var template = $(e.currentTarget)
            .closest('.file');
            
       var $this = $(this),
            data = $this.data();

        console.log(data);

        if (data.abort) {
            data.abort();
            console.log('abort1');
            template.remove();
        } else {
            data.errorThrown = 'abort';console.log('abort2');
           $('#fileupload').trigger('fail', e, data);
        }}
        );
        
  var myFileUpload =  $('#fileupload').fileupload({
        //url: url,
        dataType: 'json',
        autoUpload: false,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        //maxFileSize: 999000,
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator.userAgent),
        previewMaxWidth: 100,
        previewMaxHeight: 100,
        previewCrop: true
    }).on('fileuploadadd', function (e, data) {
        data.context = $('<div/>').addClass('file').appendTo('#files');
        
        $.each(data.files, function (index, file) {
            /*var node = $('<p/>')
                    .append($('<span/>').text(file.name));*/
        		var node = $('<span/>').text(file.name);
            if (!index) {
                node
                    //.append('<br>')
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
                console.log(data);
               
                if (data.files.length)
                	{
                		data.submit();
                	}
                /* TODO: submit each files via ajax then submit form if ok and change page, verify form before submit*/
            });

        
        
    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index,
            file = data.files[index],
            node = $(data.context.children()[index]);
        if (file.preview) {
            node
                //.prepend('<br>')
                .prepend(file.preview);
        }
        if (file.error) {
            node
                .append('<br>')
                .append($('<span class="label label-danger"/>').text('Erreur')).text(file.error);
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
                var error = ($('<span class="label label-danger"/>').text('Erreur')).text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
        });
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {
   /* 	if (data.context) {
            data.context.each(function (index) {*/
        	
        	if (data.errorThrown !== 'abort')
        	{
        		var error = ($('<span class="label label-danger"/>').text('Erreur')).append('File upload failed.');
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
            console.log('toto');
        	}
        	else
        	{
        		console.log('abort');
        		$(this).remove();
        		
        		data.files.splice(index,1);
        	}
            
        });
   // 	}
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
	


});

