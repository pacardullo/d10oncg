<?php
/**
 * Collect external service information from environment.
 * Cloud Foundry places all service credentials in VCAP_SERVICES
 */

$cf_service_data = json_decode($_ENV['VCAP_SERVICES'] ?? '{}', true);

foreach ($cf_service_data as $service_provider => $service_list) {
  foreach ($service_list as $service) {
    if ($service['name'] === 'database') {
      $databases['default']['default'] = array (
        'database' => $service['credentials']['db_name'],
        'username' => $service['credentials']['username'],
        'password' => $service['credentials']['password'],
        'prefix' => '',
        'host' => $service['credentials']['host'],
        'port' => $service['credentials']['port'],
        'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
        'driver' => 'mysql',
      );
    }
    if ($service['name'] === 'secrets') {
      $settings['hash_salt'] = $service['credentials']['HASH_SALT'];
    }
    if ($service['name'] === 'storage') {
      $config['s3fs.settings']['access_key'] = $service['credentials']['access_key_id'];
      $config['s3fs.settings']['bucket'] = $service['credentials']['bucket'];
      $config['s3fs.settings']['encryption'] = 'AES256';
      $config['s3fs.settings']['public_folder'] = 'public';
      $config['s3fs.settings']['private_folder'] = 'private';
      $config['s3fs.settings']['region'] = $service['credentials']['region'];
      $config['s3fs.settings']['secret_key'] = $service['credentials']['secret_access_key'];
      $config['s3fs.settings']['use_https'] = TRUE;

      // Updated config structure for s3fs 8.x-3.x
      $settings['s3fs.access_key'] = $service['credentials']['access_key_id'];
      $settings['s3fs.bucket'] = $service['credentials']['bucket'];
      $settings['s3fs.public_folder'] = 'public';
      $settings['s3fs.private_folder'] = 'private';
      $settings['s3fs.region'] = $service['credentials']['region'];
      $settings['s3fs.secret_key'] = $service['credentials']['secret_access_key'];
      $settings['s3fs.use_https'] = TRUE;
      $settings['s3fs.disable_cert_verify'] = FALSE;

      $settings['s3fs.use_s3_for_public'] = TRUE;
      // Twig templates _shouldn't_ be in the public dir (lest they be very slow)
      $settings['php_storage']['twig']['directory'] = '../storage/php';
    }
  }
}

// CSS and JS aggregation need per dyno/container cache.
// This is from https://www.fomfus.com/articles/how-to-create-a-drupal-8-project-for-heroku-part-1
// included here without fully understanding implications:
$settings['cache']['bins']['data'] = 'cache.backend.php';
