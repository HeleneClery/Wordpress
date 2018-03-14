<?php
/* Template Name: DepotRapportStage */
get_header();
?>

<html>
    <head>
        <title>Depot</title>
        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
        <style>
            div.info{
                display: inline-block;
                text-align: left;
            }
            input {
                width:400px;
            }
            label,input[type='file'] {
                align-content: center;
            }
            textArea {
                width: 400px;
            }
            h2 {
                text-align: center;
            }
            input[type='submit'] {
                margin-bottom: 50px;
                color: black;
            }
            .erreur {
                color: red;
                text-align: center;
                font-size: 12pt;
            }
        </style>
        <script type="text/javascript">
            function erreur(id, message) {
                document.getElementById(id).innerHTML = message;
            }
        </script>

    </head>
    <body>
        <h2>Dépôt du rapport stage</h2>

        <div id ="erreur_synthese" class="erreur"></div>
        <div id ="erreur_rapport" class="erreur"></div><br>

        <div align = "center">
            <form method="POST" enctype="multipart/form-data">
                <div align = "center" class="info">
                    <label class = "depot" for = "depot">Nom <span class = "required" title = "Ce champ est requis.">*</span></label>
                    <div class = "depot-input"><input id = "nom" class = "depot-texte required" maxlength = "128" name = "nom" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["nom"]) ? $_POST["nom"] : ""; ?>" /></div><br>
                    <label class = "depot" for = "depot">Prénom <span class = "required" title = "Ce champ est requis.">*</span></label>
                    <div class = "depot-input"><input id = "prenom" class = "depot-texte required" maxlength = "128" name = "prenom" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["prenom"]) ? $_POST["prenom"] : ""; ?>" /></div><br>
                    <label class = "depot" for = "depot">Promotion <span class = "required" title = "Ce champ est requis.">*</span></label>
                    <div class = "depot-input"><input id = "promotion" class = "depot-texte required" maxlength = "128" name = "promotion" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["promotion"]) ? $_POST["promotion"] : ""; ?>" /></div><br>
                    <label class = "depot" for = "depot">Entreprise <span class = "required" title = "Ce champ est requis.">*</span></label>
                    <div class = "depot-input"><input id = "entreprise" class = "depot-texte required" maxlength = "128" name = "entreprise" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["entreprise"]) ? $_POST["entreprise"] : ""; ?>" /></div><br>
                    <label class = "depot" for = "depot">Ville <span class = "required" title = "Ce champ est requis.">*</span></label>
                    <div class = "depot-input"><input id = "ville" class = "depot-texte required" maxlength = "128" name = "ville" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["ville"]) ? $_POST["ville"] : ""; ?>" /></div><br>
                    <label class = "depot" for = "depot">Secteur <span class = "required" title = "Ce champ est requis.">*</span></label>
                    <div class = "depot-input"><input id = "secteur" class = "depot-texte required" maxlength = "128" name = "secteur" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["secteur"]) ? $_POST["secteur"] : ""; ?>" /></div><br>
                    <label class = "depot" for = "depot">Sujet <span class = "required" title = "Ce champ est requis.">*</span></label>
                    <div class = "depot-input"><input id = "sujet" class = "depot-texte required" maxlength = "200" name = "sujet" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php echo isset($_POST["sujet"]) ? $_POST["sujet"] : ""; ?>" /></div><br>
                    <label class = "depot" for = "depot">Technologies <span class = "required" title = "Ce champ est requis.">*</span></label>
                    <div class = "depot-input"><input id = "techno1" placeholder="Technologie 1 (obligatoire)" class = "depot-texte required" name = "techno[]" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." multiple="multiple"/></div><br>
                    <div class = "depot-input"><input id = "techno2" placeholder="Technologie 2" class = "depot-texte" name = "techno[]" size = "60" type = "text" multiple="multiple"/></div><br>
                    <div class = "depot-input"><input id = "techno3" placeholder="Technologie 3" class = "depot-texte" name = "techno[]" size = "60" type = "text" multiple="multiple"/></div><br>
                    <div class = "depot-input"><input id = "techno4" placeholder="Technologie 4" class = "depot-texte" name = "techno[]" size = "60" type = "text" multiple="multiple"/></div><br>
                    <label class = "depot" for = "depot">Embauché</label>
                    <div class = "depot-input"><input id = "embauche" class = "depot-texte" maxlength = "128" placeholder="Oui/Non" name = "embauche" size = "60" type = "text" value = "<?php echo isset($_POST["embauche"]) ? $_POST["embauche"] : ""; ?>" /></div><br>
                    <label class = "depot" for = "depot">Résumé du stage <span class = "required" title = "Ce champ est requis.">*</span></label>
                    <div class ="depot-input"><textarea id="resume_stage" name="resume_stage" cols="50" rows="7" style="text-align:left; overflow:auto; border:1px outset #000000;"><?php echo isset($_POST["resume_stage"]) ? $_POST["resume_stage"] : ""; ?></textarea></div><br>
                </div>
                <div class="upload">
                    <label class="upload-synthese" for="synthese">Fiche de synthèse (PDF ou DOCX | max. 20 Mo) :</label>
                    <br><input name="synthese" type="file" />
                    <?php wp_nonce_field('synthese', 'synthese_nonce'); ?>
                    <br><br><label class="upload-rapport" for="rapport">Rapport de stage (PDF ou DOCX | max. 20 Mo) :</label>
                    <br><input name="rapport" type="file" /><br>
                    <?php wp_nonce_field('rapport', 'rapport_nonce'); ?>
                    <br>
                    <input type="submit" name="submit_envoyer" value="Envoyer"/>
                </div>
            </form> 
        </div>
    </body>
