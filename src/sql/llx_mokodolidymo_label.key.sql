-- Copyright (C) 2025 Jonathan Miller <jmiller@mokoconsulting.tech>
-- SPDX-License-Identifier: GPL-3.0-or-later

ALTER TABLE llx_mokodolidymo_label ADD UNIQUE INDEX uk_mokodolidymo_label_ref (ref, entity);
ALTER TABLE llx_mokodolidymo_label ADD INDEX idx_mokodolidymo_label_status (status);
ALTER TABLE llx_mokodolidymo_label ADD INDEX idx_mokodolidymo_label_fk_user_creat (fk_user_creat);
ALTER TABLE llx_mokodolidymo_label ADD CONSTRAINT fk_mokodolidymo_label_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
