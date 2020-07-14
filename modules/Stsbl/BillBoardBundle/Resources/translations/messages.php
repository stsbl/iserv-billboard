<?php declare(strict_types = 1);

/*
 * The MIT License
 *
 * Copyright 2020 Felix Jacobi.
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
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */

// Privileges
_('Access the bill-board');
_('Users with this privilege are able to access the bill-board and read the entries but there are not able to create '.
    'and edit entries.');

_('Create new entries at the bill-board');
_('Users with this privilege can create new entries on the bill-board, edit their existing entries and comment '.
    'entries of other users.');

_('Moderate the bill-board');
_('Users with this privilege can edit and delete every entry on the bill-board. Includes the creation privilege.');

_('Manage the bill-board');
_('Users with this privilege can manange the bill-board and create new categories. Includes the moderation privilege.');

// iservcfg
_('Module: Bill-Board');
_('Allow comments');
_('Control whether comments on entries are allowed or not. If you disable commenting, no one can add new comments. '.
    'Existing comments will still displayed.');

// Notifications
_('New comment on your post: %s commented on %s');
_('Your entry was locked: %s locked %s');
_('Your entry was opened: %s opened %s');
_('Bill-Board');