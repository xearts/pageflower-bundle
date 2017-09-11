<?php
/*
 * Copyright (c) 2015 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of PHPMentorsPageflowerBundle.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace PHPMentors\PageflowerBundle\Controller\Bundle\TestBundle\Controller;

use PHPMentors\PageflowerBundle\Annotation\Accept;
use PHPMentors\PageflowerBundle\Annotation\EndPage;
use PHPMentors\PageflowerBundle\Annotation\Init;
use PHPMentors\PageflowerBundle\Annotation\Page;
use PHPMentors\PageflowerBundle\Annotation\Pageflow;
use PHPMentors\PageflowerBundle\Annotation\StartPage;
use PHPMentors\PageflowerBundle\Annotation\Stateful;
use PHPMentors\PageflowerBundle\Annotation\Transition;
use PHPMentors\PageflowerBundle\Controller\Bundle\TestBundle\Entity\User;
use PHPMentors\PageflowerBundle\Controller\Bundle\TestBundle\Form\Type\UserRegistrationType;
use PHPMentors\PageflowerBundle\Controller\ConversationalControllerInterface;
use PHPMentors\PageflowerBundle\Conversation\ConversationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @since Class available since Release 1.1.0
 *
 * @Pageflow({
 *     @StartPage({"input",
 *         @Transition("confirmation"),
 *     }),
 *     @Page({"confirmation",
 *         @Transition("success"),
 *         @Transition("input")
 *     }),
 *     @EndPage("success")
 * })
 */
class UserRegistrationController extends Controller implements ConversationalControllerInterface
{
    const VIEW_INPUT = 'TestBundle:UserRegistration:input.html.twig';
    const VIEW_CONFIRMATION = 'TestBundle:UserRegistration:confirmation.html.twig';
    const VIEW_SUCCESS = 'TestBundle:UserRegistration:success.html.twig';

    /**
     * @var ConversationContext
     */
    private $conversationContext;

    /**
     * @var User
     *
     * @Stateful
     */
    private $user;

    /**
     * @var FormInterface
     *
     * @Stateful
     */
    private $form;

    /**
     * {@inheritDoc}
     */
    public function setConversationContext(ConversationContext $conversationContext)
    {
        $this->conversationContext = $conversationContext;
    }

    /**
     * @Init
     */
    public function initialize()
    {
        $this->user = new User();
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Accept("input")
     * @Accept("confirmation")
     */
    public function inputGetAction(Request $request)
    {
        if ($this->conversationContext->getCurrentPage()->getPageId() == 'confirmation') {
            $this->conversationContext->getConversation()->transition('input');
        }

        $form = $this->createForm(UserRegistrationType::class, $this->user, array(
            'action' => $this->generateUrl('test_user_registration_input_post'),
            'method' => 'POST',
        ));

        return $this->render(self::VIEW_INPUT, array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     *
     * @Accept("input")
     */
    public function inputPostAction(Request $request)
    {
        $form = $this->createForm(UserRegistrationType::class, $this->user, array(
            'action' => $this->generateUrl('test_user_registration_input_post'),
            'method' => 'POST',
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->conversationContext->getConversation()->transition('confirmation');

            return $this->redirect($this->conversationContext->generateUrl('test_user_registration_confirmation_get'));
        } else {
            $this->form = $form;

            return $this->redirect($this->conversationContext->generateUrl('test_user_registration_input_get'));
        }
    }

    /**
     * @param Request $request
     *
     * @Accept("confirmation")
     */
    public function confirmationGetAction(Request $request)
    {
        $form = $this->createFormBuilder(null, array('action' => $this->generateUrl('test_user_registration_confirmation_post'), 'method' => 'POST'))
            ->add('next', SubmitType::class)
            ->add('prev', SubmitType::class)
            ->getForm();

        return $this->render(self::VIEW_CONFIRMATION, array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     *
     * @Accept("confirmation")
     */
    public function confirmationPostAction(Request $request)
    {
        $form = $this->createFormBuilder(null, array('action' => $this->generateUrl('test_user_registration_confirmation_post'), 'method' => 'POST'))
            ->add('next', SubmitType::class)
            ->add('prev', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('next')->isClicked()) {
                $this->conversationContext->getConversation()->transition('success');

                return $this->redirect($this->conversationContext->generateUrl('test_user_registration_success_get'));
            } elseif ($form->get('prev')->isClicked()) {
                $this->conversationContext->getConversation()->transition('input');

                return $this->redirect($this->conversationContext->generateUrl('test_user_registration_input_get'));
            }
        } else {
            $this->form = $form;

            return $this->redirect($this->conversationContext->generateUrl('test_user_registration_confirmation_get'));
        }
    }

    /**
     * @param Request $request
     *
     * @Accept("success")
     */
    public function successGetAction(Request $request)
    {
        return $this->render(self::VIEW_SUCCESS);
    }
}
