<?php
/* Template Name: ValidationRapportStage */
get_header();
?>

<?php
// Méthode pour la connexion à la base de données
function connexion_bdd() {
    $host = DB_HOST;
    $user = DB_USER;
    $passwd = DB_PASSWORD;
    $bd = DB_NAME;
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

// Afficher le message s'il n'y pas l'adresse mail dans le lien
if ($email == null) {
    echo '<div align="center"> Veuillez valider votre inscription avec le lien fourni</div>';
    
// Afficher le message s'il n'y a pas la clé d'activation dans le lien
} elseif ($activation_key == null) {
    echo '<div align="center"> Veuillez valider votre inscription avec le lien fourni</div>';

// Vérifier si la clé d'activation dans le lien correspond à celle dans la base de données
} else {
    $pdo = connexion_bdd();
    $requete_activation_key = $pdo->prepare('SELECT user_activation_key FROM wp_m2ccitours_users WHERE user_email=?');
    $requete_activation_key->execute([$email]);
    $resultat_activation_key = $requete_activation_key->fetch();
    $activation_key_bdd = $resultat_activation_key["user_activation_key"];
    
    // Si les 2 clés correspondent --> mettre à jour le statut d'utilisateur dans la base de données et afficher le message
    if ($activation_key == $activation_key_bdd) {
        $requete_update_verif = $pdo->prepare('UPDATE wp_m2ccitours_usermeta SET meta_value="true" WHERE user_id = (SELECT ID from wp_m2ccitours_users WHERE user_email = ?) AND meta_key="verif"');
        $requete_update_verif->execute([$email]);
        echo '<div align="center"> Félicitation! Vous pouvez maintenant accéder à l\'espace Rapport de stage M2 CCI Tours</div>';
        echo '
        <script type="text/javascript">
    window.setTimeout(function() {
        window.location.href=\'/wordpress/connexion/\';
    }, 5000);
        </script>';
    } else {
        echo '<div align="center"> Veuillez valider votre inscription avec le lien fourni</div>';
    }
}
?>

<?php
get_footer();
