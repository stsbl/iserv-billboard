<?php
// src/Stsbl/BillBoardBundle/Security/Privilege.php
namespace Stsbl\BillBoardBundle\Security;

/**
 * Privilege container for bill-board
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
 */
final class Privilege
{
    /**
     * Access privilege
     */
    const BILLBOARD = 'PRIV_BILLBOARD';

    /**
     * Creation privilege
     */
    const BILLBOARD_CREATE = 'PRIV_BILLBOARD_CREATE';

    /**
     * Moderation privilege
     */
    const BILLBOARD_MODERATE = 'PRIV_BILLBOARD_MODERATE';

    /**
     * Manage privilege
     */
    const BILLBOARD_MANAGE = 'PRIV_BILLBOARD_MANAGE';
}
