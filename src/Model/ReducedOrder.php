<?php

namespace Picqer\BolRetailer\Model;

use DateTime;
use GuzzleHttp\Exception\ClientException;
use Picqer\BolRetailer\Client;
use Picqer\BolRetailer\Order;

/**
 * @property string $orderId       The identifier of the order.
 * @property DateTime $orderPlacedAt The date and time the order was placed.
 * @property ReducedOrderItem[] $orderItems    The items of the order.
 */
class ReducedOrder extends AbstractModel
{
    protected function getOrderItems(): array
    {
        return array_map(function (array $data) {
            return new ReducedOrderItem($this, $data);
        }, $this->data['orderItems']);
    }

    protected function getOrderPlacedAt(): ?DateTime
    {
        $parsedTimestamp = DateTime::createFromFormat(
            DateTime::ATOM,
            $this->data['dateTimeOrderPlaced']
        );

        return $parsedTimestamp instanceof DateTime ? $parsedTimestamp : null;
    }

    public function getFullOrder(): Order
    {
        try {
            $response = Client::request('GET', "orders/${id}");
        } catch (ClientException $e) {
            static::handleException($e);
        }

        return new Order(json_decode((string)$response->getBody(), true));
    }
}
