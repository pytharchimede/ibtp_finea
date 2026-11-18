<?php

declare(strict_types=1);
namespace App\Services\Crm\Ao;
use DOMDocument;
use DOMXPath;
use RuntimeException;

class SigomapAoAdapter implements AoSourceAdapterInterface
{
    private string $publicUrl;

    public function __construct(string $publicUrl = 'https://marchespublics.ci/appel_offre')
    {
        $this->publicUrl = $publicUrl;
    }

    public function getSourceName(): string
    {
        return 'DGMP public';
    }

    public function fetchPublicOpportunities(): array
    {
        $html = $this->fetchHtml($this->publicUrl);
        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        if (!$loaded) {
            throw new RuntimeException('Page DGMP publique illisible.');
        }

        $xpath = new DOMXPath($document);
        $tables = $xpath->query('//table');
        foreach ($tables ?: [] as $table) {
            $headers = [];
            foreach ($xpath->query('.//thead//th|.//tr[1]/th', $table) ?: [] as $header) {
                $headers[] = $this->clean($header->textContent);
            }
            $headerText = $this->clean(implode(' ', $headers));
            if (stripos($headerText, 'Num') === false || stripos($headerText, 'Objet') === false) {
                continue;
            }

            $items = [];
            foreach ($xpath->query('.//tbody/tr', $table) ?: [] as $row) {
                $cells = [];
                $rawDetails = [];
                $sourceLinks = [];
                foreach ($xpath->query('./td', $row) ?: [] as $index => $cell) {
                    $value = $this->clean($cell->textContent);
                    $cells[] = $value;
                    $label = $headers[$index] ?? ('Colonne ' . ($index + 1));
                    $rawDetails[$label] = $value;
                    foreach ($xpath->query('.//a[@href]', $cell) ?: [] as $link) {
                        $href = $this->absoluteUrl($link->getAttribute('href'));
                        if ($href !== '') {
                            $sourceLinks[] = [
                                'label' => $this->clean($link->textContent) ?: basename((string) parse_url($href, PHP_URL_PATH)),
                                'url' => $href,
                            ];
                        }
                    }
                }
                if (count($cells) < 4) {
                    continue;
                }
                $items[] = [
                    'numero_ao' => $cells[0] ?? '',
                    'reference' => $cells[0] ?? '',
                    'type_marche' => $cells[1] ?? '',
                    'titre' => $cells[2] ?? '',
                    'autorite_contractante' => $cells[3] ?? '',
                    'date_publication' => $cells[4] ?? '',
                    'date_limite' => $cells[5] ?? '',
                    'url_source' => $this->publicUrl,
                    'details_source' => $rawDetails,
                    'documents_source' => $sourceLinks,
                ];
            }
            return $items;
        }

        throw new RuntimeException('Table publique des appels d offres introuvable.');
    }

    public function normalizeOpportunity(array $raw): array
    {
        return [
            'source' => $this->getSourceName(),
            'numero_ao' => $raw['numero_ao'] ?? $raw['reference'] ?? '',
            'reference' => $raw['reference'] ?? '',
            'titre' => $raw['titre'] ?? '',
            'autorite_contractante' => $raw['autorite_contractante'] ?? '',
            'type_marche' => $raw['type_marche'] ?? '',
            'secteur' => $raw['type_marche'] ?? '',
            'date_publication' => $raw['date_publication'] ?? null,
            'date_limite' => $raw['date_limite'] ?? null,
            'lieu_execution' => 'Cote d Ivoire',
            'resume' => $raw['titre'] ?? '',
            'url_source' => $raw['url_source'] ?? $this->publicUrl,
            'details_source' => $raw['details_source'] ?? [],
            'documents_source' => $raw['documents_source'] ?? [],
        ];
    }

    private function absoluteUrl(string $href): string
    {
        $href = trim($href);
        if ($href === '' || str_starts_with($href, '#') || str_starts_with(strtolower($href), 'javascript:')) {
            return '';
        }
        if (preg_match('~^https?://~i', $href)) {
            return $href;
        }
        $parts = parse_url($this->publicUrl);
        $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? 'marchespublics.ci');
        return $origin . '/' . ltrim($href, '/');
    }

    private function fetchHtml(string $url): string
    {
        $lastError = 'Source indisponible.';
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            if (function_exists('curl_init')) {
                $curl = curl_init($url);
                curl_setopt_array($curl, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CONNECTTIMEOUT => 8,
                    CURLOPT_TIMEOUT => 25,
                    CURLOPT_USERAGENT => 'FINEA-AO-Watch/1.0 (+public procurement monitoring)',
                    CURLOPT_HTTPHEADER => ['Accept: text/html,application/xhtml+xml'],
                    CURLOPT_ENCODING => '',
                ]);
                $body = curl_exec($curl);
                $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $lastError = (string) curl_error($curl);
                curl_close($curl);
                if (is_string($body) && strlen($body) > 1000 && $status >= 200 && $status < 300) {
                    return $body;
                }
            } else {
                $context = stream_context_create(['http' => [
                    'timeout' => 25,
                    'header' => "User-Agent: FINEA-AO-Watch/1.0\r\nAccept: text/html\r\n",
                ]]);
                $body = @file_get_contents($url, false, $context);
                if (is_string($body) && strlen($body) > 1000) {
                    return $body;
                }
            }
            if ($attempt < 2) {
                sleep(2);
            }
        }
        throw new RuntimeException('Echec source DGMP publique: ' . $lastError);
    }

    private function clean(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
