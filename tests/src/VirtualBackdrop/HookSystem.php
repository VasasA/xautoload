<?php


namespace Backdrop\xautoload\Tests\VirtualBackdrop;



class HookSystem {

  /**
   * @var ModuleImplements
   */
  private $moduleImplements;

  /**
   * @param BackdropStatic $backdropStatic
   * @param Cache $cache
   * @param ModuleList $moduleList
   */
  function __construct(BackdropStatic $backdropStatic, Cache $cache, ModuleList $moduleList) {
    $this->moduleImplements = new ModuleImplements($backdropStatic, $cache, $moduleList, $this);
  }

  /**
   * @param string $hook
   */
  function moduleInvokeAll($hook) {
    $args = func_get_args();
    assert($hook === array_shift($args));
    foreach ($this->moduleImplements($hook) as $extension) {
      $function = $extension . '_' . $hook;
      if (function_exists($function)) {
        call_user_func_array($function, $args);
      }
    }
  }

  /**
   * @param string $hook
   * @param mixed $data
   */
  function backdropAlter($hook, &$data) {
    $args = func_get_args();
    assert($hook === array_shift($args));
    assert($data === array_shift($args));
    while (count($args) < 3) {
      $args[] = NULL;
    }
    foreach ($this->moduleImplements($hook . '_alter') as $extension) {
      $function = $extension . '_' . $hook . '_alter';
      $function($data, $args[0], $args[1], $args[2]);
    }
  }

  /**
   * @param string $hook
   *
   * @throws \Exception
   * @return array
   */
  function moduleImplements($hook) {
    return $this->moduleImplements->moduleImplements($hook);
  }

  /**
   * Resets the module_implements() cache.
   */
  public function moduleImplementsReset() {
    $this->moduleImplements->reset();
  }
} 
