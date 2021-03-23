<?php

namespace Picqer\BolRetailer;

use GuzzleHttp\Exception\ClientException;
use Picqer\BolRetailer\Exception\HttpException;
use Picqer\BolRetailer\Exception\OrderNotFoundException;
use Picqer\BolRetailer\Exception\RateLimitException;
use Picqer\BolRetailer\Model;
use Picqer\BolRetailer\Model\OrderItem;
use Picqer\BolRetailer\Model\ReducedOrderItem;

class Order extends Model\Order
{
    /**
     * Get a single order.
     *
     * @param string $id The identifier of the order to get.
     *
     * @return self|null
     */
    public static function get(string $id): ?Order
    {
        try {
            $response = Client::request('GET', "orders/${id}");
        } catch (ClientException $e) {
            static::handleException($e);
        }

        self::throwExceptionIfNotSuccessfull($response);

        return new Order(json_decode((string)$response->getBody(), true));
    }

    /**
     * Get all open orders.
     *
     * @param int $page The page to get the orders from.
     * @param string $method The fulfilment method of the orders to list.
     *
     * @return Model\ReducedOrder[]
     */
    public static function all(int $page = 1, string $method = 'FBR'): array
    {
        $query = ['page' => $page, 'fulfilment-method' => $method];

        try {
            $response = Client::request('GET', 'orders', ['query' => $query]);
            $response = json_decode((string)$response->getBody(), true);
        } catch (ClientException $e) {
            static::handleException($e);
        }

        /** @var array<array-key, mixed> */
        $orders = $response['orders'] ?? [];

        return array_map(function (array $data) {
            return new Model\ReducedOrder($data);
        }, $orders);
    }

    /**
     * Cancel an order item by order item id.
     *
     * @param string $orderItemId The id of the order item to cancel.
     * @param string $reasonCode The code representing the reason for cancellation of this item.
     *
     * @return Model\ProcessStatus
     */
    public static function cancelOrderItem(string $orderItemId, string $reasonCode): Model\ProcessStatus
    {
        $data = ['reasonCode' => $reasonCode];

        try {
            $response = Client::request('PUT', "orders/${orderItemId}/cancellation", ['body' => json_encode($data)]);
        } catch (ClientException $e) {
            static::handleException($e);
        }

        return new ProcessStatus(json_decode((string)$response->getBody(), true));
    }

    private static function handleException(ClientException $e): void
    {
        $response = $e->getResponse();

        if ($response && $response->getStatusCode() === 404) {
            throw new OrderNotFoundException(
                json_decode((string)$response->getBody(), true),
                404,
                $e
            );
        } elseif ($response && $response->getStatusCode() === 429) {
            throw new RateLimitException(
                json_decode((string)$response->getBody(), true),
                429,
                $e
            );
        } elseif ($response) {
            throw new HttpException(
                json_decode((string)$response->getBody(), true),
                $response->getStatusCode(),
                $e
            );
        }

        throw $e;
    }

    private static function throwExceptionIfNotSuccessfull($response)
    {
        if ($response->getStatusCode() === 404) {
            throw new OrderNotFoundException(
                json_decode((string)$response->getBody(), true),
                404
            );
        }

        if ($response->getStatusCode() === 429) {
            throw new RateLimitException(
                json_decode((string)$response->getBody(), true),
                429
            );
        }
    }
}
