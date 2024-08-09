CREATE TABLE IF NOT EXISTS `seo_element_meta_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element_type` varchar(255) NOT NULL,
  `element_id` int(11) NOT NULL,
  `integrator` varchar(255) NOT NULL,
  `data` longtext NOT NULL COMMENT '(DC2Type:array)',
  `release_type` varchar(255) default 'public' not null,
  PRIMARY KEY (`id`),
  UNIQUE KEY `element_type_id_integrator` (`element_type`,`element_id`,`integrator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `seo_queue_entry` (
  `uuid` binary(16) NOT NULL COMMENT '(DC2Type:uuid)',
  `type` varchar(255) NOT NULL,
  `data_type` varchar(255) NOT NULL,
  `data_id` int(11) NOT NULL,
  `data_url` longtext NOT NULL,
  `worker` varchar(255) NOT NULL,
  `resource_processor` varchar(255) NOT NULL,
  `creation_date` datetime NOT NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

