<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Form\Field;

use Billink\Billink\Block\Adminhtml\System\Config\Form\Field\Workflow\Type;
use Billink\Billink\Block\Adminhtml\System\Config\Form\Field\Workflow\Yesno;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

use function __;

class Workflow extends AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Billink_Billink::system/config/form/field/array.phtml';

    private ?Type $typeRenderer = null;

    private ?Yesno $checkRenderer = null;

    public function __construct(
        Context $context,
        private readonly WorkflowHelper $workflowHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @throws LocalizedException
     */
    protected function getTypeRenderer(): Type|BlockInterface|null
    {
        if (!$this->typeRenderer) {
            $this->typeRenderer = $this->getLayout()->createBlock(
                Type::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->typeRenderer;
    }

    /**
     * @throws LocalizedException
     */
    protected function getCheckRenderer(): Yesno|BlockInterface|null
    {
        if (!$this->checkRenderer) {
            $this->checkRenderer = $this->getLayout()->createBlock(
                Yesno::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->checkRenderer;
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn(
            WorkflowHelper::FIELD_TYPE,
            [
                'label' => __('Type'),
                'renderer' => $this->getTypeRenderer(),
            ]
        );

        $this->addColumn(
            WorkflowHelper::FIELD_NUMBER,
            [
                'label' => __('Number'),
            ]
        );

        $this->addColumn(
            WorkflowHelper::FIELD_MAX_AMOUNT,
            [
                'label' => __('Max Amount'),
            ]
        );

        $this->addColumn(
            WorkflowHelper::FIELD_CHECK,
            [
                'label' => __('With Check?'),
                'renderer' => $this->getCheckRenderer(),
            ]
        );

        $this->getElement()->setValue($this->prepareTypesArray());

        $this->_addAfter = false;
    }

    protected function prepareTypesArray(): array
    {
        $types = $this->workflowHelper->getTypes();
        $values = $this->getElement()->getValue();
        $result = [];

        foreach ($types as $type) {
            $key = $this->workflowHelper->getOptionKey($type['value']);

            if ($values && isset($values[$key])) {
                $values[$key]['type'] = __($values[$key]['type']);
                $result[$key] = $values[$key];
                continue;
            }

            // Add default values, if no setting is present
            $result[$key] = [
                WorkflowHelper::FIELD_TYPE => $type['label'],
                WorkflowHelper::FIELD_NUMBER => '',
                WorkflowHelper::FIELD_MAX_AMOUNT => '',
                WorkflowHelper::FIELD_CHECK => 1
            ];
        }

        return $result;
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $isWithCheck = (int) $row->getIsWithCheck();

        $options = [];

        if ($isWithCheck === 0) {
            $optionKey = 'option_' . $this->getCheckRenderer()?->calcOptionHash($isWithCheck);
            $options[$optionKey] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}
