<?php

namespace App\Console\Commands;

use App\Faq\FaqRecords;
use App\Faq\PineconeIndex;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use JsonException;

/**
 * Seeds Pinecone (integrated embedding) from data/faq.json. Only new or changed (content_hash)
 * records are sent; nothing is deleted.
 */
class FaqSeed extends Command
{
    private const BATCH_SIZE = 96; // upsert limit for integrated-embedding indexes

    protected $signature = 'faq:seed {file? : path to faq.json (default data/faq.json)} {--dry-run : only count, write nothing}';

    protected $description = 'Upsert new and changed FAQ chunks into the Pinecone index';

    public function handle(): int
    {
        $file = $this->argument('file') ?? base_path('data/faq.json');
        $textField = config('services.pinecone.text_field') ?: 'text';
        $namespace = config('services.pinecone.namespace') ?: '__default__';

        try {
            $records = FaqRecords::toRecords(json_decode(File::get($file), true, flags: JSON_THROW_ON_ERROR), $textField);
        } catch (InvalidArgumentException|JsonException|FileNotFoundException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        $this->line(sprintf('%d records from %s -> namespace "%s", embedded field "%s"', count($records), $file, $namespace, $textField));

        $dryRun = (bool) $this->option('dry-run');
        $hasCredentials = config('services.pinecone.api_key') && config('services.pinecone.index_name');
        if ($dryRun && ! $hasCredentials) {
            return self::SUCCESS;
        }
        foreach (['api_key' => 'PINECONE_API_KEY', 'index_name' => 'PINECONE_INDEX_NAME'] as $key => $env) {
            if (! config("services.pinecone.$key")) {
                $this->error("$env is not set");

                return self::FAILURE;
            }
        }

        try {
            return $this->sync($records, $dryRun);
        } catch (ConnectionException|RequestException $e) {
            $this->error('Pinecone request failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @param  list<array<string, mixed>>  $records
     */
    private function sync(array $records, bool $dryRun): int
    {
        $index = PineconeIndex::fromConfig();
        $existing = [];
        foreach (array_chunk(array_column($records, '_id'), FaqRecords::FETCH_BATCH_SIZE) as $ids) {
            $existing += $index->fetch($ids);
        }

        $diff = FaqRecords::diff($records, $existing);
        $this->line("{$diff['new']} new, {$diff['changed']} changed, {$diff['unchanged']} unchanged");
        if ($dryRun) {
            return self::SUCCESS;
        }

        $sent = 0;
        foreach (array_chunk($diff['toSend'], self::BATCH_SIZE) as $batch) {
            $index->upsert($batch);
            $sent += count($batch);
            $this->line("upserted $sent/".count($diff['toSend']));
        }

        return self::SUCCESS;
    }
}
