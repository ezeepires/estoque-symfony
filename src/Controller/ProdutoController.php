<?php

namespace App\Controller;

use App\Entity\Produto;
use App\Form\ProdutoType;
use App\Repository\ProdutoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/app/produtos')]
final class ProdutoController extends AbstractController
{
    #[Route(name: 'app_produto_index', methods: ['GET'])]
    public function index(ProdutoRepository $produtoRepository): Response
    {
        return $this->render('produto/index.html.twig', [
            'produtos' => $produtoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_produto_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produto = new Produto();
        $form = $this->createForm(ProdutoType::class, $produto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produto);
            $entityManager->flush();

            return $this->redirectToRoute('app_produto_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produto/new.html.twig', [
            'produto' => $produto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produto_show', methods: ['GET'])]
    public function show(Produto $produto): Response
    {
        return $this->render('produto/show.html.twig', [
            'produto' => $produto,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produto_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produto $produto, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProdutoType::class, $produto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_produto_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produto/edit.html.twig', [
            'produto' => $produto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produto_delete', methods: ['POST'])]
    public function delete(Request $request, Produto $produto, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produto->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($produto);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produto_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/adicionar', name: 'app_produto_adicionar', methods: ['POST'])]
    public function adicionar(Request $request, Produto $produto, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid(
            'adicionar'.$produto->getId(),
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException();
        }

        $produto->setQuantidade(($produto->getQuantidade() ?? 0) + 1);
        $entityManager->flush();

        return $this->redirectToRoute('app_produto_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/vender', name: 'app_produto_vender', methods: ['POST'])]
    public function vender(Request $request, Produto $produto, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid(
            'vender'.$produto->getId(),
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException();
        }

        $quantidade = $produto->getQuantidade() ?? 0;

        if ($quantidade > 0) {
            $produto->setQuantidade($quantidade - 1);
            $entityManager->flush();
        } else {
            $this->addFlash('danger', 'Não há unidades disponíveis para remover.');
        }

        return $this->redirectToRoute('app_produto_index', [], Response::HTTP_SEE_OTHER);
    }
}
