<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    mokodolidymo/class/DefaultTemplates.class.php
 * \ingroup mokodolidymo
 * \brief   Default label template definitions installed on module activation
 */

/**
 * Class DefaultTemplates
 *
 * Provides factory methods to create default label templates.
 * Called from modMokoDoliDymo::init() on first activation.
 */
class DefaultTemplates
{
	/**
	 * Install all default templates if they don't already exist
	 *
	 * @param  DoliDB $db   Database handler
	 * @param  int    $user_id User ID for fk_user_creat
	 * @return int           Number of templates created
	 */
	public static function install($db, $user_id)
	{
		$templates = self::getAll();
		$created = 0;

		foreach ($templates as $tpl) {
			// Check if already exists by ref
			$sql = "SELECT rowid FROM ".$db->prefix()."mokodolidymo_label";
			$sql .= " WHERE ref = '".$db->escape($tpl['ref'])."'";
			$resql = $db->query($sql);
			if ($resql && $db->num_rows($resql) > 0) {
				continue; // Already exists, skip
			}

			$sql = "INSERT INTO ".$db->prefix()."mokodolidymo_label (";
			$sql .= "ref, label, description, label_size, label_width, label_height, unit,";
			$sql .= " layout_json, source_type, object_type, status, fk_user_creat, date_creation, entity";
			$sql .= ") VALUES (";
			$sql .= "'".$db->escape($tpl['ref'])."',";
			$sql .= " '".$db->escape($tpl['label'])."',";
			$sql .= " '".$db->escape($tpl['description'])."',";
			$sql .= " '".$db->escape($tpl['label_size'])."',";
			$sql .= " ".((float) $tpl['label_width']).",";
			$sql .= " ".((float) $tpl['label_height']).",";
			$sql .= " 'mm',";
			$sql .= " '".$db->escape(json_encode($tpl['layout'], JSON_UNESCAPED_UNICODE))."',";
			$sql .= " 'designer',";
			$sql .= " '".$db->escape($tpl['object_type'])."',";
			$sql .= " 1,"; // Active status
			$sql .= " ".((int) $user_id).",";
			$sql .= " NOW(),";
			$sql .= " 1";
			$sql .= ")";

			$resql = $db->query($sql);
			if ($resql) {
				$created++;
			}
		}

		return $created;
	}

	/**
	 * Get all default template definitions
	 *
	 * @return array Array of template definitions
	 */
	public static function getAll()
	{
		return array(
			self::fileFolderThirdParty(),
			self::fileFolderThirdPartyCompact(),
			self::productBarcodeLabel(),
			self::productShelfLabel(),
			self::addressLabelThirdParty(),
			self::shippingLabel(),
		);
	}

	// ── File Folder Labels (Third Parties) ────────────────────

	/**
	 * File folder label — third party name, large and bold
	 * DYMO 30327: 87.3mm x 14.3mm
	 */
	private static function fileFolderThirdParty()
	{
		return array(
			'ref' => 'LBL-DF01',
			'label' => 'File Folder - Third Party',
			'description' => 'File folder tab label with company name. DYMO 30327.',
			'label_size' => '30327',
			'label_width' => 87.3,
			'label_height' => 14.3,
			'object_type' => 'thirdparty',
			'layout' => array(
				'elements' => array(
					array(
						'id' => 'elem_1',
						'type' => 'text',
						'x' => 2,
						'y' => 1,
						'width' => 83,
						'height' => 12,
						'properties' => array(
							'text' => '',
							'fontSize' => 18,
							'fontWeight' => 'bold',
							'textAlign' => 'left',
							'binding' => 'thirdparty.nom',
						),
					),
				),
			),
		);
	}

	/**
	 * File folder label — third party name + alias on two lines
	 * DYMO 30327: 87.3mm x 14.3mm
	 */
	private static function fileFolderThirdPartyCompact()
	{
		return array(
			'ref' => 'LBL-DF02',
			'label' => 'File Folder - Third Party (with alias)',
			'description' => 'File folder tab with company name and alias. DYMO 30327.',
			'label_size' => '30327',
			'label_width' => 87.3,
			'label_height' => 14.3,
			'object_type' => 'thirdparty',
			'layout' => array(
				'elements' => array(
					array(
						'id' => 'elem_1',
						'type' => 'text',
						'x' => 2,
						'y' => 0.5,
						'width' => 83,
						'height' => 8,
						'properties' => array(
							'text' => '',
							'fontSize' => 14,
							'fontWeight' => 'bold',
							'textAlign' => 'left',
							'binding' => 'thirdparty.nom',
						),
					),
					array(
						'id' => 'elem_2',
						'type' => 'text',
						'x' => 2,
						'y' => 8,
						'width' => 83,
						'height' => 5.5,
						'properties' => array(
							'text' => '',
							'fontSize' => 9,
							'fontWeight' => 'normal',
							'textAlign' => 'left',
							'binding' => 'thirdparty.name_alias',
						),
					),
				),
			),
		);
	}

	// ── Product Labels ────────────────────────────────────────

