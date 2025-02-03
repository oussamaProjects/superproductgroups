
{block name='cart_detailed_product_line'}
  <div class="cart-item">
    {if isset($product.super_product_id) && $product.super_product_id}
      <p class="super-product-info">
        <strong>Part of: {$product.super_product_name}</strong>
      </p>
    {/if}

    {block name='cart_detailed_product_line_inner'}
      {$smarty.block.parent}
    {/block}
  </div>
{/block}
