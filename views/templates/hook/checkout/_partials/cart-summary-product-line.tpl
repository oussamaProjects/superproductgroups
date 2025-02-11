{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{block name='cart_summary_product_line'}
  <div class="cart-item cart-item-custom">
    <div class="product-line-grid row no-gutters">
      <div class="col-12 product-line-details">
        <div class="product-image">
          {if $add_product_link}<a href="{$product.url}" target="_blank" rel="noopener noreferrer nofollow">{/if}
            {if $product.default_image}
              <picture>
                {if !empty($product.default_image.bySize.cart_default.sources.avif)}
                <source srcset="{$product.default_image.bySize.cart_default.sources.avif}" type="image/avif">{/if}
                {if !empty($product.default_image.bySize.cart_default.sources.webp)}
                <source srcset="{$product.default_image.bySize.cart_default.sources.webp}" type="image/webp">{/if}
                <img class="media-object img-fluid" src="{$product.default_image.small.url}" alt="{$product.name}"
                  loading="lazy">
              </picture>
            {else}
              <picture>
                {if !empty($urls.no_picture_image.bySize.cart_default.sources.avif)}
                <source srcset="{$urls.no_picture_image.bySize.cart_default.sources.avif}" type="image/avif">{/if}
                {if !empty($urls.no_picture_image.bySize.cart_default.sources.webp)}
                <source srcset="{$urls.no_picture_image.bySize.cart_default.sources.webp}" type="image/webp">{/if}
                <img class="media-object img-fluid" src="{$urls.no_picture_image.bySize.small_default.url}"
                  loading="lazy" />
              </picture>
            {/if}
            {if $add_product_link}
          </a>{/if}
        </div>
        <div class="product-line-grid-body">
          {if $add_product_link}
            <a href="{$product.url}" target="_blank" rel="noopener noreferrer nofollow">
            {/if}
            <span class="product-name">{$product.name}</span>
            {if isset($product.super_product_id) && $product.super_product_id > 0}
              <br><small class="text-muted">
                {l s='Part of:' d='Modules.SuperProductGroups.Shop'} <strong>{$product.super_product_name}</strong>
              </small>
            {/if}
            {if $add_product_link}
            </a>
          {/if}
          <div class="product-prices">
            <span class="price">{$product.price}</span>
            <span class="qty px-1">x</span>
            <span class="qty">{$product.quantity}</span>
          </div>
          {if $product.attributes}
            <div class="product-line-info-wrapper product-attributes">
              <span><i>{' + '|implode:$product.attributes}</i></span>
            </div>
          {/if}

          {if is_array($product.customizations) && $product.customizations|count && $modal}
            {include file='catalog/_partials/product-customization-modal.tpl' product=$product}
          {/if}

          {hook h='displayProductPriceBlock' product=$product type="unit_price"}
        </div>
      </div>
      <div class="product-line-actions">
        <div class="price-col">
          <span class="price product-price">{$product.total}</span>
        </div>
      </div>
    </div>
  </div>
{/block}