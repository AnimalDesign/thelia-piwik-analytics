<?php

namespace HookPiwikAnalytics\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ConfigQuery;

/**
 * Class FrontHook
 *
 * @author ANIMAL <studio@animal.at>
 */
class FrontHook extends BaseHook
{
    public function onMainBodyBottom(HookRenderEvent $event)
    {
        $url = ConfigQuery::read('hookpiwikanalytics_url', false);
        $website_id = ConfigQuery::read('hookpiwikanalytics_website_id', false);
        
        if($code = $this->generateTrackingCode($url, $website_id)) {
            $event->add($code);
        }
    }
    
    private function generateTrackingCode($url, $website_id) {
        if(!empty($url) && is_numeric($website_id)) {
            // remove http, https, add // before and / after url
            $url = preg_replace('#^https?://#', '', $url);
            $url = '//'.ltrim($url, '//');
            $url = rtrim($url, '/') . '/';
            
            $code = '
            <!-- Piwik -->
            <script type="text/javascript">
              var _paq = _paq || [];
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
            <!-- End Piwik Code -->
            ';

            return $code;
        }
        
        return false;
    }
}
