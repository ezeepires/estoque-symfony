<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Form\CategoriaType;
use App\Repository\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoriaController extends AbstractController
{
    public function __construct(
        private CategoriaRepository $categoriaRepository,
    ) {}

    #[Route('/app/categorias', name: 'listar_categorias', methods: ['GET', 'POST'])]
    public function mostrar(Request $request, EntityManagerInterface $em): Response
    {
        $categoria = new Categoria();
        $form = $this->createForm(CategoriaType::class, $categoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categoria);
            $em->flush();
            $this->addFlash('success', 'Categoria cadastrada!');

            return $this->redirectToRoute('listar_categorias');
        }

        return $this->render('categoria/index.html.twig', [
            'categorias' => $this->categoriaRepository->findAll(),
            'form' => $form,
        ]);
    }

    #[Route('/app/categorias/{id}/remover', name: 'remover_categoria', methods: ['POST'])]
    public function remover(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $categoria = $this->categoriaRepository->find($id);

        if (!$categoria) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid(
            'remover_categoria'.$categoria->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($categoria);
        $em->flush();

        $this->addFlash('success', 'Categoria removida!');

        return $this->redirectToRoute('listar_categorias');
    }

    #[Route(
        '/app/categorias/{id}',
        name: 'editar_categoria',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    public function editar(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $categoria = $this->categoriaRepository->find($id);

        if (!$categoria) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(CategoriaType::class, $categoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Categoria atualizada!');

            return $this->redirectToRoute('listar_categorias');
        }

        return $this->render('categoria/index.html.twig', [
            'categorias' => $this->categoriaRepository->findAll(),
            'form' => $form,
            'editando' => true,
        ]);
    }
    
}
