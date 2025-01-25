<div class="panel">
    <h3>Super Products and Associated Products</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Super Product ID</th>
                <th>Associated Products</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$superProducts item=superProduct}
                <tr>
                    <td>{$superProduct.id_super_product}</td>
                    <td>
                        <ul>
                            {foreach from=$superProduct.associated_products|json_decode:true item=product}
                                <li>{$product.name} (Qty: {$product.quantity})</li>
                            {/foreach}
                        </ul>
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>
