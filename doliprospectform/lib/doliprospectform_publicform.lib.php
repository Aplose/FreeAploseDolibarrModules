<?php
/* Copyright (C) 2026		Olivier ANDRADE SANCHEZ		<oandrade@aplose.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    doliprospectform/lib/doliprospectform_publicform.lib.php
 * \ingroup doliprospectform
 * \brief   Signed public form links (DoliProspectForm)
 */

/**
 * CSS entries for llxHeader on anonymous/public forms (Bootstrap 5 + icons + module skin).
 *
 * @return array<int,string>
 */
function doliprospectform_public_llx_css()
{
	return array(
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
		'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
		'/custom/doliprospectform/css/doliprospectform_public.css',
	);
}

/**
 * JS entries for llxHeader on public forms (Bootstrap bundle).
 *
 * @return array<int,string>
 */
function doliprospectform_public_llx_js()
{
	return array(
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
	);
}

/**
 * Base64url encode
 *
 * @param string $data Raw string
 * @return string
 */
function doliprospectform_base64url_encode($data)
{
	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64url decode
 *
 * @param string $data Encoded string
 * @return string|false
 */
function doliprospectform_base64url_decode($data)
{
	$pad = strlen($data) % 4;
	if ($pad) {
		$data .= str_repeat('=', 4 - $pad);
	}
	return base64_decode(strtr($data, '-_', '+/'), true);
}

/**
 * Country rowid (llx_c_country.rowid) of the main Dolibarr company for public form defaults.
 * Prefer mysoc->country_id; if missing, resolve from mysoc->country_code (e.g. incomplete MAIN_INFO_SOCIETE_COUNTRY).
 *
 * @param DoliDB           $db    Database handler
 * @param Societe|stdClass $mysoc Main company object
 * @return int                      c_country rowid or 0
 */
function doliprospectform_publicform_get_main_company_country_id($db, $mysoc)
{
	if (!is_object($mysoc)) {
		return 0;
	}
	$id = isset($mysoc->country_id) ? (int) $mysoc->country_id : 0;
	if ($id > 0) {
		return $id;
	}
	$code = '';
	if (!empty($mysoc->country_code)) {
		$code = trim((string) $mysoc->country_code);
	}
	if ($code !== '') {
		$fromCode = (int) dol_getIdFromCode($db, $code, 'c_country', 'code', 'rowid');
		if ($fromCode > 0) {
			return $fromCode;
		}
	}
	return 0;
}

/**
 * Build signed token for a public form link
 *
 * @param DoliDB $db           Database handler
 * @param int    $entity       Entity id
 * @param int    $commercialId Dolibarr user id (sales representative)
 * @param string $formCode     Internal form code (e.g. individual)
 * @param int    $socid        Third party id to update (0 = none, create on submit)
 * @param bool   $noExpiry     If true, omit exp (permanent until secret change). Only allowed for hub + commercial 0 + socid 0 (public embed URL).
 * @return string|false        Token or false if secret missing
 */
function doliprospectform_publicform_build_token($db, $entity, $commercialId, $formCode, $socid = 0, $noExpiry = false)
{
	global $conf;

	$secret = getDolGlobalString('DOLIPROSPECTFORM_PUBLIC_FORM_SECRET');
	if ($secret === '') {
		return false;
	}

	$payload = array(
		'v' => 1,
		'e' => (int) $entity,
		'u' => (int) $commercialId,
		'f' => (string) $formCode,
	);
	$allowNoExpiry = $noExpiry && (int) $commercialId === 0 && (string) $formCode === 'hub' && (int) $socid === 0;
	if (!$allowNoExpiry) {
		$validityDays = max(1, (int) getDolGlobalString('DOLIPROSPECTFORM_TOKEN_VALIDITY_DAYS', '90'));
		$payload['exp'] = dol_now() + ($validityDays * 86400);
	}
	if ($socid > 0) {
		$payload['s'] = (int) $socid;
	}
	$payloadJson = json_encode($payload);
	if ($payloadJson === false) {
		return false;
	}
	$sig = hash_hmac('sha256', $payloadJson, $secret, true);
	return doliprospectform_base64url_encode($payloadJson).'.'.doliprospectform_base64url_encode($sig);
}

/**
 * Verify token and return payload array or false
 *
 * @param DoliDB $db    Database handler
 * @param string $token Signed token
 * @return array<string,int|string>|false
 */
function doliprospectform_publicform_verify_token($db, $token)
{
	global $conf;

	$secret = getDolGlobalString('DOLIPROSPECTFORM_PUBLIC_FORM_SECRET');
	if ($secret === '' || $token === '') {
		return false;
	}

	$parts = explode('.', $token, 2);
	if (count($parts) !== 2) {
		return false;
	}
	$payloadJson = doliprospectform_base64url_decode($parts[0]);
	$sigBin = doliprospectform_base64url_decode($parts[1]);
	if ($payloadJson === false || $sigBin === false) {
		return false;
	}
	$expected = hash_hmac('sha256', $payloadJson, $secret, true);
	if (!hash_equals($expected, $sigBin)) {
		return false;
	}
	$payload = json_decode($payloadJson, true);
	if (!is_array($payload) || empty($payload['v']) || (int) $payload['v'] !== 1) {
		return false;
	}
	// Expiry: if "exp" absent, token does not expire by time (hub public embed only).
	if (!empty($payload['exp']) && (int) $payload['exp'] < dol_now()) {
		return false;
	}
	return $payload;
}

/**
 * Build absolute public URL for the individual (particulier) form
 *
 * @param DoliDB $db           Database handler
 * @param int    $entity       Entity id
 * @param int    $commercialId User id encoded in the link
 * @param int    $socid        Third party id to bind (0 = anonymous create flow)
 * @return string             Full URL or empty string if token cannot be built
 */
function doliprospectform_publicform_url_individual($db, $entity, $commercialId, $socid = 0)
{
	$tok = doliprospectform_publicform_build_token($db, $entity, $commercialId, 'individual', $socid);
	if ($tok === false) {
		return '';
	}
	$path = 'custom/doliprospectform/public/form_particulier.php';
	return dol_buildpath($path, 2).'?e='.((int) $entity).'&t='.urlencode($tok);
}

/**
 * Build absolute public URL for the professional (company) form
 *
 * @param DoliDB $db             Database handler
 * @param int    $entity         Entity id
 * @param int    $commercialId   User id encoded in the link
 * @param int    $socid          Third party id to bind (0 = anonymous create flow)
 * @return string                 Full URL or empty string if token cannot be built
 */
function doliprospectform_publicform_url_professional($db, $entity, $commercialId, $socid = 0)
{
	$tok = doliprospectform_publicform_build_token($db, $entity, $commercialId, 'professional', $socid);
	if ($tok === false) {
		return '';
	}
	$path = 'custom/doliprospectform/public/form_professionnel.php';
	return dol_buildpath($path, 2).'?e='.((int) $entity).'&t='.urlencode($tok);
}

/**
 * Build absolute public URL for the hub (individual / professional choice)
 *
 * @param DoliDB $db             Database handler
 * @param int    $entity         Entity id
 * @param int    $commercialId   User id encoded in the link (0 = unknown sender, optional consultant email on hub)
 * @param int    $socid          Third party id to bind (0 = anonymous)
 * @param bool   $noExpiry       Hub + u=0 + soc=0 only: token without exp (stable embed; invalid if secret is rotated)
 * @return string                 Full URL or empty string if token cannot be built
 */
function doliprospectform_publicform_url_hub($db, $entity, $commercialId, $socid = 0, $noExpiry = false)
{
	$tok = doliprospectform_publicform_build_token($db, $entity, $commercialId, 'hub', $socid, $noExpiry);
	if ($tok === false) {
		return '';
	}
	$path = 'custom/doliprospectform/public/form_aiguillage.php';
	return dol_buildpath($path, 2).'?e='.((int) $entity).'&t='.urlencode($tok);
}

/**
 * Dolibarr user used for technical create/update on public forms (when assigned commercial may differ).
 * Configure DOLIPROSPECTFORM_PUBLIC_DEFAULT_USER, else first active user of the entity is used.
 *
 * @param DoliDB $db      Database handler
 * @param int    $entity  Entity id
 * @return User|null
 */
function doliprospectform_publicform_get_public_form_actor_user($db, $entity)
{
	if (!class_exists('User')) {
		require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
	}
	$uid = getDolGlobalInt('DOLIPROSPECTFORM_PUBLIC_DEFAULT_USER', 0);
	$u = new User($db);
	if ($uid > 0) {
		if ($u->fetch($uid, '', '', 0, $entity) > 0) {
			$st = isset($u->status) ? (int) $u->status : (int) $u->statut;
			if ($st === 1) {
				return $u;
			}
		}
	}
	$sql = "SELECT u.rowid FROM ".$db->prefix()."user as u";
	$sql .= " WHERE u.statut = 1 AND u.entity IN (0, ".((int) $entity).")";
	$sql .= " ORDER BY u.admin DESC, u.entity DESC, u.rowid ASC";
	$sql .= $db->plimit(1);
	$resql = $db->query($sql);
	if ($resql) {
		$obj = $db->fetch_object($resql);
		if ($obj && (int) $obj->rowid > 0 && $u->fetch((int) $obj->rowid, '', '', 0, $entity) > 0) {
			return $u;
		}
	}
	return null;
}

/**
 * Find first active internal user id by e-mail (entity scope).
 *
 * @param DoliDB $db      Database handler
 * @param int    $entity  Entity id
 * @param string $email   E-mail to search
 * @return int            User id or 0 if not found / duplicate / inactive
 */
function doliprospectform_publicform_find_user_id_by_email($db, $entity, $email)
{
	if (!class_exists('User')) {
		require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
	}
	$email = trim((string) $email);
	if ($email === '' || !isValidEmail($email)) {
		return 0;
	}
	$tmp = new User($db);
	$res = $tmp->fetch(0, '', '', 0, $entity, $email);
	if ($res <= 0) {
		if ($tmp->error === 'USERDUPLICATEFOUND') {
			dol_syslog('doliprospectform_publicform_find_user_id_by_email duplicate email='.$email, LOG_WARNING);
		}
		return 0;
	}
	$st = isset($tmp->status) ? (int) $tmp->status : (int) $tmp->statut;
	if ($st !== 1) {
		return 0;
	}
	$ue = (int) $tmp->entity;
	if ($ue !== 0 && $ue !== (int) $entity) {
		return 0;
	}
	return (int) $tmp->id;
}

/**
 * Resolve assigned commercial id for hub → form redirect (token u if valid, else consultant e-mail).
 *
 * @param DoliDB $db               Database handler
 * @param int    $entity           Entity id
 * @param array<string,int|string> $payload Verified hub token payload
 * @param string $consultantEmail Optional consultant e-mail from hub form
 * @return int                     User id to store as third party commercial (0 if unknown)
 */
function doliprospectform_publicform_hub_resolve_assigned_commercial_id($db, $entity, $payload, $consultantEmail)
{
	if (!class_exists('User')) {
		require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
	}
	$u = isset($payload['u']) ? (int) $payload['u'] : 0;
	if ($u > 0) {
		$usr = new User($db);
		if ($usr->fetch($u, '', '', 0, $entity) > 0) {
			$st = isset($usr->status) ? (int) $usr->status : (int) $usr->statut;
			$ue = (int) $usr->entity;
			if ($st === 1 && ($ue === 0 || $ue === (int) $entity)) {
				return $u;
			}
		}
	}
	return doliprospectform_publicform_find_user_id_by_email($db, $entity, $consultantEmail);
}

/**
 * $_FILES key for public form PDF inputs (HTML: name="doliprospectform_pdfs[]")
 *
 * @return string
 */
function doliprospectform_publicform_pdf_collection_field()
{
	return 'doliprospectform_pdfs';
}

/**
 * Print one PDF file row (custom button label from Dolibarr "AddFile", same for every row).
 *
 * @param Translate $langs    Language instance
 * @param string    $inputId  HTML id for the file input (empty for dynamically added rows)
 * @param bool      $multiple Whether the input accepts multiple files at once
 * @return void
 */
function doliprospectform_publicform_print_pdf_file_row($langs, $inputId = '', $multiple = false)
{
	$btn = dol_escape_htmltag($langs->trans('AddFile'));
	$idAttr = ($inputId !== '') ? ' id="'.dol_escape_htmltag($inputId).'"' : '';
	$multAttr = $multiple ? ' multiple' : '';
	print '<div class="dpf-file-row mb-2 dpf-file-input-wrap">';
	print '<label class="dpf-btn-file">';
	print '<input type="file" name="doliprospectform_pdfs[]" class="dpf-file-input-native" accept="application/pdf,.pdf"'.$idAttr.$multAttr.'>';
	print '<span class="dpf-btn-file-text">'.$btn.'</span>';
	print '</label>';
	print '<span class="dpf-file-chosen" aria-live="polite"></span>';
	print '</div>';
}

/**
 * Inline script: bind PDF rows and "add field" button (uses Dolibarr AddFile label for new rows).
 *
 * @param Translate $langs Language instance
 * @return void
 */
function doliprospectform_publicform_print_pdf_inputs_script($langs)
{
	$labelJs = json_encode($langs->trans('AddFile'));
	print '<script>
(function(){
  var dpfAddFileLabel = '.$labelJs.';
  function dpfBindPdfRow(wrap) {
    var inp = wrap.querySelector("input.dpf-file-input-native");
    var out = wrap.querySelector(".dpf-file-chosen");
    if (!inp || !out) {
      return;
    }
    inp.addEventListener("change", function () {
      if (!inp.files || !inp.files.length) {
        out.textContent = "";
        return;
      }
      var names = [];
      for (var i = 0; i < inp.files.length; i++) {
        names.push(inp.files[i].name);
      }
      out.textContent = names.join(", ");
    });
  }
  var wrapRoot = document.getElementById("dpf-pdf-wrap");
  if (wrapRoot) {
    wrapRoot.querySelectorAll(".dpf-file-input-wrap").forEach(dpfBindPdfRow);
  }
  var addBtn = document.getElementById("dpf_add_pdf_input");
  if (addBtn && wrapRoot) {
    addBtn.addEventListener("click", function () {
      var row = document.createElement("div");
      row.className = "dpf-file-row mb-2 dpf-file-input-wrap";
      var lab = document.createElement("label");
      lab.className = "dpf-btn-file";
      var inp = document.createElement("input");
      inp.type = "file";
      inp.name = "doliprospectform_pdfs[]";
      inp.className = "dpf-file-input-native";
      inp.accept = "application/pdf,.pdf";
      var tx = document.createElement("span");
      tx.className = "dpf-btn-file-text";
      tx.textContent = dpfAddFileLabel;
      var chosen = document.createElement("span");
      chosen.className = "dpf-file-chosen";
      chosen.setAttribute("aria-live", "polite");
      lab.appendChild(inp);
      lab.appendChild(tx);
      row.appendChild(lab);
      row.appendChild(chosen);
      wrapRoot.appendChild(row);
      dpfBindPdfRow(row);
    });
  }
})();
</script>';
}

/**
 * Default public form texts stored in llx_const (French wording, same as langs/fr_FR/doliprospectform.lang).
 * Intentionally literal copy: not translation keys. Admins may edit in module setup.
 *
 * @return array<string,string>
 */
function doliprospectform_publicform_get_default_public_form_texts()
{
	return array(
		'DOLIPROSPECTFORM_FORM_HUB_TITLE' => 'DoliProspectForm — votre demande',
		'DOLIPROSPECTFORM_FORM_HUB_INTRO' => 'Choisissez le type de formulaire adapté à votre situation.',
		'DOLIPROSPECTFORM_FORM_HUB_CONSULTANT_BLOCK_TITLE' => 'E-mail de mon consultant DoliProspectForm',
		'DOLIPROSPECTFORM_FORM_HUB_CONSULTANT_BLOCK_SUBTITLE' => 'Optionnel. Utilisé uniquement si le lien ne permet pas d\'identifier automatiquement votre commercial. L\'e-mail doit correspondre à l\'e-mail d\'un utilisateur Dolibarr actif sur cette entité.',
		'DOLIPROSPECTFORM_FORM_INDIV_TITLE' => 'Votre demande — particulier',
		'DOLIPROSPECTFORM_FORM_INDIV_INTRO' => 'Merci de compléter vos coordonnées et de joindre au moins un document au format PDF (vous pouvez en sélectionner plusieurs : factures électricité, gaz, etc.). __DOLIPROSPECTFORM_PUBLIC_SALES_REP__',
		'DOLIPROSPECTFORM_FORM_INDIV_DOC_TITLE' => 'Documents',
		'DOLIPROSPECTFORM_FORM_INDIV_DOC_HINT' => 'Sélectionnez une ou plusieurs pièces (touche Ctrl ou Maj dans la boîte de dialogue). Utilisez le bouton ci-dessous pour ajouter une autre zone d\'envoi si besoin.',
		'DOLIPROSPECTFORM_FORM_PRO_TITLE' => 'Votre demande — professionnel',
		'DOLIPROSPECTFORM_FORM_PRO_INTRO' => 'Recherchez votre société (raison sociale ou SIRET), sélectionnez le bon résultat, complétez le contact et joignez les pièces PDF souhaitées (factures électricité, gaz, Kbis, etc. — plusieurs fichiers possibles). __DOLIPROSPECTFORM_PUBLIC_SALES_REP__',
		'DOLIPROSPECTFORM_FORM_PRO_DOC_TITLE' => 'Documents',
		'DOLIPROSPECTFORM_FORM_PRO_DOC_HINT' => 'Sélectionnez une ou plusieurs pièces (touche Ctrl ou Maj dans la boîte de dialogue). Utilisez le bouton ci-dessous pour ajouter une autre zone d\'envoi si besoin.',
	);
}

/**
 * Insert default public form text constants (per entity) if not already set. Safe to call from module init or admin.
 *
 * @param DoliDB $db Database handler
 * @return void
 */
function doliprospectform_publicform_ensure_default_text_consts($db)
{
	global $conf;

	if (!is_object($db) || !is_object($conf)) {
		return;
	}

	foreach (doliprospectform_publicform_get_default_public_form_texts() as $name => $value) {
		if ($value === '' || $value === null) {
			continue;
		}
		$cur = getDolGlobalString($name);
		if ($cur !== null && $cur !== '') {
			continue;
		}
		dolibarr_set_const($db, $name, $value, 'chaine', 0, 'DoliProspectForm public form', $conf->entity);
		$conf->global->{$name} = $value;
	}

	doliprospectform_publicform_ensure_public_form_enable_consts($db);
}

/**
 * Create public form on/off constants (default on) if missing. Safe to call from module init or admin.
 *
 * @param DoliDB $db Database handler
 * @return void
 */
function doliprospectform_publicform_ensure_public_form_enable_consts($db)
{
	global $conf;

	if (!is_object($db) || !is_object($conf)) {
		return;
	}

	foreach (array('DOLIPROSPECTFORM_PUBLIC_FORM_INDIVIDUAL', 'DOLIPROSPECTFORM_PUBLIC_FORM_PROFESSIONAL') as $name) {
		if (!isset($conf->global->{$name})) {
			if (dolibarr_set_const($db, $name, '1', 'chaine', 0, 'DoliProspectForm public form availability', $conf->entity) > 0) {
				$conf->global->{$name} = '1';
			}
		}
	}
}

/**
 * Whether the public individual form is enabled (module setup, default on).
 *
 * @return bool
 */
function doliprospectform_publicform_is_public_individual_enabled()
{
	return (bool) getDolGlobalInt('DOLIPROSPECTFORM_PUBLIC_FORM_INDIVIDUAL', 1);
}

/**
 * Whether the public professional form is enabled (module setup, default on).
 *
 * @return bool
 */
function doliprospectform_publicform_is_public_professional_enabled()
{
	return (bool) getDolGlobalInt('DOLIPROSPECTFORM_PUBLIC_FORM_PROFESSIONAL', 1);
}

/**
 * Replace sales-rep placeholder in a public form text (intro).
 *
 * @param DoliDB    $db                    Database handler
 * @param Translate $langs                 Language instance
 * @param int       $entity                Entity id
 * @param int       $assignedCommercialId  User id from signed token (0 = unknown)
 * @param string    $text                  Raw text (may contain __DOLIPROSPECTFORM_PUBLIC_SALES_REP__)
 * @return string                          Plain text with placeholder resolved
 */
function doliprospectform_publicform_replace_sales_rep_placeholder($db, $langs, $entity, $assignedCommercialId, $text)
{
	$ph = '__DOLIPROSPECTFORM_PUBLIC_SALES_REP__';
	if (strpos((string) $text, $ph) === false) {
		return (string) $text;
	}

	$repPhrase = '';
	if ((int) $assignedCommercialId > 0) {
		if (!class_exists('User')) {
			require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
		}
		$u = new User($db);
		if ($u->fetch((int) $assignedCommercialId, '', '', 0, (int) $entity) > 0) {
			$fn = trim((string) $u->firstname);
			$ln = trim((string) $u->lastname);
			$disp = trim($fn.' '.$ln);
			if ($disp === '') {
				$disp = trim((string) $u->login);
			}
			if ($disp !== '') {
				$known = $langs->trans('DoliProspectFormPublicFormSalesHandoffKnown', $disp);
				$repPhrase = ($known !== 'DoliProspectFormPublicFormSalesHandoffKnown') ? $known : sprintf($langs->transnoentitiesnoconv('DoliProspectFormPublicFormSalesHandoffKnown'), $disp);
			}
		}
	}

	if ($repPhrase === '') {
		$unk = $langs->trans('DoliProspectFormPublicFormSalesHandoffUnknown');
		$repPhrase = ($unk !== 'DoliProspectFormPublicFormSalesHandoffUnknown') ? $unk : $langs->transnoentitiesnoconv('DoliProspectFormPublicFormSalesHandoffUnknown');
	}

	return str_replace($ph, $repPhrase, (string) $text);
}

/**
 * Hub (routing) page title from setup (fallback: translation key).
 *
 * @param Translate $langs Language instance
 * @return string
 */
function doliprospectform_publicform_get_hub_title($langs)
{
	$v = trim((string) getDolGlobalString('DOLIPROSPECTFORM_FORM_HUB_TITLE'));
	// DB may mistakenly store the translation key string instead of the label.
	if ($v === '' || $v === 'DoliProspectFormPublicHubTitle') {
		return $langs->trans('DoliProspectFormPublicHubTitle');
	}
	return $v;
}

/**
 * Hub (routing) page intro: constant + sales-rep placeholder, HTML-escaped.
 *
 * @param DoliDB    $db                    Database handler
 * @param Translate $langs                 Language instance
 * @param int       $entity                Entity id
 * @param int       $assignedCommercialId  User id from token
 * @return string                          Escaped HTML-safe text
 */
function doliprospectform_publicform_get_resolved_hub_intro_html($db, $langs, $entity, $assignedCommercialId)
{
	$raw = trim((string) getDolGlobalString('DOLIPROSPECTFORM_FORM_HUB_INTRO'));
	if ($raw === '' || $raw === 'DoliProspectFormPublicHubIntro') {
		$raw = $langs->trans('DoliProspectFormPublicHubIntro');
	}
	$plain = doliprospectform_publicform_replace_sales_rep_placeholder($db, $langs, $entity, $assignedCommercialId, $raw);
	return dol_escape_htmltag($plain);
}

/**
 * Hub page: title above the optional consultant e-mail field (constant or fallback translation).
 *
 * @param Translate $langs Language instance
 * @return string
 */
function doliprospectform_publicform_get_hub_consultant_block_title($langs)
{
	$v = trim((string) getDolGlobalString('DOLIPROSPECTFORM_FORM_HUB_CONSULTANT_BLOCK_TITLE'));
	if ($v === '') {
		return $langs->trans('DoliProspectFormPublicHubConsultantEmail');
	}
	return $v;
}

/**
 * Hub page: subtitle / help under consultant block title (constant + sales-rep tag, HTML-escaped).
 *
 * @param DoliDB    $db                    Database handler
 * @param Translate $langs                 Language instance
 * @param int       $entity                Entity id
 * @param int       $assignedCommercialId  User id from token
 * @return string                          Escaped HTML-safe text
 */
function doliprospectform_publicform_get_hub_consultant_block_subtitle_html($db, $langs, $entity, $assignedCommercialId)
{
	$raw = trim((string) getDolGlobalString('DOLIPROSPECTFORM_FORM_HUB_CONSULTANT_BLOCK_SUBTITLE'));
	if ($raw === '') {
		$raw = $langs->trans('DoliProspectFormPublicHubConsultantEmailHelp');
	}
	$plain = doliprospectform_publicform_replace_sales_rep_placeholder($db, $langs, $entity, $assignedCommercialId, $raw);
	return dol_escape_htmltag($plain);
}

/**
 * Public form page title from setup (fallback: translation key).
 *
 * @param Translate $langs Language instance
 * @param string    $which  "individual" or "professional"
 * @return string
 */
function doliprospectform_publicform_get_form_title($langs, $which)
{
	$key = ($which === 'professional') ? 'DOLIPROSPECTFORM_FORM_PRO_TITLE' : 'DOLIPROSPECTFORM_FORM_INDIV_TITLE';
	$fb = ($which === 'professional') ? 'DoliProspectFormPublicFormProfessionnelTitle' : 'DoliProspectFormPublicFormParticulierTitle';
	$v = getDolGlobalString($key);
	return ($v !== '' && $v !== null) ? $v : $langs->trans($fb);
}

/**
 * Documents block title from setup.
 *
 * @param Translate $langs Language instance
 * @param string    $which "individual" or "professional"
 * @return string
 */
function doliprospectform_publicform_get_doc_section_title($langs, $which)
{
	$key = ($which === 'professional') ? 'DOLIPROSPECTFORM_FORM_PRO_DOC_TITLE' : 'DOLIPROSPECTFORM_FORM_INDIV_DOC_TITLE';
	$fb = 'DoliProspectFormPublicSectionDocuments';
	$v = getDolGlobalString($key);
	return ($v !== '' && $v !== null) ? $v : $langs->trans($fb);
}

/**
 * Documents block hint from setup.
 *
 * @param Translate $langs Language instance
 * @param string    $which "individual" or "professional"
 * @return string
 */
function doliprospectform_publicform_get_doc_section_hint($langs, $which)
{
	$key = ($which === 'professional') ? 'DOLIPROSPECTFORM_FORM_PRO_DOC_HINT' : 'DOLIPROSPECTFORM_FORM_INDIV_DOC_HINT';
	$fb = 'DoliProspectFormPublicDocumentsPdfHint';
	$v = getDolGlobalString($key);
	return ($v !== '' && $v !== null) ? $v : $langs->trans($fb);
}

/**
 * Resolved intro / hero lead (constants + placeholder + HTML escape).
 *
 * @param DoliDB    $db                    Database handler
 * @param Translate $langs                 Language instance
 * @param int       $entity                Entity id
 * @param int       $assignedCommercialId  User id from token
 * @param string    $which                 "individual" or "professional"
 * @return string                          Escaped HTML-safe text
 */
function doliprospectform_publicform_get_resolved_intro_html($db, $langs, $entity, $assignedCommercialId, $which)
{
	$const = ($which === 'professional') ? 'DOLIPROSPECTFORM_FORM_PRO_INTRO' : 'DOLIPROSPECTFORM_FORM_INDIV_INTRO';
	$fbTpl = ($which === 'professional') ? 'DoliProspectFormPublicFormProfessionnelIntroDefaultTemplate' : 'DoliProspectFormPublicFormIntroDefaultTemplate';
	$fbLegacy = ($which === 'professional') ? 'DoliProspectFormPublicFormProfessionnelIntro' : 'DoliProspectFormPublicFormIntro';

	$raw = getDolGlobalString($const);
	if ($raw === '' || $raw === null) {
		$raw = $langs->trans($fbTpl);
		if ($raw === $fbTpl) {
			$raw = $langs->trans($fbLegacy);
		}
	}

	$plain = doliprospectform_publicform_replace_sales_rep_placeholder($db, $langs, $entity, $assignedCommercialId, $raw);
	return dol_escape_htmltag($plain);
}

/**
 * Normalize $_FILES[$fieldKey] into a list of file rows (handles name="field[]" with multiple files and several inputs).
 *
 * @param string $fieldKey Key in $_FILES (e.g. doliprospectform_pdfs for name="doliprospectform_pdfs[]")
 * @return array<int,array{name:string,type:string,tmp_name:string,error:int,size:int}>
 */
function doliprospectform_publicform_normalize_uploaded_files($fieldKey)
{
	if (empty($_FILES[$fieldKey])) {
		return array();
	}
	$f = $_FILES[$fieldKey];
	if (!isset($f['tmp_name'])) {
		return array();
	}
	if (!is_array($f['tmp_name'])) {
		if ((isset($f['error']) ? (int) $f['error'] : 0) === UPLOAD_ERR_NO_FILE) {
			return array();
		}
		if ($f['tmp_name'] === '' && empty($f['name'])) {
			return array();
		}
		return array(array(
			'name' => (string) $f['name'],
			'type' => (string) $f['type'],
			'tmp_name' => (string) $f['tmp_name'],
			'error' => (int) $f['error'],
			'size' => (int) $f['size'],
		));
	}
	$out = array();
	$n = count($f['tmp_name']);
	for ($i = 0; $i < $n; $i++) {
		$err = isset($f['error'][$i]) ? (int) $f['error'][$i] : UPLOAD_ERR_NO_FILE;
		if ($err === UPLOAD_ERR_NO_FILE) {
			continue;
		}
		$tmp = isset($f['tmp_name'][$i]) ? (string) $f['tmp_name'][$i] : '';
		$nm = isset($f['name'][$i]) ? (string) $f['name'][$i] : '';
		if ($tmp === '' && $nm === '') {
			continue;
		}
		$out[] = array(
			'name' => $nm,
			'type' => isset($f['type'][$i]) ? (string) $f['type'][$i] : '',
			'tmp_name' => $tmp,
			'error' => $err,
			'size' => isset($f['size'][$i]) ? (int) $f['size'][$i] : 0,
		);
	}
	return $out;
}

/**
 * Validate one uploaded file row as PDF (extension, size vs MAIN_UPLOAD_DOC, mime).
 *
 * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $row One file row
 * @param Translate $langs Language object
 * @param array<int,string> $errors Error messages (append)
 * @return bool True if this row is valid
 */
function doliprospectform_publicform_validate_pdf_slice($row, $langs, &$errors)
{
	$err = (int) $row['error'];
	if ($err !== UPLOAD_ERR_OK) {
		$fn = !empty($row['name']) ? $row['name'] : '?';
		$errors[] = $langs->trans('DoliProspectFormPublicUploadPhpErr', $fn);
		return false;
	}
	if (empty($row['tmp_name']) || !is_uploaded_file($row['tmp_name'])) {
		$errors[] = $langs->trans('DoliProspectFormPublicPdfInvalidFile', !empty($row['name']) ? $row['name'] : '?');
		return false;
	}
	$ext = strtolower(pathinfo($row['name'], PATHINFO_EXTENSION));
	if ($ext !== 'pdf') {
		$errors[] = $langs->trans('DoliProspectFormPublicPdfInvalidFile', $row['name']);
		return false;
	}
	// MAIN_UPLOAD_DOC is stored in Kb (Dolibarr admin — see getMaxFileSizeArray); PHP file size is in bytes.
	$maxKb = getDolGlobalInt('MAIN_UPLOAD_DOC', 0);
	if ($maxKb > 0 && !empty($row['size'])) {
		$maxBytes = $maxKb * 1024;
		if ((int) $row['size'] > $maxBytes) {
			$errors[] = $langs->trans('DoliProspectFormPublicPdfTooBig', $row['name']);
			return false;
		}
	}
	if (function_exists('mime_content_type')) {
		$mime = @mime_content_type($row['tmp_name']);
		if ($mime && stripos((string) $mime, 'pdf') === false) {
			$errors[] = $langs->trans('DoliProspectFormPublicPdfInvalidFile', $row['name']);
			return false;
		}
	}
	return true;
}

/**
 * Require at least one PDF and validate every file in the collection.
 *
 * @param string $fieldKey $_FILES key (see doliprospectform_publicform_pdf_collection_field)
 * @param Translate $langs Language object
 * @param array<int,string> $errors Error messages (append)
 * @param int $maxFiles Hard cap on number of files
 * @return bool True if the whole collection is valid
 */
function doliprospectform_publicform_validate_pdf_upload_collection($fieldKey, $langs, &$errors, $maxFiles = 30)
{
	$rows = doliprospectform_publicform_normalize_uploaded_files($fieldKey);
	if (empty($rows)) {
		$errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('DoliProspectFormPublicDocumentsPdf'));
		return false;
	}
	if (count($rows) > $maxFiles) {
		$errors[] = $langs->trans('DoliProspectFormPublicTooManyPdfFiles', $maxFiles);
		return false;
	}
	$ok = true;
	foreach ($rows as $row) {
		if (!doliprospectform_publicform_validate_pdf_slice($row, $langs, $errors)) {
			$ok = false;
		}
	}
	return $ok;
}

