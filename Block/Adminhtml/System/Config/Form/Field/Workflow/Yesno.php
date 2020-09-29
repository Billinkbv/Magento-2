<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Form\Field\Workflow;

use Magento\Framework\View\Element\Html\Select;

/**
 * Class Yesno
 * @package Billink\Billink\Block\Adminhtml\System\Config\Form\Field\Workflow
 */
class Yesno extends Select
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption(1, __('Yes'));
            $this->addOption(0, __('No'));
        }

        return parent::_toHtml();
    }

    /**
     * @param $value string
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
