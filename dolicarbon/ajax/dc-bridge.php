<?php

require_once __DIR__.'/_bootstrap.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

$endpoint = isset($_REQUEST['endpoint']) ? trim((string) $_REQUEST['endpoint']) : '';
$method = strtoupper(isset($_REQUEST['method']) ? (string) $_REQUEST['method'] : $_SERVER['REQUEST_METHOD']);
$body = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($body)) {
	$body = array();
}

if ($endpoint === '') {
	dc_json(array('error' => 'MISSING_ENDPOINT'), 400);
}

if ($endpoint === 'config') {
	dc_json(array(
		'dolMainUrlRoot' => DOL_URL_ROOT,
		'entity' => (int) $conf->entity,
		'user' => array('id' => (int) $user->id, 'admin' => (int) !empty($user->admin)),
		'rights' => array(
			'validate' => $user->hasRight('dolicarbon', 'validate') ? 1 : 0,
		),
		'communicationDisclaimer' => 'DoliCarbon est un outil d’aide à la comptabilité carbone aligné méthodologiquement sur les bonnes pratiques Bilan Carbone. Il ne constitue pas une reconnaissance « Bilan Carbone Conform » par l’ABC sans audit externe réussi. Toute communication publique doit respecter ces limites.',
	));
}

if ($endpoint === 'session/active-bilan' && $method === 'GET') {
	dc_json(array('activeBilanId' => !empty($_SESSION['dolicarbon_active_bilan']) ? (int) $_SESSION['dolicarbon_active_bilan'] : 0));
}

if ($endpoint === 'session/active-bilan' && in_array($method, array('PUT', 'POST'), true)) {
	$_SESSION['dolicarbon_active_bilan'] = (int) ($body['id'] ?? 0);
	dc_json(array('success' => true));
}

dc_json(array('error' => 'UNKNOWN_ENDPOINT'), 404);