/**
 * Move validated PDF collection to a third-party document directory (ECM index).
 *
 * @param string $fieldKey $_FILES key
 * @param string $upload_dir Absolute directory (with trailing path handled by caller)
 * @param Societe $thirdparty Third party
 * @param Translate $langs Language object
 * @param array<int,string> $errors Error messages (append)
 * @return bool True if all files were moved
 */
function doliprospectform_publicform_save_pdf_collection_to_dir($fieldKey, $upload_dir, $thirdparty, $langs, &$errors)
{
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

	$rows = doliprospectform_publicform_normalize_uploaded_files($fieldKey);
	$idx = 0;
	foreach ($rows as $row) {
		$idx++;
		$originalName = dol_sanitizeFileName($row['name']);
		if (preg_match('/\.pdf$/i', $originalName) !== 1) {
			$originalName .= '.pdf';
		}
		$destName = 'dpf_'.str_pad((string) $idx, 3, '0', STR_PAD_LEFT).'_'.$originalName;
		$destfull = $upload_dir.'/'.$destName;
		$resUp = dol_move_uploaded_file($row['tmp_name'], $destfull, 1, 0, 0, 0, $fieldKey, $upload_dir);
		if ($resUp <= 0) {
			$errors[] = $langs->trans('DoliProspectFormPublicUploadFailed');
			return false;
		}
		addFileIntoDatabaseIndex($upload_dir, basename($destfull), $destName, 'uploaded', 0, $thirdparty, '');
	}
	return true;
}

