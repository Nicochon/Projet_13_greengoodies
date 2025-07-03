<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccountController extends AbstractController
{
    /**
     * @param OrderRepository $orderRepository
     * @param Security $security
     * @return Response
     */
    #[Route('/account', name: 'app_account')]
    public function index(OrderRepository $orderRepository, Security $security): Response
    {

        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos commandes.');
        }

        $orders = $orderRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('account/orders.html.twig', [
            'orders' => $orders,
            'user' => $user,
        ]);
    }

    /**
     * @param EntityManagerInterface $em
     * @param Security $security
     * @return RedirectResponse
     */
    #[Route('/mon-compte/api/toggle', name: 'toggle_api_access')]
    public function toggleApiAccess(EntityManagerInterface $em, Security $security): RedirectResponse {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $user->setApiEnabled(!$user->isApiEnabled());

        $em->persist($user);
        $em->flush();

        // Redirection vers la page de compte
        return $this->redirectToRoute('app_account');
    }
}
