<?php
/* Template Name: EnvoiMailRapportStage */
get_header();
?>

<?php

// méthode pour la connexion à la base de données
function connexion_bdd() {
    $host = "localhost";
    $user = "root";
    $passwd = "";
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

// récupérer les listes des groupes
$pdo = connexion_bdd();
$requeteListe = 'SELECT es_email_group FROM wp_m2ccitours_es_emaillist group by es_email_group';
$resultatListe = $pdo->query($requeteListe);
?>

<html>
    <style>
        div.a {
            font-size: 12pt;
            width: 550px;
            text-align: justify;
            margin-top: 20px;
        }
        div.b {
            width: 550px;
            display: inline-block;
            float: left;
        }
        form {
            margin-bottom: 20px;
        }
        select {
            height: 38px;
            line-height: 38px;
            width: 220px;
            font-size: 13pt;
            font-weight: bold;
        }
        label {
            width: 200px;
            padding-right: 10px;
            font-size: 13pt;
        }
        button {
            float: right;
        }
        input {
            width: 220px;
            font-size: 13pt;
        }
    </style>
    <div class="a">
        Pour envoyer le mail d'inscription aux étudiants:
        <ul>
            <li>Chercher dans l'espace admin le plugin Email Subscribers & Newsletters</li>
            <li>Ajouter un étudiant ou importer une liste d'étudiants avec un fichier *.csv</li>
            <strong><u>Attention:</u></strong> ce fichier *.csv doit être en format: Email,Name (séparé par virgule).<br>
            Le statut des étudiants doit être "Confirmé".<br>
            Choisir le nom du groupe (par exemple: CCI 2017).<br>
            Vérifier la liste ajouté.
            <li>On peut envoyer le mail à une liste ou à une adresse individuelle (qui est déjà ajoutée dans la liste) </li>
        </ul>
        <br>
    </div>
    <div class="b">
        <form id = "form_liste" method="POST">
            <label for="liste"> Choisir une liste </label>
            <select name="selectGroupe" id="selectGroupe">
                <?php
                while ($l = $resultatListe->fetch()) {
                    echo '<option name="' . $l['es_email_group'] . '" value="' . $l['es_email_group'] . '">"' . $l['es_email_group'] . '"</option>"';
                }
                ?>
            </select>
            <button type="submit" id="edit-submit" name="submit_liste" value="envoyer" >Envoyer</button>
        </form>
        <form id="form_indi" method="POST">
            <label for="indi"> Email individuel</label>
            <input type="email" maxlength="128" id='email' name="email" data-validate-email-message = "S\'il vous plaît vérifier que votre adresse e-mail est selon le format suivant: name@gmail.com. Elle ne peut pas contenir de caractères spéciaux." type = "email" data-validate-type-message = "L\'email saisi est incorrect." value = "<?php echo isset($_POST["email"]) ? $_POST["email"] : ""; ?>">
            <button type="submit" id="edit-submit" name="submit_indi" value="envoyer" >Envoyer</button>
        </form>
    </div><br><br><br><br><br>
</html>

<?php

// Vérifier si envoyer à une liste
if (isset($_POST['submit_liste'])) {
    $selectGroupe = $_POST["selectGroupe"];
    $requeteMail = $pdo->prepare("SELECT es_email_mail FROM wp_m2ccitours_es_emaillist WHERE es_email_group =?");
    $requeteMail->execute([$selectGroupe]);
    // Envoyer le mail à la liste choisie 
    while ($l = $requeteMail->fetch()) {
        $to = $l['es_email_mail'];
        envoyer_mail($to);
    }
}

// Vérifier si envoyer à une adresse mail individuelle
if (isset($_POST['submit_indi'])) {
    $to = $_POST['email'];
    $requete_verif_email = $pdo->prepare("SELECT COUNT(*) FROM wp_m2ccitours_es_emaillist WHERE es_email_mail = ?");
    $requete_verif_email->execute([$to]);
    $resultat_verif_email = $requete_verif_email->fetchColumn();
    // Vérifier si l'adresse mail est déjà ajoutée dans la liste
    if ($resultat_verif_email == 0) {
        echo "<HTML>";
        echo "<div class='c'>***Cette adresse mail $to n'est pas encore enregistrée dans la liste</div>";
        echo "<style> div.c{font-size:13pt; color:red;}</style></HTML>";
    } else {
        envoyer_mail($to);
    }
}

// Méthode pour l'envoi des mails
function envoyer_mail($email) {
    global $pdo;
    $sujet = "Invitation à l'espace gestion Rapport stage M2 CCI Tours";
    $requeteGuid = $pdo->prepare("SELECT es_email_guid FROM wp_m2ccitours_es_emaillist WHERE es_email_mail = ?");
    $requeteGuid->execute([$email]);
    $guid = $requeteGuid->fetchColumn();
    $texte = "Bonjour,\n" .
            "Bienvenue au master CCI Tours!\n" .
            "Merci de vous inscrire avec le lien ci-dessous: \n" .
            "http://localhost/wordpress/inscription/?email=$email&guid=$guid \n" .
            "A bientôt!";
    $headers = "De: Gestion Rapport de stage" . "\r\n";

    // Vérifier si les mails sont bien envoyés
    if (wp_mail($email, $sujet, $texte, $headers)) {
        echo "<HTML>";
        echo "<div class='d'>Envoyé à $email </div><br/>";
        echo "<style> div.d{font-size:13pt; color:#50D050;}</style></HTML>";
    } else {
        echo "<HTML>";
        echo "<div class='c'>Problème d'envoi email à $email </div><br>";
        echo "<style> div.c{font-size:13pt; color:red;}</style></HTML>";
    }
}
?>

<?php
get_footer();