/**
 * Build substitution tags for submission notification emails (__DOLIPROSPECTFORM_SUBMISSION_*__).
 *
 * @param DoliDB      $db                   Database handler
 * @param Translate   $outputlangs          Output language
 * @param int         $entity               Entity id
 * @param Societe     $thirdparty           Third party after submit
 * @param Contact     $contact              Created contact
 * @param int         $assignedCommercialId Commercial user id from token (0 = none)
 * @param string      $formType             individual|professional
 * @param int         $nbDocuments          Number of PDFs
 * @param int         $submissionRowid      llx_doliprospectform_publicsubmission.rowid
 * @param string      $submissionRef        Submission ref
 * @param int|string  $submissionDateTs     Submission date (timestamp or SQL datetime handled by dol_print_date)
 * @return array<string,string>             Keys are substitution placeholders
 */
function doliprospectform_publicform_build_submission_notify_substitution_tags(DoliDB $db, Translate $outputlangs, $entity, Societe $thirdparty, Contact $contact, $assignedCommercialId, $formType, $nbDocuments, $submissionRowid, $submissionRef, $submissionDateTs)
{
	require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

	$formType = (string) $formType;
	$formLabelKey = 'DoliProspectFormPublicSubmissionForm_'.$formType;
	$formLabel = $outputlangs->trans($formLabelKey);
	if ($formLabel === $formLabelKey) {
		$formLabel = $formType;
	}

	$countryContact = '';
	if (!empty($contact->country_id)) {
		$countryContact = getCountry($contact->country_id, '0', $db, $outputlangs);
	}
	$stateContact = '';
	if (!empty($contact->state_id)) {
		$stateContact = getState($contact->state_id, '0', $db, 0, $outputlangs);
	}

	$birthdayStr = '';
	if (!empty($contact->birthday)) {
		$birthdayStr = dol_print_date((int) $contact->birthday, 'day', false, $outputlangs);
	}

	$civilityLabel = '';
	if (!empty($contact->civility_code) && method_exists($contact, 'getCivilityLabel')) {
		$civilityLabel = (string) $contact->getCivilityLabel();
	} elseif (!empty($contact->civility_code)) {
		$civilityLabel = (string) $contact->civility_code;
	}

	$base = defined('DOL_MAIN_URL_ROOT') ? (string) DOL_MAIN_URL_ROOT : '';
	$thirdpartyCardUrl = ($base !== '' && !empty($thirdparty->id)) ? $base.'/societe/card.php?id='.((int) $thirdparty->id) : '';

	$comId = (int) $assignedCommercialId;
	$comLogin = '';
	$comFirst = '';
	$comLast = '';
	$comFull = '';
	$comEmail = '';
	if ($comId > 0) {
		if (!class_exists('User')) {
			require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
		}
		$cu = new User($db);
		if ($cu->fetch($comId) > 0) {
			$comLogin = (string) $cu->login;
			$comFirst = (string) $cu->firstname;
			$comLast = (string) $cu->lastname;
			$comFull = $cu->getFullName($outputlangs);
			$comEmail = (string) $cu->email;
		}
	}

	$dateSubStr = dol_print_date($submissionDateTs, 'dayhour', 'tzuser', $outputlangs);

	$siren = isset($thirdparty->idprof1) ? trim((string) $thirdparty->idprof1) : '';
	$siret = isset($thirdparty->idprof2) ? trim((string) $thirdparty->idprof2) : '';
	$naf = isset($thirdparty->idprof3) ? trim((string) $thirdparty->idprof3) : '';

	$contactFull = method_exists($contact, 'getFullName') ? $contact->getFullName($outputlangs) : dolGetFirstLastname((string) $contact->firstname, (string) $contact->lastname);

	return array(
		'__DOLIPROSPECTFORM_SUBMISSION_ID__' => (string) ((int) $submissionRowid),
		'__DOLIPROSPECTFORM_SUBMISSION_REF__' => (string) $submissionRef,
		'__DOLIPROSPECTFORM_SUBMISSION_FORM_TYPE__' => $formType,
		'__DOLIPROSPECTFORM_SUBMISSION_FORM_TYPE_LABEL__' => $formLabel,
		'__DOLIPROSPECTFORM_SUBMISSION_NB_DOCUMENTS__' => (string) max(0, (int) $nbDocuments),
		'__DOLIPROSPECTFORM_SUBMISSION_DATE__' => $dateSubStr,
		'__DOLIPROSPECTFORM_SUBMISSION_ENTITY__' => (string) ((int) $entity),
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_ID__' => (string) ((int) $contact->id),
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_CIVILITY__' => $civilityLabel,
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_FIRSTNAME__' => (string) $contact->firstname,
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_LASTNAME__' => (string) $contact->lastname,
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_FULLNAME__' => $contactFull,
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_EMAIL__' => (string) $contact->email,
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_PHONE__' => dol_print_phone($contact->phone_pro ?: $contact->phone_mobile, '', 0, 0, '', ' ', '', '', -1),
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_ADDRESS__' => (string) $contact->address,
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_ZIP__' => (string) $contact->zip,
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_TOWN__' => (string) $contact->town,
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_STATE__' => $stateContact,
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_COUNTRY__' => $countryContact,
		'__DOLIPROSPECTFORM_SUBMISSION_CONTACT_BIRTHDAY__' => $birthdayStr,
		'__DOLIPROSPECTFORM_SUBMISSION_COMMERCIAL_ID__' => (string) $comId,
		'__DOLIPROSPECTFORM_SUBMISSION_COMMERCIAL_LOGIN__' => $comLogin,
		'__DOLIPROSPECTFORM_SUBMISSION_COMMERCIAL_FIRSTNAME__' => $comFirst,
		'__DOLIPROSPECTFORM_SUBMISSION_COMMERCIAL_LASTNAME__' => $comLast,
		'__DOLIPROSPECTFORM_SUBMISSION_COMMERCIAL_FULLNAME__' => $comFull,
		'__DOLIPROSPECTFORM_SUBMISSION_COMMERCIAL_EMAIL__' => $comEmail,
		'__DOLIPROSPECTFORM_SUBMISSION_THIRDPARTY_DOLIBARR_URL__' => $thirdpartyCardUrl,
		'__DOLIPROSPECTFORM_SUBMISSION_COMPANY_SIREN__' => $siren,
		'__DOLIPROSPECTFORM_SUBMISSION_COMPANY_SIRET__' => $siret,
		'__DOLIPROSPECTFORM_SUBMISSION_COMPANY_NAF__' => $naf,
	);
}

