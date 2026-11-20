<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoriaController extends AbstractController
{
    public function __construct(
        private CategoriaRepository $categoria_repository,
    ) {}

    #[Route('/app/categorias', name: 'listar_categorias')]
    public function mostrar(): Response
    {
        return $this->render('categoria/index.html.twig', ['categorias' => $this->categoria_repository->findAll()]);
    }
}
