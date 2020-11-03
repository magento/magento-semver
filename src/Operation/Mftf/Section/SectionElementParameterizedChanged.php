<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Section;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Section <element> parameterized attribute was modified
 */
class SectionElementParameterizedChanged extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M250';

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
    protected $reason = '<section> <element> parameterized was changed';
}
