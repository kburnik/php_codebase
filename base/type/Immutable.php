<?php

namespace base\type;

use base\except\CannotMutate;
use base\except\MemberNotFound;

/** An immutable object. Members can only be set within the class. */
abstract class Immutable {
  private $_immutable = array();

  public final function __get($key) {
    if (!array_key_exists($key, $this->_immutable)) {
      throw new MemberNotFound($this, $key);
    }

    return $this->_immutable[$key];
  }

  public final function __set($key, $value) {
    if (array_key_exists($key, $this->_immutable)) {
      throw new CannotMutate($this, $key);
    }

    $this->_immutable[$key] = $value;
  }
}

