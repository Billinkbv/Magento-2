<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Form\Field\Workflow;

use Magento\Framework\View\Element\Html\Select;

class Yesno extends Select
{
    public function setInputName(string $value): static
    {
        return $this->setName($value);
    }

    protected function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->addOption(1, __('Yes'));
            $this->addOption(0, __('No'));
        }

        return parent::_toHtml();
    }
}
