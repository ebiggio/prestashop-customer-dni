<?php
declare(strict_types = 1);

namespace CustomerDNI\Form;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShopBundle\Form\Admin\Type\SwitchType;

class SettingsFormType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer_dni_display', SwitchType::class, [
                'label'    => $this->trans('Display DNI field', 'Modules.CustomerDNI.Admin'),
                'help'     => $this->trans('If enabled, the DNI field will be displayed in the customer registration and personal information forms.', 'Modules.CustomerDNI.Admin'),
                'required' => false])
            ->add('customer_dni_required', SwitchType::class, [
                'label'    => $this->trans('Require DNI', 'Modules.CustomerDNI.Admin'),
                'help'     => $this->trans('If enabled, the DNI field will be a required field when creating or editing a customer.', 'Modules.CustomerDNI.Admin'),
                'required' => false])
            ->add('customer_dni_unique', SwitchType::class, [
                'label'    => $this->trans('Unique DNI', 'Modules.CustomerDNI.Admin'),
                'help'     => $this->trans('If enabled, the DNI field will be marked as unique, meaning that two customers cannot have the same DNI.', 'Modules.CustomerDNI.Admin'),
                'required' => false])
            ->add('customer_dni_overwrite_address_dni', SwitchType::class, [
                'label'    => $this->trans('Overwrite address DNI', 'Modules.CustomerDNI.Admin'),
                'help'     => $this->trans('If enabled, the customer DNI will be copied to the address DNI field for each of the customer\'s addresses. Keep in mind that the address DNI field has a maximum length of 16 characters, so any value longer than that will be truncated.', 'Modules.CustomerDNI.Admin'),
                'required' => false])
            ->add('customer_dni_regexp', TextType::class, [
                'label'    => $this->trans('Regular expression to perform validation', 'Modules.CustomerDNI.Admin'),
                'help'     => $this->trans('If not empty, the DNI field will be validated against this regular expression.', 'Modules.CustomerDNI.Admin'),
                'required' => false])
            ->add('customer_dni_custom_validators', SwitchType::class, [
                'label'    => $this->trans('Use custom validators', 'Modules.CustomerDNI.Admin'),
                'help'     => $this->trans('If enabled, additional validations will be performed on the DNI field, based on the PHP classes inside the "validations" folders. This is a feature intended to be used by developers; if you\'re not sure about the contents of the scripts inside the "validations" folder, please disable this option.', 'Modules.CustomerDNI.Admin'),
                'required' => false]);
    }
}