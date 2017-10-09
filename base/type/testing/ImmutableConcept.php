<?php

namespace base\type\testing;

use base\type\Immutable;

/** Concept of an immutable class. */
class ImmutableConcept extends Immutable {
  protected function __construct($value) {
    $this->value = $value;
    parent::__construct();
  }

  public static function of($value) {
    return new ImmutableConcept($value);
  }
}
