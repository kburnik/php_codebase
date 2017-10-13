<?php

namespace base\except;

use base\except\MemberAccessException;

/** Accessed member of the class does not exist. */
class MemberNotFound extends MemberAccessException {
  // @override
  protected function buildMesasge($className, $member) {
    return "Non-existing member $className::$member";
  }
}

