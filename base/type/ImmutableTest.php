<?php

namespace base\type;

use base\except\MemberNotFound;
use base\type\CannotMutate;
use base\type\CannotReadWhileUnfrozen;
use base\type\Immutable;
use base\type\testing\BadImmutableConcept;
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

  public function testCannotCreateMemberExternally() {
    $this->expectException(CannotMutate::class);

    ImmutableConcept::of(100)->newValue = 200;
  }

  public function testCannotReadMemberWhileUnfrozen() {
    $this->expectException(CannotReadWhileUnfrozen::class);

    BadImmutableConcept::of(100)->value;
  }
}
