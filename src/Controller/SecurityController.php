<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Form\UserType;
use App\Entity\User;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connexion", name="connexion")
     */
    public function login(Request $request, AuthenticationUtils $authUtils)
    {
        // Get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        echo($error);
        //die();

        // Last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
          'last_username' => $lastUsername,
          'error'         => $error
        ));
    }

    /**
     * @Route("/inscription", name="user_registration")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        // création du formulaire
        $user = new User();
        // instancie le formulaire avec les contraintes par défaut, + la contrainte registration pour que la saisie du mot de passe soit obligatoire
        $form = $this->createForm(UserType::class, $user,[
           'validation_groups' => array('User', 'registration'),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Encode le mot de passe
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // Enregistre le membre en base
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('connexion');
        }

        return $this->render(
            'security/register.html.twig',
            array('form' => $form->createView())
        );
    }
}
