<?php

/*
 * Copyright 2011 Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace VIB\FliesBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use VIB\FliesBundle\Entity\Vial;
use VIB\FliesBundle\Entity\StockVial;
use VIB\FliesBundle\Entity\CrossVial;
use VIB\FliesBundle\Entity\Stock;
use VIB\FliesBundle\Entity\RackPosition;

/**
 * Description of AJAXController
 *
 * @Route("/ajax")
 * 
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class AJAXController extends Controller {
    
    /**
     * Handle vial AJAX request
     * 
     * @Route("/vials")
     * @Template()
     * 
     * @return Symfony\Component\HttpFoundation\Response
     */    
    public function vialAction(Request $request) {
        
        $id = $request->query->get('id');
        $filter = $request->query->get('filter');
        $format = $request->query->get('format');
        
        $em = $this->get('doctrine.orm.entity_manager');
        $securityContext = $this->get('security.context');
        $vial = $em->find('VIBFliesBundle:Vial', $id);
        $type = $filter !== null ? ' ' . $filter : '';
        
        if((! $vial instanceof Vial)||(($filter !== null)&&($vial->getType() != $filter))) {
            return new Response('The' . $type . ' vial ' . sprintf("%06d",$id) . ' does not exist', 404);
        } elseif (!($securityContext->isGranted('ROLE_ADMIN') || $securityContext->isGranted('VIEW', $vial))) {
            return new Response('Access to' . $type . ' vial ' . sprintf("%06d",$id) . ' denied', 401);
        }
        
        $serializer = $this->get('serializer');
        
        if ($format == 'json') {
            return new Response($serializer->serialize($vial, 'json')); 
        } else {
            return array('entity' => $vial, 'checked' => 'checked', 'type' => $filter);
        }
    }
    
    /**
     * Handle rack vial AJAX request
     * 
     * @Route("/racks/vials")
     * @Template()
     * 
     * @return Symfony\Component\HttpFoundation\Response
     */    
    public function rackVialAction(Request $request) {
        
        $vialId = $request->query->get('vialId');
        $positionId = $request->query->get('positionId');
        
        $em = $this->get('doctrine.orm.entity_manager');
        $securityContext = $this->get('security.context');
        $vial = $em->find('VIBFliesBundle:Vial', $vialId);
        $position = $em->find('VIBFliesBundle:RackPosition', $positionId);
        
        if(($vialId != null)&&(! $vial instanceof Vial)) {
            return new Response('The' . $type . ' vial ' . sprintf("%06d",$id) . ' does not exist', 404);
        } elseif (!($securityContext->isGranted('ROLE_ADMIN') || $securityContext->isGranted('VIEW', $vial))) {
            return new Response('Access to' . $type . ' vial ' . sprintf("%06d",$id) . ' denied', 401);
        }
        
        if(! $position instanceof RackPosition) {
            return new Response('Selected position does not exist', 404);
        } elseif (($vialId != null)&&(! $position->isEmpty())) {
            return new Response('Selected position is not empty', 406);
        }
        
        return array('contents' => $vial);
    }
    
    /**
     * Handle stock search AJAX request
     * 
     * @Route("/stocks/search")
     * 
     * @param Symfony\Component\HttpFoundation\Request $request
     * @return Symfony\Component\HttpFoundation\Response
     */    
    public function stockSearchAction(Request $request) {
        
        $query = $request->query->get('query');
        $qb = $this->getDoctrine()
                   ->getRepository('VIBFliesBundle:Stock')
                   ->search($query);
        $found = $this->get('vib.security.helper.acl')
                      ->apply($qb)
                      ->getResult();
        
        $stockNames = array();
        
        foreach ($found as $stock) {
            $stockNames[] = $stock->getName();
        }
        
        $response = new JsonResponse();
        $response->setData(array('options' => $stockNames));
        
        return $response;
    }
    
    /**
     * Handle stock search AJAX request
     * 
     * @Route("/popover")
     * @Template()
     * 
     * @param Symfony\Component\HttpFoundation\Request $request
     * @return Symfony\Component\HttpFoundation\Response
     */    
    public function popoverAction(Request $request) {
        $type = $request->query->get('type');
        $id = $request->query->get('id');
        
        switch($type) {
            case 'vial':
                $entity =  $this->getDoctrine()
                                ->getRepository('VIBFliesBundle:Vial')
                                ->find($id);
                $etype = "Vial";
                break;
            case 'stock':
                $entity =  $this->getDoctrine()
                                ->getRepository('VIBFliesBundle:Stock')
                                ->find($id);
                $etype = "Stock";
                break;
            default:
                return new Response('Unrecognized type', 404);
        }
        
        $status = "";
        
        if ($entity instanceof Vial) {
            if($entity->isTrashed()) {
                $status = "<span class=\"label label-inverse pull-right\">TRASHED</span>";
            } elseif($entity->isAlive()) {
                $status = "<span class=\"label label-success pull-right\">ALIVE</span>";
            } else {
                $status = "<span class=\"label label-important pull-right\">DEAD</span>";
            }
            if ($entity instanceof CrossVial) {
                $type  = "crossvial";
                $etype = "Cross";
            } elseif (($entity instanceof StockVial)&&(null !== $entity->getStock())) {
                $type  = "stockvial";
            }            
        } elseif ($entity instanceof Stock) {
            $vials = count($entity->getLivingVials());
            if($vials > 3) {
                $status = "<span class=\"label label-success pull-right\">AMPLIFIED</span>";
            } elseif($vials > 1) {
                $status = "<span class=\"label label-success pull-right\">HEALTHY</span>";
            } elseif($vials < 1) {
                $status = "<span class=\"label label-important pull-right\">DEAD</span>";
            } else {
                $status = "<span class=\"label label-warning pull-right\">EXPAND</span>";
            }
        } else {
             return new Response('Not found', 404);
        }
        
        $html = $this->render('VIBFliesBundle:AJAX:popover.html.twig',
                array('type' => $type, 'entity' => $entity))->getContent();
        $title = "<b>" . $etype . " " . $entity . "</b>" . $status;
        
        $response = new JsonResponse();
        $response->setData(array('title' => $title, 'html' => $html));
        
        return $response;
    }
}

?>
