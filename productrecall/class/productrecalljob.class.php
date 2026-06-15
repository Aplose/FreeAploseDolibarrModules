<?php

require_once __DIR__ . "/recall.class.php";

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of ProductRecallJob
 *
 * @author oandrade
 */
class ProductRecallJob {

    private static $apiUrl = "https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets/rappelconso0/records";

    public function verifyProductRecalls() {
        global $conf, $db, $user;
        //d'abord si la table recall est vide on va chercher toute la liste
        $sql = "SELECT COUNT(rowid) as nb FROM " . MAIN_DB_PREFIX . "productrecall_recall";
        $resql = $db->query($sql);
        if ($resql) {
            $obj = $db->fetch_object($resql);
            $recallsToNotify = array();
            if ($obj->nb == 0) {
                // dans ce cas on fetch l'api conso                    
                $year = 2018;
                $currentYear = dol_print_date(dol_now(), '%Y');
                $limit = 100;
                $inClause = $this->getInClause();
                while ($year <= $currentYear) {
                    $offset = 0;
                    while (true) {
//                        $resource = self::$apiUrl . "?where=date_de_publication%20>%27" . $year . "-01-01%27%20AND%20date_de_publication%20<%27" . $year . "-12-31%27&limit=" . $limit . "&offset=" . $offset;
                        $resource = self::$apiUrl . "?where=date_de_publication%20>%27" . $year . "-01-01%27%20AND%20date_de_publication%20<%27" . $year . "-12-31%27" . $inClause . "&limit=" . $limit . "&offset=" . $offset;
                        $jsonString = file_get_contents($resource);
                        $jsonObject = json_decode($jsonString);
                        //on alimente la table des recalls
                        foreach ($jsonObject->results as $recallJson) {
                            $recall = $this->createRecall($recallJson, $db, $user);
                            //on ne garde en notif que ceux de l'année en cours au premier chargement
                            if ($year == $currentYear) {
                                $recallsToNotify[] = $recall;
                            }
                        }
                        //condition de sortie de la boucle
                        if ($jsonObject->total_count < $offset) {
                            break;
                        }
                        $offset += $limit;
                    }
                    $year++;
                }
            } else {
                //On récupère le recall le plus récent de la base des Recalls
                $sqlLastRowId = "SELECT max(rowid) as lastRowId FROM " . MAIN_DB_PREFIX . "productrecall_recall";
                $resqlLastRowId = $db->query($sqlLastRowId);
                $lastRowId = 0;
                if ($resqlLastRowId) {
                    $objRowId = $db->fetch_object($resqlLastRowId);
                    $lastRowId = $objRowId->lastRowId;
                    $resqlLastRowId->close();
                } else {
                    return -1;
                }
                //on remonte le dernier Recall
                $lastRecall = new Recall($db);
                $lastRecall->fetch($lastRowId);
                //on prend la dernière date de remontée :
                $lastDateDePublication = dol_print_date($lastRecall->datedepublication, '%Y-%m-%d');
                //on construit la requête pour aller chercher les derniers recall depuis (ils va nous remonter ceux de la date en cours aussi...)
                $limit = 100;
                $offset = 0;
                $inClause = $this->getInClause();
                while (true) {
                    $resource = self::$apiUrl . "?where=date_de_publication>=%27" . $lastDateDePublication . "%27" . $inClause . "&limit=" . $limit . "&offset=" . $offset;
                    $jsonString = file_get_contents($resource);
                    $jsonObject = json_decode($jsonString);
                    //on alimente la table des recalls
                    foreach ($jsonObject->results as $recallJson) {
                        if (!$this->existRecallWithGuid($recallJson->rappelguid, $db)) {
                            $recallsToNotify[] = $this->createRecall($recallJson, $db, $user);
                        }
                    }
                    //condition de sortie de la boucle
                    if ($jsonObject->total_count < $offset) {
                        break;
                    }
                    $offset += $limit;
                }
            }
            //Traiter les notifications si besoin ici et le mail 
            if (count($recallsToNotify) > 0) {
                //notification email
                $this->sendNotiFMail($recallsToNotify);
            }
            $resql->close();
        }
        //si le job s'execute régulièrement alors on va seulement mettre à jour
        return 0;
    }

