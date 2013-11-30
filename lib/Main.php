<?php

class xautoload_Main {

  /**
   * @var xautoload_Container_LazyServices
   */
  protected $services;

  /**
   * @param xautoload_Container_LazyServices $services
   */
  function __construct($services) {
    $this->services = $services;
  }

  /**
   * Invalidate all values stored in APC.
   */
  function flushCache() {
    $this->services->apcKeyManager->renewApcPrefix();
  }

  /**
   * @param string $file
   *   File path to a *.module or *.install file.
   */
  function registerModule($file) {
    $info = pathinfo($file);
    $modules[] = (object)array(
      'name' => $info['filename'],
      'filename' => $info['dirname'] . '/' . $info['filename'] . '.module',
      'type' => 'module',
    );
    $this->services->registrationHelper->registerExtensions($modules);
  }
}
