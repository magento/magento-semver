<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Section;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Section Entity was removed from the Module
 */
class SectionRemove extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M215';

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
    protected $reason = '<section> was removed from Module';
}
