<?php
/**
 * @file
 * Administration-related forms and such.
 */

/**
 * Form callback for OPcache's administration form.
 */
function opcache_admin_form($form, &$form_state) {
  drupal_set_title(t('OPcache'));
  $backends = variable_get('opcache_backends', NULL);
  $form['opcache_backends'] = array(
    '#type' => 'textarea',
    '#title' => t('PHP proxy back-ends'),
    '#default_value' => empty($backends) ? '' : implode("\n", $backends),
    '#description' => t('If you run PHP behind one or more proxy back-ends, enter the full URLs to the PHP servers here, one per line. Include the protocol, and if clean URLs are not enabled, append &ldquo;index.php?q=&rdquo; to the end. For example, &ldquo;http://127.0.0.1:8080/&rdquo; or &ldquo;https://192.168.0.12/drupal/index.php?q=&rdquo;. If you are not running PHP behind a proxy back-end, leave this field blank.'),
    '#weight' => 0,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration'),
    '#weight' => 990,
  );
  return $form;
}

/**
 * Submit handler for our configuration form.
 */
function opcache_admin_form_submit($form, $form_state) {
  $trimmed = trim($form_state['values']['opcache_backends']);
  if (!empty($trimmed)) {
    $backends = preg_split("/\r\n|\n|\r/", $form_state['values']['opcache_backends']);
  }
  else {
    $backends = NULL;
  }
  variable_set('opcache_backends', $backends);
  drupal_set_message(t('The configuration options have been saved.'));
}
