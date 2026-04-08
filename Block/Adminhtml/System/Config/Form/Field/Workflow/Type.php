<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Form\Field\Workflow;

use Magento\Framework\View\Element\AbstractBlock;

use function __;

class Type extends AbstractBlock
{
    protected function _toHtml(): string
    {
        return $this->getTypeHtml();
    }

    private function getTypeHtml(): string
    {
        $html = '<%- ' . __($this->getColumnName()) . ' %>';
        $html .= '<input type="hidden" name="'
            . $this->getInputName() . '" value="<%- ' . $this->getColumnName() . ' %>" />';

        return $html;
    }
}
