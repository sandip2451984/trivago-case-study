<?php

namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\TopicAlias;

class TopicsAliasesController extends ApiBaseController {

    public function getTopicAliases(Request $request) : JsonResponse {
        return $this->getEntities(
            $request,
            $this->get('doctrine')->getManager()->getRepository('AppBundle:TopicAlias'),
            ['id', 'topic_name', 'alias']
        );
    }

    public function newTopicAlias(Request $request) : JsonResponse {
        $doctrineManager = $this->get('doctrine')->getManager();
        try {
            [$decodedBody, $recoveredTopic] = $this->newTopicAliasValidations($request, $doctrineManager);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        $newAlias = new TopicAlias();
        $newAlias->setAlias($decodedBody->alias);
        $newAlias->setTopic($recoveredTopic);

        $doctrineManager->persist($newAlias);
        $doctrineManager->flush();

        return new JsonResponse(["success" => TRUE, "id" => $newAlias->getId()]);
    }
    
    public function modifyTopicAlias(Request $request) : JsonResponse {
        $doctrineManager = $this->get('doctrine')->getManager();
        try {
            $validationResult = $this->modifyTopicAliasValidations($request, $doctrineManager);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        if (isset($validationResult['decodedBody']->alias))
            $validationResult['recoveredAlias']->setAlias($validationResult['decodedBody']->alias);

        $doctrineManager->persist($validationResult['recoveredAlias']);
        $doctrineManager->flush();

        return new JsonResponse(["success" => TRUE]);
    }

    public function deleteTopicAlias(int $aliasId) : JsonResponse {
        $doctrineManager = $this->get('doctrine')->getManager();

        try {
            $recoveredAlias = $this->deleteEntityValidations(
                $doctrineManager->getRepository('AppBundle:TopicAlias'),
                $aliasId
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        $doctrineManager->remove($recoveredAlias);
        $doctrineManager->flush();

        return new JsonResponse();
    }


    private function newTopicAliasValidations(Request $request, EntityManagerInterface $doctrineManager) : ?array {
        $decodedBody = json_decode($request->getContent());

        if (!isset($decodedBody->alias) || !isset($decodedBody->topic_name))
            throw new \Exception('Missing name or topic name in request');
        
        $recoveredTopic = $doctrineManager->getRepository('AppBundle:Topic')
            ->findBy(['name' => $decodedBody->topic_name]);
        
        if (count($recoveredTopic) === 0)
            throw new \Exception('Topic ' . $decodedBody->topic_name .' does not exist.');

        $recoveredAlias = $doctrineManager->getRepository('AppBundle:TopicAlias')
            ->findBy(['alias' => $decodedBody->alias]);

        if (count($recoveredAlias) > 0)
            throw new \Exception('Alias ' . $decodedBody->alias .' already exists for topic: ' . $recoveredAlias[0]->getTopic()->getName());
        
        return [$decodedBody, $recoveredTopic[0]];
    }

    private function modifyTopicAliasValidations(Request $request, EntityManagerInterface $doctrineManager) : array {
        $decodedBody = json_decode($request->getContent());

        if (!isset($decodedBody->id))
            throw new \Exception('Missing topic ID in request');

        if (!is_int($decodedBody->id))
            throw new \Exception('topic ID must be an integer');
        
        $recoveredTopicAlias = $doctrineManager->getRepository('AppBundle:TopicAlias')
            ->findBy(['id' => $decodedBody->id]);
        
        if (count($recoveredTopicAlias) === 0)
            throw new \Exception('Unable to recover the alias with the ID: ' . $decodedBody->id);
        
        return [
            'decodedBody' => $decodedBody,
            'recoveredAlias' => $recoveredTopicAlias[0]
        ];
    }

}