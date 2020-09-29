<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Version
 * @package Billink\Billink\Block\Adminhtml\System\Config\Form\Field
 */
class Version extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Billink_Billink::system/config/version.phtml';

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getCheckerUrl()
    {
        return $this->getUrl('billink/version/check', ['isAjax' => 1]);
    }
}