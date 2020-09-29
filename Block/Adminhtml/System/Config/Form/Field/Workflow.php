<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Form\Field;

use Billink\Billink\Block\Adminhtml\System\Config\Form\Field\Workflow\Type;
use Billink\Billink\Block\Adminhtml\System\Config\Form\Field\Workflow\Yesno;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Class Workflow
 * @package Billink\Billink\Block\Adminhtml\System\Config\Form\Field
 */
class Workflow extends AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Billink_Billink::system/config/form/field/array.phtml';

    /**
     * @var Type
     */
    private $typeRenderer;

    /**
     * @var Yesno
     */
    private $checkRenderer;

    /**
     * @var Workflow
     */
    private $workflowHelper;

    /**
     * Workflow constructor.
     * @param Context $context
     * @param WorkflowHelper $workflowHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        WorkflowHelper $workflowHelper,
        array $data = []
    ) {
        $this->workflowHelper = $workflowHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return Type|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getTypeRenderer()
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
     * @return Yesno|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCheckRenderer()
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
     *
     */
    protected function _prepareToRender()
    {
        $this->addColumn(WorkflowHelper::FIELD_TYPE, [
            'label' => __('Type'),
            'renderer' => $this->getTypeRenderer(),
        ]);

        $this->addColumn(WorkflowHelper::FIELD_NUMBER, [
            'label' => __('Number'),
        ]);

        $this->addColumn(WorkflowHelper::FIELD_MAX_AMOUNT, [
            'label' => __('Max Amount'),
        ]);

        $this->addColumn(WorkflowHelper::FIELD_CHECK, [
            'label' => __('With Check?'),
            'renderer' => $this->getCheckRenderer(),
        ]);

        $this->getElement()->setValue($this->prepareTypesArray());

        $this->_addAfter = false;
    }

    /**
     * @return array
     */
    protected function prepareTypesArray()
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
     * @param DataObject $row
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $isWithCheck = (int)$row->getIsWithCheck();

        $options = [];

        if ($isWithCheck === 0) {
            $optionKey = 'option_' . $this->getCheckRenderer()->calcOptionHash($isWithCheck);
            $options[$optionKey] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}