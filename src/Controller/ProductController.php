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
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/product/new', name: 'app_product_new', methods: ['GET', 'POST'])]
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
            'form' => $form->createView()
        ]);
    }

    #[Route('/product/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
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
                    $oldFilePath = $uploadDir . '/' . $oldFilename;
                    if ($filesystem->exists($oldFilePath)) {
                        $filesystem->remove($oldFilePath);
                    }
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
            'product' => $product
        ]);
    }

    #[Route('/product/{id}/delete', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $filesystem = new Filesystem();
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/products';

            $filename = $product->getImageFilename();
            if ($filename) {
                $filePath = $uploadDir . '/' . $filename;
                if ($filesystem->exists($filePath)) {
                    $filesystem->remove($filePath);
                }
            }

            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index');
    }

    #[Route('/product/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }
}

