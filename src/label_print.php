<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    mokodolidymo/label_print.php
 * \ingroup mokodolidymo
 * \brief   Print labels via DYMO Connect SDK or PDF fallback
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

if (!$user->hasRight('mokodolidymo', 'label', 'print')) {
	accessforbidden();
}

$object = new LabelTemplate($db);
$object->fetch($id);
if (!$object->id) {
	accessforbidden('Label template not found');
}

$layout = $object->getLayout();
$bindable_fields = $object->getBindableFields();

// Load object records for printing (multi-item support)
$object_type = $object->object_type;
$selected_ids = GETPOST('selected_ids', 'array');
$records = array();

if (!empty($selected_ids)) {
	foreach ($selected_ids as $rec_id) {
		$rec_id = (int) $rec_id;
		if ($rec_id <= 0) continue;

		$source_obj = null;
		switch ($object_type) {
			case 'product':
				require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
				$source_obj = new Product($db);
				$source_obj->fetch($rec_id);
				if (method_exists($source_obj, 'fetch_barcode')) {
					$source_obj->fetch_barcode();
				}
				break;
			case 'thirdparty':
				require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
				$source_obj = new Societe($db);
				$source_obj->fetch($rec_id);
				break;
			case 'contact':
				require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
				$source_obj = new Contact($db);
				$source_obj->fetch($rec_id);
				break;
			case 'warehouse':
				require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
				$source_obj = new Entrepot($db);
				$source_obj->fetch($rec_id);
				break;
			case 'member':
				require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
				$source_obj = new Adherent($db);
				$source_obj->fetch($rec_id);
				break;
		}

		if ($source_obj && $source_obj->id > 0) {
			$records[] = array(
				'id' => $source_obj->id,
				'label' => method_exists($source_obj, 'getNomUrl') ? strip_tags($source_obj->getNomUrl(0)) : $source_obj->ref,
				'values' => $object->resolveFieldValues($source_obj),
			);
		}
	}
}

// Build print data for JS
$print_data = array(
	'templateId' => $object->id,
	'templateRef' => $object->ref,
	'labelWidth' => (float) $object->label_width,
	'labelHeight' => (float) $object->label_height,
	'layout' => $layout,
	'records' => $records,
	'objectType' => $object_type,
	'pdfUrl' => dol_buildpath('/mokodolidymo/label_pdf.php', 1),
);


/*
 * View
 */

$title = $langs->trans("PrintLabel").' - '.$object->ref;
llxHeader('', $title, '', '', 0, 0, '', '', '', 'mod-mokodolidymo page-label_print');

$head = mokodolidymoLabelPrepareHead($object);
print dol_get_fiche_head($head, 'print', $langs->trans("LabelTemplate"), -1, 'fa-print');

$linkback = '<a href="'.dol_buildpath('/mokodolidymo/label_list.php', 1).'">'.$langs->trans("BackToList").'</a>';
dol_banner_tab($object, 'id', $linkback, 1, 'rowid', 'ref');

print '<div class="underbanner clearboth"></div>';

// Record selection form
print '<div class="fichecenter" style="margin-top:12px;">';
print '<h3>Select Records to Print</h3>';

print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="id" value="'.$object->id.'">';

// Simple record selector: show a search box + list
print '<p>Search and select '.dol_escape_htmltag($object_type).' records to print labels for:</p>';

// Load recent records for selection
$search_records = array();
switch ($object_type) {
	case 'product':
		require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
		$sql = "SELECT rowid, ref, label FROM ".$db->prefix()."product WHERE entity = ".((int) $conf->entity)." ORDER BY tms DESC LIMIT 50";
		break;
	case 'thirdparty':
		require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
		$sql = "SELECT rowid, nom as ref, name_alias as label FROM ".$db->prefix()."societe WHERE entity = ".((int) $conf->entity)." ORDER BY tms DESC LIMIT 50";
		break;
	case 'contact':
		require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
		$sql = "SELECT rowid, CONCAT(lastname, ' ', firstname) as ref, '' as label FROM ".$db->prefix()."socpeople WHERE entity = ".((int) $conf->entity)." ORDER BY tms DESC LIMIT 50";
		break;
	case 'warehouse':
		require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
		$sql = "SELECT rowid, ref, label FROM ".$db->prefix()."entrepot WHERE entity = ".((int) $conf->entity)." ORDER BY tms DESC LIMIT 50";
		break;
	case 'member':
		require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
		$sql = "SELECT rowid, CONCAT(lastname, ' ', firstname) as ref, login as label FROM ".$db->prefix()."adherent WHERE entity = ".((int) $conf->entity)." ORDER BY tms DESC LIMIT 50";
		break;
	default:
		$sql = '';
}

