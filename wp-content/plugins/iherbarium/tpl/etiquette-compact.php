<?php include ('header_light.php')?>
<table border="0" cellpadding="1" cellspacing="1" class="etiquette" style="font-size:11px;"> 
	<tr>
		<td style="text-align:center;"><img src="<?php echo $urlgoogle?>"></td>
		<td style="padding-top:8px;">
		<span class="important" style="font-size:12px;"><em><?php echo $nameObs;?></em></span><br />
		<span class="important">UUID Specimen</span> : <?php echo $row['uuid_specimen'];?><br/>
		Récolteur : <?php echo $row['id_user'];?> ( <?php echo $row['date_depot'];?> ) / <?php echo $position;?><br />
		Auteur détermination : <?php echo $row2['id_user'];?> ( <?php echo $row2['date'];?> )<br />
		<span class="important">n&deg; <?php echo $row['idobs'];?></span>
		</td>
	</tr>
</table>
<?php include ('footer_light.php')?>
