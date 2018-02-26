<?php
/* Template Name: ValidationRapportStage */
get_header();
?>

<?php

function connexion_bdd() {
    $host = "localhost";
    $user = "root";
    $passwd = "";
    $bd = "rapport_stage";
    $connexion = "mysql:host=$host;dbname=$bd";
    $opt = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($connexion, $user, $passwd, $opt);
    return $pdo;
}

// Récupération des variables nécessaires à l'activation
$email = $_GET['email'];
$activation_key = $_GET['activation_key'];

if ($email == null) {
    echo '<div align="center"> Veuillez valider votre inscription avec le lien fourni</div>';
} elseif ($activation_key == null) {
    echo '<div align="center"> Veuillez valider votre inscription avec le lien fourni</div>';
} else {
    $pdo = connexion_bdd();
    $requete_activation_key = $pdo->prepare('SELECT user_activation_key FROM wp_users WHERE user_email=?');
    $requete_activation_key->execute([$email]);
    $resultat_activation_key = $requete_activation_key->fetch();
    $activation_key_bdd = $resultat_activation_key["user_activation_key"];
    if ($activation_key == $activation_key_bdd) {
        $requete_update_verif = $pdo->prepare('UPDATE wp_usermeta SET meta_value="true" WHERE user_id = (SELECT ID from wp_users WHERE user_email = ?) AND meta_key="verif"');
        $requete_update_verif->execute([$email]);
        echo '<div align="center"> Félicitation! Vous pouvez maintenant accéder à l\'espace Rapport de stage M2 CCI Tours</div>';
        echo do_shortcode('[wpmem_form login]',true);
    } else {
        echo '<div align="center"> Veuillez valider votre inscription avec le lien fourni</div>';
    }
    
}

?>


<?php
get_footer();
