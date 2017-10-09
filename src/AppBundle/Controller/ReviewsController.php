<?php

namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity;
use AppBundle\Service;

class ReviewsController extends ApiBaseController {

    public function testAnalyzer(Request $request) : JsonResponse {
        $analyzer = $this->get('AppBundle.DefaultAnalyzer');
        $response = $analyzer->analyze($request->getContent());
        return new JsonResponse(json_decode($response->getFullResults(TRUE)));
    }

    public function getReviews(Request $request) : JsonResponse {
        return $this->getEntities(
            $request,
            $this->get('doctrine')->getManager()->getRepository('AppBundle:Review'),
            ['id', 'text', 'total_score']
        );
    }

    public function analyzeReview(int $reviewId) : JsonResponse {
        $doctrineManager = $this->get('doctrine')->getManager();
        $recoveredReview = $doctrineManager->getRepository('AppBundle:Review')
            ->findBy(['id' => $reviewId]);

        if (count($recoveredReview) === 0)
            return $this->errorResponse("The review with the ID " . $reviewId . " does not exist.");

        $this->deletePreviousAnalysis($recoveredReview[0], $doctrineManager);

        $analyzer = $this->get('AppBundle.DefaultAnalyzer');
        $response = $analyzer->analyze($recoveredReview[0]->getText());

        $this->persistAnalysisResults($recoveredReview[0], $response, $doctrineManager);

        $serializer = $this->get('jms_serializer');
        return new JsonResponse(json_decode($serializer->serialize($recoveredReview[0], 'json')));
    }

    public function analyzeAllReviews() : JsonResponse {
        $doctrineManager = $this->get('doctrine')->getManager();
        $unanalyzedReviews = $doctrineManager->getRepository('AppBundle:Review')->getUnanalyzedReviews();
        
        $analyzer = $this->get('AppBundle.DefaultAnalyzer');
        foreach ($unanalyzedReviews as $review) {
            $this->deletePreviousAnalysis($review, $doctrineManager);
            $response = $analyzer->analyze($review->getText());
            $this->persistAnalysisResults($review, $response, $doctrineManager);
        }

        return new JsonResponse(['sucess' => TRUE]);
    }

    public function uploadReviews(Request $request) : JsonResponse {
        if (! $request->files->get('csvFile'))
            return $this->errorResponse("Missing file.");

        $uploadedFile = $request->files->get('csvFile');

        if (strtolower($uploadedFile->getClientOriginalExtension()) != 'csv')
            return $this->errorResponse("File must be a CSV file.");/**/

        if ($uploadedFile->getClientSize() > $uploadedFile->getMaxFilesize())
            return $this->errorResponse("Maximum size exceeded. Max size:" + $uploadedFile->getMaxFilesize());

        $separator = $request->get('csvSeparator') === 'pipes' ? '|' : "\n";
        $reviews = explode($separator, file_get_contents($uploadedFile->getPathName()));
        
        $doctrineManager = $this->get('doctrine')->getManager();
        foreach ($reviews as $text) {
            $review = new Entity\Review();
            $review->setText(str_replace("\n", " ", $text));
            $doctrineManager->persist($review);
        }
        $doctrineManager->flush();
        return new JsonResponse(['success' => TRUE]);
    }

