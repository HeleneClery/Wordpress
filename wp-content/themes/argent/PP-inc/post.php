<?php 
global $id_stage;
$resultat = $wpdb->get_row($wpdb-> prepare("SELECT * FROM stages where id_stage = %d", $id_stage)); ?>
<tr>
	<td>
		<a href="single-post?stage=<?php echo $id_stage?>">
			<div class="blocOffres row">
				<div class="col-sm-1">
					<div class="logo">
						<img src="https://www.cloud-temple.com/wp-content/themes/dragonfly/img/logo-tmpl.png"/>
					</div>
					<div >
						<span class="text-small">
							<?php echo $resultat->entreprise ?>
						</span>
					</div>
				</div>
				<div  class="col-sm-4">
					<div>
						<?php echo $resultat->sujet ?>
					</div>
					<div >
						<span class="text-small">
							<br>
							<?php echo $resultat->prenom." ".$resultat->nom ?>
						</span>
					</div>
				</div>
				<div class="col-sm-2"><?php echo $resultat->secteur ?></div>
				<div class="col-sm-2"><!-- TO DO : boucle technologies --></div>
				<div class="col-sm-1"><?php echo $resultat->promotion ?></div>
				<div class="col-sm-2"><?php echo $resultat->ville ?></div>
			</div>
		</a>
	</td>
</tr>

