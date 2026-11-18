<?php
declare(strict_types=1);
namespace App\Repositories\Crm;
use PDO;

final class CrmAnalyticsRepository
{
    public function __construct(private PDO $pdo){}

    public function overview():array
    {
        $counts=$this->pdo->query("SELECT COUNT(*) AS total, SUM(type='client') AS clients, SUM(type='prospect') AS prospects FROM crm_clients")->fetch(PDO::FETCH_ASSOC);
        $pipeline=$this->pdo->query("SELECT COUNT(*) AS total, COALESCE(SUM(CASE WHEN currency='XOF' THEN estimated_amount ELSE 0 END),0) AS amount, COALESCE(SUM(currency<>'XOF' AND estimated_amount IS NOT NULL),0) AS other_currencies FROM crm_opportunities")->fetch(PDO::FETCH_ASSOC);
        $actions=$this->pdo->query("SELECT COUNT(*) AS total FROM crm_interactions WHERE next_action_date IS NOT NULL AND next_action_date <= CURRENT_DATE()")->fetch(PDO::FETCH_ASSOC);
        $stages=$this->pdo->query("SELECT stage, COUNT(*) AS total, COALESCE(SUM(CASE WHEN currency='XOF' THEN estimated_amount ELSE 0 END),0) AS amount FROM crm_opportunities GROUP BY stage ORDER BY total DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
        $recent=$this->pdo->query("SELECT c.id,c.name,c.type,c.status,c.created_at FROM crm_clients c ORDER BY c.id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
        $upcoming=$this->pdo->query("SELECT i.id,i.subject,i.next_action_date,c.name AS client_name FROM crm_interactions i JOIN crm_clients c ON c.id=i.client_id WHERE i.next_action_date IS NOT NULL AND i.next_action_date <= DATE_ADD(CURRENT_DATE(), INTERVAL 14 DAY) ORDER BY i.next_action_date ASC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
        $opportunities=$this->pdo->query("SELECT o.id,o.title,o.stage,o.estimated_amount,o.currency,o.deadline,c.name AS client_name FROM crm_opportunities o JOIN crm_clients c ON c.id=o.client_id ORDER BY o.id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
        $ao=$this->pdo->query("SELECT COUNT(*) AS total,COALESCE(SUM(date_limite>=CURDATE()),0) AS active,COALESCE(SUM(date_limite>=CURDATE() AND score_pertinence>=60),0) AS relevant,COALESCE(SUM(date_limite BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 14 DAY)),0) AS soon,COALESCE(SUM(crm_opportunity_id IS NOT NULL),0) AS converted FROM crm_ao_notices")->fetch(PDO::FETCH_ASSOC);
        $settings=$this->pdo->query('SELECT min_score FROM crm_ao_scoring_settings WHERE id=1')->fetch(PDO::FETCH_ASSOC)?:['min_score'=>60];
        $threshold=(int)$settings['min_score'];
        $q=$this->pdo->prepare('SELECT COUNT(*) FROM crm_ao_notices WHERE date_limite>=CURDATE() AND score_pertinence>=?');
        $q->execute([$threshold]);$ao['relevant']=(int)$q->fetchColumn();
        $ao['threshold']=$threshold;
        $aoUpcoming=$this->pdo->query("SELECT id,titre,date_limite,score_pertinence,autorite_contractante FROM crm_ao_notices WHERE date_limite BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 21 DAY) ORDER BY date_limite ASC,score_pertinence DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
        return compact('counts','pipeline','actions','stages','recent','upcoming','opportunities','ao','aoUpcoming');
    }
}
