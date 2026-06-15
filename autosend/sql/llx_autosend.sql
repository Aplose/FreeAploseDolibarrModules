CREATE TABLE `llx_autosend` (
  `rowid` int(11) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `ftp_host` varchar(255) NOT NULL,
  `ftp_port` int(11) NOT NULL DEFAULT 21,
  `ftp_user` varchar(255) NOT NULL,
  `ftp_password` varchar(255) NOT NULL,
  `ftp_directory` varchar(255) DEFAULT NULL,
  `ftp_mode` varchar(5) NOT NULL DEFAULT 'FTP',
  `file_regex` varchar(255) DEFAULT NULL,
  `file_rename_rule` varchar(255) DEFAULT NULL,
  `object_status` smallint(6) NOT NULL DEFAULT 1,
  `auto` tinyint(1) NOT NULL DEFAULT 1,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `llx_autosend`
--
ALTER TABLE `llx_autosend`
  ADD PRIMARY KEY (`rowid`),
  ADD UNIQUE KEY `uk_autosend_code` (`code`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `llx_autosend`
--
ALTER TABLE `llx_autosend`
  MODIFY `rowid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

