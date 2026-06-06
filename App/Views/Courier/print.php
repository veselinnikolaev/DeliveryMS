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
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>Country</th>
                    <th>Region</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // 1. Safely check if 'couriers' exists and is an array/object
            if (!empty($tpl['couriers']) && is_iterable($tpl['couriers'])) :
                foreach ($tpl['couriers'] as $user) : ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($user['courier_id'] ?? 'N/A')); ?></td>
                        <td><?= htmlspecialchars((string)($user['name'] ?? 'N/A')); ?></td>
                        <td><?= htmlspecialchars((string)($user['email'] ?? 'N/A')); ?></td>
                        <td><?= htmlspecialchars((string)($user['phone_number'] ?? 'N/A')); ?></td>
                        <td><?= htmlspecialchars((string)($user['address'] ?? 'N/A')); ?></td>
                        <td><?= htmlspecialchars((string)($user['country'] ?? 'N/A')); ?></td>
                        <td><?= htmlspecialchars((string)($user['region'] ?? 'N/A')); ?></td>
                    </tr>
                <?php endforeach;
            else : ?>
                <tr>
                    <td colspan="7" class="text-center">No courier data available.</td>
                </tr>
            <?php endif; ?>
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
