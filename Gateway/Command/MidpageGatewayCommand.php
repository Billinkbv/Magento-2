<?php

namespace Billink\Billink\Gateway\Command;

use Billink\Billink\Model\Payment\OrderHistory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

class MidpageGatewayCommand implements CommandInterface
{
    public function __construct(
        protected readonly BuilderInterface $requestBuilder,
        protected readonly TransferFactoryInterface $transferFactory,
        protected readonly ClientInterface $client,
        protected readonly HandlerInterface $handler,
        protected readonly ValidatorInterface $validator,
        protected readonly Logger $logger,
        protected readonly OrderHistory $orderHistory,
        protected readonly LoggerInterface $errorLogger
    ) {
    }

    public function execute(array $commandSubject)
    {
        $command = $this->requestBuilder->build($commandSubject);
        $transferO = $this->transferFactory->create(
            $command
        );

        try {
            $response = $this->client->placeRequest($transferO);
        } catch (ClientException $e) {
            $this->errorLogger->error('Billink Connection error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw new LocalizedException(
                __('Unable to connect to payment method. Try again a little later or contact support.')
            );
        }
        try {
            $result = $this->validator->validate(
                array_merge($commandSubject, ['response' => $response])
            );
            if (!$result->isValid()) {
                $this->processErrors($commandSubject, $result);
            }

            $this->handler->handle(
                $commandSubject,
                $response
            );
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->errorLogger->error('Failed to generate redirect url: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     *
     * Throws an exception with mapped message or default error.
     * @param array $commandSubject
     * @param ResultInterface $result
     * @throws \Exception
     */
    protected function processErrors(array $commandSubject, ResultInterface $result): void
    {
        $payment = SubjectReader::readPayment($commandSubject);
        $messages = [];
        foreach ($result->getFailsDescription() as $failPhrase) {
            $messages[] = (string)$failPhrase;
        }
        $messages = array_unique($messages);
        $order = $payment->getPayment()->getOrder();
        $messageLog = sprintf(
            'Payment Gateway Error: Order # %s - %s',
            $order->getIncrementId(),
            implode(PHP_EOL, $messages)
        );
        $this->orderHistory->setOrderMessage($order, $messageLog);
        $this->errorLogger->critical($messageLog);

        $message = !empty($messages)
            ? __(implode(PHP_EOL, $messages))
            : __('Transaction has been declined. Please try again later.');

        throw new LocalizedException($message);
    }
}
