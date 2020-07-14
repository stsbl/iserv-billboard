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

(function () {
    "use strict";
    
    function updateDeleteForm($element)
    {
        const imageName = $element.attr('data-image-name');
        const entryTitle = $element.attr('data-image-entry-title');
        const question = __('Do you really want to delete the image "{0}" from entry "{1}"?', imageName, entryTitle);
        
        $('#image-delete-confirm-question').text(question);
        $('#image_delete_confirm_image_id').attr('value', $element.attr('data-image-id'));
    }

    function initialize()
    {
        $('.billboard-delete-image').each(function () {
            $(this).click(function () {
                updateDeleteForm($(this));
            });
        });
    }
    
    $(document).ready(initialize);
}());
