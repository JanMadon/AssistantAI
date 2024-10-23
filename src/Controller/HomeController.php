<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(EntityManagerInterface $entityManager): Response
    {
        // $product = new Product();
        // $product->setName('Example Product');
        // $product->setPrice('99.99');
        // $product->setDescription('This is an example product.');
        
        // $entityManager->persist($product);
        // $entityManager->flush();

        return $this->render('dashboard.html.twig');
    }
}
