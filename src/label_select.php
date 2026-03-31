<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    mokodolidymo/label_select.php
 * \ingroup mokodolidymo
 * \brief   Select a label template then redirect to print page
 */

// Load Dolibarr environment
$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
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
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once __DIR__.'/class/LabelTemplate.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Translate $langs
 * @var User $user
 */

$langs->loadLangs(array("mokodolidymo@mokodolidymo"));

if (!$user->hasRight('mokodolidymo', 'label', 'print')) {
	accessforbidden();
}

$object_type = GETPOST('object_type', 'aZ09');
$object_id = GETPOSTINT('object_id');
$template_id = GETPOSTINT('template_id');

// If template already selected, redirect to print page
if ($template_id > 0 && $object_id > 0) {
	$url = dol_buildpath('/mokodolidymo/label_print.php', 1);
	$url .= '?id='.$template_id.'&selected_ids[]='.$object_id;
	header('Location: '.$url);
	exit;
}

// Load available templates for this object type
$templates = array();
$sql = "SELECT rowid, ref, label, label_size, label_width, label_height";
$sql .= " FROM ".$db->prefix()."mokodolidymo_label";
$sql .= " WHERE object_type = '".$db->escape($object_type)."'";
$sql .= " AND status = 1";
$sql .= " AND entity IN (".getEntity('mokodolidymo_label').")";
$sql .= " ORDER BY ref ASC";
$resql = $db->query($sql);
if ($resql) {
	while ($obj = $db->fetch_object($resql)) {
		$templates[] = $obj;
	}
}

// If only one template, skip selection and go straight to print
if (count($templates) === 1) {
	$url = dol_buildpath('/mokodolidymo/label_print.php', 1);
	$url .= '?id='.$templates[0]->rowid.'&selected_ids[]='.$object_id;
	header('Location: '.$url);
	exit;
}

// Load the source object for display
$source_label = '';
switch ($object_type) {
	case 'product':
		require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
		$src = new Product($db);
		$src->fetch($object_id);
		$source_label = $src->ref.' - '.$src->label;
		break;
	case 'thirdparty':
		require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
		$src = new Societe($db);
		$src->fetch($object_id);
		$source_label = $src->name;
		break;
	case 'contact':
		require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
		$src = new Contact($db);
		$src->fetch($object_id);
		$source_label = $src->getFullName($langs);
		break;
}


/*
 * View
 */

llxHeader('', $langs->trans("PrintLabel"), '', '', 0, 0, '', '', '', 'mod-mokodolidymo page-label_select');

print load_fiche_titre($langs->trans("PrintLabel"), '', 'fa-print');

print '<div class="fichecenter">';

if ($source_label) {
	print '<p>Printing label for: <strong>'.dol_escape_htmltag($source_label).'</strong> ('.ucfirst($object_type).')</p>';
}

if (empty($templates)) {
	print '<div class="warning">No active label templates found for '.dol_escape_htmltag(ucfirst($object_type)).' objects. ';
	print '<a href="'.dol_buildpath('/mokodolidymo/label_card.php', 1).'?action=create">Create one</a>.</div>';
} else {
	print '<p>Select a label template:</p>';

	print '<div style="display:flex;flex-wrap:wrap;gap:12px;">';
	foreach ($templates as $tpl) {
		$size_desc = $tpl->label_size;
		if (isset(LabelTemplate::LABEL_SIZES[$tpl->label_size])) {
			$size_desc .= ' - '.LabelTemplate::LABEL_SIZES[$tpl->label_size][2];
		}

		$select_url = $_SERVER['PHP_SELF'].'?object_type='.urlencode($object_type);
		$select_url .= '&object_id='.$object_id.'&template_id='.$tpl->rowid;

		print '<a href="'.$select_url.'" class="inline-block" style="text-decoration:none;">';
		print '<div style="border:2px solid #ccc;border-radius:6px;padding:16px 20px;min-width:200px;background:#fff;cursor:pointer;transition:border-color .2s;" ';
		print 'onmouseover="this.style.borderColor=\'#0066cc\'" onmouseout="this.style.borderColor=\'#ccc\'">';
		print '<div style="font-size:15px;font-weight:bold;color:#333;">'.dol_escape_htmltag($tpl->label).'</div>';
		print '<div style="font-size:12px;color:#666;margin-top:4px;">'.dol_escape_htmltag($tpl->ref).'</div>';
		print '<div style="font-size:12px;color:#888;margin-top:2px;">'.dol_escape_htmltag($size_desc).'</div>';
		print '<div style="font-size:11px;color:#888;margin-top:2px;">'.dol_escape_htmltag($tpl->label_width.'mm x '.$tpl->label_height.'mm').'</div>';
		print '</div></a>';
	}
	print '</div>';
}

print '</div>';

llxFooter();
$db->close();
