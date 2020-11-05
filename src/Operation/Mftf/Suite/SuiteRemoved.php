<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Suite;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Suite Entity was removed from the Module
 */
class SuiteRemoved extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M408';

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
    protected $reason = '<suite> was removed';
}
