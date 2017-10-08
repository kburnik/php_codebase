<?php

namespace base\except;

use Exception;

/** Member access was invalid. */
abstract class MemberAccessException extends Exception {
  public function __construct(
      $object, $member, $code = 0, Exception $previous = null) {
    parent::__construct(
      $this->buildMesasge(get_class($object), $member),
      $code,
      $previous);
  }

  abstract protected function buildMesasge($className, $member);
}
