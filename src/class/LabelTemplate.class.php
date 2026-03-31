<?php
/* Copyright (C) 2025		Jonathan Miller				<jmiller@mokoconsulting.tech>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    mokodolidymo/class/LabelTemplate.class.php
 * \ingroup mokodolidymo
 * \brief   Business class for DYMO label templates
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class LabelTemplate
 *
 * Manages DYMO label templates with JSON-based layout storage.
 */
class LabelTemplate extends CommonObject
{
	/** @var string Element ID for Dolibarr framework */
	public $element = 'mokodolidymo_label';

	/** @var string Table name without prefix */
	public $table_element = 'mokodolidymo_label';

	/** @var string Picto */
	public $picto = 'fa-print';

	/** @var string Ref */
	public $ref;

	/** @var string Label/name of the template */
	public $label;

	/** @var string Description */
	public $description;

	/** @var string Label size code (e.g. '30252', '99012', 'custom') */
	public $label_size;

	/** @var float Label width */
	public $label_width;

	/** @var float Label height */
	public $label_height;

	/** @var string Unit (mm or in) */
	public $unit;

	/** @var string JSON layout data */
	public $layout_json;

	/** @var string Source type: 'designer', 'dymo_import', 'odt_import' */
	public $source_type;

	/** @var string|null Original filename if imported */
	public $source_filename;

	/** @var string Dolibarr object type this template targets */
	public $object_type;

	/** @var int Status (0=draft, 1=active) */
	public $status;

	/** @var int User who created */
	public $fk_user_creat;

	/** @var int|null User who last modified */
	public $fk_user_modif;

	/** @var string Creation date */
	public $date_creation;

	/** @var int Entity */
	public $entity;

	/**
	 * Standard DYMO label sizes: code => [width_mm, height_mm, description]
	 */
	const LABEL_SIZES = array(
		'30252'  => array(88.9, 28.6, 'Address (1-1/8" x 3-1/2")'),
		'30256'  => array(101.6, 58.7, 'Shipping (2-5/16" x 4")'),
		'30334'  => array(57.2, 25.4, 'Multi-purpose (1" x 2-1/8")'),
		'30327'  => array(87.3, 14.3, 'File Folder (9/16" x 3-7/16")'),
		'30857'  => array(101.6, 57.2, 'Name Badge (2-1/4" x 4")'),
		'30336'  => array(25.4, 25.4, 'Small Multi-purpose (1" x 1")'),
		'11354'  => array(57.0, 32.0, 'Multi-purpose (32mm x 57mm)'),
		'99010'  => array(89.0, 28.0, 'Address (28mm x 89mm)'),
		'99012'  => array(89.0, 36.0, 'Large Address (36mm x 89mm)'),
		'99014'  => array(101.0, 54.0, 'Shipping (54mm x 101mm)'),
		'custom' => array(89.0, 36.0, 'Custom Size'),
	);