	/**
	 * Product barcode label — ref + barcode + price
	 * DYMO 30334: 57.2mm x 25.4mm
	 */
	private static function productBarcodeLabel()
	{
		return array(
			'ref' => 'LBL-DP01',
			'label' => 'Product Barcode Label',
			'description' => 'Product ref, barcode, and price. DYMO 30334 multi-purpose.',
			'label_size' => '30334',
			'label_width' => 57.2,
			'label_height' => 25.4,
			'object_type' => 'product',
			'layout' => array(
				'elements' => array(
					array(
						'id' => 'elem_1',
						'type' => 'text',
						'x' => 2,
						'y' => 1,
						'width' => 53,
						'height' => 5,
						'properties' => array(
							'text' => '',
							'fontSize' => 10,
							'fontWeight' => 'bold',
							'textAlign' => 'left',
							'binding' => 'product.ref',
						),
					),
					array(
						'id' => 'elem_2',
						'type' => 'barcode',
						'x' => 2,
						'y' => 7,
						'width' => 40,
						'height' => 12,
						'properties' => array(
							'data' => '',
							'format' => 'CODE128',
							'showText' => true,
							'binding' => 'product.barcode',
						),
					),
					array(
						'id' => 'elem_3',
						'type' => 'text',
						'x' => 2,
						'y' => 20,
						'width' => 53,
						'height' => 4.5,
						'properties' => array(
							'text' => '',
							'fontSize' => 9,
							'fontWeight' => 'normal',
							'textAlign' => 'right',
							'binding' => 'product.price_ttc',
						),
					),
				),
			),
		);
	}

	/**
	 * Product shelf label — ref, label, price in compact layout
	 * DYMO 30336: 25.4mm x 25.4mm (small square)
	 */
	private static function productShelfLabel()
	{
		return array(
			'ref' => 'LBL-DP02',
			'label' => 'Product Shelf Label',
			'description' => 'Compact shelf label with ref, name, and price. DYMO 30336 small multi-purpose.',
			'label_size' => '30336',
			'label_width' => 25.4,
			'label_height' => 25.4,
			'object_type' => 'product',
			'layout' => array(
				'elements' => array(
					array(
						'id' => 'elem_1',
						'type' => 'text',
						'x' => 1,
						'y' => 1,
						'width' => 23,
						'height' => 5,
						'properties' => array(
							'text' => '',
							'fontSize' => 7,
							'fontWeight' => 'bold',
							'textAlign' => 'center',
							'binding' => 'product.ref',
						),
					),
					array(
						'id' => 'elem_2',
						'type' => 'text',
						'x' => 1,
						'y' => 6,
						'width' => 23,
						'height' => 8,
						'properties' => array(
							'text' => '',
							'fontSize' => 6,
							'fontWeight' => 'normal',
							'textAlign' => 'center',
							'binding' => 'product.label',
						),
					),
					array(
						'id' => 'elem_3',
						'type' => 'line',
						'x' => 2,
						'y' => 15,
						'width' => 21,
						'height' => 0.5,
						'properties' => array(
							'direction' => 'horizontal',
							'thickness' => 0.5,
							'color' => '#000000',
						),
					),
					array(
						'id' => 'elem_4',
						'type' => 'text',
						'x' => 1,
						'y' => 16,
						'width' => 23,
						'height' => 8,
						'properties' => array(
							'text' => '',
							'fontSize' => 14,
							'fontWeight' => 'bold',
							'textAlign' => 'center',
							'binding' => 'product.price_ttc',
						),
					),
				),
			),
		);
	}

	// ── Address / Mailing Labels ──────────────────────────────

	/**
	 * Address label — third party full address
	 * DYMO 30252: 88.9mm x 28.6mm
	 */
	private static function addressLabelThirdParty()
	{
		return array(
			'ref' => 'LBL-DA01',
			'label' => 'Address Label - Third Party',
			'description' => 'Full mailing address label. DYMO 30252 address.',
			'label_size' => '30252',
			'label_width' => 88.9,
			'label_height' => 28.6,
			'object_type' => 'thirdparty',
			'layout' => array(
				'elements' => array(
					array(
						'id' => 'elem_1',
						'type' => 'text',
						'x' => 3,
						'y' => 1.5,
						'width' => 83,
						'height' => 6,
						'properties' => array(
							'text' => '',
							'fontSize' => 11,
							'fontWeight' => 'bold',
							'textAlign' => 'left',
							'binding' => 'thirdparty.nom',
						),
					),
					array(
						'id' => 'elem_2',
						'type' => 'text',
						'x' => 3,
						'y' => 8,
						'width' => 83,
						'height' => 5,
						'properties' => array(
							'text' => '',
							'fontSize' => 9,
							'fontWeight' => 'normal',
							'textAlign' => 'left',
							'binding' => 'thirdparty.address',
						),
					),
					array(
						'id' => 'elem_3',
						'type' => 'text',
						'x' => 3,
						'y' => 13.5,
						'width' => 30,
						'height' => 5,
						'properties' => array(
							'text' => '',
							'fontSize' => 9,
							'fontWeight' => 'normal',
							'textAlign' => 'left',
							'binding' => 'thirdparty.zip',
						),
					),
					array(
						'id' => 'elem_4',
						'type' => 'text',
						'x' => 20,
						'y' => 13.5,
						'width' => 50,
						'height' => 5,
						'properties' => array(
							'text' => '',
							'fontSize' => 9,
							'fontWeight' => 'normal',
							'textAlign' => 'left',
							'binding' => 'thirdparty.town',
						),
					),
					array(
						'id' => 'elem_5',
						'type' => 'text',
						'x' => 3,
						'y' => 19,
						'width' => 83,
						'height' => 5,
						'properties' => array(
							'text' => '',
							'fontSize' => 9,
							'fontWeight' => 'normal',
							'textAlign' => 'left',
							'binding' => 'thirdparty.country',
						),
					),
				),
			),
		);
	}