/**
 * Send submission notification email (best effort; failures are logged only).
 *
 * @param DoliDB    $db                   Database handler
 * @param Translate $langs                Language
 * @param User      $actorUser            Technical user (public form actor)
 * @param int       $entity               Entity id
 * @param Societe   $thirdparty           Third party
 * @param Contact   $contact              Created contact
 * @param int       $assignedCommercialId Assigned commercial id (0 if none)
 * @param string    $formType             individual|professional
 * @param int       $nbDocuments          PDF count
 * @param int       $submissionRowid      Created submission rowid
 * @return void
 */
function doliprospectform_publicform_send_submission_notification(DoliDB $db, Translate $langs, User $actorUser, $entity, Societe $thirdparty, Contact $contact, $assignedCommercialId, $formType, $nbDocuments, $submissionRowid)
{
	if (!isModEnabled('doliprospectform') || (int) $submissionRowid <= 0) {
		return;
	}

	$templateRowid = getDolGlobalInt('DOLIPROSPECTFORM_SUBMISSION_NOTIFY_TEMPLATE_ID', 0);
	if ($templateRowid <= 0) {
		dol_syslog('doliprospectform_publicform_send_submission_notification: no DOLIPROSPECTFORM_SUBMISSION_NOTIFY_TEMPLATE_ID', LOG_WARNING);
		return;
	}

	$submissionRef = '';
	$submissionDateTs = dol_now();
	dol_include_once('custom/doliprospectform/class/doliprospectformpublicsubmission.class.php');
	$subRec = new DoliProspectFormPublicSubmission($db);
	if ($subRec->fetch((int) $submissionRowid) > 0) {
		$submissionRef = (string) $subRec->ref;
		if (!empty($subRec->date_submission)) {
			$submissionDateTs = $subRec->date_submission;
		}
	}

	$sendto = '';
	$comId = (int) $assignedCommercialId;
	if ($comId > 0) {
		if (!class_exists('User')) {
			require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
		}
		$recv = new User($db);
		if ($recv->fetch($comId) > 0 && !empty($recv->email) && isValidEmail($recv->email)) {
			$sendto = trim((string) $recv->email);
		}
	}
	if ($sendto === '') {
		$fb = trim((string) getDolGlobalString('DOLIPROSPECTFORM_SUBMISSION_NOTIFY_FALLBACK_EMAIL', ''));
		if ($fb !== '' && isValidEmail($fb)) {
			$sendto = $fb;
		}
	}
	if ($sendto === '') {
		dol_syslog('doliprospectform_publicform_send_submission_notification: no valid recipient', LOG_WARNING);
		return;
	}

	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
	$formmail = new FormMail($db);
	$template = $formmail->getEMailTemplate($db, 'user', $actorUser, $langs, $templateRowid, 1);
	if (!is_object($template) || empty($template->id)) {
		dol_syslog('doliprospectform_publicform_send_submission_notification: template fetch failed rowid='.$templateRowid, LOG_WARNING);
		return;
	}

	$tags = doliprospectform_publicform_build_submission_notify_substitution_tags(
		$db,
		$langs,
		(int) $entity,
		$thirdparty,
		$contact,
		$assignedCommercialId,
		$formType,
		$nbDocuments,
		(int) $submissionRowid,
		$submissionRef,
		$submissionDateTs
	);

	$parameters = array(
		'doliprospectform_submission' => array(
			'tags' => $tags,
			'commercial_for_links' => $comId,
		),
	);

	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
	$substitutionarray = getCommonSubstitutionArray($langs, 0, array('user'), $thirdparty);
	complete_substitutions_array($substitutionarray, $langs, $thirdparty, $parameters);

	$subject = make_substitutions((string) $template->topic, $substitutionarray);
	$message = make_substitutions((string) $template->content, $substitutionarray);

	$from = trim((string) getDolGlobalString('MAIN_MAIL_EMAIL_FROM', ''));
	if ($from === '' && !empty($template->email_from)) {
		$from = trim((string) $template->email_from);
	}
	if ($from === '') {
		dol_syslog('doliprospectform_publicform_send_submission_notification: MAIN_MAIL_EMAIL_FROM empty', LOG_WARNING);
		return;
	}

	$filepath = array();
	$filename = array();
	$mimetype = array();
	$sendtocc = '';
	$sendtobcc = '';
	$deliveryreceipt = 0;
	$trackid = 'dpf-sub-'.((int) $entity).'-'.((int) $submissionRowid);
	$sendcontext = 'standard';
	$replyto = '';
	$upload_dir_tmp = '';

	require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';
	$mailfile = new CMailFile($subject, $sendto, $from, $message, $filepath, $mimetype, $filename, $sendtocc, $sendtobcc, $deliveryreceipt, -1, '', '', $trackid, '', $sendcontext, $replyto, $upload_dir_tmp);

	if (!empty($mailfile->error) || !empty($mailfile->errors)) {
		dol_syslog('doliprospectform_publicform_send_submission_notification: CMailFile error '.$mailfile->error.' '.implode(' ', $mailfile->errors), LOG_ERR);
		return;
	}
	$res = $mailfile->sendfile();
	if (!$res) {
		dol_syslog('doliprospectform_publicform_send_submission_notification: sendfile returned false', LOG_ERR);
	}
}

