<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Couriers List - Print View</title>
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
        <h1>Couriers List</h1>

        <table>
            <thead>
                <tr>
                    <th>Courier ID</th>
                    <th>Courier Name</th>
                    <th>Phone Number</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tpl['couriers'] as $courier): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($courier['courier_id']); ?></td>
                        <td><?php echo htmlspecialchars($courier['courier_name']); ?></td>
                        <td><?php echo htmlspecialchars($courier['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($courier['email']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="print-footer">
            Generated on: <?php echo date('Y-m-d H:i:s'); ?>
        </div>

        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print();" class="btn btn-primary">Print</button>
            <button onclick="window.close();" class="btn btn-secondary">Close</button>
        </div>
    </body>
</html>
