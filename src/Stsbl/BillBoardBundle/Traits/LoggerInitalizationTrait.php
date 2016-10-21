<?php
// src/Stsbl/BillBoardBundle/Traits/LoggerInitalizationTrait.php
namespace Stsbl\BillBoardBundle\Traits;

/**
 * Trait with common function to initalize the LoggerTrait from CoreBundle.
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
 */
trait LoggerInitalizationTrait {
    /**
     * Initalizes the logger
     */
    protected function initalizeLogger()
    {  
        // set module context for logging
        $this->logModule = 'Bill-Board';
        
        $logger = $this->get('iserv.logger');
        $this->setLogger($logger);
    }
}
