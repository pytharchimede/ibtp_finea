<?php
declare(strict_types=1);
namespace App\Services\Btp;
final class FebBudgetMetrics {
 /** @param list<array{amount:float|int,status:string}> $requests @param list<float|int> $payments */
 public static function calculate(float $planned,array $requests,array $payments):array {
  $committed=0.0;$approved=0.0;
  foreach($requests as $request){$amount=(float)$request['amount'];$status=$request['status'];if(!in_array($status,['brouillon','refuse','annule'],true))$committed+=$amount;if(in_array($status,['approuve','partiellement_paye','paye'],true))$approved+=$amount;}
  return ['planned'=>$planned,'committed'=>$committed,'approved'=>$approved,'paid'=>array_sum(array_map('floatval',$payments))];
 }
}
