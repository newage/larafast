<?php

namespace App\Faq;

class FaqRetriever
{
    public const MAX_RECORDS = 3;

    // More than MAX_RECORDS: several chunks of one record would otherwise crowd out the others.
    public const TOP_K = 10;

    public function __construct(private readonly PineconeIndex $index) {}

    /**
     * Group chunks by parent_id and keep up to MAX_RECORDS records scoring >= $minScore.
     *
     * @return list<array{parent_id: string, answer: string, score: float}>
     */
    public function search(string $query, float $minScore): array
    {
        $best = [];
        // Hits come sorted by score, so the first chunk of a record is its best one.
        foreach ($this->index->search($query, self::TOP_K, ['parent_id', 'answer']) as $hit) {
            $parentId = $hit['fields']['parent_id'] ?? null;
            if ($parentId === null || isset($best[$parentId]) || $hit['_score'] < $minScore) {
                continue;
            }
            $best[$parentId] = [
                'parent_id' => $parentId,
                'answer' => $hit['fields']['answer'] ?? '',
                'score' => $hit['_score'],
            ];
        }
        usort($best, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_slice($best, 0, self::MAX_RECORDS);
    }
}
