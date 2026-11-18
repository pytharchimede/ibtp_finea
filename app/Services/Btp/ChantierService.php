<?php
declare(strict_types=1);
namespace App\Services\Btp;
use PDO;
use DomainException;
/** Règles transactionnelles BC -> chantier et contrôle de rattachement des dépenses. */
final class ChantierService
{
    public function __construct(private PDO $pdo) {}
    public function createFromBonCommande(int $bonCommandeId,string $code,string $nom): int
    {
        $code=trim($code); $nom=trim($nom);
        if($bonCommandeId<=0 || $code==='' || mb_strlen($code)>40 || $nom==='' || mb_strlen($nom)>180)
            throw new DomainException('Paramètres du chantier invalides.');
        $this->pdo->beginTransaction();
        try {
            $q=$this->pdo->prepare("SELECT bc.id,bc.projet_id,bc.devis_id,bc.justificatif,d.statut AS devis_statut,d.projet_id AS devis_projet FROM btp_bons_commande_client bc JOIN btp_devis d ON d.id=bc.devis_id WHERE bc.id=? FOR UPDATE");
            $q->execute([$bonCommandeId]); $bc=$q->fetch(PDO::FETCH_ASSOC);
            if(!$bc || (int)$bc['projet_id']!==(int)$bc['devis_projet'] || !in_array($bc['devis_statut'],['envoye','accepte'],true) || trim((string)$bc['justificatif'])==='')
                throw new DomainException('BC justificatif et devis transmis obligatoires.');
            $q=$this->pdo->prepare("INSERT INTO btp_chantiers(projet_id,bon_commande_id,code,nom) VALUES (?,?,?,?)");
            $q->execute([(int)$bc['projet_id'],$bonCommandeId,$code,$nom]);
            $id=(int)$this->pdo->lastInsertId(); $this->pdo->commit(); return $id;
        } catch(\Throwable $e) { $this->pdo->rollBack(); throw $e; }
    }
    public function addExpense(int $chantierId,int $debourseId,string $reference,string $montant,int $actorId): int
    {
        $reference=trim($reference);
        if($chantierId<=0 || $debourseId<=0 || $actorId<=0 || $reference==='' || mb_strlen($reference)>80 || !preg_match('/^\\d+(?:\\.\\d{1,2})?$/',$montant))
            throw new DomainException('Dépense invalide.');
        $this->pdo->beginTransaction();
        try {
            $q=$this->pdo->prepare('SELECT id FROM btp_debourse_lignes WHERE id=? AND chantier_id=? FOR UPDATE');
            $q->execute([$debourseId,$chantierId]);
            if(!$q->fetchColumn())throw new DomainException('Déboursé étranger au chantier.');
            $q=$this->pdo->prepare("INSERT INTO btp_depenses(chantier_id,debourse_ligne_id,reference,montant_ht,created_by) VALUES (?,?,?,?,?)");
            $q->execute([$chantierId,$debourseId,$reference,$montant,$actorId]);
            $id=(int)$this->pdo->lastInsertId();$this->pdo->commit();return $id;
        } catch(\Throwable $e) { $this->pdo->rollBack(); throw $e; }
    }
}
