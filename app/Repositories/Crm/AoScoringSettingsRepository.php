<?php
declare(strict_types=1);
namespace App\Repositories\Crm;
use PDO;
final class AoScoringSettingsRepository
{
    public function __construct(private PDO $pdo){}
    public function get():array
    {
        return $this->pdo->query('SELECT * FROM crm_ao_scoring_settings WHERE id=1')->fetch(PDO::FETCH_ASSOC)?:[
            'keywords'=>'','excluded_keywords'=>'','min_score'=>60
        ];
    }
    public function save(array $data,int $actor):void
    {
        $q=$this->pdo->prepare('UPDATE crm_ao_scoring_settings SET keywords=?,excluded_keywords=?,min_score=?,updated_by=? WHERE id=1');
        $q->execute([$data['keywords'],$data['excluded_keywords'],$data['min_score'],$actor]);
    }
}
