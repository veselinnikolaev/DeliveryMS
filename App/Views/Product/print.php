<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Products List - Print View</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
            }
            h1 {
                text-align: center;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .print-footer {
                margin-top: 20px;
                text-align: right;
                font-size: 12px;
                color: #666;
            }
            @media print {
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <h1>Products List</h1>

        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Product Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tpl['products'] as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['product_id']); ?></td>
                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($product['product_description']); ?></td>
                        <td><?php echo htmlspecialchars($product['price']); ?></td>
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="print-footer">
            Generated on: <?php echo date($tpl['date_format'] . ' H:i:s'); ?>
        </div>

        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print();" class="btn btn-primary">Print</button>
            <button onclick="window.close();" class="btn btn-secondary">Close</button>
        </div>
    </body>
</html>