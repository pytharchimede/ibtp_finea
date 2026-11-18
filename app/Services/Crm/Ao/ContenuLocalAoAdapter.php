<?php

declare(strict_types=1);
namespace App\Services\Crm\Ao;
use Closure;
use RuntimeException;
use Throwable;

final class ContenuLocalAoAdapter implements AoSourceAdapterInterface
{
    public const PORTAL = 'https://intermediation.contenulocal.dgh.energie.gouv.ci';
    private const API = 'https://api-intermediation.contenulocal.dgh.energie.gouv.ci/users-ms/api/v1';
    private const TOKEN_URL = 'https://keycloak.contenulocal.dgh.energie.gouv.ci/realms/ic-live-realm/protocol/openid-connect/token';
    private array $config;
    private string $token = '';
    private ?Closure $transport;

    public static function configPath(): string
    {
        return (string) (getenv('FINEA_CONTENU_LOCAL_CONFIG') ?: dirname(__DIR__, 4) . '/.finea/contenu-local.php');
    }

    public static function isConfigured(): bool
    {
        return is_file(self::configPath()) || (getenv('FINEA_CONTENU_LOCAL_USERNAME') && getenv('FINEA_CONTENU_LOCAL_PASSWORD'));
    }

    public function __construct(?array $config = null, ?callable $transport = null)
    {
        if ($config === null) {
            $config = is_file(self::configPath()) ? require self::configPath() : [];
            $config['username'] = $config['username'] ?? getenv('FINEA_CONTENU_LOCAL_USERNAME');
            $config['password'] = $config['password'] ?? getenv('FINEA_CONTENU_LOCAL_PASSWORD');
        }
        $this->config = $config;
        $this->transport = $transport !== null ? Closure::fromCallable($transport) : null;
    }

    public function getSourceName(): string { return 'Contenu local DGH'; }

    public function fetchPublicOpportunities(): array
    {
        if (empty($this->config['username']) || empty($this->config['password'])) {
            throw new RuntimeException('Contenu local : configurez le compte dans le fichier privé du serveur.');
        }
        $auth = $this->request(self::TOKEN_URL, http_build_query([
            'client_id' => 'hydro-client', 'grant_type' => 'password', 'scope' => 'openid',
            'username' => $this->config['username'], 'password' => $this->config['password'],
        ]));
        $this->token = (string) ($auth['access_token'] ?? '');
        if ($this->token === '') throw new RuntimeException('Contenu local : authentification refusée.');
        $items = [];
        $seen = [];
        $totalPages = 1;
        try {
            for ($page = 0; $page < $totalPages; $page++) {
                $data = $this->request(self::API . '/tender-calls/search?' . http_build_query([
                    'types' => 'OPEN_AO,AMI_AO,AMI,AO', 'statuses' => 'PUBLISHED,LAUNCHED', 'limit' => 100, 'page' => $page,
                ]));
                if (!isset($data['content']) || !is_array($data['content']) || !isset($data['totalPages'])) {
                    throw new RuntimeException('Contenu local : réponse de pagination incompatible.');
                }
                $totalPages = (int) $data['totalPages'];
                if ($totalPages < 0 || $totalPages > 500) throw new RuntimeException('Contenu local : pagination anormale, synchronisation arrêtée.');
                foreach ($data['content'] as $raw) {
                    if (!is_array($raw) || empty($raw['id'])) throw new RuntimeException('Contenu local : offre sans identifiant.');
                    if (!isset($seen[(string) $raw['id']])) {
                        $items[] = $raw;
                        $seen[(string) $raw['id']] = true;
                    }
                }
            }
        } finally {
            $this->token = '';
        }
        return $items;
    }

    public function normalizeOpportunity(array $raw): array
    {
        $project = $raw['marketProject'] ?? $raw['interestExpressionCall']['marketProject'] ?? [];
        $org = $raw['org'] ?? $project['org'] ?? [];
        $title = $raw['title'] ?? $project['title'] ?? '';
        $reference = trim((string) ($raw['reference'] ?? ''));
        $id = (int) ($raw['id'] ?? 0);
        return [
            'source' => $this->getSourceName(), 'numero_ao' => $reference,
            'reference' => $reference !== '' ? $reference : 'DGH-' . $id,
            'titre' => $title, 'autorite_contractante' => $org['name'] ?? $org['legalName'] ?? '',
            'type_marche' => $raw['type'] ?? '', 'secteur' => $raw['activity']['name'] ?? $project['activity']['name'] ?? 'Hydrocarbures',
            'date_publication' => $this->sourceDate($raw['publishedAt'] ?? $project['publishedAt'] ?? $raw['publicationDate'] ?? null),
            // La fin du lancement est la limite de réponse, pas la clôture de la procédure.
            'date_limite' => $this->sourceDate($raw['launchEndAt'] ?? null),
            'lieu_execution' => 'Côte d’Ivoire',
            'resume' => AoOpportunity::normalizeText(implode(' ', array_filter([$raw['subject'] ?? $project['subject'] ?? '', $raw['description'] ?? $project['description'] ?? '']))),
            'url_source' => self::PORTAL . '/#/publish/details/' . $id,
            'details_source' => ['Identifiant DGH' => $id, 'Statut source' => $raw['status'] ?? '', 'Type source' => $raw['type'] ?? ''],
            'documents_source' => [],
        ];
    }

    private function sourceDate(mixed $value): ?string
    {
        if (!is_string($value) || preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:T| |$)/', $value, $parts) !== 1) return null;
        if (!checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) return null;
        return $parts[1] . '-' . $parts[2] . '-' . $parts[3];
    }

    private function request(string $url, ?string $form = null): array
    {
        if ($this->transport !== null) return ($this->transport)($url, $form);
        if (!function_exists('curl_init')) throw new RuntimeException('Contenu local : extension PHP cURL requise.');
        $curl = curl_init($url);
        $headers = ['Accept: application/json'];
        if ($form !== null) $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        if ($this->token !== '') $headers[] = 'Authorization: Bearer ' . $this->token;
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 40, CURLOPT_FOLLOWLOCATION => false, CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_HTTPHEADER => $headers, CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'FINEA-AO-Watch/2.0']);
        if ($form !== null) curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $form]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $failed = $body === false;
        curl_close($curl);
        // Ne jamais journaliser la réponse d’authentification, les identifiants ou les jetons.
        if ($failed || $status < 200 || $status >= 300) throw new RuntimeException('Contenu local : requête indisponible (HTTP ' . $status . ').');
        try { $data = json_decode((string) $body, true, 512, JSON_THROW_ON_ERROR); }
        catch (Throwable $e) { throw new RuntimeException('Contenu local : réponse JSON illisible.'); }
        if (!is_array($data)) throw new RuntimeException('Contenu local : réponse JSON incompatible.');
        return $data;
    }
}
