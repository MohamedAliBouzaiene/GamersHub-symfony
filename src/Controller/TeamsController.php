<?php

namespace App\Controller;

use App\Entity\Matchs;
use App\Entity\Teams;
use App\Form\TeamsType;
use App\Form\TeamsBackType;
use App\Repository\MatchsRepository;
use App\Repository\TeamsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teams")
 */
class TeamsController extends AbstractController
{
    /**
     * @Route("/", name="teams_index")
     */


    public function index(Request $request,TeamsRepository $teamsRepository,PaginatorInterface $paginator): Response
    {

        $repo =$this->getDoctrine()->getRepository(Teams::class)->findBy([],['rank' => 'desc']);
        $team = $paginator->paginate(
            $repo, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            4 // Nombre de résultats par page
        );
        return $this->render('teams/Teams.html.twig', [
            'user' => $this->getUser(),

            'teamsList'=> $team
        ]);

    }

    /**
     * @Route("/new", name="teams_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Teams();
        $form = $this->createForm(TeamsType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        'img\Teams'
                    );
                } catch (FileException $e) {
                }
                $team->setImage($newFilename);
            }
            $entityManager->persist($team);
            $entityManager->flush();
            $this->addFlash(
                'info',
                'Added succefully!'
            );

            return $this->redirectToRoute('teams_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('teams/new.html.twig', [
            'user' => $this->getUser(),

            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Admin/showTeams", name="teams_show")
     */
    public function show(): Response
    {   $team = new Teams();
        $repo =$this->getDoctrine()->getRepository(Teams::class);
        return $this->render('teams/TeamsBack.html.twig', [
            'user' => $this->getUser(),
            'team' => $team,
            'teamsList'=> $repo->findAll()
        ]);
    }

    /**
     * @Route("/admin/teams/{id}/edit", name="teams_edit")
     */
    public function edit(Request $request, Teams $team, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamsBackType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash(
                'info',
                'Updated succefully!'
            );

            return $this->redirectToRoute('teams_show');
        }

        return $this->render('teams/TeamUpdate.html.twig', [
            'user' => $this->getUser(),
            'team' => $team,
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/admin/teams/tri", name="tri1")
     */
    public function Tri(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repo =$this->getDoctrine()->getRepository(Teams::class);


        $query = $em->createQuery(
            'SELECT a FROM App\Entity\Teams a 
            ORDER BY a.Team_name DESC '
        );

        $activites = $query->getResult();
        return $this->render('teams/TeamsBack.html.twig',[
            'user' => $this->getUser(),
            'teamsList'=> $repo->findAll(),
             array('teams' => $activites)]);


    }

    /**
     * @Route("/admin/teams/{id}/delete", name="teams_delete")
     */
    public function delete(Teams $team): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($team);
        $em->flush();
        $this->addFlash(
            'info',
            'Deleted succefully!'
        );

        $repo =$this->getDoctrine()->getRepository(Teams::class);
        return $this->redirectToRoute("teams_show");
       
}

}