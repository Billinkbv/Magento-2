<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Form\Field\FeeRange;

use Magento\Backend\Block\Template\Context;
use Magento\Directory\Model\Config\Source\Country as SourceCountry;
use Magento\Framework\View\Element\Html\Select;

use function __;

class CountryType extends Select
{
    public function __construct(
        Context $context,
        private readonly SourceCountry $sourceCountry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function setInputName(string $value): static
    {
        return $this->setName($value);
    }

    protected function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $types = $this->sourceCountry->toOptionArray();
            $this->addOption('other', __('Other countries'));
            foreach ($types as $type) {
                $this->addOption($type['value'], $type['label']);
            }
        }

        return parent::_toHtml();
    }
}
