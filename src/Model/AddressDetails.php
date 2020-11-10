<?php

namespace Picqer\BolRetailer\Model;

/**
 * The address details of an order.
 *
 * @property string $pickUpPointName         The name of Pick Up Point location this order needs to be shipped to.
 * @property string $salutation              The salutation code.
 * @property string $firstName               The first name.
 * @property string $surName                 The surname.
 * @property string $streetName              The street name.
 * @property string $houseNumber             The house number.
 * @property string $houseNumberExtension    The extension on the house number.
 * @property string $extraAddressInformation Extra information about the address.
 * @property string $zipCode                 The ZIP code.
 * @property string $city                    The name of the city.
 * @property string $countryCode             The country code.
 * @property string $email                   The e-mail address.
 * @property string $company                 The company name.
 * @property string $vatNumber               The VAT number.
 * @property string $kvkNumber               The KVK number.
 * @property string $deliveryPhoneNumber     The delivery phone number.
 * @property string $orderReference          The order reference specified by the customer.
 */
class AddressDetails extends AbstractModel
{
}
