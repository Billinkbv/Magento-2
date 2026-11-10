<?php

namespace Billink\Billink\Gateway\Response\Midpage\Order;

use Billink\Billink\Gateway\Helper\SessionReader;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

use function __;

class RefundHandler implements HandlerInterface
{
    public const RESULT_SUCCESS = 200;

    public function __construct(
        private readonly SubjectReader $subjectReader,
        private readonly SessionReader $sessionReader
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response): void
    {
        /** @var Order $order */
        $order = $this->subjectReader->readOrder($handlingSubject);
        $response = $this->sessionReader->getResponse($response);

        foreach ($response['statuses'] ?? [] as $item) {
            if ((int) ($item['code'] ?? 0) !== self::RESULT_SUCCESS) {
                // Block the credit memo, otherwise Magento shows a refund Billink never applied
                throw new LocalizedException(
                    __('Billink refund failed. Code: %1 ; Message: %2', $item['code'] ?? '', $item['message'] ?? '')
                );
            }
        }

        if (isset($response['statuses'][0]['message'])) {
            $order->addCommentToStatusHistory('Message: ' . $response['statuses'][0]['message']);
        }
        $order->addCommentToStatusHistory('Refund was created in Billink system. ');
    }
}
