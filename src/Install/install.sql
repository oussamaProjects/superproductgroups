CREATE TABLE
    IF NOT EXISTS `PREFIX_product_group` (
        `id_group` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_super_product` INT (11) UNSIGNED NOT NULL,
        `name` VARCHAR(255) NOT NULL DEFAULT '',
        `image` VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`id_group`)
    ) ENGINE = ENGINE_TYPE DEFAULT CHARSET = utf8;

CREATE TABLE
    IF NOT EXISTS `PREFIX_product_group_relationship` (
        `id_group` INT (11) UNSIGNED NOT NULL,
        `id_product` INT (11) UNSIGNED NOT NULL,
        PRIMARY KEY (`id_group`, `id_product`),
        FOREIGN KEY (`id_group`) REFERENCES `PREFIX_product_group` (`id_group`) ON DELETE CASCADE,
        FOREIGN KEY (`id_product`) REFERENCES `PREFIX_product` (`id_product`) ON DELETE CASCADE
    ) ENGINE = ENGINE_TYPE DEFAULT CHARSET = utf8;
