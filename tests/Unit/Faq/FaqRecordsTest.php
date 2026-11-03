<?php

namespace Tests\Unit\Faq;

use App\Faq\FaqRecords;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FaqRecordsTest extends TestCase
{
    private function item(array $override = []): array
    {
        return $override + ['parent_id' => 'koszt_strony', 'type' => 'web', 'text' => 'Od 2000 zł.', 'questions' => ['Ile?', 'Cena?', 'Koszt?']];
    }

    public function test_item_with_three_questions_gives_four_chunks(): void
    {
        $records = FaqRecords::toRecords([$this->item()]);

        $this->assertSame(['koszt_strony-01', 'koszt_strony-02', 'koszt_strony-03', 'koszt_strony-04'], array_column($records, '_id'));
        $this->assertSame(['Od 2000 zł.', 'Ile?', 'Cena?', 'Koszt?'], array_column($records, 'text'));
        $this->assertSame(['Od 2000 zł.'], array_unique(array_column($records, 'answer')));
        $this->assertArrayNotHasKey('lang', $records[0]);
        $this->assertArrayNotHasKey('project', $records[0]);
    }

    public function test_custom_text_field_and_missing_type(): void
    {
        $record = FaqRecords::toRecords([$this->item(['type' => null])], 'chunk_text')[0];

        $this->assertSame('Od 2000 zł.', $record['chunk_text']);
        $this->assertArrayNotHasKey('type', $record);
    }

    public function test_invalid_item_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        FaqRecords::toRecords([$this->item(), ['parent_id' => 'x', 'text' => 't']]);
    }

    public function test_duplicate_ids_are_rejected(): void
    {
        $this->expectExceptionMessage('Duplicate record ids');

        FaqRecords::toRecords([$this->item(), $this->item()]);
    }

    public function test_content_hash_is_stable_and_depends_on_every_part(): void
    {
        $hash = FaqRecords::contentHash('a', 'b', 'web');

        $this->assertSame($hash, FaqRecords::contentHash('a', 'b', 'web'));
        $this->assertSame(hash('sha256', '["a","b","web"]'), $hash);
        $this->assertNotSame($hash, FaqRecords::contentHash('x', 'b', 'web'));
        $this->assertNotSame($hash, FaqRecords::contentHash('a', 'x', 'web'));
        $this->assertNotSame($hash, FaqRecords::contentHash('a', 'b', null));
    }

    public function test_diff_sends_only_new_and_changed(): void
    {
        $records = FaqRecords::toRecords([$this->item()]);
        $existing = [
            'koszt_strony-01' => ['content_hash' => $records[0]['content_hash']],
            'koszt_strony-02' => ['content_hash' => 'old'],
            'koszt_strony-03' => ['content_hash' => $records[2]['content_hash']],
        ];

        $diff = FaqRecords::diff($records, $existing);

        $this->assertSame(['koszt_strony-02', 'koszt_strony-04'], array_column($diff['toSend'], '_id'));
        $this->assertSame([1, 1, 2], [$diff['new'], $diff['changed'], $diff['unchanged']]);
    }

    public function test_diff_of_an_unchanged_index_sends_nothing(): void
    {
        $records = FaqRecords::toRecords([$this->item()]);
        $existing = array_column($records, null, '_id');

        $this->assertSame([], FaqRecords::diff($records, $existing)['toSend']);
    }
}
