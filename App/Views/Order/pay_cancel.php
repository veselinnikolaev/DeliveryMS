<div class="container">
    <div class="header">
        <h1>Payment Cancelled</h1>
    </div>

    <?php if ($order): ?>
        <div class="order-details">
            <div class="order-details-row">
                <span class="order-details-label">Order ID:</span>
                <span class="order-details-value">#
                    <?php echo $tpl['order']['id']; ?>
                </span>
            </div>
            <div class="order-details-row">
                <span class="order-details-label">Customer:</span>
                <span class="order-details-value">
                    <?php echo $tpl['user']['name']; ?>
                </span>
            </div>
            <div class="order-details-row">
                <span class="order-details-label">Total Amount:</span>
                <span class="order-details-value total-amount">
                    <?php echo Utility::getDisplayableAmount($tpl['order']['total_amount']); ?>
                </span>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="<?php echo INSTALL_URL; ?>?controller=Order&action=details&id=<?php echo $tpl['order']['id']; ?>"
                style="display: inline-block; padding: 12px 20px; border-radius: 5px; text-decoration: none; 
                    background-color: var(--accent-color); color: white; font-weight: 600;">
                ← Back to Order Details
            </a>
        </div>
    <?php else: ?>
        <div class="order-details">
            <p>Order not found. Please contact support.</p>
        </div>
    <?php endif; ?>

    <div class="footer">
        We're sorry that your payment was canceled. If you have any questions, please contact our support team.
    </div>
</div>