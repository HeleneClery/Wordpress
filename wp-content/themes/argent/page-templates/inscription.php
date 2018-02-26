<?php
/* Template Name: InscriptionRapportStage */
get_header();
?>

<?php

// connexion à la base de données rapport_stage avec PDO
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
$guid = $_GET['guid'];
$message = $_GET['message'];

if ($message == "oui") {
    echo '<div align="center"> Votre inscription est bien enregistrée. Un email de validation a été envoyé à votre adresse mail. Veuillez valider votre inscription avec le lien fourni dans ce mail.</div>';
} else {

// Vérification s'il y a email dans le lien
    if ($email == null) {
        echo 'Cette espace est réservée aux étudiants CCI Tours de la promotion actuelle. Si vous êtes un ancien étudiant et vous êtes intéressé par cette page, veuillez contacter xxx@yyy.com';
    } elseif ($guid == null) {
        echo 'Veuillez vous inscrire avec le lien fourni dans le mail envoyé';
    } else {
        // Récupération guid d'email dans la BDD
        $pdo = connexion_bdd();
        $requete_verif_guid = $pdo->prepare('SELECT es_email_guid FROM wp_es_emaillist WHERE es_email_mail=?');
        $requete_verif_guid->execute([$email]);
        $resultat_guid = $requete_verif_guid->fetch();
        $guid_bdd = $resultat_guid["es_email_guid"];

        if ($guid == $guid_bdd) {
            ?>
            <!--formulaire d'inscription-->

            <html>
                <head>
                    <title>Inscription</title>
                    <meta charset = "UTF-8">
                    <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
                    <style>
                        form {
                            display: inline-block;
                            text-align: left;
                        }
                        input {
                            width:400px;
                        }
                        label {
                            float:left;
                        }
                        h2 {
                            text-align: center;
                        }
                        .erreur {
                            color: red;
                            text-align: center;
                            font-size: 12pt;
                        }
                        input[type="submit"] {
                            color: black;
                        }
                    </style>

                    <script type="text/javascript">
                        function erreur(id, message) {
                            document.getElementById(id).innerHTML = message;
                        }
                    </script>

                </head>
                <body>
                    <h2>
                        INSCRIPTION
                    </h2>

                    <div id ="erreur_identifiant" class="erreur"></div>
                    <div id ="erreur_email" class="erreur"></div>
                    <div id ="erreur_verif_email" class="erreur"></div>
                    <div id ="erreur_verif_mdp" class="erreur"></div>

                    <div align = "center">
                        <form method="POST">
                            <label class = "form-label-identifiant" for = "inscription">Identifiant <span class = "required" title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" maxlength = "128" name = "identifiant" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["identifiant"]) ? $_POST["identifiant"] : ""; ?>" /></div>
                            <label class = "form-label" for = "inscription">Nom <span class = "required" title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" maxlength = "128" name = "nom" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["nom"]) ? $_POST["nom"] : ""; ?>" /></div>
                            <label class = "form-label" for = "inscripton">Prenom <span class = "required" title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" maxlength = "128" name = "prenom" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["prenom"]) ? $_POST["prenom"] : ""; ?>"/></div>
                            <label class = "form-label" for = "inscripton">Promotion <span class = "required" title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" maxlength = "128" name = "promotion" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["promotion"]) ? $_POST["promotion"] : ""; ?>"/></div>
                            <label class = "form-label" for = "inscription">Adresse <span class = "required" title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" maxlength = "128" name = "adresse" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["adresse"]) ? $_POST["adresse"] : ""; ?>"/></div>
                            <label class = "form-label" for = "inscription"> Complément adresse <span class = "k4"></span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text" maxlength = "128" name = "comp_adresse" size = "60" type = "text" value = "<?php echo isset($_POST["comp_adresse"]) ? $_POST["comp_adresse"] : ""; ?>"/></div>
                            <label class = "form-label" for = "inscription">Ville <span class = "required" title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" maxlength = "128" name = "ville" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["ville"]) ? $_POST["ville"] : ""; ?>"/></div>
                            <label class = "form-label" for = "inscription">Département <span class = "k4"></span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text" maxlength = "128" name = "departement" size = "60" type = "text" value = "<?php echo isset($_POST["departement"]) ? $_POST["departement"] : ""; ?>"/></div>
                            <label class = "form-label" for = "inscription">Code postal <span class = "required" title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" maxlength = "128" name = "code_postal" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["code_postal"]) ? $_POST["code_postal"] : ""; ?>"/></div>
                            <label class = "form-label" for = "inscription">Pays <span class = "required" title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" maxlength = "128" name = "pays" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["pays"]) ? $_POST["pays"] : ""; ?>"/></div>
                            <label class = "form-label" for = "inscription">Téléphone <span class = "required" title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" maxlength = "128" name = "telephone" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["telephone"]) ? $_POST["telephone"] : ""; ?>"/></div>
                            <label class = "form-label-email" for = "inscription">Email <span title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" required aria-required = "true" data-validate-required-message = "Ce champ est requis." data-validate-email-message = "S\'il vous plaît vérifier que votre adresse e-mail est selon le format suivant: name@gmail.com. Elle ne peut pas contenir de caractères spéciaux." type = "email" data-validate-type-message = "L\'email saisi est incorrect." id = "email" name = "email" size = "60" maxlength = "128" value = "<?php echo isset($_POST["email"]) ? $_POST["email"] : ""; ?>"/></div>
                            <label class = "form-label-email-verif" for = "inscription">Confirmer Email <span title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" required aria-required = "true" data-validate-required-message = "Ce champ est requis." data-validate-email-message = "S\'il vous plaît vérifier que votre adresse e-mail est selon le format suivant: name@gmail.com. Elle ne peut pas contenir de caractères spéciaux." type = "email" data-validate-type-message = "L\'email saisi est incorrect." id = "verif_email" name = "verif_email" size = "60" maxlength = "128" value = "<?php echo isset($_POST["verif_email"]) ? $_POST["verif_email"] : ""; ?>"/></div>
                            <label class = "form-label-mdp" for = "inscription">Mot de passe <span class = "required" title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" maxlength = "128" name = "mdp" required = "" size = "60" type = "password" aria-required = "true" data-validate-required-message = "Ce champ est requis." /></div>
                            <label class = "form-label-mdp" for = "inscription">Confirmer Mot de passe <span class = "required" title = "Ce champ est requis.">*</span></label>
                            <div class = "form-input"><input id = "inscription" class = "form-text required" maxlength = "128" name = "verif_mdp" required = "" size = "60" type = "password" aria-required = "true" data-validate-required-message = "Ce champ est requis." /></div><br/>
                            <input type = "submit" name = "submit_inscription" value = "Valider"/>
                        </form>
                    </div>
                </body>
            </html>

            <?php
        } else {
            echo 'Veuillez vous inscrire avec le lien fourni dans le mail envoyé';
        }
    }
}
?>