</html>

<?php
if (isset($_POST['submit_envoyer']) && $_POST['submit_envoyer'] == "Envoyer") {
    $pdo = connexion_bdd();
    $taille_max = 20000000;
    $ext_val = array('docx', 'pdf');
    $nom_synthese = $_FILES['synthese']['name'];
    $nom_rapport = $_FILES['rapport']['name'];
    $ext_synthese = pathinfo($nom_synthese, PATHINFO_EXTENSION);
    $ext_rapport = pathinfo($nom_rapport, PATHINFO_EXTENSION);

    if (!in_array($ext_synthese, $ext_val) && !in_array($ext_rapport, $ext_val)) {
        message_ext_synthese();
        message_ext_rapport();
    } elseif (!in_array($ext_synthese, $ext_val)) {
        message_ext_synthese();
    } elseif (!in_array($ext_rapport, $ext_val)) {
        message_ext_rapport();
    } else {
        if ($_FILES['synthese']['size'] > $taille_max && $_FILES['rapport']['size'] > $taille_max) {
            message_taille_synthese();
            message_taille_rapport();
        } elseif ($_FILES['synthese']['size'] > $taille_max) {
            message_taille_synthese();
        } elseif ($_FILES['rapport']['size'] > $taille_max) {
            message_taille_rapport();
        } else {
            $id_utilisateur = get_current_user_id();
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $promotion = $_POST['promotion'];
            $entreprise = $_POST['entreprise'];
            $ville = $_POST['ville'];
            $secteur = $_POST['secteur'];
            $sujet = $_POST['sujet'];
            $embauche = $_POST['embauche'];
            $rapport_stage = envoi_rapport("rapport");
            $note_synthese = envoi_rapport("synthese");
            $resume_stage = $_POST['resume_stage'];

            $requete_ajout_stage = $pdo->prepare('INSERT INTO stages VALUES("",?,?,?,?,?,?,?,?,?,?,?,?)');
            $requete_ajout_stage->execute([$id_utilisateur, $nom, $prenom, $promotion, $entreprise, $ville, $secteur, $sujet, $embauche, $rapport_stage, $note_synthese, $resume_stage]);

            global $wpdb;
            $id_stage = $wpdb->query($wpdb->prepare("SELECT id_stage FROM stages WHERE id_utilisateur = %s", $id_utilisateur));
            $techno = $_POST['techno'];
            foreach ($techno as $cle => $t) {
                $requete_ajout_techno = $pdo->prepare('INSERT INTO technologies(id_stage,technologie) VALUES(?,?)');
                $requete_ajout_techno->execute([$id_stage, $t[$cle]]);
            }
        }
    }
}

// méthode pour la connexion à la base de données
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

function message_ext_synthese() {
    $message_ext_synthese = "Le fichier synthèse doit être en format PDF ou DOCX";
    echo '<script>alert("' . $message_ext_synthese . '")</script>';
    echo '<style>
            .upload-synthese {
                color: red;  
            }</style>';
    echo '<script type="text/javascript"> erreur("erreur_synthese","' . $message_ext_synthese . '")</script>';
}

function message_ext_rapport() {
    $message_ext_rapport = "Le fichier rapport doit être en format PDF ou DOCX";
    echo '<script>alert("' . $message_ext_rapport . '")</script>';
    echo '<style>
            .upload-rapport {
                color: red;  
            }</style>';
    echo '<script type="text/javascript"> erreur("erreur_rapport","' . $message_ext_rapport . '")</script>';
}

function message_taille_synthese() {
    global $taille_max;
    $message_taille_synthese = "La taille du fichier synthèse doit être < " . ($taille_max / 1000000) . " Mo";
    echo '<script>alert("' . $message_taille_synthese . '")</script>';
    echo '<style>
            .upload-synthese {
                color: red;  
            }</style>';
    echo '<script type="text/javascript"> erreur("erreur_synthese","' . $message_taille_synthese . '")</script>';
}

function message_taille_rapport() {
    global $taille_max;
    $message_taille_rapport = "La taille du fichier rapport doit être < " . ($taille_max / 1000000) . " Mo";
    echo '<script>alert("' . $message_taille_rapport . '")</script>';
    echo '<style>
            .upload-rapport {
                color: red;  
            }</style>';
    echo '<script type="text/javascript"> erreur("erreur_rapport","' . $message_taille_rapport . '")</script>';
}

function envoi_rapport($fichier) {
    if (isset($_POST["$fichier" . "_nonce"])) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        $fichier_id = media_handle_upload("$fichier", 0);
        $fichier_url = wp_get_attachment_url($fichier_id);

        if ($fichier == "synthese") {
            $nom_fichier = "fichier de synthèse";
        } elseif ($fichier == "rapport") {
            $nom_fichier = "rapport de stage";
        }

        if (is_wp_error($fichier_id)) {
            echo "<HTML>";
            echo "<div class='" . $fichier . "'>L'envoi du " . $nom_fichier . " est échoué</div><br/>";
            echo "<style> div." . $fichier . "{font-size:13pt; color:#50D050;}</style></HTML>";
        } else {
            echo "<HTML>";
            echo "<div class='" . $fichier . "'>Le " . $nom_fichier . " a été envoyé avec succès</div><br/>";
            echo "<style> div." . $fichier . "{font-size:13pt; color:#50D050;}</style></HTML>";
            return $fichier_url;
        }
    } else {
        echo "<HTML>";
        echo "<div class='a'>Problème de l'envoi du fichier</div><br/>";
        echo "<style> div.a{font-size:13pt; color:#50D050;}</style></HTML>";
    }
}
?>

<?php
get_footer();
