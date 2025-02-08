<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="card card-rounded mt-3">
                <div class="card-body">
                    <h4 class="card-title">Settings</h4>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form id="settings-form" class="forms-sample" method="POST" action="<?php echo INSTALL_URL; ?>?controller=Settings&action=index">
                        <input type="hidden" name="id" value="<?php echo $tpl['settings']['id']; ?>" />

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="taxRate" class="form-label">Tax Rate (%)</label>
                                <input type="number" step="0.01" min="0" class="form-control settings-input" id="taxRate" name="tax_rate" value="<?php echo $tpl['settings']['tax_rate']; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="shippingRate" class="form-label">Shipping Rate (%)</label>
                                <input type="number" step="0.01" min="0" class="form-control settings-input" id="shippingRate" name="shipping_rate" value="<?php echo $tpl['settings']['shipping_rate']; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="currencyCode" class="form-label">Currency Code</label>
                                <select class="form-control settings-input" id="currencyCode" name="currency_code" required>
                                    <?php
                                    foreach (Utility::$currencies as $k => $v) {
                                        $selected = ($k == $tpl['settings']['currency_code']) ? 'selected' : '';
                                        echo "<option value=\"{$k}\" $selected>{$v}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="emailSending" class="form-label">Email Sending</label>
                                <select class="form-control settings-input" id="emailSending" name="email_sending" required>
                                    <option value="enabled" <?php echo ($tpl['settings']['email_sending'] == 'enabled') ? 'selected' : ''; ?>>Enabled</option>
                                    <option value="disabled" <?php echo ($tpl['settings']['email_sending'] == 'disabled') ? 'selected' : ''; ?>>Disabled</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" id="save-btn" class="btn btn-primary text-white me-2" disabled>Save Changes</button>
                                <button type="button" id="undo-btn" class="btn btn-outline-dark" disabled>Undo</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>