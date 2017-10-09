<?php

namespace base\type;

use base\except\MemberAccessException;

/** Accessed member of the class does not exist. */
class CannotReadWhileUnfrozen extends MemberAccessException {
  // @override
  protected function buildMesasge($className, $member) {
    return "Cannot read member $className::$member while immutable object is " .
           "unfrozen. Did you forget to call the parent constructor of " .
           "$className?";
  }
}

