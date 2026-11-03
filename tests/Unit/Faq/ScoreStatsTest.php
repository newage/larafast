<?php

namespace Tests\Unit\Faq;

use App\Faq\ScoreStats;
use PHPUnit\Framework\TestCase;

class ScoreStatsTest extends TestCase
{
    public function test_quantile(): void
    {
        $values = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0];

        $this->assertSame(0.1, ScoreStats::quantile($values, 0.0));
        $this->assertSame(0.6, ScoreStats::quantile($values, 0.5));
        $this->assertSame(1.0, ScoreStats::quantile($values, 1.0));
    }

    public function test_separable_groups_get_a_threshold_between_them(): void
    {
        $text = ScoreStats::recommend([0.9, 0.95, 0.92, 0.91], [0.5, 0.6, 0.7]);

        $this->assertStringContainsString('separable', $text);
        $this->assertStringContainsString('e.g. 0.80', $text);
    }

    public function test_overlapping_groups_are_reported(): void
    {
        $this->assertStringContainsString('overlap', ScoreStats::recommend([0.7, 0.9, 0.95], [0.8]));
    }
}
