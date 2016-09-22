<?php
// src/Stsbl/BillBoardBundle/Service/LoggingService.php
namespace Stsbl\BillBoardBundle\Service;

use IServ\CoreBundle\Service\Logger;

/**
 * Service for writing module log entries
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <https://gnu.org/licenses/gpl-3.0>
 */
class LoggingService {
    
    /**
     * Contains the instance of Logger for the service
     * 
     * @var Logger
     */
    protected $logger;
    
    /**
     * Contains the name of the module to log for
     * 
     * @var string
     */
    protected $module;

    /**
     * Injects the logger into the class and sets the parameters
     * 
     * @param Logger $logger
     */
    public function __construct(Logger $logger, $module = '')
    {
        $this->logger = $logger;
        $this->module = $module;
    }
    
    /**
     * Writes a new log entry in the name of the module
     * 
     * @param string $text
     */
    public function writeLog($text)
    {
        $this->logger->writeForModule($text, $this->module);
    }
}
