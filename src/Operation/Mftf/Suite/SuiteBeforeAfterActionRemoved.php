<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Suite;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Suite before or after action was removed from the Module
 */
class SuiteBeforeAfterActionRemoved extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M412';

    /**
     * Operation Severity
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = '<suite> <before/after> <action> was removed';
}
