<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Page;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Page <section> was added to the Module
 */
class PageSectionAdded extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M234';

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
    protected $reason = '<page> <section> was added';
}
