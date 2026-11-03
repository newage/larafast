<?php

namespace App\Faq;

use InvalidArgumentException;

/** Pure helpers for faq:seed: record building, content hashing, diffing against the index. */
final class FaqRecords
{
    public const FETCH_BATCH_SIZE = 100;

    public static function contentHash(string $embedded, string $answer, ?string $type): string
    {
        // Changing this formula makes every stored record count as changed.
        $payload = json_encode([$embedded, $answer, $type], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return hash('sha256', $payload);
    }

    /**
     * Chunk 01 embeds the item text, chunks 02.. embed one question each; `answer` is the item text in all of them.
     *
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    public static function toRecords(array $items, string $textField = 'text'): array
    {
        $records = [];
        $ids = [];
        foreach ($items as $item) {
            $parentId = $item['parent_id'] ?? null;
            $text = $item['text'] ?? null;
            $questions = $item['questions'] ?? null;
            if (! $parentId || ! $text || ! is_array($questions)) {
                throw new InvalidArgumentException('Invalid FAQ item: '.mb_substr(json_encode($item, JSON_UNESCAPED_UNICODE), 0, 120));
            }
            $type = $item['type'] ?? null;

            foreach ([$text, ...$questions] as $n => $embedded) {
                $id = sprintf('%s-%02d', $parentId, $n + 1);
                if (isset($ids[$id])) {
                    throw new InvalidArgumentException('Duplicate record ids');
                }
                $ids[$id] = true;

                $record = [
                    '_id' => $id,
                    'parent_id' => $parentId,
                    $textField => $embedded,
                    'answer' => $text,
                    'content_hash' => self::contentHash($embedded, $text, $type),
                ];
                if ($type !== null) { // Pinecone metadata does not accept null
                    $record['type'] = $type;
                }
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * Absent id = new; different content_hash = changed.
     *
     * @param  list<array<string, mixed>>  $records
     * @param  array<string, array<string, mixed>>  $existing  id => metadata
     * @return array{toSend: list<array<string, mixed>>, new: int, changed: int, unchanged: int}
     */
    public static function diff(array $records, array $existing): array
    {
        $toSend = [];
        $new = $changed = 0;
        foreach ($records as $record) {
            $stored = $existing[$record['_id']] ?? null;
            if ($stored === null) {
                $new++;
                $toSend[] = $record;
            } elseif (($stored['content_hash'] ?? null) !== $record['content_hash']) {
                $changed++;
                $toSend[] = $record;
            }
        }

        return ['toSend' => $toSend, 'new' => $new, 'changed' => $changed, 'unchanged' => count($records) - count($toSend)];
    }
}
