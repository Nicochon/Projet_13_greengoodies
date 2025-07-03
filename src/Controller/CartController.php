<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    private CartRepository $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @return Response
     */
    #[Route('/cart', name: 'cart_show')]
    public function index(): Response
    {
        $user = $this->getUser();
        $cart = $this->cartRepository->findOneBy([
                'user' => $user,
                'status' => 'draft',
            ]);

        $items = $cart ? $cart->getItems() : [];

        return $this->render('cart/show.html.twig', [
            'items' => $items,
        ]);
    }

    /**
     * @param int $id
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(int $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('L’utilisateur connecté n’est pas une instance de App\Entity\User');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setReference(uniqid('cart_'));
            $cart->setCreatedAt(new \DateTimeImmutable());
            $cart->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($cart);
        }

        $cartItem = null;
        foreach ($cart->getItems() as $item) {
            if ($item->getProduct() === $product) {
                $cartItem = $item;
                break;
            }
        }

        if (!$cartItem) {
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setCart($cart);
            $cartItem->setQuantity(1);
            $em->persist($cartItem);
            $cart->addItem($cartItem);
        } else {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
        }

        $cart->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        $this->addFlash('success', 'Produit ajouté au panier !');

        return $this->redirectToRoute('app_product_show', [
            'id' => $product->getId(),
        ]);
    }

    /**
     * @param CartRepository $cartRepository
     * @param EntityManagerInterface $entityManager
     * @param Security $security
     * @return Response
     */
    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clear(CartRepository $cartRepository, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if ($cart) {
            foreach ($cart->getItems() as $item) {
                $entityManager->remove($item);
            }
            $entityManager->remove($cart);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cart_show');
    }

    /**
     * @param CartRepository $cartRepository
     * @param EntityManagerInterface $em
     * @param Security $security
     * @return Response
     */
    #[Route('/cart/validate', name: 'cart_validate')]
    public function validateCart( CartRepository $cartRepository, EntityManagerInterface $em, Security $security): Response {
        $user = $security->getUser();

        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('L’utilisateur connecté n’est pas une instance de App\Entity\User');
        }

        $cart = $cartRepository->findOneBy(['user' => $user, 'status' => 'draft']);

        if (!$cart) {
            $this->addFlash('warning', 'Aucun panier à valider.');
            return $this->redirectToRoute('cart_show');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setStatus('validated');
        $order->setReference(uniqid('ORD-'));
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setUpdatedAt(new \DateTimeImmutable());

        $totalOrder = 0;
        foreach ($cart->getItems() as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setPurchaseOrder($order);
            $orderItem->setProduct($cartItem->getProduct());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setUnitPrice($cartItem->getProduct()->getPrice());
            $itemTotal = $cartItem->getQuantity() * $orderItem->getUnitPrice();
            $orderItem->setTotal($itemTotal);
            $orderItem->setCreatedAt(new \DateTimeImmutable());
            $orderItem->setUpdatedAt(null);

            $em->persist($orderItem);

            $totalOrder += $itemTotal;
        }

        $order->setTotal($totalOrder);

        foreach ($cart->getItems() as $item) {
            $em->remove($item);
        }
        $em->persist($order);

        $em->remove($cart);

        $em->flush();

        $this->addFlash('success', 'Votre panier a été validé avec succès !');

        return $this->redirectToRoute('cart_show');
    }

}
