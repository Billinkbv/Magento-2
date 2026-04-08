<?php

namespace Billink\Billink\Gateway\Response\Check;

use Billink\Billink\Gateway\Helper\Gateway as GatewayHelper;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Model\Billink\Response\Response;
use Billink\Billink\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Response\HandlerInterface;

use function array_merge;

class Handler implements HandlerInterface
{
    public function __construct(
        private readonly SubjectReader $subjectReader
    ) {
    }

    public function handle(array $handlingSubject, array $response): void
    {
        $payment = $this->subjectReader->readPayment($handlingSubject);
        $response = $this->subjectReader->readResponse($response);

        $additionalInformation = $payment->getAdditionalInformation();

        if (isset($additionalInformation[DataAssignObserver::VALIDATE_ORDER_FLAG])) {
            unset($additionalInformation[DataAssignObserver::VALIDATE_ORDER_FLAG]);
        }

        $payment->setAdditionalInformation(
            array_merge(
                $additionalInformation,
                [
                    GatewayHelper::CHECKUUID => $response->getMsg(Response::INDEX_UUID)
                ]
            )
        );
    }
}
