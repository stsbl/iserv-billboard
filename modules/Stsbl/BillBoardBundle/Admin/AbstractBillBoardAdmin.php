<?php declare(strict_types = 1);

namespace Stsbl\BillBoardBundle\Admin;

use IServ\AdminBundle\Admin\AbstractAdmin;
use IServ\CrudBundle\Crud\AbstractCrud;

/*
 * The MIT License
 *
 * Copyright 2018 felix.
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
 * Abstract Base Class for Bill-Board Admin CRUDs
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
abstract class AbstractBillBoardAdmin extends AbstractCrud
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // set base parameter
        $this->routesPrefix = 'billboard/manage/';
        $this->routesNamePrefix = 'manage_';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($action)
    {
        // Move management into admin section for admins
        if ('page' === $action && $this->isAdmin()) {
            return AbstractAdmin::TEMPLATE_PAGE;
        } elseif ('crud_base' === $action && $this->isAdmin()) {
            return AbstractAdmin::TEMPLATE_BASE;
        }

        return parent::getTemplate($action);
    }

    /**
     * Checks if the CRUD is used as an authenticated admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isGranted('IS_AUTHENTICATED_ADMIN');
    }
}
