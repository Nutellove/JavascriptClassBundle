<?php

namespace Nutellove\JavascriptClassBundle\Controller;

interface InterfaceEntityController
{
  /**
   * Gets the Bundle Name as a string (correctly cased)
   * @abstract
   * @return string
   */
  public function getBundleName();

  /**
   * Gets the Entity Name as a string (correctly cased)
   * @abstract
   * @return string
   */
  public function getEntityName();

  /**
   * Returns the JS mapping for the fields in this Entity
   * @abstract
   * @return array
   */
  public function getJavascriptMapping();

}
