<?php

namespace App\Controller;

use App\Entity\Tricks;
use App\Form\TricksType;
use App\Repository\TricksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\Exception\FileException; 
use App\Entity\Commentaires;
use App\Form\CommentairesType;
use App\Repository\CommentairesRepository;


/**
 * @Route("/tricks")
 */
class TricksController extends AbstractController
{
    /**
     * @Route("/", name="tricks_index", methods="GET")
     */
    public function index(TricksRepository $tricksRepository): Response
    {
        return $this->render('tricks/index.html.twig', ['tricks' => $tricksRepository->findAll()]);
    }

    /**
     * @Route("/connexion", name="tricks_connexion", methods="GET")
     */
    public function connexion(): Response
    {
        return $this->render('connexion.html.twig');
    }

    /**
     * @Route("/inscription", name="tricks_inscription", methods="GET")
     */
    public function inscription(): Response
    {
        return $this->render('inscription.html.twig');
    }

    /**
     * @Route("/new", name="tricks_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        
        $trick = new Tricks();
        $form = $this->createForm(TricksType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $trick->getImage();
            /*$fileName = $fileUploader->upload($file);
            $trick->setImage($fileName);*/
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            // Move the file to the directory where images are stored
            try {
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
                $trick->setImage($fileName);
                $em = $this->getDoctrine()->getManager();
                $trick->addAuteurId($this->getUser());

            $em->persist($trick);
            $em->flush();
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'images' property to store the PDF file name
            // instead of its contents
            

            // ... persist the $product variable or any other work

            /*return $this->redirect($this->generateUrl('app_product_list'));
            ;*/

            return $this->redirectToRoute('tricks_index');
        }

        return $this->render('tricks/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * @Route("/{id}", name="tricks_show", methods={"GET", "POST"})
     */
    public function show(Tricks $trick, Request $request): Response
    {
        $commentaires = new Commentaires();
        $form = $this->createForm(CommentairesType::class, $commentaires);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $date = new \DateTime();
            $commentaires->setDateCommentaire($date->format("d-m-Y h:i"));
            $trick->addCommentaire($commentaires);
            $em = $this->getDoctrine()->getManager();
            $em->persist($trick);
            $em->flush();
        }
        return $this->render('tricks/show.html.twig', ['trick' => $trick,  'form' => $form->createView()]);
    }

    /**
     * @Route("/{id}/edit", name="tricks_edit", methods="GET|POST")
     */
    public function edit(Request $request, Tricks $trick): Response
    {
        $form = $this->createForm(TricksType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $trick->getImage();
            /*$fileName = $fileUploader->upload($file);
            $trick->setImage($fileName);*/
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            // Move the file to the directory where images are stored
            try {
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
                $trick->setImage($fileName);
                $em = $this->getDoctrine()->getManager();
            $em->persist($trick);
            $em->flush();
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            return $this->redirectToRoute('tricks_index', ['id' => $trick->getId()]);
        }

        return $this->render('tricks/edit.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tricks_delete", methods="DELETE")
     */
    public function delete(Request $request, Tricks $trick): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($trick);
            $em->flush();
        }

        return $this->redirectToRoute('tricks_index');
    }


}
