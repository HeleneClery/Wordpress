<div class="main-container">
			<div id="page1Col">
				<!-- CONTENU CENTRAL-->
                <div id="colCenter">	
					<h1>Les offres d'emploi Réseau-TEE.net</h1>

					<form id="rechercheOffre" name="recherche" action="espace-candidats-offres.html#tab" method="post" class="form-horizontal" role="form">
						<input type="hidden" name="filtre" value="1">
						
												
						<h3 class="rechOffr">Critères de recherche d'offres</h3>
						<div class="well">
						
							<div class="row">
								<div class="col-sm-6">
									<!-- fonction -->
									<div class="form-group">
										<label class="col-sm-3 control-label">Fonction</label>
										<div class="col-sm-9">
											<select multiple="multiple" id="id_fonction" name="id_fonction[]"  class="select2" ><option value="2" title="Animation / Formation / Enseignement"  >Animation / Formation / Enseignement</option><option value="3" title="Communication / Médias"  >Communication / Médias</option><option value="4" title="Conseil"  >Conseil</option><option value="5" title="Direction"  >Direction</option><option value="6" title="Droit environnemental"  >Droit environnemental</option><option value="7" title="Encadrement"  >Encadrement</option><option value="8" title="Études / Projets / Développement"  >Études / Projets / Développement</option><option value="9" title="Gestion technique"  >Gestion technique</option><option value="10" title="Informatique"  >Informatique</option><option value="11" title="Service généraux (secrétariat, RH, finances, gestion, comptabilité)"  >Service généraux (secrétariat, RH, finances, gestion, comptabilité)</option><option value="12" title="Autres"  >Autres</option>
	</select>										</div>
									</div>
									
									<!-- localisation -->
									<div class="form-group">
										<label class="col-sm-3 control-label">Localisation</label>
										<div class="col-sm-9">
											<select multiple="multiple" id="id_localisation" name="id_localisation[]"  class="select2" ><option value="1" title="FRANCE"  >FRANCE</option><option value="4" title="Alsace"  >Alsace</option><option value="5" title="Aquitaine"  >Aquitaine</option><option value="6" title="Auvergne"  >Auvergne</option><option value="7" title="Bourgogne"  >Bourgogne</option><option value="8" title="Bretagne"  >Bretagne</option><option value="9" title="Centre"  >Centre</option><option value="10" title="Champagne-Ardenne"  >Champagne-Ardenne</option><option value="11" title="Corse"  >Corse</option><option value="26" title="Dom-Tom"  >Dom-Tom</option><option value="12" title="Franche-Comté"  >Franche-Comté</option><option value="13" title="Ile-de-France"  >Ile-de-France</option><option value="14" title="Languedoc-Rousillon"  >Languedoc-Rousillon</option><option value="15" title="Limousin"  >Limousin</option><option value="16" title="Lorraine"  >Lorraine</option><option value="17" title="Midi-Pyrénées"  >Midi-Pyrénées</option><option value="63" title="Monaco"  >Monaco</option><option value="18" title="Nord-Pas-de-Calais"  >Nord-Pas-de-Calais</option><option value="19" title="Normandie (Basse-)"  >Normandie (Basse-)</option><option value="20" title="Normandie (Haute-)"  >Normandie (Haute-)</option><option value="24" title="PACA"  >PACA</option><option value="21" title="Pays-de-la-Loire"  >Pays-de-la-Loire</option><option value="22" title="Picardie"  >Picardie</option><option value="23" title="Poitou-Charentes"  >Poitou-Charentes</option><option value="25" title="Rhône-Alpes"  >Rhône-Alpes</option><option value="3" title="INTERNATIONAL"  >INTERNATIONAL</option>
	</select>										</div>
									</div>

									<!-- annonceur -->
									<div class="form-group">
										<label class="col-sm-3 control-label">Annonceur</label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="mots_cles_annonceur" value="" placeholder="Contenant" />
										</div>
									</div>
									
									<!-- mot clé -->
									<div class="form-group">
										<label class="col-sm-3 col-xs-12 control-label" title="Effectue une recherche dans les champs Titre, Référence et Corps de l'annonce">Mots clés</label>
										<div class="col-sm-4 col-xs-6">
											<input placeholder="Contenant" type="text" class="form-control" name="mots_cles_1" value=""/>
										</div>
										<div class="col-sm-4 col-xs-6">
											<input placeholder="Contenant" type="text" class="form-control" name="mots_cles_2" value=""/>
										</div>
									</div>
									
									<div class="form-group">
										<div class="col-sm-4 col-sm-offset-3 col-xs-4">
											<label>
												<input type="radio" class="radio-inline" name="operator" value="1" checked="checked"/> ET
											</label> 
											<label>
												<input type="radio" class="radio-inline" name="operator" value="2" /> OU
											</label>
										</div>
									</div>
									
								</div>	
								<div class="col-sm-6">	

									<!-- secteur -->
									<div class="form-group">
										<label class="col-sm-3 control-label">Secteur</label>
										<div class="col-sm-9">
											<select multiple="multiple" id="id_secteur" name="id_secteur[]"  class="select2" ><option value="1" title="Agriculture / Bois / Forêt"  >Agriculture / Bois / Forêt</option><option value="12" title="Aquaculture / Pêche / Mer"  >Aquaculture / Pêche / Mer</option><option value="18" title="Bâtiment / Eco-construction"  >Bâtiment / Eco-construction</option><option value="8" title="Biodiversité / Services écologiques"  >Biodiversité / Services écologiques</option><option value="2" title="Communication / Médias"  >Communication / Médias</option><option value="10" title="Déchets / Recyclage"  >Déchets / Recyclage</option><option value="3" title="Développement durable"  >Développement durable</option><option value="21" title="Développement territorial"  >Développement territorial</option><option value="9" title="Eau / Assainissement"  >Eau / Assainissement</option><option value="17" title="Economie sociale et solidaire"  >Economie sociale et solidaire</option><option value="5" title="Education à l'environnement (EEDD)"  >Education à l'environnement (EEDD)</option><option value="6" title="Gestion de l'énergie / Energies renouvelables"  >Gestion de l'énergie / Energies renouvelables</option><option value="23" title="Management environnemental"  >Management environnemental</option><option value="13" title="Recherche et environnement"  >Recherche et environnement</option><option value="19" title="Risques et pollutions (air-sol-bruit)"  >Risques et pollutions (air-sol-bruit)</option><option value="14" title="Sol, sous-sol/environnement"  >Sol, sous-sol/environnement</option><option value="11" title="Systèmes d'information"  >Systèmes d'information</option><option value="15" title="Tourisme durable"  >Tourisme durable</option><option value="20" title="Transports durables / Eco-mobilité"  >Transports durables / Eco-mobilité</option><option value="7" title="Urbanisme / Cadre de vie / Paysage"  >Urbanisme / Cadre de vie / Paysage</option>
	</select>										</div>
									</div>
						
									<!-- Type de contrat -->
									<div class="form-group">
										<label class="col-sm-3 control-label">Contrat</label>
										<div class="col-sm-9">
											<select multiple="multiple" id="id_contrat" name="id_contrat[]"  class="select2" ><option value="32" title="Apprentissage"  >Apprentissage</option><option value="35" title="Avis de concours"  >Avis de concours</option><option value="34" title="Bénévolat"  >Bénévolat</option><option value="31" title="CDD"  >CDD</option><option value="30" title="CDI"  >CDI</option><option value="36" title="Emploi aidé"  >Emploi aidé</option><option value="39" title="Fonction Publique"  >Fonction Publique</option><option value="33" title="Stage"  >Stage</option><option value="37" title="Volontariat / Service civique"  >Volontariat / Service civique</option><option value="38" title="Z Autre"  >Z Autre</option>
	</select>										</div>
									</div>
						
									<!-- Type de structure -->
									<div class="form-group">
										<label class="col-sm-3 control-label">Structure</label>
										<div class="col-sm-9">
											<select multiple="multiple" id="id_statut_co" name="id_statut_co[]"  class="select2" ><option value="1" title="Association"  >Association</option><option value="4" title="Autre"  >Autre</option><option value="3" title="Entreprise"  >Entreprise</option><option value="2" title="Organisme public"  >Organisme public</option>
	</select>										</div>
									</div>
			
									<!--pagination -->
									<div class="form-group">
										<div class="col-sm-9 col-sm-offset-3">
											<select id="id_paquet" name="id_paquet" class="select2 autoReloadForm">
	<option value="10" title="Afficher 10 résultats par page" >Afficher 10 résultats par page</option>
	<option value="20" title="Afficher 20 résultats par page" >Afficher 20 résultats par page</option>
	<option value="50" title="Afficher 50 résultats par page" >Afficher 50 résultats par page</option>
	</select>										</div>
									</div>
									
									<div class="row">
										<div class="col-sm-12 text-right">
											<input type="submit" class="btn btn-success" title="Filtrer" value="Filtrer"/>
											<a href="?defiltre&reset" class="btn btn-default" title="Réinitialiser les critères">Défiltrer</a>
										</div>
									</div>
									
								</div>
							</div>

						</div>

												
					</form>
