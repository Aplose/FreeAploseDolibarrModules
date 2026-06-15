<?php

// Angular SPA posts JSON without Dolibarr form token; session + per-endpoint rights still apply.
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', 1);
}

$res = 0;
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
	$res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'].'/main.inc.php';
}
if (!$res && file_exists('../../main.inc.php')) {
	$res = @include '../../main.inc.php';
}
if (!$res && file_exists('../../../main.inc.php')) {
	$res = @include '../../../main.inc.php';
}
if (!$res) {
	http_response_code(500);
	header('Content-Type: application/json');
	echo json_encode(array('error' => 'MAIN_INC_LOAD_FAILED'));
	exit;
}

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($user) || (int) $user->id <= 0) {
	http_response_code(401);
	echo json_encode(array('error' => 'UNAUTHORIZED'));
	exit;
}

function dc_json($payload, $status = 200)
{
	http_response_code($status);
	echo json_encode($payload);
	exit;
}

