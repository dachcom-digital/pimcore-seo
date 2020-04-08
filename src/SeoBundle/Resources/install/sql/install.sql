CREATE TABLE IF NOT EXISTS `seo_queue_entry` (
  `uuid` varchar(255) NOT NULL,
  `data_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `data_type` varchar(255) NOT NULL,
  `data_url` longtext NOT NULL,
  `creation_date` datetime NOT NULL,
  `worker` varchar(255) NOT NULL,
  `resource_processor` varchar(255) NOT NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;