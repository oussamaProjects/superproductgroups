<div id="product-groups">
    <h3>Available Groups</h3>
    <ul>
        {foreach from=$groups item=group}
            <li>
                <img src="{$group.image}" alt="{$group.name}" />
                <button class="btn btn-primary js-open-group-modal" data-group-id="{$group.id_group}">
                    View {$group.name}
                </button>
            </li>
        {/foreach}
    </ul>
</div>

<!-- Modal for displaying products in the selected group -->
<div id="group-modal" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Group Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="group-products"></div>
                <input type="text" id="group-product-search" placeholder="Search products..." class="form-control" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary js-confirm-selection">Confirm</button>
            </div>
        </div>
    </div>
</div>
