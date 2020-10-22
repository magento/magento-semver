<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\Metadata;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Metadata Child was added in the Module
 */
class MetadataChildAdded extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M242';

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
    protected $reason = '<operation> child element was added';
}