	/**
	 * Dolibarr object types and their bindable fields
	 */
	const BINDABLE_FIELDS = array(
		'product' => array(
			'product.ref' => 'Product Ref',
			'product.label' => 'Product Label',
			'product.barcode' => 'Product Barcode',
			'product.price' => 'Selling Price',
			'product.price_ttc' => 'Price Inc. Tax',
			'product.weight' => 'Weight',
			'product.description' => 'Description',
			'product.note_public' => 'Public Note',
			'product.fk_barcode_type' => 'Barcode Type',
			'extra.mokodolidymo_label_text' => 'Label Text',
		),
		'thirdparty' => array(
			'thirdparty.nom' => 'Company Name',
			'thirdparty.name_alias' => 'Alias',
			'thirdparty.address' => 'Address',
			'thirdparty.zip' => 'Zip Code',
			'thirdparty.town' => 'City',
			'thirdparty.country' => 'Country',
			'thirdparty.phone' => 'Phone',
			'thirdparty.email' => 'Email',
			'thirdparty.barcode' => 'Barcode',
		),
		'contact' => array(
			'contact.firstname' => 'First Name',
			'contact.lastname' => 'Last Name',
			'contact.address' => 'Address',
			'contact.zip' => 'Zip Code',
			'contact.town' => 'City',
			'contact.country' => 'Country',
			'contact.phone_pro' => 'Phone (Pro)',
			'contact.email' => 'Email',
			'contact.photo' => 'Contact Photo',
		),
		'shipment' => array(
			'shipment.ref' => 'Shipment Ref',
			'shipment.tracking_number' => 'Tracking Number',
			'shipment.date_delivery' => 'Delivery Date',
			'shipment.weight' => 'Weight',
		),
		'warehouse' => array(
			'warehouse.ref' => 'Warehouse Ref',
			'warehouse.label' => 'Warehouse Name',
			'warehouse.lieu' => 'Location / Address',
			'warehouse.description' => 'Description',
			'warehouse.barcode' => 'Barcode',
		),
		'member' => array(
			'member.ref' => 'Member Ref',
			'member.firstname' => 'First Name',
			'member.lastname' => 'Last Name',
			'member.login' => 'Login',
			'member.email' => 'Email',
			'member.phone' => 'Phone',
			'member.address' => 'Address',
			'member.zip' => 'Zip Code',
			'member.town' => 'City',
			'member.country' => 'Country',
			'member.photo' => 'Member Photo',
			'member.type' => 'Member Type',
			'member.datefin' => 'End of Subscription',
		),
	);

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $conf;

