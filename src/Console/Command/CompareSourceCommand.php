<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker\Console\Command;

use Exception;
use Magento\SemanticVersionChecker\DbSchemaReporter;
use Magento\SemanticVersionChecker\FileChangeDetector;
use Magento\SemanticVersionChecker\ReportBuilder;
use Magento\SemanticVersionChecker\Reporter\HtmlDbSchemaReporter;
use Magento\SemanticVersionChecker\Reporter\HtmlPackageLevelChangesRenderer;
use Magento\SemanticVersionChecker\ReportTypes;
use Magento\SemanticVersionChecker\SemanticVersionChecker;
use PHPSemVerChecker\SemanticVersioning\Level;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class CompareSourceCommand extends Command
{
    public const REPORT_FORMAT_HTML = 'html';
    public const REPORT_FORMAT_TEXT = 'text';

    private const SUCCESS_EXIT_CODE = 0;
    private const FAILURE_EXIT_CODE = 1;

    private $changeLevels = [
        Level::NONE  => 'none',
        Level::PATCH => 'patch',
        Level::MINOR => 'minor',
        Level::MAJOR => 'major',
    ];

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('compare')
            ->setDescription('Compare a set of files to determine what semantic versioning change needs to be done')
            ->setDefinition([
                new InputArgument('source-before', InputArgument::REQUIRED, 'A directory to check'),
                new InputArgument('source-after', InputArgument::REQUIRED, 'A directory to check against'),
                new InputArgument(
                    'allowed-change-level',
                    InputArgument::OPTIONAL,
                    'Allowed code change level: '
                    . Level::PATCH . ' for ' . $this->changeLevels[Level::PATCH] . ' change, '
                    . Level::MINOR . ' for ' . $this->changeLevels[Level::MINOR] . ' change, '
                    . Level::MAJOR . ' for ' . $this->changeLevels[Level::MAJOR] . ' change, ',
                    LEVEL::MAJOR
                ),
                new InputOption(
                    'include-patterns',
                    '',
                    InputArgument::OPTIONAL,
                    'Path to a file containing include patterns',
                    realpath(__DIR__ . '/../../resources/application_includes.txt')
                ),
                new InputOption(
                    'exclude-patterns',
                    '',
                    InputArgument::OPTIONAL,
                    'Path to a file containing exclude patterns',
                    realpath(__DIR__ . '/../../resources/application_excludes.txt')
                ),
                new InputOption(
                    'log-output-location',
                    '',
                    InputArgument::OPTIONAL,
                    'Full path to output report file in table format',
                    'svc.log'
                ),
                new InputOption(
                    'filechange-output-location',
                    '',
                    InputArgument::OPTIONAL,
                    'Full path to report of changed files',
                    'changed-files.log'
                ),
                new InputOption(
                    'report-type',
                    null,
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'Specify report to be used from list: '
                    . implode(', ', $this->getAllReportTypes())
                    . '. Example: --report-type=' . ReportTypes::MFTF . PHP_EOL,
                    []
                ),
                new InputOption(
                    'report-html-target-url',
                    '',
                    InputOption::VALUE_OPTIONAL,
                    'Json data to create url for Target field in HTML report of a specific type. Example: [{"reportTypes": ["interface", "class"], "url": "https://example.com/?target=%s"}]',
                    ''
                ),
            ]);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $cliOutput)
    {
        $sourceBeforeDirArg = $input->getArgument('source-before');
        $sourceBeforeDir = realpath($sourceBeforeDirArg);
        $sourceAfterDirArg = $input->getArgument('source-after');
        $sourceAfterDir = realpath($sourceAfterDirArg);
        $allowedChangeLevel = $input->getArgument('allowed-change-level');
        $includePatternsPath = $input->getOption('include-patterns');
        $excludePatternsPath = $input->getOption('exclude-patterns');
        $logOutputPath = $input->getOption('log-output-location');
        $reportType = $input->getOption('report-type');

        // Derive log format from specified output location.  Default to text.
        $logFormat = self::REPORT_FORMAT_TEXT;
        $logOutputFileParts = pathinfo($logOutputPath);
        $logOutputFileExtension = $logOutputFileParts['extension'];
        if (isset($logOutputFileExtension)) {
            $logFormat = strtolower($logOutputFileExtension);
        }

        // validate input
        $this->validateAllowedLevel($allowedChangeLevel);
        if (!empty($reportType)) {
            $this->validateAllowedReportType($reportType);
        }

        // Generate separate reports for API-annotated code and all code
        $reportBuilder = new ReportBuilder(
            $includePatternsPath,
            $excludePatternsPath,
            $sourceBeforeDir,
            $sourceAfterDir,
            $reportType
        );
        $fileChangeDetector = new FileChangeDetector($sourceBeforeDir, $sourceAfterDir);
        $semanticVersionChecker = new SemanticVersionChecker($reportBuilder, $fileChangeDetector);
        $versionIncrease = $semanticVersionChecker->getVersionIncrease();
        $versionIncWord = strtoupper($this->changeLevels[$versionIncrease]);
        $versionReport = $semanticVersionChecker->loadVersionReport();
        $changedFiles = $semanticVersionChecker->loadChangedFiles();

        foreach ($changedFiles as &$file) {
            if (substr($file, 0, strlen($sourceBeforeDir)) == $sourceBeforeDir) {
                $file = substr($file, strlen($sourceBeforeDir));
            } elseif (substr($file, 0, strlen($sourceAfterDir)) == $sourceAfterDir) {
                $file = substr($file, strlen($sourceAfterDir));
            }
        }
        $changedFiles = array_unique($changedFiles);

        // Log report output
        $logOutputStream = new StreamOutput(fopen($logOutputPath, 'w+'));

        if ($logFormat == self::REPORT_FORMAT_HTML) {
            $logOutputStream->writeln($this->getHtmlHeader());

            $logOutputStream->writeln(
                '<tr class="text-' . ($versionIncrease > $allowedChangeLevel ? 'danger' : 'success') . '">' .
                '<td class="test-name">' .
                'Suggested version increase (based on PHP code analysis and files changed)</td>' .
                '<td>' . $versionIncWord . '</td></tr>'
            );

            // PHP analysis
            $logReporter = new HtmlDbSchemaReporter($versionReport, $input);
            $logReporter->output($logOutputStream);

            // File change analysis
            if ($changedFiles) {
                $logOutputStream->writeln(
                    '<tr class="text"><td class="test-name">Changed files</td>' .
                    '<td><button class="btn-danger collapsible">Details</button><div class="content"><ul>'
                );
                foreach ($changedFiles as $file) {
                    $logOutputStream->writeln('<li>' . $file . '</li>');
                }
                $logOutputStream->writeln('</ul></div></td></tr>');
            } else {
                $logOutputStream->writeln(
                    '<tr class="text"><td class="test-name">Changed files</td><td>No changed files found.</td></tr>'
                );
            }
            $pkgLevelChangeRenderer = new HtmlPackageLevelChangesRenderer($versionReport, $input, $logOutputStream);
            $pkgLevelChangeRenderer->outputPackageChanges();

            $logOutputStream->writeln($this->getHtmlFooter());
        } else {
            // Overall version suggestion
            $logOutputStream->writeln(
                "Suggested version increase (based on PHP code analysis and files changed): $versionIncWord"
            );
            $logOutputStream->writeln('');

            // PHP analysis
            $logReporter = new DbSchemaReporter($versionReport);
            $logOutputStream->writeln('PHP code analysis results:');
            $logReporter->output($logOutputStream);
            $logOutputStream->writeln('');

            // File change analysis
            $logOutputStream->writeln('Changed files:');
            $logOutputStream->writeln('');
            if ($changedFiles) {
                foreach ($changedFiles as $file) {
                    $logOutputStream->writeln($file);
                }
            } else {
                $logOutputStream->writeln('No changed files found.');
            }
        }

        // Console output - display the version increase level
        $cliOutput->writeln(
            ucfirst($this->changeLevels[$versionIncrease]) .
            ' change is detected. Please look into ' . $logOutputPath . ' for details.'
        );

        if ($versionIncrease > $allowedChangeLevel) {
            $cliOutput->writeln(
                "It exceeds the allowed change level, which is $allowedChangeLevel " .
                '(' . $versionIncWord . ').'
            );
            return self::FAILURE_EXIT_CODE;
        }
        return self::SUCCESS_EXIT_CODE;
    }

    /**
     * Method to validate allowed level.
     *
     * @param $input
     *
     * @return void
     * @throws Exception
     */
    private function validateAllowedLevel($input)
    {
        $allowed = array_keys($this->changeLevels);
        if (!in_array($input, $allowed)) {
            throw new Exception("Invalid allowed-change-level argument \"$input\"");
        }
    }

    /**
     * Method to validate allowed report type.
     *
     * @param $input
     *
     * @return void
     * @throws Exception
     */
    private function validateAllowedReportType($input)
    {
        $allowed = array_values($this->getAllReportTypes());
        if (count(array_intersect($input, $allowed)) === 0) {
            throw new Exception('Invalid report-type argument "' . implode(', ', $input) . '"');
        }
    }

    /**
     * Method to get all report types.
     *
     * @return array
     */
    private function getAllReportTypes()
    {
        $typesClass = new ReflectionClass(ReportTypes::class);
        return $typesClass->getConstants();
    }

    /**
     * Return HTML Header
     *
     * @return string
     */
    private function getHtmlHeader()
    {
        $css = file_get_contents(__DIR__ . '/css/bootstrap.min.css');

        return <<<HEADER
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html">
<title>Semantic Version Checker</title>

<style type="text/css">
{$css}
</style>

<style type="text/css">
.collapsible {
  cursor: pointer;
}
.active, .collapsible:hover {
  background-color: #ccc;
}
.content {
  padding: 0 18px;
  display: none;
  overflow: hidden;
  clear: both;
  background-color: #f1f1f1;
}
.collapsible:after {
  content: '\\02795'; /* Unicode character for "plus" sign (+) */
  float: right;
  margin-left: 5px;
}
.active:after {
  content: "\\2796"; /* Unicode character for "minus" sign (-) */
}

.table-bordered {
    border: 1px solid #ddd;
}

.table > tbody > tr > td.test-name {
    padding-left: 20px;
    font-weight: bold;
    width: 30% !important;
}

tr.text-success td.test-name {
    background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAALVBMVEX///8UiSwkkTrQ59VTqGTA4MeRyJ1isHJyuIDv9/Gx2LlDoVaCwI7g7+Oh0KsTvs0lAAAAeUlEQVQYlWWPWxLFIAhDJby0ave/3IJe53am+TsDCaGUFA/3weWIBRSCzM0XSEzVhHCtOaB7okDahPR4lSQX5If3KBIrRra5EjjBwzFbaR4cHs8IRp+desuQmpbWo4UHL/8KrTFJZeg+q/+z32Kf6uc5QF7/mlfb+ABu8AKlWQc1mAAAAABJRU5ErkJggg==');
    background-repeat: no-repeat;
    background-position: left top;
    background-position-y: 10px;
}

tr.text-danger td.test-name {
    background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAAJFBMVEX////QRDfQRDfQRDfQRDfQRDfQRDfQRDfQRDfQRDfQRDfQRDe4JWNlAAAADHRSTlMAIjNEVXeImaq77v89BVwsAAAAYElEQVR42mVPSRLAIAhrRRSS//+3Igc6Q05kYXsuxjJbI6qkDh7A3+QTdBVRJ+b1AUlHgGhzBidDoUfAnxTCPBGllhDEKCUILUeUsG8qkS1taFvbDmun13OA//5V25r0Axi7A+qFTJ0fAAAAAElFTkSuQmCC');
    background-repeat: no-repeat;
    background-position: left top;
    background-position-y: 10px;
}

.table-fixed {
    table-layout: fixed;
}

button.collapsible {
    margin-right: 10px;
    float: right;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,.05);
}

th.column10 {
    width: 10%;
}

th.column60 {
    width: 60%;
}

th.column30 {
    width: 30%;
}
.btn-tooltip:hover:after {
    content: "Click to Copy JSON";
    position: absolute;
    background-color: gray;
    color: white;
}
 .btn-tooltip-copied:hover:after {
     content: "Copied!";
     position: absolute;
     background-color: gray;
     color: white;
 }

</style>
</head>
<body>
<h4>Semantic Version Checker</h4>
<table class="table table-bordered">
<tbody>
HEADER;
    }

    /**
     * Return HTML footer
     *
     * @return string
     */
    private function getHtmlFooter()
    {
        return <<<'FOOTER'
</tbody>
</table>
</body>

<script>
let coll = document.getElementsByClassName("collapsible");
let i;

for (i = 0; i < coll.length; i++) {
  coll[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.display === "block") {
      content.style.display = "none";
    } else {
      content.style.display = "block";
    }
  });
}
</script>
</html>
FOOTER;
    }
}
