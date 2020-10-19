<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Test;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Test annotation was added to Module
 */
class TestAnnotationAdded extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M239';

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
    protected $reason = '<test> <annotation> was added';
}