<?php
// traitement du formulaire
if (isset($_POST['submit_inscription']) && $_POST['submit_inscription'] == "Valider") {
    $pdo = connexion_bdd();
    $requete_verif_mail = $pdo->prepare('SELECT COUNT(*) FROM wp_users WHERE user_email=?');
    $email = $_POST['email'];
    $requete_verif_mail->execute([$email]);
    $count_email = $requete_verif_mail->fetchColumn();

    $requete_verif_id = $pdo->prepare('SELECT COUNT(*) FROM wp_users WHERE user_login=?');
    $identifiant = $_POST['identifiant'];
    $requete_verif_id->execute([$identifiant]);
    $count_id = $requete_verif_id->fetchColumn();

    if ($count_id != 0 && $count_email != 0) {
        message_id();
        message_email();
    } elseif ($count_id != 0) {
        message_id();
    } elseif ($count_email != 0) {
        message_email();
    } else {

// confirmer l'email 
        if ($_POST['email'] != $_POST['verif_email']) {
            message_verif_email();
        } elseif ($_POST['mdp'] != $_POST['verif_mdp']) {
            message_verif_mdp();
        }

// ajouter les infos d'utilisateur dans la BD
        else {
            // préparation pour la table wp_users (infos importantes pour la connexion)
            // verifier si le mail et l'identifiant entré est déjà enregistré dans la BDD
            // préparation des infos pour la table wp_users

            $user_pass = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
            $user_nicename = strtolower($identifiant);
            $user_url = "";
            $user_registered = date("Y-m-d H:i:s");
            $user_activation_key = bin2hex(openssl_random_pseudo_bytes(10));
            $user_status = 0;
            $display_name = $_POST['nom'] . "\n" . $_POST['prenom'];

            // préparation pour la table wp_usermeta
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $promotion = $_POST['promotion'];
            $adresse = $_POST['adresse'];
            $comp_adresse = $_POST['comp_adresse'];
            $ville = $_POST['ville'];
            $departement = $_POST['departement'];
            $code_postal = $_POST['code_postal'];
            $pays = $_POST['pays'];
            $telephone = $_POST['telephone'];

            //ajout des infos dans le tableau wp_users
            $requete_ajout_membre = $pdo->prepare('INSERT INTO wp_users VALUES("",?,?,?,?,?,?,?,?,?)');
            $requete_ajout_membre->execute([$identifiant, $user_pass, $user_nicename, $email, $user_url, $user_registered, $user_activation_key, $user_status, $display_name]);

            //ajout des infos dans le tableau wp_usermeta
            // cherche l'utilisateur id
            $requete_cherche_userID = $pdo->prepare('SELECT ID FROM wp_users WHERE user_email = ?');
            $requete_cherche_userID->execute([$email]);
            $resultat_userID = $requete_cherche_userID->fetch();
            $userID = $resultat_userID["ID"];

            $requete_ajout_nom = $pdo->prepare('INSERT INTO wp_usermeta VALUES("",?,"last_name",?)');
            $requete_ajout_nom->execute([$userID, $nom]);
            $requete_ajout_prenom = $pdo->prepare('INSERT INTO wp_usermeta VALUES("",?,"first_name",?)');
            $requete_ajout_prenom->execute([$userID, $prenom]);
            $requete_ajout_promotion = $pdo->prepare('INSERT INTO wp_usermeta VALUES("",?,"promotion",?)');
            $requete_ajout_promotion->execute([$userID, $promotion]);
            $requete_ajout_adresse = $pdo->prepare('INSERT INTO wp_usermeta VALUES("",?,"addr1",?)');
            $requete_ajout_adresse->execute([$userID, $adresse]);
            $requete_ajout_compadresse = $pdo->prepare('INSERT INTO wp_usermeta VALUES("",?,"addr2",?)');
            $requete_ajout_compadresse->execute([$userID, $comp_adresse]);
            $requete_ajout_ville = $pdo->prepare('INSERT INTO wp_usermeta VALUES("",?,"city",?)');
            $requete_ajout_ville->execute([$userID, $ville]);
            $requete_ajout_departement = $pdo->prepare('INSERT INTO wp_usermeta VALUES("",?,"thestate",?)');
            $requete_ajout_departement->execute([$userID, $departement]);
            $requete_ajout_codepostal = $pdo->prepare('INSERT INTO wp_usermeta VALUES("",?,"zip",?)');
            $requete_ajout_codepostal->execute([$userID, $code_postal]);
            $requete_ajout_pays = $pdo->prepare('INSERT INTO wp_usermeta VALUES("",?,"country",?)');
            $requete_ajout_pays->execute([$userID, $pays]);
            $requete_ajout_telephone = $pdo->prepare('INSERT INTO wp_usermeta VALUES("",?,"phone1",?)');
            $requete_ajout_telephone->execute([$userID, $telephone]);
            $requete_ajout_verif = $pdo->prepare('INSERT INTO wp_usermeta VALUES("",?,"verif","false")');
            $requete_ajout_verif->execute([$userID]);

            $subject = "Valider votre inscription Rapport de stage M2 CCI Tours";
            $txt = "Bonjour,\n" .
                    "Bienvenue à l'espace Rapport de stage Master CCI Tours. Merci de valider votre inscription avec le lien ci-dessous: " .
                    "http://localhost/wordpress/validation/?email=$email&activation_key=$user_activation_key";
            $headers = "From: Master CCI Tours";
            mail($email, $subject, $txt, $headers);

            redirect("/wordpress/inscription/?message=oui");
        }
    }
}

