<div class="wrap">
    <h1>Maintenance mode</h1>

    <?php if (!empty($_POST)) { ?>
        <div class="updated settings-error" id="setting-error-settings_updated">
            <p><strong>Settings saved.</strong></p>
        </div>
    <?php } ?>
    <form method="post">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">Status</th>
                <td>
                    <p>
                        <label><input type="radio" name="options[status]" value="0" <?php checked($settings['status'], 0); ?>> Off</label><br>
                    </p>
                    <p>
                        <label><input type="radio" name="options[status]" value="1" <?php checked($settings['status'], 1); ?>> On</label>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">Access for roles</th>
                <td>
                    <?php
                    global $wp_roles;
                    foreach ($wp_roles->roles as $role => $details) { ?>
                        <p>
                            <label>
                                <input type="checkbox" name="options[access][]" value="<?php echo esc_attr($role); ?>"
                                    <?php checked( in_array($role, $settings['access']), true ); ?>>
                                <?php echo $details['name'] . ' (' . $role . ')'; ?>
                            </label>
                        </p>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th scope="row">Excluded urls</th>
                <td>
                    <textarea rows="7" name="options[exclude]" class="large-text code"><?php
                        if (!empty($settings['exclude']) && is_array($settings['exclude'])) {
                            echo implode("\n", stripslashes_deep($settings['exclude']));
                        }
                        ?></textarea>
                    <p class="description">Allow access to the URL at the maintenance mode. Each URL on a new line, with no domain (/path/to/page/).</p>
                </td>
            </tr>
            </tbody>
        </table>

        <?php wp_nonce_field(WP_503_NONCE); ?>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
        </p>

    </form>

</div>
