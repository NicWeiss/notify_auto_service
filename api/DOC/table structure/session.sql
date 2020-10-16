CREATE TABLE `notifier`.`session` (
                                      `id` INT NOT NULL AUTO_INCREMENT,
                                      `user_id` INT NULL,
                                      `session` VARCHAR(45) NULL,
                                      `expire_at` DOUBLE NULL,
                                      PRIMARY KEY (`id`));
