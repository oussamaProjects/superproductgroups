<div id="super-product-groups" class="super-product-groups isLogged_{$logged|intval}">

  <div id="list-super-product-groups">

    <div class="selected-groups-list-container">
      {* <div class="infos">Sous-total <strong class="sub-total"></strong></div> *}
      <div class="total-info infos">Total éclaté technique <strong class="total"></strong></div>
      <ul class="selected-groups-list"></ul>
    </div>

    <div class="title">Les éclatés techniques pour <strong>{$product.name}</strong></div>
    <ul class="list-super-product-groups">
      {foreach from=$groups item=group name=groupLoop}
        <li class="list-super-product-group-item">
          {* <img src="{$group.image}" alt="{$group.name|escape:'html'}" class="group-image" /> *}
          {* <span class="group-name">{$group.name|escape:'html'}</span> *}
          <span class="js-open-group-popup" data-id_group="{$group.id}" data-name_group="{$group.name}"
            data-products="{$group.products|json_encode|escape:'html'}">
            {sprintf("%02d", $smarty.foreach.groupLoop.index+1)} - {$group.name|escape:'html'}
            <i class="fa fa-chevron-right"></i>
          </span>
        </li>
      {/foreach}
    </ul>
  </div>

  <!-- Side popup for displaying products in the selected group -->
  <div id="group-popup" class="side-popup">
    <button type="button" class="btn-close js-close-popup" aria-label="Close">
      <i class="fa fa-close"></i>
    </button>

    <div class="side-popup-header">
      <div class="breadcrumb">
        {$product.name} <span>></span> <span class="selected-group-name"></span>
      </div>
      <div class="title">Sélectionner les produits</div>
    </div>

    <div class="side-popup-search">
      <div>Scroller ou Rechercher :</div>

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
      <div class="login-message">
        {if !$logged}
          <div>
            <span>{l s='Vous êtes professionnel ? ' d='Shop.Module.superproductgroups'}</span>
            <a href="{$urls.pages.authentication}?back={$urls.current_url|urlencode}"
              title="{l s='Log in to your customer account' d='Shop.Module.superproductgroups'}" rel="nofollow">
              <span class="hidden-sm-down">{l s='Sign in' d='Shop.Module.superproductgroups'}</span>
            </a>
          </div>
          <div>{l s='pour accéder au cataloque prix dédié aux professionnels.' d='Shop.Module.superproductgroups'}
          </div>
        {/if}
      </div>

      <div id="group-products" class=""></div>
    </div>

    {* <div class="side-popup-after-body">
      <div class="selected-groups-list-container">
        <div class="total-info infos">Sous-total <strong class="sub-total"></strong></div>
      </div>
    </div> *}
    <div class="side-popup-after-body">
    <div class="selected-groups-list-container">

    </div>
  </div>
    <div class="side-popup-footer">
      <button type="button" class="custom-button js-confirm-selection">Valider ma séléction</button>
    </div>

    <div class="side-popup-after-body">
      <div class="selected-groups-list-container">
        <div class="total-info infos">Total éclaté technique <strong class="total"></strong></div>
        <ul class="selected-groups-list"></ul>
      </div>
    </div>

  </div>

  <!-- Selected Products Popup -->
  <div id="selected-products-popup" class="side-popup">

    <div class="side-popup-header">

      {* <div class="notice">Décocher produit : suppression du panier et la ligne s'enlève</div> *}
      <div class="title">
        Valider ma sélection
        <div class="blue"><strong>{$product.name}</strong></div>
      </div>

      <button type="button" class="btn-close js-close-selected-popup" aria-label="Close">
        <i class="fa fa-close"></i>
      </button>
    </div>

    <div class="side-popup-body">
      <div class="login-message">
        {if !$logged}
        <div>
          <span>{l s='Vous êtes professionnel ? ' d='Shop.Module.superproductgroups'}</span>
          <a href="{$urls.pages.authentication}?back={$urls.current_url|urlencode}"
            title="{l s='Log in to your customer account' d='Shop.Module.superproductgroups'}" rel="nofollow">
            <span class="hidden-sm-down">{l s='Sign in' d='Shop.Module.superproductgroups'}</span>
          </a>
        </div>
        <div>
          {l s='pour accéder au cataloque prix dédié aux professionnels.' d='Shop.Module.superproductgroups'}
          </div>
        {/if}
      </div>

      <ul id="selected-products-list" class="list-group"></ul>
    </div>

    <div class="side-popup-after-body">
      <div class="selected-groups-list-container">
        {* <div class="infos">Sous-total <strong class="sub-total"></strong></div> *}
        <div class="total-info infos">Total éclaté technique <strong class="total"></strong></div>
        {* <ul class="selected-groups-list"></ul> *}
      </div>
    </div>

    <div class="side-popup-footer">
      <button type="button" class="custom-button add-to-cart js-add-confirmed-selection-to-cart">Ajouter le panier</button>
      <button type="button" class="custom-button order js-add-confirmed-selection-to-cart">Commander</button>
    </div>

  </div>

</div>
