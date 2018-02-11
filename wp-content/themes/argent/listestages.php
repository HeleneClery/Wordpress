<?php
/*
Template Name: ListeStages
*/
?>


<!-- header -->
<?php get_header();?>

<!-- zone de recherche -->
<!-- <?php //include 'PP-inc/form-search.php'; ?> -->

<!-- initialisation du compteur -->
<?php 
$resultats = $wpdb->get_results("SELECT * FROM stages");
$cpt = 0;
foreach($resultats as $post){
  $cpt++;
}
?>
<!-- En-tête du tableau -->
<?php include 'PP-inc/thead.php'; ?>

<!-- Boucle : contenu du tableau = liste des stages -->
<?php  
  foreach($resultats as $post){
    //Récupération de l'id du stage, réutilisé pour extraire les information de la bd et les afficher dans le post.php
    $id_stage = $post->id_stage; 
    include 'PP-inc/post.php';
  } 
?>



<?php get_sidebar(); ?>
<?php get_footer(); ?>