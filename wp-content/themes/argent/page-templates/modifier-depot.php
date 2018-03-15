<?php
/* Template Name: ModifierDepotRapportStage */
get_header();
?>


<!--Formulaire pour le dépôt-->
<html>
    <head>
        <title>Dépôt rapport de stage</title>
        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
        <style>
            span.message {
                padding: 10px;
                border: 3px solid black;
                display: inline-block;
                margin-bottom: 70px;
                margin-top: 70px;
            }
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

            // JQuery pour forcer les infos comme Promotion doivent être numériques 
            (function (b) {
                var c = {allowFloat: false, allowNegative: false};
                b.fn.numericInput = function (e) {
                    var f = b.extend({}, c, e);
                    var d = f.allowFloat;
                    var g = f.allowNegative;
                    this.keypress(function (j) {
                        var i = j.which;
                        var h = b(this).val();
                        if (i > 0 && (i < 48 || i > 57)) {
                            if (d == true && i == 46) {
                                if (g == true && a(this) == 0 && h.charAt(0) == "-") {
                                    return false
                                }
                                if (h.match(/[.]/)) {
                                    return false
                                }
                            } else {
                                if (g == true && i == 45) {
                                    if (h.charAt(0) == "-") {
                                        return false
                                    }
                                    if (a(this) != 0) {
                                        return false
                                    }
                                } else {
                                    if (i == 8) {
                                        return true
                                    } else {
                                        return false
                                    }
                                }
                            }
                        } else {
                            if (i > 0 && (i >= 48 && i <= 57)) {
                                if (g == true && h.charAt(0) == "-" && a(this) == 0) {
                                    return false
                                }
                            }
                        }
                    });
                    return this
                };
                function a(d) {
                    if (d.selectionStart) {
                        return d.selectionStart
                    } else {
                        if (document.selection) {
                            d.focus();
                            var f = document.selection.createRange();
                            if (f == null) {
                                return 0
                            }
                            var e = d.createTextRange(), g = e.duplicate();
                            e.moveToBookmark(f.getBookmark());
                            g.setEndPoint("EndToStart", e);
                            return g.text.length
                        }
                    }
                    return 0
                }
            }
            (jQuery));

            jQuery(function () {
                jQuery("#promotion").numericInput({allowFloat: true, allowNegative: true});
            });
        </script>
    </head>
    <body>

        <?php
// Connexion à la base de données
        $pdo = connexion_bdd();
        $message = $_GET['message'];

