<?PHP
/*
Template Name: Test
*/

get_header(); ?>


<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <form method="post">
            <label>Votre email: </label> <input type="text" name="email" placeholder="email"/><br/>
            <label>Votre mot de passe: </label><input type="password" name="mdp" placeholder="mot de passe"/><br/>
            <label>Retapez votre mot de passe: </label><input type="password" name="mdpre" placeholder="mot de passe retape"/><br/>
            <input type="submit" name= "inscription" value = "envoyer"/>
        </form>

        <?php
        if (isset($_POST['inscription']) && $_POST['inscription'] == 'envoyer') {
            if ((isset($_POST['email']) && !empty($_POST['email'])) && (isset($_POST['mdp']) && !empty($_POST['mdp'])) && (isset($_POST['mdpre']) && !empty($_POST['mdpre']))) {
                if ($_POST['mdp'] != $_POST['mdpre']) {
                    $erreur = 'Les 2 mots de passe sont différents.';
                } else {
                    $Host = "localhost";
                    $User = "root";
                    $Passwd = "";
                    $BD = "rapport_stage";
                    $connexion = mysqli_connect("$Host", "$User", "$Passwd");
                    mysqli_select_db($connexion, $BD);
                    $sql = 'SELECT count(*) FROM utilisateurs WHERE email="' . mysqli_escape_string($connexion, $_POST['email']) . '"';
                    $req = mysqli_query($connexion, $sql) or die('Erreur SQL !<br />' . $sql . '<br />' . mysqli_error($connexion));
                    $data = mysqli_fetch_array($req);
                    if ($data[0] == 0) {
                        $sql = 'INSERT INTO utilisateurs VALUES("", '
                                . '"' . mysqli_escape_string($connexion, $_POST['email']) . '",'
                                . '"' . mysqli_escape_string($connexion, md5($_POST['mdp'])) . '",base64_encode(openssl_random_pseudo_bytes(8)),"")';
                        mysqli_query($connexion, $sql) or die('Erreur SQL !' . $sql . '<br />' . mysqli_error($connexion));

                        session_start();
                        $_SESSION['email'] = $_POST['email'];
                        
                        exit();
                    } else {
                        $erreur = 'Un membre possède déjà ce login.';
                    }
                }
            } else {
                $erreur = 'Au moins un des champs est vide.';
            }
        }
        ?>

        <?php
        if (isset($erreur)) {
            echo '<br />', $erreur;
        }
        ?>

    </body>
</html>

<?php
get_sidebar();
get_footer();
