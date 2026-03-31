<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    mokodolidymo/label_card.php
 * \ingroup mokodolidymo
 * \brief   Card page for label template (create/view/edit)
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
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once __DIR__.'/class/LabelTemplate.class.php';
require_once __DIR__.'/lib/mokodolidymo.lib.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Translate $langs
 * @var User $user
 */

$langs->loadLangs(array("mokodolidymo@mokodolidymo"));

$id = GETPOSTINT('id');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

// Security
if (!$user->hasRight('mokodolidymo', 'label', 'read')) {
	accessforbidden();
}

$object = new LabelTemplate($db);
if ($id > 0 || $ref) {
	$object->fetch($id, $ref);
	$id = $object->id;
}


/*
 * Actions
 */

if ($action == 'add' && $user->hasRight('mokodolidymo', 'label', 'write')) {
	$object->label = GETPOST('label', 'alphanohtml');
	$object->description = GETPOST('description', 'restricthtml');
	$object->label_size = GETPOST('label_size', 'aZ09');
	$object->object_type = GETPOST('object_type', 'aZ09');
	$object->source_type = 'designer';

	// Handle label size
	if ($object->label_size !== 'custom' && isset(LabelTemplate::LABEL_SIZES[$object->label_size])) {
		$size = LabelTemplate::LABEL_SIZES[$object->label_size];
		$object->label_width = $size[0];
		$object->label_height = $size[1];
	} else {
		$object->label_width = GETPOST('label_width', 'int') ?: 89;
		$object->label_height = GETPOST('label_height', 'int') ?: 36;
	}
	$object->unit = 'mm';

	// Handle file import
	$uploaded_file = '';
	if (!empty($_FILES['template_file']['name'])) {
		$uploaded_file = $_FILES['template_file']['name'];
		$ext = strtolower(pathinfo($uploaded_file, PATHINFO_EXTENSION));

		if ($ext === 'dymo' || $ext === 'label') {
			$object->source_type = 'dymo_import';
			$object->source_filename = $uploaded_file;
			// Read DYMO XML and convert to our JSON layout
			$xml_content = file_get_contents($_FILES['template_file']['tmp_name']);
			$object->layout_json = mokodolidymoParseDymoXml($xml_content);
		} elseif ($ext === 'odt') {
			$object->source_type = 'odt_import';
			$object->source_filename = $uploaded_file;
			// Store a basic layout — ODT parsing will be enhanced later
			$object->setLayout($object->getDefaultLayout());
		} else {
			setEventMessages('Unsupported file type. Use .dymo, .label, or .odt files.', null, 'errors');
			$action = 'create';
		}
	}

	if ($action != 'create') {
		// Set default layout if none from import
		if (empty($object->layout_json)) {
			$object->setLayout($object->getDefaultLayout());
		}

		$result = $object->create($user);
		if ($result > 0) {
			header("Location: ".dol_buildpath('/mokodolidymo/label_card.php', 1).'?id='.$result);
			exit;
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
			$action = 'create';
		}
	}
}

if ($action == 'update' && $user->hasRight('mokodolidymo', 'label', 'write')) {
	$object->label = GETPOST('label', 'alphanohtml');
	$object->description = GETPOST('description', 'restricthtml');
	$object->label_size = GETPOST('label_size', 'aZ09');
	$object->object_type = GETPOST('object_type', 'aZ09');

	if ($object->label_size !== 'custom' && isset(LabelTemplate::LABEL_SIZES[$object->label_size])) {
		$size = LabelTemplate::LABEL_SIZES[$object->label_size];
		$object->label_width = $size[0];
		$object->label_height = $size[1];
	} else {
		$object->label_width = GETPOST('label_width', 'int') ?: $object->label_width;
		$object->label_height = GETPOST('label_height', 'int') ?: $object->label_height;
	}

	$result = $object->update($user);
	if ($result > 0) {
		setEventMessages('RecordSaved', null, 'mesgs');
		header("Location: ".$_SERVER["PHP_SELF"].'?id='.$object->id);
		exit;
	} else {
		setEventMessages($object->error, $object->errors, 'errors');
		$action = 'edit';
	}
}

if ($action == 'confirm_delete' && $confirm == 'yes' && $user->hasRight('mokodolidymo', 'label', 'delete')) {
	$result = $object->delete($user);
	if ($result > 0) {
		header("Location: ".dol_buildpath('/mokodolidymo/label_list.php', 1));
		exit;
	} else {
		setEventMessages($object->error, $object->errors, 'errors');
	}
}

if ($action == 'activate' && $user->hasRight('mokodolidymo', 'label', 'write')) {
	$object->setStatus($user, 1);
	header("Location: ".$_SERVER["PHP_SELF"].'?id='.$object->id);
	exit;
}

