<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    doliprospectform/ajax/recherche_entreprises.php
 * \ingroup doliprospectform
 * \brief   JSON proxy for api.gouv.fr Recherche d'entreprises (public professional form token)
 */

if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
if (!defined('NOLOGIN')) {
	define('NOLOGIN', 1);
}
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', 1);
}
if (!defined('NOIPCHECK')) {
	define('NOIPCHECK', '1');
}
if (!defined('NOBROWSERNOTIF')) {
	define('NOBROWSERNOTIF', '1');
}

$entity = 1;
if (!empty($_GET['e'])) {
	$entity = (int) $_GET['e'];
} elseif (!empty($_GET['entity'])) {
	$entity = (int) $_GET['entity'];
}
if ($entity <= 0) {
	$entity = 1;
}
define('DOLENTITY', $entity);

$res = 0;
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
	$res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'].'/main.inc.php';
}
if (!$res && file_exists(__DIR__.'/../../../main.inc.php')) {
	$res = @include __DIR__.'/../../../main.inc.php';
}
if (!$res) {
	http_response_code(500);
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode(array('ok' => false, 'error' => 'include'));
	exit;
}

dol_include_once('custom/doliprospectform/lib/doliprospectform_publicform.lib.php');
dol_include_once('custom/doliprospectform/lib/doliprospectform_recherche_entreprises.lib.php');

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Translate $langs
 */

header('Content-Type: application/json; charset=UTF-8');

if (!isModEnabled('doliprospectform')) {
	http_response_code(403);
	echo json_encode(array('ok' => false, 'error' => 'module'));
	exit;
}

$langs->loadLangs(array('doliprospectform@doliprospectform'));

$token = GETPOST('t', 'aZ09', 0, null, null, 1);
$payload = doliprospectform_publicform_verify_token($db, $token);
if ($payload === false || empty($payload['f']) || $payload['f'] !== 'professional') {
	http_response_code(403);
	echo json_encode(array('ok' => false, 'error' => 'token'));
	exit;
}
if ((int) $payload['e'] !== (int) $entity) {
	http_response_code(403);
	echo json_encode(array('ok' => false, 'error' => 'entity'));
	exit;
}

$q = trim((string) GETPOST('q', 'nohtml', 1));
if ($q === '') {
	echo json_encode(array('ok' => true, 'results' => array()));
	exit;
}
if (dol_strlen($q) > 200) {
	$q = dol_substr($q, 0, 200);
}

$rows = doliprospectform_recherche_entreprises_search($q, 15);
if (isset($rows['error'])) {
	http_response_code(502);
	echo json_encode(array(
		'ok' => false,
		'error' => 'api',
		'message' => $langs->trans('DoliProspectFormCompanySearchApiError'),
	));
	exit;
}

echo json_encode(array('ok' => true, 'results' => $rows));
$db->close();
exit;
