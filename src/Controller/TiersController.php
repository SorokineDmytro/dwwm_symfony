<?php

namespace App\Controller;

use App\Entity\Tiers;
use App\Service\MyFct;
use App\Form\TiersType;
use App\Entity\TypeTiers;
use App\Repository\TiersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/vente/tiers')]
final class TiersController extends AbstractController
{
    #[Route('/api', name:'app_tiers_api', methods:["GET"])]
    public function apiTiers(TiersRepository $tr){
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
		$tiers=$tr->findBy([],['id'=>'ASC']);
		$response=[];
		foreach($tiers as $tier){
			$response[]=[
				'id'=>$tier->getId(),
				'num_tiers'=>$tier->getNumTiers(),
				'nom_tiers'=>$tier->getNomTiers(),
				'adresse_tiers'=>$tier->getAdresseTiers(),
                'type_tiers'=>$tier->getTypeTiers()->getLibelle()
            ];
		}
		echo json_encode($response);
		exit();
	} 

    #[Route('/api/edit', name:'app_tiers_api_edit', methods:["GET", "POST"])]
    public function apiEditTiers(EntityManagerInterface $em, Request $request){
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        $edit_id = (int) $request->get('edit_id');
        $numTiers = $request->get('numTiers');
        $nomTiers = $request->get('nomTiers');
        $typeTiersLibelle = $request->get('typeTiers');
        $adresseTiers = $request->get('adresseTiers');
        $error = false;

        $typeTiers = $em->getRepository(TypeTiers::class)->findOneBy(['libelle' => $typeTiersLibelle]);

        if ($edit_id == 0) {
            $tiers = new Tiers();
        } else {
            $tiers = $em->getRepository(Tiers::class)->find($edit_id);
            if (!$tiers) {
                $error = true;
            }
        }

        if ($error) {
            $response = "Erreur d'enregistrement ! Le tiers dont le id = $edit_id n'existe pas.";
        } else {
            $tiers->setNumTiers($numTiers);
            $tiers-> setNomTiers($nomTiers);
            $tiers->setTypeTiers($typeTiers);
            $tiers->setAdresseTiers($adresseTiers);
            $em->persist($tiers);
            $em->flush();
            $response = "Enregistrement effectué avec succès !";
        }
        echo json_encode($response);
        exit;
    }

    #[Route("/api/delete", name:"app_tiers_api_delete", methods:["GET", "POST"])]
	public function apiDeleteTiers(EntityManagerInterface $em, Request $request){
        header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
		$id = (int) $request->get('id');
		$tier = $em->getRepository(Tiers::class)->find($id);
		if($tier) {
			$em->remove($tier);
			$em->flush();
			$response = "Suppression bien faite dans la base de données!";
		}else{
			$response = "Il existe une erreur sur la suppression de id= $id! Suppression annulée!";
		}
		echo json_encode($response);
		exit();
	} 

    #[Route(name: 'app_tiers_index', methods: ['GET'])]
    public function index(TiersRepository $tiersRepository): Response
    {
        return $this->render('tiers/index.html.twig', [
            'tiers' => $tiersRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_tiers_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tier = new Tiers();
        $form = $this->createForm(TiersType::class, $tier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $myFct = new MyFct();
            $typeTiers = $form->get('typeTiers')->getData();
            $numTiers = $myFct->createNumEntity($entityManager, $typeTiers); 
            $tier->setNumTiers($numTiers);
            $entityManager->persist($tier);
            $entityManager->flush();

            return $this->redirectToRoute('app_tiers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tiers/new.html.twig', [
            'tier' => $tier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tiers_show', methods: ['GET'])]
    public function show(Tiers $tier): Response
    {
        return $this->render('tiers/show.html.twig', [
            'tier' => $tier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tiers_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tiers $tier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TiersType::class, $tier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tiers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tiers/edit.html.twig', [
            'tier' => $tier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tiers_delete', methods: ['POST'])]
    public function delete(Request $request, Tiers $tier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tier->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tiers_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/import/excel', name: 'app_tiers_import_excel', methods: ['GET'])]
    public function importExcel(EntityManagerInterface $em) {
        $filePath='/Users/sorokine/Codding/myProjects/2024/Symphony/tiers.xlsx';
        $r0=2;
        $myFct=new MyFct();
        $datas=$myFct->readExcel($filePath,$r0);
        foreach($datas as $data){
            $numTiers=$data[0];
            $nomTiers=$data[1];
            $adresseTiers=$data[2];
            $typeTiers_id=$data[3];
            $typeTiers=$em->getRepository(TypeTiers::class)->find($typeTiers_id);
            $tiers=$em->getRepository(Tiers::class)->findOneBy(['numTiers'=>$numTiers]);
            if(!$tiers){
                $tiers=new Tiers;
            }
            $tiers->setNumTiers($numTiers);
            $tiers->setNomTiers($nomTiers);
            $tiers->setAdresseTiers($adresseTiers);
            $tiers->setTypeTiers($typeTiers);
            $em->persist($tiers);
            $em->flush();
        }
        return $this->redirectToRoute("app_tiers_index");
        }

    #[Route('/export/excel', name: 'app_tiers_export_excel', methods: ['GET'])]
    public function exportExcel(TiersRepository $tr) {
        $tiers = $tr->findBy([], ['numTiers'=>'ASC']);
        $datas[] = [
            'NUM TIERS',
            'NOM TIERS',
            'ADRESSE',
        ];
        foreach($tiers as $tier) {
            $datas[] = [
                $tier->getNumTiers(),
                $tier->getNomTiers(),
                $tier->getAdresseTiers(),
            ];
        }
        $myFct=new MyFct();
        $filePath = $filePath='/Users/sorokine/Codding/myProjects/2024/Symphony/all_tiers.xlsx';
        $r0 = 1;
        $file = $myFct->writeExcel($datas,$filePath,$r0=1);
        return $this->file($filePath,"Export tiers.xlsx", ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
