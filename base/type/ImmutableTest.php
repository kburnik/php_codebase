<?php

namespace base\type;

use base\except\CannotMutate;
use base\except\MemberNotFound;
use base\type\Immutable;
use base\type\testing\ImmutableConcept;
use PHPUnit\Framework\TestCase;

class ImmutableTest extends TestCase {
  public function testCanReadValue() {
    $this->assertEquals(100, ImmutableConcept::of(100)->value);
  }

  public function testCannotWriteValue() {
    $this->expectException(CannotMutate::class);

    ImmutableConcept::of(100)->value = 200;
  }

  public function testCannotReadNonExistingMember() {
    $this->expectException(MemberNotFound::class);

    ImmutableConcept::of(100)->notTheDroidsYouAreLookingFor;
  }
}
