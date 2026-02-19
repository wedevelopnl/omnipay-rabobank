<?php

namespace Omnipay\Rabobank\Message\Response;

use Omnipay\Rabobank\Message\Request\AbstractRabobankRequest;
use Omnipay\Rabobank\Order;

class StatusResponse extends AbstractRabobankResponse
{

    /**
     * Indication if there are more order statuses available than in this response.
     * In that case, a status pull call can be made (with the same notification token).
     * This can be repeated until this result is false.
     *
     * @return bool
     */
    public function getMoreStatusesAvailable()
    {
        return (bool)$this->data['moreOrderResultsAvailable'];
    }

    /**
     * @return Order[]
     */
    public function getOrders()
    {
        $orders = [];
        foreach ((array)$this->data['orderResults'] as $orderResult) {
            $order = new Order();

            foreach ($orderResult as $field => $value) {
                $order->{$field} = $value;
            }

            $orders[] = $order;
        }

        return $orders;
    }

    protected function getSignatureData(): array
    {
        return [
            $this->getMoreStatusesAvailable(),
            array_map(function (Order $order) {
                return [
                    $order->merchantOrderId,
                    $order->omnikassaOrderId,
                    $order->poiId,
                    $order->orderStatus,
                    $order->orderStatusDateTime,
                    $order->errorCode,
                    [$order->paidAmount['currency'], $order->paidAmount['amount']],
                    [$order->totalAmount['currency'], $order->totalAmount['amount']],
                ];
            }, $this->getOrders()),
        ];
    }
}