// Vérifier si l'utilisateur a déjà déposé un rapport
        $requete_verif_statut_depot = $pdo->prepare('SELECT COUNT(*) FROM stages WHERE id_utilisateur = ?');
        $requete_verif_statut_depot->execute([get_current_user_id()]);
        $count_depot = $requete_verif_statut_depot->fetchColumn();
        if ($count_depot == 0) {
            echo '<div align="center"><span class = "message">Vous devez déposer le rapport de stage en allant sur l\'onglet "Dépôt de rapports"</span></div>';

// Pour la rédirection après avoir modifié le dépôt
        } elseif ($message == "oui") {
            echo '<div align="center"><span class = "message">Le dépôt de rapports est bien modifié </span></div>';
        } else {

            // Préparation des infos dans la base de données pour pré-remplir dans le formulaire
            $id_utilisateur = get_current_user_id();
            $requete_get_id_stage = $pdo->prepare('SELECT id_stage FROM stages WHERE id_utilisateur = ?');
            $requete_get_id_stage->execute([$id_utilisateur]);
            $id_stage = $requete_get_id_stage->fetchColumn();

            global $wpdb;
            $nom_db = $wpdb->get_var($wpdb->prepare('SELECT nom FROM stages WHERE id_utilisateur = %s', $id_utilisateur));
            $prenom_db = $wpdb->get_var($wpdb->prepare('SELECT prenom FROM stages WHERE id_utilisateur = %s', $id_utilisateur));
            $promotion_db = $wpdb->get_var($wpdb->prepare('SELECT promotion FROM stages WHERE id_utilisateur = %s', $id_utilisateur));
            $entreprise_db = $wpdb->get_var($wpdb->prepare('SELECT entreprise FROM stages WHERE id_utilisateur = %s', $id_utilisateur));
            $ville_db = $wpdb->get_var($wpdb->prepare('SELECT ville FROM stages WHERE id_utilisateur = %s', $id_utilisateur));
            $secteur_db = $wpdb->get_var($wpdb->prepare('SELECT secteur FROM stages WHERE id_utilisateur = %s', $id_utilisateur));
            $sujet_db = $wpdb->get_var($wpdb->prepare('SELECT sujet FROM stages WHERE id_utilisateur = %s', $id_utilisateur));
            $admission_db = $wpdb->get_var($wpdb->prepare('SELECT admission FROM stages WHERE id_utilisateur = %s', $id_utilisateur));
            $rapport_stage_db = $wpdb->get_var($wpdb->prepare('SELECT rapport_stage FROM stages WHERE id_utilisateur = %s', $id_utilisateur));
            $note_synthese_db = $wpdb->get_var($wpdb->prepare('SELECT note_synthese FROM stages WHERE id_utilisateur = %s', $id_utilisateur));
            $resume_stage_db = $wpdb->get_var($wpdb->prepare('SELECT resume_stage FROM stages WHERE id_utilisateur = %s', $id_utilisateur));

            $resultat_techno_db = $wpdb->get_results($wpdb->prepare('SELECT technologie FROM technologies WHERE id_stage = %s', $id_stage), ARRAY_A);
            $techno_db = array();
            $i = 0;
            foreach ($resultat_techno_db as $techno) {
                $techno_db[$i] = $techno['technologie'];
                $i++;
            }
            ?>

            <h2>Modifier le dépôt du rapport de stage</h2>

            <!--Champs pour l'affichage des erreurs-->
            <div id ="erreur_synthese" class="erreur"></div>
            <div id ="erreur_rapport" class="erreur"></div><br>

            <div align = "center">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="mgjp_mv_protected" value="on">
                    <div align = "center" class="info">
                        <label class = "depot" for = "depot">Nom <span class = "required" title = "Ce champ est requis.">*</span></label>
                        <div class = "depot-input"><input id = "nom" class = "depot-texte required" maxlength = "128" name = "nom" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $nom_db;
                            } else {
                                echo isset($_POST["nom"]) ? $_POST["nom"] : "";
                            }
                            ?>" /></div><br>
                        <label class = "depot" for = "depot">Prénom <span class = "required" title = "Ce champ est requis.">*</span></label>
                        <div class = "depot-input"><input id = "prenom" class = "depot-texte required" maxlength = "128" name = "prenom" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $prenom_db;
                            } else {
                                echo isset($_POST["prenom"]) ? $_POST["prenom"] : "";
                            }
                            ?>" /></div><br>
                        <label class = "depot" for = "depot">Promotion <span class = "required" title = "Ce champ est requis.">*</span></label>
                        <div class = "depot-input"><input id = "promotion" placeholder="Année d'obtention du diplôme (par exemple: 2018)" class = "depot-texte required" maxlength = "4" name = "promotion" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $promotion_db;
                            } else {
                                echo isset($_POST["promotion"]) ? $_POST["promotion"] : "";
                            }
                            ?>" /></div><br>
                        <label class = "depot" for = "depot">Entreprise <span class = "required" title = "Ce champ est requis.">*</span></label>
                        <div class = "depot-input"><input id = "entreprise" class = "depot-texte required" maxlength = "128" name = "entreprise" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $entreprise_db;
                            } else {
                                echo isset($_POST["entreprise"]) ? $_POST["entreprise"] : "";
                            }
                            ?>" /></div><br>
                        <label class = "depot" for = "depot">Ville <span class = "required" title = "Ce champ est requis.">*</span></label>
                        <div class = "depot-input"><input id = "ville" class = "depot-texte required" maxlength = "128" name = "ville" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $ville_db;
                            } else {
                                echo isset($_POST["ville"]) ? $_POST["ville"] : "";
                            }
                            ?>" /></div><br>
                        <label class = "depot" for = "depot">Secteur <span class = "required" title = "Ce champ est requis.">*</span></label>
                        <div class = "depot-input"><input id = "secteur" class = "depot-texte required" maxlength = "128" name = "secteur" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $secteur_db;
                            } else {
                                echo isset($_POST["secteur"]) ? $_POST["secteur"] : "";
                            }
                            ?>" /></div><br>
                        <label class = "depot" for = "depot">Sujet <span class = "required" title = "Ce champ est requis.">*</span></label>
                        <div class = "depot-input"><input id = "sujet" class = "depot-texte required" maxlength = "200" name = "sujet" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $sujet_db;
                            } else {
                                echo isset($_POST["sujet"]) ? $_POST["sujet"] : "";
                            }
                            ?>" /></div><br>
                        <label class = "depot" for = "depot">Technologies <span class = "required" title = "Ce champ est requis.">*</span></label>
                        <div class = "depot-input"><input id = "techno1" placeholder="Technologie 1 (obligatoire)" class = "depot-texte required" name = "techno[]" required = "" size = "60" type = "text" aria-required = "true" data-validate-required-message = "Ce champ est requis." multiple="multiple" value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $techno_db[0];
                            } else {
                                echo isset($_POST["techno"][0]) ? $_POST["techno"][0] : "";
                            }
                            ?>"  /></div><br>
                        <div class = "depot-input"><input id = "techno2" placeholder="Technologie 2" class = "depot-texte" name = "techno[]" size = "60" type = "text" multiple="multiple" value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $techno_db[1];
                            } else {
                                echo isset($_POST["techno"][1]) ? $_POST["techno"][1] : "";
                            }
                            ?>"/></div><br>
                        <div class = "depot-input"><input id = "techno3" placeholder="Technologie 3" class = "depot-texte" name = "techno[]" size = "60" type = "text" multiple="multiple" value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $techno_db[2];
                            } else {
                                echo isset($_POST["techno"][2]) ? $_POST["techno"][2] : "";
                            }
                            ?>"/></div><br>
                        <div class = "depot-input"><input id = "techno4" placeholder="Technologie 4" class = "depot-texte" name = "techno[]" size = "60" type = "text" multiple="multiple" value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $techno_db[3];
                            } else {
                                echo isset($_POST["techno"][3]) ? $_POST["techno"][3] : "";
                            }
                            ?>"/></div><br>
                        <label class = "depot" for = "depot">Embauché après le stage</label>
                        <div class = "depot-input"><input id = "admission" class = "depot-texte" maxlength = "128" placeholder="Oui/Non" name = "admission" size = "60" type = "text" value = "<?php
                            if (!isset($_POST['submit_envoyer'])) {
                                echo $admission_db;
                            } else {
                                echo isset($_POST["admission"]) ? $_POST["admission"] : "";
                            }
                            ?>" /></div><br>
                        <label class = "depot" for = "depot">Résumé du stage <span class = "required" title = "Ce champ est requis.">*</span></label>
                        <div class ="depot-input"><textarea id="resume_stage" name="resume_stage" cols="50" rows="7" style="text-align:left; overflow:auto; border:1px outset #000000;"><?php
                                if (!isset($_POST['submit_envoyer'])) {
                                    echo $resume_stage_db;
                                } else {
                                    echo isset($_POST["resume_stage"]) ? $_POST["resume_stage"] : "";
                                }
                                ?></textarea></div><br>
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
        <?php } ?>
    </body>
