CREATE TABLE IF NOT EXISTS `llx_cryptodepositaddresses` (
  `rowid` int(11) NOT NULL,
  `asset` varchar(64) NOT NULL,
  `address` varchar(128) DEFAULT NULL,
  `network` varchar(64) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
--
-- Index pour la table `llx_cryptodepositaddresses`
--
ALTER TABLE `llx_cryptodepositaddresses`
  ADD PRIMARY KEY (`rowid`);

--
-- AUTO_INCREMENT pour la table `llx_cryptodepositaddresses`
--
ALTER TABLE `llx_cryptodepositaddresses`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;
