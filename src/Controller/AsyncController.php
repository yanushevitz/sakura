<?php

namespace App\Controller;

use App\Entity\Post;
use App\Services\CommentService;
use App\Services\PostService;
use Auth0\SDK\Auth0;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Serializer\SerializerInte;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class AsyncController extends AbstractController
{
    private readonly SerializerInterface $serializer;
    private readonly Auth0 $auth0;
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PostService $postService,
        private readonly CommentService $commentService       
    ){
        $this->auth0 = new Auth0([
            'domain' => $_ENV['AUTH0_DOMAIN'],
            'clientId' => $_ENV['AUTH0_CLIENT_ID'],
            'clientSecret' => $_ENV['AUTH0_CLIENT_SECRET'],
            'cookieSecret' => $_ENV['AUTH0_COOKIE_SECRET']
        ]);
    
        $defaultContext = [ 
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function($object){
                return $object->getAuthor();            
            }
            ];
        $this->serializer = new Serializer([new ObjectNormalizer(defaultContext: $defaultContext)], [new JsonEncoder]);
    }

    #[Route('/async/posts', name: 'async_posts_fetch')]
    public function index(): Response
    {
        $posts = $this->entityManager->getRepository(Post::class)->findAll();
        $posts = array_reverse($posts);
        $defaultContext = [ 
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function($object){
                return $object->getName();            
            }
            ];
        $json = $this->serializer->serialize($posts, 'json', ['groups' => ['user', 'comment']]);
        $response = new JsonResponse();
        $response->setContent($json);
        $response->headers->set('Access-Control-Allow-Origin', "*");
        return $response;
    }
    #[Route("/async/post", name: "async_post_create")]
    public function createPost(Request $request){
        $user = $this->auth0->getUser()['sub'];
        $post = $this->postService->create($request, $user);
        // $this->auth0->
        return new JsonResponse(['status' => 'created', "id" => $post]);
    }

    #[Route("/async/post/{id}", name: "async_post_fetch")]
    public function fetchPost($id){
        $data = $this->postService->fetchById($id);
        $jsonData = $this->serializer->normalize($data, 'json');
        return new JsonResponse($jsonData);
    }
    #[Route("/async/search", name: "async_search")]
    public function search(Request $request){
        $phrase = $request->getContent();
        $phrase = $this->serializer->decode($phrase, 'json');
        $phrase = $phrase['phrase'];
        $query = $this->entityManager->createQuery("SELECT p.id, p.author, p.text FROM App\Entity\Post p WHERE p.text LIKE '%".$phrase."%'");
        $result = $query->getResult();
        return new JsonResponse($result);
    }


    #[Route("/async/comment/{id}", name: "async_comment_create")]
    public function createComment(Request $request, $id){
        $user = $this->auth0->getUser()['sub'];
        $comment = $this->commentService->create($request, $user, $id);
        // $this->auth0->
        return new JsonResponse(['status' => 'created', "id" => 0]);
    }

}
