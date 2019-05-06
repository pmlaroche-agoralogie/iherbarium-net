<?php include ('header_light.php')?>
<div class="etiquette complete"> 
		<div><img src="<?php echo $urlgoogle?>"><?php  echo $imgs;?></div>
		<div>
		<span class="important">N° obs.</span> : <?php echo $row['idobs'];?><br>
		<span class="important">Récolteur</span> : <?php echo $authorRecolt;?><br>
		<span class="important">Date de récolte</span> : <?php echo $row['original_timestamp'];?><br>
		<span class="important">Localisation</span> : <?php echo $position;?><br>
		<span class="important">Notes</span> : <?php echo $row['commentaires'];?><br><br>
		
		<span class="important">Numéro de référence récolteur</span> : <br>
		<span class="important">Adresse</span> :  <?php echo $row['address'];?><br><br>
		<span class="famille"><?php echo $row2['famille'];?></span><br>
		<span class="important">Détermination</span> : <?php echo $nameObs;?>  &nbsp; &nbsp; &nbsp; &nbsp;<?php echo $authorDeterminObs;?><br/>
		<span class="important">UUID Specimen</span> :  <?php echo $row['uuid_specimen'];?><br/>
		</div>
</div>
<?php include ('footer_light.php')?>