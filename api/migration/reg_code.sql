CREATE TABLE `notifier`.`reg_codes` (
                                        `id` INT NOT NULL AUTO_INCREMENT,
                                        `email` VARCHAR(45) NULL,
                                        `code` VARCHAR(45) NULL,
                                        `expire_at` DOUBLE NULL,
                                        PRIMARY KEY (`id`));
