<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class BaseController extends FOSRestController
{
    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @param $item
     * @param Request $request
     * @return PaginationInterface
     */
    protected function paginate($item, Request $request)
    {
        $page = (int)$request->query->get('page', 1);
        $size = (int)$request->query->get('per_page', 10);
        if($page <= 0)
            throw new BadRequestHttpException('The page parameter should be a positive number.');
        if($size <= 0)
            throw new BadRequestHttpException('The per_page parameter should be a positive number.');
        if($size > 1000)
            throw new BadRequestHttpException('The per_page parameter should not exceed 1000.');
        return $this->get('knp_paginator')->paginate($item, $page, $size);
    }

    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->get('event_dispatcher');
    }

}
