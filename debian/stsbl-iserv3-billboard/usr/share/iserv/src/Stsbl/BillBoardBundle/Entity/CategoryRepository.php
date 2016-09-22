<?php
// src/Stsbl/BillBoardBundle/Entity/CategoryRepository.php
namespace Stsbl\BillBoardBundle\Entity;

use Doctrine\ORM\NoResultException;
use IServ\CrudBundle\Doctrine\ORM\EntitySpecificationRepository;

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license http://gnu.org/licenses/gpl-3.0 GNU Lesser General Public License
 */
class CategoryRepository extends EntitySpecificationRepository
{
    /**
     * Checks if there's at least one category
     * 
     * @return bool
     */
     public function exists()
     {
	$qb = $this->createQueryBuilder('c');
	$qb
            ->select('1')
            ->setMaxResults(1);
        ;

        try {
            $qb->getQuery()->getSingleScalarResult();
            return true;
	} catch (NoResultException $e) {
            return false;
	}
    }
}
