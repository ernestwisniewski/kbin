<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\Contracts\ReportInterface;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

final class SubjectExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            new TwigTest(
                'entry', function (ReportInterface $report) {
                return $report instanceof Entry;
            }
            ),
            new TwigTest(
                'entry_comment', function (ReportInterface $report) {
                return $report instanceof EntryComment;
            }
            ),
            new TwigTest(
                'post', function (ReportInterface $report) {
                return $report instanceof Post;
            }
            ),
            new TwigTest(
                'post_comment', function (ReportInterface $report) {
                return $report instanceof PostComment;
            }
            ),
        ];
    }
}
