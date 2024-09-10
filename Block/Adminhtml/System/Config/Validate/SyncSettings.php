<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Validate;

use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

class SyncSettings extends Field
{
    protected $_template = 'Billink_Billink::system/config/syncSettings.phtml';

    /**
     * Remove scope label
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope();
        $element->unsCanUseWebsiteValue();
        $element->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    //@codingStandardsIgnoreLine
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    public function getAjaxUrl(): string
    {
        return $this->getUrl('billink/config/sync');
    }

    /**
     * @throws LocalizedException
     */
    public function getButtonHtml(): string
    {
        $layout = $this->getLayout();
        $buttonBlock = $layout->createBlock(Button::class);
        $buttonBlock->setData(
            [
                'id' => 'sync_settings',
                'label' => __('Sync Settings'),
            ]
        );

        return $buttonBlock->toHtml();
    }
}
