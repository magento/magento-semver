<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Suite;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Suite before or after action type was modified
 */
class SuiteBeforeAfterActionTypeChanged extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M419';

    /**
     * Operation Severity
     * @var int
     */
    protected $level = Level::PATCH;

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = '<suite> <before/after> <action> type was changed';
}
