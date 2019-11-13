<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation\DocblockAnnotations;

use PhpParser\Node\Stmt\Class_;
use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodParameterTypeMovedFromInlineToDoc extends ClassMethodOperationUnary
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M135', 'M154', 'M166'],
        'interface' => ['M139', 'M139', 'M139'],
        'trait'     => ['M143', 'M155', 'M167']
    ];

    /**
     * @var array
     */
    protected $mapping = [
        'M135' => Level::MAJOR,
        'M139' => Level::MAJOR,
        'M143' => Level::MAJOR,
        'M154' => Level::MINOR,
        'M155' => Level::MINOR,
        'M166' => Level::PATCH,
        'M167' => Level::MINOR
    ];

    /**
     * @var string
     */
    protected $reason = 'Method parameter typehint was moved from in-line to doc block annotation.';

    /**
     * Returns level of error.
     *
     * @return int
     */
    public function getLevel() : int
    {
        return $this->mapping[$this->getCode()];
    }
}
