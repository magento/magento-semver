<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Section;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Section <element> was removed from the Module
 */
class SectionElementRemove extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M216';

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
    protected $reason = '<section> <element> was removed';
}
