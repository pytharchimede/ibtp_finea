<?php
declare(strict_types=1);
namespace App\Services\Btp;
use PDO;
use InvalidArgumentException;
final class DossierService
{
    public function __construct(private PDO $pdo) {}
    public function project(int $id): ?array {
        $q=$this->pdo->prepare('SELECT p.*,o.description,o.deadline,o.estimated_amount,o.currency,ao.url_source FROM btp_projets p LEFT JOIN crm_opportunities o ON o.id=p.crm_opportunity_id LEFT JOIN crm_ao_notices ao ON ao.crm_opportunity_id=o.id WHERE p.id=?');
        $q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC)?:null;
    }
    public function events(int $id):array {
        $q=$this->pdo->prepare('SELECT * FROM btp_dossier_events WHERE projet_id=? ORDER BY occurred_at DESC,id DESC LIMIT 300');
        $q->execute([$id]);return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function quotes(int $id):array {
        $q=$this->pdo->prepare('SELECT id,revision,title AS label,total_ht AS amount_ht,total_ttc,status,created_at FROM btp_offer_drafts WHERE projet_id=? ORDER BY revision DESC,id DESC');
        $q->execute([$id]);return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function documents(int $id): array {
        $q=$this->pdo->prepare('SELECT id,projet_id,category,label,version,original_name,mime_type,file_size,sha256,created_at FROM btp_documents WHERE projet_id=? ORDER BY created_at DESC,id DESC LIMIT 500');
        $q->execute([$id]);return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function document(int $projectId,int $documentId): ?array {
        $q=$this->pdo->prepare('SELECT * FROM btp_documents WHERE projet_id=? AND id=?');
        $q->execute([$projectId,$documentId]);return $q->fetch(PDO::FETCH_ASSOC)?:null;
    }
    public function uploadDocuments(int $projectId,array $files,?int $actor): int {
        if(!$this->project($projectId))throw new InvalidArgumentException('Projet introuvable.');
        $names=$files['name']??[];
        if(!is_array($names)||count($names)<1||count($names)>20)throw new InvalidArgumentException('Sélectionnez entre 1 et 20 documents.');
        $count=0;
        foreach(array_keys($names) as $index){
            $file=[];
            foreach(['name','type','tmp_name','error','size'] as $key)$file[$key]=$files[$key][$index]??null;
            $label=trim(pathinfo(basename((string)$file['name']),PATHINFO_FILENAME));
            if($label==='')$label='Document';
            $this->uploadDocument($projectId,['category'=>'autre','label'=>mb_substr($label,0,180)],$file,$actor);
            $count++;
        }
        return $count;
    }
    public function uploadDocument(int $projectId,array $input,array $file,?int $actor): void {
        if(!$this->project($projectId))throw new InvalidArgumentException('Projet introuvable.');
        $category=(string)($input['category']??'');
        if(!in_array($category,['dce','administratif','technique','financier','client','chantier','autre'],true))throw new InvalidArgumentException('Catégorie invalide.');
        $label=trim((string)($input['label']??''));
        if($label===''||mb_strlen($label)>180)throw new InvalidArgumentException('Intitulé du document invalide.');
        $uploadError=(int)($file['error']??UPLOAD_ERR_NO_FILE);
        if($uploadError!==UPLOAD_ERR_OK){
            $messages=[UPLOAD_ERR_INI_SIZE=>'Le fichier dépasse la limite upload_max_filesize du serveur.',UPLOAD_ERR_FORM_SIZE=>'Le fichier dépasse la limite du formulaire.',UPLOAD_ERR_PARTIAL=>'Le transfert du fichier est incomplet.',UPLOAD_ERR_NO_FILE=>'Aucun fichier reçu.',UPLOAD_ERR_NO_TMP_DIR=>'Dossier temporaire PHP indisponible.',UPLOAD_ERR_CANT_WRITE=>'Écriture du fichier temporaire impossible.',UPLOAD_ERR_EXTENSION=>'Téléversement interrompu par une extension PHP.'];
            throw new InvalidArgumentException($messages[$uploadError]??'Échec du transfert du fichier.');
        }
        if(!isset($file['tmp_name'])||!is_uploaded_file((string)$file['tmp_name']))throw new InvalidArgumentException('Fichier temporaire non reçu.');
        $size=(int)($file['size']??0);
        if($size<1||$size>20*1024*1024)throw new InvalidArgumentException('Taille maximale : 20 Mo.');
        $mime=(new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name'])?:'application/octet-stream';
        // Conserver les fichiers de tout format hors du répertoire web, sans jamais utiliser leur nom d'origine comme chemin.
        $dir=BASE_PATH.'/storage/private/btp';
        if(!is_dir($dir)&&!mkdir($dir,0700,true)&&!is_dir($dir))throw new \RuntimeException('Stockage indisponible.');
        $name=bin2hex(random_bytes(24));
        $destination=$dir.'/'.$name;
        if(!move_uploaded_file($file['tmp_name'],$destination))throw new \RuntimeException('Impossible de conserver le fichier.');
        if(!chmod($destination,0600)){@unlink($destination);throw new \RuntimeException('Permissions du document impossibles à définir.');}
        try {
            $hash=hash_file('sha256',$destination);
            $this->pdo->beginTransaction();
            $lock=$this->pdo->prepare('SELECT id FROM btp_projets WHERE id=? FOR UPDATE');
            $lock->execute([$projectId]);
            if(!$lock->fetchColumn())throw new InvalidArgumentException('Projet introuvable.');
            $v=$this->pdo->prepare('SELECT COALESCE(MAX(version),0)+1 FROM btp_documents WHERE projet_id=? AND category=? AND label=?');
            $v->execute([$projectId,$category,$label]);$version=(int)$v->fetchColumn();
            $q=$this->pdo->prepare('INSERT INTO btp_documents(projet_id,category,label,version,original_name,storage_name,mime_type,file_size,sha256,created_by) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $q->execute([$projectId,$category,$label,$version,mb_substr(basename((string)($file['name']??'document')),0,255),$name,$mime,$size,$hash,$actor]);
            $this->pdo->commit();
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();@unlink($destination);throw $e;}
    }
    public function addEvent(int $id,array $data,?int $actor):void {
        $type=(string)($data['event_type']??'');
        if(!in_array($type,['soumission','demande_client','reponse','negociation','reunion','decision','note'],true))throw new InvalidArgumentException('Type d’événement invalide.');
        $subject=trim((string)($data['subject']??''));
        $details=trim((string)($data['details']??''));
        if($id<1||$subject===''||mb_strlen($subject)>180||mb_strlen($details)>10000)throw new InvalidArgumentException('Contenu invalide.');
        $q=$this->pdo->prepare('INSERT INTO btp_dossier_events(projet_id,event_type,subject,details,created_by) VALUES (?,?,?,?,?)');
        $q->execute([$id,$type,$subject,$details,$actor]);
    }
    public function addQuote(int $id,array $data,?int $actor):void {
        $label=trim((string)($data['label']??''));
        $amount=trim((string)($data['amount_ht']??''));
        if($id<1||$label===''||mb_strlen($label)>180||!preg_match('/^\\d{1,15}(?:\\.\\d{1,2})?$/',$amount))throw new InvalidArgumentException('Intitulé ou montant du devis invalide.');
        $this->pdo->beginTransaction();
        try {
            $lock=$this->pdo->prepare('SELECT id FROM btp_projets WHERE id=? FOR UPDATE');
            $lock->execute([$id]);
            if(!$lock->fetchColumn())throw new InvalidArgumentException('Projet introuvable.');
            $q=$this->pdo->prepare('SELECT COALESCE(MAX(revision),0)+1 FROM btp_dossier_quotes WHERE projet_id=?');
            $q->execute([$id]);$revision=(int)$q->fetchColumn();
            $insert=$this->pdo->prepare('INSERT INTO btp_dossier_quotes(projet_id,revision,label,amount_ht,created_by) VALUES (?,?,?,?,?)');
            $insert->execute([$id,$revision,$label,$amount,$actor]);
            $this->pdo->commit();
        } catch (\Throwable $e) {if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }
}
