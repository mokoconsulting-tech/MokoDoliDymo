-- Copyright (C) 2025 Jonathan Miller <jmiller@mokoconsulting.tech>
-- SPDX-License-Identifier: GPL-3.0-or-later

CREATE TABLE llx_mokodolidymo_label (
	rowid           INTEGER AUTO_INCREMENT PRIMARY KEY NOT NULL,
	ref             VARCHAR(128) NOT NULL,
	label           VARCHAR(255) NOT NULL DEFAULT '',
	description     TEXT,
	label_size      VARCHAR(32) NOT NULL DEFAULT 'custom',
	label_width     DOUBLE(8,2) NOT NULL DEFAULT 89.00,
	label_height    DOUBLE(8,2) NOT NULL DEFAULT 36.00,
	unit            VARCHAR(4) NOT NULL DEFAULT 'mm',
	layout_json     LONGTEXT,
	source_type     VARCHAR(16) DEFAULT 'designer',
	source_filename VARCHAR(255) DEFAULT NULL,
	object_type     VARCHAR(64) DEFAULT 'product',
	status          SMALLINT NOT NULL DEFAULT 0,
	fk_user_creat   INTEGER NOT NULL,
	fk_user_modif   INTEGER DEFAULT NULL,
	date_creation   DATETIME NOT NULL,
	tms             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	import_key      VARCHAR(14) DEFAULT NULL,
	entity          INTEGER NOT NULL DEFAULT 1
) ENGINE=innodb;
