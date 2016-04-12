<?php

namespace HookPiwikAnalytics\EventListeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Model\ConfigQuery;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Model\CategoryQuery;
use Thelia\Model\ProductQuery;

class CartEventListener implements EventSubscriberInterface
{
    protected $url;
    protected $website_id;
    protected $tracker;

    public function __construct()
    {
        $this->url = ConfigQuery::read('hookpiwikanalytics_url', false);
        $this->website_id = ConfigQuery::read('hookpiwikanalytics_website_id', false);

        \PiwikTracker::$URL = $this->url;
        $this->tracker = new \PiwikTracker($this->website_id);
    }

    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::CART_ADDITEM => ['trackCart'],
            TheliaEvents::CART_UPDATEITEM => ['trackCart'],
            TheliaEvents::CART_DELETEITEM => ['trackCart'],

            TheliaEvents::ORDER_PAY => ['trackOrder'],
        );
    }

    public function trackCart(CartEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $cart = $event->getCart();

        foreach ($cart->getCartItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $defaultCategory = CategoryQuery::create()->findPk($product->getDefaultCategoryId());

            $this->tracker->addEcommerceItem(
                $product->getRef() ? $product->getRef() : $product->getId(),
                $product->getTitle(),
                $defaultCategory->getTitle(),
                $cartItem->getRealPrice(),
                $cartItem->getQuantity()
            );
        }

        $this->tracker->doTrackEcommerceCartUpdate($cart->getTotalAmount());
    }

    public function trackOrder(OrderEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $order = $event->getPlacedOrder();
        $taxTotal = 0;

        foreach ($order->getOrderProducts() as $orderProduct) {
            $product = ProductQuery::create()->findPk($orderProduct->getVirtualColumn('product_id'));
            $defaultCategory = CategoryQuery::create()->findPk($product->getDefaultCategoryId());

            $taxTotal += $orderProduct->getVirtualColumn('TOTAL_TAX');
            $this->tracker->addEcommerceItem(
                $orderProduct->getProductSaleElementsRef() || $orderProduct->getProductRef() || $orderProduct->getId() || $orderProduct->getProductSaleElementsId(),
                $orderProduct->getTitle(),
                $defaultCategory->getTitle(),
                $orderProduct->getPrice(),
                $orderProduct->getQuantity()
            );
        }

        $this->tracker->doTrackEcommerceOrder(
            $order->getRef(),
            $order->getTotalAmount($taxTotal, true, true),
            $order->getTotalAmount($taxTotal, false, true),
            $taxTotal,
            $order->getPostage() + $order->getPostageTax(),
            $order->getDiscount()
        );
    }
}
