<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Test;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Test action sequence was changed
 */
class TestActionSequenceChanged extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M223';

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
    protected $reason = '<test> <action> sequence was changed';
}
