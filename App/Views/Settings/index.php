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
                        <div class="settings-section mb-4">
                            <h5 class="section-title border-bottom pb-2 mb-3">Financial Settings</h5>
                            <div class="row">
                                <?php foreach ($tpl['settings'] as $setting): ?>
                                    <?php if (in_array($setting['key'], ['tax_rate', 'shipping_rate', 'currency_code'])): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-group">
                                                <label for="<?php echo $setting['key']; ?>" class="form-label"><?php echo ucwords(str_replace('_', ' ', $setting['key'])); ?></label>
                                                <?php if ($setting['key'] === 'tax_rate' || $setting['key'] === 'shipping_rate'): ?>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0" class="form-control settings-input" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" value="<?php echo $setting['value']; ?>" required>
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                <?php elseif ($setting['key'] === 'currency_code'): ?>
                                                    <select class="form-control settings-input" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" required>
                                                        <?php
                                                        foreach (Utility::$currencies as $k => $v) {
                                                            $selected = ($k == $setting['value']) ? 'selected' : '';
                                                            echo "<option value=\"{$k}\" $selected>{$v}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="settings-section mb-4">
                            <h5 class="section-title border-bottom pb-2 mb-3">Localization</h5>
                            <div class="row">
                                <?php foreach ($tpl['settings'] as $setting): ?>
                                    <?php if (in_array($setting['key'], ['timezone', 'date_format'])): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label for="<?php echo $setting['key']; ?>" class="form-label"><?php echo ucwords(str_replace('_', ' ', $setting['key'])); ?></label>
                                                <?php if ($setting['key'] === 'timezone'): ?>
                                                    <select class="form-control settings-input" id="timezone" name="settings[timezone]" required>
                                                        <?php
                                                        $timezones = DateTimeZone::listIdentifiers();
                                                        foreach ($timezones as $timezone) {
                                                            $selected = ($timezone == $setting['value']) ? 'selected' : '';
                                                            echo "<option value=\"{$timezone}\" $selected>{$timezone}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                <?php elseif ($setting['key'] === 'date_format'): ?>
                                                    <select class="form-control settings-input" id="date_format" name="settings[date_format]" required>
                                                        <?php
                                                        foreach (Utility::$dateFormats as $format => $label) {
                                                            $selected = ($format == $setting['value']) ? 'selected' : '';
                                                            echo "<option value=\"{$format}\" $selected>{$label}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="settings-section mb-4">
                            <h5 class="section-title border-bottom pb-2 mb-3">Communication</h5>
                            <div class="row">
                                <?php foreach ($tpl['settings'] as $setting): ?>
                                    <?php if (in_array($setting['key'], ['email_sending'])): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label for="<?php echo $setting['key']; ?>" class="form-label"><?php echo ucwords(str_replace('_', ' ', $setting['key'])); ?></label>
                                                <select class="form-control settings-input" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" required>
                                                    <option value="enabled" <?php echo ($setting['value'] == 'enabled') ? 'selected' : ''; ?> 
                                                            <?php echo (!MAIL_CONFIGURED) ? 'disabled' : ''; ?>>Enabled</option>
                                                    <option value="disabled" <?php echo ($setting['value'] == 'disabled') ? 'selected' : ''; ?>>Disabled</option>
                                                </select>

                                                <?php if (!MAIL_CONFIGURED): ?>
                                                    <div class="mt-2">
                                                        <a href="<?php echo INSTALL_URL; ?>?controller=Install&action=step4" class="btn btn-warning">Configure Email</a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="form-actions mt-4 pt-3 border-top">
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" id="save-btn" class="btn btn-primary text-white me-2" disabled>Save Changes</button>
                                    <button type="button" id="undo-btn" class="btn btn-outline-dark" disabled>Undo</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

