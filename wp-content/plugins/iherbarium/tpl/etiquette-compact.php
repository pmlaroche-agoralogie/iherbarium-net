<?php include ('header_light.php')?>
<div class="etiquettes_marges"> 
<div class="etiquette compact"> 
		<div><img src="<?php echo $urlgoogle?>"></div>
		<div>
		<span class="important title"><?php echo $nameObs;?></span><br>
		<span class="important">UUID Specimen</span> : <?php echo $row['uuid_specimen'];?><br>
		Récolteur : <?php echo $authorRecolt;?> ( <?php echo $row['original_timestamp'];?> ) / <?php echo $position;?><br>
		Auteur détermination : <?php echo $authorDeterminObs;?> <br>
		<span class="important">n&deg; <?php echo $row['idobs'];?></span>
		</div>
</div>
</div>
<?php include ('footer_light.php')?>