</html>

<?php
if (isset($_POST['submit_envoyer']) && $_POST['submit_envoyer'] == "Envoyer") {
// Supprimer les fichiers du précédent dépôt (il faut substr() avec paramètre 11 car le lien contient /wordpress/
    unlink(substr(parse_url($note_synthese_db, PHP_URL_PATH), 11));
    unlink(substr(parse_url($rapport_stage_db, PHP_URL_PATH), 11));

// Limite de la taille des fichiers à envoyer
    $taille_max = 20000000;    // en Octets
// Les fichiers doivent être en format PDF ou DOCX
    $ext_val = array('docx', 'pdf');

// Récupérer l'extension des fichiers envoyés
    $nom_synthese = $_FILES['synthese']['name'];
    $nom_rapport = $_FILES['rapport']['name'];
    $ext_synthese = pathinfo($nom_synthese, PATHINFO_EXTENSION);
    $ext_rapport = pathinfo($nom_rapport, PATHINFO_EXTENSION);

// Vérifier l'extension des fichiers envoyés, afficher les messages d'erreur éventuelle
    if (!in_array($ext_synthese, $ext_val) && !in_array($ext_rapport, $ext_val)) {
        message_ext_fichier('synthese');
        message_ext_fichier('rapport');
    } elseif (!in_array($ext_synthese, $ext_val)) {
        message_ext_fichier('synthese');
    } elseif (!in_array($ext_rapport, $ext_val)) {
        message_ext_fichier('rapport');
    } else {

// Vérifier la taille des fichiers envoyés et afficher le message d'erreur éventuelle
        if ($_FILES['synthese']['size'] > $taille_max && $_FILES['rapport']['size'] > $taille_max) {
            message_taille_fichier('synthese');
            message_taille_fichier('rapport');
        } elseif ($_FILES['synthese']['size'] > $taille_max) {
            message_taille_fichier('synthese');
        } elseif ($_FILES['rapport']['size'] > $taille_max) {
            message_taille_fichier('rapport');
        } else {

            // Préparation des valeurs à modifier dans la table "stages"
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $promotion = $_POST['promotion'];
            $entreprise = $_POST['entreprise'];
            $ville = $_POST['ville'];
            $secteur = $_POST['secteur'];
            $sujet = $_POST['sujet'];
            $admission = $_POST['admission'];
            $rapport_stage = envoi_rapport("rapport");
            $note_synthese = envoi_rapport("synthese");
            $resume_stage = $_POST['resume_stage'];

            // Modifier les infos dans la table "stages"
            $requete_ajout_stage = $pdo->prepare('UPDATE stages SET nom = ?, prenom = ?, promotion = ?, entreprise = ?, ville = ?, secteur = ?, sujet = ?, admission = ?, rapport_stage = ?, note_synthese = ?, resume_stage = ? WHERE id_stage = ?');
            $requete_ajout_stage->execute([$nom, $prenom, $promotion, $entreprise, $ville, $secteur, $sujet, $admission, $rapport_stage, $note_synthese, $resume_stage, $id_stage]);

            // Modifier les infos dans la table "technologies"
            $techno = $_POST['techno'];
            $j = 0;
            foreach ($techno as $cle => $t) {
                $requete_ajout_techno = $pdo->prepare('UPDATE technologies SET technologie = ? WHERE id_stage = ? AND technologie = ?');
                $requete_ajout_techno->execute([$t, $id_stage, $techno_db[$j]]);
                $j++;
            }

            // Rédiger vers le massage de confirmation
            redirect("/wordpress/" . get_query_var('pagename') . "/?message=oui");
        }
    }
}

