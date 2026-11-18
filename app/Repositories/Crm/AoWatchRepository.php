<?php
declare(strict_types=1);
namespace App\Repositories\Crm;
use App\Services\Crm\Ao\AoOpportunity;
use PDO;

final class AoWatchRepository
{
    public function __construct(private PDO $pdo) {}

    public function search(array $filters=[], int $limit=300): array
    {
        $sql='SELECT * FROM crm_ao_notices WHERE 1=1';
        $params=[];
        $scope=(string)($filters['deadline_scope']??'active');
        if ($scope==='active') $sql.=' AND date_limite >= CURDATE()';
        elseif ($scope==='expired') $sql.=' AND date_limite < CURDATE()';
        elseif ($scope==='undated') $sql.=' AND date_limite IS NULL';
        $q=mb_substr(trim((string)($filters['q']??'')),0,150);
        if ($q!=='') {
            $sql.=' AND (titre LIKE :q1 OR reference LIKE :q2 OR numero_ao LIKE :q3 OR autorite_contractante LIKE :q4 OR resume LIKE :q5)';
            foreach(['q1','q2','q3','q4','q5'] as $key) $params[$key]='%'.$q.'%';
        }
        $status=(string)($filters['status']??'');
        if(in_array($status,self::statuses(),true)){$sql.=' AND statut_interne=:status';$params['status']=$status;}
        $source=(string)($filters['source']??'');
        if(in_array($source,['DGMP public','Contenu local DGH'],true)){$sql.=' AND source=:source';$params['source']=$source;}
        $score=(string)($filters['min_score']??'');
        if($score==='recommended'){$settings=(new AoScoringSettingsRepository($this->pdo))->get();$sql.=' AND score_pertinence>=:score';$params['score']=(int)$settings['min_score'];}
        elseif(in_array($score,['40','60','80'],true)){$sql.=' AND score_pertinence>=:score';$params['score']=(int)$score;}
        if(!empty($filters['deadline_soon']))$sql.=' AND date_limite BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)';
        $sql.=' ORDER BY score_pertinence DESC,(date_limite IS NULL),date_limite ASC,id DESC LIMIT '.max(1,min(1000,$limit));
        $stmt=$this->pdo->prepare($sql);$stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rescore(\App\Services\Crm\Ao\AoScoringService $scorer,array $settings):int
    {
        $count=0;
        $query=$this->pdo->query('SELECT id,titre,resume,secteur,type_marche,lieu_execution FROM crm_ao_notices');
        $update=$this->pdo->prepare('UPDATE crm_ao_notices SET score_pertinence=? WHERE id=?');
        while($item=$query->fetch(PDO::FETCH_ASSOC)){
            $update->execute([$scorer->scoreConfigured($item,$settings),(int)$item['id']]);
            $count++;
        }
        $query->closeCursor();
        return $count;
    }

    public function stats():array
    {
        return $this->pdo->query("SELECT COUNT(*) total,
            COALESCE(SUM(score_pertinence>=60),0) relevant,
            COALESCE(SUM(date_limite BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 14 DAY)),0) soon,
            COALESCE(SUM(crm_opportunity_id IS NOT NULL),0) converted
            FROM crm_ao_notices")->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id):?array
    {
        $q=$this->pdo->prepare('SELECT * FROM crm_ao_notices WHERE id=?');
        $q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:null;
    }

    public function logs():array
    {
        return $this->pdo->query('SELECT * FROM crm_ao_sync_logs ORDER BY id DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function statuses():array
    {
        return ['nouveau','a_analyser','interessant','non_interessant','dossier_en_preparation','soumission','soumissionne','converti_crm','gagne','perdu','expire'];
    }

    public function upsert(array $raw):string
    {
        $data=AoOpportunity::prepare($raw);
        if($data['titre']==='')throw new \DomainException("Appel d'offres sans intitulé.");
        $identifier=$data['reference_normalized']!==''?'ref:'.$data['reference_normalized']:'signature:'.$data['signature_hash'];
        // Le préfixe source protège les références identiques publiées par des portails distincts.
        $key=hash('sha256',$data['source'].'|'.$identifier);
        $current=$this->pdo->prepare('SELECT id FROM crm_ao_notices WHERE source_key=?');
        $current->execute([$key]);$id=(int)($current->fetchColumn()?:0);
        $values=[
            $data['source'],mb_substr($data['numero_ao'],0,160),mb_substr($data['reference'],0,160),
            mb_substr($data['titre'],0,700),mb_substr($data['autorite_contractante'],0,500),
            mb_substr($data['type_marche'],0,160),mb_substr($data['secteur'],0,255),
            $data['date_publication'],$data['date_limite'],mb_substr($data['lieu_execution'],0,255),
            $data['resume'],$data['url_source'],$data['score_pertinence'],
            $data['documents_source_json'],$data['details_source_json']
        ];
        if($id){
            $q=$this->pdo->prepare('UPDATE crm_ao_notices SET
                source=?,numero_ao=?,reference=?,titre=?,autorite_contractante=?,type_marche=?,secteur=?,
                date_publication=?,date_limite=?,lieu_execution=?,resume=?,url_source=?,score_pertinence=?,
                documents_source_json=?,details_source_json=?,last_seen_at=NOW()
                WHERE id=?');
            $q->execute([...$values,$id]);return 'updated';
        }
        $q=$this->pdo->prepare('INSERT INTO crm_ao_notices
            (source,numero_ao,reference,titre,autorite_contractante,type_marche,secteur,
             date_publication,date_limite,lieu_execution,resume,url_source,score_pertinence,
             documents_source_json,details_source_json,source_key,last_seen_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
        $q->execute([...$values,$key]);return 'new';
    }

    public function log(string $source,string $status,int $found,int $new,int $updated,string $error=''):void
    {
        $q=$this->pdo->prepare('INSERT INTO crm_ao_sync_logs
            (source,status,found_count,new_count,updated_count,error_message) VALUES (?,?,?,?,?,?)');
        $q->execute([$source,$status,$found,$new,$updated,mb_substr($error,0,500)?:null]);
    }

    public function setStatus(int $id,string $status):void
    {
        if(!in_array($status,self::statuses(),true)||$status==='converti_crm')throw new \DomainException('Statut AO non autorisé pour une modification manuelle.');
        $q=$this->pdo->prepare('UPDATE crm_ao_notices SET statut_interne=:status WHERE id=:id AND crm_opportunity_id IS NULL');
        $q->execute(['status'=>$status,'id'=>$id]);
        if($q->rowCount()===0){
            $row=$this->find($id);
            if(!$row)throw new \DomainException("Appel d'offres introuvable.");
            if($row['crm_opportunity_id']!==null)throw new \DomainException('Cet AO est déjà rattaché à une opportunité.');
        }
    }

    public function convert(int $id,int $clientId):int
    {
        if($id<=0||$clientId<=0)throw new \DomainException('Sélectionnez un client valide.');
        $this->pdo->beginTransaction();
        try{
            $q=$this->pdo->prepare('SELECT * FROM crm_ao_notices WHERE id=? FOR UPDATE');
            $q->execute([$id]);$ao=$q->fetch(PDO::FETCH_ASSOC);
            if(!$ao)throw new \DomainException("Appel d'offres introuvable.");
            if($ao['crm_opportunity_id']!==null){
                $this->pdo->commit();return (int)$ao['crm_opportunity_id'];
            }
            if(!$ao['date_limite']||$ao['date_limite']<date('Y-m-d'))throw new \DomainException('AO expiré ou sans échéance vérifiée : conversion impossible.');
            if($ao['statut_interne']==='non_interessant')throw new \DomainException("Cet AO est classé non intéressant.");
            $q=$this->pdo->prepare('SELECT id FROM crm_clients WHERE id=?');
            $q->execute([$clientId]);
            if(!$q->fetchColumn())throw new \DomainException('Client ou prospect introuvable.');
            $description="Source AO : ".$ao['source']."\nRéférence : ".(($ao['numero_ao']?:$ao['reference'])?:'-')
                ."\nAutorité : ".$ao['autorite_contractante']."\nSource : ".$ao['url_source']
                ."\n\n".($ao['resume']?:'');
            $title=mb_substr((string)$ao['titre'],0,180);
            $probability=max(10,min(90,(int)$ao['score_pertinence']));
            $q=$this->pdo->prepare("INSERT INTO crm_opportunities
                (client_id,title,stage,estimated_amount,currency,expected_close_date,probability,description,deadline)
                VALUES (?,?,'qualification',NULL,'XOF',?,?,?,?)");
            $q->execute([$clientId,$title,$ao['date_limite'],$probability,$description,$ao['date_limite']]);
            $oppId=(int)$this->pdo->lastInsertId();
            $q=$this->pdo->prepare("UPDATE crm_ao_notices SET
                crm_opportunity_id=?,statut_interne='converti_crm' WHERE id=?");
            $q->execute([$oppId,$id]);$this->pdo->commit();
            return $oppId;
        }catch(\Throwable $e){
            if($this->pdo->inTransaction())$this->pdo->rollBack();
            throw $e;
        }
    }
}
