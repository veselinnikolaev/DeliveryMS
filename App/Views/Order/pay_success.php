<div class="container">
    <div class="header">
        <h1>Payment Success</h1>
    </div>

    <?php if ($tpl['order']): ?>
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

        <div class="secure-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path
                    d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" />
            </svg>
            Secure payment
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
        Thank you for your order. If you have any questions, please contact our support team.
    </div>
</div>