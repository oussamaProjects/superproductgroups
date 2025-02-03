
{block name='cart_detailed_product'}
  <div class="cart-overview js-cart">
    {if $cart.products}
      <div class="cart-items">
        {foreach from=$cart.products item=product}
          <div class="cart-item">
            {block name='cart_detailed_product_line'}
              <div class="product-info">
                <strong>{$product.name}</strong>
                {if $product.super_product_id}
                  <small>Part of: <strong>{$product.super_product_name}</strong></small>
                {/if}
              </div>
            {/block}
          </div>
        {/foreach}
      </div>
    {/if}
  </div>
{/block}
