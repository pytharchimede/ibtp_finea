<?php
declare(strict_types=1);
namespace App\Services\Feb;
use DomainException;
final class FebSecurityRules {
 public static function assertUpdated(int $affected,string $message):void {if($affected!==1)throw new DomainException($message);}
 public static function assertPayment(float $total,float $paid,float $amount,bool $referenceExists):void {
  if($referenceExists)throw new DomainException('Cette référence de paiement existe déjà.');
  if($amount<=0||$amount>$total-$paid+0.001)throw new DomainException('Le paiement dépasse le reliquat.');
 }
 /** @param array<int,float> $limits @param array<int,float> $requested @param array<int,float> $committed */
 public static function assertBudget(array $limits,array $requested,array $committed):void {
  foreach($requested as $line=>$amount){if(!array_key_exists($line,$limits))throw new DomainException('Ligne hors budget d’exécution du chantier.');if($amount+($committed[$line]??0)>$limits[$line]+0.001)throw new DomainException('Budget insuffisant sur la ligne de déboursé #'.$line.'.');}
 }
}
