<?php
// src/Stsbl/BillBoardBundle/Entity/Category.php
namespace Stsbl\BillBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IServ\CrudBundle\Entity\CrudInterface;
use Symfony\Component\Validator\Constraints as Assert;

/*
 * The MIT License
 *
 * Copyright 2017 Felix Jacobi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * BillBoardBundle:Category
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
 * @ORM\Entity(repositoryClass="CategoryRepository")
 * @ORM\Table(name="billboard_category")
 */
class Category implements CrudInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var int
     */
    private $id;
    
    /**
     * @ORM\Column(name="title",type="text",length=255)
     * @Assert\NotBlank()
     * 
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(name="description",type="text")
     * @Assert\NotBlank()
     * 
     * @var string
     */
    private $description;
    
    /**
     * Returns a human readable string
     * 
     * @return string
     */
    public function __toString()
    {
        return (string)$this->title;
    }

    /**
     * Get id
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get title
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get description
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set title
     * 
     * @param string $title
     * 
     * @return Category
     */
    public function setTitle($title)
    {
        $this->title = $title;
        
        return $this;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Category
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
