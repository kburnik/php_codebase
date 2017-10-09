<?php

namespace base\type\testing;

use base\type\Immutable;

/**
 * Concept of an immutable class which does not freeze it's state as it should.
 */
class BadImmutableConcept extends Immutable {
  protected function __construct($value) {
    $this->value = $value;
    // parent::__construct() is left out as if accidental. This will cause an
    // exception if any of the object members are read. This behavior guarantees
    // immutability once the object is constructed.
  }

  public static function of($value) {
    return new BadImmutableConcept($value);
  }
}
