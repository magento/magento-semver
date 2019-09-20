<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

class HtmlTableRenderer
{
    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var array
     */
    private $rows = [];

    /**
     * @var string
     */
    private $indent = "    ";

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Initialize dependencies.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param array $headers
     * @return void
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @param array $rows
     * @return void
     */
    public function setRows(array $rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return void
     */
    public function render()
    {
        $output = "<table><tbody>\n";
        $output .= $this->indent . "<tr>\n";
        foreach ($this->headers as $header) {
            $output .= $this->indent . $this->indent . "<th>";
            $output .= $header;
            $output .= "</th>\n";
        }
        $output .= $this->indent . "</tr>\n";

        foreach ($this->rows as $row) {
            $output .= $this->indent . "<tr>\n";
            foreach ($row as $column) {
                $output .= $this->indent . $this->indent . "<td>";
                $output .= $column;
                $output .= "</td>\n";
            }
            $output .= $this->indent . "</tr>\n";
        }

        $output .= "</tbody></table>\n";
        $this->output->write($output);
    }
}
