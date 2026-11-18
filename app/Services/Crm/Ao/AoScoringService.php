<?php

declare(strict_types=1);
namespace App\Services\Crm\Ao;
class AoScoringService
{
    private const KEYWORDS = [
        'batiment' => 16, 'travaux' => 14, 'construction' => 16, 'rehabilitation' => 15,
        'genie civil' => 18, 'route' => 14, 'voirie' => 16, 'assainissement' => 17,
        'terrassement' => 16, 'ouvrage' => 13, 'amenagement' => 13, 'cloture' => 12,
        'infrastructure' => 14, 'drainage' => 16, 'vrd' => 18, 'maintenance batiment' => 18,
        'renovation' => 15, 'refection' => 15, 'gros oeuvre' => 16, 'second oeuvre' => 14,
        'bitumage' => 17, 'reprofilage' => 15, 'chaussee' => 15, 'pavage' => 14,
        'pont' => 18, 'dalot' => 17, 'caniveau' => 16, 'ecole' => 12,
        'centre de sante' => 13, 'adduction d eau' => 18, 'chateau d eau' => 18,
        'forage' => 16, 'reseau hydraulique' => 17, 'electrification' => 15,
        'eclairage public' => 15, 'installation electrique' => 14, 'remise en etat' => 14,
    ];

    public function score(array $opportunity, array $customKeywords = []): int
    {
        $title = $this->normalize($opportunity['titre'] ?? '');
        $body = $this->normalize(implode(' ', [
            $opportunity['resume'] ?? '', $opportunity['secteur'] ?? '',
            $opportunity['type_marche'] ?? '', $opportunity['lieu_execution'] ?? '',
        ]));
        $score = 0;
        $titleMatches = 0;
        $bodyMatches = 0;
        foreach (self::KEYWORDS as $keyword => $points) {
            if (str_contains($title, $keyword)) {
                $score += $points;
                $titleMatches++;
            } elseif (str_contains($body, $keyword)) {
                $score += max(5, (int) floor($points * 0.65));
                $bodyMatches++;
            }
        }
        // Un objet qui combine plusieurs marqueurs BTP forts est une opportunite
        // metier, meme si son libelle officiel reste tres court.
        if ($titleMatches >= 2) {
            $score += 30;
        } elseif (($titleMatches + $bodyMatches) >= 3) {
            $score += 20;
        }
        foreach ($customKeywords as $keyword) {
            $keyword = $this->normalize($keyword);
            if ($keyword !== '' && (str_contains($title, $keyword) || str_contains($body, $keyword))) {
                $score += 12;
            }
        }
        return min(100, $score);
    }

    public function analyzeIbtpCompatibility(array $opportunity): array
    {
        $title = $this->normalize($opportunity['titre'] ?? '');
        $body = $this->normalize(implode(' ', [
            $opportunity['resume'] ?? '', $opportunity['secteur'] ?? '',
            $opportunity['type_marche'] ?? '', $opportunity['lieu_execution'] ?? '',
        ]));
        $reasons = [];
        $score = 0;
        foreach (self::KEYWORDS as $keyword => $points) {
            if (str_contains($title, $keyword) || str_contains($body, $keyword)) {
                $reasons[$keyword] = ucfirst($keyword);
                $score += str_contains($title, $keyword) ? $points : max(5, (int) floor($points * .65));
            }
        }
        if (!empty($opportunity['date_limite']) && strtotime((string) $opportunity['date_limite']) >= strtotime(date('Y-m-d'))) {
            $reasons['deadline'] = 'Delai de depot encore ouvert';
            $score += 8;
        }
        return ['score' => min(100, $score), 'reasons' => array_values($reasons)];
    }

    public function splitKeywords(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[,;\r\n]+/', $value) ?: [])));
    }

    public function containsExcludedKeyword(array $opportunity, string $excluded): bool
    {
        $haystack = $this->normalize(implode(' ', $opportunity));
        foreach ($this->splitKeywords($excluded) as $keyword) {
            if (str_contains($haystack, $this->normalize($keyword))) {
                return true;
            }
        }
        return false;
    }

    private function normalize($value): string
    {
        $value = mb_strtolower((string) $value, 'UTF-8');
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return trim((string) preg_replace('/\s+/', ' ', $converted !== false ? $converted : $value));
    }
}
