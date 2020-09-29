<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Form\Field\Workflow;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Class Type
 * @package Billink\Billink\Block\Adminhtml\System\Config\Form\Field\Workflow
 */
class Type extends AbstractBlock
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getTypeHtml();
    }

    /**
     * @return string
     */
    private function getTypeHtml()
    {
        $html = '<%- ' . __($this->getColumnName()) . ' %>';
        $html .= '<input type="hidden" name="'
            . $this->getInputName() . '" value="<%- ' . $this->getColumnName() . ' %>" />';

        return $html;
    }
}