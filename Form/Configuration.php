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
                    'data' => ConfigQuery::read('hookpiwikanalytics_website_id', ''),
                    'label' => $this->translator->trans('Website ID'),
                    'label_attr' => array(
                        'for' => 'hookpiwikanalytics_website_id',
                    ),
                )
            )
            ->add(
                'hookpiwikanalytics_enable_ecommerce',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (bool)ConfigQuery::read('hookpiwikanalytics_enable_ecommerce', ''),
                    'label' => $this->translator->trans('Enable e-commerce tracking'),
                    'label_attr' => array(
                        'for' => 'hookpiwikanalytics_enable_ecommerce',
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
