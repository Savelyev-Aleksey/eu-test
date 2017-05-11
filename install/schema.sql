-- Create users table
CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_UNIQUE` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- Create goods table
CREATE TABLE `goods` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- Create reviews table
CREATE TABLE `good_reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `good_id` int(11) unsigned DEFAULT NULL,
  `rate` tinyint(1) unsigned NOT NULL,
  `comment` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_good_id_UNIQUE` (`user_id`,`good_id`),
  KEY `fk_goods_users_idx` (`user_id`),
  KEY `fk_goods_goods_idx` (`good_id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;
