import '../legacy/subscribe';
import Dialog from '../legacy/dialog';
import Textarea from "../libs/textarea";

$(() => {
  'use strict';

  $('#box-comment').on('submit', 'form.comment-form', e => {
    let form = $(e.currentTarget);

    $.post(form.attr('action'), form.serialize(), html => {
      let commentId = parseInt($(':hidden[name="comment_id"]', form).val());
      form.find('textarea').val('');

      if (commentId) {
        $('#comment-' + commentId).replaceWith(html);
      } else {
        $('#comments-container').prepend(html);
      }
    })
      .always(() => {
        form.find(':submit').removeAttr('disabled').text('Zapisz');
      })
      .error(event => {
        if (typeof event.responseJSON !== 'undefined') {
          let error = '';

          if (typeof event.responseJSON.error !== 'undefined') {
            error = event.responseJSON.error;
          } else {
            let key = Object.keys(event.responseJSON)[0];
            error = event.responseJSON[key];
          }

          Dialog.alert({message: error}).show();
        }
      });

    return false;
  })
    .on('click', '.btn-edit', e => {
      let self = $(e.currentTarget);

      $('.comment-form, .comment-content', '#comment-' + self.data('id')).toggle();
      $('.comment-form').find('textarea').inputFocus();
    })
    .on('click', 'button.btn-cancel', e => {
      let form = $(e.currentTarget).parents('form');

      form.hide();
      form.prev('.comment-content').show();
    })
    .on('focus', 'textarea:not(.clicked)', e => {
      $(e.currentTarget).addClass('clicked').autogrow().prompt().fastSubmit();
    });

  /**
   * Upload attachment
   */
  $('#btn-upload').click(function () {
    $('.input-file').click();
  });

  $('.input-file').change(function () {
    var $form = $('#submit-form');
    var formData = new FormData($form[0]);

    $.ajax({
      url: __INITIAL_STATE.upload_url,
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      beforeSend: function () {
        $('#btn-upload').attr('disabled', 'disabled').text('Wysyłanie...');
      },
      success: function (html) {
        $('#attachments .text-center').remove();
        $('#attachments tbody').append(html);
      },
      error: function (err) {
        $('#alert').modal('show');

        if (typeof err.responseJSON !== 'undefined') {
          $('.modal-body').text(err.responseJSON.attachment[0]);
        }
      },
      complete: function () {
        $('#btn-upload').removeAttr('disabled').text('Dodaj załącznik');
      },
    }, 'json');

    return false;
  });

  $('#attachments')
    .on('click', '.btn-del', function () {
      $(this).parents('tr').remove();
    })
    .on('click', '.btn-append', function () {
      let $form = $(this).parents('form');

      let file = $(this).data('url');
      let suffix = file.split('.').pop().toLowerCase();
      let markdown = '';

      if (suffix === 'png' || suffix === 'jpg' || suffix === 'jpeg' || suffix === 'gif') {
        markdown = '![' + $(this).text() + '](' + $(this).data('url') + ')';
      } else {
        markdown = '[' + $(this).text() + '](' + $(this).data('url') + ')';
      }

      const textarea = new Textarea($('textarea[name="text"]', $form)[0]);
      textarea.insertAtCaret("\n", "\n", textarea.isSelected() ? textarea.getSelection() : markdown);
    });
});
