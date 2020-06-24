<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Test;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Test Entity was removed from the Module
 */
class TestRemove extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M218';

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
    protected $reason = '<test> was removed from Module';
}
