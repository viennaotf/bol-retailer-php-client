<?php

namespace Picqer\BolRetailer\Model;

use DateTime;
use Picqer\BolRetailer\Client;
use Picqer\BolRetailer\ProcessStatus;

/**
 * An order.
 *
 * @property string $orderId                        The identifier of the order.
 * @property boolean $pickUpPoint                   The identifier of the order.
 * @property DateTime|null $orderPlacedDateTime     The date and time the order was placed.
 * @property OrderItem[] $orderItems                The items of the order.
 * @property OrderCustomerDetails $shipmentDetails  The details of the customer that placed the order.
 * @property OrderCustomerDetails $billingDetails   The details of the customer that placed the order.
 */
class Order extends AbstractModel
{
    protected function getOrderItems(): array
    {
        /** @var array<array-key, mixed> */
        $items = $this->data['orderItems'] ?? [];

        return array_map(function (array $data) {
            return new OrderItem($this, $data);
        }, $items);
    }

    protected function getOrderPlacedAt(): ?DateTime
    {
        if (empty($this->data['dateTimeOrderPlaced'])) {
            return null;
        }

        return DateTime::createFromFormat(DateTime::ATOM, $this->data['dateTimeOrderPlaced']);
    }

    protected function getCustomerDetails(): OrderCustomerDetails
    {
        return new OrderCustomerDetails($this->data['customerDetails']);
    }

    /**
     * Get delivery options for a shippable configuration
     * of a number of order items within an order.
     *
     * @param string $orderId The id of the order item to cancel.
     * @param string $reasonCode The code representing the reason for cancellation of this item.
     *
     * @return Model\ProcessStatus
     */
    protected function getDeliveryOptions(): Model\ProcessStatus
    {
        $orderItemId = $orderItem instanceof OrderItem || $orderItem instanceof ReducedOrderItem
            ? $orderItem->orderItemId
            : $orderItem;
        $data = ['reasonCode' => $reasonCode];

        try {
            $response = Client::request('PUT', "orders/${orderItemId}/cancellation", ['body' => json_encode($data)]);
        } catch (ClientException $e) {
            static::handleException($e);
        }

        return new ProcessStatus(json_decode((string)$response->getBody(), true));
    }
}
