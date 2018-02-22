<div id="tab">
	<div class="gauche"><?php echo $total ?> stages</div>
	<!-- TO DO : améliorer la navigation ( intervalle) -->
	<div class="droite">
		<ul class="navigationPages">
			<li><a href="rapports-de-stage/"><span class="glyphicon glyphicon-chevron-left"></span></a></li>
			<?php 
			if ($nombreDePages<5) {
				for ($i=1; $i<=$nombreDePages ; $i++) { 
				if ($i==$pageActuelle) {
					echo "<li ><a class=\"pageActuelle\" href=\"#\">".$i."</a></li>";
				} else {
					echo "<li><a href=\"rapports-de-stage/".$i."/\">".$i."</a></li>";
				}
			} 
			} else {
				echo "<li><a href=\"rapports-de-stage/".$pageActuelle-2."/\">".$pageActuelle-2."</a></li>";
				echo "<li><a href=\"rapports-de-stage/".$pageActuelle-1."/\">".$pageActuelle-1."</a></li>";
				echo "<li ><a class=\"pageActuelle\" href=\"#\">".$pageActuelle."</a></li>";
				echo "<li><a href=\"rapports-de-stage/".$pageActuelle+1."/\">".$pageActuelle+1."</a></li>";
				echo "<li><a href=\"rapports-de-stage/".$pageActuelle+2."/\">".$pageActuelle+2."</a></li>";
			}
			?>
			
			
			<li><a href="rapports-de-stage/<?php echo $nombreDePages; ?>"><span class="glyphicon glyphicon-chevron-right"></span></a></li>
		</ul>
	</div>
</div>
<table class="table">
	<thead>
	<tr>
		<th>
		<div class="enteteOffres row">
			<div class="col-sm-1 hidden-xs">Entreprise</div>
			<div class="col-sm-4 hidden-xs">Intitulé du stage</div>
			<div class="col-sm-2 hidden-xs">Secteur d'activité</div>
			<div class="col-sm-2 hidden-xs">Technologies</div>
			<div class="col-sm-1 hidden-xs">Promotion</div>
			<div class="col-sm-2 hidden-xs">Localisation</div>
		</div>
		</th>
	</tr>
	</thead>