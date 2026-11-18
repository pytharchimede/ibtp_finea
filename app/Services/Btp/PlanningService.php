<?php
declare(strict_types=1);
namespace App\Services\Btp;
use PDO;
use InvalidArgumentException;
final class PlanningService
{
    public function __construct(private PDO $pdo) {}
    public function all(): array
    {
        return $this->pdo->query("SELECT pl.id,pl.projet_id,pl.revision,pl.title,pl.status,pl.created_at,p.reference,p.intitule,p.client_nom,COUNT(t.id) AS tasks_count,MIN(t.start_date) AS starts,MAX(t.end_date) AS ends FROM btp_project_plannings pl JOIN btp_projets p ON p.id=pl.projet_id LEFT JOIN btp_project_planning_tasks t ON t.planning_id=pl.id GROUP BY pl.id,pl.projet_id,pl.revision,pl.title,pl.status,pl.created_at,p.reference,p.intitule,p.client_nom ORDER BY pl.created_at DESC,pl.id DESC LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function save(int $projectId,array $data,?int $actor): int
    {
        $title=trim((string)($data['title']??''));
        if($title===''||mb_strlen($title)>180)throw new InvalidArgumentException('Intitulé du planning invalide.');
        $tasks=$data['planning']??[];
        if(!is_array($tasks)||count($tasks)<1||count($tasks)>100)throw new InvalidArgumentException('Ajoutez entre 1 et 100 tâches.');
        $parsed=[];
        foreach($tasks as $task){
            if(!is_array($task))throw new InvalidArgumentException('Tâche invalide.');
            $label=trim((string)($task['label']??''));$start=(string)($task['start_date']??'');$end=(string)($task['end_date']??'');
            $a=\DateTimeImmutable::createFromFormat('!Y-m-d',$start);$z=\DateTimeImmutable::createFromFormat('!Y-m-d',$end);
            if($label===''||mb_strlen($label)>250||!$a||!$z||$a->format('Y-m-d')!==$start||$z->format('Y-m-d')!==$end||$end<$start)throw new InvalidArgumentException('Vérifiez les intitulés et dates des tâches.');
            $parsed[]=[$label,$start,$end];
        }
        $this->pdo->beginTransaction();
        try{
            $q=$this->pdo->prepare('SELECT id FROM btp_projets WHERE id=? FOR UPDATE');$q->execute([$projectId]);
            if(!$q->fetchColumn())throw new InvalidArgumentException('Projet introuvable.');
            $q=$this->pdo->prepare('SELECT COALESCE(MAX(revision),0)+1 FROM btp_project_plannings WHERE projet_id=?');$q->execute([$projectId]);$rev=(int)$q->fetchColumn();
            $q=$this->pdo->prepare('INSERT INTO btp_project_plannings(projet_id,revision,title,created_by) VALUES(?,?,?,?)');$q->execute([$projectId,$rev,$title,$actor]);$id=(int)$this->pdo->lastInsertId();
            $q=$this->pdo->prepare('INSERT INTO btp_project_planning_tasks(planning_id,task_order,label,start_date,end_date) VALUES(?,?,?,?,?)');
            foreach($parsed as $i=>$task)$q->execute([$id,$i+1,...$task]);
            $this->pdo->commit();return $id;
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }
}
