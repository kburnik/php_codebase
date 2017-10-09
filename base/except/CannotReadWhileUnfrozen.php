<?php

namespace base\except;

use base\except\MemberAccessException;

/** Accessed member of the class does not exist. */
class CannotReadWhileUnfrozen extends MemberAccessException {
 // @override
  protected function buildMesasge($className, $member) {
    return "Cannot read member $className::$member while immutable object is" .
           " unfrozen. Did you call the parent constructor?";
  }
}

