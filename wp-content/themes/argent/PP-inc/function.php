<?php  
function ListeTechno($id){
	global $wpdb;
	$resultatTechno = $wpdb->get_results($wpdb-> prepare("SELECT * FROM technologies where id_stage = %d", $id));
	$techno = "";
	foreach ($resultatTechno as $post) {
	 		$techno .= $post->technologie.", " ;
		}
	$rest = substr($techno, 0, -2);
	return $rest;
}


?>