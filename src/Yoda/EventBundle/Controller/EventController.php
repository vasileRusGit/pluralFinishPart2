<?php

namespace Yoda\EventBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Yoda\EventBundle\Entity\Event;
use Yoda\EventBundle\Form\EventType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Event controller.
 *
 */
class EventController extends Controller {

    /**
     * Lists all Event entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $events = $em->getRepository('EventBundle:Event')->findAll();
        return $this->render('event/index.html.twig', array(
                    'events' => $events,
        ));
    }

    /**
     * Creates a new Event event.
     *
     */
    public function newAction(Request $request) {
        $this->enforceUserSecurity();
        $event = new Event();
        $form = $this->createForm(new EventType(), $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute('event_show', array('id' => $event->getId()));
        }
        return $this->render('event/new.html.twig', array(
                    'event' => $event,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Event entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $event = $em->getRepository('EventBundle:Event')->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('event/show.html.twig', array(
                    'event' => $event,
                    'delete_form' => $deleteForm->createView(),));
    }

    /**
     * Displays a form to edit an existing Event event.
     *
     */
    public function editAction($id) {
        $this->enforceUserSecurity();
        $em = $this->getDoctrine()->getManager();

        $event = $em->getRepository('EventBundle:Event')->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Unable to find Event event.');
        }

        $editForm = $this->createEditForm($event);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('event/edit.html.twig', array(
                    'event' => $event,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Event entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $this->enforceUserSecurity();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EventBundle:Event')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('event_edit', array('id' => $id)));
        }

        return $this->render('EventBundle:Event:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Event event.
     *
     * @param Event $event The event
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Event $event) {
        $form = $this->createForm(new EventType(), $event, array(
            'action' => $this->generateUrl('event_update', array('id' => $event->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Deletes a Event event.
     *
     */
    public function deleteAction(Request $request, $id) {
        $this->enforceUserSecurity();
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $event = $em->getRepository('EventBundle:Event')->find($id);

            if (!$event) {
                throw $this->createNotFoundException('Unable to find Event event.');
            }

            $em->remove($event);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('event_index'));
    }

    /**
     * Creates a form to delete a Event event by id.
     *
     * @param mixed $id The event id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('event_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    public function enforceAdminUserSecurity() {
        $securityContext = $this->get('security.context');
        if (!$securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Need ROLE_ADMIN!');
        }
    }

    public function enforceUserSecurity() {
        $securityContext = $this->get('security.context');
        if (!$securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('Need ROLE_USER!');
        }
    }

}
