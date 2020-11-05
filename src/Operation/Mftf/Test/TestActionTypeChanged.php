<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Test;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Test action type was modified
 */
class TestActionTypeChanged extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M224';

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
    protected $reason = '<test> <action> type was changed';
}
