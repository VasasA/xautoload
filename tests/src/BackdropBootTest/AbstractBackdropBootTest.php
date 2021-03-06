<?php


namespace Backdrop\xautoload\Tests\BackdropBootTest;

use Backdrop\xautoload\Tests\Example\AbstractExampleModules;
use Backdrop\xautoload\Tests\VirtualBackdrop\BackdropEnvironment;
use Backdrop\xautoload\Tests\Util\CallLog;
use Backdrop\xautoload\Tests\Util\StaticCallLog;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @see BackdropBootHookTest
 * @see BackdropBootTest
 */
abstract class AbstractBackdropBootTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var BackdropEnvironment
   */
  protected $exampleBackdrop;

  /**
   * @var AbstractExampleModules
   */
  protected $exampleModules;

  /**
   * @var CallLog
   */
  protected $callLog;

  /**
   * Multiplies a given array of variations.
   *
   * @param array[] $bases
   * @param string|int $key
   * @param mixed[] $values
   *
   * @return array[]
   */
  protected function providerArrayKeyVariations(array $bases, $key, array $values) {
    $result = array();
    foreach ($bases as $variation) {
      foreach ($values as $value) {
        $variation[$key] = $value;
        $result[] = $variation;
      }
    }
    return $result;
  }

  /**
   * Tests a simulated regular request.
   */
  function testNormalRequest() {

    $this->prepare();

    $this->prepareAllEnabled();

    $this->exampleBackdrop->boot();

    $expectedCalls = $this->getExpectedCallsForNormalRequest();

    $this->callLog->assertCalls($this, $expectedCalls);

    // Now we want all classes to be available.
    foreach ($this->exampleModules->getExampleClasses() as $classes) {
      foreach ((array)$classes as $class) {
        $this->assertClassExists($class);
      }
    }

    $this->unprepare();
  }

  /**
   * Tests a request where modules are enabled, but xautoload is already
   * enabled.
   *
   * @dataProvider providerModuleEnable
   *
   * @param mixed[] $initialModules
   *   Initial modules being installed / enabled.
   * @param array $expectedCalls
   *
   * @throws \Exception
   */
  function testModuleEnable(array $initialModules, array $expectedCalls) {

    $this->prepare();

    $this->prepareInitialModules($initialModules);

    foreach ($this->exampleModules->getExampleClasses() as $classes) {
      foreach ((array)$classes as $class) {
        $this->assertClassIsUndefined($class);
      }
    }

    $this->exampleBackdrop->boot();

    $new_modules = array_keys($this->exampleModules->getExampleClasses());
    $this->exampleBackdrop->moduleEnable($new_modules);

    # HackyLog::log($this->callLog->getCalls());

    $this->callLog->assertCalls($this, $expectedCalls);

    // Now we want all classes to be available.
    foreach ($this->exampleModules->getExampleClasses() as $classes) {
      foreach ((array)$classes as $class) {
        $this->assertClassExists($class);
      }
    }

    $this->unprepare();
  }

  /**
   * @return array[]
   */
  abstract public function providerModuleEnable();

  /**
   * Start with all available modules enabled.
   */
  private function prepareAllEnabled() {
    foreach (array('system', 'xautoload', 'libraries') as $name) {
      $this->exampleBackdrop->getSystemTable()->moduleSetEnabled($name);
    }
    foreach ($this->exampleModules->getExampleClasses() as $name => $classes) {
      $this->exampleBackdrop->getSystemTable()->moduleSetEnabled($name);
    }
    $this->exampleBackdrop->getSystemTable()->moduleSetWeight('xautoload', -90);
  }

  /**
   * @param mixed[] $initialModules
   *   Initial modules being installed / enabled.
   *
   * @throws \Exception
   */
  private function prepareInitialModules($initialModules) {
    foreach ($initialModules as $name => $state) {
      if (TRUE === $state) {
        // Module is installed and enabled.
        $this->exampleBackdrop->getSystemTable()->moduleSetEnabled($name);
        $this->exampleBackdrop->getSystemTable()->moduleSetSchemaVersion($name, 7000);
      }
      elseif (FALSE === $state) {
        // Module is installed, but disabled.
        $this->exampleBackdrop->getSystemTable()->moduleSetSchemaVersion($name, 7000);
      }
      elseif (NULL === $state) {
        // Module is neither installed nor enabled.
      }
      else {
        throw new \Exception("Unexpected state.");
      }
    }
    if (isset($initialModules['xautoload'])) {
      // xautoload is installed or enabled, so the module weight must be in the database.
      $this->exampleBackdrop->getSystemTable()->moduleSetWeight('xautoload', -90);
    }
  }

  /**
   * setUp() does not help us because of the process sharing problem.
   * So we use this instead.
   *
   * @throws \Exception
   */
  abstract protected function prepare();

  /**
   * Runs after a test is finished.
   */
  private function unprepare() {
    stream_wrapper_unregister('test');
    StaticCallLog::unsetCallLog();
  }

  /**
   * @param string $class
   */
  public function assertLoadClass($class) {
    $this->assertFalse(class_exists($class, FALSE), "Class '$class' is not defined yet.");
    $this->assertTrue(class_exists($class), "Class '$class' successfully loaded.");
  }

  /**
   * @param string $class
   */
  public function assertClassExists($class) {
    $this->assertTrue(class_exists($class), "Class '$class' exists.");
  }

  /**
   * @param string $class
   */
  public function assertClassIsDefined($class) {
    $this->assertTrue(class_exists($class, FALSE), "Class '$class' is defined.");
  }

  /**
   * @param string $class
   */
  public function assertClassIsUndefined($class) {
    $this->assertFalse(class_exists($class, FALSE), "Class '$class' is undefined.");
  }

  /**
   * @return array[]
   */
  abstract protected function getExpectedCallsForNormalRequest();

}