// Methode pour la connexion à la base de données
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

// Méthode pour l'affichage du message d'erreur concernant l'extension des fichiers envoyés
function message_ext_fichier($fichier) {
    $message_ext_fichier = "Le fichier " . $fichier . " doit être en format PDF ou DOCX";
    echo '<script>alert("' . $message_ext_fichier . '")</script>';
    echo '<style>
            .upload-' . $fichier . ' {
                color: red;  
            }</style>';
    echo '<script type="text/javascript"> erreur("erreur_' . $fichier . '","' . $message_ext_fichier . '")</script>';
}

// Méthode pour l'affichage du message d'erreur concernant la taille des fichiers envoyés
function message_taille_fichier($fichier) {
    global $taille_max;
    $message_taille_fichier = "La taille du fichier " . $fichier . " doit être < " . ($taille_max / 1000000) . " Mo";
    echo '<script>alert("' . $message_taille_fichier . '")</script>';
    echo '<style>
            .upload-' . $fichier . ' {
                color: red;  
            }</style>';
    echo '<script type="text/javascript"> erreur("erreur_' . $fichier . '","' . $message_taille_fichier . '")</script>';
}

// Méthode pour envoyer les fichiers du rapport
function envoi_rapport($fichier) {
    if (isset($_POST["$fichier" . "_nonce"])) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        $fichier_id = media_handle_upload("$fichier", 0);
        $fichier_url = wp_get_attachment_url($fichier_id);

// Déterminer le type de fichier: synthèse ou rapport
        if ($fichier == "synthese") {
            $nom_fichier = "fichier de synthèse";
        } elseif ($fichier == "rapport") {
            $nom_fichier = "rapport de stage";
        }

// Vérifier si l'envoi est réalisé avec succès, afficher les messages correspondants
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