	// ── Shipping Labels ───────────────────────────────────────

	/**
	 * Shipping label — large format with address and tracking barcode
	 * DYMO 30256: 101.6mm x 58.7mm
	 */
	private static function shippingLabel()
	{
		return array(
			'ref' => 'LBL-DS01',
			'label' => 'Shipping Label',
			'description' => 'Shipping label with sender, recipient, and tracking barcode. DYMO 30256.',
			'label_size' => '30256',
			'label_width' => 101.6,
			'label_height' => 58.7,
			'object_type' => 'shipment',
			'layout' => array(
				'elements' => array(
					// Sender (top-left, small)
					array(
						'id' => 'elem_1',
						'type' => 'text',
						'x' => 3,
						'y' => 2,
						'width' => 45,
						'height' => 4,
						'properties' => array(
							'text' => 'FROM:',
							'fontSize' => 7,
							'fontWeight' => 'bold',
							'textAlign' => 'left',
							'binding' => '',
						),
					),
					array(
						'id' => 'elem_2',
						'type' => 'text',
						'x' => 3,
						'y' => 6,
						'width' => 45,
						'height' => 4,
						'properties' => array(
							'text' => '',
							'fontSize' => 7,
							'fontWeight' => 'normal',
							'textAlign' => 'left',
							'binding' => 'static.company',
						),
					),
					// Divider
					array(
						'id' => 'elem_3',
						'type' => 'line',
						'x' => 3,
						'y' => 12,
						'width' => 95,
						'height' => 0.5,
						'properties' => array(
							'direction' => 'horizontal',
							'thickness' => 0.5,
							'color' => '#000000',
						),
					),
					// Shipment ref (top-right)
					array(
						'id' => 'elem_4',
						'type' => 'text',
						'x' => 55,
						'y' => 2,
						'width' => 43,
						'height' => 5,
						'properties' => array(
							'text' => '',
							'fontSize' => 9,
							'fontWeight' => 'bold',
							'textAlign' => 'right',
							'binding' => 'shipment.ref',
						),
					),
					// TO label
					array(
						'id' => 'elem_5',
						'type' => 'text',
						'x' => 8,
						'y' => 15,
						'width' => 10,
						'height' => 5,
						'properties' => array(
							'text' => 'TO:',
							'fontSize' => 9,
							'fontWeight' => 'bold',
							'textAlign' => 'left',
							'binding' => '',
						),
					),
					// Recipient name (large)
					array(
						'id' => 'elem_6',
						'type' => 'text',
						'x' => 18,
						'y' => 15,
						'width' => 78,
						'height' => 7,
						'properties' => array(
							'text' => 'Recipient Name',
							'fontSize' => 14,
							'fontWeight' => 'bold',
							'textAlign' => 'left',
							'binding' => '',
						),
					),
					// Address lines
					array(
						'id' => 'elem_7',
						'type' => 'text',
						'x' => 18,
						'y' => 23,
						'width' => 78,
						'height' => 5,
						'properties' => array(
							'text' => 'Street Address',
							'fontSize' => 10,
							'fontWeight' => 'normal',
							'textAlign' => 'left',
							'binding' => '',
						),
					),
					array(
						'id' => 'elem_8',
						'type' => 'text',
						'x' => 18,
						'y' => 28.5,
						'width' => 78,
						'height' => 5,
						'properties' => array(
							'text' => 'City, State ZIP',
							'fontSize' => 10,
							'fontWeight' => 'normal',
							'textAlign' => 'left',
							'binding' => '',
						),
					),
					// Tracking barcode (bottom)
					array(
						'id' => 'elem_9',
						'type' => 'barcode',
						'x' => 10,
						'y' => 38,
						'width' => 60,
						'height' => 14,
						'properties' => array(
							'data' => '',
							'format' => 'CODE128',
							'showText' => true,
							'binding' => 'shipment.tracking_number',
						),
					),
					// Date
					array(
						'id' => 'elem_10',
						'type' => 'text',
						'x' => 72,
						'y' => 52,
						'width' => 26,
						'height' => 4,
						'properties' => array(
							'text' => '',
							'fontSize' => 7,
							'fontWeight' => 'normal',
							'textAlign' => 'right',
							'binding' => 'static.date',
						),
					),
				),
			),
		);
	}
}
