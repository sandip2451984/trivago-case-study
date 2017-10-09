<?php

namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\Emphasizer;

class EmphasizersController extends ApiBaseController {

    public function getEmphasizer(Request $request) : JsonResponse {
        return $this->getEntities(
            $request,
            $this->get('doctrine')->getManager()->getRepository('AppBundle:Emphasizer'),
            ['id', 'name', 'score_modifier']
        );
    }

    public function newEmphasizer(Request $request) : JsonResponse {
        $doctrineManager = $this->get('doctrine')->getManager();
        try {
            $decodedBody = $this->newEmphasizerValidations($request, $doctrineManager);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        $newEmphasizer = new Emphasizer();
        $newEmphasizer->setName($decodedBody->name);
        $newEmphasizer->setScoreModifier($decodedBody->score_modifier);
        $doctrineManager->persist($newEmphasizer);
        $doctrineManager->flush();

        return new JsonResponse(["success" => TRUE, "id" => $newEmphasizer->getId()]);
    }

    public function modifyEmphasizer(Request $request) : JsonResponse {
        $doctrineManager = $this->get('doctrine')->getManager();
        try {
            $validationResult = $this->modifyEmphasizerValidations($request, $doctrineManager);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        if (isset($validationResult['decodedBody']->name))
            $validationResult['recoveredEmphasizer']->setName($validationResult['decodedBody']->name);

        if (isset($validationResult['decodedBody']->score_modifier))
            $validationResult['recoveredEmphasizer']->setScoreModifier($validationResult['decodedBody']->score_modifier);

        $doctrineManager->persist($validationResult['recoveredEmphasizer']);
        $doctrineManager->flush();

        return new JsonResponse(["success" => TRUE]);
    }

    public function deleteEmphasizer(int $emphasizerId) : JsonResponse {
        $doctrineManager = $this->get('doctrine')->getManager();

        try {
            $recoveredEmphasizer = $this->deleteEntityValidations(
                $doctrineManager->getRepository('AppBundle:Emphasizer'),
                $emphasizerId
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        $doctrineManager->remove($recoveredEmphasizer);
        $doctrineManager->flush();

        return new JsonResponse();
    }


    private function newEmphasizerValidations(Request $request, EntityManagerInterface $doctrineManager) : ?\stdClass {
        $decodedBody = json_decode($request->getContent());

        if (!isset($decodedBody->name) || !isset($decodedBody->score_modifier))
            throw new \Exception('Missing name or score_modifier in request');
        
        if (!is_numeric($decodedBody->score_modifier))
            throw new \Exception('score_modifier must be a valid number');
        
        $recoveredEmphasizer = $doctrineManager->getRepository('AppBundle:Emphasizer')
            ->findBy(['name' => $decodedBody->name]);
        
        if (count($recoveredEmphasizer) > 0)
            throw new \Exception('Emphasizer already exists.');
        
        return $decodedBody;
    }


    private function modifyEmphasizerValidations(Request $request, EntityManagerInterface $doctrineManager) : array {
        $decodedBody = json_decode($request->getContent());

        if (!isset($decodedBody->id))
            throw new \Exception('Missing emphasizer ID in request');

        if (!is_int($decodedBody->id))
            throw new \Exception('Emphasizer ID must be an integer');
        
        if (isset($decodedBody->score_modifier) && !is_numeric($decodedBody->score_modifier))
            throw new \Exception('Score_modifier must be a number');
        
        $recoveredEmphasizer = $doctrineManager->getRepository('AppBundle:Emphasizer')
            ->findBy(['id' => $decodedBody->id]);
        
        if (count($recoveredEmphasizer) === 0)
            throw new \Exception('Unable to recover the emphasizer with the ID: ' . $decodedBody->id);
        
        return [
            'decodedBody' => $decodedBody,
            'recoveredEmphasizer' => $recoveredEmphasizer[0]
        ];
    }

}
