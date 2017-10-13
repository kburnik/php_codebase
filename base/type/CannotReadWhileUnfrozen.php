<?php

namespace base\type;

use base\except\MemberAccessException;

/** Trying to read a member of an immutable object before it was frozen. */
class CannotReadWhileUnfrozen extends MemberAccessException {
  // @override
  protected function buildMesasge($className, $member) {
    return "Cannot read member $className::$member while immutable object is " .
           "unfrozen. Did you forget to call the parent constructor of " .
           "$className?";
  }
}

