<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    mokodolidymo/label_pdf.php
 * \ingroup mokodolidymo
 * \brief   PDF fallback label generation using TCPDF
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once __DIR__.'/class/LabelTemplate.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Translate $langs
 * @var User $user
 */

if (!$user->hasRight('mokodolidymo', 'label', 'print')) {
	accessforbidden();
}

$id = GETPOSTINT('id');
$records_json = GETPOST('records_json', 'none');

$object = new LabelTemplate($db);
$object->fetch($id);
if (!$object->id) {
	die('Label template not found');
}

$layout = $object->getLayout();
$records = array();

if ($records_json) {
	$decoded = json_decode($records_json, true);
	if (is_array($decoded)) {
		$records = $decoded;
	}
}

// If no records provided, generate a specimen label
if (empty($records)) {
	$records[] = array(
		'id' => 0,
		'label' => 'Specimen',
		'values' => array(),
	);
}

// ── Generate PDF using TCPDF ──────────────────────────────

$width_mm = (float) $object->label_width;
$height_mm = (float) $object->label_height;

// Create custom page size matching the label
$pdf = pdf_getInstance(array($width_mm, $height_mm), 'mm', 'L');
$pdf->SetCreator('MokoDoliDymo');
$pdf->SetAuthor('Dolibarr');
$pdf->SetTitle('Label - '.$object->ref);
$pdf->SetAutoPageBreak(false, 0);
$pdf->SetMargins(0, 0, 0);

foreach ($records as $record) {
	$values = isset($record['values']) ? $record['values'] : array();

	$pdf->AddPage('L', array($width_mm, $height_mm));

	foreach ($layout['elements'] as $element) {
		$props = isset($element['properties']) ? $element['properties'] : array();
		$x = isset($element['x']) ? (float) $element['x'] : 0;
		$y = isset($element['y']) ? (float) $element['y'] : 0;
		$w = isset($element['width']) ? (float) $element['width'] : 20;
		$h = isset($element['height']) ? (float) $element['height'] : 10;

		// Resolve data binding
		$text = '';
		if (!empty($props['binding']) && isset($values[$props['binding']]) && $values[$props['binding']] !== '') {
			$text = $values[$props['binding']];
		} elseif (isset($props['text'])) {
			$text = $props['text'];
		} elseif (isset($props['data'])) {
			$text = $props['data'];
		}

		switch ($element['type']) {
			case 'text':
				$font_size = isset($props['fontSize']) ? (int) $props['fontSize'] : 12;
				$font_style = ($props['fontWeight'] ?? '') === 'bold' ? 'B' : '';
				$align_map = array('left' => 'L', 'center' => 'C', 'right' => 'R');
				$align = isset($align_map[$props['textAlign'] ?? '']) ? $align_map[$props['textAlign']] : 'L';

				$pdf->SetFont('helvetica', $font_style, $font_size);
				$pdf->SetXY($x, $y);
				$pdf->Cell($w, $h, $text, 0, 0, $align, false, '', 0, false, 'T', 'M');
				break;

			case 'barcode':
				$format = $props['format'] ?? 'CODE128';
				$barcode_type = 'C128';
				$barcode_map = array(
					'CODE128' => 'C128', 'EAN13' => 'EAN13', 'EAN8' => 'EAN8',
					'UPCA' => 'UPCA', 'CODE39' => 'C39', 'ITF14' => 'I25',
				);
				if (isset($barcode_map[$format])) {
					$barcode_type = $barcode_map[$format];
				}

				if (!empty($text)) {
					$barcode_h = ($props['showText'] ?? true) ? $h * 0.7 : $h;
					$pdf->write1DBarcode($text, $barcode_type, $x, $y, $w, $barcode_h, 0.4, array(), 'N');

					if ($props['showText'] ?? true) {
						$pdf->SetFont('helvetica', '', 7);
						$pdf->SetXY($x, $y + $barcode_h);
						$pdf->Cell($w, $h - $barcode_h, $text, 0, 0, 'C');
					}
				}
				break;

			case 'qrcode':
				if (!empty($text)) {
					$size = min($w, $h);
					$pdf->write2DBarcode($text, 'QRCODE,L', $x, $y, $size, $size);
				}
				break;

			case 'image':
				// Resolve image source: bound value (e.g. company logo) or static src
				$img_data = '';
				if (!empty($props['binding']) && isset($values[$props['binding']]) && $values[$props['binding']] !== '') {
					$img_data = $values[$props['binding']];
				} elseif (!empty($props['src'])) {
					$img_data = $props['src'];
				}

				if (!empty($img_data) && preg_match('/^data:image\/(\w+);base64,(.+)$/', $img_data, $matches)) {
					$img_ext = $matches[1];
					$img_raw = base64_decode($matches[2]);
					$tmp_file = tempnam(sys_get_temp_dir(), 'mdd_img_').'.'.$img_ext;
					file_put_contents($tmp_file, $img_raw);
					$pdf->Image($tmp_file, $x, $y, $w, $h, '', '', '', true, 300, '', false, false, 0, 'CM');
					@unlink($tmp_file);
				}
				break;

			case 'line':
				$color = $props['color'] ?? '#000000';
				$r = hexdec(substr($color, 1, 2));
				$g = hexdec(substr($color, 3, 2));
				$b = hexdec(substr($color, 5, 2));
				$pdf->SetDrawColor($r, $g, $b);
				$thickness = $props['thickness'] ?? 1;
				$pdf->SetLineWidth($thickness * 0.3);

				if (($props['direction'] ?? 'horizontal') === 'horizontal') {
					$pdf->Line($x, $y + $h / 2, $x + $w, $y + $h / 2);
				} else {
					$pdf->Line($x + $w / 2, $y, $x + $w / 2, $y + $h);
				}
				break;
		}
	}
}

// Output PDF
$pdf->Output('label_'.$object->ref.'.pdf', 'I');