if ($action == 'draft' && $user->hasRight('mokodolidymo', 'label', 'write')) {
	$object->setStatus($user, 0);
	header("Location: ".$_SERVER["PHP_SELF"].'?id='.$object->id);
	exit;
}

// Save layout from designer (AJAX)
if ($action == 'save_layout' && $user->hasRight('mokodolidymo', 'label', 'write')) {
	$layout_data = GETPOST('layout_json', 'none');
	if ($layout_data) {
		$decoded = json_decode($layout_data, true);
		if ($decoded !== null) {
			$object->layout_json = $layout_data;
			$result = $object->update($user);

			if (GETPOST('ajax', 'int')) {
				header('Content-Type: application/json');
				echo json_encode(array('success' => ($result > 0)));
				exit;
			}
		}
	}
	header("Location: ".$_SERVER["PHP_SELF"].'?id='.$object->id);
	exit;
}


/*
 * View
 */

$form = new Form($db);

$title = $langs->trans("LabelTemplate");
llxHeader('', $title, '', '', 0, 0, '', '', '', 'mod-mokodolidymo page-label_card');

// Create form
if ($action == 'create') {
	print load_fiche_titre($langs->trans("NewLabelTemplate"), '', 'fa-print');

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'" enctype="multipart/form-data">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';

	print dol_get_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">';

	// Label name
	print '<tr><td class="titlefieldcreate fieldrequired">'.$langs->trans("Label").'</td>';
	print '<td><input type="text" name="label" value="'.dol_escape_htmltag(GETPOST('label', 'alpha')).'" class="minwidth300" required></td></tr>';

	// Description
	print '<tr><td>'.$langs->trans("Description").'</td>';
	print '<td><textarea name="description" rows="3" class="minwidth300">'.dol_escape_htmltag(GETPOST('description', 'alpha')).'</textarea></td></tr>';

	// Label size
	print '<tr><td class="fieldrequired">'.$langs->trans("LabelSize").'</td><td>';
	print '<select name="label_size" id="label_size" class="flat minwidth200" onchange="toggleCustomSize()">';
	foreach (LabelTemplate::LABEL_SIZES as $code => $info) {
		print '<option value="'.$code.'">'.$code.' - '.$info[2].' ('.$info[0].'x'.$info[1].'mm)</option>';
	}
	print '</select>';
	print '<div id="custom_size" style="display:none; margin-top:8px;">';
	print $langs->trans("LabelWidth").': <input type="number" name="label_width" value="89" step="0.1" class="maxwidth75"> mm &nbsp; ';
	print $langs->trans("LabelHeight").': <input type="number" name="label_height" value="36" step="0.1" class="maxwidth75"> mm';
	print '</div>';
	print '</td></tr>';

	// Object type
	print '<tr><td class="fieldrequired">Data Source</td><td>';
	print '<select name="object_type" class="flat minwidth200">';
	foreach (array_keys(LabelTemplate::BINDABLE_FIELDS) as $type) {
		print '<option value="'.$type.'">'.ucfirst($type).'</option>';
	}
	print '</select></td></tr>';

	// Import template file
	print '<tr><td>Import Template</td>';
	print '<td><input type="file" name="template_file" accept=".dymo,.label,.odt">';
	print '<br><span class="opacitymedium">Optional: import a .dymo, .label, or .odt file as starting point</span>';
	print '</td></tr>';

	print '</table>';
	print dol_get_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" value="'.$langs->trans("Create").'">';
	print ' &nbsp; <a class="button button-cancel" href="'.dol_buildpath('/mokodolidymo/label_list.php', 1).'">'.$langs->trans("Cancel").'</a>';
	print '</div>';
	print '</form>';

	print '<script>
	function toggleCustomSize() {
		var sel = document.getElementById("label_size");
		document.getElementById("custom_size").style.display = (sel.value === "custom") ? "block" : "none";
	}
	</script>';
}

