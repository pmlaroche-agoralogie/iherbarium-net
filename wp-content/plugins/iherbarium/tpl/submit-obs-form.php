<h1 class="page-title">Soumettre une observation</h1>
<form id="fileupload" action="<?php echo get_bloginfo('wpurl')?>/observation/thankyou" method="POST" enctype="multipart/form-data"> 
	<noscript>Nécessite l'exécution de scripts</noscript>
	
	<input type="hidden" name="id_user" value="<?php echo $user_id?>"/>
	<input type="hidden" name="uuid_obs" value="<?php echo $uuid_obs?>"/>
	
	
    
    <div id="dropzone" class="fade well">
    <div class="drophere">
    Déposez les fichiers ici <br>ou <br>
    <span class="btn btn-success fileinput-button">
        <i class="glyphicon glyphicon-plus"></i>
        <span>Ajouter des fichiers...</span>
        <!-- The file input field used as target for the file upload widget -->
        <input  type="file" name="files[]" multiple><!--  id="fileupload" -->
    </span>
    </div>
    
    <!-- The global progress bar -->
    <div id="progress" class="progress">
        <div class="progress-bar progress-bar-success"></div>
    </div>
    
    <!-- The container for the uploaded files -->
    <div id="files" class="files"></div>
    
    </div>

    Commentaires:<br>
    <!--  input type="text" name="commentaires" value="">-->
    <textarea name="commentaires" class="commentaires"></textarea>

    <div class="fileupload-buttonbar">
		<div class="fileupload-buttons">
			<button type="submit" class="btn btn-primary start">
				<i class="glyphicon glyphicon-upload"></i>
				<span>Soumettre</span>
			</button>
		</div>
	</div>
    <br>
    
    
</form>

