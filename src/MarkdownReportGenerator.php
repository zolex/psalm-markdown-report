<?php declare(strict_types=1);

namespace Zolex\PsalmMarkdownReport;

use Psalm\Internal\Analyzer\IssueData;
use Psalm\Plugin\EventHandler\AfterAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterAnalysisEvent;

class MarkdownReportGenerator implements AfterAnalysisInterface
{
    private static string $reportPath = 'psalm-report.md';

    public static function setReportPath(string $reportPath): void
    {
        self::$reportPath = $reportPath;
    }

    public static function afterAnalysis(AfterAnalysisEvent $event): void
    {
        echo "Generating Markdown Report in " . self::$reportPath . PHP_EOL;

        $psalmIssues = $event->getIssues();
        $issues = ['error' => [], 'warning' => [], 'note' => [], 'none' => []];

        /** @var list<IssueData> $fileIssues */
        foreach ($psalmIssues as $fileIssues) {
            foreach ($fileIssues as $issue) {
                $issues[$issue->severity][$issue->file_name][] = $issue;
            }
        }

        foreach (['error', 'warning', 'note', 'none'] as $severity) {
            if (!count($issues[$severity])) {
                unset($issues[$severity]);
            }
        }

        self::writeMarkdown($issues);
    }

    private static function createDir(string $path): bool
    {
        if (is_dir($path)) {
            return true;
        }

        $previous = substr($path, 0, strrpos($path, '/', -2) + 1 );
        $return = self::createDir($previous);

        return $return && is_writable($previous) && mkdir($path);
    }

    private static function writeMarkdown(array $severities): void
    {
        if ($branch = getenv('MARKDOWN_LINK_BRANCH')) {
            $prefix = '../blob/' . $branch . '/';
        } else {
            $prefix = '';
        }

        $md = "## Psalm Report \n\n";
        if (isset($severities['error'])) {
            $md .= "**Status ❌**";
        } else if (isset($severities['warning'])) {
            $md .= "**Status ⚠️**";
        } else {
            $md .= "**Status ✅**";
        }
        foreach ($severities as $severity => $files) {
            $md .= "\n\n<details>\n\n";
            $md .= "<summary>" . $severity . "</summary>";
            foreach ($files as $fileName => $issues) {

                $md .= "\n\n".'**['. $fileName . '](' . $prefix . $fileName . ')**' . "\n\n";
                /** @var IssueData $issue */
                foreach ($issues as $issue) {
                    $md .= "- [". $issue->type ."](". $issue->link ."): ". $issue->message . "\\\n"
                        . "`". trim($issue->snippet) ."`\\\n"
                        ." <sup>at _[". $issue->file_name . "](". $prefix . $issue->file_name . "#L" . $issue->line_from . ") line " . $issue->line_from . "_</sup>\n";
                }
            }

            $md .= "</details>\n\n \n\n";
        }

        self::createDir(dirname(self::$reportPath));
        file_put_contents(self::$reportPath, $md);
    }
}
