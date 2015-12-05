<?php

namespace HookPiwikAnalytics\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\ConfigQuery;

/**
 * Class Configuration
 *
 * @author ANIMAL <studio@animal.at>
 */
class Configuration extends BaseAdminController
{
    public function saveAction()
    {
        if ($response = $this->checkAuth(array(AdminResources::MODULE), array('hookpiwikanalytics'), AccessManager::UPDATE) !== null) {
            return $response;
        }

        $form = new \HookPiwikAnalytics\Form\Configuration($this->getRequest());
        $resp = array(
            'error' => 0,
            'message' => '',
        );
        $response = null;

        try {
            $vform = $this->validateForm($form);
            $data = $vform->getData();

            ConfigQuery::write('hookpiwikanalytics_url', $data['hookpiwikanalytics_url'], false, true);
            ConfigQuery::write('hookpiwikanalytics_website_id', $data['hookpiwikanalytics_website_id'], false, true);
            ConfigQuery::write('hookpiwikanalytics_enable_subdomains', ($data['hookpiwikanalytics_enable_subdomains'] === 'on' ? true : false), false, true);
            ConfigQuery::write('hookpiwikanalytics_custom_campaign_name', $data['hookpiwikanalytics_custom_campaign_name'], false, true);
            ConfigQuery::write('hookpiwikanalytics_custom_campaign_keyword', $data['hookpiwikanalytics_custom_campaign_keyword'], false, true);
        } catch (\Exception $e) {
            $resp['error'] = 1;
            $resp['message'] = $e->getMessage();
        }

        return JsonResponse::create($resp);
    }
}