// View / Edit existing
if ($object->id > 0) {
	// Delete confirmation
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm(
			$_SERVER["PHP_SELF"].'?id='.$object->id,
			$langs->trans('DeleteLabelTemplate'),
			$langs->trans('ConfirmDelete'),
			'confirm_delete',
			'',
			0,
			1
		);
		print $formconfirm;
	}

	$head = mokodolidymoLabelPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("LabelTemplate"), -1, 'fa-print');

	$linkback = '<a href="'.dol_buildpath('/mokodolidymo/label_list.php', 1).'">'.$langs->trans("BackToList").'</a>';

	dol_banner_tab($object, 'id', $linkback, 1, 'rowid', 'ref');

	if ($action == 'edit') {
		print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';
	}

	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">';

	// Label name
	print '<tr><td class="titlefield">'.$langs->trans("Label").'</td><td>';
	if ($action == 'edit') {
		print '<input type="text" name="label" value="'.dol_escape_htmltag($object->label).'" class="minwidth300">';
	} else {
		print dol_escape_htmltag($object->label);
	}
	print '</td></tr>';

	// Description
	print '<tr><td>'.$langs->trans("Description").'</td><td>';
	if ($action == 'edit') {
		print '<textarea name="description" rows="3" class="minwidth300">'.dol_escape_htmltag($object->description).'</textarea>';
	} else {
		print dol_escape_htmltag($object->description);
	}
	print '</td></tr>';

	// Label size
	print '<tr><td>'.$langs->trans("LabelSize").'</td><td>';
	if ($action == 'edit') {
		print '<select name="label_size" class="flat minwidth200">';
		foreach (LabelTemplate::LABEL_SIZES as $code => $info) {
			$selected = ($object->label_size == $code) ? ' selected' : '';
			print '<option value="'.$code.'"'.$selected.'>'.$code.' - '.$info[2].'</option>';
		}
		print '</select>';
	} else {
		$size_desc = $object->label_size;
		if (isset(LabelTemplate::LABEL_SIZES[$object->label_size])) {
			$size_desc .= ' - '.LabelTemplate::LABEL_SIZES[$object->label_size][2];
		}
		print dol_escape_htmltag($size_desc);
	}
	print '</td></tr>';

	// Dimensions
	print '<tr><td>Dimensions</td><td>';
	print dol_escape_htmltag($object->label_width.'mm x '.$object->label_height.'mm');
	print '</td></tr>';

	// Object type
	print '<tr><td>Data Source</td><td>';
	if ($action == 'edit') {
		print '<select name="object_type" class="flat minwidth200">';
		foreach (array_keys(LabelTemplate::BINDABLE_FIELDS) as $type) {
			$selected = ($object->object_type == $type) ? ' selected' : '';
			print '<option value="'.$type.'"'.$selected.'>'.ucfirst($type).'</option>';
		}
		print '</select>';
	} else {
		print ucfirst($object->object_type);
	}
	print '</td></tr>';

	// Source
	print '<tr><td>Source</td><td>';
	$source_labels = array('designer' => 'Designer', 'dymo_import' => 'Imported from DYMO', 'odt_import' => 'Imported from ODT');
	print isset($source_labels[$object->source_type]) ? $source_labels[$object->source_type] : $object->source_type;
	if ($object->source_filename) {
		print ' ('.dol_escape_htmltag($object->source_filename).')';
	}
	print '</td></tr>';

	// Status
	print '<tr><td>'.$langs->trans("Status").'</td><td>';
	print $object->getLibStatut(0);
	print '</td></tr>';

	print '</table>';
	print '</div>';

	if ($action == 'edit') {
		print '<div class="center" style="margin-top:12px;">';
		print '<input type="submit" class="button button-save" value="'.$langs->trans("Save").'">';
		print ' &nbsp; <a class="button button-cancel" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">'.$langs->trans("Cancel").'</a>';
		print '</div>';
		print '</form>';
	}

	print dol_get_fiche_end();

	// Actions bar
	if ($action != 'edit') {
		print '<div class="tabsAction">';

		if ($user->hasRight('mokodolidymo', 'label', 'write')) {
			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken().'">'.$langs->trans("Modify").'</a>';

			// Open designer
			print '<a class="butAction" href="'.dol_buildpath('/mokodolidymo/label_designer.php', 1).'?id='.$object->id.'">Open Designer</a>';

			// Status toggle
			if ($object->status == 0) {
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=activate&token='.newToken().'">Activate</a>';
			} else {
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=draft&token='.newToken().'">Set to Draft</a>';
			}
		}

		if ($user->hasRight('mokodolidymo', 'label', 'print')) {
			print '<a class="butAction" href="'.dol_buildpath('/mokodolidymo/label_print.php', 1).'?id='.$object->id.'">'.$langs->trans("PrintLabel").'</a>';
		}

		if ($user->hasRight('mokodolidymo', 'label', 'delete')) {
			print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=delete&token='.newToken().'">'.$langs->trans("Delete").'</a>';
		}

		print '</div>';
	}
}

llxFooter();
$db->close();


/**
 * Parse DYMO XML label file into our JSON layout format.
 *
 * DYMO label files (DesktopLabel Version="1") store coordinates in inches.
 * LabelObjects are found inside DynamicLayoutManager > LabelObjects.
 * Each object has an ObjectLayout with DYMOPoint (X,Y) and Size (Width,Height).
 *
 * @param  string $xml_content Raw XML content from .dymo/.label file
 * @return string              JSON layout string
 */
