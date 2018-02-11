<?php 
global $id_stage;
$resultat = $wpdb->get_row($wpdb-> prepare("SELECT * FROM stages where id_stage = %d", $id_stage)); ?>
<tbody>
	<tr>
		<td>
			<a href="48155_offre-emploi-charge-mission-scientifique-specialite--habitats-pelagiques-et-eutrophisation--h-f.html">
				<div class="blocOffre row">
					<div class="col-sm-2">
						<div class="logo">
							<img src="https://www.cloud-temple.com/wp-content/themes/dragonfly/img/logo-tmpl.png"/>
						</div>
					</div>
					<div class="col-sm-2"><span class="text-upper">
						<?php echo $resultat->entreprise ?>.
					</span></div>
					<div class="col-sm-4"><div class="text-bold f18">
						<?php echo $resultat->sujet ?>
					</div>
					<small><?php echo $resultat->promotion."-".$resultat->prenom." ".$resultat->nom ?></small></div>
					<div class="col-sm-2"><?php echo $resultat->secteur ?></div>
					<div class="col-sm-2"><?php echo $resultat->ville ?></div>
				</div>
			</a>
		</td>
	</tr>
</tody>
</table>
</div>