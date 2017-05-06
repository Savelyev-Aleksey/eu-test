-- Create users table
CREATE TABLE `eu-test`.`users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `login_UNIQUE` (`login` ASC));

-- Create goods table
CREATE TABLE `eu-test`.`goods` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC));

-- Create reviews table
CREATE TABLE `eu-test`.`goods_reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL,
  `good_id` INT NULL,
  `rate` TINYINT(1) UNSIGNED NULL,
  `comment` TEXT(2000) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_goods_users_idx` (`user_id` ASC),
  INDEX `fk_goods_goods_idx` (`good_id` ASC),
  UNIQUE INDEX `user_good_id_UNIQUE` (`user_id` ASC, `good_id` ASC),
  CONSTRAINT `fk_review_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `eu-test`.`users` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_review_goods`
    FOREIGN KEY (`good_id`)
    REFERENCES `eu-test`.`goods` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION);
