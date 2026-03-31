<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    mokodolidymo/core/hooks/HookMokoDoliDymo.class.php
 * \ingroup mokodolidymo
 * \brief   Hook class to inject Print Label buttons on Dolibarr object cards
 */

/**
 * Class HookMokoDoliDymo
 *
 * Hooks into product, thirdparty, contact, and shipment cards to add
 * a "Print Label" button in the actions bar.
 */
class HookMokoDoliDymo
{
	/** @var DoliDB */
	public $db;

	/** @var string */
	public $error = '';

	/** @var string[] */
	public $errors = array();

	/** @var string[] Accumulated action button HTML */
	public $resprints = '';

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Add "Print Label" button in the actions bar of object cards.
	 * Called by hook 'addMoreActionsButtons'.
	 *
	 * @param  array       $parameters Hook parameters
	 * @param  CommonObject $object    Current object
	 * @param  string      $action     Current action
	 * @return int                      0=OK, <0=KO
	 */
	public function addMoreActionsButtons($parameters, &$object, &$action)
	{
		global $user, $langs;

		$langs->load('mokodolidymo@mokodolidymo');

		if (!$user->hasRight('mokodolidymo', 'label', 'print')) {
			return 0;
		}

		$contexts = explode(':', $parameters['context']);

		// Map Dolibarr hook contexts to our object types
		$context_map = array(
			'productcard' => 'product',
			'thirdpartycard' => 'thirdparty',
			'contactcard' => 'contact',
			'expeditioncard' => 'shipment',
			'deliverycard' => 'shipment',
		);

		$object_type = '';
		foreach ($contexts as $ctx) {
			if (isset($context_map[$ctx])) {
				$object_type = $context_map[$ctx];
				break;
			}
		}

		if (empty($object_type) || empty($object->id)) {
			return 0;
		}

		// Check if any active label templates exist for this object type
		$sql = "SELECT COUNT(*) as cnt FROM ".$this->db->prefix()."mokodolidymo_label";
		$sql .= " WHERE object_type = '".$this->db->escape($object_type)."'";
		$sql .= " AND status = 1";
		$sql .= " AND entity IN (".getEntity('mokodolidymo_label').")";
		$resql = $this->db->query($sql);
		$has_templates = false;
		if ($resql) {
			$obj = $this->db->fetch_object($resql);
			$has_templates = ($obj->cnt > 0);
		}

		if (!$has_templates) {
			return 0;
		}

		// Build the print URL — links to template selection then print
		$print_url = dol_buildpath('/mokodolidymo/label_select.php', 1);
		$print_url .= '?object_type='.urlencode($object_type);
		$print_url .= '&object_id='.((int) $object->id);

		$this->resprints .= '<a class="butAction" href="'.$print_url.'">';
		$this->resprints .= img_picto('', 'fa-print', 'class="pictofixedwidth"');
		$this->resprints .= $langs->trans("PrintLabel");
		$this->resprints .= '</a>';

		return 0;
	}

	/**
	 * Add content to the end of the tab bar on object cards.
	 * Called by hook 'completeTabsHead'.
	 *
	 * @param  array  $parameters Hook parameters (contains 'head', 'object')
	 * @param  object $object     Current object
	 * @param  string $action     Current action
	 * @return int                 0=OK
	 */
	public function completeTabsHead($parameters, &$object, &$action)
	{
		// Tabs are handled via $this->tabs in the module descriptor
		return 0;
	}
}
