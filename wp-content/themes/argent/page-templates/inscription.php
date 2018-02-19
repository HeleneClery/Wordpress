<?php
/* Template Name: InscriptionRapportStage */
get_header();
?>

<html>
    <head>
        <title>Inscription</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        </style>

    </head>
    <body>
        <h2>
            INSCRIPTION
        </h2>
        <div align="center">
            <form id="inscription" method="post">
                <label class="form-label" for="inscription">Identifiant <span class="required" title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" maxlength="128" name="identifiant" required="" size="60" type="text" aria-required="true" data-validate-required-message="Ce champ est requis." value="<?php echo isset($_POST["identifiant"]) ? $_POST["identifiant"] : ''; ?>" /></div>
                <label class="form-label" for="inscription">Nom <span class="required" title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" maxlength="128" name="nom" required="" size="60" type="text" aria-required="true" data-validate-required-message="Ce champ est requis." value="<?php echo isset($_POST["nom"]) ? $_POST["nom"] : ''; ?>" /></div>
                <label class="form-label" for="inscription">Prenom <span class="required" title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" maxlength="128" name="prenom" required="" size="60" type="text" aria-required="true" data-validate-required-message="Ce champ est requis." value="<?php echo isset($_POST["prenom"]) ? $_POST["prenom"] : ''; ?>"/></div>
                <label class="form-label" for="inscription">Adresse <span class="required" title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" maxlength="128" name="adresse" required="" size="60" type="text" aria-required="true" data-validate-required-message="Ce champ est requis." value="<?php echo isset($_POST["adresse"]) ? $_POST["adresse"] : ''; ?>"/></div>
                <label class="form-label" for="inscription"> Complément adresse <span class="k4"></span></label>
                <div class="form-input"><input id="inscription" class="form-text" maxlength="128" name="comp_adresse" size="60" type="text" value="<?php echo isset($_POST["comp_adresse"]) ? $_POST["comp_adresse"] : ''; ?>"/></div>
                <label class="form-label" for="inscription">Ville <span class="required" title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" maxlength="128" name="ville" required="" size="60" type="text" aria-required="true" data-validate-required-message="Ce champ est requis." value="<?php echo isset($_POST["ville"]) ? $_POST["ville"] : ''; ?>"/></div>
                <label class="form-label" for="inscription">Département <span class="k4"></span></label>
                <div class="form-input"><input id="inscription" class="form-text" maxlength="128" name="departement" size="60" type="text" value="<?php echo isset($_POST["departement"]) ? $_POST["departement"] : ''; ?>"/></div>
                <label class="form-label" for="inscription">Code postal <span class="required" title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" maxlength="128" name="code_postal" required="" size="60" type="text" aria-required="true" data-validate-required-message="Ce champ est requis." value="<?php echo isset($_POST["code_postal"]) ? $_POST["code_postal"] : ''; ?>"/></div>
                <label class="form-label" for="inscription">Pays <span class="required" title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" maxlength="128" name="pays" required="" size="60" type="text" aria-required="true" data-validate-required-message="Ce champ est requis." value="<?php echo isset($_POST["pays"]) ? $_POST["pays"] : ''; ?>"/></div>
                <label class="form-label" for="inscription">Téléphone <span class="required" title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" maxlength="128" name="telephone" required="" size="60" type="text" aria-required="true" data-validate-required-message="Ce champ est requis." value="<?php echo isset($_POST["telephone"]) ? $_POST["telephone"] : ''; ?>"/></div>
                <label class="form-label" for="inscription">Email <span title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" required aria-required="true"  data-validate-required-message="Ce champ est requis." data-validate-email-message="S'il vous plaît vérifier que votre adresse e-mail est selon le format suivant: name@gmail.com. Elle ne peut pas contenir de caractères spéciaux." type="email" data-validate-type-message="L'email saisi est incorrect." id="email" name="email" size="60" maxlength="128" value="<?php echo isset($_POST["email"]) ? $_POST["email"] : ''; ?>"/></div>
                <label class="form-label-email" for="inscription">Confirmer Email <span title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" required aria-required="true"  data-validate-required-message="Ce champ est requis." data-validate-email-message="S'il vous plaît vérifier que votre adresse e-mail est selon le format suivant: name@gmail.com. Elle ne peut pas contenir de caractères spéciaux." type="email" data-validate-type-message="L'email saisi est incorrect." id="verif_email" name="verif_email" size="60" maxlength="128" value="<?php echo isset($_POST["verif_email"]) ? $_POST["verif_email"] : ''; ?>"/></div>
                <label class="form-label-mdp" for="inscription">Mot de passe <span class="required" title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" maxlength="128" name="mdp" required="" size="60" type="password" aria-required="true" data-validate-required-message="Ce champ est requis." /></div>
                <label class="form-label-mdp" for="inscription">Confirmer Mot de passe <span class="required" title="Ce champ est requis.">*</span></label>
                <div class="form-input"><input id="inscription" class="form-text required" maxlength="128" name="verif_mdp" required="" size="60" type="password" aria-required="true" data-validate-required-message="Ce champ est requis." /></div><br/>
                <input type="submit" name= "submit_inscription" value = "Valider"/>
            </form>
        </div>
    </body>
</html> 

<?php

$verif_email = $_POST['verif_email'];
if (isset($_POST['submit_inscription']) && $_POST['submit_inscription'] == "Valider") {
    if ($_POST['email'] != $_POST['verif_email']) {
//        $erreur = "Les deux emails sont différents";
        $verif_email = "";
        echo "<script>alert('Les deux emails ne correspondent pas')</script>";
        echo '<style>
            .form-label-email {
                color: red;  
            }';
    }
    if ($_POST['mdp'] != $_POST['verif_mdp']) {
        echo "<script>alert('Les deux mots de passe ne correspondent pas')</script>";
        echo '<style>
            .form-label-mdp {
                color: red;  
            }';
    } else {
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
        $requete_verif_dispo = $pdo->prepare('SELECT COUNT(*) FROM wp_users WHERE user_email=?');
        $email = $_POST['email'];
        $requete_verif_dispo->execute([$email]);
        $id = $requete_verif_dispo->fetch();
        if ($id[0] == 0) {
            $requete_ajout_membre = $pdo->prepare('INSERT INTO wp_users VALUES("",bin2hex(openssl_random_pseudo_bytes(10)), ')
        }
    }
}
if (isset($erreur)) {
    echo '<br />', $erreur;
}




//while ($row = $requete->fetch()) {
//    echo $row['es_email_name']."<br/>";
//}
//
//// Récupération des variables nécessaires à l'activation
//$email = $_GET['email'];
//$guid = $_GET['guid'];
//
//$stmt = "SELECT es_email_guid FROM wp_es_emaillist WHERE es_email_mail='$email'";
//$result = mysqli_query($connexion, $stmt);
//$row = mysqli_fetch_row($result);
//$guidBdd = $row[0];
//
//if ($email == null) {
//    echo 'Cette espace est réservée aux étudiants CCI de la promotion actuelle. Si vous êtes un ancien étudiant et vous êtes intéressé par cette page, veuillez contacter xxx@yyy.com';
//} elseif ($guid == $guidBdd) {
//    echo '
//[wpmem_form]
//Bienvenue au Master CCI Tours
//[/wpmem_form]
//';
//} else {
//    echo 'Veuillez vous inscrire avec le lien fourni dans le mail envoyé';
//}
//echo $_POST['username'];
//$username = $_POST['username'];
//if (username_exists($username))
//    echo "Username In Use!";
//else
//    echo "Username Not In Use!";
?>

<?php
get_footer();
