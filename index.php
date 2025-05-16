<?php
/*
 * @author Tropotek <http://www.tropotek.com/>
 */

try {
    require_once __DIR__ . '/_prepend.php';

    $response = \Bs\Factory::instance()->getFrontController()->handle(\Bs\Factory::instance()->getRequest());
    $response->send();
    \Bs\Factory::instance()->getFrontController()->terminate(\Bs\Factory::instance()->getRequest(), $response);
} catch (\Exception $e) {
    error_log($e->__toString());
}
