<?php
/**
 * ClientInterface.php
 *
 * @author: David Baker <dbaker@acorncomputersolutions.com
 * Date: 12/19/13
 * Time: 4:16 PM
 */

namespace Payum\DineroMail\Api;


interface ClientInterface {

    function getDineroMailLastResponse();

    function getDineroMailLastRequest();

}
