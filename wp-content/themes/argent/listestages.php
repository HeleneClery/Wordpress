<?php
/*
Template Name: ListeStages
*/
?>

<!-- HEADER -->
<?php get_header();?>

<!-- BODY -->
<!-- zone de recherche -->
<!-- <?php //include 'PP-inc/form-search.php'; ?> -->

<!-- Pagination -->
<?php 
$stagesParPage = 5;
$resultatTotal = $wpdb->get_row("SELECT count(*) AS total FROM stages");
$total = $resultatTotal->total;
$nombreDePages = ceil($total/$stagesParPage);


if($wp_query->query_vars['page']>0){ // Si la variable $_GET['page'] existe...
  global $wp_query;
  $pageActuelle = intval($wp_query->query_vars['page']);

    if($pageActuelle > $nombreDePages){ 
      $pageActuelle = $nombreDePages;
    }
}
else{
  $pageActuelle = 1;
}

$premiereEntree = ($pageActuelle-1) * $stagesParPage;
$resultats = $wpdb->get_results($wpdb-> prepare("SELECT * FROM stages ORDER BY id_stage LIMIT %d, %d", $premiereEntree, $stagesParPage)); 
?>
<!-- En-tête du tableau -->
<?php include 'PP-inc/thead.php'; ?>


    <tbody class="tbody">
        <!-- Contenu des cellules -->
        <?php  
          foreach($resultats as $post){
            //Récupération de l'id du stage, réutilisé pour extraire les information de la bd et les afficher dans le post.php
            $id_stage = $post->id_stage;
            include 'PP-inc/post.php';
            //Incrémentation de l'id de tri, réutilisé pour les boutons suivant/précédent
            $premiereEntree++;
          } 
        ?>
    </tody>
  </table>
</div>
<script src="PP-pagination.js"></script>

<!-- Footer -->
<?php get_footer(); ?>