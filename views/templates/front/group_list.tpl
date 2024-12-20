<div id="super-product-groups">
	<div id="list-super-product-groups">

		<div class="selected-groups-list-container">
			{* <div class="infos">Sous-total <strong class="sub-total"></strong></div> *}
			<div class="total-info infos">Total éclaté technique <strong class="total"></strong></div>
			<ul class="selected-groups-list"></ul>
		</div>

		<p>Les éclatés techniques pour <strong>{$product.name}</strong></p>
		<ul class="list-super-product-groups">
			{foreach from=$groups item=group}
				<li class="list-super-product-group-item">
					{* <img src="{$group.image}" alt="{$group.name|escape:'html'}" class="group-image" /> *}
					{* <span class="group-name">{$group.name|escape:'html'}</span> *}
					<span class="js-open-group-popup" data-id_group="{$group.id}" data-name_group="{$group.name}"
						data-products="{$group.products|json_encode|escape:'html'}">
						{$group.name|escape:'html'}
						<i class="fa fa-chevron-right"></i>
					</span>
				</li>
			{/foreach}
		</ul>
	</div>

	<!-- Side popup for displaying products in the selected group -->
	<div id="group-popup" class="side-popup">

		<div class="side-popup-header">
			<p><strong>{$product.name} <span>></span></strong> <span class="selected-group-name"></span> </p>
			<h5>Sélectionner les produits</h5>
			<br>
			<span>Scroller ou Rechercher :</span>
			<button type="button" class="btn-close js-close-popup" aria-label="Close"></button>
		</div>

		<div class="side-popup-search">
			{* <div class="side-popup-search-controller">
				<label>Numéro : </label>
				<input type="text" id="group-product-search-num" class="group-product-search form-control" />
			</div> *}

			<div class="side-popup-search-controller">
				<label>Mot clé : </label>
				<input type="text" id="group-product-search" class="group-product-search form-control" />
			</div>
		</div>



		<div class="side-popup-body">
			<div class="login-message">
				{if !$logged}
					<div>
						{l s='Vous êtes professionnel ? ' d='Shop.Module.superproductgroups'}
						<a href="{$urls.pages.authentication}?back={$urls.current_url|urlencode}"
							title="{l s='Log in to your customer account' d='Shop.Module.superproductgroups'}" rel="nofollow">
							<span class="hidden-sm-down">{l s='Sign in' d='Shop.Module.superproductgroups'}</span>
						</a>
					</div>
					<div>{l s='pour accéder au cataloque prix dédié aux professionnels.' d='Shop.Module.superproductgroups'}</div>
				{/if}
			</div>

			<div id="group-products" class=""></div>
		</div>

		<div class="side-popup-after-body">
			<div class="selected-groups-list-container">
				{* <div class="infos">Sous-total <strong class="sub-total"></strong></div> *}
				<div class="total-info infos">Total éclaté technique <strong class="total"></strong></div>
				<ul class="selected-groups-list"></ul>
			</div>
		</div>

		<div class="side-popup-footer">
			{* <button type="button" class="btn btn-secondary js-close-popup">Close</button> *}
			<button type="button" class="btn btn-primary js-confirm-selection">Valider ma séléction</button>
		</div>

	</div>


	<!-- Selected Products Popup -->
	<div id="selected-products-popup" class="side-popup">

		<div class="side-popup-header">
			<h5>Valider ma sélection</h5>
      <div><strong>{$product.name} <strong></div>
			<button type="button" class="btn-close js-close-selected-popup" aria-label="Close"></button>
		</div>

		<div class="side-popup-body">
     <br>
     <br>
     <br>
			<div class="login-message">
				{if !$logged}
					<div>
						{l s='Vous êtes professionnel ? ' d='Shop.Module.superproductgroups'}
						<a href="{$urls.pages.authentication}?back={$urls.current_url|urlencode}"
							title="{l s='Log in to your customer account' d='Shop.Module.superproductgroups'}" rel="nofollow">
							<span class="hidden-sm-down">{l s='Sign in' d='Shop.Module.superproductgroups'}</span>
						</a>
					</div>
					<div>{l s='pour accéder au cataloque prix dédié aux professionnels.' d='Shop.Module.superproductgroups'}</div>
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
			{* <button type="button" class="btn btn-secondary js-close-selected-popup">Close</button> *}
			<button type="button" class="btn btn-primary js-add-confirmed-selection-to-cart">Voir le panier</button>
			<button type="button" class="btn btn-secondary js-add-confirmed-selection-to-cart">Commander</button>
		</div>

	</div>
</div>