/**
 * Persist one public form submission (business object) after contact + PDFs were stored successfully.
 *
 * @param DoliDB  $db                    Database handler
 * @param User    $actorUser             Technical Dolibarr user used for public form operations
 * @param int     $entity                Entity id (same as public link / third party)
 * @param Societe $thirdparty            Third party updated or created by the form
 * @param int     $contactId             Created contact rowid (>0)
 * @param int     $assignedCommercialId  Sales user encoded in the signed link (may be 0)
 * @param string  $formType              internal code: individual | professional
 * @param int     $nbDocuments           Number of PDF files attached
 * @return int<-1,max>                   Created rowid or -1 on failure (logged; does not roll back tiers/contact)
 */
function doliprospectform_publicform_register_submission(DoliDB $db, User $actorUser, $entity, Societe $thirdparty, $contactId, $assignedCommercialId, $formType, $nbDocuments)
{
	dol_include_once('custom/doliprospectform/class/doliprospectformpublicsubmission.class.php');

	$sub = new DoliProspectFormPublicSubmission($db);
	$sub->entity = (int) $entity;
	// Use dayhourlog (%Y%m%d%H%M%S): %i/%s are not Dolibarr date tokens and produced broken refs (e.g. %41 for minute 41).
	$sub->ref = 'DPS-'.dol_print_date(dol_now(), 'dayhourlog').'-'.((int) $thirdparty->id).'_'.mt_rand(10000, 99999);
	$sub->fk_soc = (int) $thirdparty->id;
	$sub->fk_contact = null;
	if ($contactId > 0) {
		$sub->fk_contact = (int) $contactId;
	}
	$sub->fk_user_commercial = (int) $assignedCommercialId;
	$sub->form_type = (string) $formType;
	$sub->nb_documents = max(0, (int) $nbDocuments);
	$sub->date_submission = dol_now();
	$sub->status = DoliProspectFormPublicSubmission::STATUS_VALIDATED;

	$res = $sub->create($actorUser, 1);
	if ($res <= 0) {
		dol_syslog('doliprospectform_publicform_register_submission: '.$sub->error, LOG_ERR);
		return -1;
	}
	return (int) $res;
}

