<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Section;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Section Entity was added to the Module
 */
class SectionAdded extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M235';

    /**
     * Operation Severity
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = '<section> was added';
}
