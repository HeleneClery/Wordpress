<?php
/*
Template Name: FicheDeStage
*/
?>

<!-- HEADER -->
<?php get_header();?>

<!-- BODY-->
<?php 
  require_once("function.php");
  $id_stage = intval($_GET['stage']);
  global $wpdb;
  $resultat = $wpdb->get_row($wpdb-> prepare("SELECT * FROM stages where id_stage = %d", $id_stage));
  
?>  

<div class="container">
  
  <div class="col-sm-12">
    <ul class="navigationPages singlePost">
      <?php 
        $resultats = $wpdb->get_row($wpdb-> prepare("SELECT id_stage FROM stages WHERE id_stage < %d ORDER BY id_stage DESC LIMIT 0,1", $id_stage));
        if (isset($resultats)) {
          echo "<li class=\"gauche\"><a href=\"single-post?stage=".$resultats->id_stage."\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Précédent</a></li>";
        }
        echo "<li><a href=\"rapports-de-stage?>\"><span class=\"glyphicon glyphicon-menu-hamburger\"></span> Retour aux résultats</a></li>";         
        $resultats = $wpdb->get_row($wpdb-> prepare("SELECT id_stage FROM stages WHERE id_stage > %d ORDER BY id_stage LIMIT 0,1", $id_stage));
        if (isset($resultats)) {
          echo "<li class=\"droite\"><a href=\"single-post?stage=".$resultats->id_stage."\">Suivant <span class=\"glyphicon glyphicon-arrow-right\"></span></a></li>";
        }
      ?>
      <span class=\"glyphicon glyphicon-triangle-right\"></span>
    </ul>  
  </div>
 
  <div class="col-sm-12 center">
    <h2><?php echo $resultat->sujet ?></h2>
  </div>

  <div class="col-sm-12 center">
    Promotion <?php echo $resultat->promotion ?>
  </div>

        
 <div class="col-sm-12 divEntete row">
    <div class="col-sm-8 carac"> 
      <span class="label label-default">Entreprise</span><span class="simpletext"><?php echo $resultat->entreprise ?></span><br/>
      <span class="label label-default">Secteur d'activité</span> <span class="simpletext"><?php echo $resultat->secteur ?></span><br/>
      <span class="label label-default">Technologies</span> <span class="simpletext"><?php echo ListeTechno($id_stage) ?></span><br/>
      <span class="label label-default">Localisation</span> <span class="simpletext"><?php echo $resultat->ville ?></span><br/>
    </div>
    <div class="col-sm-4 logo" >
      <!-- TO DO : insérez dans la bd un colonne pour les liens vers les images -->
      <img src="https://www.cloud-temple.com/wp-content/themes/dragonfly/img/logo-tmpl.png"/>             
    </div>
  </div>

  <div class="col-sm-12">
    <?php if($resultat->admission == 1): ?>
      <span class="label label-success spanCenter">Stage de pré-embauche</span>
    <?php elseif($resultat->admission == 0): ?>
      <span class="label label-default spanCenter">Pas d'embauche</span>
    <?php endif; ?>
  </div>

  <div class="col-sm-12">
    <h3 class="center">Résumé du stage</h3>
    <p><?php echo $resultat->resume_stage ?></p>
  </div>
  
  <!-- TO DO : condition -> uniquement pour les membres -->
  <!-- TO DO : Lien vers le pdf -->
  <div class="col-sm-12 row">
    <div class="col-sm-4"></div>
    <div class="col-sm-4 center download">
      <a class="btn btn-info" title="telecharger" href="" ><span class="glyphicon glyphicon-download-alt"></span> Rapport de stage</a>
      <a class="btn btn-info" title="telecharger" href="" ><span class="glyphicon glyphicon-download-alt"></span> Note de synthèse</a>
    </div>
    <div class="col-sm-4"></div>
  </div>

</div>

<!-- FOOTER -->
<?php get_footer(); ?>