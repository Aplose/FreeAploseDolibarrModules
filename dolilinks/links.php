<?php
/* Copyright (C) 2001-2005  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2015       Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2025		Florian TOCCO			<ftocco@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       dolilinks/dolilinksindex.php
 *	\ingroup    dolilinks
 *	\brief      Home page of dolilinks top menu
 */

// Load Dolibarr environment
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

if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array("dolilinks@dolilinks"));
if (!$user) {
    accessforbidden();
}


$action = GETPOST('action', 'aZ09');
$socId = GETPOST('id', 'int');
if (!$socId) {
    llxHeader('', $langs->trans('CreateLinkPageHtmlTitle'), '');
    print load_fiche_titre($langs->trans("CreateLinkPageTitle", (string)$socId));
    setEventMessage($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("ThirdParty") . "::rowid"), 'errors');
    llxFooter();
    exit;
}

$langs->load('dolilinks@dolilinks');

dol_include_once('custom/dolilinks/class/SocieteLink.class.php');
$societeLink = new SocieteLink($db);

switch ($action) {
    case 'add-parent':
        $parentId = GETPOST('parent-id', 'int');
        $linkType = GETPOSTISSET('parent-link-type') ? GETPOST('parent-link-type') : null;
        if (!$parentId) {
            setEventMessage($langs->trans("PARENT_ID_REQUIRED"), "errors");
            break;
        }
        if ($parentId === $socId) {
            // header('Location: ' . dol_buildpath('/custom/dolilinks/links.php?id=' . $socId, 1));
            setEventMessage($langs->trans("CANNOT_LINK_COMPANY_TO_ITSELF"), 'errors');
            break;
        }
        if((int)$linkType === -1){$linkType = null;}
        $societeLink->create($parentId, $socId, $user, $linkType, $langs);
        header('Location: ' . dol_buildpath('/custom/dolilinks/links.php?id=' . $socId, 1));
        exit;
        break;

    case 'delete-parent':
        $parentId = GETPOST('parent-id', 'int');
        if (!$parentId) {
            setEventMessage($langs->trans("PARENT_ID_REQUIRED"), "errors");
            break;
        }
        $societeLink->deleteParent($socId, $parentId, $langs);
        header('Location: ' . dol_buildpath('/custom/dolilinks/links.php?id=' . $socId, 1));
        exit;
        break;

    case 'add-child':
        $childId = GETPOST('child-id', 'int');
        $linkType = GETPOSTISSET('child-link-type') ? GETPOST('child-link-type') : null;
        if (!$childId) {
            setEventMessage($langs->trans("CHILD_ID_REQUIRED"), "errors");
        }
        if ($childId === $socId) {
            setEventMessage($langs->trans("CANNOT_LINK_COMPANY_TO_ITSELF"), 'errors');
            break;
        }
        if((int)$linkType === -1){$linkType = null;}
        $societeLink->create($socId, $childId, $user, $linkType, $langs);
        header('Location: ' . dol_buildpath('/custom/dolilinks/links.php?id=' . $socId, 1));
        exit;
        break;

    case 'delete-child':
        $childId = GETPOST('child-id', 'int');
        if (!$childId) {
            setEventMessage($langs->trans("CHILD_ID_REQUIRED"), "errors");
            break;
        }
        $societeLink->deleteChild($socId, $childId, $langs);
        header('Location: ' . dol_buildpath('/custom/dolilinks/links.php?id=' . $socId, 1));
        exit;
        break;

    default:
}

$currentSociete = new Societe($db);
$result = $currentSociete->fetch($socId);
if ($result === 0) {
    throw new Exception($langs->trans("THIRD_PARTY_NOT_FOUND"));
}
if ($result < 0) {
    throw new Exception($currentSociete->error);
}

// Récupérer les parents et les enfants
$parents = $societeLink->getParents($socId, -1, $langs);
$childs = $societeLink->getChilds($socId, -1, $langs);


// Récupérer des ids des parents et des enfants
$parentIds = array_map('intval', $societeLink->getParentIds($socId, $langs));
$childIds = array_map('intval', $societeLink->getChildIds($socId, $langs));
$parentIds[] = $socId; // exclure le tiers courant


