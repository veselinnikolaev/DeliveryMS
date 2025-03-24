
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled</title>
    <style>
        :root {
            --primary-color: #4a6de5;
            --secondary-color: #f8f9fa;
            --accent-color: #6c757d;
            --success-color: #28a745;
            --text-color: #343a40;
            --light-border: #e9ecef;
            --error-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--light-border);
        }

        .header h1 {
            color: var(--error-color);
            margin: 0;
            font-size: 32px;
            font-weight: 600;
        }

        .order-details {
            background-color: var(--secondary-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .order-details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .order-details-row:last-child {
            margin-bottom: 0;
            padding-top: 15px;
            border-top: 1px dashed var(--light-border);
        }

        .order-details-label {
            font-weight: 600;
            color: var(--accent-color);
        }

        .order-details-value {
            font-weight: 700;
        }

        .total-amount {
            font-size: 1.2em;
            color: var(--error-color);
        }

        .back-button {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            background-color: var(--accent-color);
            color: white;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #5a6268;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: var(--accent-color);
            font-size: 14px;
        }

        .message-container {
            text-align: center;
            margin: 30px 0;
        }

        .message-icon {
            color: var(--error-color);
            font-size: 48px;
            margin-bottom: 15px;
        }

        .message-text {
            font-size: 18px;
            color: var(--text-color);
            margin-bottom: 25px;
        }

        .retry-payment {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            margin: 20px 10px;
            transition: background-color 0.3s;
        }

        .retry-payment:hover {
            background-color: #3955c8;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 20px;
            }
        }
    </style>
</head>
<div class="container">
    <div class="header-error">
        <h1>Payment Cancelled</h1>
    </div>

    <?php if (!empty($tpl['order'])): ?>
        <div class="order-details">
            <div class="order-details-row">
                <span class="order-details-label">Order ID:</span>
                <span class="order-details-value">#<?php echo $tpl['order']['id']; ?></span>
            </div>
            <div class="order-details-row">
                <span class="order-details-label">Customer:</span>
                <span class="order-details-value"><?php echo $tpl['user']['name']; ?></span>
            </div>
            <div class="order-details-row">
                <span class="order-details-label">Total Amount:</span>
                <span class="order-details-value total-amount-error"><?php echo Utility::getDisplayableAmount($tpl['order']['total_amount']); ?></span>
            </div>
        </div>

        <div class="message-container">
            <div class="message-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </div>
            <div class="message-text">
                Your payment has been cancelled. Would you like to try again?
            </div>
            <a href="<?php echo INSTALL_URL; ?>?controller=Order&action=pay&order_id=<?php echo $tpl['order']['id']; ?>" class="retry-payment">
                Retry Payment
            </a>
            <a href="<?php echo INSTALL_URL; ?>?controller=Order&action=details&id=<?php echo $tpl['order']['id']; ?>" class="back-button">
                ← Back to Order Details
            </a>
        </div>
    <?php else: ?>
        <div class="order-details">
            <p style="text-align: center; font-size: 18px; color: var(--error-color);">Order not found. Please contact support.</p>
        </div>
        <div class="message-container">
            <a href="<?php echo INSTALL_URL; ?>?controller=Home" class="back-button">
                ← Back to Home
            </a>
        </div>
    <?php endif; ?>

    <div class="footer">
        We're sorry that your payment was cancelled. If you have any questions, please contact our support team.
    </div>
</div>