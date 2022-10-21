DROP TABLE IF EXISTS `twitter`;
DROP TABLE IF EXISTS `linkedin`;
DROP TABLE IF EXISTS `relationship`;

CREATE TABLE `linkedin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company` varchar(64) NOT NULL  DEFAULT '',
  `website` varchar(256) NOT NULL  DEFAULT '',
  `company_linkedin` varchar(256) NOT NULL  DEFAULT '',
  `address` varchar(256) NOT NULL  DEFAULT '',
  `zip` int(11) NOT NULL  DEFAULT 0,
  `first_name` varchar(32) NOT NULL  DEFAULT '',
  `last_name` varchar(32) NOT NULL  DEFAULT '',
  `title` varchar(64) NOT NULL  DEFAULT '',
  `email` varchar(64) NOT NULL  DEFAULT '',
  `linkedin_profile` varchar(64) NOT NULL  DEFAULT '',
  `industry_type` varchar(64) NOT NULL  DEFAULT '',
  `company_size` tinyint(1) NOT NULL COMMENT '1: 1-10,2:11-50,3:...',
  `imported_at` timestamp DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

CREATE TABLE `twitter` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `name_md5` varchar(32) NOT NULL,
  `screen_name` varchar(64) NOT NULL,
  `profile_image_url_https` varchar(256) NOT NULL DEFAULT '',
  `default_profile_image` tinyint(1) NOT NULL,
  `profile_banner_url` tinyint(1) NOT NULL,
  `location` varchar(64) NOT NULL DEFAULT '',
  `url` varchar(64) NOT NULL DEFAULT '',
  `description` varchar(256) NOT NULL DEFAULT '',
  `protected` tinyint(1) NOT NULL,
  `verified` tinyint(1) NOT NULL,
  `is_translator` tinyint(1) NOT NULL,
  `followers_count` int(11) NOT NULL,
  `friends_count` int(11) NOT NULL,
  `listed_count` int(11) NOT NULL,
  `favourites_count` int(11) NOT NULL,
  `statuses_count` int(11) NOT NULL,
  `translator_type` varchar(32) NOT NULL DEFAULT '',
  `phone` varchar(16) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `imported_at` timestamp default current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_email` (`email`(13)) USING BTREE,
  KEY `idx_name_md` (`name_md5`(4)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

CREATE TABLE `relationship` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `twitter_id` int(11) unsigned NOT NULL,
  `linkedin_id` int(11) unsigned NOT NULL,
  `first_name` varchar(32) NOT NULL,
  `last_name` varchar(32) NOT NULL,
  `email` varchar(64) NOT NULL,
  `imported_at` timestamp default current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_twitter_id` (`twitter_id`) USING BTREE,
  KEY `idx_linkedin_id` (`linkedin_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;