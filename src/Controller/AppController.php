<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    #[Route('/app', name: 'app_app')]
    public function index(CategoriaRepository $categoriaRepository): Response
    {
        return $this->render('app/index.html.twig', [
            'totalCategorias' => $categoriaRepository->count([]),
        ]);
    }
}