if ($sql) {
	$resql = $db->query($sql);
	if ($resql) {
		while ($obj = $db->fetch_object($resql)) {
			$search_records[] = $obj;
		}
	}
}

print '<div style="max-height:300px;overflow-y:auto;border:1px solid #ccc;padding:8px;border-radius:4px;margin-bottom:12px;">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td><input type="checkbox" id="select-all"></td><td>Ref</td><td>Label</td><td>Qty</td></tr>';

foreach ($search_records as $rec) {
	$checked = in_array($rec->rowid, $selected_ids ?: array()) ? ' checked' : '';
	print '<tr class="oddeven">';
	print '<td><input type="checkbox" name="selected_ids[]" value="'.$rec->rowid.'"'.$checked.'></td>';
	print '<td>'.dol_escape_htmltag($rec->ref).'</td>';
	print '<td>'.dol_escape_htmltag($rec->label).'</td>';
	print '<td><input type="number" name="qty_'.$rec->rowid.'" value="1" min="1" max="100" class="maxwidth50"></td>';
	print '</tr>';
}

print '</table>';
print '</div>';

print '<input type="submit" class="button" value="Load Selected Records">';
print '</form>';

// Print buttons
if (!empty($records)) {
	print '<div style="margin-top:16px;padding:12px;background:#f0f8ff;border:1px solid #b0d0f0;border-radius:4px;">';
	print '<h3>Ready to Print: '.count($records).' record(s)</h3>';

	print '<div style="display:flex;gap:12px;margin-top:8px;">';
	print '<button type="button" class="button" id="btn-print-dymo" style="padding:8px 20px;">';
	print '<strong>Print via DYMO Connect</strong></button>';
	print '<button type="button" class="button" id="btn-print-pdf" style="padding:8px 20px;">';
	print 'Download as PDF</button>';
	print '</div>';

	print '<div id="print-status" style="margin-top:8px;"></div>';
	print '<div id="dymo-printers" style="margin-top:8px;display:none;">';
	print '<label>Select Printer: </label><select id="dymo-printer-select"></select>';
	print '</div>';
	print '</div>';

	// Preview area
	print '<h3 style="margin-top:16px;">Preview</h3>';
	print '<div id="label-preview" style="display:flex;flex-wrap:wrap;gap:8px;">';
	foreach ($records as $rec) {
		print '<div style="border:1px solid #999;padding:4px;background:#fff;font-size:11px;">';
		print '<strong>'.dol_escape_htmltag($rec['label']).'</strong><br>';
		foreach ($rec['values'] as $k => $v) {
			if ($v !== '' && $v !== null) {
				print '<span class="opacitymedium">'.dol_escape_htmltag($k).'</span>: '.dol_escape_htmltag($v).'<br>';
			}
		}
		print '</div>';
	}
	print '</div>';
}

print '</div>';

