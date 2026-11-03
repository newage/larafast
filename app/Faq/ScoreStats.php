<?php

namespace App\Faq;

/** Pure statistics for faq:measure-score. */
final class ScoreStats
{
    /** @param  list<float>  $sorted  ascending */
    public static function quantile(array $sorted, float $q): float
    {
        return $sorted[min(count($sorted) - 1, (int) ($q * count($sorted)))];
    }

    /** @param  list<float>  $scores */
    public static function summary(array $scores): string
    {
        sort($scores);

        return sprintf(
            'n=%d min %.3f p10 %.3f median %.3f max %.3f',
            count($scores), $scores[0], self::quantile($scores, 0.1), self::quantile($scores, 0.5), end($scores),
        );
    }

    /**
     * @param  list<float>  $inScores
     * @param  list<float>  $offScores
     */
    public static function recommend(array $inScores, array $offScores): string
    {
        $offMax = max($offScores);
        sort($inScores);
        $inP5 = self::quantile($inScores, 0.05);

        if ($inP5 > $offMax) {
            return sprintf(
                'Groups are separable: pick VECTOR_MIN_SCORE in (%.3f, %.3f), e.g. %.2f.',
                $offMax, $inP5, ($offMax + $inP5) / 2,
            );
        }

        return sprintf(
            'Groups overlap (off-topic max %.3f >= in-topic p5 %.3f): pick a trade-off from the table.',
            $offMax, $inP5,
        );
    }
}