    public function newReview(Request $request) : JsonResponse {
        $doctrineManager = $this->get('doctrine')->getManager();

        try {
            $decodedBody = $this->newReviewValidations($request, $doctrineManager);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        $newReview = new Entity\Review();
        $newReview->setText($decodedBody->text);
        $doctrineManager->persist($newReview);
        $doctrineManager->flush();

        return new JsonResponse(["success" => TRUE, "id" => $newReview->getId()]);
    }

    public function modifyReview(Request $request) : JsonResponse {
        $doctrineManager = $this->get('doctrine')->getManager();

        try {
            $validationResult = $this->modifyReviewValidations($request, $doctrineManager);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        if (isset($validationResult['decodedBody']->text))
            $validationResult['recoveredReview']->setText($validationResult['decodedBody']->text);

        $this->deletePreviousAnalysis($validationResult['recoveredReview'], $doctrineManager);
        $validationResult['recoveredReview']->setTotalScore(NULL);

        $doctrineManager->persist($validationResult['recoveredReview']);
        $doctrineManager->flush();

        $serializer = $this->get('jms_serializer');
        return new JsonResponse(json_decode($serializer->serialize($validationResult['recoveredReview'], 'json')));
    }

    public function deleteReview(int $reviewId) : JsonResponse { 
        $doctrineManager = $this->get('doctrine')->getManager();

        try {
            $recoveredReview = $this->deleteEntityValidations(
                $doctrineManager->getRepository('AppBundle:Review'),
                $reviewId
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        $doctrineManager->remove($recoveredReview);
        $doctrineManager->flush();

        return new JsonResponse();
    }

    private function deletePreviousAnalysis(Entity\Review $review, EntityManagerInterface $doctrineManager) : void {
        $reviewAnalysis = $doctrineManager->getRepository('AppBundle:Analysis')->findBy(['review' => $review]);

        foreach ($reviewAnalysis as $analysis)
            $doctrineManager->remove($analysis);

        $doctrineManager->flush();
    }


    private function newReviewValidations(Request $request, EntityManagerInterface $doctrineManager) : ?\stdClass {
        $decodedBody = json_decode($request->getContent());

        if (!isset($decodedBody->text))
            throw new \Exception('Missing text in request');
        
        if (empty($decodedBody->text))
            throw new \Exception('Text cannot be empty');

        
        return $decodedBody;
    }

    private function modifyReviewValidations(Request $request, EntityManagerInterface $doctrineManager) : array {
        $decodedBody = json_decode($request->getContent());

        if (!isset($decodedBody->id))
            throw new \Exception('Missing review ID in request');

        if (!is_int($decodedBody->id))
            throw new \Exception('Review ID must be an integer');
        
        $recoveredReview = $doctrineManager->getRepository('AppBundle:Review')
            ->findBy(['id' => $decodedBody->id]);
        
        if (count($recoveredReview) === 0)
            throw new \Exception('Unable to recover the review with the ID: ' . $decodedBody->id);
        
        return [
            'decodedBody' => $decodedBody,
            'recoveredReview' => $recoveredReview[0]
        ];
    }


    private function persistAnalysisResults(Entity\Review $review, Service\AnalyzerResponse $analysisResults, EntityManagerInterface $doctrineManager) : void {
        $fullResults = $analysisResults->getFullResults();
        $analysisCollection = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($fullResults as $topicName => $topicResult) {
            $analysis = new Entity\Analysis;
            $analysis->setReview($review);
            $topicEntity = $doctrineManager->getRepository('AppBundle:Topic')->findBy(['name' => $topicName]);
            $analysis->setTopic($topicEntity[0]);
            $analysis->setScore($topicResult['score']);
            $doctrineManager->persist($analysis);

            $analysisCriteriaCollection = new \Doctrine\Common\Collections\ArrayCollection();
            foreach ($topicResult['criteria'] as $criteria) {
                $analysisCriteria = new Entity\AnalysisCriteria;
                $analysisCriteria->setAnalysis($analysis);
                $analysisCriteria->setCriteria($criteria['entity']);
                $analysisCriteria->setEmphasizer($criteria['emphasizer']);
                $analysisCriteria->setNegated($criteria['negated']);
                $doctrineManager->persist($analysisCriteria);

                $analysisCriteriaCollection->add($analysisCriteria);
            }
            $analysisCriteriaPersistentCollection = new \Doctrine\ORM\PersistentCollection(
                $doctrineManager,
                $doctrineManager->getClassMetadata('AppBundle\\Entity\\AnalysisCriteria'),
                $analysisCriteriaCollection
            );
            $analysis->setAnalysisCriteria($analysisCriteriaPersistentCollection);
            $analysisCollection->add($analysis);
        }

        $analysisPersistentCollection = new \Doctrine\ORM\PersistentCollection(
            $doctrineManager,
            $doctrineManager->getClassMetadata('AppBundle\\Entity\\Analysis'),
            $analysisCollection
        );
        $review->setAnalysis($analysisPersistentCollection);
        $review->setTotalScore($analysisResults->getScore());
        $doctrineManager->persist($review);

        $doctrineManager->flush();
    }

}
