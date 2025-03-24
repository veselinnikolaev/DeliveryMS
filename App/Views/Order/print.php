<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Orders List - Print View</title>
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
        <h1>Orders List</h1>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Tracking Number</th>
                    <th>Customer</th>
                    <th>Courier</th>
                    <th>Delivery Date</th>
                    <th>Total Price</th>
                    <th>Address</th>
                    <th>Country</th>
                    <th>Region</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tpl['orders'] as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['tracking_number']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer']); ?></td>
                        <td><?php echo htmlspecialchars($order['courier']); ?></td>
                        <td><?php echo htmlspecialchars($order['delivery_date']); ?></td>
                        <td><?php echo htmlspecialchars($order['total_price']); ?></td>
                        <td><?php echo htmlspecialchars($order['address']); ?></td>
                        <td><?php echo htmlspecialchars($order['country']); ?></td>
                        <td><?php echo htmlspecialchars($order['region']); ?></td>
                        <td><?php echo Utility::$order_status[$order['status']] ?? 'Unknown'; ?></td>
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