    private function getInClause() {
        global $conf;
        $catFilter = (!empty($conf->global->PRODUCTRECALL_PARAM_CAT_TO_FOLLOW)) ? $conf->global->PRODUCTRECALL_PARAM_CAT_TO_FOLLOW : '';
        $subCatFilter = (!empty($conf->global->PRODUCTRECALL_PARAM_SUB_CAT_TO_FOLLOW)) ? $conf->global->PRODUCTRECALL_PARAM_SUB_CAT_TO_FOLLOW : '';
        if (empty($catFilter) && empty($subCatFilter)) {
            return '';
        }
        $inclause = '';
        if (!empty($catFilter)) {
            if (!empty($subCatFilter)) {
                $inclause .= "(";
            }
            $inclause .= "categorie_de_produit in " . $this->getInListInString($catFilter);
        }
        if (!empty($subCatFilter)) {
            if (!empty($catFilter)) {
                $inclause .= " or ";
            }
            $inclause .= "sous_categorie_de_produit in " . $this->getInListInString($subCatFilter);
            if (!empty($catFilter)) {
                $inclause .= ")";
            }
        }
        return '%20and%20' . str_replace(" ", "%20", $inclause);
    }

    private function getInListInString($filter) {
        $filterArray = explode(",", $filter);
        $listInString = '';
        foreach ($filterArray as $value) {
            //chaque valeur peut contenir des | au lieu de ,
            $value = str_replace("|", ",", $value);
            //on construit listInString
            if (!empty($listInString)) {
                $listInString .= ",";
            }
            $listInString .= "%22" . $value . "%22";
        }
        return "(" . $listInString . ")";
    }