function mokodolidymoParseDymoXml($xml_content)
{
	$layout = array('elements' => array());

	$xml = @simplexml_load_string($xml_content);
	if ($xml === false) {
		return json_encode($layout);
	}

	// Extract label dimensions from DYMORect (inches -> mm)
	$dymo_label = $xml->DYMOLabel;
	if ($dymo_label && $dymo_label->DYMORect && $dymo_label->DYMORect->Size) {
		$layout['width'] = round((float) $dymo_label->DYMORect->Size->Width * 25.4, 1);
		$layout['height'] = round((float) $dymo_label->DYMORect->Size->Height * 25.4, 1);
	}

	$elem_id = 1;

	// Find all label objects inside DynamicLayoutManager > LabelObjects
	$label_objects = $xml->xpath('//DynamicLayoutManager/LabelObjects/*') ?: array();
	foreach ($label_objects as $obj) {
		$obj_name = $obj->getName(); // TextObject, BarcodeObject, ImageObject, ShapeObject

		// Get position and size from ObjectLayout (inches -> mm, 1 inch = 25.4mm)
		$obj_layout = $obj->ObjectLayout;
		if (!$obj_layout) {
			continue;
		}

		$x_in = $obj_layout->DYMOPoint ? (float) $obj_layout->DYMOPoint->X : 0;
		$y_in = $obj_layout->DYMOPoint ? (float) $obj_layout->DYMOPoint->Y : 0;
		$w_in = $obj_layout->Size ? (float) $obj_layout->Size->Width : 1;
		$h_in = $obj_layout->Size ? (float) $obj_layout->Size->Height : 0.5;

		$element = array(
			'id' => 'elem_'.$elem_id,
			'x' => round($x_in * 25.4, 1),
			'y' => round($y_in * 25.4, 1),
			'width' => round($w_in * 25.4, 1),
			'height' => round($h_in * 25.4, 1),
		);

		switch ($obj_name) {
			case 'TextObject':
				$element['type'] = 'text';
				$text = '';
				$font_size = 12;
				$is_bold = false;
				$h_align = 'left';

				// Extract text from FormattedText > LineTextSpan > TextSpan > Text
				$text_span = $obj->xpath('.//TextSpan');
				if ($text_span && count($text_span) > 0) {
					$text = (string) $text_span[0]->Text;
					if ($text_span[0]->FontInfo) {
						$font_size = (float) $text_span[0]->FontInfo->FontSize ?: 12;
						$is_bold = ((string) $text_span[0]->FontInfo->IsBold === 'True');
					}
				}

				$alignment = (string) $obj->HorizontalAlignment;
				$align_map = array('Left' => 'left', 'Center' => 'center', 'Right' => 'right');
				$h_align = isset($align_map[$alignment]) ? $align_map[$alignment] : 'left';

				$element['properties'] = array(
					'text' => $text ?: 'Text',
					'fontSize' => round($font_size),
					'fontWeight' => $is_bold ? 'bold' : 'normal',
					'textAlign' => $h_align,
					'binding' => '',
				);
				break;

			case 'BarcodeObject':
				$element['type'] = 'barcode';
				$format = (string) $obj->BarcodeFormat ?: 'Code128Auto';
				$barcode_text = (string) $obj->Text ?: '';
				$show_text = ((string) $obj->TextPosition !== 'None');

				// Map DYMO barcode formats to standard names
				$format_map = array(
					'Code128Auto' => 'CODE128', 'QRCode' => 'QRCODE', 'EAN13' => 'EAN13',
					'EAN8' => 'EAN8', 'UPCA' => 'UPCA', 'Code39' => 'CODE39',
					'ITF14' => 'ITF14', 'PDF417' => 'PDF417', 'DataMatrix' => 'DATAMATRIX',
				);
				$mapped_format = isset($format_map[$format]) ? $format_map[$format] : 'CODE128';

				$element['properties'] = array(
					'data' => $barcode_text,
					'format' => $mapped_format,
					'showText' => $show_text,
					'binding' => 'product.barcode',
				);
				break;

			case 'ImageObject':
				$element['type'] = 'image';
				$image_data = (string) $obj->Image ?: '';
				$element['properties'] = array(
					'src' => $image_data ? 'data:image/png;base64,'.$image_data : '',
					'fit' => 'contain',
				);
				break;

			case 'ShapeObject':
				$element['type'] = 'line';
				$element['properties'] = array(
					'direction' => ((string) $obj->ShapeType === 'VerticalLine') ? 'vertical' : 'horizontal',
					'thickness' => 1,
					'color' => '#000000',
				);
				break;

			default:
				continue 2;
		}

		$layout['elements'][] = $element;
		$elem_id++;
	}

	return json_encode($layout, JSON_UNESCAPED_UNICODE);
}
