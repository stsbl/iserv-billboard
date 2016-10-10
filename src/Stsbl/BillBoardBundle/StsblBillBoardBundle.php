<?php
// src/Stsbl/BillBoardBundle/StsblBillBoardBundle.php
namespace Stsbl\BillBoardBundle;

use Stsbl\BillBoardBundle\DependencyInjection\StsblBillBoardExtension;
use IServ\CoreBundle\Routing\AutoloadRoutingBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license http://gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class StsblBillBoardBundle extends Bundle implements AutoloadRoutingBundleInterface
{
    public function getContainerExtension()
    {
        return new StsblBillBoardExtension();
    }
}
