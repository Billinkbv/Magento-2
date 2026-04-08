<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Form\Field\FeeRange;

use Billink\Billink\Gateway\Helper\Workflow;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Html\Select;

class WorkflowType extends Select
{
    public function __construct(
        Context $context,
        private readonly Workflow $workflowHelper,
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
            foreach ($this->workflowHelper->getTypes() as $type) {
                $key = $this->workflowHelper->getOptionKey($type['value']);

                $this->addOption($key, $type['label']);
            }
        }

        return parent::_toHtml();
    }
}
