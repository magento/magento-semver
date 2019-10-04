<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\SemanticVersionChecker\Filter\AllowedChangeFilter;

interface ChangedFileFilterInterface
{
    /**
     * Filter out files that only differ due to changes of the type checked by this filter
     *
     * If allowed changes are found but the file still differs, the in-memory file should be adjusted accordingly
     * in case other allowed changes checked in other filters make the file acceptable
     *
     * file contents parameter format:
     * [
     *   relative_path => [<lines of file according to file(<path>)>]
     *   ...
     * ]
     *
     * @param array[] $beforeFileContents
     * @param array[] $afterFileContents
     * @return void
     */
    public function filter(&$beforeFileContents, &$afterFileContents);
}