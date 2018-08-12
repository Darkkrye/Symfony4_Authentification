<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Form\UserType;

class AccountController extends Controller
{
    /**
     * @Route("/mon-espace", name="account")
     * @Method({"GET", "POST"})
     */
    public function account(AuthorizationCheckerInterface $authChecker, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$authChecker->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $this->getUser(), [
            'validation_groups' => array('User'),
        ]);

        $form->handleRequest($request);

        $plainPassword = $user->getPlainPassword();

        if (null !== $plainPassword) {
            $encoded = $encoder->encodePassword($user, $plainPassword);
            $user->setPassword($encoded);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $request->getSession()->getFlashBag()->add('success', 'Votre profil a été modifié');
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }

        return $this->render('account/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
