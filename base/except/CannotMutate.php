<?php

namespace base\except;

/** An update was attempted on an immutable class member. */
class CannotMutate extends MemberAccessException {
  // @override
  protected function buildMesasge($className, $member) {
    return "Cannot mutate $className::$member";
  }
}