?>
<script>
(function() {
	'use strict';

	// Select-all checkbox
	var selectAll = document.getElementById('select-all');
	if (selectAll) {
		selectAll.addEventListener('change', function() {
			var boxes = document.querySelectorAll('input[name="selected_ids[]"]');
			for (var i = 0; i < boxes.length; i++) boxes[i].checked = this.checked;
		});
	}

	var printData = <?php echo json_encode($print_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

	// DYMO Connect SDK integration
	var btnDymo = document.getElementById('btn-print-dymo');
	var btnPdf = document.getElementById('btn-print-pdf');
	var statusDiv = document.getElementById('print-status');
	var printerDiv = document.getElementById('dymo-printers');
	var printerSelect = document.getElementById('dymo-printer-select');

	if (btnDymo) {
		btnDymo.addEventListener('click', function() {
			statusDiv.textContent = 'Connecting to DYMO Connect...';

			// Try loading DYMO Connect SDK
			if (typeof dymo === 'undefined' || !dymo.connect) {
				// Load SDK dynamically
				var script = document.createElement('script');
				script.src = 'https://labelwriter.com/software/dls/sdk/js/dymo.connect.framework.js';
				script.onload = function() { initDymoPrint(); };
				script.onerror = function() {
					statusDiv.textContent = 'Could not load DYMO Connect SDK. Is DYMO Connect installed?';
				};
				document.head.appendChild(script);
			} else {
				initDymoPrint();
			}
		});
	}

	function initDymoPrint() {
		try {
			dymo.connect.framework.init(function() {
				var printers = dymo.connect.framework.getPrinters();
				if (!printers || printers.length === 0) {
					statusDiv.textContent = 'No DYMO printers found. Check DYMO Connect is running.';
					return;
				}

				// Populate printer dropdown
				while (printerSelect.firstChild) printerSelect.removeChild(printerSelect.firstChild);
				printers.forEach(function(p) {
					if (p.printerType === 'LabelWriterPrinter') {
						var opt = document.createElement('option');
						opt.value = p.name;
						opt.textContent = p.name;
						printerSelect.appendChild(opt);
					}
				});

				printerDiv.style.display = 'block';
				statusDiv.textContent = 'Ready. Click printer name then print.';

				// Print all records
				printViaDymo();
			});
		} catch (e) {
			statusDiv.textContent = 'DYMO Connect error: ' + e.message;
		}
	}

	function printViaDymo() {
		var printer = printerSelect.value;
		if (!printer) {
			statusDiv.textContent = 'Select a printer first.';
			return;
		}

		statusDiv.textContent = 'Printing ' + printData.records.length + ' label(s)...';

		try {
			printData.records.forEach(function(record) {
				var xml = buildDymoXml(printData.layout, record.values, printData.labelWidth, printData.labelHeight);
				var labelObj = dymo.connect.framework.openLabelXml(xml);
				labelObj.print(printer);
			});
			statusDiv.textContent = 'Sent ' + printData.records.length + ' label(s) to ' + printer;
		} catch (e) {
			statusDiv.textContent = 'Print error: ' + e.message;
		}
	}

	/**
	 * Build DYMO Desktop Label XML from our JSON layout
	 */
	function buildDymoXml(layout, values, widthMm, heightMm) {
		var wIn = (widthMm / 25.4).toFixed(4);
		var hIn = (heightMm / 25.4).toFixed(4);

		var xml = '<?xml version="1.0" encoding="utf-8"?>\n';
		xml += '<DesktopLabel Version="1"><DYMOLabel Version="4">\n';
		xml += '<Description>MokoDoliDymo Label</Description>\n';
		xml += '<Orientation>Landscape</Orientation>\n';
		xml += '<LabelName>Custom</LabelName>\n';
		xml += '<DYMORect><DYMOPoint><X>0</X><Y>0</Y></DYMOPoint>';
		xml += '<Size><Width>' + wIn + '</Width><Height>' + hIn + '</Height></Size></DYMORect>\n';
		xml += '<DynamicLayoutManager><RotationBehavior>ClearObjects</RotationBehavior><LabelObjects>\n';

		(layout.elements || []).forEach(function(el) {
			var props = el.properties || {};
			var xIn = (el.x / 25.4).toFixed(4);
			var yIn = (el.y / 25.4).toFixed(4);
			var elWIn = (el.width / 25.4).toFixed(4);
			var elHIn = (el.height / 25.4).toFixed(4);
			var objectLayout = '<ObjectLayout><DYMOPoint><X>' + xIn + '</X><Y>' + yIn + '</Y></DYMOPoint>';
			objectLayout += '<Size><Width>' + elWIn + '</Width><Height>' + elHIn + '</Height></Size></ObjectLayout>';

			var resolvedText = resolveValue(props, values);

			switch (el.type) {
				case 'text':
					xml += '<TextObject><Name>' + escXml(el.id) + '</Name>';
					xml += '<HorizontalAlignment>' + capitalize(props.textAlign || 'left') + '</HorizontalAlignment>';
					xml += '<VerticalAlignment>Middle</VerticalAlignment>';
					xml += '<FitMode>AlwaysFit</FitMode>';
					xml += '<FormattedText><FitMode>AlwaysFit</FitMode>';
					xml += '<HorizontalAlignment>' + capitalize(props.textAlign || 'left') + '</HorizontalAlignment>';
					xml += '<VerticalAlignment>Middle</VerticalAlignment><IsVertical>False</IsVertical>';
					xml += '<LineTextSpan><TextSpan>';
					xml += '<Text>' + escXml(resolvedText) + '</Text>';
					xml += '<FontInfo><FontName>Segoe UI</FontName>';
					xml += '<FontSize>' + (props.fontSize || 12) + '</FontSize>';
					xml += '<IsBold>' + (props.fontWeight === 'bold' ? 'True' : 'False') + '</IsBold>';
					xml += '<IsItalic>False</IsItalic><IsUnderline>False</IsUnderline>';
					xml += '</FontInfo></TextSpan></LineTextSpan>';
					xml += '</FormattedText>';
					xml += objectLayout + '</TextObject>\n';
					break;

				case 'barcode':
					var format = props.format || 'CODE128';
					var dymoFormat = { 'CODE128': 'Code128Auto', 'EAN13': 'EAN13', 'EAN8': 'EAN8', 'UPCA': 'UPCA', 'CODE39': 'Code39', 'QRCODE': 'QRCode' }[format] || 'Code128Auto';
					xml += '<BarcodeObject><Name>' + escXml(el.id) + '</Name>';
					xml += '<BarcodeFormat>' + dymoFormat + '</BarcodeFormat>';
					xml += '<Text>' + escXml(resolvedText) + '</Text>';
					xml += '<TextPosition>' + (props.showText !== false ? 'Bottom' : 'None') + '</TextPosition>';
					xml += objectLayout + '</BarcodeObject>\n';
					break;

				case 'qrcode':
					xml += '<BarcodeObject><Name>' + escXml(el.id) + '</Name>';
					xml += '<BarcodeFormat>QRCode</BarcodeFormat>';
					xml += '<Text>' + escXml(resolvedText) + '</Text>';
					xml += '<TextPosition>None</TextPosition>';
					xml += objectLayout + '</BarcodeObject>\n';
					break;

				case 'image':
					// Resolve image source: bound value (e.g. company logo data URL) or static src
					var imgSrc = props.src || '';
					if (props.binding && values && values[props.binding]) {
						imgSrc = values[props.binding];
					}
					if (imgSrc) {
						var b64 = imgSrc.replace(/^data:image\/\w+;base64,/, '');
						xml += '<ImageObject><Name>' + escXml(el.id) + '</Name>';
						xml += '<ScaleMode>Uniform</ScaleMode>';
						xml += '<Image>' + b64 + '</Image>';
						xml += objectLayout + '</ImageObject>\n';
					}
					break;

				case 'line':
					xml += '<ShapeObject><Name>' + escXml(el.id) + '</Name>';
					xml += '<ShapeType>' + ((props.direction || 'horizontal') === 'vertical' ? 'VerticalLine' : 'Line') + '</ShapeType>';
					xml += objectLayout + '</ShapeObject>\n';
					break;
			}
		});

		xml += '</LabelObjects></DynamicLayoutManager>\n';
		xml += '</DYMOLabel><LabelApplication>Blank</LabelApplication>\n';
		xml += '<DataTable><Columns></Columns><Rows></Rows></DataTable>\n';
		xml += '</DesktopLabel>';
		return xml;
	}

	function resolveValue(props, values) {
		if (props.binding && values && values[props.binding] !== undefined && values[props.binding] !== '') {
			return String(values[props.binding]);
		}
		return props.text || props.data || '';
	}

	function escXml(s) {
		return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}

	function capitalize(s) {
		return s.charAt(0).toUpperCase() + s.slice(1);
	}

	// PDF fallback
	if (btnPdf) {
		btnPdf.addEventListener('click', function() {
			// Submit to PDF generation endpoint
			var form = document.createElement('form');
			form.method = 'POST';
			form.action = printData.pdfUrl;
			form.target = '_blank';

			var fields = { id: printData.templateId, token: '<?php echo newToken(); ?>' };
			<?php if (!empty($records)) { ?>
			fields.records_json = JSON.stringify(printData.records);
			<?php } ?>

			for (var k in fields) {
				var inp = document.createElement('input');
				inp.type = 'hidden';
				inp.name = k;
				inp.value = fields[k];
				form.appendChild(inp);
			}

			document.body.appendChild(form);
			form.submit();
			document.body.removeChild(form);
		});
	}
})();
</script>

<?php
print dol_get_fiche_end();
llxFooter();
$db->close();
