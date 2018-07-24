<?php include ('header_light.php')?>
<div class="etiquette classic"> 
		<div><img src="<?php echo $urlgoogle?>"></div>
		<div>
		<span class="important title"><em><?php echo $nameObs;?></em></span><br>
		<span class="important">N° obs.</span> : <?php echo $row['idobs'];?><br>
		<span class="important">Récolteur</span> : <?php echo $row['id_user'];?><br>
		<span class="important">Date de récolte</span> : <?php echo $row['date_depot'];?><br>
		<span class="important">Localisation</span> : <?php echo $position;?><br>
		<span class="important">Auteur d&eacutetermination</span> : <br>
		<span class="important">Nom </span> : <?php echo $authorDeterminObs;?> <br>
		Identifiants uniques :<br>
		<span class="important">UUID Specimen</span> :  <?php echo $row['uuid_specimen'];?><br/>
        <span class="important">UUID Observation</span> : <?php echo $row['uuid_observation'];?><br/>
		</div>
</div>
<?php include ('footer_light.php')?>

