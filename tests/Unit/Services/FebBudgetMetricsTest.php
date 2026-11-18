<?php
declare(strict_types=1);
namespace Tests\Unit\Services;
use App\Services\Btp\FebBudgetMetrics;use PHPUnit\Framework\TestCase;
final class FebBudgetMetricsTest extends TestCase {
 public function testSeparatesPlannedCommittedApprovedAndPaid():void{
  self::assertSame(['planned'=>1000.0,'committed'=>500.0,'approved'=>300.0,'paid'=>125.0],FebBudgetMetrics::calculate(1000,[['amount'=>200,'status'=>'soumis'],['amount'=>300,'status'=>'partiellement_paye'],['amount'=>900,'status'=>'refuse']],[100,25]));
 }
}
