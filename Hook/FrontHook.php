<?php

namespace HookPiwikAnalytics\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ConfigQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductCategoryQuery;
use Thelia\Model\CategoryQuery;

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
            case 'category':
                $categoryId = $this->getRequest()->get('category_id');

                $defaultCategory = CategoryQuery::create()
                    ->findPk($categoryId);

                $options[] = array(
                    'setEcommerceView',
                    false, // SKU or ID
                    false, // Name
                    $defaultCategory->getTitle(), // Category
                );
                break;
            
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
                    (isset($defaultCategory) ? $defaultCategory->getTitle() : false), // Category
                    false, // Price
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

            $code = '
            <script type="text/javascript">
                var _paq = _paq || [];';
            
            foreach ($options as $option) {
                $code .= '
                    _paq.push('.json_encode($option).');
                ';
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
