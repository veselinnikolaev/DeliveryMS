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
                        <?php foreach ($tpl['settings'] as $setting): ?>
                            <div class="row">
                                <div class="form-group col-md-6 mb-3">
                                    <label for="<?php echo $setting['key']; ?>" class="form-label"><?php echo ucfirst(str_replace('_', ' ', $setting['key'])); ?></label>
                                    <?php if ($setting['key'] === 'tax_rate' || $setting['key'] === 'shipping_rate'): ?>
                                        <input type="number" step="0.01" min="0" class="form-control settings-input" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" value="<?php echo $setting['value']; ?>" required>
                                    <?php elseif ($setting['key'] === 'currency_code'): ?>
                                        <select class="form-control settings-input" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" required>
                                            <?php
                                            foreach (Utility::$currencies as $k => $v) {
                                                $selected = ($k == $setting['value']) ? 'selected' : '';
                                                echo "<option value=\"{$k}\" $selected>{$v}</option>";
                                            }
                                            ?>
                                        </select>
                                    <?php elseif ($setting['key'] === 'email_sending'): ?>
                                        <select class="form-control settings-input" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" required>
                                            <option value="enabled" <?php echo ($setting['value'] == 'enabled') ? 'selected' : ''; ?> 
                                                    <?php echo (!MAIL_CONFIGURED) ? 'disabled' : ''; ?>>Enabled</option>
                                            <option value="disabled" <?php echo ($setting['value'] == 'disabled') ? 'selected' : ''; ?>>Disabled</option>
                                        </select>

                                        <?php if (!MAIL_CONFIGURED): ?>
                                            <a href="<?php echo INSTALL_URL; ?>?controller=Install&action=step3" class="btn btn-warning mt-2">Configure Email</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <input type="text" class="form-control settings-input" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" value="<?php echo $setting['value']; ?>" required>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

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
