<?php
/*
Template Name: FicheDeStage
*/
?>
<?php 
  $id_stage = $_GET['stage'];
  global $wpdb;
  $resultat = $wpdb->get_row($wpdb-> prepare("SELECT * FROM stages where id_stage = %d", $id_stage));
  require("function.php"); 
?>

<!-- HEADER -->
<?php get_header();?>

    
<!-- BODY-->
<div class="container">
   
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
    <div class=" col-sm-offset-2 center download">
      <span class="glyphicon glyphicon-download-alt">
      <a class="btn btn-info" title="telecharger" href="" ><span class="glyphicon glyphicon-download-alt"></span>Rapport de stage</a>
      <a class="btn btn-info" title="telecharger" href="" ><span class="glyphicon glyphicon-download-alt"></span>Note de synthèse</a>            
    </div>


</div>
<!-- FIN BODY-->




<!-- FOOTER -->
<?php get_footer(); ?>