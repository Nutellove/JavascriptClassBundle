<?php

/**
 * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
 * Front Controller to avoid generating routes
 * Is this the best way of dealing with routes ?
 */

namespace Nutellove\JavascriptClassBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FrontController extends Controller
{

  /**
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   * @param string $bundleName
   * @param string $entityName
   * @param  $id
   * @return
   */
  public function loadAction (string $bundleName, string $entityName, $id)
  {
    $controllerClass = __NAMESPACE__."\\Entity\\$bundleName\\$entityName"."Controller";
    if ( ! class_exists($controllerClass) ) {
      throw new NotFoundHttpException('The targeted Controller does not exist.');
    }

    $controllerABC = "JavascriptClassBundle:Entity\\$bundleName\\$entityName:load";
    $response = $this->forward ($controllerABC, array('id' => $id));

    return $response;
  }

  /**
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   * @param string $bundleName
   * @param string $entityName
   * @param  $id
   * @return
   */
  public function saveAction (string $bundleName, string $entityName, $id)
  {
    $controllerClass = __NAMESPACE__."\\Entity\\$bundleName\\$entityName"."Controller";
    if ( ! class_exists($controllerClass) ) {
      throw new NotFoundHttpException('The targeted Controller does not exist.');
    }

    $controllerABC = "JavascriptClassBundle:Entity\\$bundleName\\$entityName:save";
    $response = $this->forward ($controllerABC, array('id' => $id));

    return $response;
  }
}
