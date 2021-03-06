<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


/**
 * @Route("/api")
 */
class GameAPIController extends AbstractController
{
    /**
     * @Route("/games", name="api_games")
     */
    public function index(NormalizerInterface $normalizer): Response
    {
        $gamesList = $this->getDoctrine()->getRepository(Game::class)->findAll();
        $jsonContent = $normalizer->normalize($gamesList, 'json', ['groups' => 'api:game']);
        return new Response(
            json_encode($jsonContent),
            200,
            ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
    }

    /**
     * @Route("/game/addGame", name="api_add_game")
     */
    public function addGame(NormalizerInterface $normalizer,Request $request): Response
    {
        if (!($request->request->get('gameName') && $request->request->get('image') && $request->request->get('description')))
            return new Response(
                '{"error": "Missing gameName or image or description."}',
                400, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        $game = new Game();
        $game->setName($request->request->get('gameName'));
        $game->setDescription($request->request->get('description'));
        $file=new File($request->request->get('image'));
        $fileName = md5(uniqid()) . '.jpg';
        $game->setImage($fileName);
        $file->move($this->getParameter('game_image_directory'), $fileName);
        $em=$this->getDoctrine()->getManager();
        $em->persist($game);
        $em->flush();
        $jsonContent = $normalizer->normalize($game, 'json', ['groups' => 'api:game']);
        return new Response(
            json_encode($jsonContent),
            200,
            ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
    }

    /**
     * @Route("/game/updateGame", name="api_update_game")
     */
    public function updateGame(NormalizerInterface $normalizer,Request $request): Response
    {
        if (!($request->request->get('gameId')))
            return new Response(
                '{"error": "Missing gameName."}',
                400, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        $game = $this->getDoctrine()->getRepository(Game::class)->findOneBy(["id"=>$request->request->get('gameId')]);
        $game->setName($request->request->get('gameName'));
        $game->setDescription($request->request->get('description'));
        if($request->request->get('image')) {
            $file = new File($request->request->get('image'));
            $fileName = md5(uniqid()) . '.jpg';
            $game->setImage($fileName);
            $file->move($this->getParameter('game_image_directory'), $fileName);
        }
        $em=$this->getDoctrine()->getManager();
        $em->persist($game);
        $em->flush();
        $jsonContent = $normalizer->normalize($game, 'json', ['groups' => 'api:game']);
        return new Response(
            json_encode($jsonContent),
            200,
            ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
    }


    /**
     * @Route("/likedGames", name="api_liked_games")
     */
    public function likedGames(NormalizerInterface $normalizer, Request $request): Response
    {
        if (!$request->query->get('username'))
            return new Response(
                '{"error": "Missing username."}',
                400, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["username" => $request->query->get('username')]);
        if ($user == null)
            return new Response(
                '{"error": "User not found."}',
                401, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        $gamesList = $user->getGames();
        $jsonContent = $normalizer->normalize($gamesList, 'json', ['groups' => 'api:game']);
        return new Response(
            json_encode($jsonContent),
            200,
            ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
    }


    /**
     * @Route("/game/like", name="api_game_like", methods={"POST"})
     */
    public function like(NormalizerInterface $normalizer, Request $request): Response
    {
        if (!($request->request->get('gameName') && $request->request->get('username')))
            return new Response(
                '{"error": "Missing username or gameName or both."}',
                400, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        $game = $this->getDoctrine()->getRepository(Game::class)->findOneBy(['name' => $request->request->get('gameName')]);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["username" => $request->request->get('username')]);
        if ($user == null)
            return new Response(
                '{"error": "User not found."}',
                401, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        if ($game == null)
            return new Response(
                '{"error": "Game not found."}',
                401, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
//        $jsonContent = $normalizer->normalize($gamesList, 'json', ['groups' => 'api:game']);
        $game->addUser($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($game);
        $em->flush();
        return new Response(
            "{\"response\": \"{$game->getName()} liked.\"}",
            200,
            ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
    }


    /**
     * @Route("/game/unlike", name="api_game_unlike", methods={"POST"})
     */
    public function unlike(NormalizerInterface $normalizer, Request $request): Response
    {
        if (!($request->request->get('gameName') && $request->request->get('username')))
            return new Response(
                '{"error": "Missing username or gameName or both."}',
                400, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        $game = $this->getDoctrine()->getRepository(Game::class)->findOneBy(['name' => $request->request->get('gameName')]);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["username" => $request->request->get('username')]);
        if ($user == null)
            return new Response(
                '{"error": "User not found."}',
                401, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        if ($game == null)
            return new Response(
                '{"error": "Game not found."}',
                401, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
//        $jsonContent = $normalizer->normalize($gamesList, 'json', ['groups' => 'api:game']);
        $game->removeUser($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($game);
        $em->flush();
        return new Response(
            "{\"response\": \"{$game->getName()} unliked.\"}",
            200,
            ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
    }

    /**
     * @Route("/game/isLiked", name="api_game_isLiked", methods={"GET"})
     */
    public function isLiked(Request $request): Response
    {
        if (!($request->query->get('gameName') && $request->query->get('username')))
            return new Response(
                '{"error": "Missing username or gameName or both."}',
                400, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        $game = $this->getDoctrine()->getRepository(Game::class)->findOneBy(['name' => $request->query->get('gameName')]);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["username" => $request->query->get('username')]);
        if ($user == null)
            return new Response(
                '{"error": "User not found."}',
                401, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        if ($game == null)
            return new Response(
                '{"error": "Game not found."}',
                401, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);

        if (in_array($user, $game->getUsers()->toArray()))
            return new Response(
                "{\"response\": \"True\"}",
                200,
                ['Accept' => 'application/json',
                    'Content-Type' => 'application/json']);
        else
            return new Response(
                "{\"response\": \"False\"}",
                200,
                ['Accept' => 'application/json',
                    'Content-Type' => 'application/json']);
    }

    /**
     * @Route("/game", name="api_game_isLiked")
     */
    public function getAGame(Request $request, NormalizerInterface $normalizer): Response
    {
        if (!($request->query->get('gameName') && $request->query->get('username')))
            return new Response(
                '{"error": "Missing username or gameName or both."}',
                400, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        $game = $this->getDoctrine()->getRepository(Game::class)->findOneBy(['name' => $request->query->get('gameName')]);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["username" => $request->query->get('username')]);
        if ($user == null)
            return new Response(
                '{"error": "User not found."}',
                401, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        if ($game == null)
            return new Response(
                '{"error": "Game not found."}',
                401, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        //$game["isLiked"] = in_array($user,$game->getUsers()->toArray());
        //dd($game);
        $jsonContent = $normalizer->normalize($game, 'json', ['groups' => 'api:game']);
        $jsonContent["isLiked"] = in_array($user, $game->getUsers()->toArray());
        return new Response(
            json_encode($jsonContent),
            200,
            ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
    }

    /**
     * @Route("/game/delete", name="api_game_delete", methods={"DELETE"})
     */
    public function delete(Request $request, NormalizerInterface $normalizer): Response
    {
        if (!$request->query->get('gameName'))
            return new Response(
                '{"error": "Missing gameName."}',
                400, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        $game = $this->getDoctrine()->getRepository(Game::class)->findOneBy(['name' => $request->query->get('gameName')]);
        if ($game == null)
            return new Response(
                '{"error": "Game not found."}',
                401, ['Accept' => 'application/json',
                'Content-Type' => 'application/json']);
        $em = $this->getDoctrine()->getManager();
        $em->remove($game);
        $em->flush();
        return new Response(
            "{\"response\": \"{$request->query->get('gameName')} deleted.\"}",
            200, ['Accept' => 'application/json',
            'Content-Type' => 'application/json']);
    }
}
