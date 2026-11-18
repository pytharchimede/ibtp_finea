<?php

declare(strict_types=1);
namespace App\Services\Crm\Ao;
class AoOpportunity
{
    public static function normalizeText($value): string
    {
        $value = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }

    public static function normalizeReference($value): string
    {
        $value = self::normalizeText($value);
        $value = function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
        return (string) preg_replace('/[^\pL\pN]+/u', '', $value);
    }

    public static function canonicalUrl($value): string
    {
        $url = trim((string) $value);
        if ($url === '') {
            return '';
        }
        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['host'])) {
            return rtrim($url, '/');
        }
        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $host = strtolower((string) $parts['host']);
        $path = preg_replace('~/+~', '/', (string) ($parts['path'] ?? '/'));
        // Les routes SPA identifient les offres Contenu local.
        $fragment = (string) ($parts['fragment'] ?? '');
        $route = str_starts_with($fragment, '/') ? '#' . rtrim($fragment, '/') : '';
        return $scheme . '://' . $host . rtrim($path ?: '/', '/') . $route;
    }

    public static function normalizeDate($value): ?string
    {
        $value = self::normalizeText($value);
        if ($value === '' || str_contains($value, '0001')) {
            return null;
        }
        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'd.m.Y'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);
            if ($date instanceof \DateTime) {
                return $date->format('Y-m-d');
            }
        }
        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    public static function prepare(array $data): array
    {
        $prepared = [
            'source' => self::normalizeText($data['source'] ?? 'DGMP'),
            'numero_ao' => self::normalizeText($data['numero_ao'] ?? $data['reference'] ?? ''),
            'reference' => self::normalizeText($data['reference'] ?? ''),
            'titre' => self::normalizeText($data['titre'] ?? ''),
            'autorite_contractante' => self::normalizeText($data['autorite_contractante'] ?? ''),
            'type_marche' => self::normalizeText($data['type_marche'] ?? ''),
            'secteur' => self::normalizeText($data['secteur'] ?? ''),
            'date_publication' => self::normalizeDate($data['date_publication'] ?? null),
            'date_limite' => self::normalizeDate($data['date_limite'] ?? null),
            'lieu_execution' => self::normalizeText($data['lieu_execution'] ?? ''),
            'resume' => self::normalizeText($data['resume'] ?? ''),
            'url_source' => trim((string) ($data['url_source'] ?? '')),
            'score_pertinence' => max(0, min(100, (int) ($data['score_pertinence'] ?? 0))),
            'statut_interne' => self::normalizeText($data['statut_interne'] ?? 'nouveau') ?: 'nouveau',
            'details_source_json' => self::normalizeJson($data['details_source'] ?? $data['details_source_json'] ?? []),
            'documents_source_json' => self::normalizeJson($data['documents_source'] ?? $data['documents_source_json'] ?? []),
        ];
        $prepared['reference_normalized'] = self::normalizeReference($prepared['reference']);
        $prepared['url_canonical'] = self::canonicalUrl($prepared['url_source']);
        $signatureParts = [
            self::normalizeReference($prepared['titre']),
            self::normalizeReference($prepared['autorite_contractante']),
            $prepared['date_limite'] ?? '',
            self::normalizeReference($prepared['type_marche']),
        ];
        $prepared['signature_hash'] = hash('sha256', implode('|', $signatureParts));
        $identity = $prepared['reference_normalized'] !== ''
            ? 'ref|' . $prepared['reference_normalized']
            : 'signature|' . $prepared['signature_hash'];
        $prepared['hash_unique'] = hash('sha256', $identity);
        return $prepared;
    }

    private static function normalizeJson($value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            } elseif (trim($value) !== '') {
                $value = ['Contenu source' => self::normalizeText($value)];
            } else {
                $value = [];
            }
        }
        if (!is_array($value)) {
            $value = [];
        }
        return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

