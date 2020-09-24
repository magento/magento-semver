<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Suite;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Suite before or after remove action was added from the Module
 */
class SuiteBeforeAfterRemoveActionAdded extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M420';

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
    protected $reason = '<suite> <before/after> <remove> <action> was added';
}
