<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    mokodolidymo/label_list.php
 * \ingroup mokodolidymo
 * \brief   List of label templates
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once __DIR__.'/class/LabelTemplate.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Translate $langs
 * @var User $user
 */

$langs->loadLangs(array("mokodolidymo@mokodolidymo"));

// Security
if (!$user->hasRight('mokodolidymo', 'label', 'read')) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTINT('page');
$limit = GETPOSTINT('limit') ? GETPOSTINT('limit') : $conf->liste_limit;
$offset = $limit * $page;

$search_ref = GETPOST('search_ref', 'alpha');
$search_label = GETPOST('search_label', 'alpha');
$search_status = GETPOST('search_status', 'intcomma');

if (!$sortfield) {
	$sortfield = 't.rowid';
}
if (!$sortorder) {
	$sortorder = 'DESC';
}

// Build SQL
$sql = "SELECT t.rowid, t.ref, t.label, t.label_size, t.label_width, t.label_height,";
$sql .= " t.object_type, t.source_type, t.status, t.date_creation";
$sql .= " FROM ".$db->prefix()."mokodolidymo_label as t";
$sql .= " WHERE t.entity = ".((int) $conf->entity);

if ($search_ref) {
	$sql .= natural_search('t.ref', $search_ref);
}
if ($search_label) {
	$sql .= natural_search('t.label', $search_label);
}
if ($search_status !== '' && $search_status !== '-1') {
	$sql .= " AND t.status = ".((int) $search_status);
}

$sql .= $db->order($sortfield, $sortorder);

// Count total
$nbtotalofrecords = 0;
$sqlcount = preg_replace('/^SELECT.*FROM/', 'SELECT COUNT(*) as total FROM', preg_replace('/ORDER BY.*$/', '', $sql));
$resqlcount = $db->query($sqlcount);
if ($resqlcount) {
	$objcount = $db->fetch_object($resqlcount);
	$nbtotalofrecords = $objcount->total;
}

$sql .= $db->plimit($limit + 1, $offset);

/*
 * View
 */

llxHeader('', $langs->trans("LabelTemplates"), '', '', 0, 0, '', '', '', 'mod-mokodolidymo page-label_list');

$title = $langs->trans("LabelTemplates");
$newurl = dol_buildpath('/mokodolidymo/label_card.php', 1).'?action=create';

print_barre_liste(
	$title,
	$page,
	$_SERVER["PHP_SELF"],
	'',
	$sortfield,
	$sortorder,
	'',
	0,
	$nbtotalofrecords,
	'fa-print',
	0,
	'<a class="butAction" href="'.$newurl.'">'.$langs->trans("NewLabelTemplate").'</a>',
	'',
	$limit
);

// Search row
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

print '<table class="noborder centpercent">';

// Header
print '<tr class="liste_titre">';
print_liste_field_titre("Ref", $_SERVER["PHP_SELF"], "t.rowid", "", "", "", $sortfield, $sortorder);
print_liste_field_titre("Label", $_SERVER["PHP_SELF"], "t.label", "", "", "", $sortfield, $sortorder);
print_liste_field_titre("LabelSize", $_SERVER["PHP_SELF"], "t.label_size", "", "", "", $sortfield, $sortorder);
print_liste_field_titre("ObjectType", $_SERVER["PHP_SELF"], "t.object_type", "", "", "", $sortfield, $sortorder);
print_liste_field_titre("Source", $_SERVER["PHP_SELF"], "t.source_type", "", "", "", $sortfield, $sortorder);
print_liste_field_titre("Status", $_SERVER["PHP_SELF"], "t.status", "", "", '', $sortfield, $sortorder, 'center ');
print_liste_field_titre("DateCreation", $_SERVER["PHP_SELF"], "t.date_creation", "", "", '', $sortfield, $sortorder, 'center ');
print '</tr>';

// Search row
print '<tr class="liste_titre">';
print '<td><input type="text" name="search_ref" value="'.dol_escape_htmltag($search_ref).'" class="maxwidth75"></td>';
print '<td><input type="text" name="search_label" value="'.dol_escape_htmltag($search_label).'" class="maxwidth200"></td>';
print '<td></td>';
print '<td></td>';
print '<td></td>';
print '<td class="center"><select name="search_status" class="flat"><option value="-1">&nbsp;</option>';
print '<option value="0"'.($search_status === '0' ? ' selected' : '').'>Draft</option>';
print '<option value="1"'.($search_status === '1' ? ' selected' : '').'>Active</option>';
print '</select></td>';
print '<td class="center">';
print '<button type="submit" class="liste_titre button_search" name="button_search" value="x">';
print img_picto($langs->trans("Search"), 'search');
print '</button></td>';
print '</tr>';

// Data rows
$resql = $db->query($sql);
if ($resql) {
	$num = $db->num_rows($resql);
	$i = 0;
	$labeltemplate = new LabelTemplate($db);

	while ($i < min($num, $limit)) {
		$obj = $db->fetch_object($resql);

		$labeltemplate->id = $obj->rowid;
		$labeltemplate->ref = $obj->ref;
		$labeltemplate->label = $obj->label;
		$labeltemplate->status = $obj->status;

		print '<tr class="oddeven">';

		// Ref
		print '<td>'.$labeltemplate->getNomUrl(1).'</td>';

		// Label
		print '<td>'.dol_escape_htmltag($obj->label).'</td>';

		// Size
		$size_label = $obj->label_size;
		if (isset(LabelTemplate::LABEL_SIZES[$obj->label_size])) {
			$size_label = $obj->label_size.' - '.LabelTemplate::LABEL_SIZES[$obj->label_size][2];
		}
		print '<td>'.dol_escape_htmltag($size_label).'</td>';

		// Object type
		print '<td>'.dol_escape_htmltag(ucfirst($obj->object_type)).'</td>';

		// Source
		$source_labels = array('designer' => 'Designer', 'dymo_import' => 'DYMO Import', 'odt_import' => 'ODT Import');
		$src = isset($source_labels[$obj->source_type]) ? $source_labels[$obj->source_type] : $obj->source_type;
		print '<td>'.dol_escape_htmltag($src).'</td>';

		// Status
		print '<td class="center">'.$labeltemplate->getLibStatut(0).'</td>';

		// Date
		print '<td class="center">'.dol_print_date($db->jdate($obj->date_creation), 'day').'</td>';

		print '</tr>';
		$i++;
	}

	if ($num == 0) {
		print '<tr class="oddeven"><td colspan="7" class="opacitymedium">'.$langs->trans("NoRecordFound").'</td></tr>';
	}

	$db->free($resql);
} else {
	dol_print_error($db);
}

print '</table>';
print '</form>';

llxFooter();
$db->close();
