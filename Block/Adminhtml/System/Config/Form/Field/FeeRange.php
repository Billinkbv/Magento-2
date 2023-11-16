<?php

namespace Billink\Billink\Block\Adminhtml\System\Config\Form\Field;

use Billink\Billink\Block\Adminhtml\System\Config\Form\Field\FeeRange\CountryType;
use Billink\Billink\Block\Adminhtml\System\Config\Form\Field\FeeRange\WorkflowType;
use Billink\Billink\Helper\Fee;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class FeeRange
 * @package Billink\Billink\Block\Adminhtml\System\Config\Form\Field
 */
class FeeRange extends AbstractFieldArray
{
    private ?WorkflowType $typeRenderer = null;
    private ?CountryType $countryRenderer = null;

    /**
     * FeeRange constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Add table system config columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn(Fee::COUNTRY, [
            'label' => __('Country'),
            'renderer' => $this->getCountryRenderer()
        ]);

        $this->addColumn(Fee::INDEX_WORKFLOW_TYPE, [
            'label' => __('Workflow Type'),
            'renderer' => $this->getWorkflowTypeRenderer()
        ]);

        $this->addColumn(Fee::INDEX_TOTAL_FROM, [
            'label' => __('From')
        ]);

        $this->addColumn(Fee::INDEX_TOTAL_TO, [
            'label' => __('To')
        ]);

        $this->addColumn(Fee::INDEX_AMOUNT, [
            'label' => __('Amount')
        ]);

        $this->_addAfter = false;
    }

    /**
     * @return WorkflowType|BlockInterface
     * @throws LocalizedException
     */
    protected function getWorkflowTypeRenderer(): WorkflowType|BlockInterface
    {
        if ($this->typeRenderer === null) {
            $this->typeRenderer = $this->getLayout()->createBlock(
                WorkflowType::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->typeRenderer;
    }

    protected function getCountryRenderer(): CountryType|BlockInterface
    {
        if ($this->countryRenderer === null) {
            $this->countryRenderer = $this->getLayout()->createBlock(
                CountryType::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->countryRenderer;
    }

    /**
     * @param DataObject $row
     *
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = $row->getData('option_extra_attrs') ?? [];

        $workflowType = $row->getWorkflowType();
        if ($workflowType) {
            $optionType = $this->getWorkflowTypeRenderer()->calcOptionHash($workflowType);

            $options['option_' . $optionType] = 'selected="selected"';
        }

        $countryType = $row->getCountry();
        if ($countryType) {
            $optionType = $this->getCountryRenderer()->calcOptionHash($countryType);

            $options['option_' . $optionType] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}