/**
 * Best-effort split of third party display name into first / last name for form prefill
 *
 * @param string $fullName Value from Societe::name
 * @return array{0:string,1:string} firstname, lastname
 */
function doliprospectform_publicform_split_display_name($fullName)
{
	$fullName = trim((string) $fullName);
	if ($fullName === '') {
		return array('', '');
	}
	$p = strpos($fullName, ' ');
	if ($p === false) {
		return array('', $fullName);
	}
	return array(trim(substr($fullName, 0, $p)), trim(substr($fullName, $p + 1)));
}

/**
 * Assign customer code on thirdparty when numbering is automatic (same pattern as core public forms)
 *
 * @param Societe $thirdparty Third party (Societe instance)
 * @return void
 */
function doliprospectform_publicform_assign_customer_code($thirdparty)
{
	global $db, $conf;

	$module = getDolGlobalString('SOCIETE_CODECLIENT_ADDON', 'mod_codeclient_leopard');
	if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php') {
		$module = substr($module, 0, dol_strlen($module) - 4);
	}
	$dirsociete = array_merge(array('/core/modules/societe/'), (array) $conf->modules_parts['societe']);
	foreach ($dirsociete as $dirroot) {
		$res = dol_include_once($dirroot.$module.'.php');
		if ($res) {
			break;
		}
	}
	if (empty($res)) {
		return;
	}
	/** @var ModeleThirdPartyCode $modCodeClient */
	$modCodeClient = new $module($db);
	$tmpcode = '';
	if (empty($tmpcode) && !empty($modCodeClient->code_auto)) {
		$tmpcode = $modCodeClient->getNextValue($thirdparty, 0);
	}
	if (!empty($tmpcode)) {
		$thirdparty->code_client = $tmpcode;
	}
}

