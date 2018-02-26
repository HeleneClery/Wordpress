<?php
/* Template Name: EnvoiMailRapportStage */
get_header();
?>

<?php
$Host = "localhost";
$User = "root";
$Passwd = "";
$BD = "rapport_stage";
$connexion = mysqli_connect("$Host", "$User", "$Passwd");
mysqli_select_db($connexion, $BD);
$requeteListe = 'SELECT es_email_group FROM wp_es_emaillist group by es_email_group';
$resultatListe = mysqli_query($connexion, $requeteListe);
?>

<html>
    <form id = "form" method="POST">
        <select name="selectGroupe" id="selectGroupe">
            <?php
            while ($l = mysqli_fetch_array($resultatListe)) {
                echo '<option name="' . $l[0] . '" value="' . $l[0] . '">"' . $l[0] . '"</option>"';
            }
            ?>
        </select>
        <button type="submit" id="edit-submit" name="submit" value="Sendmail" >Sendmail</button>
    </form>
</html>

<?php
if (isset($_POST['submit'])) {
    $selectGroupe = ($_POST["selectGroupe"]);
    $requeteMail = "SELECT es_email_mail FROM wp_es_emaillist WHERE es_email_group = '$selectGroupe'";
    $resultatMail = mysqli_query($connexion, $requeteMail);
   
    while ($l = mysqli_fetch_array($resultatMail)) {
        $to = $l[0];
        $subject = "Invitation";
        $requeteGuid = "SELECT es_email_guid FROM wp_es_emaillist WHERE es_email_mail = '$l[0]'";
        $resultatGuid = mysqli_query($connexion, $requeteGuid);
        $guid = mysqli_fetch_array($resultatGuid)[0];
        $txt = "Bonjour,\n" .
                "Bienvenue au forum CCI. Merci de vous inscrire avec le lien ci-dessous: " .
                "http://localhost/wordpress/inscription/?email=$l[0]&guid=$guid";
        $headers = "From: Master CCI Tours";
        mail($to, $subject, $txt, $headers);
        echo 'Envoyé à '.$to.'<br/>';
    }
}
?>

<?php
get_footer();
