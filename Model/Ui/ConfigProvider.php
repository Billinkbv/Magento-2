<?php

namespace Billink\Billink\Model\Ui;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Gateway\Config\MidpageConfig;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Billink\Billink\Model\Config\Source\UsedWorkflow;
use Billink\Billink\Observer\DataAssignObserver;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Payment method code used in the system
     */
    public const CODE = 'billink';
    public const CODE_MIDPAGE = 'billink_midpage';

    public function __construct(
        private readonly Config $config,
        private readonly Session $checkoutSession,
        private readonly SubjectReader $subjectReader,
        private readonly StoreManagerInterface $storeManager,
        private readonly MidpageConfig $midpageConfig
    ) {
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getConfig(): array
    {
        return ['payment' => $this->preparePaymentConfig(), 'quoteData' => $this->prepareQuoteData()];
    }

    /**
     * @throws NoSuchEntityException
     */
    private function preparePaymentConfig(): array
    {
        return [
            self::CODE => [
                'logo' => $this->config->getLogo($this->storeManager->getStore()),
                'isActive' => $this->config->isActive(),
                'isAlternateDeliveryAddressAllowed' => $this->config->getIsAlternateDeliveryAddressAllowed(),
                'workflow' => $this->config->getWorkflow($this->storeManager->getStore()->getId()),
                'workflowTypePrefix' => WorkflowHelper::WORKFLOW_TYPE_PREFIX,
                'feeActive' => $this->config->getIsFeeActive(),
                'feeLabel' => $this->config->getFeeLabel()
            ],
            self::CODE_MIDPAGE => [
                'logo' => $this->midpageConfig->getLogo($this->storeManager->getStore()),
                'isActive' => $this->midpageConfig->isActive(),
                'feeActive' => $this->midpageConfig->getIsFeeActive(),
                'feeLabel' => $this->midpageConfig->getFeeLabel()
            ]
        ];
    }

    /**
     * @throws LocalizedException|NoSuchEntityException
     */
    private function prepareQuoteData(): array
    {
        $quote = $this->checkoutSession->getQuote();

        if (!$quote || !($payment = $quote->getPayment())) {
            return [];
        }

        $usedWorkflows = $this->config->getUsedWorkflow($quote->getStoreId());

        $selectedWorkflow = match ($usedWorkflows) {
            UsedWorkflow::CONFIG_WORKFLOW_PRIVATE => 'P',
            UsedWorkflow::CONFIG_WORKFLOW_BUSINESS => 'B',
            default => $this->subjectReader->readPaymentAIField(
                DataAssignObserver::CUSTOMER_TYPE,
                ['payment' => $payment]
            ),
        };

        return [
            'payment_method' => $payment->getMethod(),
            'selected_workflow' => $selectedWorkflow
        ];
    }
}
