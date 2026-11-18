<?php
declare(strict_types=1);
namespace App\Repositories\Btp;
use PDO;
/** Indicateurs HT : contractualisé, déboursé révisé, engagements et paiements. */
final class PilotageRepository
{
    public function __construct(private PDO $pdo) {}
    public function overview(): array
    {
        $sql="SELECT p.id,p.reference,p.intitule,p.client_nom,
            COALESCE((SELECT SUM(bc.montant_ht) FROM btp_bons_commande_client bc WHERE bc.projet_id=p.id),0) AS ca_contractuel_ht,
            COALESCE((SELECT SUM(dl.montant_revise_ht) FROM btp_debourse_lignes dl JOIN btp_chantiers ch ON ch.id=dl.chantier_id WHERE ch.projet_id=p.id),0) AS debourse_revise_ht,
            COALESCE((SELECT SUM(dep.montant_ht) FROM btp_depenses dep JOIN btp_chantiers ch ON ch.id=dep.chantier_id WHERE ch.projet_id=p.id AND dep.statut IN ('engage','constate','paye')),0) AS couts_engages_ht,
            COALESCE((SELECT SUM(dep.montant_ht) FROM btp_depenses dep JOIN btp_chantiers ch ON ch.id=dep.chantier_id WHERE ch.projet_id=p.id AND dep.statut='paye'),0) AS paiements_ht
            FROM btp_projets p ORDER BY p.id DESC LIMIT 100";
        $rows=$this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as &$row){
            $row['marge_previsionnelle_ht']=round((float)$row['ca_contractuel_ht']-(float)$row['debourse_revise_ht'],2);
        }
        return $rows;
    }
}
