<table class="table">
  <thead>
    <tr>
      <th>Super Product</th>
      <th>Product</th>
    </tr>
  </thead>
  <tbody>
    {assign var="lastSuperProductName" value=""}
    {foreach from=$customFieldsByProduct key=productId item=super_product}
      <tr>
        <td>
          {if $super_product['super_product_name'] != $lastSuperProductName}
            {$super_product['super_product_name']}
            {assign var="lastSuperProductName" value=$super_product['super_product_name']}
          {/if}
        </td>
        <td>
          {$super_product['product_name']}
        </td>
      </tr>
    {/foreach}
  </tbody>
</table>
