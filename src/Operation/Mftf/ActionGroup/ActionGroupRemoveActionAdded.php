<?php

namespace Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup;

use Magento\SemanticVersionChecker\Operation\Mftf\MftfOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * ActionGroup remove action was added
 */
class ActionGroupRemoveActionAdded extends MftfOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M404';

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
    protected $reason = '<actionGroup> <remove action> was added';
}