$sql = "SELECT rowid, nom FROM " . MAIN_DB_PREFIX . "societe WHERE entity IN (0, " . $conf->entity . ") ORDER BY nom ASC";
$resql = $db->query($sql);


$unlinkedCompanies = [];
if ($resql) {
    while ($arr = $db->fetch_array($resql)) {
        if (!in_array($arr['rowid'], $parentIds) && !in_array($arr['rowid'], $childIds)) {
            $unlinkedCompanies[$arr['rowid']] = isset($arr['ref']) ? ($arr['ref'] . ' - ' . $arr['nom']) : $arr['nom'];
        }
    }
}

dol_include_once('/custom/dolilinks/class/LinkType.class.php');
$linkType = new LinkType($db, $conf);
$linkTypes = $linkType->getAll($langs);


// $linkTypes = 



/**
 * 
 * View
 * 
 */


llxHeader('', 'Create link', '');


echo  load_fiche_titre($langs->trans("CreateLinkPageTitle") . " " . $currentSociete->getNomUrl(1), '', 'fa-sitemap');

?>

<table class="tagtable noborder" width="100%">



    <tr class="tagtr liste_titre">

        <!-- Colonne Parents -->
        <th>
            <!-- Formulaire pour ajouter un parent -->

            <strong style="margin-right: 20px;"><?php echo $langs->trans('Parents') . '</strong> ' . count($parents) . ' ' . $langs->trans('ElementOrElements'); ?>

                <form id="add-parent-form" method="POST" action="<?php echo dol_buildpath('/custom/dolilinks/links.php', 1); ?>">
                    <input type="hidden" name="action" value="add-parent">
                    <input type="hidden" name="id" value="<?php echo $currentSociete->id; ?>">
                    <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                    <input type="hidden" name="parent-link-type" value="">

                    <div style="display: flex;flex-direction: column; gap: 5px;">

                        <?php
                        print $form->selectarray(
                            'parent-id',
                            $unlinkedCompanies,
                            '',
                            $langs->trans('Company'),    // showempty
                            0,    // translate
                            0,    // maxlen
                            'style="width: 300px"',   // moreattr
                            1     // enable select2
                        );
                        ?>


                        <!-- Select link type form -->
                        <div>
                            <?php
                            print $form->selectArray(
                                'parent-link-type',
                                array_map(fn($item) => $item->label, $linkTypes),
                                '',
                                $langs->trans('LinkType'),    // showempty
                                0,    // translate
                                0,    // maxlen
                                'style="width: 300px"'

                            );
                            ?>

                        </div>

                        <button type="submit" id="add-parent-button" class="butActionRefused opacitymedium nomarginleft center width100" disabled="true">
                            <?php print $langs->trans('Add'); ?>
                        </button>

                        <script>
                            let addParentButton = document.getElementById("add-parent-button");

                            document.getElementById('parent-id').onchange = (e) => {
                                console.log('onchange')
                                const selectedParentId = +e.target.value;
                                if (selectedParentId <= 0) {
                                    addParentButton.classList.replace('butAction', "butActionRefused");
                                    addParentButton.classList.add('opacitymedium');
                                    addParentButton.disabled = true;
                                } else {
                                    addParentButton.classList.replace('butActionRefused', "butAction");
                                    addParentButton.classList.remove('opacitymedium');
                                    addParentButton.disabled = false;
                                }
                            };
                        </script>

                    </div>

                </form>
        </th>



        <!-- Colonne Childs -->

        <th>
            <strong style="margin-right: 20px;"><?php echo $langs->trans('Childs') . '</strong> ' . count($childs) . ' ' . $langs->trans('ElementOrElements'); ?>
                <form id="add-child-form">
                    <input type="hidden" name="action" value="add-child">
                    <input type="hidden" name="id" value="<?php echo $currentSociete->id; ?>">
                    <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                    <input type="hidden" name="child-link-type" value="">

                    <div style="display: flex;flex-direction: column; gap: 5px;">

                        <!-- Select Societe form -->
                        <?php
                        print $form->selectArray(
                            'child-id',
                            $unlinkedCompanies,
                            '',
                            $langs->trans('Company'),    // showempty
                            0,    // translate
                            0,    // maxlen
                            'style="width: 300px"'

                        );
                        ?>

                        <!-- Select link type form -->
                        <div>
                            <?php
                            print $form->selectArray(
                                'child-link-type',
                                array_map(fn($item) => $item->label, $linkTypes),
                                '',
                                $langs->trans('LinkType'),    // showempty
                                0,    // translate
                                0,    // maxlen
                                'style="width: 300px"'

                            );
                            ?>

                        </div>

                        <button type="submit" id="add-child-button" class="butActionRefused opacitymedium nomarginleft center width100" disabled="true">
                            <?php print $langs->trans('Add'); ?>
                        </button>
                        <script>
                            let addChildButton = document.getElementById("add-child-button");

                            document.getElementById('child-id').onchange = (e) => {
                                console.log('onchange')
                                const selectedChildId = +e.target.value;
                                if (selectedChildId <= 0) {
                                    addChildButton.classList.replace('butAction', "butActionRefused");
                                    addChildButton.classList.add('opacitymedium');
                                    addChildButton.disabled = true;
                                } else {
                                    addChildButton.classList.replace('butActionRefused', "butAction");
                                    addChildButton.classList.remove('opacitymedium');
                                    addChildButton.disabled = false;
                                }
                            };
                        </script>
                    </div>
                </form>
        </th>
    </tr>


    <?php for ($i = 0; $i < (count($parents) > count($childs) ? count($parents) : count($childs)); $i++) { ?>
        <tr>

            <!-- Lignes Parents -->
            <td>
                <!-- Formulaire pour supprimer un parent -->
                <form id="delete-parent-form-<?php print $i; ?>" style="width: min-content;display:inline">
                    <input type="hidden" name="action" value="delete-parent">
                    <input type="hidden" name="id" value="<?php echo $socId; ?>">
                    <input type="hidden" name="parent-id" value="<?php echo (isset($parents[$i]) ? $parents[$i]->id : ''); ?>">
                    <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                    <?php if (isset($parents[$i])) { ?>
                        <span style="margin-right:15px;cursor:pointer" class="icon-action">
                            <a onclick="document.getElementById('delete-parent-form-<?php print $i; ?>').submit()" style="cursor:pointer">
                                <?php
                                echo img_picto('Delete', 'delete', '', false, 0);
                                ?>
                            </a>
                        </span>
                    <?php } ?>
                </form>
                <?php
                // $typeId = $societeLink->getLinkTypeId($parents[$i]->id, $currentSociete->id);
                // $type = (new LinkType($db))->fetch($typeId);
                echo (isset($parents[$i]) ? $parents[$i]->getNomUrl(1) : '');
                ?>
            </td>


            <!-- Lignes Childs -->
            <td>
                <!-- Formulaire pour supprimer un child -->
                <form id="delete-child-form-<?php print $i; ?>" style="width: min-content;display:inline">
                    <input type="hidden" name="action" value="delete-child">
                    <input type="hidden" name="id" value="<?php echo $socId; ?>">
                    <input type="hidden" name="child-id" value="<?php echo (isset($childs[$i]) ? $childs[$i]->id : ''); ?>">
                    <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                    <!-- Si childs[$i] -->
                    <?php if (isset($childs[$i])) { ?>
                        <span style="margin-right:15px;cursor:pointer" class="icon-action">
                            <a onclick="document.getElementById('delete-child-form-<?php print $i; ?>').submit()" style="cursor:pointer">
                                <?php
                                echo img_picto('Delete', 'delete', '', false, 0);
                                ?>
                            </a>
                        </span>
                    <?php } ?>
                </form>
                <?php
                echo (isset($childs[$i]) ? $childs[$i]->getNomUrl(1) : '');
                ?>
            </td>
        </tr>
    <?php } ?>

</table>


<?php
llxFooter();
$db->close();
