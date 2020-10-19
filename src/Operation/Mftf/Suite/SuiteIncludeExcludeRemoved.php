<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Suite;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * <group/test/module> was removed in suite include or exclude
 */
class SuiteIncludeExcludeRemoved extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M410';

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
    protected $reason = '<suite> <include/exclude> <group/test/module> was removed';
}
