<?php

namespace base\type;

use base\except\CannotMutate;
use base\except\CannotReadWhileUnfrozen;
use base\except\MemberNotFound;

/** An immutable object. Members can only be set once in the constructor. */
abstract class Immutable {
  // The object's members.
  private $_immutable = array();

  // Whether the object became immutable.
  private $_frozen = false;

  protected function __construct() {
    $this->_frozen = true;
  }

  public final function __get($key) {
    if (!$this->_frozen) {
       throw new CannotReadWhileUnfrozen($this, $key);
    }

    if (!array_key_exists($key, $this->_immutable)) {
      throw new MemberNotFound($this, $key);
    }

    return $this->_immutable[$key];
  }

  public final function __set($key, $value) {
    if ($this->_frozen) {
      throw new CannotMutate($this, $key);
    }

    $this->_immutable[$key] = $value;
  }
}
