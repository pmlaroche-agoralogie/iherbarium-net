<form id="fileupload" action="?ihaction=newobs" method="POST" enctype="multipart/form-data"> 
	<noscript>Nécessite l'exécution de scripts</noscript>
	
	
	
	
	<span class="btn btn-success fileinput-button">
        <i class="glyphicon glyphicon-plus"></i>
        <span>Add files...</span>
        <!-- The file input field used as target for the file upload widget -->
        <input  type="file" name="files[]" multiple><!--  id="fileupload" -->
    </span>
    
    
    <br>
    <br>
    <!-- The global progress bar -->
    <div id="progress" class="progress">
        <div class="progress-bar progress-bar-success"></div>
    </div>
   
    <!-- The container for the uploaded files -->
    <div id="files" class="files"></div>
    <input type="text" name="montest" value="truc">
    <div class="fileupload-buttonbar">
		<div class="fileupload-buttons">
			<button type="submit" class="btn btn-primary start">
				<i class="glyphicon glyphicon-upload"></i>
				<span>Start upload</span>
			</button>
		</div>
	</div>
    <br>
    
    
</form>

