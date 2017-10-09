<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ApiBaseController extends Controller {

    protected function getEntities(Request $request, EntityRepository $repository, array $queryStringParams) : JsonResponse {
        if (count($request->query)) {
            $result = $repository->getFiltered(
                $this->getFiltersFromQueryString($request, $queryStringParams)
            );
        } else {
            $result = $repository->findAll();
        }

        $serializer = $this->get('jms_serializer');
        return new JsonResponse(json_decode($serializer->serialize($result, 'json')));
    }

    protected function errorResponse(string $message) : JsonResponse {
         return new JsonResponse(['error' => $message], JsonResponse::HTTP_BAD_REQUEST);
    }

    protected function deleteEntityValidations(EntityRepository $repository, int $entityId) {
        $recoveredEntity = $repository->findBy(['id' => $entityId]);
        
        if (count($recoveredEntity) === 0)
            throw new \Exception('Unable to recover with the ID: ' . $entityId);

        return $recoveredEntity[0];
    }

    private  function getFiltersFromQueryString(Request $request, array $queryStringParams) : ?array {
        $filters = [];

        foreach ($queryStringParams as $parameter)
            if ($request->get($parameter))
                $filters[$parameter] = $request->get($parameter);

        return $filters;
    }

}