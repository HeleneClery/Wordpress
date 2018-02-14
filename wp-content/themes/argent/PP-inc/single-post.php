<?php
/*
Template Name: FicheDeStage
*/
?>
<?php 
global $id_stage;
$resultat = $wpdb->get_row($wpdb-> prepare("SELECT * FROM stages where id_stage = %d", $id_stage)); ?>

<!-- HEADER -->
<?php get_header();?>

    
<!-- BODY-->
<div class=" row">
   
    <div class="col-sm-12 center">
      <h2><?php echo $resultat->sujet ?></h2><br/>
    </div>
    <div class="col-sm-12 center">
      Promotion <?php echo $resultat->promotion ?>
    </div>
 
          
   <div class="col-sm-12 divEntete row">
      <div class="col-sm-8 carac"> 
        <span class="label label-default">Entreprise</span> <?php echo $resultat->entreprise ?><br/>
        <span class="label label-default">Secteur d'activité</span> <?php echo $resultat->secteur ?><br/>
        <span class="label label-default">Technologies</span> <!-- TO DO : Boucle technologies --><br/>
        <span class="label label-default">Localisation</span> <?php echo $resultat->ville ?><br/>
      </div>
      <div class="col-sm-4 logo" >
        <img src="https://www.cloud-temple.com/wp-content/themes/dragonfly/img/logo-tmpl.png"/>             
      </div>
    </div>
    <div class="col-sm-12">
      <?php if($resultat->admission == 0): ?>
        <span class="label label-success spanCenter">Stage de pré-embauche</span>
      <?php elseif($resultat->admission == 1): ?>
        <span class="label label-warning spanCenter">Pas d'embauche</span>
      <?php endif; ?>
    </div>
  
    <div class="col-sm-12">
      <h3 class="center">Résumé du stage</h3>
      <?php echo $resultat->resume_stage ?>
    </div>

    <div class=" col-sm-offset-2 center download">
      <a class="btn btn-info" title="telecharger" href="#" >Rapport de stage</a>
      <a class="btn btn-info" href="" >Note de synthèse</a>            
    </div>


</div>
<!-- FIN BODY-->




<!-- FOOTER -->
<?php get_footer(); ?>