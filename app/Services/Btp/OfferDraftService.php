<?php
declare(strict_types=1);
namespace App\Services\Btp;
use PDO;
use InvalidArgumentException;
final class OfferDraftService
{
    public function __construct(private PDO $pdo) {}
    public function project(int $id): ?array
    {
        $q=$this->pdo->prepare('SELECT id,reference,intitule,client_nom FROM btp_projets WHERE id=?');
        $q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:null;
    }
    public function save(int $projectId,array $data,?int $actor): int
    {
        $title=trim((string)($data['title']??''));
        $date=(string)($data['issue_date']??'');
        $valid=(string)($data['validity_date']??'');
        if($title===''||mb_strlen($title)>180||!self::validDate($date)||($valid!==''&&!self::validDate($valid)))throw new InvalidArgumentException('Intitulé ou dates invalides.');
        if($valid!==''&&$valid<$date)throw new InvalidArgumentException('La date de validité doit suivre la date d’émission.');
        $lines=$data['lines']??[];
        if(!is_array($lines)||count($lines)<1||count($lines)>200)throw new InvalidArgumentException('Ajoutez entre 1 et 200 lignes de devis.');
        $parsed=[];$totalCents=0;
        foreach($lines as $line){
            if(!is_array($line))throw new InvalidArgumentException('Ligne invalide.');
            $desc=trim((string)($line['description']??''));$unit=trim((string)($line['unit']??''));
            $qty=(string)($line['quantity']??'');$price=(string)($line['unit_price']??'');
            if($desc===''||mb_strlen($desc)>500||$unit===''||mb_strlen($unit)>30||!preg_match('/^\d{1,10}(?:\.\d{1,3})?$/',$qty)||!preg_match('/^\d{1,12}(?:\.\d{1,2})?$/',$price)|| (float)$qty<=0)throw new InvalidArgumentException('Vérifiez la désignation, l’unité, la quantité et le prix.');
            $lineCents=(int)round((float)$qty*(float)$price*100);
            if($lineCents<0||$lineCents>9000000000000000)throw new InvalidArgumentException('Montant de ligne trop élevé.');
            $totalCents+=$lineCents;$parsed[]=[$desc,$unit,$qty,$price,number_format($lineCents/100,2,'.','')];
        }
        $discount=(string)($data['discount_amount']??'0');
        if(!preg_match('/^\d{1,12}(?:\.\d{1,2})?$/',$discount))throw new InvalidArgumentException('Remise invalide.');
        $discountCents=(int)round((float)$discount*100);
        if($discountCents>$totalCents)throw new InvalidArgumentException('La remise dépasse le montant HT.');
        $taxable=$totalCents-$discountCents;
        $vat=!empty($data['vat_enabled'])?(int)round($taxable*0.18):0;
        $planning=$data['planning']??[];
        if(!is_array($planning)||count($planning)<1||count($planning)>100)throw new InvalidArgumentException('Ajoutez entre 1 et 100 tâches de planning.');
        $tasks=[];
        foreach($planning as $task){
            if(!is_array($task))throw new InvalidArgumentException('Tâche invalide.');
            $label=trim((string)($task['label']??''));$start=(string)($task['start_date']??'');$end=(string)($task['end_date']??'');
            if($label===''||mb_strlen($label)>250||!self::validDate($start)||!self::validDate($end)||$end<$start)throw new InvalidArgumentException('Vérifiez les tâches et leurs dates.');
            $tasks[]=[$label,$start,$end];
        }
        $days=trim((string)($data['delivery_days']??''));
        if($days!==''&&(!ctype_digit($days)||(int)$days>36500))throw new InvalidArgumentException('Délai invalide.');
        $correspondent=trim((string)($data['correspondent']??''));
        $terms=trim((string)($data['terms']??''));
        if(mb_strlen($correspondent)>180||mb_strlen($terms)>10000)throw new InvalidArgumentException('Texte trop long.');
        $this->pdo->beginTransaction();
        try{
            $lock=$this->pdo->prepare('SELECT id FROM btp_projets WHERE id=? FOR UPDATE');$lock->execute([$projectId]);
            if(!$lock->fetchColumn())throw new InvalidArgumentException('Projet introuvable.');
            $v=$this->pdo->prepare('SELECT COALESCE(MAX(revision),0)+1 FROM btp_offer_drafts WHERE projet_id=?');$v->execute([$projectId]);$revision=(int)$v->fetchColumn();
            $q=$this->pdo->prepare('INSERT INTO btp_offer_drafts(projet_id,revision,title,issue_date,validity_date,delivery_days,correspondent,terms,vat_enabled,discount_amount,total_ht,vat_amount,total_ttc,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $q->execute([$projectId,$revision,$title,$date,$valid!==''?$valid:null,$days!==''?(int)$days:null,$correspondent,$terms,!empty($data['vat_enabled'])?1:0,number_format($discountCents/100,2,'.',''),number_format($taxable/100,2,'.',''),number_format($vat/100,2,'.',''),number_format(($taxable+$vat)/100,2,'.',''),$actor]);
            $id=(int)$this->pdo->lastInsertId();
            $q=$this->pdo->prepare('INSERT INTO btp_offer_lines(offer_id,line_order,description,unit,quantity,unit_price,line_total) VALUES(?,?,?,?,?,?,?)');
            foreach($parsed as $i=>$line)$q->execute([$id,$i+1,...$line]);
            $q=$this->pdo->prepare('INSERT INTO btp_offer_planning(offer_id,task_order,label,start_date,end_date) VALUES(?,?,?,?,?)');
            foreach($tasks as $i=>$task)$q->execute([$id,$i+1,...$task]);
            $this->pdo->commit();return $id;
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }
    private static function validDate(string $s): bool
    {
        $d=\DateTimeImmutable::createFromFormat('!Y-m-d',$s);
        return $d!==false&&$d->format('Y-m-d')===$s;
    }
}
