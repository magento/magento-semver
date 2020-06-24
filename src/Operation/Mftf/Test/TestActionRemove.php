<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Test;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Test action was removed from the Module
 */
class TestActionRemove extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M219';

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
    protected $reason = '<test> <action> was removed from Module';
}