		$this->db = $db;
		$this->entity = $conf->entity;
	}

	/**
	 * Create label template in database
	 *
	 * @param  User $user User creating
	 * @return int         >0 if OK, <0 if KO
	 */
	public function create($user)
	{
		global $conf;

		$this->ref = $this->ref ?: $this->getNextNumRef();
		$this->status = $this->status ?: 0;
		$this->entity = $conf->entity;
		$this->fk_user_creat = $user->id;
		$this->date_creation = dol_now();

		$sql = "INSERT INTO ".$this->db->prefix().$this->table_element." (";
		$sql .= "ref, label, description, label_size, label_width, label_height, unit,";
		$sql .= " layout_json, source_type, source_filename, object_type,";
		$sql .= " status, fk_user_creat, date_creation, entity";
		$sql .= ") VALUES (";
		$sql .= "'".$this->db->escape($this->ref)."',";
		$sql .= " '".$this->db->escape($this->label)."',";
		$sql .= " ".($this->description ? "'".$this->db->escape($this->description)."'" : "NULL").",";
		$sql .= " '".$this->db->escape($this->label_size)."',";
		$sql .= " ".((float) $this->label_width).",";
		$sql .= " ".((float) $this->label_height).",";
		$sql .= " '".$this->db->escape($this->unit ?: 'mm')."',";
		$sql .= " ".($this->layout_json ? "'".$this->db->escape($this->layout_json)."'" : "NULL").",";
		$sql .= " '".$this->db->escape($this->source_type ?: 'designer')."',";
		$sql .= " ".($this->source_filename ? "'".$this->db->escape($this->source_filename)."'" : "NULL").",";
		$sql .= " '".$this->db->escape($this->object_type ?: 'product')."',";
		$sql .= " ".((int) $this->status).",";
		$sql .= " ".((int) $this->fk_user_creat).",";
		$sql .= " '".$this->db->idate($this->date_creation)."',";
		$sql .= " ".((int) $this->entity);
		$sql .= ")";

		$this->db->begin();

		$resql = $this->db->query($sql);
		if ($resql) {
			$this->id = $this->db->last_insert_id($this->db->prefix().$this->table_element);
			$this->db->commit();
			return $this->id;
		} else {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load label template from database
	 *
	 * @param  int    $id   Row ID
	 * @param  string $ref  Ref (alternative to ID)
	 * @return int           >0 if OK, 0 if not found, <0 if KO
	 */
	public function fetch($id, $ref = '')
	{
		global $conf;

		$sql = "SELECT rowid, ref, label, description, label_size, label_width, label_height, unit,";
		$sql .= " layout_json, source_type, source_filename, object_type,";
		$sql .= " status, fk_user_creat, fk_user_modif, date_creation, tms, entity";
		$sql .= " FROM ".$this->db->prefix().$this->table_element;
		if ($id > 0) {
			$sql .= " WHERE rowid = ".((int) $id);
		} elseif ($ref) {
			$sql .= " WHERE ref = '".$this->db->escape($ref)."' AND entity = ".((int) $conf->entity);
		} else {
			return -1;
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id              = $obj->rowid;
				$this->ref             = $obj->ref;
				$this->label           = $obj->label;
				$this->description     = $obj->description;
				$this->label_size      = $obj->label_size;
				$this->label_width     = $obj->label_width;
				$this->label_height    = $obj->label_height;
				$this->unit            = $obj->unit;
				$this->layout_json     = $obj->layout_json;
				$this->source_type     = $obj->source_type;
				$this->source_filename = $obj->source_filename;
				$this->object_type     = $obj->object_type;
				$this->status          = $obj->status;
				$this->fk_user_creat   = $obj->fk_user_creat;
				$this->fk_user_modif   = $obj->fk_user_modif;
				$this->date_creation   = $this->db->jdate($obj->date_creation);
				$this->tms             = $obj->tms;
				$this->entity          = $obj->entity;

				$this->db->free($resql);
				return 1;
			} else {
				$this->db->free($resql);
				return 0;
			}
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Update label template in database
	 *
	 * @param  User $user User modifying
	 * @return int         >0 if OK, <0 if KO
	 */
	public function update($user)
	{
		$sql = "UPDATE ".$this->db->prefix().$this->table_element." SET";
		$sql .= " ref = '".$this->db->escape($this->ref)."',";
		$sql .= " label = '".$this->db->escape($this->label)."',";
		$sql .= " description = ".($this->description ? "'".$this->db->escape($this->description)."'" : "NULL").",";
		$sql .= " label_size = '".$this->db->escape($this->label_size)."',";
		$sql .= " label_width = ".((float) $this->label_width).",";
		$sql .= " label_height = ".((float) $this->label_height).",";
		$sql .= " unit = '".$this->db->escape($this->unit)."',";
		$sql .= " layout_json = ".($this->layout_json ? "'".$this->db->escape($this->layout_json)."'" : "NULL").",";
		$sql .= " source_type = '".$this->db->escape($this->source_type)."',";
		$sql .= " source_filename = ".($this->source_filename ? "'".$this->db->escape($this->source_filename)."'" : "NULL").",";
		$sql .= " object_type = '".$this->db->escape($this->object_type)."',";
		$sql .= " status = ".((int) $this->status).",";
		$sql .= " fk_user_modif = ".((int) $user->id);
		$sql .= " WHERE rowid = ".((int) $this->id);

		$this->db->begin();

		$resql = $this->db->query($sql);
		if ($resql) {
			$this->db->commit();
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Delete label template from database
	 *
	 * @param  User $user User deleting
	 * @return int         >0 if OK, <0 if KO
	 */
	public function delete($user)
	{
		$sql = "DELETE FROM ".$this->db->prefix().$this->table_element;
		$sql .= " WHERE rowid = ".((int) $this->id);

		$this->db->begin();

		$resql = $this->db->query($sql);
		if ($resql) {
			$this->db->commit();
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Set status (draft/active)
	 *
	 * @param  User $user    User changing status
	 * @param  int  $status  New status (0=draft, 1=active)
	 * @return int            >0 if OK, <0 if KO
	 */
	public function setStatus($user, $status)
	{
		$this->status = (int) $status;
		return $this->update($user);
	}

	/**
	 * Get decoded layout as PHP array
	 *
	 * @return array Layout data
	 */
	public function getLayout()
	{
		if (empty($this->layout_json)) {
			return array('elements' => array());
		}
		$layout = json_decode($this->layout_json, true);
		return is_array($layout) ? $layout : array('elements' => array());
	}

	/**
	 * Set layout from PHP array
	 *
	 * @param  array $layout Layout data
	 * @return void
	 */
	public function setLayout($layout)
	{
		$this->layout_json = json_encode($layout, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Get default empty layout for a given label size
	 *
	 * @return array Default layout
	 */
	public function getDefaultLayout()
	{
		return array(
			'width' => (float) $this->label_width,
			'height' => (float) $this->label_height,
			'unit' => $this->unit ?: 'mm',
			'elements' => array(),
		);
	}

	/**
	 * Generate next ref number
	 *
	 * @return string Next ref
	 */
	public function getNextNumRef()
	{
		global $conf;

		$sql = "SELECT MAX(CAST(SUBSTRING(ref, 5) AS UNSIGNED)) as maxref";
		$sql .= " FROM ".$this->db->prefix().$this->table_element;
		$sql .= " WHERE ref LIKE 'LBL-%' AND entity = ".((int) $conf->entity);

		$resql = $this->db->query($sql);
		if ($resql) {
			$obj = $this->db->fetch_object($resql);
			$next = ($obj->maxref ? (int) $obj->maxref + 1 : 1);
			return 'LBL-'.sprintf('%04d', $next);
		}
		return 'LBL-0001';
	}

	/**
	 * Get URL to card page
	 *
	 * @param  int    $withpicto  Include picto
	 * @param  string $option     Link option
	 * @return string              HTML link
	 */
	public function getNomUrl($withpicto = 0, $option = '')
	{
		$result = '';
		$label = img_picto('', $this->picto).' <u>Label Template</u>';
		$label .= '<br><b>Ref:</b> '.$this->ref;
		if ($this->label) {
			$label .= '<br>'.$this->label;
		}

		$url = dol_buildpath('/mokodolidymo/label_card.php', 1).'?id='.$this->id;

		$linkstart = '<a href="'.$url.'" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
		$linkend = '</a>';

		$result .= $linkstart;
		if ($withpicto) {
			$result .= img_picto('', $this->picto, 'class="pictofixedwidth"');
		}
		$result .= $this->ref;
		$result .= $linkend;

		return $result;
	}

	/**
	 * Get bindable fields for the configured object type
	 *
	 * @return array Field key => label pairs
	 */
	public function getBindableFields()
	{
		$type = $this->object_type ?: 'product';
		$fields = isset(self::BINDABLE_FIELDS[$type]) ? self::BINDABLE_FIELDS[$type] : array();

		// Add common static fields available to all object types
		$fields['static.text'] = 'Static Text';
		$fields['static.date'] = 'Current Date';
		$fields['static.company'] = 'My Company Name';
		$fields['static.company_logo'] = 'My Company Logo';

		return $fields;
	}

	/**
	 * Resolve bound field values from a Dolibarr object
	 *
	 * @param  CommonObject $object The source object (Product, Societe, etc.)
	 * @return array                 field_key => resolved_value
	 */
	public function resolveFieldValues($object)
	{
		$values = array();
		$fields = $this->getBindableFields();

		foreach (array_keys($fields) as $key) {
			$parts = explode('.', $key, 2);
			$prefix = $parts[0];
			$field = isset($parts[1]) ? $parts[1] : '';

			if ($prefix === 'static') {
				if ($field === 'date') {
					$values[$key] = dol_print_date(dol_now(), 'day');
				} elseif ($field === 'company') {
					global $mysoc;
					$values[$key] = $mysoc->name;
				} elseif ($field === 'company_logo') {
					$values[$key] = self::getCompanyLogoDataUrl();
				} else {
					$values[$key] = '';
				}
			} elseif ($prefix === 'extra' && is_object($object)) {
				// Extrafields: extra.fieldname
				if (property_exists($object, 'array_options') && is_array($object->array_options)) {
					$extra_key = 'options_'.$field;
					$values[$key] = isset($object->array_options[$extra_key]) ? $object->array_options[$extra_key] : '';
				} else {
					$values[$key] = '';
				}
			} elseif ($field === 'photo' && is_object($object) && !empty($object->photo)) {
				// Resolve photo field to a base64 data URL
				$values[$key] = self::getObjectPhotoDataUrl($object, $prefix);
			} elseif (is_object($object) && property_exists($object, $field)) {
				$values[$key] = $object->$field;
			} else {
				$values[$key] = '';
			}
		}

		return $values;
	}

	/**
	 * Get the company logo as a base64 data URL.
	 * Reads from Dolibarr's mysoc logo path.
	 *
	 * @return string Data URL (data:image/...) or empty string
	 */
	public static function getCompanyLogoDataUrl()
	{
		global $conf, $mysoc;

		if (empty($mysoc->logo)) {
			return '';
		}

		$logo_path = $conf->mycompany->dir_output.'/logos/'.$mysoc->logo;
		if (!file_exists($logo_path)) {
			// Try thumb version
			$logo_path = $conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo;
			if (!file_exists($logo_path)) {
				return '';
			}
		}

		$mime_types = array(
			'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
			'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'webp' => 'image/webp',
		);
		$ext = strtolower(pathinfo($logo_path, PATHINFO_EXTENSION));
		$mime = isset($mime_types[$ext]) ? $mime_types[$ext] : 'image/png';

		$data = @file_get_contents($logo_path);
		if ($data === false) {
			return '';
		}

		return 'data:'.$mime.';base64,'.base64_encode($data);
	}

	/**
	 * Get a Dolibarr object's photo as a base64 data URL.
	 * Works for contacts (socpeople), members, users.
	 *
	 * @param  CommonObject $object     The object with a photo property
	 * @param  string       $obj_type   Object type prefix (contact, user, etc.)
	 * @return string                    Data URL or empty string
	 */
	public static function getObjectPhotoDataUrl($object, $obj_type = 'contact')
	{
		global $conf;

		if (empty($object->photo)) {
			return '';
		}

		$photo_path = '';

		switch ($obj_type) {
			case 'contact':
				$dir = !empty($conf->societe->multidir_output[$object->entity])
					? $conf->societe->multidir_output[$object->entity]
					: $conf->societe->dir_output;
				$photo_path = $dir.'/contact/'.get_exdir(0, 0, 0, 0, $object, 'contact').$object->id.'/photos/'.$object->photo;
				break;
			case 'user':
				$photo_path = $conf->user->dir_output.'/'.$object->id.'/photos/'.$object->photo;
				break;
			default:
				return '';
		}

		if (!file_exists($photo_path)) {
			// Try without /photos/ subdirectory
			$alt_path = dirname(dirname($photo_path)).'/'.$object->photo;
			if (file_exists($alt_path)) {
				$photo_path = $alt_path;
			} else {
				return '';
			}
		}

		$mime_types = array(
			'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
			'gif' => 'image/gif', 'webp' => 'image/webp',
		);
		$ext = strtolower(pathinfo($photo_path, PATHINFO_EXTENSION));
		$mime = isset($mime_types[$ext]) ? $mime_types[$ext] : 'image/jpeg';

		$data = @file_get_contents($photo_path);
		if ($data === false) {
			return '';
		}

		return 'data:'.$mime.';base64,'.base64_encode($data);
	}

	/**
	 * Return label for a status
	 *
	 * @param  int $mode Display mode (0=long, 1=short, etc.)
	 * @return string     HTML string with status
	 */
	public function getLibStatut($mode = 0)
	{
		return self::LibStatut($this->status, $mode);
	}

	/**
	 * Return label for a given status
	 *
	 * @param  int $status Status value
	 * @param  int $mode   Display mode
	 * @return string       HTML string
	 */
	public static function LibStatut($status, $mode = 0)
	{
		$statuslabel = array(0 => 'Draft', 1 => 'Active');
		$statusclass = array(0 => 'status0', 1 => 'status4');

		$label = isset($statuslabel[$status]) ? $statuslabel[$status] : 'Unknown';
		$class = isset($statusclass[$status]) ? $statusclass[$status] : 'status0';

		if ($mode == 0) {
			return '<span class="badge badge-'.$class.'">'.$label.'</span>';
		}
		return $label;
	}
}
