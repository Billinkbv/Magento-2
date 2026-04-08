<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Version extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Billink_Billink::system/config/version.phtml';

    public function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    public function getCheckerUrl(): string
    {
        return $this->getUrl('billink/version/check', ['isAjax' => 1]);
    }
}
