<?php

namespace HookPiwikAnalytics\Form;

use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\ConfigQuery;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class Configuration.
 *
 * @author ANIMAL <studio@animal.at>
 */
class Configuration extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'hookpiwikanalytics_url',
                'text',
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                    'data' => ConfigQuery::read('hookpiwikanalytics_url', ''),
                    'label' => $this->translator->trans('Piwik URL'),
                    'label_attr' => array(
                        'for' => 'hookpiwikanalytics_url',
                    ),
                )
            )
            ->add(
                'hookpiwikanalytics_website_id',
                'number',
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                    'data' => ConfigQuery::read('hookpiwikanalytics_website_id', 0),
                    'label' => $this->translator->trans('Website ID'),
                    'label_attr' => array(
                        'for' => 'hookpiwikanalytics_website_id',
                    ),
                )
            )
            ->add(
                'hookpiwikanalytics_enable_subdomains',
                'checkbox',
                array(
                    'required' => false,
                    'value' => (bool)ConfigQuery::read('hookpiwikanalytics_enable_subdomains', false),
                    'label' => $this->translator->trans('Enable tracking across subdomains'),
                    'label_attr' => array(
                        'for' => 'hookpiwikanalytics_enable_subdomains',
                    ),
                )
            )
            ->add(
                'hookpiwikanalytics_custom_campaign_name',
                'text',
                array(
                    'required' => false,
                    'data' => ConfigQuery::read('hookpiwikanalytics_custom_campaign_name', ''),
                    'label' => $this->translator->trans('Custom campaign name parameter'),
                    'label_attr' => array(
                        'for' => 'hookpiwikanalytics_custom_campaign_name',
                    ),
                )
            )
            ->add(
                'hookpiwikanalytics_custom_campaign_keyword',
                'text',
                array(
                    'required' => false,
                    'data' => ConfigQuery::read('hookpiwikanalytics_custom_campaign_keyword', ''),
                    'label' => $this->translator->trans('Custom campaign keyword parameter'),
                    'label_attr' => array(
                        'for' => 'hookpiwikanalytics_custom_campaign_keyword',
                    ),
                )
            );            
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return 'hookpiwikanalytics';
    }
}
