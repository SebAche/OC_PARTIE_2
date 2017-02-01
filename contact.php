<?php
namespace ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends Controller
{
    public function indexAction(Request $request)
    {
        $sujetManager = $this->get('contact.manager.sujet');
        $listSujet = $sujetManager->getListSujet();
        $currentActivite = $this->get('common.manager.activite')->getCurrentActivite();
        $defaultData = array('activite' => $currentActivite);
        if (count($listSujet) === 1) {
            $defaultData['sujet'] = $listSujet[0];
        }

        $defaultOptions = array();

        $data = $this->getContactFormData($defaultData);
        $options = $this->getContactFormOptions($defaultOptions);

        $form = $this->createForm($this->get('contact.form.type.contact'), $data, $options);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $this->formatageData($form->getData());
            $this->get('contact.envoiMail')->sendContactMessage($formData);
            $request->getSession()->getFlashBag()->add('info', $this->get('translator')->trans('messages.email_succes', array(), 'ContactBundle'));

            return $this->redirect($this->generateUrl('formulaire_contact'));
        }

        return $this->view($form);
    }

    protected function getContactFormData($defaultData)
    {
        return $defaultData;
    }

    protected function getContactFormOptions($defaultOptions)
    {
        return $defaultOptions;
    }

    protected function formatageData($dataFormulaire)
    {

        $formatedData = array();

        $formatedData['expediteur']['mail'] =$dataFormulaire['email'];
        $formatedData['sujet'] = $dataFormulaire['sujet'];
        $formatedData['message'] = $dataFormulaire['message'];
        if (isset($dataFormulaire['documentContactForm'])){
            $formatedData['uploadedFile'] = $dataFormulaire['documentContactForm'];
        }

        return $formatedData;
    }

    protected function view($form)
    {
        return $this->render('ContactBundle:Contact:formulaireContact.html.twig', array(
            'form' =>$form->createView()
        ));
    }
}
