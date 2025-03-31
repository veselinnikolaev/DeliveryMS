
<div class="container">
    <div class="header">
        <h1>Order Payment</h1>
    </div>

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
            <span class="order-details-label">Date:</span>
            <span class="order-details-value">
                <?php echo date($tpl['date_format'], $tpl['order']['created_at']); ?>
            </span>
        </div>

        <?php if (!empty($tpl['order_products'])): ?>
            <div class="order-details-row">
                <span class="order-details-label">Items:</span>
                <span class="order-details-value">
                    <?php echo count($tpl['order_products']); ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="order-details-row">
            <span class="order-details-label">Subtotal:</span>
            <span class="order-details-value">
                <?php echo Utility::getDisplayableAmount($tpl['order']['product_price']); ?>
            </span>
        </div>

        <div class="order-details-row">
            <span class="order-details-label">Tax:</span>
            <span class="order-details-value">
                <?php echo Utility::getDisplayableAmount($tpl['order']['tax']); ?>
            </span>
        </div>

        <div class="order-details-row">
            <span class="order-details-label">Shipping:</span>
            <span class="order-details-value">
                <?php echo Utility::getDisplayableAmount($tpl['order']['shipping_price']); ?>
            </span>
        </div>

        <div class="order-details-row">
            <span class="order-details-label">Total Amount:</span>
            <span class="order-details-value total-amount">
                <?php echo Utility::getDisplayableAmount($tpl['order']['total_amount']); ?>
            </span>
        </div>
    </div>

    <div class="payment-methods">
        <div class="payment-method-title">Please select your payment method</div>
        <?php
        $url = "https://www.paypal.com/cgi-bin/webscr";
        //$url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        ?>
        <!-- PayPal Button -->
        <form action="<?php echo $url; ?>" method="post" target="_blank">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="<?php echo PAYPAL_EMAIL; ?>">
            <input type="hidden" name="custom" value="<?php echo $tpl['order']['id']; ?>">
            <input type="hidden" name="item_name" value="Order #<?php echo $tpl['order']['id']; ?>">
            <input type="hidden" name="amount" value="<?php echo str_replace(',', '', $tpl['order']['total_amount']); ?>">
            <input type="hidden" name="currency_code" value="<?php echo Utility::$currencies[$tpl['currency_code']]; ?>">

            <input type="hidden" name="return"
                   value="<?php echo INSTALL_URL; ?>?controller=Order&action=pay_success&order_id=<?php echo $tpl['order']['id']; ?>">
            <input type="hidden" name="cancel_return"
                   value="<?php echo INSTALL_URL; ?>?controller=Order&action=pay_cancel&order_id=<?php echo $tpl['order']['id']; ?>">
            <input type="hidden" name="notify_url"
                   value="<?php echo INSTALL_URL; ?>?controller=Order&action=paypal_ipn">
            <button type="submit" class="payment-button">
                <span class="button-content">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                         viewBox="0 0 16 16">
                    <path
                        d="M14.06 3.713c.12-1.071-.093-1.832-.702-2.526C12.628.356 11.312 0 9.626 0H4.734a.7.7 0 0 0-.691.59L2.005 13.509a.42.42 0 0 0 .415.486h2.756l-.202 1.28a.628.628 0 0 0 .62.726H8.14c.429 0 .793-.31.862-.731l.025-.13.48-3.043.03-.164.001-.007a.351.351 0 0 1 .348-.297h.38c1.266 0 2.425-.256 3.345-.91.379-.27.712-.603.993-1.005a4.942 4.942 0 0 0 .88-2.195c.242-1.246.13-2.356-.57-3.154a2.687 2.687 0 0 0-.76-.59l-.094-.061Z" />
                    </svg>
                    Pay with PayPal
                </span>
            </button>
        </form>
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

    <div class="footer">
        Thank you for your order. If you have any questions, please contact our support team.
    </div>
</div>
