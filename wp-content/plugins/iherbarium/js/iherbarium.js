jQuery(function ($) {
	
	'use strict';
	var url = '?ihaction=submitobs' ;
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

        if (data.abort) {
            data.abort();
            template.remove();
        } else {
            data.errorThrown = 'abort';
           $('#fileupload').trigger('fail', e, data);
        }}
        );
        
  var myFileUpload =  $('#fileupload').fileupload({
        url: url,
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
        previewCrop: true,
        sequentialUploads: true,
        dropZone: $('#dropzone'),
    }).on('fileuploadadd', function (e, data) {
        data.context = $('<div/>').addClass('file').appendTo('#files');
        
        $.each(data.files, function (index, file) {
        		var node = $('<div/>').append($('<div class="name"/>').text(file.name));
            if (!index) {
                node
                    .append(cancelButton.clone(true).data(data));
            }
            node.append($('<div class="clearboth"/>')).appendTo(data.context);
        }
        );
        
        
        var fileUploadButtonBar = $('body').find('.fileupload-buttonbar');
        fileUploadButtonBar.find('.start').on('click',function (e) {
                e.preventDefault();
                if (typeof data.result !== 'undefined') 
                if (data.result.id_obs)
    				{
    				
    					$("input[name=id_obs]").val(data.result.id_obs);
    				}

                if (data.files.length)
                	{
                		$('.drophere').hide();
                		$('.progress').show();
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
                .prepend(file.preview);
        }
        if (file.error) {
            node
                .append('<br>')
                .append($('<span class="label label-danger"/>').text('Erreur'))
                .append(file.error);
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

    		if (data.result.status!='error') {
    			$.each(data.files, function (index) {
    				$(data.context).remove();
    				data.files.splice(index,1);
    				if ($('#files > *').length == 0)
    				{
    					$('#fileupload').submit();
    				}
    			});
    		}
    		else
    		{
    			$.each(data.files, function (index) {
    			
    			var error = ($('<span class="label label-danger"/>').text('Erreur'));
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error)
                    .append(data.result.file.error);
    			});
    		}
   // });
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {       	
	        	if (data.errorThrown !== 'abort')
	        	{
	        		var error = ($('<span class="label label-danger"/>').text('Erreur'));
	            $(data.context.children()[index])
	                .append('<br>')
	                .append(error)
	                .append('File upload failed.');
	        	}
	        	else
	        	{
	        		//console.log('abort');
	        		$(this).remove();
	        		
	        		data.files.splice(index,1);
	        	}
            
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
	

  $(document).bind('dragover', function (e) {
	    var dropZone = $('#dropzone'),
	        timeout = window.dropZoneTimeout;
	    if (timeout) {
	        clearTimeout(timeout);
	    } else {
	        dropZone.addClass('in');
	    }
	    var hoveredDropZone = $(e.target).closest(dropZone);
	    dropZone.toggleClass('hover', hoveredDropZone.length);
	    window.dropZoneTimeout = setTimeout(function () {
	        window.dropZoneTimeout = null;
	        dropZone.removeClass('in hover');
	    }, 100);
	});

	$(document).bind('drop dragover', function (e) {
	    e.preventDefault();
	});
});



