<?php
/* Copyright (C) 2001-2005  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2015       Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2025		Florian TOCCO           <ftocco@aplose.fr>
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
    $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
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
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
    $res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
    $res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
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

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array("dolilinks@dolilinks"));

$action = GETPOST('action', 'aZ09');

$now = dol_now();
$max = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT', 5);

// Security check - Protection if external user
$socid = GETPOSTINT('socid');
if (!empty($user->socid) && $user->socid > 0) {
    $action = '';
    $socid = $user->socid;
}

dol_include_once('/custom/dolilinks/class/SocieteLink.class.php');
dol_include_once('/custom/dolilinks/class/LinkType.class.php');

$societeLink = new SocieteLink($db);
$linkType = new LinkType($db);
$currentSociete = new Societe($db);

if ($currentSociete->fetch($socid) <= 0) {
    throw new Exception("Fail to fecth Societe id " . $socid . ". Error: " . $currentSociete->error);
}

$parents = $societeLink->getParents($currentSociete->id);
$childs = $societeLink->getChilds($currentSociete->id);

$allreadyUsedIds = [$currentSociete->id];

// Ajouter les parents direct
foreach ($parents as $parent) {
    if (!in_array($parent->id, $allreadyUsedIds)) {

        $allreadyUsedIds[] = $parent->id;
        $linkTypeId = $societeLink->getLinkTypeId($parent->id, $currentSociete->id);
        if ($linkTypeId > 0) {
            $linkType->fetch($linkTypeId);
        }
        $nodes[] = [
            'id' => $parent->id,
            'label' => $parent->name,
            'url' => dol_buildpath('/societe/card.php?id=' . $parent->id, 1),
            'color' => [
                'background' => 'lightgrey',
                'border' => 'lightblue'
            ]
        ];
        $edges[] = [
            'from' => $parent->id,
            'to'   => $currentSociete->id,
            'label' => isset($linkTypeId) && $linkType->active ? $linkType->label : '',
            'color' => 'lightblue',

        ];
    }
}

// Ajouter le currentSociete
$nodes[] = [
    'id' => $currentSociete->id,
    'label' => $currentSociete->name,
    'url' => dol_buildpath('/societe/card.php?id=' . $currentSociete->id, 1),
    'color' => [
        'background' => 'lightgreen',
        'border' => 'lightblue'
    ]
];

// Ajouter les enfants
foreach ($childs as $child) {
    if (!in_array($child->id, $allreadyUsedIds)) {

        $allreadyUsedIds[] = $child->id;
        $linkTypeId = $societeLink->getLinkTypeId($currentSociete->id, $child->id);
        if ($linkTypeId > 0) {
            $linkType->fetch($linkTypeId);
        }
        $nodes[] = [
            'id' => $child->id,
            'label' => $child->name,
            'url' => dol_buildpath('/societe/card.php?id=' . $child->id, 1)
        ];
        $edges[] = [
            'from' => $currentSociete->id,
            'to'   => $child->id,
            'label' => isset($linkTypeId) && $linkType->active ? $linkType->label : '',
            'color' => 'lightblue'
        ];
    }
}

// Ajouter les enfants des enfants
foreach ($childs as $child) {
    foreach ($societeLink->getChilds($child->id) as $subChild) {
        if (!in_array($subChild->id, $allreadyUsedIds)) {
            $allreadyUsedIds[] = $subChild->id;
            $linkTypeId = $societeLink->getLinkTypeId($child->id, $subChild->id);
            if ($linkTypeId > 0) {
                $linkType->fetch($linkTypeId);
            }
            $nodes[] = [
                'id' => $subChild->id,
                'label' => $subChild->name,
                'url' => dol_buildpath('/societe/card.php?id=' . $subChild->id, 1)
            ];
            $edges[] = [
                'from' => $child->id,
                'to'   => $subChild->id,
                'label' => isset($linkTypeId) && $linkType->active ? $linkType->label : '',
                'color' => 'lightblue'
            ];
        } else {
            $id = $subChild->id + 1;
            while (in_array($id, $allreadyUsedIds)) {
                $id++;
            }
            $allreadyUsedIds[] = $id;
            $linkTypeId = $societeLink->getLinkTypeId($child->id, $subChild->id);
            if ($linkTypeId > 0) {
                $linkType->fetch($linkTypeId);
            }
            $nodes[] = [
                'id' => $id,
                'label' => $subChild->name,
                'url' => dol_buildpath('/societe/card.php?id=' . $subChild->id, 1)
            ];
            $edges[] = [
                'from' => $child->id,
                'to'   => $id,
                'label' => isset($linkTypeId) && $linkType->active ? $linkType->label : '',
                'color' => 'lightblue'
            ];
        }
    }
}

llxHeader();
?>

<div style="display: flex;justify-content: space-between;">

    <a class="" href="<?php print dol_buildpath('/societe/card.php?socid=' . $socid, 1); ?>">
        <i class="fa fa-arrow-left"></i> <?php print $langs->trans("GoBack"); ?>
    </a>
    
    
    <!-- Légende -->
    <div id="legend" style="display: flex;justify-content: end;">
        <div style="margin-left: 20px" ;><span style="margin-right: 5px;background-color: lightgrey;height: 10px;width: 10px;display: inline-block;"> </span> <?php print $langs->trans("Parents"); ?></div>
        <div style="margin-left: 20px" ;><span style="margin-right: 5px;background-color: lightgreen;height: 10px;width: 10px;display: inline-block;"> </span><?php print $langs->trans("CurrentSociete"); ?></div>
        <div style="margin-left: 20px" ;><span style="margin-right: 5px;background-color: lightblue;height: 10px;width: 10px;display: inline-block;"> </span><?php print $langs->trans("Childs"); ?> </div>
    </div>
    
</div>

<div id="network" style="height:90vh; border:1px solid #ccc;">
    <!-- Diagramme injected here -->
</div>



<!-- <script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script> -->

<script src="<?php print dol_buildpath('/custom/dolilinks/js/vis-network.js.php', 1); ?>"></script>
<script>
    var nodes = new vis.DataSet(<?php print json_encode($nodes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>);
    var edges = new vis.DataSet(<?php print json_encode($edges, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>);

    console.log('nodes', nodes.data);
    var container = document.getElementById('network');
    var data = {
        nodes: nodes,
        edges: edges
    };
    var options = {
        interaction: {
            hover: true
        },
        layout: {
            hierarchical: {
                enabled: true,
                direction: 'UD',
                sortMethod: 'directed',
                nodeSpacing: 300
            }

        },
        physics: false,
        nodes: {
            shape: 'box',
            font: {
                size: 22
            }
        },
        // edges: {
        // 	arrows: 'to'
        // }
    };
    const network = new vis.Network(container, data, options);

    network.on('selectNode', (params) => {
        console.log('test');

        if (params.nodes.length > 0) {
            console.log('test 2');
            const node = nodes.get(params.nodes[0]);
            console.log('node', node);
            if (node.url) {
                console.log('test url');
                window.location.href = node.url;
            }
        }
    });
    network.on("hoverNode", function(params) {
        network.canvas.body.container.style.cursor = "pointer";
    });

    network.on("blurNode", function(params) {
        network.canvas.body.container.style.cursor = "default";
    });
</script>


<?php

llxFooter();
