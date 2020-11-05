<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Suite;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Suite Entity was added to the Module
 */
class SuiteAdded extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M407';

    /**
     * Operation Severity
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = '<suite> was added';
}
