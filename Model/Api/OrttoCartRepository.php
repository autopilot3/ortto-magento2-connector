<?php
declare(strict_types=1);


namespace Ortto\Connector\Model\Api;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as QuoteItemsCollectionFactory;
use Ortto\Connector\Api\ConfigScopeInterface;
use Ortto\Connector\Api\Data\OrttoCartInterface;
use Ortto\Connector\Api\Data\OrttoCartItemInterface;
use Ortto\Connector\Helper\Data;
use Ortto\Connector\Helper\To;
use Ortto\Connector\Logger\OrttoLoggerInterface;
use Ortto\Connector\Model\Data\OrttoAddressFactory;
use Ortto\Connector\Model\Data\OrttoCartFactory;

class OrttoCartRepository implements \Ortto\Connector\Api\OrttoCartRepositoryInterface
{
    private const PRODUCT_ID = 'product_id';
    private const PARENT_ITEM_ID = 'parent_item_id';
    private const ITEM_ID = 'item_id';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';
    private const DISCOUNT = 'discount_amount';
    private const BASE_DISCOUNT = 'base_discount_amount';
    private const PRICE = 'price';
    private const BASE_PRICE = 'base_price';
    private const ROW_TOTAL = 'row_total';
    private const BASE_ROW_TOTAL = 'base_row_total';
    private const BASE_TAX = 'base_tax_amount';
    private const TAX = 'tax_amount';
    private const TAX_PERCENT = 'tax_percent';
    private const QUANTITY = 'qty';
    private const ITEMS = 'items';

    private OrttoCartFactory $cartFactory;
    private OrttoLoggerInterface $logger;
    private CartRepositoryInterface $cartRepository;
    private Data $helper;
    private \Ortto\Connector\Model\Data\OrttoCartItemFactory $cartItemFactory;
    private OrttoProductRepository $productRepository;
    private OrttoAddressFactory $addressFactory;
    private UrlInterface $url;
    private QuoteItemsCollectionFactory $quoteItemsCollectionFactory;
    private CountryFactory $countryFactory;

