<button type="button" class="btn btn-info js-view-selected-products">Voir Produits Sélectionnés</button>

<div id="list-super-product-groups">
  <h3>Les éclatés techniques pour</h3>
  <ul class="list-super-product-groups">
    {foreach from=$groups item=group}
      <li class="list-super-product-group-item">
        {* <img src="{$group.image}" alt="{$group.name|escape:'html'}" class="group-image" /> *}
        {* <span class="group-name">{$group.name|escape:'html'}</span> *}
        <span class="js-open-group-popup" data-group-id="{$group.id}"
          data-products="{$group.products|json_encode|escape:'html'}">
          01 - {$group.name|escape:'html'}
          <i class="fa fa-chevron-right"></i>
        </span>
      </li>
    {/foreach}
  </ul>
</div>

<!-- Side popup for displaying products in the selected group -->
<div id="group-popup" class="side-popup">
  <div class="side-popup-header">
    <h5>Ma sélection</h5>
    <span>Scroller ou Rechercher :</span>
    <button type="button" class="btn-close js-close-popup" aria-label="Close"></button>
  </div>

  <div class="side-popup-search">
    <div class="side-popup-search-controller">
      <label>Numéro : </label>
      <input type="text" id="group-product-search-num" class="group-product-search form-control" />
    </div>

    <div class="side-popup-search-controller">
      <label>Mot clé : </label>
      <input type="text" id="group-product-search" class="group-product-search form-control" />
    </div>
  </div>

  <div class="side-popup-body">
    <div id="group-products" class=""></div>
  </div>
  <div class="side-popup-footer">
    <button type="button" class="btn btn-secondary js-close-popup">Close</button>
    <button type="button" class="btn btn-primary js-confirm-selection">Confirm</button>
    </div>
    </div>


    <!-- Selected Products Popup -->
    <div id="selected-products-popup" class="side-popup">
    <div class="side-popup-header">
    <h5>Produits Sélectionnés</h5>
    <button type="button" class="btn-close js-close-selected-popup" aria-label="Close"></button>
    </div>
    <div class="side-popup-body">
    <ul id="selected-products-list" class="list-group"></ul>
    </div>
    <div class="side-popup-footer">
    <button type="button" class="btn btn-secondary js-close-selected-popup">Close</button>
    <button type="button" class="btn btn-primary js-add-confirmed-selection-to-cart">Add to cart</button>
  </div>
</div>
