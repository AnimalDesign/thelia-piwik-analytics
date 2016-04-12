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
    protected $url;
    protected $website_id;

    public function __construct()
    {
        $this->url = ConfigQuery::read('hookpiwikanalytics_url', false);
        $this->website_id = ConfigQuery::read('hookpiwikanalytics_website_id', false);
    }

    /* Include tracking bug
     */
    public function onMainBodyBottom(HookRenderEvent $event)
    {
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
        }

        if ($code = $this->generateTrackingCode($options)) {
            $event->add($code);
        }
    }

    private function generateTrackingCode($options)
    {
        if (!empty($this->url) && is_numeric($this->website_id)) {
            // remove http, https, add // before and / after url
            $this->url = preg_replace('#^https?://#', '', $this->url);
            $this->url = '//'.ltrim($this->url, '//');
            $this->url = rtrim($this->url, '/').'/';
            
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
                    var u="'.$this->url.'";
                    _paq.push([\'setTrackerUrl\', u+\'piwik.php\']);
                    _paq.push([\'setSiteId\', '.$this->website_id.']);
                    var d=document, g=d.createElement(\'script\'), s=d.getElementsByTagName(\'script\')[0];
                    g.type=\'text/javascript\'; g.async=true; g.defer=true; g.src=u+\'piwik.js\'; s.parentNode.insertBefore(g,s);
                })();
            </script>
            <noscript><p><img src="'.$this->url.'piwik.php?idsite='.$this->website_id.'" style="border:0;" alt="" /></p></noscript>
            ';

            return $code;
        }

        return false;
    }
}
