<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Reporter;

use Magento\SemanticVersionChecker\Analyzer\EtSchemaAnalyzer;
use Magento\SemanticVersionChecker\Helper\PackageNameResolver;
use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\Report\Report;
use PHPSemVerChecker\SemanticVersioning\Level;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Prints a table of packages with BIC level changes
 */
class HtmlPackageLevelChangesRenderer
{
    /**
     * @var Report
     */
    private $report;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $contexts;

    /**
     * @var PackageNameResolver
     */
    private $packageNameResolver;

    /**
     * HtmlPackagingJsonRenderer constructor.
     * @param Report $report
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct(Report $report, InputInterface $input, OutputInterface $output)
    {

        $this->report = $report;
        $this->input = $input;
        $this->output = $output;
        $this->contexts = [
            'class',
            'function',
            'interface',
            'trait',
            'database',
            'layout',
            'di',
            'system',
            'xsd',
            'less',
            'mftf',
            EtSchemaAnalyzer::CONTEXT
        ];
        $this->packageNameResolver = new PackageNameResolver($input);
    }

    /**
     * Output Package Level Changes table.
     */
    public function outputPackageChanges()
    {
        $pkgChangesJson = $this->getPackageChanges();
        $this->output->writeln('<tr class="text"><td class="test-name">Package Level Changes</td>');
        //Skip writing table if no severe changes are detected
       if (!$pkgChangesJson) {
           $this->output->writeln('<td>No severe changes found to packages.</td></tr>');
           return;
       }
        $this->output->writeln('<td><button class="btn-danger collapsible">Details</button><div class="content">');
        $this->output->writeln(
            '<table class="table table-striped"><tr><th class="column10">Level</th>' .
            '<th class="column60">Package Name</th></tr>'
        );

        foreach ($pkgChangesJson as $pkg) {
            $this->output->writeln(
                '<tr class="text-danger"><td>'. $pkg['level'] . '</td><td>' . $pkg['name'] . '</td></tr>'
            );
        }
        $this->output->writeln('</table>');
        $this->printClipboardJS();
        $pkgChangesJsonString = json_encode($pkgChangesJson, JSON_PRETTY_PRINT);
        $this->output->writeln('<pre id="packageChangesJson">');
        $this->output->writeln($pkgChangesJsonString);

        $this->output->writeln('</pre></tr>');
    }

    /**
     * Outputs JS to copy JSON to clipboard
     */
    private function printClipboardJS() {
        $this->output->writeln( <<<COPY_PKG_JSON_SCRIPT
<input type="hidden" id="copyBuffer" value="">
<button class="btn-info btn-tooltip" id="copy_pkg_json_btn">Copy</button>
<script>
let copyPkgJsonBtn = document.getElementById('copy_pkg_json_btn');
    copyPkgJsonBtn.addEventListener('mouseover', () => {
    copyPkgJsonBtn.classList.remove('btn-tooltip-copied');
    copyPkgJsonBtn.classList.remove('btn-tooltip');
    copyPkgJsonBtn.classList.add('btn-tooltip');
})
copyPkgJsonBtn.addEventListener('click', () => {
    let packageChangesJson = document.getElementById('packageChangesJson');
    let text = packageChangesJson.innerText;
    let textJson = JSON.parse(text);
    text = JSON.stringify(textJson, null, 0)

    let copyBuffer = document.getElementById('copyBuffer');
    copyBuffer.value = text;
    copyBuffer.type = 'text';
    copyBuffer.select();
    let didCopy = document.execCommand('copy');
    copyBuffer.type = 'hidden';
    packageChangesJson.focus();
    if (didCopy) {
        copyPkgJsonBtn.classList.remove('btn-tooltip');
        copyPkgJsonBtn.classList.remove('btn-tooltip-copied');
        copyPkgJsonBtn.classList.add('btn-tooltip-copied');
    }
});
</script>
COPY_PKG_JSON_SCRIPT
        );
    }

    /**
     * Get array of changed packages and their severity
     *
     * @return array
     */
    private function getPackageChanges():array
    {
        $results = [];
        foreach ($this->contexts as $context) {
            foreach (Level::asList('desc') as $level) {
                $reportForLevel = $this->report[$context][$level] ?? [];
                /** @var \PHPSemVerChecker\Operation\Operation $operation */
                foreach ($reportForLevel as $operation) {
                    $pkgName = $this->getPackageName($operation);
                    if ($pkgName === null) {
                        $error = "Unable to resolve package name for composer.json for change to file: "
                            . $operation->getLocation();
                        $this->output->writeln('<pre>' . $error  . '</pre>');
                        continue;
                    }
                    $this->insertPackageChange($pkgName, $level, $results);
                }
            }
        }
        $results = $this->transformOutputArray($results);
        return $results;
    }

    /**
     * Insert package into results array. Skip displaying patch-level changes
     *
     * @param string $pkgName
     * @param int $level
     * @param array $results
     */
    private function insertPackageChange(string $pkgName, int $level, array &$results) {
        if(isset($results[$pkgName]))
        {
            if ($level <= $results[$pkgName]) {
                return;
            }
        }
        if($level > Level::PATCH) {
            $results[$pkgName] = $level;
        }
    }

    /**
     * Transforms array of pkgChanges into expected output format
     *
     * @param array $pkgChanges
     * @return array
     */
    private function transformOutputArray(array &$pkgChanges) {
        $results = [];
        foreach ($pkgChanges as $pkgName => $level) {
            $results[] = [
                'name' => $pkgName,
                'level' => Level::toString($level)
            ];
        }
        return $results;
    }

    /**
     * Get ModulePackage name given the changed file's location
     *
     * @param Operation $operation
     * @return string|null
     */
    private function getPackageName(Operation $operation):?string {
        return $this->packageNameResolver->getPackageName($operation->getLocation());
    }
}