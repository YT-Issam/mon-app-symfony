<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProductController extends AbstractController
{


    #[Route('/admin/product', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/admin/product/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $filesystem = new Filesystem();
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/products';

                if (!$filesystem->exists($uploadDir)) {
                    $filesystem->mkdir($uploadDir);
                }

                $filename = uniqid('product_', true) . '.' . $imageFile->guessExtension();
                $imageFile->move($uploadDir, $filename);

                $product->setImageFilename($filename);
            }

            $product->setCreatedAt(new \DateTime());
            $product->setUpdatedAt(new \DateTime());

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/product/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $filesystem = new Filesystem();
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/products';

                if (!$filesystem->exists($uploadDir)) {
                    $filesystem->mkdir($uploadDir);
                }

                $oldFilename = $product->getImageFilename();
                if ($oldFilename) {
                    $filesystem->remove($uploadDir . '/' . $oldFilename);
                }

                $filename = uniqid('product_', true) . '.' . $imageFile->guessExtension();
                $imageFile->move($uploadDir, $filename);

                $product->setImageFilename($filename);
            }

            $product->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/admin/product/{id}/delete', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $filesystem = new Filesystem();
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/products';

            if ($product->getImageFilename()) {
                $filesystem->remove($uploadDir . '/' . $product->getImageFilename());
            }

            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index');
    }


    #[Route('/product/{id}', name: 'product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }


    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(ProductRepository $productRepository): Response
    {
        return $this->render('home.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }
}
