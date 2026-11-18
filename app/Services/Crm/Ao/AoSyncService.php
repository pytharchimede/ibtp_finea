<?php
declare(strict_types=1);
namespace App\Services\Crm\Ao;
use App\Repositories\Crm\AoWatchRepository;
use App\Repositories\Crm\AoScoringSettingsRepository;
use PDO;

final class AoSyncService
{
    public function __construct(private PDO $pdo) {}

    public function run():array
    {
        $lock=$this->pdo->query("SELECT GET_LOCK('finea_ibtp_crm_ao_sync',0)")->fetchColumn();
        if((int)$lock!==1)throw new \RuntimeException('Une synchronisation AO est déjà en cours.');
        try{
            $adapters=[new SigomapAoAdapter()];
            if(ContenuLocalAoAdapter::isConfigured())$adapters[]=new ContenuLocalAoAdapter();
            $repo=new AoWatchRepository($this->pdo);
            $scorer=new AoScoringService();
            $settings=(new AoScoringSettingsRepository($this->pdo))->get();
            $report=['found'=>0,'new'=>0,'updated'=>0,'errors'=>0,'sources'=>[]];
            foreach($adapters as $adapter){
                $found=0;$new=0;$updated=0;$errors=0;$message='';
                try{
                    $raw=$adapter->fetchPublicOpportunities();
                    $found=count($raw);
                    $seen=[];
                    foreach($raw as $item){
                        try{
                            $normalized=$adapter->normalizeOpportunity($item);
                            $prepared=AoOpportunity::prepare($normalized);
                            $identifier=$prepared['reference_normalized']!==''?'ref:'.$prepared['reference_normalized']:'sig:'.$prepared['signature_hash'];
                            $key=$adapter->getSourceName().'|'.$identifier;
                            if(isset($seen[$key]))continue;
                            $seen[$key]=true;
                            $normalized['score_pertinence']=$scorer->scoreConfigured($normalized,$settings);
                            $outcome=$repo->upsert($normalized);
                            if($outcome==='new')$new++;else $updated++;
                        }catch(\Throwable $e){$errors++;$message='Une ou plusieurs annonces n’ont pas été enregistrées.';}
                    }
                }catch(\Throwable $e){$errors++;$message=$e->getMessage();}
                $repo->log($adapter->getSourceName(),$errors?'partial':'success',$found,$new,$updated,$message);
                $report['found']+=$found;$report['new']+=$new;
                $report['updated']+=$updated;$report['errors']+=$errors;
                $report['sources'][$adapter->getSourceName()]=compact('found','new','updated','errors','message');
            }
            return $report;
        }finally{
            $this->pdo->query("SELECT RELEASE_LOCK('finea_ibtp_crm_ao_sync')");
        }
    }
}
