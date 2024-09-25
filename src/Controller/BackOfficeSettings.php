<?php
declare(strict_types = 1);

namespace CustomerDNI\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

/**
 * Handles back office settings page.
 */
class BackOfficeSettings extends FrameworkBundleAdminController
{
    public function index(Request $request): Response
    {
        $formHandler = $this->get('customer_dni.form.settings_form_handler');
        $configurationForm = $formHandler->getForm();
        $configurationForm->handleRequest($request);

        if ($configurationForm->isSubmitted() && $configurationForm->isValid()) {
            $formErrors = $formHandler->save($configurationForm->getData());

            if (empty($formErrors)) {
                $this->addFlash('success', $this->trans('Settings updated successfully.', 'Admin.Notifications.Success'));

                return $this->redirectToRoute('customer_dni_settings');
            }

            foreach ($formErrors as $key => $error) {
                $configurationForm->get($key)->addError(new FormError($error));
            }

            $this->flashErrors([$this->trans('An error occurred while updating settings. Please review the form.', 'Admin.Notifications.Error')]);
        }

        return $this->render('@Modules/customer_dni/views/templates/admin/settings.html.twig', [
            'SettingsForm'  => $configurationForm->createView(),
            'layoutTitle'   => $this->trans('Customer DNI module settings', 'Modules.Customerdni.Admin'),
            'enableSidebar' => true,
            'help_link'     => $this->generateSidebarLink('BackOfficeSettings'),
        ]);
    }
}