<?php

namespace HookPiwikAnalytics\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ConfigQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductCategoryQuery;
use Thelia\Model\CategoryQuery;
use Thelia\Model\OrderQuery;

/**
 * Class FrontHook.
 *
 * @author ANIMAL <studio@animal.at>
 */
class FrontHook extends BaseHook
{
    /* Include tracking bug
     */
    public function onMainBodyBottom(HookRenderEvent $event)
    {
        $url = ConfigQuery::read('hookpiwikanalytics_url', false);
        $website_id = ConfigQuery::read('hookpiwikanalytics_website_id', false);
        $options = array();

        switch ($this->getRequest()->get('_view')) {
            // Category page viewed
            case 'category':
                $categoryId = $this->getRequest()->get('category_id');

                $defaultCategory = CategoryQuery::create()
                    ->findPk($categoryId);

                $options[] = array(
                    'setEcommerceView',
                    false,
                    false,
                    $defaultCategory->getTitle(), // Category
                );
                break;
            
            // Product detail page viewed
            case 'product':
                $productId = $this->getRequest()->getProductId();
                $product = ProductQuery::create()
                    ->findPk($productId);
                
                if($defaultCategoryId = $product->getDefaultCategoryId()) {
                    $defaultCategory = CategoryQuery::create()
                        ->findPk($defaultCategoryId);
                }
                
                $options[] = array(
                    'setEcommerceView',
                    ($product->getRef() ? $product->getRef() : $product->getId()), // SKU or ID
                    $product->getTitle(), // Name
                    (isset($defaultCategory) ? $defaultCategory->getTitle() : false), // Default product category
                    false, // Price
                );
                break;
            
            // Order confirmation page
            case 'order-placed':
                $orderId = $this->getRequest()->get('order_id');
                $order = OrderQuery::create()
                    ->findPk($orderId);
                
                foreach ($order->getOrderProducts() as $orderProduct) {
                    $options[] = array(
                        'addEcommerceItem',
                        ($orderProduct->getProductRef() ? $orderProduct->getProductRef() : $orderProduct->getId()), // SKU or ID
                        $orderProduct->getTitle(), // Product name
                        false, // Default product category
                        floatval($orderProduct->getPrice()), // Product price
                        $orderProduct->getQuantity() // Product quantity
                    );
                }    
                
                $options[] = array(
                    'trackEcommerceOrder',
                    $order->getRef(), // Unique Order ID
                    floatval($order->getTotalAmount($tax, true, true)), // Order Revenue grand total (includes tax, shipping, and subtracted discount)
                    floatval($order->getTotalAmount($tax, false, true)), // Order sub total (excludes shipping)
                    floatval($tax), // Tax amount
                    floatval($order->getPostageTax()), // Shipping amount
                    (floatval($order->getDiscount()) == 0 ? false : floatval($order->getDiscount())) // Discount offered
                );
                break;
        }

        if ($code = $this->generateTrackingCode($url, $website_id, $options)) {
            $event->add($code);
        }
    }

    private function generateTrackingCode($url, $website_id, $options)
    {
        if (!empty($url) && is_numeric($website_id)) {
            // remove http, https, add // before and / after url
            $url = preg_replace('#^https?://#', '', $url);
            $url = '//'.ltrim($url, '//');
            $url = rtrim($url, '/').'/';
            
            if((bool)ConfigQuery::read('hookpiwikanalytics_enable_subdomains', false)) {
                // Get host w/o www or subdomain, see http://snipplr.com/view/61235/
                preg_match("/[^\.\/]+\.[^\.\/]+$/", $_SERVER['HTTP_HOST'], $matches);

                $options[] = array(
                    'setCookieDomain',
                    '*.'.$matches[0]
                );
            }

            if(!empty(trim(ConfigQuery::read('hookpiwikanalytics_custom_campaign_name', '')))) {
                $options[] = array(
                    'setCampaignNameKey',
                    ConfigQuery::read('hookpiwikanalytics_custom_campaign_name')
                );
            }
            
            if(!empty(trim(ConfigQuery::read('hookpiwikanalytics_custom_campaign_keyword', '')))) {
                $options[] = array(
                    'setCampaignKeywordKey',
                    ConfigQuery::read('hookpiwikanalytics_custom_campaign_keyword')
                );
            }

            $code = '
            <script type="text/javascript">
                var _paq = _paq || [];';
            
            foreach ($options as $option) {
                $code .= '
                _paq.push('.json_encode($option).');';
            }
            
            $code .= '
                _paq.push([\'trackPageView\']);
                _paq.push([\'enableLinkTracking\']);
                (function() {
                    var u="'.$url.'";
                    _paq.push([\'setTrackerUrl\', u+\'piwik.php\']);
                    _paq.push([\'setSiteId\', '.$website_id.']);
                    var d=document, g=d.createElement(\'script\'), s=d.getElementsByTagName(\'script\')[0];
                    g.type=\'text/javascript\'; g.async=true; g.defer=true; g.src=u+\'piwik.js\'; s.parentNode.insertBefore(g,s);
                })();
            </script>
            <noscript><p><img src="'.$url.'piwik.php?idsite='.$website_id.'" style="border:0;" alt="" /></p></noscript>
            ';

            return $code;
        }

        return false;
    }
}