    private function sendNotiFMail($recallsToNotify) {
        global $conf, $langs;
        if (!empty($conf->global->PRODUCTRECALL_PARAM_NOTIF_EMAIL)) {
            require_once DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php';
            $emailHtmlText = "";
            $subject = "Notification rappel(s) produit(s) ProductRecall";
            $sendto = $conf->global->PRODUCTRECALL_PARAM_NOTIF_EMAIL;
            $from = $conf->global->MAIN_MAIL_EMAIL_FROM;
            $emailHtmlText .= "<html><head></head><body>";
            $emailHtmlText .= "<h1>Nouveau(x) Rappel(s) produit(s)</h1>";
            $emailHtmlText .= "<h3>(selon votre configuration du module ProductRecall)</h3>";
            if (count($recallsToNotify) > 100) {
                $emailHtmlText .= "<h2>Attention : plus de 100 rappels produits viennent d'être chargés</h2>";
                $emailHtmlText .= "<p>Ceci peut être du au fait qu'il s'agit d'un premier chargement ou que vos critères sont larges. Veuillez vérifier dans ProductRecall (ou directement sur le site RappelConso) si des rappels plus anciens ne vous concerneraient pas.</p>";
            }
            $count = 0;
            foreach ($recallsToNotify as $recall) {
                $emailHtmlText .= "<table>";
                if (!empty($recall->liensverslesimages)) {
                    $images = explode(" ", $recall->liensverslesimages);
                    $emailHtmlText .= "<tr><td><img alt=\"[Image produit]\" height=\"200px\" src=\"" . $images[0] . "\"></td><td></td></tr>";
                }
                $emailHtmlText .= (!empty($recall->nomsdesmodelesoureferences)) ? "<tr><td>Noms des modèles ou références :</td><td>" . $recall->nomsdesmodelesoureferences . '</td></tr>' : '';
                $emailHtmlText .= (!empty($recall->nomdelamarqueduproduit)) ? "<tr><td>Marque :</td><td>" . $recall->nomdelamarqueduproduit . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->catgoriedeproduit)) ? "<tr><td>Catégorie :</td><td>" . $recall->catgoriedeproduit . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->souscatgoriedeproduit)) ? "<tr><td>Sous-catégorie  :</td><td>" . $recall->souscatgoriedeproduit . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->naturejuridiquedurappel)) ? "<tr><td>Nature Juridique du rappel :</td><td>" . $recall->naturejuridiquedurappel . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->identificationdesproduits)) ? "<tr><td>Identification des produits :</td><td>" . $recall->identificationdesproduits . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->conditionnements)) ? "<tr><td>Conditionnements :</td><td>" . $recall->conditionnements . "</td></tr>" : "<tr><td></td><td></td></tr>";
                $emailHtmlText .= (!empty($recall->datedebutfindecommercialisa)) ? "<tr><td>Dates de début et de fin de commercialisation :</td><td>" . $recall->datedebutfindecommercialisa . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->temperaturedeconservation)) ? "<tr><td>Température de conservation :</td><td>" . $recall->temperaturedeconservation . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->marquedesalubrite)) ? "<tr><td>Marque de salubrité :</td><td>" . $recall->marquedesalubrite . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->informationscomplementaires)) ? "<tr><td>Informations complémentaires :</td><td>" . $recall->informationscomplementaires . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->zonegeographiquedevente)) ? "<tr><td>Zone géographique de vente :</td><td>" . $recall->zonegeographiquedevente . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->distributeurs)) ? "<tr><td>Distributeurs :</td><td>" . $recall->distributeurs . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->motifdurappel)) ? "<tr><td>Motif du rappel :</td><td>" . $recall->motifdurappel . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->risquesencourusparleconsomm)) ? "<tr><td>Motif du rappel :</td><td>" . $recall->risquesencourusparleconsomm . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->preconisationssanitaires)) ? "<tr><td>Préconisations sanitaires :</td><td>" . $recall->preconisationssanitaires . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->descriptioncomplementairedu)) ? "<tr><td>Description complémentaire du risque :</td><td>" . $recall->descriptioncomplementairedu . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->conduitesatenirparleconsomm)) ? "<tr><td>Conduite à tenir par le consommateur :</td><td>" . $recall->conduitesatenirparleconsomm . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->numerodecontact)) ? "<tr><td>Conduite à tenir par le consommateur :</td><td><a href=\"tel:" . $recall->numerodecontact . "\">" . $recall->numerodecontact . "</a></td></tr>" : "";
                $emailHtmlText .= (!empty($recall->modalitesdecompensation)) ? "<tr><td>Modalité de compensation :</td><td>" . $recall->modalitesdecompensation . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->datedefindelaprocedurederap)) ? "<tr><td>Date de fin de la procédure de rappel :</td><td>" . $recall->datedefindelaprocedurederap . "</td></tr>" : "";
                if (!empty($recall->lienverslalistedesproduits)) {
                    $listeProduits = explode(" ", $recall->lienverslalistedesproduits);
                    $emailHtmlText .= "<tr><td>Lien vers la liste des produits :</td><td><a href=\"" . $listeProduits[0] . "\">" . $listeProduits[0] . "</a></td></tr>";
                }
                if (!empty($recall->lienverslalistedesdistribut)) {
                    $listeDistributeurs = explode(" ", $recall->lienverslalistedesdistribut);
                    $emailHtmlText .= "<tr><td>Lien vers la liste des distributeurs :</td><td><a href=\"" . $listeDistributeurs[0] . "\">" . $listeDistributeurs[0] . "</a></td></tr>";
                }
                $emailHtmlText .= (!empty($recall->lienversaffichettepdf)) ? "<tr><td>Affichette en pdf :</td><td><a href=\"" . $recall->lienversaffichettepdf . "\">" . $recall->lienversaffichettepdf . "</a></td></tr>" : "";
                $emailHtmlText .= (!empty($recall->lienverslaficherappel)) ? "<tr><td>Fiche de rappel :</td><td><a href=\"" . $recall->lienverslaficherappel . "\">" . $recall->lienverslaficherappel . "</a></td></tr>" : "";
                $emailHtmlText .= (!empty($recall->datedepublication)) ? "<tr><td>Date de publication :</td><td>" . $recall->datedepublication . "</td></tr>" : "";
                $emailHtmlText .= (!empty($recall->informationscomppubliques)) ? "<tr><td>Informations complémentaires publiques :</td><td>" . $recall->informationscomppubliques . "</td></tr>" : "";
                $emailHtmlText .= "</table>";
                $emailHtmlText .= "<br>___________________________________________________________________________________<br><br>";
                $count++;
                if($count>=100){
                    break;
                }
            }
            $emailHtmlText .= "<p>ProductRecall est un module édité par <a href=\"https://www.aplose.fr\">Aplose</a>.</p>";
            $emailHtmlText .= "<p>En cas d'anomalie constatée ou pour toute demande, merci d'utiliser notre formulaire de support : <a href=\"https://aplose.ma-gestion-cloud.fr/public/ticket/\">Support Aplose</a>.</p>";
            $emailHtmlText .= "<p>Si vous recherchez un hébergement Dolibarr, performant, sécurisé et sauvegardé pour 10€HT par mois, visitez <a href=\"https://www.ma-gestion-cloud.fr/\">Ma Gestion Cloud</a>. Nous vous accompagnerons pour tout transfert de votre hébergement actuel et mise à jour de votre Dolibarr.</p>";
            $emailHtmlText .= "<br>_____________<br><br><b>L'équipe d'Aplose</b>";
            $emailHtmlText .= "</body></html>";
            //envoi email
            $mailfile = new CMailFile($subject, $sendto, $from, $emailHtmlText, array(), array(), array(), '', '', 0, 1, '', '', '', '', 'standard');
            if ($mailfile->error) {
                setEventMessages($mailfile->error, $mailfile->errors, 'errors');
            } else {
                $result = $mailfile->sendfile();
                if ($result) {
                    $mesg = $langs->trans('MailSuccessfulySent', $mailfile->getValidAddress($from, 2), $mailfile->getValidAddress($sendto, 2));
                    setEventMessages($mesg, null, 'mesgs');
                } else {
                    $langs->load("other");
                    $mesg = '<div class="error">';
                    if ($mailfile->error) {
                        $mesg .= $langs->trans('ErrorFailedToSendMail', $from, $sendto);
                        $mesg .= '<br>' . $mailfile->error;
                    } else {
                        $mesg .= 'No mail sent. Feature is disabled by option MAIN_DISABLE_ALL_MAILS';
                    }
                    $mesg .= '</div>';
                    setEventMessages($mesg, null, 'warnings');
                }
            }
        }
    }

    private function existRecallWithGuid($guid, $db) {
        $result = false;
        $sql = "SELECT COUNT(rowid) as nb FROM " . MAIN_DB_PREFIX . "productrecall_recall WHERE rappelguid='" . $guid . "'";
        $resql = $db->query($sql);
        if ($resql) {
            $obj = $db->fetch_object($resql);
            if ($obj->nb > 0) {
                $result = true;
            }
            $resql->close();
        }
        return $result;
    }

    private function createRecall($recallJson, $db, $user) {
        $recall = new Recall($db);
        $recall->label = $recallJson->noms_des_modeles_ou_references; //idem $recall->noms_des_modeles_ou_references
        $recall->referencefiche = $recallJson->reference_fiche;
        $recall->naturejuridiquedurappel = $recallJson->nature_juridique_du_rappel;
        $recall->catgoriedeproduit = $recallJson->categorie_de_produit;
        $recall->souscatgoriedeproduit = $recallJson->sous_categorie_de_produit;
        $recall->nomdelamarqueduproduit = $recallJson->nom_de_la_marque_du_produit;
        $recall->nomsdesmodelesoureferences = $recallJson->noms_des_modeles_ou_references;
        $recall->identificationdesproduits = $recallJson->identification_des_produits;
        $recall->conditionnements = $recallJson->conditionnements;
        $recall->datedebutfindecommercialisa = $recallJson->date_debut_fin_de_commercialisation;
        $recall->temperaturedeconservation = $recallJson->temperature_de_conservation;
        $recall->marquedesalubrite = $recallJson->marque_de_salubrite;
        $recall->informationscomplementaires = $recallJson->informations_complementaires;
        $recall->zonegeographiquedevente = $recallJson->zone_geographique_de_vente;
        $recall->distributeurs = $recallJson->distributeurs;
        $recall->motifdurappel = $recallJson->motif_du_rappel;
        $recall->risquesencourusparleconsomm = $recallJson->risques_encourus_par_le_consommateur;
        $recall->preconisationssanitaires = $recallJson->preconisations_sanitaires;
        $recall->descriptioncomplementairedu = $recallJson->description_complementaire_du_risque;
        $recall->conduitesatenirparleconsomm = $recallJson->conduites_a_tenir_par_le_consommateur;
        $recall->numerodecontact = $recallJson->numero_de_contact;
        $recall->modalitesdecompensation = $recallJson->modalites_de_compensation;
        $recall->datedefindelaprocedurederap = $recallJson->date_de_fin_de_la_procedure_de_rappel;
        $recall->informationscomppubliques = $recallJson->informations_complementaires_publiques;
        $recall->liensverslesimages = $recallJson->liens_vers_les_images;
        $recall->lienverslalistedesproduits = $recallJson->lien_vers_la_liste_des_produits;
        $recall->lienverslalistedesdistribut = $recallJson->lien_vers_la_liste_des_distributeurs;
        $recall->lienversaffichettepdf = $recallJson->lien_vers_affichette_pdf;
        $recall->lienverslaficherappel = $recallJson->lien_vers_la_fiche_rappel;
        $recall->rappelguid = $recallJson->rappelguid;
        $recall->datedepublication = $recallJson->date_de_publication;
        $recall->create($user);
        return $recall;
    }
}