    public function __construct(
        OrttoLoggerInterface $logger,
        Data $helper,
        \Ortto\Connector\Model\Data\OrttoCartFactory $cartFactory,
        \Ortto\Connector\Model\Data\OrttoCartItemFactory $cartItemFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        OrttoProductRepository $productRepository,
        \Ortto\Connector\Model\Data\OrttoAddressFactory $addressFactory,
        QuoteItemsCollectionFactory $quoteItemsCollectionFactory,
        UrlInterface $url,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->cartFactory = $cartFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->cartItemFactory = $cartItemFactory;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->addressFactory = $addressFactory;
        $this->url = $url;
        $this->quoteItemsCollectionFactory = $quoteItemsCollectionFactory;
        $this->countryFactory = $countryFactory;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(ConfigScopeInterface $scope, int $cartId, array $data = [])
    {
        $cart = $this->cartRepository->get($cartId);
        return $this->convert($scope, $cart);
    }

    /**
     * @param CartInterface|\Magento\Quote\Model\Quote $cart
     * @return OrttoCartInterface
     */
    private function convert(ConfigScopeInterface $scope, $cart)
    {
        $subtotal = To::float($cart->getSubtotal());
        $subtotalWithDiscount = To::float($cart->getSubtotalWithDiscount());
        $baseSubtotal = To::float($cart->getBaseSubtotal());
        $baseSubtotalWithDiscount = To::float($cart->getBaseSubtotalWithDiscount());
        $cartId = To::int($cart->getId());
        $data = $this->cartFactory->create();
        $data->setId($cartId);

        $itemsData = $this->getItems($scope, $cartId);
        $data->setItems($itemsData[self::ITEMS]);
        $data->setTax($itemsData[self::TAX]);
        $data->setBaseTax($itemsData[self::BASE_TAX]);

        $data->setIpAddress($cart->getRemoteIp());
        $data->setCreatedAt($this->helper->toUTC($cart->getCreatedAt()));
        $data->setUpdatedAt($this->helper->toUTC($cart->getUpdatedAt()));
        $data->setItemsCount(To::int($cart->getItemsCount()));
        $data->setItemsQuantity(To::int($cart->getItemsQty()));
        $data->setCurrencyCode((string)$cart->getGlobalCurrencyCode());
        $data->setBaseCurrencyCode((string)$cart->getBaseCurrencyCode());
        $data->setStoreCurrencyCode((string)$cart->getStoreCurrencyCode());
        // In case they support multiple codes in the future
        // https://support.magento.com/hc/en-us/articles/115004348454-How-many-coupons-can-a-customer-use-in-Adobe-Commerce-
        $data->setDiscountCodes([(string)$cart->getCouponCode()]);
        $data->setGrandTotal(To::float($cart->getGrandTotal()));
        $data->setBaseGrandTotal(To::float($cart->getBaseGrandTotal()));
        $data->setSubtotal($subtotal);
        $data->setBaseSubtotal($baseSubtotal);
        $data->setSubtotalWithDiscount($subtotalWithDiscount);
        $data->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
        $data->setCartUrl($this->url->getUrl('checkout/cart', ['_secure' => true]));
        $data->setCheckoutUrl($this->url->getUrl('checkout', ['_secure' => true]));
        $data->setDiscount(abs($subtotal - $subtotalWithDiscount));
        $data->setBaseDiscount(abs($baseSubtotal - $baseSubtotalWithDiscount));

        $shippingAddress = $cart->getShippingAddress();
        if (!empty($shippingAddress)) {
            $address = $this->convertQuoteAddress($shippingAddress);
            $data->setShippingAddress($address);
            $data->setShipping(To::float($shippingAddress->getShippingAmount()));
            $data->setBaseShipping(To::float($shippingAddress->getBaseShippingAmount()));
            $shippingTax = To::float($shippingAddress->getShippingTaxAmount());
            if ($shippingTax > 0) {
                $data->setShippingTax($shippingTax);
                $data->setTax($data->getTax() + $shippingTax);
            }

            $baseShippingTax = To::float($shippingAddress->getBaseShippingTaxAmount());
            if ($baseShippingTax > 0) {
                $data->setBaseTax($data->getBaseTax() + $baseShippingTax);
                $data->setBaseShippingTax($baseShippingTax);
            }
        }
        $billingAddress = $cart->getBillingAddress();
        if (!empty($billingAddress)) {
            $address = $this->convertQuoteAddress($billingAddress);
            $data->setBillingAddress($address);
        }

        return $data;
    }

    /**
     * @param ConfigScopeInterface $scope
     * @param int $cartId
     * @return array
     */
    private function getItems(ConfigScopeInterface $scope, $cartId)
    {
        $collection = $this->quoteItemsCollectionFactory->create();
        $collection->addFieldToSelect("*")
            // Items with null parent item ids will always be first
            ->setOrder(self::PARENT_ITEM_ID, SortOrder::SORT_ASC)
            ->addFieldToFilter(
                CartItemInterface::KEY_QUOTE_ID,
                ["eq" => $cartId]
            )->addFieldToFilter('store_id', ['eq' => $scope->getId()]);

        $productIds = [];
        $cartItems = [];
        $productVariations = [];
        $bundles = [];
        foreach ($collection->getItems() as $item) {
            $productId = To::int($item->getData(self::PRODUCT_ID));
            $productIds[] = $productId;

            if ((string)$item->getData('product_type') == 'bundle') {
                $bundles[] = $item->getData(self::ITEM_ID);
                $cartItems[] = $item;
                continue;
            }
            // An item wih non-empty parent ID is variation of a configurable product
            // which should not be listed in the items
            if ($patentItemId = $item->getData(self::PARENT_ITEM_ID)) {
                if (array_contains($bundles, $patentItemId, false)) {
                    // It's not a variant of a configurable product. It's a bundled sub-product.
                    continue;
                }
                $productVariations[To::int($patentItemId)] = $productId;
            } else {
                $cartItems[] = $item;
            }
        }

        $products = $this->productRepository->getByIds($scope, $productIds)->getItems();
        $items = [];
        $totalTax = 0;
        $totalBaseTax = 0;
        foreach ($cartItems as $item) {
            $orttoItem = $this->cartItemFactory->create();
            $productId = To::int($item->getData(self::PRODUCT_ID));

            $product = $products[$productId];
            if ($product == null) {
                $this->logger->warn("Cart product was not loaded", ['product_id' => $productId]);
                continue;
            }

            $orttoItem->setProduct($product);

            $itemId = To::int($item->getData(self::ITEM_ID));
            if (key_exists($itemId, $productVariations)) {
                $variantId = $productVariations[$itemId];
                $orttoItem->setVariant($products[$variantId]);
            }

            $orttoItem->setCreatedAt($this->helper->toUTC($item->getData(self::CREATED_AT)));
            $orttoItem->setUpdatedAt($this->helper->toUTC($item->getData(self::UPDATED_AT)));
            $orttoItem->setDiscount(abs(To::float($item->getData(self::DISCOUNT))));
            $orttoItem->setBaseDiscount(abs(To::float($item->getData(self::BASE_DISCOUNT))));
            $orttoItem->setPrice(To::float($item->getData(self::PRICE)));
            $orttoItem->setBasePrice(To::float($item->getData(self::BASE_PRICE)));
            $orttoItem->setRowTotal(To::float($item->getData(self::ROW_TOTAL)));
            $orttoItem->setBaseRowTotal(To::float($item->getData(self::BASE_ROW_TOTAL)));

            $tax = To::float($item->getData(self::TAX));
            $totalTax += $tax;
            $orttoItem->setTax($tax);

            $baseTax = To::float($item->getData(self::BASE_TAX));
            $totalBaseTax += $baseTax;
            $orttoItem->setBaseTax($baseTax);

            $orttoItem->setTaxPercent(To::float($item->getData(self::TAX_PERCENT)));
            $orttoItem->setQuantity(To::float($item->getData(self::QUANTITY)));

            $items[] = $orttoItem;
        }
        return [
            self::ITEMS => $items,
            self::TAX => $totalTax,
            self::BASE_TAX => $totalBaseTax,
        ];
    }

    /**
     * @param Address $address
     * @return \Ortto\Connector\Api\Data\OrttoAddressInterface
     */
    private function convertQuoteAddress($address)
    {
        $data = $this->addressFactory->create();
        $data->setCity((string)$address->getCity());
        $data->setCompany((string)$address->getCompany());
        $data->setFirstName((string)$address->getFirstname());
        $data->setLastName((string)$address->getLastname());
        $data->setMiddleName((string)$address->getMiddlename());
        $data->setPostCode((string)$address->getPostcode());
        $data->setPrefix((string)$address->getPrefix());
        $data->setSuffix((string)$address->getSuffix());
        $data->setRegion((string)$address->getRegion());
        $data->setVat((string)$address->getVatId());
        $data->setPhone((string)$address->getTelephone());
        $data->setType((string)$address->getAddressType());
        $data->setFax((string)$address->getFax());
        $countryId = (string)$address->getCountry();
        if (!empty($countryId)) {
            $data->setCountryCode($countryId);
            $country = $this->countryFactory->create()->loadByCode($countryId);
            if (!empty($country)) {
                $data->setCountryName((string)$country->getName());
            }
        }
        if ($street = $address->getStreetFull()) {
            $data->setStreetLines(explode("\n", $street));
        }
        return $data;
    }
}
