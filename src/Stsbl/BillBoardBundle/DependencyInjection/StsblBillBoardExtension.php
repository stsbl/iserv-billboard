<?php
// src/Stsbl/BillBoardBundle/DependencyInjection/StsblBillBoardExtension.php
namespace Stsbl\BillBoardBundle\DependencyInjection;

use IServ\CoreBundle\DependencyInjection\IServBaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/*
 * The MIT License
 *
 * Copyright 2018 Felix Jacobi.
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

class StsblBillBoardExtension extends IServBaseExtension
{
    const ALIAS = 'stsbl_billboard';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());
        $this->processConfiguration($configuration, $configs);

        parent::load($configs, $container);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }
}
