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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use VIB\BaseBundle\Controller\CRUDController;

use VIB\FliesBundle\Form\RackType;

use VIB\FliesBundle\Entity\Rack;

/**
 * RackController class
 * 
 * @Route("/racks")
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class RackController extends CRUDController
{
    /**
     * Construct StockController
     * 
     */ 
    public function __construct()
    {
        $this->entityClass = 'VIB\FliesBundle\Entity\Rack';
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getEditForm() {
        return new RackType();
    }
    
    /**
     * Create rack
     * 
     * @Route("/new")
     * @Template()
     * 
     * @return array|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction() {
        
        $rack = new Rack();
        $data = array('rack' => $rack, 'rows' => 10, 'columns' => 10);
        
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm($this->getCreateForm(), $data);
        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST') {
            
            $form->bindRequest($request);
            
            if ($form->isValid()) {
                
                $data = $form->getData();
                $rack = $data['rack'];
                $rows = $data['rows'];
                $columns = $data['columns'];
                
                $rack->setGeometry($rows, $columns);
                
                $em->persist($rack);
                $em->flush();

                $this->setACL($rack);
                
                $url = $this->generateUrl('vib_flies_rack_show',array('id' => $rack->getId())); 
                return $this->redirect($url);
            }
        }
        
        return array('form' => $form->createView());
    }
    
    /**
     * Edit rack
     * 
     * @Route("/edit/{id}")
     * @Template()
     * 
     * @param mixed $id
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $rack = $this->getEntity($id);
        $securityContext = $this->get('security.context');
        
        if (!($securityContext->isGranted('ROLE_ADMIN')||$securityContext->isGranted('EDIT', $rack))) {
            throw new AccessDeniedException();
        }        
        
        $data = array('rack' => $rack, 'rows' => 10, 'columns' => 10);
        
        $form = $this->createForm($this->getCreateForm(), $data);
        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST') {
            
            $form->bindRequest($request);
            
            if ($form->isValid()) {
                
                $data = $form->getData();
                $rack = $data['rack'];
                $rows = $data['rows'];
                $columns = $data['columns'];
                
                $rack->setGeometry($rows, $columns);
                
                $em->persist($rack);
                $em->flush();
                
                $url = $this->generateUrl('vib_flies_rack_show',array('id' => $rack->getId())); 
                return $this->redirect($url);
            }
        }
        
        return array('form' => $form->createView());
    }
}