function message_id() {
    $message_Id = "Cet identifiant est déjà utilisé pour l\'inscription. Merci de choisir un autre identifiant";
    echo '<script>alert("' . $message_Id . '")</script>';
    echo '<style>
            .form-label-identifiant {
                color: red;  
            }</style>';
    echo '<script type="text/javascript"> erreur("erreur_identifiant","' . $message_Id . '")</script>';
//    exit();
}

function message_email() {
    $message_email = "Cette adresse email est déjà utilisée pour l\'inscription. Merci de choisir un autre adresse mail";
    echo '<script>alert("' . $message_email . '")</script>';
    echo '<style>
            .form-label-email {
                color: red;  
            }</style>';
    echo '<script type="text/javascript"> erreur("erreur_email","' . $message_email . '")</script>';
}

function message_verif_email() {
    $message_confirm_email = "Les deux emails ne correspondent pas";
    echo '<script>alert("' . $message_confirm_email . '")</script>';
    echo '<style>
            .form-label-email-verif {
                color: red;  
            }</style>';
    echo '<script type="text/javascript"> erreur("erreur_verif_email","' . $message_confirm_email . '")</script>';
}

function message_verif_mdp() {
    $message_confirm_mdp = "Les deux mots de passe ne correspondent pas";
    echo '<script>alert("' . $message_confirm_mdp . '")</script>';
    echo '<style>
            .form-label-mdp {
                color: red;  
            }</style>';
    echo '<script type="text/javascript"> erreur("erreur_verif_mdp","' . $message_confirm_mdp . '")</script>';
}
?>

<?php
get_footer();
