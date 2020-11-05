<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Test;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Test remove action was removed
 */
class TestRemoveActionRemoved extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M402';

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
    protected $reason = '<test> <remove action> was removed';
}
