<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdditionalInfo extends Module
{
    public function __construct()
    {
        $this->name = 'additionalinfo';
        $this->tab = 'front_office_features';
        $this->version = '0.0.1';
        $this->author = 'Michota';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Dodatkowe informacje');
        $this->description = $this->l('Moduł do dodawania niestandardowych informacji na stronie produktu.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        // Dodaj pole konfiguracyjne
        $this->registerConfigurationKeys();
    }

    public function install()
    {
        if (
            parent::install() &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerHook('displayAdminForm')
        ) {
            return true;
        }

        return false;
    }

    public function uninstall()
    {
        if (parent::uninstall() && Configuration::deleteByName('ADDITIONAL_INFO_TEXT')) {
            return true;
        }

        return false;
    }

    public function registerConfigurationKeys()
    {
        $configKeys = array(
            'ADDITIONAL_INFO_TEXT' => 'Domyślny tekst',
        );

        foreach ($configKeys as $key => $value) {
            if (!Configuration::get($key)) {
                Configuration::updateValue($key, $value);
            }
        }
    }

    public function hookDisplayAdminForm()
    {
        if (Tools::isSubmit('submit'.$this->name)) {
            $customText = Tools::getValue('ADDITIONAL_INFO_TEXT');
            Configuration::updateValue('ADDITIONAL_INFO_TEXT', $customText);
        }

        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Konfiguracja dodatkowych informacji'),
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Tekst do wyświetlenia'),
                        'name' => 'ADDITIONAL_INFO_TEXT',
                        'lang' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Zapisz'),
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper->generateForm(array($fieldsForm));
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        $customText = Configuration::get('ADDITIONAL_INFO_TEXT');

        if (!empty($customText)) {
            $this->context->smarty->assign('custom_text', $customText);
            return $this->display(__FILE__, 'views/templates/hook/additionalinfo.tpl');
        }

        return '';
    }
}