/**
 * Whether Dolibarr captcha is enabled for DoliProspectForm public forms (Home — Setup — Security — Captcha).
 *
 * @return bool
 */
function doliprospectform_publicform_is_captcha_enabled()
{
	return (bool) getDolGlobalInt('MAIN_SECURITY_ENABLECAPTCHA_DOLIPROSPECTFORM');
}

/**
 * Build URL to reload the page for captcha image refresh (GET, preserves signed token in query).
 *
 * @param int    $entity Entity id
 * @param string $token  Signed token (query param t)
 * @return string
 */
function doliprospectform_publicform_get_captcha_reload_url($entity, $token)
{
	$params = array('e' => (int) $entity);
	if ($token !== '') {
		$params['t'] = $token;
	}
	return dolBuildUrl($_SERVER['PHP_SELF'], $params);
}

/**
 * Load Dolibarr captcha handler instance (same pattern as public/ticket/create_ticket.php).
 *
 * @param DoliDB    $db    Database handler
 * @param Translate $langs Language instance
 * @param User|null $user  Context user (may be empty for public pages)
 * @return object|null    Captcha object or null if disabled / load error
 */
function doliprospectform_publicform_get_captcha_object($db, $langs, $user)
{
	global $conf;

	if (!doliprospectform_publicform_is_captcha_enabled()) {
		return null;
	}

	require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
	$captcha = getDolGlobalString('MAIN_SECURITY_ENABLECAPTCHA_HANDLER', 'standard');
	$dirModCaptcha = array_merge(
		array('main' => '/core/modules/security/captcha/'),
		(isset($conf->modules_parts['captcha']) && is_array($conf->modules_parts['captcha'])) ? $conf->modules_parts['captcha'] : array()
	);
	$fullpathclassfile = '';
	foreach ($dirModCaptcha as $dir) {
		$fullpathclassfile = dol_buildpath($dir.'modCaptcha'.ucfirst($captcha).'.class.php', 0, 2);
		if ($fullpathclassfile) {
			break;
		}
	}
	if (!$fullpathclassfile) {
		return null;
	}
	include_once $fullpathclassfile;
	$classname = 'modCaptcha'.ucfirst($captcha);
	if (!class_exists($classname)) {
		return null;
	}
	return new $classname($db, $conf, $langs, $user);
}

