<div id="product-groups">
  <h3>Available Groups</h3>
  <ul class="list-unstyled">
    {foreach from=$groups item=group}
      <li class="mb-2">

        <div class="group-header">
          <img src="{$group.image}" alt="{$group.name|escape:'html'}" class="group-image" />
          <span class="group-name">{$group.name|escape:'html'}</span>
          <button class="btn btn-primary js-open-group-popup" data-group-id="{$group.id}"
            data-products="{$group.products|json_encode|escape:'html'}">
            View {$group.name|escape:'html'}
          </button>
        </div>
        <div class="group-products">
          {if $group.products|@count > 0}
            <ul class="list-unstyled">
              {foreach from=$group.products item=product}
                <li class="product-item">
                  <span>{$product.name|escape:'html'}</span>
                  <span class="product-price">{$product.price|number_format:2:'.':','} $</span>
                </li>
              {/foreach}
            </ul>
          {else}
            <p>No products available in this group.</p>
          {/if}
        </div>

      </li>
    {/foreach}
  </ul>
</div>

<!-- Side popup for displaying products in the selected group -->
<div id="group-popup" class="side-popup">
  <div class="side-popup-header">
    <h5>Group Products</h5>
    <button type="button" class="btn-close js-close-popup" aria-label="Close"></button>
  </div>
  <input type="text" id="group-product-search" placeholder="Search products..." class="group-product-search form-control" />
  <div class="side-popup-body">
    <div id="group-products" class=""></div>
  </div>
  <div class="side-popup-footer">
    <button type="button" class="btn btn-secondary js-close-popup">Close</button>
    <button type="button" class="btn btn-primary js-confirm-selection">Confirm</button>
  </div>
</div>
