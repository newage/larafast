<?php

namespace App\Faq;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Plain REST client for a Pinecone index with integrated embedding.
 * Control plane resolves the index host once; data plane is search / fetch / upsert of records.
 */
class PineconeIndex
{
    private const CONTROL_URL = 'https://api.pinecone.io';

    private const API_VERSION = '2026-07';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $indexName,
        private readonly string $namespace,
        private readonly ?string $host = null,
        private readonly string $textField = 'text',
    ) {}

    public static function fromConfig(): self
    {
        $config = config('services.pinecone');

        return new self(
            (string) $config['api_key'],
            (string) $config['index_name'],
            (string) ($config['namespace'] ?: '__default__'),
            $config['host'] ?: null,
            (string) ($config['text_field'] ?: 'text'),
        );
    }

    public function namespace(): string
    {
        return $this->namespace;
    }

    public function indexName(): string
    {
        return $this->indexName;
    }

    /**
     * @param  list<string>  $fields
     * @return list<array{_id: string, _score: float, fields: array<string, mixed>}> sorted by score, best first
     */
    public function search(string $text, int $topK, array $fields): array
    {
        $matches = $this->data()
            ->post($this->url("/namespaces/{$this->namespace}/documents/search"), [
                'score_by' => [['type' => 'embed', 'field' => $this->textField, 'query' => $text]],
                'top_k' => $topK,
                'include_fields' => $fields,
            ])
            ->throw()
            ->json('matches', []);

        return array_map(fn (array $m) => [
            '_id' => $m['_id'],
            '_score' => (float) ($m['_score'] ?? 0),
            'fields' => array_diff_key($m, ['_id' => 1, '_score' => 1]),
        ], $matches);
    }

    /**
     * @param  list<string>  $ids
     * @return array<string, array<string, mixed>> id => fields of the documents that exist
     */
    public function fetch(array $ids): array
    {
        return $this->data()
            ->post($this->url("/namespaces/{$this->namespace}/documents/fetch"), ['ids' => $ids, 'include_fields' => ['*']])
            ->throw()
            ->json('documents', []);
    }

    /** @param  list<array<string, mixed>>  $records */
    public function upsert(array $records): void
    {
        $this->data()
            ->post($this->url("/namespaces/{$this->namespace}/documents/upsert"), ['documents' => $records])
            ->throw();
    }

    private function data(): PendingRequest
    {
        return Http::withHeaders(['Api-Key' => $this->apiKey, 'X-Pinecone-Api-Version' => self::API_VERSION])
            ->acceptJson()
            ->timeout(30);
    }

    private function url(string $path): string
    {
        return 'https://'.$this->host().$path;
    }

    private function host(): string
    {
        if ($this->host) {
            return preg_replace('#^https?://#', '', $this->host);
        }

        return Cache::remember('pinecone.host.'.$this->indexName, now()->addDay(), function () {
            return Http::withHeaders(['Api-Key' => $this->apiKey, 'X-Pinecone-Api-Version' => self::API_VERSION])
                ->acceptJson()
                ->get(self::CONTROL_URL.'/indexes/'.rawurlencode($this->indexName))
                ->throw()
                ->json('host');
        });
    }
}
