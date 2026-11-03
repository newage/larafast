<?php

namespace App\Console\Commands;

use App\Faq\PineconeIndex;
use App\Faq\ScoreStats;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Measures top-hit scores on the seeded index to tune VECTOR_MIN_SCORE.
 *
 * In-topic queries are the questions from data/faq.json. Each question is itself an indexed chunk,
 * so its own chunk is dropped from the hits (leave-one-out); the rest of the same FAQ item must still
 * be found. Off-topic queries are fixed below.
 */
class FaqMeasureScore extends Command
{
    private const TOP_K = 10;

    private const OFF_TOPIC = [
        'off-en' => [
            'Write a song about autumn', 'What is the capital of France?', 'hello', 'How do I cook pasta?',
            'Write a Python function to sort a list', 'Who is the president of the USA?', "What's the weather today?",
            'Tell me a joke', 'What is the meaning of life?', 'hi there', 'good morning', 'Tell me a funny story',
            'What is the best movie of all time?', 'Recommend a good book', 'How tall is Mount Everest?',
            'Translate "thank you" to German', 'Who won the World Cup in 2018?', 'What is 17 times 23?',
        ],
        'off-pl' => [
            'napisz piosenkę o jesieni', 'Jaka jest stolica Francji?', 'cześć', 'Jak ugotować makaron?',
            'Jaka jest dziś pogoda?', 'Opowiedz dowcip', 'dzień dobry', 'Opowiedz zabawną historię',
            'Polecisz dobrą książkę?', 'Ile to jest 17 razy 23?', 'Kto wygrał mistrzostwa świata w 2018?',
            'Jak nauczyć się angielskiego w miesiąc?', 'Jaka jest najwyższa góra świata?',
        ],
        'user-data' => [
            'Ile kosztuje strona internetowa?', 'Ile trwa zrobienie strony?', 'Czy Laravel nie jest droższy?',
            'Czy sam będę mógł zmieniać teksty i zdjęcia?', 'Czy strona będzie działać na telefonie?', 'Na kogo będzie zarejestrowana domena?s',
            'Czy zajmie się Pan wszystkim — od projektu do uruchomienia strony?', 'Czy strona będzie moja?',
            'Czy może Pan zrobić chatbota na moją stronę?', 'Ile kosztuje wdrożenie AI?',
        ],
        'other-people' => [
            'Who is Elon Musk?', 'Tell me about Steve Jobs', 'Where does Anna Kowalska live?',
            'Розкажи про Тараса Шевченка', 'Хто такий Володимир Зеленський?', 'Gdzie mieszka Jan Kowalski?',
            'Kim jest Robert Lewandowski?', 'What is the phone number of my neighbour?',
        ],
    ];

    protected $signature = 'faq:measure-score {file? : path to faq.json (default data/faq.json)}';

    protected $description = 'Measure Pinecone top-hit scores to choose VECTOR_MIN_SCORE';

    public function handle(): int
    {
        foreach (['api_key' => 'PINECONE_API_KEY', 'index_name' => 'PINECONE_INDEX_NAME'] as $key => $env) {
            if (! config("services.pinecone.$key")) {
                $this->error("$env is not set");

                return self::FAILURE;
            }
        }
        $file = $this->argument('file') ?? base_path('data/faq.json');
        $items = json_decode(File::get($file), true, flags: JSON_THROW_ON_ERROR);
        $index = PineconeIndex::fromConfig();

        $inTopic = [];
        foreach ($items as $item) {
            // chunk 01 embeds the answer text, chunks 02.. one question each (same ids as faq:seed)
            foreach ($item['questions'] as $i => $question) {
                [$score, $parent] = $this->topHit($index, $question, sprintf('%s-%02d', $item['parent_id'], $i + 2));
                $inTopic[] = [$question, $score, $parent !== $item['parent_id']];
            }
        }

        $offTopic = [];
        foreach (self::OFF_TOPIC as $group => $questions) {
            foreach ($questions as $question) {
                $offTopic[] = [$group, $question, $this->topHit($index, $question)[0]];
            }
        }

        $in = array_column($inTopic, 1);
        $off = array_column($offTopic, 2);

        $this->line(sprintf(
            "index \"%s\" ns \"%s\", VECTOR_MIN_SCORE=%s\n",
            $index->indexName(), $index->namespace(), Setting::resolve('VECTOR_MIN_SCORE'),
        ));
        $this->line('in-topic (leave-one-out): '.ScoreStats::summary($in));
        $this->line(sprintf('  top hit from another FAQ item: %d/%d', count(array_filter(array_column($inTopic, 2))), count($inTopic)));
        $this->line('  lowest:');
        usort($inTopic, fn ($a, $b) => $a[1] <=> $b[1]);
        foreach (array_slice($inTopic, 0, 10) as [$question, $score, $wrong]) {
            $this->line(sprintf('    %.3f%s  %s', $score, $wrong ? ' (wrong item)' : '', $question));
        }

        $this->line("\noff-topic: ".ScoreStats::summary($off));
        $this->line('  highest:');
        usort($offTopic, fn ($a, $b) => $b[2] <=> $a[2]);
        foreach (array_slice($offTopic, 0, 10) as [$group, $question, $score]) {
            $this->line(sprintf('    %.3f  [%s] %s', $score, $group, $question));
        }

        $this->line("\nthreshold  in-topic answered  off-topic answered");
        for ($t = floor(min([...$in, ...$off]) * 20) / 20; $t <= 1.0001; $t += 0.02) {
            $this->line(sprintf(
                '%.2f       %4.0f%%              %4.0f%%',
                $t,
                100 * count(array_filter($in, fn ($s) => $s >= $t)) / count($in),
                100 * count(array_filter($off, fn ($s) => $s >= $t)) / count($off),
            ));
        }

        $this->line("\n".ScoreStats::recommend($in, $off));

        return self::SUCCESS;
    }

    /** @return array{0: float, 1: ?string} best score and its parent_id, ignoring the query's own chunk */
    private function topHit(PineconeIndex $index, string $text, ?string $excludeId = null): array
    {
        $hits = array_values(array_filter(
            $index->search($text, self::TOP_K + 1, ['parent_id']),
            fn (array $h) => $h['_id'] !== $excludeId,
        ));

        return $hits === [] ? [0.0, null] : [$hits[0]['_score'], $hits[0]['fields']['parent_id'] ?? null];
    }
}
