<?php


namespace Backdrop\xautoload\DirectoryBehavior;

/**
 * Directory behavior for PSR-0.
 *
 * This class is a marker only, to be checked with instanceof.
 * @see \Backdrop\xautoload\ClassFinder\GenericPrefixMap::loadClass()
 */
final class Psr0DirectoryBehavior implements DirectoryBehaviorInterface {
}
