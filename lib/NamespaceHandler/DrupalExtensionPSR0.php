<?php


class xautoload_NamespaceHandler_DrupalExtensionPSR0 extends xautoload_NamespaceHandler_DrupalExtensionLib {

  protected function _moduleClassesDir($module, $module_dir, $path_prefix_symbolic) {
    return $module_dir . DIRECTORY_SEPARATOR .
      $path_prefix_symbolic . $module . DIRECTORY_SEPARATOR;
  }
}
