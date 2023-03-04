<?php

use SimpleJWTLogin\Modules\SimpleJWTLoginSettings;
use SimpleJWTLogin\Modules\WordPressData;

if (!defined('ABSPATH')) {
    exit;
}

$value = '';
$error = null;
$success = null;
$wordPressData = new WordPressData();

if (!empty($_POST) && isset($_POST['action']) && isset($_POST['_wpnonce'])) {
    $result = $wordPressData
        ->checkNonce($_POST['_wpnonce'], WordPressData::NONCE_NAME);
    if ($result === false) {
        $error = 'Something is wrong.';
        $success = null;
        $value = '';
    } else {
        $action = esc_html($_POST['action']);
        switch ($action) {
            case "import":
                if (isset($_POST['settings'])) {
                    $settings = base64_decode(esc_html($_POST['settings']));
                    if ($settings === false) {
                        $error = 'Invalid import settings';
                        break;
                    }
                    $array = json_decode($settings, true);
                    if (!is_array($array)) {
                        $error = 'Invalid import settings.';
                        break;
                    }

                    $needUpdate = $wordPressData->getOptionFromDatabase(SimpleJWTLoginSettings::OPTIONS_KEY) !== false;

                    if ($needUpdate) {
                        $wordPressData->updateOption(SimpleJWTLoginSettings::OPTIONS_KEY, $settings);
                        $success = 'Settings has been imported.';
                        break;
                    }

                    $wordPressData->addOption(SimpleJWTLoginSettings::OPTIONS_KEY, $settings);
                    $success = 'Settings has been imported.';
                    break;
                }
                break;

            case 'export':
                $value = base64_encode($wordPressData->getOptionFromDatabase(SimpleJWTLoginSettings::OPTIONS_KEY));
                break;
        }
    }
}
?>
<div id="simple-jwt-login-export-import">
    <?php
    if ($success !== null || $error !== null) {

        $alertMessage = $success;
        $type = 'success';
        if ($error !== null) {
            $type = 'danger';
            $alertMessage = $error;
        }

        ?>
        <div class="row">
            <div class="alert alert-<?php echo $type; ?>">
                <?php echo esc_html($alertMessage); ?>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="row">
        <div class="col-md-12">
            <h1><?php echo __('Export/Import Settings ', 'simple-jwt-login-export-import'); ?></h1>
        </div>
    </div>
    <form method="POST">
        <?php
        $wordPressData->insertNonce(WordPressData::NONCE_NAME);
        ?>
        <div class="row">
            <div class="col-md-12">
                <input type="submit" name="action" value="import" class="btn btn-dark" onclick="return simple_jwt_login_export_import_confirm()"/>
                <input type="submit" name="action" value="export" class="btn btn-dark"/>
            </div>
        </div>
        <hr/>

        <div class="row">
            <div class="col-md-12">
                <label for="settings-response">Response:</label>
                <textarea
                        id="settings-response"
                        rows="20"
                        name="settings"
                        class="input form-control"
                    <?php if (!empty($value)) {
                        echo "readonly";
                    }
                    ?>
            ><?php echo esc_html($value); ?></textarea>
            </div>
        </div>
    </form>
</div>