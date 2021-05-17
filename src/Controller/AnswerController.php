<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AnswerController extends AbstractController {

    public function getRandomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /**
     * Gets the answers.
     *
     * @Route(
     *     "/answers",
     *     name="get answers",
     *     methods={"GET"}
     * )
     *
     * @return JsonResponse
     */
    public function getAnswers()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        /** @var Answer[] $answers */
        $answers = $entityManager->getRepository(Answer::class)->findAll();

        return $this->json([
            'data' => array_map(function (Answer $answer): array {
                return [
                    'id' => $answer->getId(),
                    'user' => [
                        'id' => $answer->getUser()->getId(),
                        'email' => $answer->getUser()->getEmail(),
                        'name' => $answer->getUser()->getName(),
                    ],
                    'data' => $answer->getData(),
                ];
            }, $answers)
        ]);
    }

    /**
     * Creates an answer.
     *
     * @Route(
     *     "/answers",
     *     name="create an answer",
     *     methods={"POST"}
     * )
     *
     * @param Request $request Request.
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function createAnswer(Request $request)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $content = $request->getContent();
        $json = json_decode($content);
        $user = new User();
        $user->setEmail($json->email);
        $user->setName($json->name);
        $user->setPassword($this->getRandomPassword());
        $entityManager->persist($user);

        $answer = new Answer();
        $answer->setData($json->data);
        $answer->setUser($user);
        $entityManager->persist($answer);

        $entityManager->flush();
        
        return $this->json(null);
    }
}
