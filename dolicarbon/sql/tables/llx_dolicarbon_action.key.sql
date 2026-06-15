-- DoliCarbon - Indexes for actions

ALTER TABLE llx_dolicarbon_action ADD INDEX idx_dolicarbon_action_fk_bilan (fk_bilan);
ALTER TABLE llx_dolicarbon_action ADD INDEX idx_dolicarbon_action_status (status);
