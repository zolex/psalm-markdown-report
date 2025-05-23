<?php declare(strict_types=1);

namespace Zolex\PsalmMarkdownReport;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

final class Plugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        // don't run markdown generation if another report is requested
        $options = getopt('', ['report:']);
        if (count($options)) {
            return;
        }

        if ($config !== null) {
            if (property_exists($config, 'reportPath')) {
                MarkdownReportGenerator::setReportPath((string)$config->reportPath);
            }
        }

        $registration->registerHooksFromClass(MarkdownReportGenerator::class);
    }
}