/**
 * Validate captcha on POST when enabled (uses GETPOST "code" like core).
 *
 * @param object|null $captchaobj Return value of doliprospectform_publicform_get_captcha_object()
 * @param Translate   $langs      Language instance
 * @param string[]    $errors     Append ErrorBadValueForCode here if validation fails
 * @return bool                   True if OK or captcha disabled
 */
function doliprospectform_publicform_validate_captcha_submission($captchaobj, $langs, array &$errors)
{
	if (!doliprospectform_publicform_is_captcha_enabled() || !$captchaobj) {
		return true;
	}
	$langs->load('errors');
	$ok = false;
	if (method_exists($captchaobj, 'validateCodeAfterLoginSubmit')) {
		$ok = (bool) $captchaobj->validateCodeAfterLoginSubmit();
	}
	if (!$ok) {
		$errors[] = $langs->trans('ErrorBadValueForCode');
		return false;
	}
	return true;
}

/**
 * Print captcha block before submit. Use $simpleRefreshLink when the form has several submit buttons (legacy hub; hub no longer shows captcha).
 *
 * @param object|null $captchaobj        Captcha handler
 * @param string      $reloadUrl         URL for image refresh
 * @param bool        $simpleRefreshLink If true, use GET refresh link instead of JS form.submit (hub form)
 * @return void
 */
function doliprospectform_publicform_print_captcha_block($captchaobj, $reloadUrl, $simpleRefreshLink = false)
{
	global $langs;

	if (!$captchaobj || !method_exists($captchaobj, 'getCaptchaCodeForForm')) {
		return;
	}

	print '<div class="dpf-captcha-wrap mb-3">';

	if ($simpleRefreshLink) {
		print '<div class="tagtd tdinputlogin nowrap valignmiddle">';
		print '<span class="fa fa-unlock"></span> ';
		print '<span class="nofa span-icon-security inline-block">';
		print '<input id="securitycode" placeholder="'.dol_escape_htmltag($langs->trans('SecurityCode')).'" class="flat dpf-form-control" type="text" maxlength="5" name="code" autocomplete="off" />';
		print '</span> ';
		print '<span class="nowrap inline-block">';
		print '<img class="inline-block valignmiddle" src="'.DOL_URL_ROOT.'/core/antispamimage.php" border="0" width="80" height="32" id="img_securitycode" alt="" /> ';
		print '<a class="inline-block valignmiddle" href="'.dol_escape_htmltag($reloadUrl).'">'.img_picto($langs->trans('Refresh'), 'refresh', 'class="pictofixedwidth"').'</a>';
		print '</span>';
		print '</div>';
	} else {
		print $captchaobj->getCaptchaCodeForForm($reloadUrl);
	}

	print '</div>';
}
