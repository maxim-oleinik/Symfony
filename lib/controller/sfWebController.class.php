<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebController provides web specific methods to sfController such as, url redirection.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id: sfWebController.class.php 30563 2010-08-06 11:22:44Z fabien $
 */
abstract class sfWebController extends sfController
{
  /**
   * Generates an URL from an array of parameters.
   *
   * @param mixed   $parameters An associative array of URL parameters or an internal URI as a string.
   * @param boolean $absolute   Whether to generate an absolute URL
   *
   * @return string A URL to a symfony resource
   */
  public function genUrl($parameters = array(), $absolute = false)
  {
    return sfUrlBuilder::generateUrl($this->context->getRouting(), $parameters, $absolute);
  }

  /**
   * Converts an internal URI string to an array of parameters.
   *
   * @param string $url An internal URI
   *
   * @return array An array of parameters
   */
  public function convertUrlStringToParameters($url)
  {
    return sfUrlBuilder::convertUrlStringToParameters($url);
  }

  /**
   * Redirects the request to another URL.
   *
   * @param string $url        An associative array of URL parameters or an internal URI as a string
   * @param int    $delay      A delay in seconds before redirecting. This is only needed on
   *                           browsers that do not support HTTP headers
   * @param int    $statusCode The status code
   *
   * @throws InvalidArgumentException If the url argument is null or an empty string
   */
  public function redirect($url, $delay = 0, $statusCode = 302)
  {
    if (empty($url))
    {
      throw new InvalidArgumentException('Cannot redirect to an empty URL.'); 
    }

    $url = $this->genUrl($url, true);
    // see #8083
    $url = str_replace('&amp;', '&', $url);

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Redirect to "%s"', $url))));
    }

    // redirect
    $response = $this->context->getResponse();
    $response->clearHttpHeaders();
    $response->setStatusCode($statusCode);

    // The Location header should only be used for status codes 201 and 3..
    // For other code, only the refresh meta tag is used
    if ($statusCode == 201 || ($statusCode >= 300 && $statusCode < 400))
    {
      $response->setHttpHeader('Location', $url);
    }

    $response->setContent(sprintf('<html><head><meta http-equiv="refresh" content="%d;url=%s"/></head></html>', $delay, htmlspecialchars($url, ENT_QUOTES, sfConfig::get('sf_charset'))));
    $response->send();
  }
}
