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
        $q=$this->pdo->prepare('SELECT p.id,p.reference,p.intitule,p.client_nom,o.currency,o.deadline FROM btp_projets p LEFT JOIN crm_opportunities o ON o.id=p.crm_opportunity_id WHERE p.id=?');
        $q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:null;
    }
    public function units(): array
    {
        return $this->pdo->query('SELECT code,label FROM btp_quote_units WHERE active=1 ORDER BY sort_order,label')->fetchAll(PDO::FETCH_ASSOC);
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
        $allowedUnits=array_column($this->units(),'code');
        foreach($lines as $line){
            if(!is_array($line))throw new InvalidArgumentException('Ligne invalide.');
            $desc=trim((string)($line['description']??''));$unit=trim((string)($line['unit']??''));
            $qty=(string)($line['quantity']??'');$price=(string)($line['unit_price']??'');
            $group=trim((string)($line['group_name']??''));
            if(mb_strlen($group)>180||!in_array($unit,$allowedUnits,true))throw new InvalidArgumentException('Groupe ou unité invalide.');
            if($desc===''||mb_strlen($desc)>500||$unit===''||mb_strlen($unit)>30||!preg_match('/^\d{1,10}(?:\.\d{1,3})?$/',$qty)||!preg_match('/^\d{1,12}(?:\.\d{1,2})?$/',$price)|| (float)$qty<=0)throw new InvalidArgumentException('Vérifiez la désignation, l’unité, la quantité et le prix.');
            $lineCents=(int)round((float)$qty*(float)$price*100);
            if($lineCents<0||$lineCents>9000000000000000)throw new InvalidArgumentException('Montant de ligne trop élevé.');
            $totalCents+=$lineCents;$parsed[]=[$desc,$unit,$qty,$price,number_format($lineCents/100,2,'.',''),$group!==''?$group:null];
        }
        $discount=(string)($data['discount_amount']??'0');
        if(!preg_match('/^\d{1,12}(?:\.\d{1,2})?$/',$discount))throw new InvalidArgumentException('Remise invalide.');
        $discountCents=(int)round((float)$discount*100);
        if($discountCents>$totalCents)throw new InvalidArgumentException('La remise dépasse le montant HT.');
        $taxable=$totalCents-$discountCents;
        $vat=!empty($data['vat_enabled'])?(int)round($taxable*0.18):0;
        $validityDays=trim((string)($data['validity_days']??'30'));
        if($validityDays===''||!ctype_digit($validityDays)||(int)$validityDays>3650)throw new InvalidArgumentException('Durée de validité invalide.');
        $calculatedEnd=(new \DateTimeImmutable($date))->modify('+'.(int)$validityDays.' days')->format('Y-m-d');
        if($valid!==''&&$valid!==$calculatedEnd)throw new InvalidArgumentException('Les dates et la durée de validité ne correspondent pas.');
        $valid=$calculatedEnd;
        $billingDate=(string)($data['billing_date']??'');
        if(!self::validDate($billingDate))throw new InvalidArgumentException('Date de facturation invalide.');
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
            $q=$this->pdo->prepare('INSERT INTO btp_offer_drafts(projet_id,revision,title,issue_date,validity_date,delivery_days,correspondent,terms,vat_enabled,discount_amount,total_ht,vat_amount,total_ttc,created_by,validity_days,billing_date) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $q->execute([$projectId,$revision,$title,$date,$valid!==''?$valid:null,$days!==''?(int)$days:null,$correspondent,$terms,!empty($data['vat_enabled'])?1:0,number_format($discountCents/100,2,'.',''),number_format($taxable/100,2,'.',''),number_format($vat/100,2,'.',''),number_format(($taxable+$vat)/100,2,'.',''),$actor,(int)$validityDays,$billingDate]);
            $id=(int)$this->pdo->lastInsertId();
            $q=$this->pdo->prepare('INSERT INTO btp_offer_lines(offer_id,line_order,description,unit,quantity,unit_price,line_total,group_name) VALUES(?,?,?,?,?,?,?,?)');
            foreach($parsed as $i=>$line)$q->execute([$id,$i+1,...$line]);
            $this->pdo->commit();return $id;
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }
    public function transition(int $id,string $action,int $actor): void {
        if($actor<1)throw new InvalidArgumentException('Utilisateur non identifié.');
        $map=[
            'soumettre_dt'=>['brouillon','soumis_dt'],
            'valider_dt'=>['soumis_dt','valide_dt'],
            'soumettre_dg'=>['valide_dt','soumis_dg'],
            'valider_dg'=>['soumis_dg','valide_dg'],
            'transmettre_client'=>['valide_dg','transmis_client'],
            'accepter_client'=>['transmis_client','accepte_client'],
        ];
        if(!isset($map[$action]))throw new InvalidArgumentException('Transition de devis inconnue.');
        [$from,$to]=$map[$action];
        $this->pdo->beginTransaction();
        try{
            $q=$this->pdo->prepare('SELECT id,projet_id,status FROM btp_offer_drafts WHERE id=? FOR UPDATE');$q->execute([$id]);$offer=$q->fetch(PDO::FETCH_ASSOC);
            if(!$offer||$offer['status']!==$from)throw new InvalidArgumentException('Le statut du devis ne permet pas cette validation.');
            if($action==='accepter_client'){
                $q=$this->pdo->prepare('SELECT COUNT(*) FROM btp_client_orders WHERE offer_id=? AND document_id IS NOT NULL');$q->execute([$id]);
                if(!(int)$q->fetchColumn())throw new InvalidArgumentException('Un BC avec justificatif lié à ce devis est requis pour confirmer l’acceptation client.');
            }
            $q=$this->pdo->prepare('UPDATE btp_offer_drafts SET status=? WHERE id=? AND status=?');$q->execute([$to,$id,$from]);
            $this->pdo->commit();
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }
    private static function validDate(string $s): bool
    {
        $d=\DateTimeImmutable::createFromFormat('!Y-m-d',$s);
        return $d!==false&&$d->format('Y-m-d')===$s;
    }
}
