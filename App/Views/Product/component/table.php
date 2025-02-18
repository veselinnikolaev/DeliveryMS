<table class="table select-table" id="product-table-id">
    <thead>
        <tr>
            <th>
                <div class="form-check form-check-flat mt-0">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input">
                    </label>
                </div>
            </th>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Product Description</th>
            <th>Price</th>
            <th>Stock</th>
            <th style="text-align: right;"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tpl['products'] as $product) { ?>
            <tr>
                <td>
                    <div class="form-check form-check-flat mt-0">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input">
                        </label>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($product['id']); ?></td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['description']); ?></td>
                <td><?php echo Utility::getDisplayableAmount(htmlspecialchars($product['price'])); ?></td>
                <td><?php echo htmlspecialchars($product['stock']); ?></td>
                <td style="text-align: right;">
                    <a class="btn btn-light btn-circle mdc-ripple-upgraded" href="<?php echo INSTALL_URL; ?>?controller=Product&action=edit&id=<?php echo $product['id'] ?>">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                    </a>
                    <a class="btn btn-danger btn-circle delete-product" href="#" data-id="<?php echo $product['id']; ?>">
                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>