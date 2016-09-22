<?php
// src/Stsbl/BillBoardBundle/Security/Privilege.php
namespace Stsbl\BillBoardBundle\Security;

/**
 * Privilege container for bill-board
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
