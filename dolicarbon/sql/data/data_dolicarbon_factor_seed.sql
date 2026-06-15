-- DoliCarbon - Default ADEME-style emission factors (approximate 2023 values)
-- INSERT IGNORE avoids errors on module re-activation

INSERT IGNORE INTO llx_dolicarbon_factor (code, label, category, scope, unit_input, kgco2e_per_unit, source, year_ref, active, entity) VALUES
('ADEME_CAR_ESS', 'Voiture essence', 'transport_road', 1, 'km', 0.192, 'ADEME 2023', 2023, 1, 0),
('ADEME_CAR_DIE', 'Voiture diesel', 'transport_road', 1, 'km', 0.171, 'ADEME 2023', 2023, 1, 0),
('ADEME_TRUCK_LT35', 'Camion < 3.5t', 'transport_road', 1, 'km.kg', 0.00102, 'ADEME 2023', 2023, 1, 0),
('ADEME_GAZ_NAT', 'Gaz naturel', 'energy_combustion', 1, 'kWh', 0.227, 'ADEME 2023', 2023, 1, 0),
('ADEME_FIOUL', 'Fioul domestique', 'energy_combustion', 1, 'L', 3.24, 'ADEME 2023', 2023, 1, 0),
('ADEME_ELEC_FR', 'Électricité France', 'electricity', 2, 'kWh', 0.052, 'ADEME 2023', 2023, 1, 0),
('ADEME_ELEC_EU', 'Électricité Europe mix', 'electricity', 2, 'kWh', 0.420, 'ADEME 2023', 2023, 1, 0),
('ADEME_VOL_CC', 'Vol court-courrier', 'transport_air', 3, 'km.passager', 0.255, 'ADEME 2023', 2023, 1, 0),
('ADEME_VOL_LC', 'Vol long-courrier', 'transport_air', 3, 'km.passager', 0.195, 'ADEME 2023', 2023, 1, 0),
('ADEME_TRAIN_FR', 'Train France', 'transport_rail', 3, 'km.passager', 0.00573, 'ADEME 2023', 2023, 1, 0),
('ADEME_DECHETS', 'Déchets ménagers enfouis', 'waste', 3, 'kg', 0.449, 'ADEME 2023', 2023, 1, 0),
('ADEME_EMAIL_PJ', 'Email avec PJ', 'digital', 3, 'unité', 0.019, 'ADEME 2023', 2023, 1, 0),
('ADEME_STREAM', 'Streaming vidéo 1h', 'digital', 3, 'heure', 0.036, 'ADEME 2023', 2023, 1, 0),
('ADEME_ACHAT_EUR', 'Achats de services (ordre de grandeur)', 'purchases_services', 3, 'EUR', 0.12, 'Estimation import', 2023, 1, 0);
