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

IServ.BillBoard.Image = IServ.register(function (IServ) {
    function updateDeleteForm(e)
    {
        var imageName = e.attr('data-image-name');
        var entryTitle = e.attr('data-image-entry-title');
        var question = __('Do you really want to delete the image "{0}" from entry "{1}"?', imageName, entryTitle);
        
        $('#image-delete-confirm-question').text(question);
        $('#image_delete_confirm_image_id').attr('value', e.attr('data-image-id'));
    }
    
    function registerImageDeleteHandler()
    {
        $('.billboard-delete-image').each(function() {
            $(this).click(function() {
                updateDeleteForm($(this));
            })
        })
    }
    
    function initialize()
    {
        registerImageDeleteHandler();
    }
    
    // Public API
    return {
        init: initialize
    };
}(IServ));
