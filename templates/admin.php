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
                        <label><input type="radio" name="options[status]" value="0" <?php checked($this->settings['status'], 0); ?>> On</label><br>
                    </p>
                    <p>
                        <label><input type="radio" name="options[status]" value="1" <?php checked($this->settings['status'], 1); ?>> Off</label>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="options[access]">Access</label>
                </th>
                <td>
                    <select name="options[access]">
                        <?php
                        global $wp_roles;
                        foreach ($wp_roles->roles as $role => $details) { ?>
                            <option value="<?php echo esc_attr($role); ?>" <?php selected($this->settings['access'], $role); ?>><?php echo $details['name'] . ' (' . $role . ')'; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="options[exclude]">Excluded urls</label>
                </th>
                <td>
                    <textarea rows="7" name="options[exclude]" class="large-text code"><?php
                        if (!empty($this->settings['exclude']) && is_array($this->settings['exclude'])) {
                            echo implode("\n", stripslashes_deep($this->settings['exclude']));
                        }
                        ?></textarea>
                    <p class="description">Allow access to the URL at the maintenance mode. Each URL on a new line, with no domain (/path/to/page/).</p>
                </td>
            </tr>
            </tbody>
        </table>

        <?php wp_nonce_field('wpMntMode'); ?>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
        </p>

    </form>

</div>
