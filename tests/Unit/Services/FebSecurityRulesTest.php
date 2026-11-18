<?php
declare(strict_types=1);
namespace Tests\Unit\Services;
use App\Services\Feb\FebSecurityRules;use DomainException;use PHPUnit\Framework\TestCase;
final class FebSecurityRulesTest extends TestCase {
 public function testBudgetRejectsLineOutsideExecutionBudget():void{$this->expectException(DomainException::class);FebSecurityRules::assertBudget([1=>1000.0],[2=>10.0],[]);}
 public function testBudgetAggregatesCurrentAndCommittedAmounts():void{$this->expectException(DomainException::class);FebSecurityRules::assertBudget([1=>1000.0],[1=>400.0],[1=>650.0]);}
 public function testConcurrentUpdateIsRejected():void{$this->expectException(DomainException::class);FebSecurityRules::assertUpdated(0,'conflit');}
 public function testDuplicatePaymentReferenceIsRejected():void{$this->expectException(DomainException::class);FebSecurityRules::assertPayment(1000,100,100,true);}
 public function testPaymentCannotExceedRemainingAmount():void{$this->expectException(DomainException::class);FebSecurityRules::assertPayment(1000,900,101,false);}
 public function testValidPaymentIsAccepted():void{FebSecurityRules::assertPayment(1000,900,100,false);self::addToAssertionCount(1);}
}
