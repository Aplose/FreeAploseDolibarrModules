<?php

// Load Dolibarr environment

use Stripe\Terminal\Location;

$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

// Load translation files
$langs->loadLangs(array("dolilinks@dolilinks"));

if (empty($user->admin)) {
	accessforbidden('Must be admin');
}

try {



$sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe_parent";

$resql = $db->query($sql);

if($resql === false){
    throw new Exception("SQL Error on importing links. Error: ".$db->lasterror());
}

$links = [];

while($arr = $db->fetch_array($resql)){
    $links[] = $arr;
}

dol_include_once('/custom/dolilinks/class/SocieteLink.class.php');
$societeLink = new SocieteLink($db);

foreach ($links as $key => $link) {
    $sql = "INSERT INTO " . MAIN_DB_PREFIX . "dolilinks_societe_link (";
    $sql .= "fk_parent, fk_child, entity, fk_link_type, date_creation, tms, fk_user_creat, import_key";
    $sql .= ") VALUES (";
    // Inverser car erreur dans SocParent
    $sql .= $link['fk_child'] . ", ";
    $sql .= $link['fk_parent'] . ", ";
    $sql .= $link['entity'] . ", ";
    $sql .= "NULL, ";
    $sql .= "'".$link['date_creation']."', ";
    $sql .= "'".$link['tms'] . "', ";
    $sql .= $user->id . ", ";
    $sql .= dol_print_date(dol_now(), 'dayhourlog');
    $sql .= ")";

    // Vérifier qu'il n'existe pas déjà un lien identique
    if($societeLink->linkExists($link['fk_parent'], $link['fk_child'])){
        continue;
    }

    // Vérifier que les deux Societe (parent et enfant) existent
    $societeNotExist = false;
    foreach (array($link['fk_parent'], $link['fk_child']) as $key => $socid) {
        $soc = new Societe($db);
        if($soc->fetch($socid) === 0){
            $societeNotExist = true;
        }
    }
    // Pas de Societe avec cette id: on continue
    if($societeNotExist) { continue; }

    $resql = $db->query($sql);

    if($resql === false){
        throw new Exception("SQL Error on saving import. Error: ".$db->lasterror());
    }
}
} catch (\Throwable $th) {
    setEventMessage([$langs->trans("ERROR_OCCURRED"), $th->getMessage()], 'errors');
    header('Location: ' . dol_buildpath('/custom/dolilinks/admin/setup.php', 1));
    exit;
}
setEventMessage($langs->trans("TABLES_SUCCESSFULLY_IMPORTED"));



header('Location: ' . dol_buildpath('/custom/dolilinks/admin/setup.php', 1));