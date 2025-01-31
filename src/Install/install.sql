CREATE TABLE
    IF NOT EXISTS `PREFIX_product_group` (
        `id_group` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_super_product` INT (11) UNSIGNED NOT NULL,
        `name` VARCHAR(255) NOT NULL DEFAULT '',
        `image` VARCHAR(255) NOT NULL DEFAULT '',
        `group_order` INT (11) UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (`id_group`)
    ) ENGINE = ENGINE_TYPE DEFAULT CHARSET = utf8;

CREATE TABLE
    IF NOT EXISTS `PREFIX_product_group_relationship` (
        `id_group` INT (11) UNSIGNED NOT NULL,
        `id_product` INT (11) UNSIGNED NOT NULL,
        `product_order` INT (11) UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (`id_group`, `id_product`),
        FOREIGN KEY (`id_group`) REFERENCES `PREFIX_product_group` (`id_group`) ON DELETE CASCADE,
        FOREIGN KEY (`id_product`) REFERENCES `PREFIX_product` (`id_product`) ON DELETE CASCADE
    ) ENGINE = ENGINE_TYPE DEFAULT CHARSET = utf8;

CREATE TABLE
    IF NOT EXISTS `PREFIX_superproduct_order` (
        `id_superproduct_order` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_order` INT(11) UNSIGNED NOT NULL,
        `id_super_product` INT(11) UNSIGNED NOT NULL,
        `associated_products` TEXT NOT NULL,
        PRIMARY KEY (`id_superproduct_order`),
        INDEX (`id_order`, `id_super_product`)
    ) ENGINE = ENGINE_TYPE DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_superproduct_cart_custom_fields` (
    `id_cart_custom_field` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cart` INT(11) UNSIGNED NOT NULL,
    `id_product` INT(11) UNSIGNED NOT NULL,
    `custom_fields` TEXT NOT NULL,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_cart_custom_field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
