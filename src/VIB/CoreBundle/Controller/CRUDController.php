<?php

/*
 * Copyright 2013 Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
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

namespace VIB\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

use VIB\CoreBundle\Form\AclType;

/**
 * Base class for CRUD operations CRUDController
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
abstract class CRUDController extends AbstractController
{

    /**
     * Entity class for this controller
     *
     * @var string
     */
    protected $entityClass;

    /**
     * Entity name for this controller
     *
     * @var string
     */
    protected $entityName;

    /**
     * Construct CRUDController
     *
     */
    public function __construct()
    {
        $this->entityClass = null;
        $this->entityName = 'entity|entities';
    }

    /**
     * List entities
     *
     * @Route("/")
     * @Route("/list/{filter}")
     * @Template()
     * @Secure(roles="ROLE_USER, ROLE_ADMIN")
     *
     * @param  string $filter
     * @return array
     */
    public function listAction($filter = null)
    {
        $paginator  = $this->getPaginator();
        $page = $this->getCurrentPage();
        $repository = $this->getObjectManager()->getRepository($this->getEntityClass());
        $options = $this->getListOptions($filter);
        $count = $repository->getListCount($options);
        $query = $repository->getListQuery($options)->setHint('knp_paginator.count', $count);
        $entities = $paginator->paginate($query, $page, 25, array('distinct' => false));
        
        return array('entities' => $entities, 'filter' => $filter);
    }

    /**
     *
     * @param  string $filter
     * @return array
     */
    protected function getListOptions($filter = null)
    {
        $securityContext = $this->getSecurityContext();
        $options = array();
        $options['filter'] = $filter;
        $options['user'] = $this->getUser();
        switch ($filter) {
            case 'public':
            case 'all':
                $options['permissions'] = $securityContext->isGranted('ROLE_ADMIN') ? false : array('VIEW');
                break;
            default:
                $options['permissions'] = array('OWNER');
                break;
        }

        return $options;
    }

    /**
     * Show entity
     *
     * @Route("/show/{id}")
     * @Template()
     *
     * @param mixed $id
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function showAction($id)
    {
        $om = $this->getObjectManager();
        $entity = $this->getEntity($id);
        $owner = $om->getOwner($entity);
        $securityContext = $this->getSecurityContext();
        if (!($securityContext->isGranted('ROLE_ADMIN')||$securityContext->isGranted('VIEW', $entity))) {
            throw new AccessDeniedException();
        }

        return array('entity' => $entity, 'owner' => $owner);
    }

    /**
     * Create entity
     *
     * @Route("/new")
     * @Template()
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $om = $this->getObjectManager();
        $class = $this->getEntityClass();
        $entity = new $class();
        $form = $this->createForm($this->getCreateForm(), $entity);
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $om->persist($entity);
                $om->flush();
                $om->createACL($entity,$this->getDefaultACL());
                $message = ucfirst($this->getEntityName()) . ' ' . $entity . ' was created.';
                $this->addSessionFlash('success', $message);
                $route = str_replace("_create", "_show", $request->attributes->get('_route'));
                $url = $this->generateUrl($route,array('id' => $entity->getId()));

                return $this->redirect($url);
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * Edit entity
     *
     * @Route("/edit/{id}")
     * @Template()
     *
     * @param  mixed                                     $id
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id)
    {
        $om = $this->getObjectManager();
        $entity = $this->getEntity($id);
        $securityContext = $this->getSecurityContext();
        if (!($securityContext->isGranted('ROLE_ADMIN')||$securityContext->isGranted('EDIT', $entity))) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm($this->getEditForm(), $entity);
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $om->persist($entity);
                $om->flush();
                $message = 'Changes to ' . $this->getEntityName() . ' ' . $entity . ' were saved.';
                $this->addSessionFlash('success', $message);
                $route = str_replace("_edit", "_show", $request->attributes->get('_route'));
                $url = $this->generateUrl($route,array('id' => $entity->getId()));

                return $this->redirect($url);
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * Delete entity
     *
     * @Route("/delete/{id}")
     * @Template()
     *
     * @param  mixed                                     $id
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id)
    {
        $om = $this->getObjectManager();
        $entity = $this->getEntity($id);
        $securityContext = $this->getSecurityContext();
        if (!($securityContext->isGranted('ROLE_ADMIN')||$securityContext->isGranted('DELETE', $entity))) {
            throw new AccessDeniedException();
        }
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $message = ucfirst($this->getEntityName()) . ' ' . $entity . ' was permanently deleted.';
            $om->remove($entity);
            $om->flush();
            $this->addSessionFlash('success', $message);
            $route = str_replace("_delete", "_list", $request->attributes->get('_route'));
            $url = $this->generateUrl($route);

            return $this->redirect($url);
        }

        return array('entity' => $entity);
    }

    /**
     * Edit permissions
     *
     * @Route("/permissions/{id}")
     * @Template()
     *
     * @param  mixed                                     $id
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function permissionsAction($id)
    {
        $om = $this->getObjectManager();
        $entity = $this->getEntity($id);
        $acl_array = $om->getACL($entity);
        $securityContext = $this->getSecurityContext();
        if (!($securityContext->isGranted('ROLE_ADMIN')||$securityContext->isGranted('MASTER', $entity))) {
            throw new AccessDeniedException();
        }

        $data = array(
            'user_acl' => array(),
            'role_acl' => array()
        );
                
        foreach($acl_array as $acl_entry) {
            $identity = $acl_entry['identity'];
            if ($identity instanceof UserInterface) {
                $data['user_acl'][] = $acl_entry;
            } else if (is_string($identity)) {
                $data['role_acl'][] = $acl_entry;
            }
        }
        
        $form = $this->createForm(new AclType(), $data);
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $acl_array = array_merge($data['user_acl'], $data['role_acl']);
                $om->updateACL($entity, $acl_array);
                $message = 'Changes to ' . $this->getEntityName() . ' ' . $entity . ' permissions were saved.';
                $this->addSessionFlash('success', $message);
                $route = str_replace("_permissions", "_show", $request->attributes->get('_route'));
                $url = $this->generateUrl($route,array('id' => $entity->getId()));

                return $this->redirect($url);
            }
        }

        return array('form' => $form->createView(), 'entity' => $entity);
    }
    
    /**
     * Get entity
     *
     * @param  mixed                                                         $id
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \VIB\CoreBundle\Entity\Entity
     */
    protected function getEntity($id)
    {
        $class = $this->getEntityClass();
        if ($id instanceof $class) {
            return $id;
        }
        $om = $this->getObjectManager();
        try {
            $entity = $om->find($this->getEntityClass(),$id);
            if ($entity instanceof $class) {
                return $entity;
            }
            throw new NotFoundHttpException();
        } catch (NoResultException $e) {
            throw new NotFoundHttpException();
        }

        return null;
    }

    /**
     * Get default ACL
     * 
     * @param mixed $user
     * @return array
     */
    protected function getDefaultACL($user = null)
    {
        $user = (null === $user) ? $this->getUser() : $user;
        return array(
            array('identity' => $user,
                  'permission' => MaskBuilder::MASK_OWNER),
            array('identity' => 'ROLE_USER',
                  'permission' => MaskBuilder::MASK_VIEW));
    }

    /**
     * Get create form
     *
     * @return \Symfony\Component\Form\AbstractType
     */
    protected function getCreateForm()
    {
        return $this->getEditForm();
    }

    /**
     * Get edit form
     *
     * @return \Symfony\Component\Form\AbstractType
     */
    protected function getEditForm()
    {
        return null;
    }

    /**
     * Get managed entity class
     *
     * @return string
     */
    protected function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Get managed entity name
     *
     * @return string
     */
    protected function getEntityName()
    {
        $names = explode('|',$this->entityName);

        return $names[0];
    }

    /**
     * Get managed entity plural name
     *
     * @return string
     */
    protected function getEntityPluralName()
    {
        $names = explode('|',$this->entityName);

        return $names[1];
    }

    /**
     * Check if entity is controlled by this controller
     *
     * @param  object  $entity
     * @return boolean
     */
    protected function controls($entity)
    {
        $reflectionClass = new \ReflectionClass($entity);

        return $this->getEntityClass() == $reflectionClass->getName();
    }
}
